<!-- Product Details Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="card rounded-xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border-2" style="color:var(--text-primary)">
        <h3 id="modalTitle" class="text-2xl font-bold mb-8 text-center bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent">Product Details</h3>
        <div class="space-y-6">
            <!-- Size Selection -->
            <div id="sizeSection" class="hidden">
                <h4 class="text-md font-semibold mb-4">Select Size</h4>
                <div class="grid grid-cols-3 gap-4">
                    <button class="size-btn text-sm font-medium flex items-center justify-center space-x-2" data-size="small">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span>Small - ₱2.50</span>
                    </button>
                    <button class="size-btn text-sm font-medium flex items-center justify-center space-x-2" data-size="medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span>Medium - ₱3.00</span>
                    </button>
                    <button class="size-btn text-sm font-medium flex items-center justify-center space-x-2" data-size="large">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span>Large - ₱3.50</span>
                    </button>
                </div>
            </div>
            <hr class="modal-separator">
            <!-- Sugar Level Selection -->
            <div id="sugarSection" class="hidden">
                <h4 class="text-md font-semibold mb-4">Sugar Level</h4>
                <div class="grid grid-cols-5 gap-4">
                    <button class="sugar-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-sugar="0%">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>0%</span>
                    </button>
                    <button class="sugar-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-sugar="25%">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>25%</span>
                    </button>
                    <button class="sugar-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-sugar="50%">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>50%</span>
                    </button>
                    <button class="sugar-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-sugar="75%">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>75%</span>
                    </button>
                    <button class="sugar-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-sugar="100%">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>100%</span>
                    </button>
                </div>
            </div>
            <hr class="modal-separator">
            <!-- Add-ons -->
            <div id="addonSection" class="hidden">
                <h4 class="text-md font-semibold mb-4">Add-ons</h4>
                <div class="grid grid-cols-3 gap-4">
                    <button class="addon-btn bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-addon="Extra Milk" data-price="0.50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        <span>Extra Milk (+₱0.50)</span>
                    </button>
                    <button class="addon-btn bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-addon="Syrup" data-price="0.75">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Syrup (+₱0.75)</span>
                    </button>
                    <button class="addon-btn bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transition text-sm font-medium flex items-center justify-center space-x-2" data-addon="Whipped Cream" data-price="1.00">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span>Whipped Cream (+₱1.00)</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
            <button id="cancelProduct" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-medium">Cancel</button>
            <button id="confirmProduct" class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition font-medium">Add to Order</button>
        </div>
    </div>
</div>
