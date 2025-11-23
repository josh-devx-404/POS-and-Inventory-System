<!-- Cookie Package Modal -->
<div id="packageModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-lg w-full">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white" id="packageModalTitle">Add New Package</h3>
            <button onclick="closeModal('packageModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="packageForm" class="p-6 space-y-4">
            <input type="hidden" id="package_id" name="package_id">
            
            <!-- Package Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Package Name</label>
                <input type="text" id="package_name" name="package_name" required 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent dark:bg-gray-800 dark:text-white"
                    placeholder="e.g., Bag of 6 Classic Choco Chip">
            </div>
            
            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="package_description" name="description" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent dark:bg-gray-800 dark:text-white"
                    placeholder="Package description"></textarea>
            </div>
            
            <!-- Quantity and Price -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity</label>
                    <input type="number" id="package_quantity" name="quantity" min="1" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price (₱)</label>
                    <input type="number" id="package_price" name="price" step="0.01" min="0" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('packageModal')"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="gradient-violet text-white px-6 py-2 rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Save Package
                </button>
            </div>
        </form>
    </div>
</div>