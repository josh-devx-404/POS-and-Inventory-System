<?php
// pages/users/index.php
include '../../config/db.php';

// Ensure a PDO instance is available for legacy files expecting `$pdo`.
if (!isset($pdo)) {
    $pdo = getPDO();
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

// Build query with filters
$whereClause = "WHERE 1=1";
if ($searchQuery) {
    $whereClause .= " AND (fullname LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
}
if ($roleFilter !== 'all') {
    $whereClause .= " AND role = '$roleFilter'";
}
if ($statusFilter !== 'all') {
    $whereClause .= " AND status = '$statusFilter'";
}

// Fetch users
$users = $pdo->query("SELECT * FROM users $whereClause ORDER BY created_at DESC")->fetchAll();

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Active'")->fetchColumn();
$adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'")->fetchColumn();
$cashierCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Cashier'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Café POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        violet: {
                            primary: '#6A0DAD',
                            secondary: '#A020F0'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        /* Light mode background: soft violet gradient (glass-like) */
        .gradient-bg-light {
            background: linear-gradient(135deg, #F8F4FF 0%, #FFF8F0 50%, #F4F0FF 100%);
            background-attachment: fixed;
        }

        /* Dark mode root background: true dark slate / neutral tones */
        .dark .gradient-bg-light {
            background-image: none;
            background-color: #0f0f10; /* fallback */
            background: linear-gradient(135deg, #0f0f10 0%, #1a1a1c 100%);
        }

        /* Primary gradients (light) and neutral (dark) */
        .gradient-primary {
            background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%);
            color: white;
        }

        .dark .gradient-primary {
            background: linear-gradient(135deg, #0f1724 0%, #111827 100%);
            color: #e6e6e6;
        }

        /* Shadcn-inspired glass surfaces */
        .glass-card {
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(8px) saturate(120%);
            border: 1px solid rgba(15,15,20,0.04);
        }

        .dark .glass-card {
            background: rgba(15,15,20,0.70); /* zink-900/70 like */
            backdrop-filter: blur(8px) saturate(120%);
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* Card rounding / shadow / transitions to match shadcn style */
        .card-rounded { border-radius: 1rem; } /* rounded-2xl */
        .card-shadow { box-shadow: 0 10px 30px rgba(2,6,23,0.12); }

        .card-hover { transition: all 300ms ease; }

        /* Theme transition helper (applied briefly on toggle) */
        .theme-transition, .theme-transition * {
            transition-property: background-color, color, border-color, box-shadow, fill, stroke, opacity;
            transition-duration: 300ms;
            transition-timing-function: ease;
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
            animation: fadeIn 0.2s ease-out, scaleIn 0.2s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.95); }
            to { transform: scale(1); }
        }
        
        .toast {
            animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-in 2.7s;
            animation-fill-mode: forwards;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        
        .dark .card-hover:hover {
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.3), 0 8px 10px -6px rgb(0 0 0 / 0.2);
        }
        
        .glow-effect {
            box-shadow: 0 0 20px rgba(160, 32, 240, 0.3);
        }
        
        .dark .glow-effect {
            box-shadow: 0 0 20px rgba(160, 32, 240, 0.5);
        }
        
        .floating-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 40;
            transition: all 0.3s ease;
        }
        
        .floating-button:hover {
            transform: scale(1.1) rotate(90deg);
        }
        
        @media (min-width: 768px) {
            .floating-button {
                display: none;
            }
        }
        
        .status-toggle {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        
        .status-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%);
        }
        
        input:checked + .slider:before {
            transform: translateX(24px);
        }
        
        /* Tablet card view */
        @media (max-width: 767px) {
            .table-view { display: none; }
            .card-view { display: block; }
        }
        
        @media (min-width: 768px) {
            .table-view { display: table; }
            .card-view { display: none; }
        }
    </style>
</head>
<body class="gradient-bg-light dark:bg-slate-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    
    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 glass-card shadow-2xl z-50 transform -translate-x-full transition-transform duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-violet-primary to-violet-secondary bg-clip-text text-transparent">Café POS</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Management System</p>
                </div>
                <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 lg:hidden">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="space-y-2">
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors group">
                    <i class="fas fa-chart-line text-gray-600 dark:text-gray-400 group-hover:text-violet-primary"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors group">
                    <i class="fas fa-coffee text-gray-600 dark:text-gray-400 group-hover:text-violet-primary"></i>
                    <span>Products</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors group">
                    <i class="fas fa-boxes text-gray-600 dark:text-gray-400 group-hover:text-violet-primary"></i>
                    <span>Inventory</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors group">
                    <i class="fas fa-shopping-cart text-gray-600 dark:text-gray-400 group-hover:text-violet-primary"></i>
                    <span>Orders</span>
                </a>
                <a href="../Admin/ReportManagement.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors group">
                    <i class="fas fa-chart-bar text-gray-600 dark:text-gray-400 group-hover:text-violet-primary"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl gradient-primary text-white shadow-lg glow-effect">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </nav>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="min-h-screen">
        <!-- Top Navigation -->
        <nav class="glass-card shadow-sm sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <button onclick="toggleSidebar()" class="gradient-primary text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 glow-effect">
                            <i class="fas fa-bars mr-2"></i>Menu
                        </button>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-violet-primary to-violet-secondary bg-clip-text text-transparent hidden sm:block">User Management</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        <button id="darkToggle" onclick="toggleDarkMode()" aria-pressed="false" title="Toggle dark mode" class="p-2 text-gray-600 dark:text-gray-300 hover:text-violet-primary dark:hover:text-violet-secondary transition-colors rounded-lg hover:bg-violet-100 dark:hover:bg-slate-700">
                            <i class="fas fa-moon dark:hidden text-xl" aria-hidden="true"></i>
                            <i class="fas fa-sun hidden dark:inline text-xl" aria-hidden="true"></i>
                        </button>
                        
                        <!-- User Profile -->
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 rounded-xl hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center text-white font-semibold glow-effect">
                                    A
                                </div>
                                <span class="hidden sm:inline font-semibold">Admin</span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 glass-card rounded-xl shadow-xl py-2">
                                <a href="#" class="block px-4 py-2 hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors rounded-lg mx-2">Profile</a>
                                <a href="#" class="block px-4 py-2 hover:bg-violet-100 dark:hover:bg-slate-700 transition-colors rounded-lg mx-2">Settings</a>
                                <hr class="my-2 border-gray-200 dark:border-slate-600">
                                <a href="#" class="block px-4 py-2 hover:bg-violet-100 dark:hover:bg-slate-700 text-red-600 transition-colors rounded-lg mx-2">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="glass-card card-rounded card-shadow rounded-2xl p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center glow-effect">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Users</h3>
                    <p id="statTotalUsers" class="text-3xl font-bold"><?= $totalUsers ?></p>
                </div>
                
                <div class="glass-card card-rounded card-shadow rounded-2xl p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                            <i class="fas fa-user-check text-white text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Active Users</h3>
                    <p id="statActiveUsers" class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $activeUsers ?></p>
                </div>
                
                <div class="glass-card card-rounded card-shadow rounded-2xl p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-user-shield text-white text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Admins</h3>
                    <p id="statAdminCount" class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $adminCount ?></p>
                </div>
                
                <div class="glass-card card-rounded card-shadow rounded-2xl p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                            <i class="fas fa-cash-register text-white text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-600 dark:text-gray-400 text-sm mb-1">Cashiers</h3>
                    <p id="statCashierCount" class="text-3xl font-bold text-orange-600 dark:text-orange-400"><?= $cashierCount ?></p>
                </div>
            </div>
            
            <!-- Filters and Add Button -->
            <div class="glass-card card-rounded card-shadow rounded-2xl p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form id="filterForm" method="GET" action="#" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4" onsubmit="return false;">
                        <div class="relative">
                            <input 
                                    type="text" 
                                    id="filterSearch"
                                    name="search" 
                                    value="<?= htmlspecialchars($searchQuery) ?>" 
                                    placeholder="Search users..." 
                                    class="w-full px-4 py-2 pr-10 rounded-2xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all"
                                >
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-violet-primary hover:text-violet-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <select id="filterRole" name="role" class="px-4 py-2 rounded-2xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all">
                            <option value="all">All Roles</option>
                            <option value="Admin" <?= $roleFilter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Cashier" <?= $roleFilter === 'Cashier' ? 'selected' : '' ?>>Cashier</option>
                        </select>
                        
                        <select id="filterStatus" name="status" class="px-4 py-2 rounded-2xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all">
                            <option value="all">All Status</option>
                            <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $statusFilter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </form>
                    
                    <div class="hidden md:flex items-center space-x-3">
                        <!-- Button examples (Primary / Outline / Ghost) -->
                        <div class="space-x-2">
                            <button class="gradient-primary px-5 py-2 rounded-2xl text-white transition-all duration-300 shadow-lg" type="button">Primary</button>
                            <button class="px-5 py-2 rounded-2xl border border-gray-200 dark:border-zinc-700 text-gray-800 dark:text-gray-100 bg-transparent transition-all duration-300 hover:bg-gray-50 dark:hover:bg-zinc-800" type="button">Outline</button>
                            <button class="px-5 py-2 rounded-2xl text-gray-700 dark:text-gray-200 bg-transparent transition-all duration-300 hover:bg-gray-100 dark:hover:bg-zinc-900" type="button">Ghost</button>
                        </div>

                        <button onclick="openModal('add')" class="hidden md:inline-block gradient-primary text-white px-6 py-2 rounded-2xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold glow-effect whitespace-nowrap">
                            <i class="fas fa-plus mr-2"></i>Add User
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Users Table (Desktop) -->
            <div id="usersContainerWrapper">
                <div id="usersLoading" class="hidden mb-4 flex items-center justify-center">
                    <div class="w-10 h-10 border-4 border-gray-200 rounded-full border-t-violet-primary animate-spin"></div>
                </div>
            <div id="usersContainer">
            <div class="glass-card card-rounded card-shadow rounded-2xl overflow-hidden table-view">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-violet-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Full Name</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Username</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Role</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Created At</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if(count($users) > 0): ?>
                                <?php foreach($users as $user): ?>
                                <tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full gradient-primary flex items-center justify-center text-white font-semibold">
                                                <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                            </div>
                                            <span class="font-semibold"><?= htmlspecialchars($user['fullname']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 <?= $user['role'] === 'Admin' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' ?> rounded-full text-sm font-semibold">
                                            <i class="fas <?= $user['role'] === 'Admin' ? 'fa-user-shield' : 'fa-cash-register' ?> mr-1"></i>
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 <?= $user['status'] === 'Active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' ?> rounded-full text-sm font-semibold">
                                            <i class="fas fa-circle text-xs mr-1"></i>
                                            <?= htmlspecialchars($user['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600 dark:text-gray-400"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick='editUser(<?= json_encode($user) ?>)' class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleStatus(<?= $user['user_id'] ?>, '<?= $user['status'] ?>')" class="p-2 <?= $user['status'] === 'Active' ? 'text-orange-600 hover:bg-orange-100 dark:hover:bg-orange-900/30' : 'text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30' ?> rounded-lg transition-colors" title="<?= $user['status'] === 'Active' ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas <?= $user['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                            </button>
                                            <button onclick="deleteUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['fullname']) ?>')" class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-users text-4xl mb-4"></i>
                                        <p class="text-lg font-semibold">No users found</p>
                                        <p class="text-sm">Try adjusting your filters</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            </div>
            </div>

            <!-- Pagination (AJAX) -->
            <div id="usersPagination" class="mt-4 flex items-center justify-center space-x-2"></div>

            <!-- Users Cards (Tablet/Mobile) -->
            <div id="usersCards" class="card-view space-y-4">
                <?php foreach($users as $user): ?>
                <div class="glass-card rounded-2xl p-6 card-hover">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-lg glow-effect">
                                <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($user['fullname']) ?></h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">@<?= htmlspecialchars($user['username']) ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1 <?= $user['status'] === 'Active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' ?> rounded-full text-xs font-semibold">
                            <?= htmlspecialchars($user['status']) ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Role</p>
                            <span class="px-3 py-1 <?= $user['role'] === 'Admin' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' ?> rounded-full text-sm font-semibold inline-block">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Joined</p>
                            <p class="text-sm font-semibold"><?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick='editUser(<?= json_encode($user) ?>)' class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all font-semibold">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </button>
                        <button onclick="toggleStatus(<?= $user['user_id'] ?>, '<?= $user['status'] ?>')" class="flex-1 px-4 py-2 <?= $user['status'] === 'Active' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' ?> text-white rounded-xl transition-all font-semibold">
                            <i class="fas <?= $user['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check' ?> mr-2"></i><?= $user['status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <button onclick="deleteUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['fullname']) ?>')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Add Button (Mobile/Tablet) -->
    <button onclick="openModal('add')" class="floating-button gradient-primary text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center glow-effect">
        <i class="fas fa-plus text-xl"></i>
    </button>
    
    <!-- Add/Edit User Modal -->
            <div id="userModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="glass-card card-rounded card-shadow rounded-2xl max-w-md w-full modal-enter">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-violet-primary to-violet-secondary bg-clip-text text-transparent" id="modalTitle">Add User</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="userForm" class="p-6">
                <input type="hidden" id="userId" name="user_id">
                <input type="hidden" id="modalAction" name="action" value="add">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Full Name</label>
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            required 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all"
                            placeholder="John Doe"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all"
                            placeholder="johndoe"
                        >
                    </div>
                    
                    <div id="passwordField">
                        <label class="block text-sm font-semibold mb-2">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-violet-primary transition-colors">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="passwordHint">Leave blank to keep current password</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Role</label>
                        <select 
                            id="role" 
                            name="role" 
                            required 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:ring-2 focus:ring-violet-primary focus:border-transparent transition-all"
                        >
                            <option value="">Select role...</option>
                            <option value="Admin">Admin</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-3">Status</label>
                        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700">
                            <span class="text-sm font-medium">User Active</span>
                            <label class="status-toggle">
                                <input type="checkbox" id="status" name="status" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="submit" class="flex-1 gradient-primary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold glow-effect">
                        <i class="fas fa-save mr-2"></i>Save User
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="glass-card card-rounded card-shadow rounded-2xl max-w-md w-full modal-enter">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Delete User?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Are you sure you want to delete <span id="deleteUserName" class="font-bold text-red-600"></span>? This action cannot be undone.</p>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="confirmDelete()" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl transition-all duration-300 hover:scale-105 font-semibold">
                        <i class="fas fa-trash mr-2"></i>Yes, Delete
                    </button>
                    <button onclick="closeDeleteModal()" class="flex-1 px-6 py-3 rounded-xl border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-all font-semibold">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let deleteUserId = null;
        
        // Theme helpers: persistent class-based dark mode with smooth transition
        function applyTheme(theme) {
            const root = document.documentElement;
            if (theme === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
            const btn = document.getElementById('darkToggle');
            if (btn) btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        }

        function toggleDarkMode() {
            const root = document.documentElement;
            const isDark = root.classList.contains('dark');

            // Add a temporary transition helper class so theme switch fades smoothly
            root.classList.add('theme-transition');
            window.setTimeout(() => root.classList.remove('theme-transition'), 300);

            const newTheme = isDark ? 'light' : 'dark';
            applyTheme(newTheme);
            try { localStorage.setItem('theme', newTheme); } catch (e) { /* ignore */ }
        }

        // Restore theme on load (persisted in localStorage) or fall back to system preference
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark' || saved === 'light') {
                applyTheme(saved);
            } else {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                applyTheme(prefersDark ? 'dark' : 'light');
            }
        });
        
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
        
        // Close user menu on outside click
        document.addEventListener('click', (e) => {
            const userMenu = document.getElementById('userMenu');
            if (!e.target.closest('[onclick="toggleUserMenu()"]') && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // Modal Functions
        function openModal(action, user = null) {
            const modal = document.getElementById('userModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalAction = document.getElementById('modalAction');
            const passwordField = document.getElementById('passwordField');
            const passwordInput = document.getElementById('password');
            const passwordHint = document.getElementById('passwordHint');
            
            // Reset form
            document.getElementById('userForm').reset();
            
            if (action === 'add') {
                modalTitle.textContent = 'Add User';
                modalAction.value = 'add';
                passwordInput.required = true;
                passwordHint.classList.add('hidden');
            } else {
                modalTitle.textContent = 'Edit User';
                modalAction.value = 'edit';
                passwordInput.required = false;
                passwordHint.classList.remove('hidden');
                
                // Fill form with user data
                document.getElementById('userId').value = user.user_id;
                document.getElementById('fullname').value = user.fullname;
                document.getElementById('username').value = user.username;
                document.getElementById('role').value = user.role;
                document.getElementById('status').checked = user.status === 'Active';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal() {
            const modal = document.getElementById('userModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        function editUser(user) {
            openModal('edit', user);
        }
        
        // Password Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
        
        // Form Submit
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.set('status', document.getElementById('status').checked ? 'Active' : 'Inactive');
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving user', 'error');
            }
        });
        
        // Toggle Status
        async function toggleStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const action = newStatus === 'Active' ? 'activate' : 'deactivate';
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('user_id', userId);
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating status', 'error');
            }
        }
        
        // Delete User
        function deleteUser(userId, fullname) {
            deleteUserId = userId;
            document.getElementById('deleteUserName').textContent = fullname;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            deleteUserId = null;
        }
        
        async function confirmDelete() {
            if (!deleteUserId) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', deleteUserId);
            
            try {
                const response = await fetch('actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeDeleteModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error deleting user', 'error');
            }
        }
        
        // Toast Notifications
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-orange-500',
                info: 'bg-blue-500'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const toast = document.createElement('div');
            toast.className = `toast ${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-3 min-w-[300px]`;
            toast.innerHTML = `
                <i class="fas ${icons[type]} text-xl"></i>
                <span class="font-semibold">${message}</span>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Close modal on outside click
        document.getElementById('userModal').addEventListener('click', (e) => {
            if (e.target.id === 'userModal') {
                closeModal();
            }
        });
        
        document.getElementById('deleteModal').addEventListener('click', (e) => {
            if (e.target.id === 'deleteModal') {
                closeDeleteModal();
            }
        });

        // AJAX Filters: dynamic fetch without full page reload
        (function(){
            const searchInput = document.getElementById('filterSearch');
            const roleSelect = document.getElementById('filterRole');
            const statusSelect = document.getElementById('filterStatus');
            const usersLoading = document.getElementById('usersLoading');
            const usersContainer = document.getElementById('usersContainer');
            const usersTableBody = document.getElementById('usersTableBody');
            const usersCards = document.getElementById('usersCards');

            let debounceTimer = null;

            function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            function showLoading(){ if(usersLoading) usersLoading.classList.remove('hidden'); if(usersContainer) usersContainer.style.opacity = '0.6'; }
            function hideLoading(){ if(usersLoading) usersLoading.classList.add('hidden'); if(usersContainer) usersContainer.style.opacity = '1'; }

            async function fetchUsers(page = 1){
                showLoading();
                const fd = new FormData();
                fd.append('search', searchInput ? searchInput.value.trim() : '');
                fd.append('role', roleSelect ? roleSelect.value : 'all');
                fd.append('status', statusSelect ? statusSelect.value : 'all');
                fd.append('page', page);

                try{
                    const res = await fetch('fetch_users.php', { method: 'POST', body: fd });
                    const json = await res.json();
                    if(json && json.success){
                        // update stats
                        const sTotal = document.getElementById('statTotalUsers'); if(sTotal) sTotal.textContent = json.stats.total;
                        const sActive = document.getElementById('statActiveUsers'); if(sActive) sActive.textContent = json.stats.active;
                        const sAdmin = document.getElementById('statAdminCount'); if(sAdmin) sAdmin.textContent = json.stats.admin;
                        const sCashier = document.getElementById('statCashierCount'); if(sCashier) sCashier.textContent = json.stats.cashier;
                        renderUsers(json.users || []);
                        renderPagination(json.page || 1, json.per_page || 20, json.total || 0);
                    } else {
                        renderUsers([]);
                        renderPagination(1, 20, 0);
                    }
                } catch(err){
                    console.error('Fetch users error', err);
                    renderUsers([]);
                } finally{
                    hideLoading();
                }
            }

            // Render pagination UI (prev/next and page numbers)
            const paginationContainer = document.getElementById('usersPagination');
            function renderPagination(currentPage, perPage, total){
                if(!paginationContainer) return;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                let html = '';

                function pageBtn(page, label, extraClass=''){
                    const disabled = page === currentPage ? 'opacity-50 pointer-events-none' : '';
                    return `<button data-page="${page}" class="px-3 py-1 rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-200 transition-all duration-200 ${extraClass} ${disabled}">${label}</button>`;
                }

                // Prev
                const prevPage = Math.max(1, currentPage - 1);
                html += pageBtn(prevPage, 'Prev');

                // page window
                const windowSize = 5;
                let start = Math.max(1, currentPage - 2);
                let end = Math.min(totalPages, start + windowSize - 1);
                if(end - start < windowSize - 1){ start = Math.max(1, end - windowSize + 1); }

                for(let p = start; p <= end; p++){
                    const activeClass = p === currentPage ? 'bg-violet-primary text-white' : '';
                    html += `<button data-page="${p}" class="px-3 py-1 rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-200 transition-all duration-200 ${activeClass}">${p}</button>`;
                }

                // Next
                const nextPage = Math.min(totalPages, currentPage + 1);
                html += pageBtn(nextPage, 'Next');

                paginationContainer.innerHTML = html;

                // bind events
                paginationContainer.querySelectorAll('button[data-page]').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const p = parseInt(btn.getAttribute('data-page')) || 1;
                        fetchUsers(p);
                    });
                });
            }

            function renderUsers(users){
                if(!usersContainer) return;
                usersContainer.style.transition = 'opacity 250ms ease';
                usersContainer.style.opacity = '0';

                setTimeout(() => {
                    // table rows
                    if(usersTableBody){
                        let rows = '';
                        if(users.length > 0){
                            users.forEach(u => {
                                const created = u.created_at ? new Date(u.created_at).toLocaleDateString() : '';
                                rows += `<tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-slate-700/30 transition-colors">`
                                    + `<td class="py-4 px-6"><div class="flex items-center space-x-3"><div class="w-10 h-10 rounded-full gradient-primary flex items-center justify-center text-white font-semibold">${esc(String(u.fullname).charAt(0).toUpperCase())}</div><span class="font-semibold">${esc(u.fullname)}</span></div></td>`
                                    + `<td class="py-4 px-6 text-gray-600 dark:text-gray-400">${esc(u.username)}</td>`
                                    + `<td class="py-4 px-6"><span class="px-3 py-1 ${u.role==='Admin' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'} rounded-full text-sm font-semibold"><i class="fas ${u.role==='Admin' ? 'fa-user-shield' : 'fa-cash-register'} mr-1"></i>${esc(u.role)}</span></td>`
                                    + `<td class="py-4 px-6"><span class="px-3 py-1 ${u.status==='Active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'} rounded-full text-sm font-semibold"><i class="fas fa-circle text-xs mr-1"></i>${esc(u.status)}</span></td>`
                                    + `<td class="py-4 px-6 text-sm text-gray-600 dark:text-gray-400">${esc(created)}</td>`
                                    + `<td class="py-4 px-6"><div class="flex space-x-2"><button onclick='editUser(${JSON.stringify(u)})' class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit"></i></button><button onclick="toggleStatus(${u.user_id}, '${u.status}')" class="p-2 ${u.status==='Active' ? 'text-orange-600 hover:bg-orange-100 dark:hover:bg-orange-900/30' : 'text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30'} rounded-lg transition-colors" title="${u.status==='Active' ? 'Deactivate' : 'Activate'}"><i class="fas ${u.status==='Active' ? 'fa-user-slash' : 'fa-user-check'}"></i></button><button onclick="deleteUser(${u.user_id}, '${esc(u.fullname)}')" class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Delete"><i class="fas fa-trash"></i></button></div></td>`
                                    + `</tr>`;
                            });
                        } else {
                            rows = `<tr><td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-users text-4xl mb-4"></i><p class="text-lg font-semibold">No users found</p><p class="text-sm">Try adjusting your filters</p></td></tr>`;
                        }
                        usersTableBody.innerHTML = rows;
                    }

                    // mobile cards
                    if(usersCards){
                        let cards = '';
                        if(users.length > 0){
                            users.forEach(u => {
                                const created = u.created_at ? new Date(u.created_at).toLocaleDateString() : '';
                                cards += `<div class="glass-card rounded-2xl p-6 card-hover"><div class="flex items-start justify-between mb-4"><div class="flex items-center space-x-3"><div class="w-12 h-12 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-lg glow-effect">${esc(String(u.fullname).charAt(0).toUpperCase())}</div><div><h3 class="font-bold text-lg">${esc(u.fullname)}</h3><p class="text-sm text-gray-600 dark:text-gray-400">@${esc(u.username)}</p></div></div><span class="px-3 py-1 ${u.status==='Active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'} rounded-full text-xs font-semibold">${esc(u.status)}</span></div><div class="grid grid-cols-2 gap-4 mb-4"><div><p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Role</p><span class="px-3 py-1 ${u.role==='Admin' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'} rounded-full text-sm font-semibold inline-block">${esc(u.role)}</span></div><div><p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Joined</p><p class="text-sm font-semibold">${esc(created)}</p></div></div><div class="flex space-x-2"><button onclick='editUser(${JSON.stringify(u)})' class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all font-semibold"><i class="fas fa-edit mr-2"></i>Edit</button><button onclick="toggleStatus(${u.user_id}, '${u.status}')" class="flex-1 px-4 py-2 ${u.status==='Active' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700'} text-white rounded-xl transition-all font-semibold"><i class="fas ${u.status==='Active' ? 'fa-user-slash' : 'fa-user-check'} mr-2"></i>${u.status==='Active' ? 'Deactivate' : 'Activate'}</button><button onclick="deleteUser(${u.user_id}, '${esc(u.fullname)}')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all"><i class="fas fa-trash"></i></button></div></div>`;
                            });
                        } else {
                            cards = '<div class="py-12 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-users text-4xl mb-4"></i><p class="text-lg font-semibold">No users found</p><p class="text-sm">Try adjusting your filters</p></div>';
                        }
                        usersCards.innerHTML = cards;
                    }

                    usersContainer.style.opacity = '1';
                }, 220);
            }

            // attach listeners
            if(searchInput){
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => fetchUsers(1), 300);
                });
            }
            if(roleSelect){ roleSelect.addEventListener('change', () => fetchUsers(1)); }
            if(statusSelect){ statusSelect.addEventListener('change', () => fetchUsers(1)); }

            // Expose fetchUsers globally for manual refresh if needed
            window.fetchUsers = fetchUsers;
        })();
    </script>
</body>
</html>