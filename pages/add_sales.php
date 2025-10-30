<?php
require_once '../include/header.php'; 


// Fetch all active products
$query = "SELECT product_id, name, price, stock_quantity FROM products WHERE status = 'active' ORDER BY name ASC";
$result = $mysqli->query($query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($p = $result->fetch_assoc()) {
        $products[] = $p;
    }
}
?>

<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">In-Shop Sales</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="panel panel-default">
                    <div class="panel-heading">Record a New Sale</div>
                    <div class="panel-body">
                        <form id="inShopSaleForm">
                            <input type="hidden" name="action" value="add">

                            <!-- Customer Info -->
                            <div class="form-group">
                                <label>Customer Name (Optional)</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="John Doe">
                            </div>

                            <div class="form-group">
                                <label>Customer Phone (Optional)</label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="677123456">
                            </div>

                            <!-- Product List -->
                            <div class="form-group">
                                <label>Products</label>
                                <table class="table table-bordered" id="productTable">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productRows">
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control product-input" placeholder="Type product name" required>
                                                <div class="autocomplete-list" style="position:absolute; z-index:1000; background:#fff; border:1px solid #ccc; display:none;"></div>
                                            </td>
                                            <td class="price text-center">0</td>
                                            <td class="stock text-center">0</td>
                                            <td><input type="number" class="form-control qty" min="1" value="1" style="width:90px;"></td>
                                            <td class="subtotal text-center">0</td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="addProductRow" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> Add Product</button>
                            </div>

                            <!-- Total -->
                            <div class="form-group text-right">
                                <h4>Total: <strong id="totalAmount">0 FCFA</strong></h4>
                            </div>

                            <button type="submit" class="btn btn-primary">Complete Sale</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- /#page-wrapper -->

</div>
<!-- /#wrapper -->

<!-- JS Dependencies -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const productsData = <?php echo json_encode($products); ?>;
    const tableBody = document.getElementById('productRows');
    const addRowBtn = document.getElementById('addProductRow');
    const totalAmountEl = document.getElementById('totalAmount');
    const form = document.getElementById('inShopSaleForm');

    function updateTotals() {
        let total = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const price = parseFloat(row.querySelector('.price').textContent) || 0;
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const subtotal = price * qty;
            row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
            total += subtotal;
        });
        totalAmountEl.textContent = total.toLocaleString() + ' FCFA';
    }

    function autocomplete(input, row) {
        const list = row.querySelector('.autocomplete-list');
        input.addEventListener('input', () => {
            const val = input.value.toLowerCase();
            list.innerHTML = '';
            if (!val) { list.style.display = 'none'; return; }

            const matches = productsData.filter(p => p.name.toLowerCase().includes(val));
            matches.forEach(p => {
                const div = document.createElement('div');
                div.textContent = p.name;
                div.style.padding = '5px';
                div.style.cursor = 'pointer';
                div.addEventListener('click', () => {
                    input.value = p.name;
                    row.dataset.productId = p.product_id;
                    row.querySelector('.price').textContent = p.price;
                    row.querySelector('.stock').textContent = p.stock_quantity;
                    list.style.display = 'none';
                    updateTotals();
                });
                list.appendChild(div);
            });
            list.style.display = matches.length ? 'block' : 'none';
        });

        document.addEventListener('click', e => {
            if (e.target !== input) list.style.display = 'none';
        });
    }

    function attachRowEvents(row) {
        const input = row.querySelector('.product-input');
        const qtyInput = row.querySelector('.qty');
        const removeBtn = row.querySelector('.remove-row');

        autocomplete(input, row);
        qtyInput.addEventListener('input', updateTotals);
        removeBtn.addEventListener('click', () => { row.remove(); updateTotals(); });
    }

    attachRowEvents(tableBody.querySelector('tr'));

    addRowBtn.addEventListener('click', () => {
        const newRow = tableBody.querySelector('tr').cloneNode(true);
        newRow.querySelector('.product-input').value = '';
        newRow.querySelector('.price').textContent = '0';
        newRow.querySelector('.stock').textContent = '0';
        newRow.querySelector('.qty').value = '1';
        newRow.querySelector('.subtotal').textContent = '0';
        attachRowEvents(newRow);
        tableBody.appendChild(newRow);
    });

    form.addEventListener('submit', e => {
        e.preventDefault();
        const items = [];
        let valid = true;

        tableBody.querySelectorAll('tr').forEach(row => {
            const name = row.querySelector('.product-input').value.trim();
            const product = productsData.find(p => p.name.toLowerCase() === name.toLowerCase());
            const qty = parseInt(row.querySelector('.qty').value);
            if (!product) valid = false;
            if(product && qty > 0) items.push({ product_id: product.product_id, quantity: qty });
        });

        if (!valid) { alert('Please select valid products from the suggestions.'); return; }
        if(items.length === 0) { alert('Please select at least one product.'); return; }

        const formData = new FormData(form);
        formData.append('items', JSON.stringify(items));

        fetch('../include/sale.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { alert(data.message); if(data.status==='success'){ window.location.reload(); } })
            .catch(err => { console.error(err); alert('Error processing sale.'); });
    });
});
</script>

</body>
</html>
