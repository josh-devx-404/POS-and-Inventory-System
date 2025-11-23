<!-- Product Delete Modal -->
<div id="productDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="card rounded-xl p-6 max-w-md w-full shadow-2xl border" style="color:var(--text-primary)">
        <header class="flex items-center justify-between mb-4" style="background:var(--accent); padding:0.75rem 1rem; border-radius:0.5rem; margin:-1rem -1rem 1rem -1rem; align-items:center;">
            <h3 class="text-xl font-bold" style="color:#fff;">Delete Product</h3>
            <button id="closeProductDelete" class="text-white hover:text-gray-200" style="font-size:1.25rem; background:transparent; border:none;">&times;</button>
        </header>

        <p class="mb-4">Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>

        <div class="flex justify-end space-x-3">
            <button id="cancelDeleteProduct" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
            <button id="confirmDeleteProduct" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
        </div>
    </div>
</div>