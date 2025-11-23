<!-- Product View Modal (read-only) -->
<div id="productViewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="card rounded-xl p-6 max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border" style="color:var(--text-primary);">
        <header class="flex items-center justify-between mb-4" style="background:var(--accent); padding:0.75rem 1rem; border-radius:0.5rem; margin:-1rem -1rem 1rem -1rem; align-items:center;">
            <h3 id="viewModalTitle" class="text-xl font-bold" style="color:#fff;">Product</h3>
            <button id="closeProductView" class="text-white hover:text-gray-200" style="font-size:1.25rem; background:transparent; border:none;">&times;</button>
        </header>

        <div class="flex gap-6">
            <div class="w-40 h-40 shrink-0">
                <img id="viewProductImage" src="" alt="Product image" class="w-full h-full object-cover rounded-lg" />
            </div>
            <div class="flex-1">
                <p class="text-lg font-semibold" id="viewProductName"></p>
                <p class="text-sm text-gray-500 mb-3" id="viewProductCategory"></p>
                <p class="text-sm text-gray-700 mb-3" id="viewProductDescription"></p>
                <p class="text-base font-semibold mb-2">Price: <span id="viewProductPrice"></span></p>

                <div id="viewProductSizes" class="mb-3"></div>
                <div id="viewProductAddons" class="mb-3"></div>
            </div>
        </div>

        <div class="mt-6 text-right">
            <button id="closeProductView2" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
        </div>
    </div>
</div>