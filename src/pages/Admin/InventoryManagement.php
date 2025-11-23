<?php
// pages/inventory/index.php
include '../../config/db.php';

// Ensure a PDO instance is available (db.php exposes getPDO())
$pdo = getPDO();

// Fetch statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$totalAddons = $pdo->query("SELECT COUNT(*) FROM addons")->fetchColumn();
$totalPackages = $pdo->query("SELECT COUNT(*) FROM cookies_packages")->fetchColumn();

// Check whether the `stock` column exists in the current database; the migrations add it
$hasStock = (bool) $pdo->query(
    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menu_items' AND COLUMN_NAME = 'stock'"
)->fetchColumn();

if ($hasStock) {
    $lowStock = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE stock < 10")->fetchColumn();
} else {
    // Migration not applied — avoid fatal SQL errors by defaulting to a sane value
    $lowStock = 0;
}

// Fetch all data
$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY item_id DESC")->fetchAll();

// If `stock` doesn't exist in the schema, provide a default stock value so the UI remains stable.
if (!$hasStock) {
    foreach ($menuItems as &$itm) {
        $itm['stock'] = 50; // default to 50 (matches migration intent)
    }
    unset($itm);
}
$itemSizes = $pdo->query("
    SELECT s.*, m.name as item_name 
    FROM item_sizes s 
    JOIN menu_items m ON s.item_id = m.item_id 
    ORDER BY s.size_id DESC
")->fetchAll();
$addons = $pdo->query("SELECT * FROM addons ORDER BY addon_id DESC")->fetchAll();
$packages = $pdo->query("SELECT * FROM cookies_packages ORDER BY package_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - shadcn/ui Style</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            from: '#6A0DAD',
                            to: '#A020F0'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-enter {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .modal-enter {
            animation: fadeIn 0.2s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .tab-active {
            border-bottom: 2px solid #A020F0;
            color: #6A0DAD;
        }
        
        .dark .tab-active {
            color: #A020F0;
        }
        
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .image-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-slate-800 shadow-2xl z-50 transform -translate-x-full transition-transform duration-300 sidebar-enter">
        <div class="p-6">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold gradient-text">Inventory</h2>
                <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="space-y-2">
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-chart-line text-purple-600"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-[#6A0DAD] to-[#A020F0] text-white shadow-lg">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-coffee"></i>
                    <span>Products</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add-ons</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-cookie-bite"></i>
                    <span>Cookies Packages</span>
                </a>
            </nav>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="min-h-screen">
        <!-- Top Navigation -->
        <nav class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <button onclick="toggleSidebar()" class="gradient-primary text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <i class="fas fa-bars mr-2"></i>Menu
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        
                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                            <i class="fas fa-moon dark:hidden text-xl"></i>
                            <i class="fas fa-sun hidden dark:inline text-xl"></i>
                        </button>
                        
                        <!-- User Profile -->
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center text-white font-semibold">
                                    A
                                </div>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-700 rounded-xl shadow-xl py-2">
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-slate-600">Profile</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-slate-600">Settings</a>
                                <hr class="my-2 border-gray-200 dark:border-slate-600">
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-slate-600 text-red-600">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold gradient-text mb-2">Inventory Management</h1>
                <p class="text-gray-600 dark:text-gray-400">Manage your products, add-ons, and packages</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center">
                            <i class="fas fa-coffee text-white text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm font-semibold">+12%</span>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Products</h3>
                    <p class="text-3xl font-bold"><?= $totalProducts ?></p>
                </div>
                
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-plus-circle text-white text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm font-semibold">+8%</span>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Add-ons</h3>
                    <p class="text-3xl font-bold"><?= $totalAddons ?></p>
                </div>
                
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                            <i class="fas fa-cookie-bite text-white text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm font-semibold">+5%</span>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Cookie Packages</h3>
                    <p class="text-3xl font-bold"><?= $totalPackages ?></p>
                </div>
                
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <span class="text-red-500 text-sm font-semibold">Alert</span>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Low Stock Items</h3>
                    <p class="text-3xl font-bold"><?= $lowStock ?></p>
                </div>
            </div>
            
            <!-- Tabs Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm">
                <!-- Tab Headers -->
                <div class="border-b border-gray-200 dark:border-slate-700 px-6">
                    <div class="flex space-x-8 overflow-x-auto">
                        <button onclick="switchTab('menuItems')" id="tab-menuItems" class="tab-active py-4 font-semibold whitespace-nowrap transition-colors">
                            Menu Items
                        </button>
                        <button onclick="switchTab('itemSizes')" id="tab-itemSizes" class="py-4 font-semibold text-gray-500 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 whitespace-nowrap transition-colors">
                            Item Sizes
                        </button>
                        <button onclick="switchTab('addons')" id="tab-addons" class="py-4 font-semibold text-gray-500 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 whitespace-nowrap transition-colors">
                            Add-ons
                        </button>
                        <button onclick="switchTab('packages')" id="tab-packages" class="py-4 font-semibold text-gray-500 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 whitespace-nowrap transition-colors">
                            Cookie Packages
                        </button>
                    </div>
                </div>
                
                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Menu Items Tab -->
                    <div id="content-menuItems" class="tab-content">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Menu Items</h2>
                            <button onclick="openModal('menuItemModal')" class="gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Add Product
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-slate-700">
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Image</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Name</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Category</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Base Price</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Stock</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($menuItems as $item): ?>
                                    <tr class="border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-4 px-4">
                                            <img src="<?= htmlspecialchars($item['image_path'] ?? '/uploads/products/placeholder.jpg') ?>" alt="Product" class="image-preview">
                                        </td>
                                        <td class="py-4 px-4 font-semibold"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="py-4 px-4">
                                            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-sm">
                                                Category <?= $item['category_id'] ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($item['price'], 2) ?></td>
                                        <td class="py-4 px-4">
                                            <span class="px-3 py-1 <?= $item['stock'] < 10 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' ?> rounded-full text-sm font-semibold">
                                                <?= $item['stock'] ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <button onclick='editMenuItem(<?= json_encode($item) ?>)' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteItem('menu_items', <?= $item['item_id'] ?>)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Item Sizes Tab -->
                    <div id="content-itemSizes" class="tab-content hidden">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Item Sizes</h2>
                            <button onclick="openModal('itemSizeModal')" class="gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Add Size
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-slate-700">
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Item Name</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Size</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Price</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($itemSizes as $size): ?>
                                    <tr class="border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-4 px-4 font-semibold"><?= htmlspecialchars($size['item_name']) ?></td>
                                        <td class="py-4 px-4">
                                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm">
                                                <?= htmlspecialchars($size['size_name']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($size['price'], 2) ?></td>
                                        <td class="py-4 px-4">
                                            <button onclick='editItemSize(<?= json_encode($size) ?>)' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteItem('item_sizes', <?= $size['size_id'] ?>)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Add-ons Tab -->
                    <div id="content-addons" class="tab-content hidden">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Add-ons</h2>
                            <button onclick="openModal('addonModal')" class="gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Add Add-on
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-slate-700">
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Name</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Price</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($addons as $addon): ?>
                                    <tr class="border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-4 px-4 font-semibold"><?= htmlspecialchars($addon['name']) ?></td>
                                        <td class="py-4 px-4 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($addon['price'], 2) ?></td>
                                        <td class="py-4 px-4">
                                            <button onclick='editAddon(<?= json_encode($addon) ?>)' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteItem('addons', <?= $addon['addon_id'] ?>)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Cookie Packages Tab -->
                    <div id="content-packages" class="tab-content hidden">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Cookie Packages</h2>
                            <button onclick="openModal('packageModal')" class="gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Add Package
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-slate-700">
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Package Name</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Description</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Quantity</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Price</th>
                                        <th class="text-left py-4 px-4 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($packages as $pkg): ?>
                                    <tr class="border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-4 px-4 font-semibold"><?= htmlspecialchars($pkg['package_name']) ?></td>
                                        <td class="py-4 px-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($pkg['description']) ?></td>
                                        <td class="py-4 px-4">
                                            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-sm font-semibold">
                                                <?= $pkg['quantity'] ?> pcs
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($pkg['price'], 2) ?></td>
                                        <td class="py-4 px-4">
                                            <button onclick='editPackage(<?= json_encode($pkg) ?>)' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteItem('cookies_packages', <?= $pkg['package_id'] ?>)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menu Item Modal -->
    <div id="menuItemModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full modal-enter overflow-y-auto max-h-[90vh]">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold gradient-text" id="menuItemModalTitle">Add Menu Item</h3>
                    <button onclick="closeModal('menuItemModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="menuItemForm" enctype="multipart/form-data">
                    <input type="hidden" id="menuItemId" name="item_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Product Name</label>
                            <input type="text" id="menuItemName" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Category ID</label>
                            <input type="number" id="menuItemCategory" name="category_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Description</label>
                            <textarea id="menuItemDescription" name="description" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Product Image</label>
                            <input type="file" id="menuItemImage" name="image" accept="image/*" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Base Price</label>
                                <input type="number" id="menuItemPrice" name="price" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold mb-2">Stock</label>
                                <input type="number" id="menuItemStock" name="stock" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="submit" class="flex-1 gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Product
                        </button>
                        <button type="button" onclick="closeModal('menuItemModal')" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Item Size Modal -->
    <div id="itemSizeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full modal-enter overflow-y-auto max-h-[90vh]">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold gradient-text" id="itemSizeModalTitle">Add Item Size</h3>
                    <button onclick="closeModal('itemSizeModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="itemSizeForm">
                    <input type="hidden" id="sizeId" name="size_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Select Item</label>
                            <select id="sizeItemId" name="item_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="">Choose an item...</option>
                                <?php foreach($menuItems as $item): ?>
                                <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Size Name</label>
                            <select id="sizeName" name="size_name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="">Choose size...</option>
                                <option value="12oz Hot">12oz Hot</option>
                                <option value="12oz Cold">12oz Cold</option>
                                <option value="16oz Hot">16oz Hot</option>
                                <option value="16oz Cold">16oz Cold</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Price</label>
                            <input type="number" id="sizePrice" name="price" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="submit" class="flex-1 gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Size
                        </button>
                        <button type="button" onclick="closeModal('itemSizeModal')" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add-on Modal -->
    <div id="addonModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full modal-enter">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold gradient-text" id="addonModalTitle">Add Add-on</h3>
                    <button onclick="closeModal('addonModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="addonForm">
                    <input type="hidden" id="addonId" name="addon_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Add-on Name</label>
                            <input type="text" id="addonName" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Price</label>
                            <input type="number" id="addonPrice" name="price" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="submit" class="flex-1 gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Add-on
                        </button>
                        <button type="button" onclick="closeModal('addonModal')" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Package Modal -->
    <div id="packageModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full modal-enter overflow-y-auto max-h-[90vh]">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold gradient-text" id="packageModalTitle">Add Cookie Package</h3>
                    <button onclick="closeModal('packageModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="packageForm">
                    <input type="hidden" id="packageId" name="package_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Package Name</label>
                            <input type="text" id="packageName" name="package_name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Description</label>
                            <textarea id="packageDescription" name="description" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Quantity</label>
                                <input type="number" id="packageQuantity" name="quantity" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold mb-2">Price</label>
                                <input type="number" id="packagePrice" name="price" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="submit" class="flex-1 gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Package
                        </button>
                        <button type="button" onclick="closeModal('packageModal')" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // User Menu Toggle
        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('hidden');
        }
        
        // Dark Mode Toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }
        
        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
        
        // Tab Switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(tab => {
                tab.classList.remove('tab-active');
                tab.classList.add('text-gray-500', 'dark:text-gray-400');
            });
            
            // Show selected tab content
            document.getElementById(`content-${tabName}`).classList.remove('hidden');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(`tab-${tabName}`);
            activeTab.classList.add('tab-active');
            activeTab.classList.remove('text-gray-500', 'dark:text-gray-400');
        }
        
        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            // Reset form
            const form = modal.querySelector('form');
            if (form) form.reset();
            
            // Reset hidden ID fields
            const idField = modal.querySelector('input[type="hidden"]');
            if (idField) idField.value = '';
        }
        
        // Edit Menu Item
        function editMenuItem(item) {
            document.getElementById('menuItemModalTitle').textContent = 'Edit Menu Item';
            document.getElementById('menuItemId').value = item.item_id;
            document.getElementById('menuItemName').value = item.name;
            document.getElementById('menuItemCategory').value = item.category_id;
            document.getElementById('menuItemDescription').value = item.description || '';
            document.getElementById('menuItemPrice').value = item.price;
            document.getElementById('menuItemStock').value = item.stock;
            openModal('menuItemModal');
        }
        
        // Edit Item Size
        function editItemSize(size) {
            document.getElementById('itemSizeModalTitle').textContent = 'Edit Item Size';
            document.getElementById('sizeId').value = size.size_id;
            document.getElementById('sizeItemId').value = size.item_id;
            document.getElementById('sizeName').value = size.size_name;
            document.getElementById('sizePrice').value = size.price;
            openModal('itemSizeModal');
        }
        
        // Edit Add-on
        function editAddon(addon) {
            document.getElementById('addonModalTitle').textContent = 'Edit Add-on';
            document.getElementById('addonId').value = addon.addon_id;
            document.getElementById('addonName').value = addon.name;
            document.getElementById('addonPrice').value = addon.price;
            openModal('addonModal');
        }
        
        // Edit Package
        function editPackage(pkg) {
            document.getElementById('packageModalTitle').textContent = 'Edit Cookie Package';
            document.getElementById('packageId').value = pkg.package_id;
            document.getElementById('packageName').value = pkg.package_name;
            document.getElementById('packageDescription').value = pkg.description || '';
            document.getElementById('packageQuantity').value = pkg.quantity;
            document.getElementById('packagePrice').value = pkg.price;
            openModal('packageModal');
        }
        
        // Delete Item
        async function deleteItem(table, id) {
            if (!confirm('Are you sure you want to delete this item?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('table', table);
            formData.append('id', id);
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error deleting item');
            }
        }
        
        // Menu Item Form Submit
        document.getElementById('menuItemForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', document.getElementById('menuItemId').value ? 'update' : 'create');
            formData.append('table', 'menu_items');
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving menu item');
            }
        });
        
        // Item Size Form Submit
        document.getElementById('itemSizeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', document.getElementById('sizeId').value ? 'update' : 'create');
            formData.append('table', 'item_sizes');
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving item size');
            }
        });
        
        // Add-on Form Submit
        document.getElementById('addonForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', document.getElementById('addonId').value ? 'update' : 'create');
            formData.append('table', 'addons');
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving add-on');
            }
        });
        
        // Package Form Submit
        document.getElementById('packageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', document.getElementById('packageId').value ? 'update' : 'create');
            formData.append('table', 'cookies_packages');
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving package');
            }
        });
        
        // Close modal on outside click
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });
        
        // Close user menu on outside click
        document.addEventListener('click', (e) => {
            const userMenu = document.getElementById('userMenu');
            if (!e.target.closest('[onclick="toggleUserMenu()"]') && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>