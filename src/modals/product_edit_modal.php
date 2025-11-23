<!-- Product Edit Modal -->
<div id="productEditModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="card rounded-xl p-6 max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border" style="color:var(--text-primary)">
        <header class="flex items-center justify-between mb-4" style="background:var(--accent); padding:0.75rem 1rem; border-radius:0.5rem; margin:-1rem -1rem 1rem -1rem; align-items:center;">
            <h3 class="text-xl font-bold" style="color:#fff;">Edit Product</h3>
            <button id="closeProductEdit" class="text-white hover:text-gray-200" style="font-size:1.25rem; background:transparent; border:none;">&times;</button>
        </header>

        <form id="editProductForm" class="space-y-4">
            <input type="hidden" name="item_id" id="edit_item_id" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input name="name" id="edit_name" class="mt-1 w-full px-3 py-2 border rounded" required />
                </div>

                <div>
                    <label class="text-sm font-medium">Category</label>
                    <select name="category_id" id="edit_category" class="mt-1 w-full px-3 py-2 border rounded" required>
                        <option value="">Select category</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Description</label>
                <textarea name="description" id="edit_description" class="mt-1 w-full px-3 py-2 border rounded" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Price</label>
                    <input type="number" step="0.01" name="base_price" id="edit_price" class="mt-1 w-full px-3 py-2 border rounded" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Image Path (optional)</label>
                    <input name="image_path" id="edit_image_path" class="mt-1 w-full px-3 py-2 border rounded" placeholder="public/uploads/your-image.jpg" />
                    <input type="file" id="edit_image_file" class="mt-2" accept="image/*" />
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelEditProduct" class="px-4 py-2 modal-btn">Cancel</button>
                <button type="submit" class="px-4 py-2 btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>