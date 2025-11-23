<div id="sidebar" class="fixed top-0 left-0 h-full w-80 bg-gray-800 text-white transform -translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Order Summary</h2>
            <button id="closeSidebar" class="text-white hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="orderItems" class="space-y-4 mb-6">
            <!-- Order items will be added here dynamically -->
        </div>

        <div class="border-t border-gray-600 pt-4">
            <div class="flex justify-between items-center text-lg font-bold">
                <span>Total:</span>
                <span id="totalAmount">₱0.00</span>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded transition duration-200">
                Complete Order
            </button>
            <button id="clearOrder" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded transition duration-200">
                Clear Order
            </button>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
