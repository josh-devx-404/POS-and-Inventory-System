<?php
require_once __DIR__ . '/../../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Light Mode */
        body.light-mode {
            background: linear-gradient(135deg, #9F7AEA 0%, #6B46C1 100%);
            color: #3d2a54;
        }
        
        .light-mode .sidebar {
            background: linear-gradient(180deg, #7C3AED 0%, #6B46C1 100%);
        }
        
        .light-mode .card {
            background: white;
            box-shadow: 0 4px 20px rgba(107, 70, 193, 0.15);
        }
        
        .light-mode .card:hover {
            box-shadow: 0 8px 30px rgba(107, 70, 193, 0.25);
        }
        
        .light-mode .topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(107, 70, 193, 0.1);
        }
        
        .light-mode .input {
            background: white;
            border: 2px solid #DDD6FE;
            color: #3d2a54;
        }
        
        .light-mode .btn-primary {
            background: linear-gradient(135deg, #9F7AEA 0%, #7C3AED 100%);
            color: white;
        }
        
        .light-mode .cart-section {
            background: white;
        }
        
        .light-mode .modal {
            background: white;
        }
        
        .light-mode .sidebar-overlay {
            background: rgba(107, 70, 193, 0.3);
        }
        
        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(135deg, #1F1F1F 0%, #2A2A2A 100%);
            color: #F5F5F5;
        }
        
        .dark-mode .sidebar {
            background: linear-gradient(180deg, #1A1A1A 0%, #0F0F0F 100%);
        }
        
        .dark-mode .card {
            background: #2E2E2E;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .dark-mode .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }
        
        .dark-mode .topbar {
            background: rgba(46, 46, 46, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .dark-mode .input {
            background: #2E2E2E;
            border: 2px solid #3E3E3E;
            color: #F5F5F5;
        }
        
        .dark-mode .btn-primary {
            background: linear-gradient(135deg, #6B5A8E 0%, #4A3F66 100%);
            color: #F5F5F5;
        }
        
        .dark-mode .cart-section {
            background: #2E2E2E;
        }
        
        .dark-mode .modal {
            background: #2E2E2E;
        }
        
        .dark-mode .sidebar-overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        
        /* Sidebar Animations */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 40;
            overflow-y: auto;
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 30;
        }
        
        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        
        /* Animations */
        .card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .card:hover {
            transform: translateY(-5px);
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
            border-left: 4px solid #9F7AEA;
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
            background: #9F7AEA;
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
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 50;
            animation: fadeIn 0.2s ease;
        }
        
        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal {
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .size-option, .addon-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .size-option:hover, .addon-option:hover {
            transform: scale(1.02);
        }
        
        .size-option.selected {
            border-color: #9F7AEA !important;
            background: rgba(159, 122, 234, 0.1);
        }
        
        .dark-mode .size-option.selected {
            background: rgba(107, 90, 142, 0.2);
        }
        
        .sugar-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .sugar-option:hover {
            transform: scale(1.05);
        }
        
        .sugar-option.selected {
            border-color: #9F7AEA !important;
            background: rgba(159, 122, 234, 0.1);
        }
        
        .dark-mode .sugar-option.selected {
            background: rgba(107, 90, 142, 0.2);
        }
        
        .cart-item {
            transition: all 0.2s ease;
        }
        
        .cart-item:hover {
            background: rgba(159, 122, 234, 0.05);
        }
        
        .cart-section {
            transition: transform 0.2s ease;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            
            .cart-section {
                width: 100% !important;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .cart-section {
                width: 320px !important;
            }
        }
    </style>
</head>
<body class="light-mode">
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar text-white p-6" id="sidebar">
        
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="white" viewBox="0 0 24 24">
                    <path d="M12 3C8.5 3 6 5.5 6 9c0 2.5 1.5 4 3 6 1.5 2 2 3 2 5h2c0-2 .5-3 2-5 1.5-2 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold">Café POS</h1>
                <p class="text-xs text-gray-300">Cashier Panel</p>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1">
            <a href="#" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Orders</span>
            </a>
            
            <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Products</span>
            </a>
            
            <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Add-ons</span>
            </a>
            
            <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Customers</span>
            </a>
        </nav>
        
        <!-- User Profile -->
        <div class="mt-6 p-4 rounded-lg bg-white bg-opacity-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center font-bold">
                    C
                </div>
                <div>
                    <p class="font-semibold">Cashier</p>
                    <p class="text-xs text-gray-300">Online</p>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="flex flex-col min-h-screen">
        
        <!-- Top Bar -->
        <header class="topbar sticky top-0 z-20 p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Sidebar Toggle Button -->
                <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-black hover:bg-opacity-5 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <div>
                    <h2 class="text-xl md:text-2xl font-bold">Point of Sale</h2>
                    <p class="text-xs md:text-sm opacity-70">Select products to create order</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 md:gap-4">
                <!-- Theme Toggle -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 md:w-5 md:h-5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                    </svg>
                    <div class="toggle-switch active" onclick="toggleTheme()">
                        <div class="toggle-slider"></div>
                    </div>
                    <svg class="w-4 h-4 md:w-5 md:h-5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </div>
                
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg hover:bg-black hover:bg-opacity-5">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </header>
        
        <!-- Content Area -->
        <div class="flex flex-1 overflow-hidden flex-col lg:flex-row">
            
            <!-- Products Section -->
            <div class="flex-1 p-4 md:p-6 overflow-y-auto">
                
                <!-- Search Bar -->
                <div class="mb-6">
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Search products..." 
                        class="input px-4 py-3 rounded-xl w-full"
                        oninput="searchProducts()"
                    >
                </div>
                
                <div class="mb-6" id="coffeeSection">
                    <h3 class="text-lg font-semibold mb-4">Coffee</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Espresso" data-category="coffee" onclick="openProductModal('Espresso', 120)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Espresso</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱120</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Cappuccino" data-category="coffee" onclick="openProductModal('Cappuccino', 140)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Cappuccino</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱140</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Latte" data-category="coffee" onclick="openProductModal('Latte', 150)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Latte</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱150</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Americano" data-category="coffee" onclick="openProductModal('Americano', 110)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Americano</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱110</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Mocha" data-category="coffee" onclick="openProductModal('Mocha', 160)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Mocha</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱160</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Macchiato" data-category="coffee" onclick="openProductModal('Macchiato', 135)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 004 4h6a4 4 0 004-4v-1h2a2 2 0 002-2V5c0-1.11-.89-2-2-2M4 3h14v10a2 2 0 01-2 2H6a2 2 0 01-2-2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Macchiato</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱135</p>
                        </div>
                        
                    </div>
                </div>
                
                <div class="mb-6" id="pastriesSection">
                    <h3 class="text-lg font-semibold mb-4">Pastries</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Croissant" data-category="pastries" onclick="openProductModal('Croissant', 85, false)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5.5,2C5.5,2.4 5.4,2.7 5.2,3C5,3.3 4.8,3.5 4.5,3.6C3.9,3.9 3.4,4.3 3.1,4.9C2.8,5.5 2.6,6.2 2.6,6.9C2.6,7.6 2.8,8.3 3.1,8.9L6.9,16.4C7.2,17 7.7,17.4 8.2,17.7C8.8,18 9.4,18.1 10,18.1H14C14.6,18.1 15.2,18 15.8,17.7C16.3,17.4 16.8,17 17.1,16.4L20.9,8.9C21.2,8.3 21.4,7.6 21.4,6.9C21.4,6.2 21.2,5.5 20.9,4.9C20.6,4.3 20.1,3.9 19.5,3.6C19.2,3.5 19,3.3 18.8,3C18.6,2.7 18.5,2.4 18.5,2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Croissant</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱85</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Muffin" data-category="pastries" onclick="openProductModal('Muffin', 75, false)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16,11V3H8V11H6L4,21H20L18,11M14,5V11H10V5"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Muffin</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱75</p>
                        </div>
                        
                        <div class="card p-4 md:p-6 rounded-2xl text-center product-card" data-name="Danish" data-category="pastries" onclick="openProductModal('Danish', 95, false)">
                            <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4Z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-sm md:text-base">Danish</h4>
                            <p class="text-xs md:text-sm opacity-70 mt-1">₱95</p>
                        </div>
                        
                    </div>
                </div>
                
                <!-- No Results Message -->
                <div id="noResults" class="text-center py-12 hidden">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-lg font-semibold opacity-70">No products found</p>
                    <p class="text-sm opacity-50 mt-2">Try searching with different keywords</p>
                </div>
                
            </div>
            
            <!-- Cart Section -->
            <div class="cart-section w-full lg:w-96 p-4 md:p-6 flex flex-col border-t lg:border-t-0 lg:border-l" style="border-color: rgba(128, 128, 128, 0.2);">
                <h3 class="text-xl font-bold mb-4">Current Order</h3>
                
                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto mb-4" id="cartItems">
                    <div class="text-center py-12 opacity-50">
                        <svg class="w-16 h-16 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>No items added</p>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="border-t pt-4 mb-4" style="border-color: rgba(128, 128, 128, 0.2);">
                    <div class="flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span class="font-semibold" id="subtotal">₱0.00</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Tax (12%):</span>
                        <span class="font-semibold" id="tax">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t" style="border-color: rgba(128, 128, 128, 0.2);">
                        <span>Total:</span>
                        <span id="total">₱0.00</span>
                    </div>
                </div>
                
                <!-- Checkout Buttons -->
                <div class="space-y-2">
                    <button class="btn-primary w-full py-3 rounded-xl font-semibold hover:shadow-lg transition-all" onclick="checkout('Cash')">
                        💵 Pay with Cash
                    </button>
                    <button class="btn-primary w-full py-3 rounded-xl font-semibold hover:shadow-lg transition-all" onclick="checkout('GCash')">
                        📱 Pay with GCash
                    </button>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Product Modal -->
    <div class="modal-overlay" id="productModal">
        <div class="modal p-6 md:p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-xl md:text-2xl font-bold" id="modalProductName">Product Name</h3>
                    <p class="text-sm opacity-70 mt-1">Customize your order</p>
                </div>
                <button onclick="closeProductModal()" class="p-2 rounded-lg hover:bg-black hover:bg-opacity-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Size Selection -->
            <div class="mb-6" id="sizeSection">
                <h4 class="font-semibold mb-3">Select Size</h4>
                <div class="grid grid-cols-3 gap-3">
                    <div class="size-option card p-3 md:p-4 rounded-xl text-center border-2" onclick="selectSize('Small', 0)">
                        <p class="font-semibold text-sm md:text-base">Small</p>
                        <p class="text-xs opacity-70">+₱0</p>
                    </div>
                    <div class="size-option card p-3 md:p-4 rounded-xl text-center border-2" onclick="selectSize('Medium', 20)">
                        <p class="font-semibold text-sm md:text-base">Medium</p>
                        <p class="text-xs opacity-70">+₱20</p>
                    </div>
                    <div class="size-option card p-3 md:p-4 rounded-xl text-center border-2" onclick="selectSize('Large', 40)">
                        <p class="font-semibold text-sm md:text-base">Large</p>
                        <p class="text-xs opacity-70">+₱40</p>
                    </div>
                </div>
            </div>
            
            <!-- Sugar Level Selection -->
            <div class="mb-6" id="sugarSection">
                <h4 class="font-semibold mb-3">Sugar Level</h4>
                <div class="grid grid-cols-5 gap-2">
                    <div class="sugar-option card p-2 md:p-3 rounded-xl text-center border-2" onclick="selectSugar('0%')">
                        <p class="font-semibold text-xs md:text-sm">0%</p>
                    </div>
                    <div class="sugar-option card p-2 md:p-3 rounded-xl text-center border-2" onclick="selectSugar('25%')">
                        <p class="font-semibold text-xs md:text-sm">25%</p>
                    </div>
                    <div class="sugar-option card p-2 md:p-3 rounded-xl text-center border-2 selected" onclick="selectSugar('50%')">
                        <p class="font-semibold text-xs md:text-sm">50%</p>
                    </div>
                    <div class="sugar-option card p-2 md:p-3 rounded-xl text-center border-2" onclick="selectSugar('75%')">
                        <p class="font-semibold text-xs md:text-sm">75%</p>
                    </div>
                    <div class="sugar-option card p-2 md:p-3 rounded-xl text-center border-2" onclick="selectSugar('100%')">
                        <p class="font-semibold text-xs md:text-sm">100%</p>
                    </div>
                </div>
            </div>
            
            <!-- Add-ons Selection -->
            <div class="mb-6">
                <h4 class="font-semibold mb-3">Add-ons (Optional)</h4>
                <div class="space-y-2">
                    <label class="addon-option card p-3 md:p-4 rounded-xl flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 md:w-5 md:h-5 accent-purple-600" onchange="toggleAddon('Extra Shot', 30, this)">
                            <span class="text-sm md:text-base">Extra Shot</span>
                        </div>
                        <span class="text-xs md:text-sm opacity-70">+₱30</span>
                    </label>
                    
                    <label class="addon-option card p-3 md:p-4 rounded-xl flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 md:w-5 md:h-5 accent-purple-600" onchange="toggleAddon('Vanilla Syrup', 25, this)">
                            <span class="text-sm md:text-base">Vanilla Syrup</span>
                        </div>
                        <span class="text-xs md:text-sm opacity-70">+₱25</span>
                    </label>
                    
                    <label class="addon-option card p-3 md:p-4 rounded-xl flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 md:w-5 md:h-5 accent-purple-600" onchange="toggleAddon('Caramel Syrup', 25, this)">
                            <span class="text-sm md:text-base">Caramel Syrup</span>
                        </div>
                        <span class="text-xs md:text-sm opacity-70">+₱25</span>
                    </label>
                    
                    <label class="addon-option card p-3 md:p-4 rounded-xl flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 md:w-5 md:h-5 accent-purple-600" onchange="toggleAddon('Whipped Cream', 20, this)">
                            <span class="text-sm md:text-base">Whipped Cream</span>
                        </div>
                        <span class="text-xs md:text-sm opacity-70">+₱20</span>
                    </label>
                    
                    <label class="addon-option card p-3 md:p-4 rounded-xl flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 md:w-5 md:h-5 accent-purple-600" onchange="toggleAddon('Oat Milk', 35, this)">
                            <span class="text-sm md:text-base">Oat Milk</span>
                        </div>
                        <span class="text-xs md:text-sm opacity-70">+₱35</span>
                    </label>
                </div>
            </div>
            
            <!-- Price Summary -->
            <div class="card p-4 rounded-xl mb-6">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-semibold">Quantity:</span>
                    <div class="flex items-center gap-3">
                        <button onclick="updateModalQuantity(-1)" class="w-8 h-8 rounded-lg btn-primary flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="w-12 text-center font-bold text-xl" id="modalQuantity">1</span>
                        <button onclick="updateModalQuantity(1)" class="w-8 h-8 rounded-lg btn-primary flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex justify-between items-center pt-3 border-t" style="border-color: rgba(128, 128, 128, 0.2);">
                    <span class="font-semibold">Total Price:</span>
                    <span class="text-xl md:text-2xl font-bold" id="modalTotalPrice">₱0</span>
                </div>
            </div>
            
            <!-- Add to Cart Button -->
            <button class="btn-primary w-full py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg hover:shadow-lg transition-all" onclick="addToCart()">
                Add to Cart
            </button>
        </div>
    </div>
    
    <script>
        // State Management
        let currentTheme = 'light';
        let cart = [];
        let modalState = {
            productName: '',
            basePrice: 0,
            selectedSize: { name: 'Small', price: 0 },
            selectedSugar: '50%',
            selectedAddons: [],
            quantity: 1,
            hasSize: true
        };
        
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        // Theme Toggle
        function toggleTheme() {
            const body = document.body;
            const toggle = document.querySelector('.toggle-switch');
            
            if (currentTheme === 'light') {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                toggle.classList.remove('active');
                currentTheme = 'dark';
            } else {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                toggle.classList.add('active');
                currentTheme = 'light';
            }
        }
        
        // Search Products
        function searchProducts() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
            const productCards = document.querySelectorAll('.product-card');
            const coffeeSection = document.getElementById('coffeeSection');
            const pastriesSection = document.getElementById('pastriesSection');
            const noResults = document.getElementById('noResults');
            
            let visibleCount = 0;
            let coffeeSectionVisible = false;
            let pastriesSectionVisible = false;
            
            productCards.forEach(card => {
                const productName = card.getAttribute('data-name').toLowerCase();
                
                if (searchInput === '' || productName.includes(searchInput)) {
                    card.style.display = 'block';
                    visibleCount++;
                    
                    // Check which section this card belongs to
                    const category = card.getAttribute('data-category');
                    if (category === 'coffee') coffeeSectionVisible = true;
                    if (category === 'pastries') pastriesSectionVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide sections based on visibility
            coffeeSection.style.display = coffeeSectionVisible ? 'block' : 'none';
            pastriesSection.style.display = pastriesSectionVisible ? 'block' : 'none';
            
            // Show/hide no results message
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
        
        // Product Modal Functions
        function openProductModal(productName, basePrice, hasSize = true) {
            modalState = {
                productName: productName,
                basePrice: basePrice,
                selectedSize: { name: 'Small', price: 0 },
                selectedSugar: '50%',
                selectedAddons: [],
                quantity: 1,
                hasSize: hasSize
            };
            
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalQuantity').textContent = '1';
            document.getElementById('productModal').classList.add('active');
            
            // Show/hide size section
            const sizeSection = document.getElementById('sizeSection');
            const sugarSection = document.getElementById('sugarSection');
            
            if (hasSize) {
                sizeSection.style.display = 'block';
                sugarSection.style.display = 'block';
                
                // Select small size by default
                document.querySelectorAll('.size-option').forEach((opt, idx) => {
                    opt.classList.remove('selected');
                    if (idx === 0) opt.classList.add('selected');
                });
                
                // Select 50% sugar by default
                document.querySelectorAll('.sugar-option').forEach((opt, idx) => {
                    opt.classList.remove('selected');
                    if (idx === 2) opt.classList.add('selected');
                });
            } else {
                sizeSection.style.display = 'none';
                sugarSection.style.display = 'none';
            }
            
            // Reset addons
            document.querySelectorAll('.addon-option input[type="checkbox"]').forEach(cb => cb.checked = false);
            
            updateModalPrice();
        }
        
        function closeProductModal() {
            document.getElementById('productModal').classList.remove('active');
        }
        
        function selectSize(sizeName, sizePrice) {
            modalState.selectedSize = { name: sizeName, price: sizePrice };
            
            // Update UI
            document.querySelectorAll('.size-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.target.closest('.size-option').classList.add('selected');
            
            updateModalPrice();
        }
        
        function selectSugar(sugarLevel) {
            modalState.selectedSugar = sugarLevel;
            
            // Update UI
            document.querySelectorAll('.sugar-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.target.closest('.sugar-option').classList.add('selected');
        }
        
        function updateModalQuantity(change) {
            modalState.quantity += change;
            if (modalState.quantity < 1) modalState.quantity = 1;
            
            document.getElementById('modalQuantity').textContent = modalState.quantity;
            updateModalPrice();
        }
        
        function toggleAddon(addonName, addonPrice, checkbox) {
            if (checkbox.checked) {
                modalState.selectedAddons.push({ name: addonName, price: addonPrice });
            } else {
                modalState.selectedAddons = modalState.selectedAddons.filter(a => a.name !== addonName);
            }
            
            updateModalPrice();
        }
        
        function updateModalPrice() {
            const itemPrice = modalState.basePrice + 
                             modalState.selectedSize.price + 
                             modalState.selectedAddons.reduce((sum, addon) => sum + addon.price, 0);
            
            const total = itemPrice * modalState.quantity;
            
            document.getElementById('modalTotalPrice').textContent = '₱' + total.toFixed(2);
        }
        
        function addToCart() {
            const itemPrice = modalState.basePrice + 
                             modalState.selectedSize.price + 
                             modalState.selectedAddons.reduce((sum, addon) => sum + addon.price, 0);
            
            const item = {
                id: Date.now(),
                name: modalState.productName,
                basePrice: modalState.basePrice,
                size: modalState.hasSize ? modalState.selectedSize : null,
                sugar: modalState.hasSize ? modalState.selectedSugar : null,
                addons: [...modalState.selectedAddons],
                quantity: modalState.quantity,
                totalPrice: itemPrice
            };
            
            cart.push(item);
            updateCartUI();
            closeProductModal();
            
            // Show success animation
            showCartAnimation();
        }
        
        function showCartAnimation() {
            // Simple visual feedback
            const cartSection = document.querySelector('.cart-section');
            cartSection.style.transform = 'scale(1.05)';
            setTimeout(() => {
                cartSection.style.transform = 'scale(1)';
            }, 200);
        }
        
        function updateCartUI() {
            const cartContainer = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-center py-12 opacity-50">
                        <svg class="w-16 h-16 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>No items added</p>
                    </div>
                `;
            } else {
                cartContainer.innerHTML = cart.map(item => {
                    const addonsText = item.addons.length > 0 
                        ? `<p class="text-xs opacity-60 mt-1">+ ${item.addons.map(a => a.name).join(', ')}</p>`
                        : '';
                    
                    const sizeText = item.size 
                        ? `<p class="text-xs opacity-60">${item.size.name}</p>`
                        : '';
                    
                    const sugarText = item.sugar
                        ? `<p class="text-xs opacity-60">Sugar: ${item.sugar}</p>`
                        : '';
                    
                    return `
                        <div class="cart-item card p-3 md:p-4 rounded-xl mb-3">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm md:text-base">${item.name}</h4>
                                    ${sizeText}
                                    ${sugarText}
                                    ${addonsText}
                                </div>
                                <button onclick="removeFromCart(${item.id})" class="p-1 hover:bg-red-500 hover:bg-opacity-10 rounded">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button onclick="updateQuantity(${item.id}, -1)" class="w-7 h-7 md:w-8 md:h-8 rounded-lg btn-primary flex items-center justify-center">
                                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-semibold text-sm md:text-base">${item.quantity}</span>
                                    <button onclick="updateQuantity(${item.id}, 1)" class="w-7 h-7 md:w-8 md:h-8 rounded-lg btn-primary flex items-center justify-center">
                                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                                <span class="font-bold text-sm md:text-base">₱${(item.totalPrice * item.quantity).toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                }).join('');
            }
            
            updateCartSummary();
        }
        
        function updateQuantity(itemId, change) {
            const item = cart.find(i => i.id === itemId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(itemId);
                } else {
                    updateCartUI();
                }
            }
        }
        
        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartUI();
        }
        
        function updateCartSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.totalPrice * item.quantity), 0);
            const tax = subtotal * 0.12;
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '₱' + tax.toFixed(2);
            document.getElementById('total').textContent = '₱' + total.toFixed(2);
        }
        
        function checkout(paymentMethod) {
            if (cart.length === 0) {
                alert('Please add items to cart before checkout.');
                return;
            }
            
            const total = cart.reduce((sum, item) => sum + (item.totalPrice * item.quantity), 0) * 1.12;
            const confirmed = confirm(`Confirm ${paymentMethod} payment of ₱${total.toFixed(2)}?`);
            
            if (confirmed) {
                alert(`Payment successful! Order confirmed with ${paymentMethod}.`);
                cart = [];
                updateCartUI();
            }
        }
        
        // Initialize
        updateCartUI();
    </script>
    
</body>
</html>