<?php
require_once '../include/header.php';
require_once '../classes/connection.php';

$result = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <h1 class="page-header">Category Management</h1>

        <!-- ADD CATEGORY FORM -->
        <div class="row mb-3">
            <div class="col-lg-6">
                <h4>Add New Category</h4>
                <form id="addCategoryForm">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="addCategoryName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="addCategorySlug" class="form-control" required>
                        <small>Slug auto-generated but editable</small>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Add Category</button>
                </form>
            </div>
        </div>

        <!-- CATEGORY TABLE -->
        <div class="row">
            <div class="col-lg-12">
                <h4>All Categories</h4>
                <table class="table table-striped table-bordered" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($categories): ?>
                            <?php $i = 1; foreach($categories as $cat): ?>
                                <tr id="cat-<?php echo $cat['category_id']; ?>">
                                    <td><?php echo $i++; ?></td>
                                    <td class="cat-name"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td class="cat-slug"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                    <td class="cat-status"><?php echo $cat['status']; ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-xs edit-btn" data-id="<?php echo $cat['category_id']; ?>">Edit</button>
                                        <button class="btn btn-danger btn-xs delete-btn" data-id="<?php echo $cat['category_id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No categories found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SINGLE CONFIRMATION / MESSAGE MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalTitle">Confirm</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="confirmModalBody">Are you sure?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="confirmCancelBtn" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmOkBtn">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addCategoryForm');
    const addName = document.getElementById('addCategoryName');
    const addSlug = document.getElementById('addCategorySlug');
    const table = document.getElementById('categoriesTable').querySelector('tbody');

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const modalTitle = document.getElementById('confirmModalTitle');
    const modalBody = document.getElementById('confirmModalBody');
    const okBtn = document.getElementById('confirmOkBtn');

    // Auto-generate slug
    addName.addEventListener('input', () => {
        addSlug.value = addName.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g,'')
            .trim()
            .replace(/\s+/g,'-');
    });

    // Show modal for messages
    function showMessage(message, callback=null) {
        modalTitle.textContent = 'Message';
        modalBody.textContent = message;
        okBtn.style.display = 'inline-block';
        okBtn.onclick = function() {
            modal.hide();
            if(callback) callback();
        };
        modal.show();
    }

    // Show modal for confirmation
    function showConfirmation(message, callback) {
        modalTitle.textContent = 'Confirm';
        modalBody.textContent = message;
        okBtn.style.display = 'inline-block';
        okBtn.onclick = function() {
            modal.hide();
            callback();
        };
        modal.show();
    }

    // Add category
    addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(addForm);
        fetch('../include/category.php', { method:'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, () => {
                    if(data.status==='success') location.reload();
                });
            }).catch(()=>showMessage('Error adding category.'));
    });

    // Edit / Delete
    table.addEventListener('click', function(e) {
        const target = e.target;
        const row = target.closest('tr');
        const id = target.dataset.id;

        // DELETE
        if(target.classList.contains('delete-btn')) {
            showConfirmation('Are you sure you want to delete this category?', () => {
                fetch('../include/category.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:`action=delete&category_id=${id}`
                })
                .then(res=>res.json())
                .then(data=>{
                    showMessage(data.message, ()=>{
                        if(data.status==='success') location.reload();
                    });
                });
            });
        }

        // EDIT
        if(target.classList.contains('edit-btn')) {
            const name = row.querySelector('.cat-name').textContent;
            const slug = row.querySelector('.cat-slug').textContent;
            const status = row.querySelector('.cat-status').textContent;

            row.innerHTML = `
                <td>${row.cells[0].textContent}</td>
                <td><input type="text" class="form-control edit-name" value="${name}"></td>
                <td><input type="text" class="form-control edit-slug" value="${slug}"></td>
                <td>
                    <select class="form-control edit-status">
                        <option value="active" ${status==='active'?'selected':''}>Active</option>
                        <option value="inactive" ${status==='inactive'?'selected':''}>Inactive</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-success btn-xs save-btn" data-id="${id}">Save</button>
                    <button class="btn btn-default btn-xs cancel-btn">Cancel</button>
                </td>
            `;
        }

        // CANCEL EDIT
        if(target.classList.contains('cancel-btn')) location.reload();

        // SAVE EDIT
        if(target.classList.contains('save-btn')) {
            const newName = row.querySelector('.edit-name').value;
            const newSlug = row.querySelector('.edit-slug').value;
            const newStatus = row.querySelector('.edit-status').value;

            fetch('../include/category.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:`action=update&category_id=${id}&name=${encodeURIComponent(newName)}&slug=${encodeURIComponent(newSlug)}&status=${newStatus}`
            })
            .then(res=>res.json())
            .then(data=>{
                showMessage(data.message, ()=>{
                    if(data.status==='success') location.reload();
                });
            });
        }
    });
});
</script>
</body>
</html>
