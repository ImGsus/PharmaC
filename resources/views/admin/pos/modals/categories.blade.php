{{-- Categories modal — included from admin.pos.orders.
     Lists every category with the number of in-stock products it contains.
     Clicking a row sets the on-page category dropdown and re-renders the
     product grid so it only shows that category. --}}
<div class="modal fade" id="categoriesModal" tabindex="-1" role="dialog" aria-labelledby="categoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoriesModalLabel">
                    <i class="fas fa-th-list"></i> Browse by Category
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Pick a category to filter the product grid. Click <strong>All Categories</strong>
                    to clear the filter.
                </p>
                <div id="categories-list">
                    {{-- populated by JS so each row can be made clickable --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" id="categories-clear-btn">
                    <i class="fas fa-undo"></i> Show All
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-js')
<script>
(function(){
    var listEl   = document.getElementById('categories-list');
    var clearBtn = document.getElementById('categories-clear-btn');
    if (!listEl) return;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Counts come straight from the product payload the page already has —
    // we only show categories that actually have at least one in-stock product.
    function buildList() {
        var products = [];
        try {
            products = JSON.parse(document.getElementById('pos-v2-products').textContent || '[]');
        } catch (e) { products = []; }

        var byCat = {};
        products.forEach(function (p) {
            var cid = p.category_id;
            if (cid == null || cid === '') return;
            byCat[cid] = byCat[cid] || { id: cid, name: p.category || 'Uncategorized', count: 0 };
            byCat[cid].count += 1;
        });

        // Read existing <option>s in the on-page category select so the
        // modal stays in sync even if a category has zero products.
        var select = document.getElementById('pos-v2-category');
        if (select) {
            Array.prototype.forEach.call(select.options, function (opt) {
                if (!opt.value) return;
                if (!byCat[opt.value]) {
                    byCat[opt.value] = { id: opt.value, name: opt.text, count: 0 };
                }
            });
        }

        var rows = Object.values(byCat)
            .sort(function (a, b) { return a.name.localeCompare(b.name); });

        if (rows.length === 0) {
            listEl.innerHTML = '<div class="alert alert-info mb-0">No categories yet.</div>';
            return;
        }

        listEl.innerHTML =
            '<div class="pos-v2-cat-list">' +
            rows.map(function (c) {
                return '<button type="button" class="pos-v2-cat-row" data-cat-id="' + escapeHtml(c.id) + '">'
                    + '<span class="pos-v2-cat-name">' + escapeHtml(c.name) + '</span>'
                    + '<span class="pos-v2-cat-count">' + escapeHtml(c.count) + '</span>'
                    + '</button>';
            }).join('') +
            '</div>';

        Array.prototype.forEach.call(listEl.querySelectorAll('.pos-v2-cat-row'), function (btn) {
            btn.addEventListener('click', function () {
                var cid = btn.getAttribute('data-cat-id');
                if (select) {
                    select.value = cid;
                    // trigger change so any existing listener re-renders
                    select.dispatchEvent(new Event('change'));
                }
                // Hide the modal
                if (window.jQuery) {
                    window.jQuery('#categoriesModal').modal('hide');
                }
            });
        });
    }

    // Build list every time the modal opens so it reflects the latest data
    // (e.g. after a product is added).
    if (window.jQuery) {
        window.jQuery('#categoriesModal').on('show.bs.modal', buildList);
    } else {
        // fallback if jQuery isn't loaded for some reason
        buildList();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            var select = document.getElementById('pos-v2-category');
            if (select) {
                select.value = '';
                select.dispatchEvent(new Event('change'));
            }
            if (window.jQuery) {
                window.jQuery('#categoriesModal').modal('hide');
            }
        });
    }
})();
</script>
@endpush
@endonce