<?php
require_once __DIR__ . '/../../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café POS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../../styles/global.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .light-mode {
            background: linear-gradient(135deg, #f5f1e8 0%, #faf8f3 100%);
            color: #2d2d2d;
        }
        
        .dark-mode {
            background: linear-gradient(135deg, #1a1412 0%, #2d2520 100%);
            color: #e8e8e8;
        }
        
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .light-mode .sidebar {
            background: linear-gradient(180deg, #3d2817 0%, #5c4033 100%);
        }
        
        .dark-mode .sidebar {
            background: linear-gradient(180deg, #0f0a08 0%, #1a1412 100%);
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .light-mode .card {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .dark-mode .card {
            background: rgba(42, 33, 28, 0.6);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .nav-item {
            transition: all 0.2s ease;
        }
        
        .nav-item:hover {
            transform: translateX(8px);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #d4a574;
        }
        
        .toggle-switch {
            width: 60px;
            height: 30px;
            background: #4a4a4a;
            border-radius: 15px;
            position: relative;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle-switch.active {
            background: #d4a574;
        }
        
        .toggle-slider {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 3px;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .toggle-switch.active .toggle-slider {
            transform: translateX(30px);
        }
        
        .stat-card {
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .light-mode .table-row:nth-child(even) {
            background: rgba(245, 241, 232, 0.3);
        }
        
        .dark-mode .table-row:nth-child(even) {
            background: rgba(61, 40, 23, 0.2);
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4f4dd;
            color: #2d6a3e;
        }
        
        .dark-mode .badge-success {
            background: #2d6a3e;
            color: #d4f4dd;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .dark-mode .badge-pending {
            background: #856404;
            color: #fff3cd;
        }
    </style>
</head>
<body class="light-mode">
    
    <div class="flex min-h-screen">
        
        <!-- Sidebar -->
        <aside class="sidebar w-64 p-6 text-white flex flex-col">
            
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="white" viewBox="0 0 24 24">
                        <path d="M12 3C8.5 3 6 5.5 6 9c0 2.5 1.5 4 3 6 1.5 2 2 3 2 5h2c0-2 .5-3 2-5 1.5-2 3-3.5 3-6 0-3.5-2.5-6-6-6zm0 2c.8 0 1.5.3 2 .8.5.5.8 1.2.8 2-.3-.2-.7-.3-1-.3-1.1 0-2 .9-2 2s.9 2 2 2c.3 0 .7-.1 1-.3-.3 1.5-1.5 2.8-2.8 2.8S9.3 11 9 9.5c-.3.2-.7.3-1 .3-1.1 0-2-.9-2-2s.9-2 2-2c.3 0 .7.1 1 .3C9 4.8 10.3 3.5 12 3.5z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Café POS</h1>
                    <p class="text-xs text-gray-300">Admin Panel</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1">
                <a href="#" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Products</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Categories</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Inventory</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Purchases</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Sales</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>User Management</span>
                </a>
                
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Reports</span>
                </a>
            </nav>
            
            <!-- User Profile -->
            <div class="mt-6 p-4 rounded-lg bg-white bg-opacity-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center font-bold">
                        A
                    </div>
                    <div>
                        <p class="font-semibold">Admin</p>
                        <p class="text-xs text-gray-300">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            
            <!-- Top Bar -->
            <header class="card sticky top-0 z-10 p-6 mb-6 rounded-none flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Dashboard</h2>
                    <p class="text-sm opacity-70">Welcome back, Admin</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                        </svg>
                        <div class="toggle-switch" onclick="toggleTheme()">
                            <div class="toggle-slider"></div>
                        </div>
                        <svg class="w-5 h-5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </div>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 rounded-lg hover:bg-black hover:bg-opacity-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </header>
            
            <div class="p-6">
                
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    
                    <!-- Total Sales -->
                    <div class="card stat-card p-6 rounded-2xl">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">+12.5%</span>
                        </div>
                        <h3 class="text-sm font-medium opacity-70 mb-1">Total Sales</h3>
                        <p class="text-3xl font-bold">₱45,890</p>
                        <p class="text-xs opacity-60 mt-2">vs last month</p>
                    </div>
                    
                    <!-- Total Orders -->
                    <div class="card stat-card p-6 rounded-2xl">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700">+8.2%</span>
                        </div>
                        <h3 class="text-sm font-medium opacity-70 mb-1">Total Orders</h3>
                        <p class="text-3xl font-bold">1,248</p>
                        <p class="text-xs opacity-60 mt-2">vs last month</p>
                    </div>
                    
                    <!-- Total Products -->
                    <div class="card stat-card p-6 rounded-2xl">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-medium opacity-70 mb-1">Total Products</h3>
                        <p class="text-3xl font-bold">87</p>
                        <p class="text-xs opacity-60 mt-2">Active items</p>
                    </div>
                    
                    <!-- Low Stock -->
                    <div class="card stat-card p-6 rounded-2xl">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-orange-100 text-orange-700">Alert</span>
                        </div>
                        <h3 class="text-sm font-medium opacity-70 mb-1">Low Stock Items</h3>
                        <p class="text-3xl font-bold">12</p>
                        <p class="text-xs opacity-60 mt-2">Need restock</p>
                    </div>
                    
                </div>
                
                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Daily Sales Chart -->
                    <div class="card p-6 rounded-2xl">
                        <h3 class="text-lg font-semibold mb-4">Daily Sales</h3>
                        <canvas id="salesChart"></canvas>
                    </div>
                    
                    <!-- Best Selling Products -->
                    <div class="card p-6 rounded-2xl">
                        <h3 class="text-lg font-semibold mb-4">Best Selling Products</h3>
                        <canvas id="productsChart"></canvas>
                    </div>
                    
                </div>
                
                <!-- Recent Orders Table -->
                <div class="card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Recent Orders</h3>
                        <button class="text-sm px-4 py-2 rounded-lg bg-gradient-to-r from-amber-400 to-orange-600 text-white font-medium hover:shadow-lg transition-all">
                            View All
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-opacity-20">
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Order ID</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Customer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Items</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Total</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Payment</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm opacity-70">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-row border-b border-opacity-10">
                                    <td class="py-4 px-4 font-medium">#ORD-001</td>
                                    <td class="py-4 px-4">John Doe</td>
                                    <td class="py-4 px-4">Cappuccino, Croissant</td>
                                    <td class="py-4 px-4 font-semibold">₱245</td>
                                    <td class="py-4 px-4">Cash</td>
                                    <td class="py-4 px-4">Nov 21, 2025</td>
                                    <td class="py-4 px-4"><span class="badge badge-success">Completed</span></td>
                                </tr>
                                <tr class="table-row border-b border-opacity-10">
                                    <td class="py-4 px-4 font-medium">#ORD-002</td>
                                    <td class="py-4 px-4">Jane Smith</td>
                                    <td class="py-4 px-4">Americano, Muffin</td>
                                    <td class="py-4 px-4 font-semibold">₱185</td>
                                    <td class="py-4 px-4">GCash</td>
                                    <td class="py-4 px-4">Nov 21, 2025</td>
                                    <td class="py-4 px-4"><span class="badge badge-success">Completed</span></td>
                                </tr>
                                <tr class="table-row border-b border-opacity-10">
                                    <td class="py-4 px-4 font-medium">#ORD-003</td>
                                    <td class="py-4 px-4">Mike Johnson</td>
                                    <td class="py-4 px-4">Latte, Sandwich</td>
                                    <td class="py-4 px-4 font-semibold">₱320</td>
                                    <td class="py-4 px-4">Card</td>
                                    <td class="py-4 px-4">Nov 21, 2025</td>
                                    <td class="py-4 px-4"><span class="badge badge-pending">Pending</span></td>
                                </tr>
                                <tr class="table-row border-b border-opacity-10">
                                    <td class="py-4 px-4 font-medium">#ORD-004</td>
                                    <td class="py-4 px-4">Sarah Wilson</td>
                                    <td class="py-4 px-4">Espresso, Cake</td>
                                    <td class="py-4 px-4 font-semibold">₱290</td>
                                    <td class="py-4 px-4">Cash</td>
                                    <td class="py-4 px-4">Nov 21, 2025</td>
                                    <td class="py-4 px-4"><span class="badge badge-success">Completed</span></td>
                                </tr>
                                <tr class="table-row">
                                    <td class="py-4 px-4 font-medium">#ORD-005</td>
                                    <td class="py-4 px-4">Tom Brown</td>
                                    <td class="py-4 px-4">Mocha, Cookie</td>
                                    <td class="py-4 px-4 font-semibold">₱210</td>
                                    <td class="py-4 px-4">GCash</td>
                                    <td class="py-4 px-4">Nov 21, 2025</td>
                                    <td class="py-4 px-4"><span class="badge badge-success">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
            
    </main>
    
    <script src="../../js/adminDashboard.js"></script>