<?php
// Use shared DB connection from config/db.php
require_once __DIR__ . '/../../config/db.php';
$pdo = getPDO();

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        switch($action) {
            case 'uploadImage':
                // Handle image upload (expects `image` file in multipart/form-data)
                if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
                    break;
                }

                $file = $_FILES['image'];
                $maxSize = 5 * 1024 * 1024; // 5 MB
                if ($file['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'message' => 'File is too large (max 5 MB)']);
                    break;
                }

                // Validate MIME type using finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mime, $allowed, true)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid image type']);
                    break;
                }

                $origName = basename($file['name']);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowedExt, true)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid file extension']);
                    break;
                }

                // Save uploads into the real `public/uploads` directory at project root
                $publicDir = realpath(__DIR__ . '/../../../public');
                if ($publicDir === false) {
                    // fallback: try one less level
                    $publicDir = realpath(__DIR__ . '/../../public');
                }

                if ($publicDir === false) {
                    echo json_encode(['success' => false, 'message' => 'Unable to locate public directory to store uploads']);
                    break;
                }

                $uploadDir = $publicDir . DIRECTORY_SEPARATOR . 'uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeName = uniqid('img_', true) . '.' . $ext;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Compute a web-accessible path by subtracting the DOCUMENT_ROOT portion
                    $full = realpath($targetPath);
                    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
                    if ($full !== false && $docRoot !== false && strpos($full, $docRoot) === 0) {
                        $webPath = '/' . str_replace('\\', '/', ltrim(substr($full, strlen($docRoot)), '/\\'));
                    } else {
                        // fallback to a relative public/uploads path
                        $webPath = 'public/uploads/' . $safeName;
                    }

                    echo json_encode(['success' => true, 'path' => $webPath]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
                }
                break;
            case 'getProducts':
                // Align column names with `database_schema.sql`:
                // - categories.name => category_name (alias)
                // - menu_items.name => item_name (alias)
                // - menu_items.price => base_price (alias)
                $stmt = $pdo->query("
                    SELECT mi.item_id, mi.category_id, c.name AS category_name,
                    mi.name AS item_name, mi.description, mi.image_path, mi.price AS base_price,
                    GROUP_CONCAT(DISTINCT CONCAT(is2.size_id, ':', is2.price) SEPARATOR '|') as sizes
                    FROM menu_items mi
                    LEFT JOIN categories c ON mi.category_id = c.category_id
                    LEFT JOIN item_sizes is2 ON mi.item_id = is2.item_id
                    GROUP BY mi.item_id
                ");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
                
            case 'getCategories':
                // Alias `name` as `category_name` so front-end code (`cat.category_name`) works
                $stmt = $pdo->query("SELECT category_id, name AS category_name FROM categories");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
                
            case 'getAddons':
                $stmt = $pdo->query("SELECT * FROM addons");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
                
            case 'getCookiesPackages':
                $stmt = $pdo->query("SELECT * FROM cookies_packages");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
                
            case 'addProduct':
                $pdo->beginTransaction();
                
                // Insert product (align to schema column names)
                // Accept an optional `image_path` in the payload (URL or relative path)
                $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, image_path, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['category_id'],
                    $data['name'],
                    $data['description'],
                    $data['image_path'] ?? null,
                    $data['base_price']
                ]);
                $itemId = $pdo->lastInsertId();

                // Sizes: schema `item_sizes` expects (item_id, size_name, price)
                // Accept either `size_name` or `size_id` from client; map common ids to names.
                $sizeNameMap = [
                    1 => '12oz Hot',
                    2 => '12oz Cold',
                    3 => '16oz Hot',
                    4 => '16oz Cold'
                ];

                if (!empty($data['sizes'])) {
                    $sizeStmt = $pdo->prepare("INSERT INTO item_sizes (item_id, size_name, price) VALUES (?, ?, ?)");
                    foreach ($data['sizes'] as $size) {
                        $sizeName = $size['size_name'] ?? ($sizeNameMap[$size['size_id']] ?? null);
                        if ($sizeName === null) continue; // skip malformed size
                        $sizeStmt->execute([$itemId, $sizeName, $size['price']]);
                    }
                }

                // Addons relation: some schemas use `item_addons(item_id, addon_id)`
                // If table exists, attach addons; if not, skip gracefully.
                if (!empty($data['addons'])) {
                    try {
                        $addonStmt = $pdo->prepare("INSERT INTO item_addons (item_id, addon_id) VALUES (?, ?)");
                        foreach ($data['addons'] as $addonId) {
                            $addonStmt->execute([$itemId, $addonId]);
                        }
                    } catch (PDOException $e) {
                        // If `item_addons` doesn't exist, ignore and continue.
                        error_log('item_addons missing or insert failed: ' . $e->getMessage());
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Product added successfully']);
                break;
                
            case 'updateProduct':
                $pdo->beginTransaction();
                
                // Update product (use schema columns)
                // Update product including optional `image_path`
                $stmt = $pdo->prepare("UPDATE menu_items SET name=?, category_id=?, price=?, description=?, image_path=? WHERE item_id=?");
                $stmt->execute([
                    $data['name'],
                    $data['category_id'],
                    $data['base_price'],
                    $data['description'],
                    $data['image_path'] ?? null,
                    $data['item_id']
                ]);

                // Replace sizes: delete existing then insert new (size_name, price)
                $pdo->prepare("DELETE FROM item_sizes WHERE item_id=?")->execute([$data['item_id']]);
                if (!empty($data['sizes'])) {
                    $sizeStmt = $pdo->prepare("INSERT INTO item_sizes (item_id, size_name, price) VALUES (?, ?, ?)");
                    foreach ($data['sizes'] as $size) {
                        $sizeName = $size['size_name'] ?? ($sizeNameMap[$size['size_id']] ?? null);
                        if ($sizeName === null) continue;
                        $sizeStmt->execute([$data['item_id'], $sizeName, $size['price']]);
                    }
                }

                // Replace addons if table exists
                try {
                    $pdo->prepare("DELETE FROM item_addons WHERE item_id=?")->execute([$data['item_id']]);
                    if (!empty($data['addons'])) {
                        $addonStmt = $pdo->prepare("INSERT INTO item_addons (item_id, addon_id) VALUES (?, ?)");
                        foreach ($data['addons'] as $addonId) {
                            $addonStmt->execute([$data['item_id'], $addonId]);
                        }
                    }
                } catch (PDOException $e) {
                    error_log('item_addons missing or update failed: ' . $e->getMessage());
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
                break;
                
            case 'deleteProduct':
                $stmt = $pdo->prepare("DELETE FROM menu_items WHERE item_id=?");
                $stmt->execute([$data['item_id']]);
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
                break;
                
            case 'addCookiePackage':
                $stmt = $pdo->prepare("INSERT INTO cookies_packages (package_name, price, quantity, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$data['name'], $data['price'], $data['quantity'], $data['description']]);
                echo json_encode(['success' => true, 'message' => 'Cookie package added successfully']);
                break;
                
            case 'updateCookiePackage':
                $stmt = $pdo->prepare("UPDATE cookies_packages SET package_name=?, price=?, quantity=?, description=? WHERE package_id=?");
                $stmt->execute([$data['name'], $data['price'], $data['quantity'], $data['description'], $data['package_id']]);
                echo json_encode(['success' => true, 'message' => 'Cookie package updated successfully']);
                break;
                
            case 'deleteCookiePackage':
                $stmt = $pdo->prepare("DELETE FROM cookies_packages WHERE package_id=?");
                $stmt->execute([$data['package_id']]);
                echo json_encode(['success' => true, 'message' => 'Cookie package deleted successfully']);
                break;
        }
    } catch(Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Makyś Café - Product Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../../styles/global.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
        }
        
        .light-mode {
            --bg-primary: #f4f6fb;
            --bg-secondary: #ffffff;
            --bg-gradient: linear-gradient(135deg, #f4f6fb 0%, #eef2ff 100%);
            --text-primary: #0f172a;
            --text-secondary: #6b7280;
            --accent: #5b21b6; /* deeper indigo for better contrast */
            --accent-hover: #4c1d95;
            --border: #e6e8ec;
            --sidebar-bg: linear-gradient(180deg, #5b21b6 0%, #7c3aed 100%);
            --card-shadow: 0 1px 6px rgba(91,33,182,0.06);
        }
        
        .dark-mode {
            --bg-primary: #0a0a0b;
            --bg-secondary: #18181b;
            --bg-gradient: linear-gradient(135deg, #0a0a0b 0%, #18181b 100%);
            --text-primary: #fafafa;
            --text-secondary: #a1a1aa;
            --accent: #8b5cf6;
            --accent-hover: #a78bfa;
            --border: #27272a;
            --sidebar-bg: linear-gradient(180deg, #18181b 0%, #27272a 100%);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }
        
        body {
            background: var(--bg-gradient);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        /* Modern form controls that respect theme variables */
        /* Apply to inputs regardless of explicit type attribute so unnamed inputs still get proper color */
        input, textarea, select, input[type="file"] {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            transition: box-shadow .18s ease, transform .12s ease, border-color .12s ease;
        }

        input::placeholder, textarea::placeholder {
            color: var(--text-secondary);
            opacity: 1;
        }

        /* Modal action buttons that should be visible in both themes */
        .modal-btn {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: filter .12s ease, box-shadow .12s ease, transform .08s ease;
        }

        .modal-btn:hover {
            filter: brightness(0.98);
            box-shadow: 0 6px 14px rgba(16,24,40,0.06);
            transform: translateY(-2px);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            box-shadow: 0 6px 18px rgba(91,33,182,0.12);
            border-color: var(--accent);
            transform: translateY(-1px);
        }

        /* Buttons - subtle lift + transition */
        button { transition: transform .12s ease, box-shadow .12s ease; }
        button:hover { transform: translateY(-3px); }

        /* Modal inner card animation & style */
        .fixed.inset-0 .card { 
            transform: translateY(8px) scale(.98);
            opacity: 0;
            transition: transform .18s cubic-bezier(.2,.9,.3,1), opacity .18s ease;
        }
        .fixed.inset-0:not(.hidden) .card {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        /* Backdrop transition */
        .fixed.inset-0.bg-black { transition: background-color .18s ease, opacity .18s ease; }

        /* File input: nicer minimal appearance */
        input[type="file"] { background: transparent; padding: .25rem 0; }

        /* Card hover enhancement already exists, make it smoother */
        .card { transition: box-shadow .18s ease, transform .12s ease; }
        .card:hover { transform: translateY(-4px); }

        /* Decorative accents for product cards */
        .product-card { position: relative; overflow: hidden; }
        .product-card .decor { position: absolute; right: -30px; top: -30px; opacity: 0.12; transform: rotate(25deg); }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transform: translateX(-100%);
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        .sidebar-link {
            transition: all 0.2s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(4px);
        }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }
        
        .product-card .accent-dot { position: absolute; left: -24px; bottom: -24px; width: 120px; height: 120px; border-radius: 9999px; background: linear-gradient(135deg, rgba(91,33,182,0.08), rgba(76,29,149,0.06)); }
        .product-card .price-badge { box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
                .card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(91,33,182,0.12);
                }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            transition: all 0.2s ease;
            box-shadow: 0 6px 16px rgba(91,33,182,0.08);
        }
        
        .btn-primary:hover {
            filter: brightness(0.98);
            transform: translateY(-1px);
        }
        
        .size-badge {
            transition: all 0.2s ease;
        }
        
        .size-badge:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                z-index: 50;
            }
        }
        
        @media (min-width: 769px) {
            .sidebar {
                position: fixed;
                left: 0;
            }
        }
    </style>
</head>
<body class="light-mode">
    <!-- Mobile Overlay -->
    <div id="overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed top-0 left-0 h-screen z-50">
        <div class="p-6 border-b border-white border-opacity-20">
            <h1 class="text-white text-2xl font-bold">Makyś Café</h1>
        </div>
        
        <nav class="p-4 space-y-2">
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white bg-white bg-opacity-20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Products</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>Categories</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Inventory</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Orders</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Add-ons</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Cookies Packages</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Reports</span>
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main id="mainContent" class="transition-all duration-300">
        <!-- Topbar -->
        <header class="card sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="menuToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h2 class="text-2xl font-bold">Product Management</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <button id="themeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg id="lightIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="darkIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                
                <button class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <div class="flex items-center gap-3 pl-4 border-l" style="border-color: var(--border)">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=8b5cf6&color=fff" alt="Admin" class="w-10 h-10 rounded-full">
                    <div class="hidden md:block">
                        <p class="font-semibold">Admin User</p>
                        <p class="text-sm" style="color: var(--text-secondary)">admin@makyscafe.com</p>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content Area -->
        <div class="p-6">
            <!-- Action Bar -->
            <div class="card p-4 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    <input type="text" id="searchInput" placeholder="Search products..." class="px-4 py-2 rounded-lg border w-full md:w-64" style="border-color: var(--border); background: var(--bg-secondary)">
                    
                    <select id="categoryFilter" class="px-4 py-2 rounded-lg border" style="border-color: var(--border); background: var(--bg-secondary)">
                        <option value="">All Categories</option>
                    </select>
                    
                    <select id="typeFilter" class="px-4 py-2 rounded-lg border" style="border-color: var(--border); background: var(--bg-secondary)">
                        <option value="menu">Menu Items</option>
                        <option value="cookies">Cookie Packages</option>
                    </select>
                </div>
                
                <button id="addProductBtn" class="btn-primary px-6 py-2 rounded-lg font-semibold flex items-center gap-2 w-full md:w-auto justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Product
                </button>
            </div>
            
            <!-- Products Grid -->
            <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Products will be loaded here -->
            </div>
            
            <!-- Cookie Packages Grid -->
            <div id="cookiesGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Cookie packages will be loaded here -->
            </div>
        </div>
    </main>
    
    <script>
        let products = [];
        let categories = [];
        let addons = [];
        let cookiePackages = [];
        
        const sizeNames = {
            1: '12oz Hot',
            2: '12oz Cold',
            3: '16oz Hot',
            4: '16oz Cold'
        };
        
        document.addEventListener('DOMContentLoaded', async () => {
            // Apply saved theme (persisted in localStorage) so refresh keeps selection.
            // If no saved theme, follow the OS preference (`prefers-color-scheme`).
            const storedTheme = localStorage.getItem('makys_theme');
            let theme;
            if (storedTheme) {
                theme = storedTheme;
            } else {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = prefersDark ? 'dark' : 'light';
            }

            const bodyEl = document.body;
            const lightIcon = document.getElementById('lightIcon');
            const darkIcon = document.getElementById('darkIcon');

            if (theme === 'dark') {
                bodyEl.classList.remove('light-mode');
                bodyEl.classList.add('dark-mode');
                lightIcon.classList.add('hidden');
                darkIcon.classList.remove('hidden');
            } else {
                bodyEl.classList.remove('dark-mode');
                bodyEl.classList.add('light-mode');
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            }

            await loadData();
            setupEventListeners();
            renderProducts();
        });
        
        async function loadData() {
            try {
                const [productsRes, categoriesRes, addonsRes, cookiesRes] = await Promise.all([
                    fetch('?action=getProducts', { method: 'POST' }),
                    fetch('?action=getCategories', { method: 'POST' }),
                    fetch('?action=getAddons', { method: 'POST' }),
                    fetch('?action=getCookiesPackages', { method: 'POST' })
                ]);
                
                const productsData = await productsRes.json();
                const categoriesData = await categoriesRes.json();
                const addonsData = await addonsRes.json();
                const cookiesData = await cookiesRes.json();
                
                if (productsData.success) products = productsData.data;
                if (categoriesData.success) categories = categoriesData.data;
                if (addonsData.success) addons = addonsData.data;
                if (cookiesData.success) cookiePackages = cookiesData.data;
                
                populateCategories();
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }
        
        function populateCategories() {
            const select = document.getElementById('categoryFilter');
            select.innerHTML = '<option value="">All Categories</option>';
            categories.forEach(cat => {
                select.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
            });
        }
        
        function renderProducts() {
            const grid = document.getElementById('productsGrid');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            
            let filtered = products.filter(p => {
                const matchSearch = p.item_name.toLowerCase().includes(searchTerm) || 
                                  (p.description && p.description.toLowerCase().includes(searchTerm));
                const matchCategory = !categoryFilter || p.category_id == categoryFilter;
                return matchSearch && matchCategory;
            });
            
            grid.innerHTML = '';
            
            filtered.forEach(product => {
                const sizes = parseSizes(product.sizes);
                const sizesBadges = sizes.map(s => 
                    `<span class="size-badge inline-block px-2 py-1 rounded text-xs font-semibold mr-1 mb-1" style="background: var(--accent); color: white;">
                        ${sizeNames[s.size_id]} - ₱${parseFloat(s.price).toFixed(2)}
                    </span>`
                ).join('');

                const imageSrc = product.image_path ? product.image_path : null;
                const imageHtml = imageSrc ?
                    `<img src="${imageSrc}" alt="${product.item_name}" class="w-20 h-20 rounded-lg object-cover shadow-sm">` :
                    `<div class="w-20 h-20 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 11h8M8 15h5" />
                        </svg>
                    </div>`;

                grid.innerHTML += `
                    <div class="card product-card rounded-xl p-6">
                        <svg class="decor w-48 h-48" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="g1" x1="0%" x2="100%" y1="0%" y2="100%">
                                    <stop offset="0%" stop-color="var(--accent)" stop-opacity="0.16" />
                                    <stop offset="100%" stop-color="var(--accent-hover)" stop-opacity="0.06" />
                                </linearGradient>
                            </defs>
                            <path fill="url(#g1)" d="M43.4,-28.9C55.7,-12.9,60.3,9.6,52.9,26.4C45.5,43.2,26.1,54.3,6.8,53.3C-12.6,52.3,-25.1,39.1,-35.7,23C-46.3,6.9,-55,-11.2,-48.2,-25.2C-41.3,-39.2,-18.8,-49.1,0.8,-49.7C20.3,-50.3,40.9,-41,43.4,-28.9Z" transform="translate(100 100)" />
                        </svg>
                        <div class="accent-dot"></div>
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                ${imageHtml}
                                <div>
                                    <h3 class="text-xl font-bold mb-1">${product.item_name}</h3>
                                    <p class="text-sm" style="color: var(--text-secondary)">${product.category_name || 'No Category'}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold price-badge" style="background: var(--accent); color: white;">
                                ₱${parseFloat(product.base_price).toFixed(2)}
                            </span>
                        </div>

                        <p class="text-sm mb-4" style="color: var(--text-secondary)">${product.description || 'No description'}</p>

                        <div class="mb-4">
                            <p class="text-xs font-semibold mb-2" style="color: var(--text-secondary)">AVAILABLE SIZES</p>
                            <div class="flex flex-wrap">
                                ${sizesBadges || '<span class="text-sm" style="color: var(--text-secondary)">No sizes available</span>'}
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button data-id="${product.item_id}" aria-label="View ${product.item_name}" class="view-btn flex-1 px-4 py-2 rounded-lg border font-semibold hover:bg-gray-50 dark:hover:bg-gray-800" style="border-color: var(--border)">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>View</span>
                                </span>
                            </button>
                            <button data-id="${product.item_id}" aria-label="Edit ${product.item_name}" class="edit-btn flex-1 btn-primary px-4 py-2 rounded-lg font-semibold inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 20h4l10-10a2.828 2.828 0 00-4-4L4 16v4z" />
                                </svg>
                                <span>Edit</span>
                            </button>
                            <button data-id="${product.item_id}" aria-label="Delete ${product.item_name}" class="delete-btn px-4 py-2 rounded-lg font-semibold bg-red-500 text-white hover:bg-red-600 inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-lg font-semibold mb-2">No products found</p>
                        <p style="color: var(--text-secondary)">Try adjusting your search or filters</p>
                    </div>
                `;
            }
        }
        
        function renderCookies() {
            const grid = document.getElementById('cookiesGrid');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = cookiePackages.filter(p => 
                p.package_name.toLowerCase().includes(searchTerm) || 
                (p.description && p.description.toLowerCase().includes(searchTerm))
            );
            
            grid.innerHTML = '';
            
            filtered.forEach(pkg => {
                grid.innerHTML += `
                    <div class="card rounded-xl p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold mb-1">${pkg.package_name}</h3>
                                <p class="text-sm" style="color: var(--text-secondary)">Qty: ${pkg.quantity} pieces</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold" style="background: var(--accent); color: white;">
                                ₱${parseFloat(pkg.price).toFixed(2)}
                            </span>
                        </div>
                        
                        <p class="text-sm mb-4" style="color: var(--text-secondary)">${pkg.description || 'No description'}</p>
                        
                        <div class="flex gap-2">
                            <button class="flex-1 btn-primary px-4 py-2 rounded-lg font-semibold">
                                Edit
                            </button>
                            <button class="px-4 py-2 rounded-lg font-semibold bg-red-500 text-white hover:bg-red-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-lg font-semibold mb-2">No cookie packages found</p>
                        <p style="color: var(--text-secondary)">Try adjusting your search</p>
                    </div>
                `;
            }
        }
        
        function parseSizes(sizesStr) {
            if (!sizesStr) return [];
            return sizesStr.split('|').map(s => {
                const [size_id, price] = s.split(':');
                return { size_id: parseInt(size_id), price: parseFloat(price) };
            });
        }
        
        function setupEventListeners() {
            // Theme toggle
            document.getElementById('themeToggle').addEventListener('click', () => {
                const body = document.body;
                const lightIcon = document.getElementById('lightIcon');
                const darkIcon = document.getElementById('darkIcon');
                
                if (body.classList.contains('light-mode')) {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
                    localStorage.setItem('makys_theme', 'dark');
                } else {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                    localStorage.setItem('makys_theme', 'light');
                }
            });
            
            // Menu toggle
            document.getElementById('menuToggle').addEventListener('click', () => {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                sidebar.classList.toggle('open');
                overlay.classList.toggle('hidden');
            });
            
            document.getElementById('overlay').addEventListener('click', () => {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                sidebar.classList.remove('open');
                overlay.classList.add('hidden');
            });
            
            // Search and filters
            document.getElementById('searchInput').addEventListener('input', () => {
                const type = document.getElementById('typeFilter').value;
                type === 'menu' ? renderProducts() : renderCookies();
            });
            
            document.getElementById('categoryFilter').addEventListener('change', renderProducts);
            
            document.getElementById('typeFilter').addEventListener('change', (e) => {
                const productsGrid = document.getElementById('productsGrid');
                const cookiesGrid = document.getElementById('cookiesGrid');
                
                if (e.target.value === 'menu') {
                    productsGrid.classList.remove('hidden');
                    cookiesGrid.classList.add('hidden');
                    renderProducts();
                } else {
                    productsGrid.classList.add('hidden');
                    cookiesGrid.classList.remove('hidden');
                    renderCookies();
                }
            });
            
            
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${bgColor} text-white font-semibold`;
            notification.style.transform = 'translateX(400px)';
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    ${type === 'success' ? `
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    ` : type === 'error' ? `
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    ` : `
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    `}
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
    <?php
    // Include modal fragments
    require_once __DIR__ . '/../../modals/product_view_modal.php';
    require_once __DIR__ . '/../../modals/product_add_modal.php';
    require_once __DIR__ . '/../../modals/product_edit_modal.php';
    require_once __DIR__ . '/../../modals/product_delete_modal.php';
    ?>

    <script>
        // Modal handling and wiring for add/edit/view/delete
        (function(){
            function showModal(id){ document.getElementById(id).classList.remove('hidden'); }
            function hideModal(id){ document.getElementById(id).classList.add('hidden'); }

            // Open Add Product Modal
            document.getElementById('addProductBtn').addEventListener('click', () => {
                // populate category select
                const sel = document.getElementById('add_category');
                sel.innerHTML = '<option value="">Select category</option>';
                categories.forEach(c => sel.innerHTML += `<option value="${c.category_id}">${c.category_name}</option>`);
                document.getElementById('productAddModal').classList.remove('hidden');
            });

            // Close add modal
            document.getElementById('closeProductAdd').addEventListener('click', () => hideModal('productAddModal'));
            document.getElementById('cancelAddProduct').addEventListener('click', () => hideModal('productAddModal'));

            // Add form submit
            document.getElementById('addProductForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const fileEl = document.getElementById('add_image_file');
                let imagePath = document.getElementById('add_image_path').value || null;
                if (fileEl && fileEl.files && fileEl.files.length) {
                    const file = fileEl.files[0];
                    // Client-side validation: MIME and extension and size
                    const allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                    if (!allowedTypes.includes(file.type)) { showNotification('Only JPG/PNG/GIF/WEBP images allowed','error'); return; }
                    if (file.size > 5 * 1024 * 1024) { showNotification('Image too large (max 5MB)','error'); return; }
                    const fd = new FormData(); fd.append('image', file);
                    const res = await fetch('?action=uploadImage', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (json.success) imagePath = json.path; else { showNotification(json.message||'Upload failed','error'); return; }
                }

                const payload = {
                    category_id: document.getElementById('add_category').value,
                    name: document.getElementById('add_name').value,
                    description: document.getElementById('add_description').value,
                    base_price: parseFloat(document.getElementById('add_price').value) || 0,
                    image_path: imagePath
                };

                const resp = await fetch('?action=addProduct', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
                const j = await resp.json();
                if (j.success) {
                    hideModal('productAddModal');
                    await loadData(); renderProducts();
                    showNotification('Product added','success');
                } else showNotification(j.message || 'Add failed','error');
            });

            // Delegate product grid clicks
            document.getElementById('productsGrid').addEventListener('click', async (e) => {
                const viewBtn = e.target.closest('.view-btn');
                const editBtn = e.target.closest('.edit-btn');
                const deleteBtn = e.target.closest('.delete-btn');
                if (viewBtn) {
                    const id = viewBtn.getAttribute('data-id');
                    const prod = products.find(p=>p.item_id==id);
                    if (!prod) return;
                    document.getElementById('viewProductImage').src = prod.image_path || 'public/images/placeholder.png';
                    document.getElementById('viewProductName').textContent = prod.item_name;
                    document.getElementById('viewProductCategory').textContent = prod.category_name || '';
                    document.getElementById('viewProductDescription').textContent = prod.description || '';
                    document.getElementById('viewProductPrice').textContent = '₱' + parseFloat(prod.base_price).toFixed(2);
                    // sizes
                    const sizesEl = document.getElementById('viewProductSizes'); sizesEl.innerHTML = '';
                    (parseSizes(prod.sizes) || []).forEach(s=> sizesEl.innerHTML += `<div class="text-sm">${s.size_id ? sizeNames[s.size_id] || s.size_id : ''} - ₱${parseFloat(s.price).toFixed(2)}</div>`);
                    showModal('productViewModal');
                } else if (editBtn) {
                    const id = editBtn.getAttribute('data-id');
                    const prod = products.find(p=>p.item_id==id);
                    if (!prod) return;
                    document.getElementById('edit_item_id').value = prod.item_id;
                    document.getElementById('edit_name').value = prod.item_name;
                    // populate category select
                    const sel = document.getElementById('edit_category'); sel.innerHTML = '<option value="">Select category</option>';
                    categories.forEach(c => sel.innerHTML += `<option value="${c.category_id}" ${c.category_id==prod.category_id? 'selected':''}>${c.category_name}</option>`);
                    document.getElementById('edit_description').value = prod.description || '';
                    document.getElementById('edit_price').value = prod.base_price;
                    document.getElementById('edit_image_path').value = prod.image_path || '';
                    document.getElementById('productEditModal').classList.remove('hidden');
                } else if (deleteBtn) {
                    const id = deleteBtn.getAttribute('data-id');
                    const prod = products.find(p=>p.item_id==id);
                    if (!prod) return;
                    document.getElementById('deleteProductName').textContent = prod.item_name;
                    document.getElementById('confirmDeleteProduct').setAttribute('data-id', id);
                    showModal('productDeleteModal');
                }
            });

            // Close/view modal controls
            document.getElementById('closeProductView').addEventListener('click', ()=> hideModal('productViewModal'));
            document.getElementById('closeProductView2').addEventListener('click', ()=> hideModal('productViewModal'));

            // Edit modal controls
            document.getElementById('closeProductEdit').addEventListener('click', ()=> hideModal('productEditModal'));
            document.getElementById('cancelEditProduct').addEventListener('click', ()=> hideModal('productEditModal'));

            // Edit form submit
            document.getElementById('editProductForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = document.getElementById('edit_item_id').value;
                const fileEl = document.getElementById('edit_image_file');
                let imagePath = document.getElementById('edit_image_path').value || null;
                if (fileEl && fileEl.files && fileEl.files.length) {
                    const file = fileEl.files[0];
                    const allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                    if (!allowedTypes.includes(file.type)) { showNotification('Only JPG/PNG/GIF/WEBP images allowed','error'); return; }
                    if (file.size > 5 * 1024 * 1024) { showNotification('Image too large (max 5MB)','error'); return; }
                    const fd = new FormData(); fd.append('image', file);
                    const res = await fetch('?action=uploadImage', { method: 'POST', body: fd });
                    const json = await res.json(); if (json.success) imagePath = json.path; else { showNotification(json.message||'Upload failed','error'); return; }
                }

                const payload = {
                    item_id: id,
                    name: document.getElementById('edit_name').value,
                    category_id: document.getElementById('edit_category').value,
                    description: document.getElementById('edit_description').value,
                    base_price: parseFloat(document.getElementById('edit_price').value) || 0,
                    image_path: imagePath
                };

                const resp = await fetch('?action=updateProduct', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
                const j = await resp.json();
                if (j.success) {
                    hideModal('productEditModal'); await loadData(); renderProducts(); showNotification('Product updated','success');
                } else showNotification(j.message || 'Update failed','error');
            });

            // Delete modal controls
            document.getElementById('closeProductDelete').addEventListener('click', ()=> hideModal('productDeleteModal'));
            document.getElementById('cancelDeleteProduct').addEventListener('click', ()=> hideModal('productDeleteModal'));
            document.getElementById('confirmDeleteProduct').addEventListener('click', async (e)=>{
                const id = e.target.getAttribute('data-id');
                const resp = await fetch('?action=deleteProduct', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ item_id: id }) });
                const j = await resp.json();
                if (j.success) { hideModal('productDeleteModal'); await loadData(); renderProducts(); showNotification('Deleted','success'); }
                else showNotification(j.message||'Delete failed','error');
            });
        })();
    </script>
</body>
</html>