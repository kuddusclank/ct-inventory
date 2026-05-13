<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Inventory</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .page-header {
            background: #0d6efd;
            color: #fff;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .table thead th {
            background-color: #0d6efd;
            color: #fff;
            border: none;
            font-weight: 600;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: #f0f4ff;
        }

        .total-row td {
            background-color: #e8f0fe;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .btn-edit { min-width: 70px; }
        .edit-inputs input { font-size: .875rem; }

        #alert-box {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1055;
            min-width: 260px;
        }

        .empty-state {
            padding: 3rem 0;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container">
        <h1 class="h3 mb-0"><i class="bi bi-box-seam me-2"></i>Product Inventory</h1>
    </div>
</div>

<div id="alert-box"></div>

<div class="container pb-5">

    {{-- Add Product Form --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold">Add Product</div>
        <div class="card-body">
            <form id="product-form" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="name">Product Name</label>
                        <input type="text" id="name" name="name"
                            class="form-control" placeholder="e.g. USB-C Cable" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="quantity">Quantity in Stock</label>
                        <input type="number" id="quantity" name="quantity"
                            class="form-control" placeholder="0" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="price">Price per Item ($)</label>
                        <input type="number" id="price" name="price"
                            class="form-control" placeholder="0.00" min="0" step="0.01" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" id="submit-btn">
                            <span id="submit-text">Add</span>
                            <span id="submit-spinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Inventory</span>
            <span class="badge bg-secondary" id="product-count">{{ count($products) }} item(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="products-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-end">Quantity in Stock</th>
                            <th class="text-end">Price per Item</th>
                            <th>Datetime Submitted</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-body">
                        @forelse($products as $product)
                            @include('products._row', ['product' => $product])
                        @empty
                            <tr id="empty-row">
                                <td colspan="6" class="text-center empty-state">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No products yet. Add one above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($products) > 0)
                    <tfoot>
                        <tr class="total-row" id="total-row">
                            <td colspan="4" class="text-end">Grand Total</td>
                            <td class="text-end" id="grand-total">
                                ${{ number_format(array_sum(array_map(fn($p) => $p['quantity'] * $p['price'], $products)), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

    // ── Flash alert ──────────────────────────────────────────────────────────
    function showAlert(message, type = 'success') {
        const box = document.getElementById('alert-box');
        box.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        setTimeout(() => {
            const alert = box.querySelector('.alert');
            if (alert) bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 3500);
    }

    // ── Render table from JSON response ──────────────────────────────────────
    function renderTable(products, grandTotal) {
        const tbody = document.getElementById('products-body');
        const countBadge = document.getElementById('product-count');
        let tfoot = document.querySelector('#products-table tfoot');

        tbody.innerHTML = '';

        if (products.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="6" class="text-center empty-state">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No products yet. Add one above.
                    </td>
                </tr>`;
            if (tfoot) tfoot.remove();
            countBadge.textContent = '0 item(s)';
            return;
        }

        products.forEach(p => {
            tbody.insertAdjacentHTML('beforeend', buildRow(p));
        });

        const total = (grandTotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        if (!tfoot) {
            tfoot = document.createElement('tfoot');
            document.getElementById('products-table').appendChild(tfoot);
        }

        tfoot.innerHTML = `
            <tr class="total-row" id="total-row">
                <td colspan="4" class="text-end">Grand Total</td>
                <td class="text-end" id="grand-total">$${total}</td>
                <td></td>
            </tr>`;

        countBadge.textContent = `${products.length} item(s)`;
        attachEditHandlers();
    }

    // ── Build a single row HTML ───────────────────────────────────────────────
    function buildRow(p) {
        const rowTotal = (p.quantity * p.price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const price    = parseFloat(p.price).toFixed(2);
        return `
        <tr data-id="${p.id}" data-name="${escHtml(p.name)}"
            data-quantity="${p.quantity}" data-price="${price}">
            <td class="cell-name">${escHtml(p.name)}</td>
            <td class="cell-quantity text-end">${p.quantity}</td>
            <td class="cell-price text-end">$${price}</td>
            <td>${p.created_at}</td>
            <td class="text-end">$${rowTotal}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary btn-edit">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
            </td>
        </tr>`;
    }

    // ── Escape HTML to prevent XSS ───────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Submit new product ────────────────────────────────────────────────────
    document.getElementById('product-form').addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const submitBtn  = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const spinner    = document.getElementById('submit-spinner');

        submitBtn.disabled = true;
        submitText.textContent = 'Saving…';
        spinner.classList.remove('d-none');

        const payload = {
            name:     document.getElementById('name').value.trim(),
            quantity: document.getElementById('quantity').value,
            price:    document.getElementById('price').value,
        };

        axios.post('/products', payload)
            .then(res => {
                renderTable(res.data.products, res.data.total);
                this.reset();
                showAlert('Product added successfully.');
            })
            .catch(err => {
                if (err.response?.status === 422) {
                    showValidationErrors(err.response.data.errors);
                } else {
                    showAlert('Something went wrong. Please try again.', 'danger');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = 'Add';
                spinner.classList.add('d-none');
            });
    });

    // ── Inline row editing ────────────────────────────────────────────────────
    function attachEditHandlers() {
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                enterEditMode(row);
            });
        });
    }

    function enterEditMode(row) {
        const id       = row.dataset.id;
        const name     = row.dataset.name;
        const quantity = row.dataset.quantity;
        const price    = row.dataset.price;

        row.innerHTML = `
            <td><input type="text" class="form-control form-control-sm" id="edit-name" value="${escHtml(name)}" required></td>
            <td><input type="number" class="form-control form-control-sm text-end" id="edit-quantity" value="${quantity}" min="0" required></td>
            <td><input type="number" class="form-control form-control-sm text-end" id="edit-price" value="${price}" min="0" step="0.01" required></td>
            <td colspan="2" class="text-muted fst-italic align-middle">editing…</td>
            <td class="text-center">
                <button class="btn btn-sm btn-success me-1 btn-save">
                    <i class="bi bi-check-lg"></i> Save
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-cancel">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>`;

        row.querySelector('.btn-save').addEventListener('click', () => saveRow(row, id));
        row.querySelector('.btn-cancel').addEventListener('click', () => {
            row.querySelector('#edit-name').value     = name;
            row.querySelector('#edit-quantity').value = quantity;
            row.querySelector('#edit-price').value    = price;
            reloadPage();
        });
    }

    function saveRow(row, id) {
        const payload = {
            name:     row.querySelector('#edit-name').value.trim(),
            quantity: row.querySelector('#edit-quantity').value,
            price:    row.querySelector('#edit-price').value,
            _method:  'PUT',
        };

        axios.post(`/products/${id}`, payload)
            .then(res => {
                renderTable(res.data.products, res.data.total);
                showAlert('Product updated successfully.');
            })
            .catch(err => {
                showAlert('Could not save changes. Please check your input.', 'danger');
            });
    }

    function reloadPage() { window.location.reload(); }

    // ── Validation error display ──────────────────────────────────────────────
    function showValidationErrors(errors) {
        const fields = { name: 'name', quantity: 'quantity', price: 'price' };
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.getElementById(fields[field]);
            if (input) {
                input.classList.add('is-invalid');
                input.nextElementSibling.textContent = messages[0];
            }
        }
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    // ── Init on page load ─────────────────────────────────────────────────────
    attachEditHandlers();
</script>
</body>
</html>
