<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%); }
        .gradient-text { background: linear-gradient(135deg, #6A0DAD 0%, #A020F0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar.open { transform: translateX(0); }
        .modal { display: none; opacity: 0; transition: opacity 0.3s ease; }
        .modal.show { display: flex; opacity: 1; }
        .counter { transition: all 0.5s ease; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        body.dark { background-color: #0f172a; color: #e2e8f0; }
        body.dark .bg-white { background-color: #1e293b; }
        body.dark .text-gray-900 { color: #e2e8f0; }
        body.dark .text-gray-600 { color: #94a3b8; }
        body.dark .text-gray-700 { color: #cbd5e1; }
        body.dark .border-gray-200 { border-color: #334155; }
        body.dark .bg-gray-50 { background-color: #0f172a; }
        body.dark .bg-gray-100 { background-color: #1e293b; }
        body.dark .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.26); }
    </style>
</head>
<body class="bg-gray-50 transition-colors duration-200">
    
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed top-0 left-0 h-full w-64 bg-white shadow-xl z-50 overflow-y-auto">
        <div class="p-6 gradient-bg">
            <h2 class="text-2xl font-bold text-white">☕ Café Admin</h2>
        </div>
        <nav class="p-4 space-y-2">
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-[#6A0DAD] to-[#A020F0] text-white">
                <span>📊</span><span class="font-medium">Dashboard</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>🍰</span><span class="font-medium">Products</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>📦</span><span class="font-medium">Inventory</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>🏷️</span><span class="font-medium">Categories</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>🛒</span><span class="font-medium">Purchases</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>💰</span><span class="font-medium">Sales</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>👥</span><span class="font-medium">User Management</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-100 text-gray-700 transition">
                <span>📈</span><span class="font-medium">Reports</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div id="mainContent" class="transition-all duration-300">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm sticky top-0 z-30">
            <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                <button id="menuToggle" class="gradient-bg text-white p-2 rounded-xl hover:opacity-90 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-xl hover:bg-gray-100 transition">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 rounded-xl hover:bg-gray-100 transition">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- User Profile -->
                    <div class="relative">
                        <button id="userMenuToggle" class="flex items-center space-x-2 p-2 rounded-xl hover:bg-gray-100 transition">
                            <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center text-white font-semibold">
                                A
                            </div>
                            <span class="hidden md:block text-gray-700 font-medium">Admin</span>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-200">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⚙️ Settings</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">🚪 Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <main class="p-4 lg:p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Overview</h1>
            
            <!-- Analytics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Sales Card -->
                <div class="bg-white rounded-2xl shadow p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 gradient-bg rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-1">Total Sales Today</p>
                    <h3 class="text-3xl font-bold gradient-text counter" data-target="12450">₱0</h3>
                    <p class="text-xs text-gray-500 mt-2">All-time: ₱485,320</p>
                </div>

                <!-- Total Orders -->
                <div class="bg-white rounded-2xl shadow p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-1">Total Orders</p>
                    <h3 class="text-3xl font-bold text-gray-900 counter" data-target="248">0</h3>
                    <p class="text-xs text-green-500 mt-2">↑ 12% from yesterday</p>
                </div>

                <!-- Total Products -->
                <div class="bg-white rounded-2xl shadow p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-1">Total Products</p>
                    <h3 class="text-3xl font-bold text-gray-900 counter" data-target="156">0</h3>
                    <p class="text-xs text-gray-500 mt-2">8 categories</p>
                </div>

                <!-- Low Stock -->
                <div class="bg-white rounded-2xl shadow p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-1">Low Stock Items</p>
                    <h3 class="text-3xl font-bold text-gray-900 counter" data-target="12">0</h3>
                    <p class="text-xs text-red-500 mt-2">Requires attention</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Daily Sales Chart -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Daily Sales</h3>
                        <p class="text-sm text-gray-600">Last 7 days performance</p>
                    </div>
                    <canvas id="salesChart"></canvas>
                </div>

                <!-- Best Selling Products -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Best Selling Products</h3>
                        <p class="text-sm text-gray-600">Top 5 this week</p>
                    </div>
                    <canvas id="productsChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="ordersTableBody">
                            <!-- Orders will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="gradient-bg px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white">Order Details</h3>
                <button id="closeModal" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Order ID</p>
                    <p class="text-lg font-semibold text-gray-900" id="modalOrderId">#ORD-001</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Customer</p>
                    <p class="text-lg font-semibold text-gray-900" id="modalCustomer">John Doe</p>
                </div>
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Order Items</h4>
                    <div class="space-y-3" id="modalItems">
                        <!-- Items will be inserted here -->
                    </div>
                </div>
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-2xl font-bold gradient-text" id="modalTotal">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample Data
        const orders = [
            { id: 'ORD-001', customer: 'Juan Dela Cruz', date: '2024-11-24', total: 485, status: 'Completed', items: [{name: 'Caramel Macchiato', size: 'Grande', addons: 'Extra Shot', qty: 2, price: 175}, {name: 'Blueberry Muffin', size: 'Regular', addons: 'None', qty: 1, price: 135}] },
            { id: 'ORD-002', customer: 'Maria Santos', date: '2024-11-24', total: 320, status: 'Pending', items: [{name: 'Iced Americano', size: 'Venti', addons: 'None', qty: 1, price: 150}, {name: 'Chocolate Cake', size: 'Slice', addons: 'None', qty: 1, price: 170}] },
            { id: 'ORD-003', customer: 'Pedro Reyes', date: '2024-11-24', total: 250, status: 'Completed', items: [{name: 'Cappuccino', size: 'Tall', addons: 'Oat Milk', qty: 2, price: 125}] },
            { id: 'ORD-004', customer: 'Ana Garcia', date: '2024-11-23', total: 540, status: 'Completed', items: [{name: 'Flat White', size: 'Grande', addons: 'None', qty: 3, price: 180}] },
            { id: 'ORD-005', customer: 'Carlos Mendoza', date: '2024-11-23', total: 395, status: 'Cancelled', items: [{name: 'Espresso', size: 'Double', addons: 'None', qty: 2, price: 95}, {name: 'Croissant', size: 'Regular', addons: 'Butter', qty: 2, price: 100}] }
        ];

        // Sidebar Toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.add('hidden');
        });

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
        });

        // User Menu Toggle
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userMenu = document.getElementById('userMenu');
        userMenuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => userMenu.classList.add('hidden'));

        // Counter Animation
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const increment = target / 50;
            let count = 0;
            const updateCounter = () => {
                if (count < target) {
                    count += increment;
                    counter.textContent = counter.textContent.includes('₱') ? `₱${Math.ceil(count).toLocaleString()}` : Math.ceil(count);
                    setTimeout(updateCounter, 20);
                } else {
                    counter.textContent = counter.textContent.includes('₱') ? `₱${target.toLocaleString()}` : target;
                }
            };
            updateCounter();
        });

        // Charts
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [12000, 15000, 13500, 17000, 16500, 19000, 12450],
                    borderColor: '#6A0DAD',
                    backgroundColor: 'rgba(106, 13, 173, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const productsCtx = document.getElementById('productsChart').getContext('2d');
        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: ['Caramel Macchiato', 'Iced Americano', 'Cappuccino', 'Flat White', 'Espresso'],
                datasets: [{
                    label: 'Units Sold',
                    data: [145, 132, 118, 105, 98],
                    backgroundColor: ['#6A0DAD', '#7B1FA2', '#8E24AA', '#9C27B0', '#A020F0']
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });

        // Populate Orders Table
        const tbody = document.getElementById('ordersTableBody');
        orders.forEach(order => {
            const statusColors = {
                'Completed': 'bg-green-100 text-green-800',
                'Pending': 'bg-yellow-100 text-yellow-800',
                'Cancelled': 'bg-red-100 text-red-800'
            };
            const row = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${order.id}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${order.customer}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${order.date}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱${order.total}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium ${statusColors[order.status]}">${order.status}</span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="openOrderModal('${order.id}')" class="gradient-bg text-white px-4 py-2 rounded-lg text-sm hover:opacity-90 transition">View</button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        // Order Modal
        const orderModal = document.getElementById('orderModal');
        const closeModal = document.getElementById('closeModal');

        function openOrderModal(orderId) {
            const order = orders.find(o => o.id === orderId);
            document.getElementById('modalOrderId').textContent = order.id;
            document.getElementById('modalCustomer').textContent = order.customer;
            document.getElementById('modalTotal').textContent = `₱${order.total}`;
            
            const itemsContainer = document.getElementById('modalItems');
            itemsContainer.innerHTML = '';
            order.items.forEach(item => {
                itemsContainer.innerHTML += `
                    <div class="flex justify-between items-start p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">${item.name}</p>
                            <p class="text-sm text-gray-600">Size: ${item.size} | Add-ons: ${item.addons}</p>
                            <p class="text-sm text-gray-600">Qty: ${item.qty}</p>
                        </div>
                        <p class="font-semibold text-gray-900">₱${item.price}</p>
                    </div>
                `;
            });
            
            orderModal.classList.add('show');
        }

        closeModal.addEventListener('click', () => orderModal.classList.remove('show'));
        orderModal.addEventListener('click', (e) => {
            if (e.target === orderModal) orderModal.classList.remove('show');
        });
    </script>
</body>
</html>