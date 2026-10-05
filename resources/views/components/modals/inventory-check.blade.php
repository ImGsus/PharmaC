<style>
    #inventory-check-modal .modal-dialog {
        width: calc(100% - 1rem);
        max-width: 1080px;
    }
    #inventory-check-modal .modal-content {
        max-height: calc(100vh - 2rem);
    }
    #inventory-check-modal .modal-header {
        position: sticky;
        top: 0;
        z-index: 4;
        flex: 0 0 auto;
    }
    #inventory-check-modal .modal-body {
        min-height: 0;
        padding-top: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    #inventory-check-modal .inventory-check-toolbar {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #fff;
        padding: 12px 0;
        border-bottom: 1px solid #e6ebf1;
    }
    #inventory-check-modal .inventory-check-list {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 10px;
        padding-top: 12px;
    }
    #inventory-check-modal .inventory-check-card {
        border: 1px solid #e6ebf1;
        border-radius: 6px;
        min-width: 0;
    }
    #inventory-check-modal .inventory-check-card .card-body {
        padding: 14px;
    }
    #inventory-check-modal .inventory-check-card h5 {
        margin-bottom: 4px;
        overflow-wrap: anywhere;
    }
    #inventory-check-modal .inventory-check-meta {
        color: #718096;
        font-size: 13px;
        overflow-wrap: anywhere;
    }
    body.dark-mode #inventory-check-modal .modal-content,
    body.dark-mode #inventory-check-modal .modal-header,
    body.dark-mode #inventory-check-modal .modal-body {
        background: #1c2025;
        color: #f3f4f6;
        border-color: rgba(255, 255, 255, .1);
    }
    body.dark-mode #inventory-check-modal .inventory-check-toolbar,
    body.dark-mode #inventory-check-modal .inventory-check-card {
        background: #1c2025;
        border-color: rgba(255, 255, 255, .1);
    }
    @media (min-width: 768px) {
        #inventory-check-modal .inventory-check-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="modal fade" id="inventory-check-modal" tabindex="-1" role="dialog" aria-labelledby="inventory-check-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inventory-check-modal-title">Inventory Check</h5>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="inventory-check-modal-content" aria-live="polite" aria-busy="true">
                <div class="text-center text-muted py-4">Loading inventory...</div>
            </div>
        </div>
    </div>
</div>

@push('page-js')
<script>
(function ($) {
    var endpoint = @json(route('inventory-check.modal-content'));
    var requestId = 0;
    var modalIsShown = false;

    function loadInventory(url, focusSearch) {
        var currentRequestId = ++requestId;
        var $content = $('#inventory-check-modal-content');
        $content.attr('aria-busy', 'true').html('<div class="text-center text-muted py-4">Loading inventory...</div>');

        $.get(url)
            .done(function (markup) {
                if (currentRequestId !== requestId) return;
                $content.html(markup);
                if (focusSearch && modalIsShown) $content.find('#inventory-check-search').trigger('focus');
            })
            .fail(function () {
                if (currentRequestId !== requestId) return;
                $content.html('<div class="alert alert-danger mb-0" role="alert">Inventory could not be loaded. Close the dialog and try again.</div>');
            })
            .always(function () {
                if (currentRequestId === requestId) $content.attr('aria-busy', 'false');
            });
    }

    $(document).off('.inventoryCheck')
        .on('show.bs.modal.inventoryCheck', '#inventory-check-modal', function () {
            modalIsShown = false;
            loadInventory(endpoint, false);
        })
        .on('shown.bs.modal.inventoryCheck', '#inventory-check-modal', function () {
            modalIsShown = true;
            $('#inventory-check-search').trigger('focus');
        })
        .on('hidden.bs.modal.inventoryCheck', '#inventory-check-modal', function () {
            modalIsShown = false;
        })
        .on('click.inventoryCheck', '#inventory-check-modal .close', function (event) {
            event.preventDefault();
            $('#inventory-check-modal').modal('hide');
        })
        .on('submit.inventoryCheck', '#inventory-check-search-form', function (event) {
            event.preventDefault();
            var query = $(this).serialize();
            loadInventory(query ? endpoint + '?' + query : endpoint, true);
        })
        .on('click.inventoryCheck', '#inventory-check-modal-content .pagination a', function (event) {
            event.preventDefault();
            loadInventory(this.href, true);
        });
})(jQuery);
</script>
@endpush