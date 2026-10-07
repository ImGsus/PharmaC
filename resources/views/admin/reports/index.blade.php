@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
    .reports-dashboard { width: 100%; }
    /* LIGHT MODE BOX POSITION: these values move the complete light-mode grid. */
    .reports-grid {
        --reports-grid-x: 0px; /* Move left/right: use negative or positive values. */
        --reports-grid-y: 0px; /* Move up/down: use negative or positive values. */
        transform: translate(var(--reports-grid-x), var(--reports-grid-y));
    }
    /* LIGHT MODE ROW 1: edit x, y, and height to move or resize the first white-mode row. */
    body:not(.dark-mode) .reports-grid > :nth-child(-n+3) { --light-row-x: 0px; --light-row-y: 0px; --light-row-height: 240px; transform: translate(var(--light-row-x), var(--light-row-y)); }
    body:not(.dark-mode) .reports-grid > :nth-child(-n+3) .report-card { --report-card-height: var(--light-row-height); }
    /* LIGHT MODE ROW 2: edit x, y, and height to move or resize the second white-mode row. */
    body:not(.dark-mode) .reports-grid > :nth-child(n+4):nth-child(-n+6) { --light-row-x: 0px; --light-row-y: 0px; --light-row-height: 240px; transform: translate(var(--light-row-x), var(--light-row-y)); }
    body:not(.dark-mode) .reports-grid > :nth-child(n+4):nth-child(-n+6) .report-card { --report-card-height: var(--light-row-height); }
    /* LIGHT MODE ROW 3: edit x, y, and height to move or resize the third white-mode row. */
    body:not(.dark-mode) .reports-grid > :nth-child(n+7):nth-child(-n+9) { --light-row-x: 0px; --light-row-y: 0px; --light-row-height: 240px; transform: translate(var(--light-row-x), var(--light-row-y)); }
    body:not(.dark-mode) .reports-grid > :nth-child(n+7):nth-child(-n+9) .report-card { --report-card-height: var(--light-row-height); }
    /* LIGHT MODE ROW 4: edit x, y, and height to move or resize the fourth white-mode row. */
    body:not(.dark-mode) .reports-grid > :nth-child(n+10):nth-child(-n+12) { --light-row-x: 0px; --light-row-y: 0px; --light-row-height: 240px; transform: translate(var(--light-row-x), var(--light-row-y)); }
    body:not(.dark-mode) .reports-grid > :nth-child(n+10):nth-child(-n+12) .report-card { --report-card-height: var(--light-row-height); }
    /* DARK MODE ROW 1: change these values to move the first dark-mode row left/right or up/down.
       IMPORTANT: keep --dark-row-y at 0px (same as light mode). Positive values push the rows
       below the 100vh main-wrapper, exposing the near-black body background at the bottom. */
    body.dark-mode .reports-grid > :nth-child(-n+3) { --dark-row-x: 0px; --dark-row-y: 0px; transform: translate(var(--dark-row-x), var(--dark-row-y)); }
    /* DARK MODE ROW 2: change these values to move the second dark-mode row left/right or up/down. */
    body.dark-mode .reports-grid > :nth-child(n+4):nth-child(-n+6) { --dark-row-x: 0px; --dark-row-y: 0px; transform: translate(var(--dark-row-x), var(--dark-row-y)); }
    /* DARK MODE ROW 3: change these values to move the third dark-mode row left/right or up/down. */
    body.dark-mode .reports-grid > :nth-child(n+7):nth-child(-n+9) { --dark-row-x: 0px; --dark-row-y: 0px; transform: translate(var(--dark-row-x), var(--dark-row-y)); }
    /* DARK MODE ROW 4: change these values to move the fourth dark-mode row left/right or up/down. */
    body.dark-mode .reports-grid > :nth-child(n+10):nth-child(-n+12) { --dark-row-x: 0px; --dark-row-y: 0px; transform: translate(var(--dark-row-x), var(--dark-row-y)); }
    .report-hero { background: linear-gradient(135deg, #19324a, #1f7a75); color: #fff; border-radius: 12px; padding: 28px; margin-bottom: 24px; min-height: 96px; }
    .report-hero h3 { margin-bottom: 6px; }
    .report-hero p { margin: 0; color: rgba(255,255,255,.82); }
    /* SHARED REPORT BOXES: these dimensions apply to every box in both light and dark mode. */
    /* REPORT BOX SIZE: change the height value below to resize every box vertically. */
    .report-card { --report-card-height: 240px; height: var(--report-card-height); min-height: var(--report-card-height); box-sizing: border-box; border: 1px solid #e6ebf1; border-radius: 10px; transition: transform .18s ease, box-shadow .18s ease; }
    .report-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(25,50,74,.12); }
    .report-card .card-body { display: flex; flex-direction: column; padding: 1.5rem; }
    /* LIGHT MODE ICONS: dark blue icon color for the white-mode report boxes. */
    .report-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: #e8f4f2; color: #123b66; font-size: 20px; margin-bottom: 16px; }
    .report-card h5 { margin-bottom: 8px; }
    .report-card p { color: #718096; min-height: 48px; }
    .report-card .btn { margin-top: auto; align-self: flex-start; }
    /* DARK MODE REPORT BOXES: edit this rule for the dark-mode background and border. */
    body.dark-mode .reports-dashboard .report-card { background: #1c2025; border-color: rgba(255,255,255,.08); }
    body.dark-mode .reports-dashboard .report-card p { color: #aeb4bc; }
    /* DARK MODE ICONS: dark green icon color for the dark-mode report boxes. */
    body.dark-mode .reports-dashboard .report-icon,
    html.dark-mode .reports-dashboard .report-icon { background: #e8f4f2 !important; color: #0f6b57 !important; }
    body.dark-mode .reports-dashboard .report-icon i,
    html.dark-mode .reports-dashboard .report-icon i { color: #0f6b57 !important; opacity: 1 !important; }
    /* DARK MODE BUTTONS: green outline and text for all report buttons. */
    body.dark-mode .reports-dashboard .report-card .report-action-button,
    html.dark-mode .reports-dashboard .report-card .report-action-button { background-color: transparent !important; border: 1px solid #2e9d72 !important; color: #55c995 !important; }
    body.dark-mode .reports-dashboard .report-card .report-action-button:hover,
    html.dark-mode .reports-dashboard .report-card .report-action-button:hover { background: #2e9d72 !important; border-color: #2e9d72 !important; color: #fff !important; }
    @media (min-width: 768px) {
        #reportViewerModal .modal-dialog {
            width: 95vw !important;
            max-width: 1400px !important;
            min-width: 860px !important;
            margin: 1.75rem auto !important;
        }
    }
    @media (min-width: 1400px) {
        #reportViewerModal .modal-dialog {
            max-width: 1520px !important;
        }
    }
    .report-modal .modal-content { border: 0; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.2); }
    .report-modal .modal-header { background: #19324a; color: #fff; border-radius: 12px 12px 0 0; padding: 16px 22px; }
    .report-modal .modal-header .close { color: #fff; opacity: .9; }
    .report-modal .modal-body { padding: 22px; max-height: calc(100vh - 120px); overflow-y: auto; }
    .report-modal .report-toolbar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 18px; margin-bottom: 14px; }
    .report-modal .report-toolbar label { font-size: 0.8125rem; font-weight: 600; margin-bottom: 4px; color: #475569; }
    .report-modal .report-filter-form button[type="submit"] { background-color: #10b981 !important; border-color: #10b981 !important; color: #fff !important; font-weight: 500; transition: background-color .15s ease, border-color .15s ease; }
    .report-modal .report-filter-form button[type="submit"]:hover { background-color: #059669 !important; border-color: #059669 !important; }
    .report-modal .report-csv-link { font-weight: 500; }
    .report-modal .report-summary { color: #64748b; font-size: 0.875rem; margin-bottom: 16px; }
    body.dark-mode .report-modal .report-toolbar { background: #111827; border-color: rgba(255,255,255,.1); }
    body.dark-mode .report-modal .report-toolbar label { color: #cbd5e1; }
    body.dark-mode .report-modal .report-summary { color: #94a3b8; }
    .report-modal .pharma-table-toolbar { position: static !important; top: auto !important; display: flex !important; align-items: center !important; justify-content: flex-start !important; margin-bottom: 12px !important; background: transparent !important; padding: 0 !important; }
    .report-modal .pharma-table-toolbar .dataTables_filter { margin: 0 !important; }
    .report-modal .pharma-table-toolbar .dataTables_filter label { margin: 0 !important; display: flex !important; align-items: center !important; }
    .report-modal .pharma-table-toolbar .dataTables_filter input { min-width: 260px !important; height: 38px !important; border-radius: 8px !important; padding: 6px 14px !important; border: 1px solid #d1d5db !important; font-size: 0.875rem !important; }
    body.dark-mode .report-modal .pharma-table-toolbar .dataTables_filter input { background-color: #111827 !important; border-color: rgba(255,255,255,.15) !important; color: #f3f4f6 !important; }
    .report-modal .tabulator { position: static !important; border: 1px solid var(--ph-tab-border, #e5e7eb) !important; border-radius: 8px !important; }
    .report-modal .tabulator .tabulator-footer { position: static !important; top: auto !important; order: -1 !important; background-color: var(--ph-header-bg, #f8fafc) !important; border-top: none !important; border-bottom: 1px solid var(--ph-footer-border, #e5e7eb) !important; padding: 8px 14px !important; display: flex !important; align-items: center !important; justify-content: flex-end !important; min-height: 44px !important; }
    .report-modal .tabulator .tabulator-header { position: static !important; top: auto !important; border-bottom: 1px solid var(--ph-header-border, #e5e7eb) !important; }
    body.dark-mode .report-modal .modal-content { background: #1c2025; color: #e7e9ec; }
    body.dark-mode .report-modal .tabulator .tabulator-footer { background-color: #1f2937 !important; border-color: rgba(255,255,255,.12) !important; }
    .generation-option { display: flex; align-items: center; justify-content: space-between; gap: 16px; border: 1px solid #e6ebf1; border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; }
    .generation-option:last-child { margin-bottom: 0; }
    .generation-option strong { display: block; margin-bottom: 3px; }
    .generation-option span { color: #718096; font-size: 13px; }
    body.dark-mode .generation-option { border-color: rgba(255,255,255,.12); }
    body.dark-mode .generation-option span { color: #aeb4bc; }
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Reports</h3>
    <ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Reports</li></ul>
</div>
@endpush

@section('content')
<div class="reports-dashboard">
<div class="report-hero">
    <h3>Pharmacy Reports</h3>
    <p>Review stock, expiry, purchasing, dispensing, batch traceability, valuation, and compliance exceptions from one place.</p>
</div>

<!-- REPORT BOX WIDTH: change col-md-6 / col-xl-4 below to resize boxes horizontally and change the number per row. -->
<div class="row reports-grid">
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card report-card">
            <div class="card-body">
                <span class="report-icon"><i class="fe fe-file"></i></span>
                <h5>Report Generations</h5>
                <p>Generate the existing date-filtered sales and purchase reports.</p>
                <button type="button" class="btn btn-outline-primary report-action-button" data-toggle="modal" data-target="#reportGenerationsModal"><i class="fe fe-file mr-1"></i> Choose report</button>
            </div>
        </div>
    </div>
    @foreach($reports as $slug => $report)
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card report-card">
            <div class="card-body">
                <span class="report-icon"><i class="fe {{ $report['icon'] }}"></i></span>
                <h5>{{ $report['title'] }}</h5>
                <p>{{ $report['description'] }}</p>
                <button type="button" class="btn btn-outline-primary report-action-button open-report-button" data-report-title="{{ $report['title'] }}" data-report-url="{{ route('reports.show', $slug) }}"><i class="fe fe-eye mr-1"></i> Open report</button>
            </div>
        </div>
    </div>
    @endforeach
</div>
</div>

<div class="modal fade report-modal" id="reportGenerationsModal" tabindex="-1" role="dialog" aria-labelledby="reportGenerationsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportGenerationsTitle">Report Generations</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="generation-option">
                    <div><strong>Sales Report</strong><span>Review dispensing transactions by date range.</span></div>
                    <button type="button" class="btn btn-primary btn-sm open-generation-report" data-report-title="Sales Report" data-report-url="{{ route('sales.report') }}"><i class="fe fe-bar-chart mr-1"></i> Open</button>
                </div>
                <div class="generation-option">
                    <div><strong>Purchase Report</strong><span>Review purchases, suppliers, costs, and expiry.</span></div>
                    <button type="button" class="btn btn-primary btn-sm open-generation-report" data-report-title="Purchase Report" data-report-url="{{ route('purchases.report') }}"><i class="fe fe-shopping-bag mr-1"></i> Open</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade report-modal" id="reportViewerModal" tabindex="-1" role="dialog" aria-labelledby="reportViewerTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportViewerTitle">Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="reportViewerBody"><div class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin mr-2"></i>Loading report...</div></div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    (function () {
        function syncReportButtonTheme() {
            var isDark = document.body.classList.contains('dark-mode') || document.documentElement.classList.contains('dark-mode');
            $('.report-action-button').each(function () {
                if (isDark) {
                    this.style.setProperty('background-color', 'transparent', 'important');
                    this.style.setProperty('border-color', '#2e9d72', 'important');
                    this.style.setProperty('color', '#55c995', 'important');
                } else {
                    this.style.removeProperty('background-color');
                    this.style.removeProperty('border-color');
                    this.style.removeProperty('color');
                }
            });
        }

        syncReportButtonTheme();
        new MutationObserver(syncReportButtonTheme).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
        new MutationObserver(syncReportButtonTheme).observe(document.body, { attributes: true, attributeFilter: ['class', 'data-theme'] });

        function initializeReportTable() {
            var tables = $('#reportViewerBody .report-table, #reportViewerBody .generation-report-table');
            if (window.PharmaTabulator && tables.length) {
                tables.each(function (index) {
                    var tableInstance = window.PharmaTabulator.fromDom({
                        el: this,
                        key: 'embedded-report-' + index,
                        pageLength: 10,
                        searchPlaceholder: 'Search report...',
                        minWidth: 100
                    });
                    if (tableInstance) {
                        window.setTimeout(function () {
                            try { tableInstance.redraw(true); } catch (e) {}
                        }, 60);
                    }
                });
            }
        }

        $('#reportViewerModal').on('shown.bs.modal', function () {
            $('#reportViewerBody .tabulator').each(function () {
                var key = $(this).closest('.tabulator-wrapper').attr('data-table-key');
                if (key && window.PharmaTabulator) {
                    var table = window.PharmaTabulator.get(key);
                    if (table) {
                        try { table.redraw(true); } catch (e) {}
                    }
                }
            });
        });

        function loadReport(url) {
            var body = $('#reportViewerBody');
            if (window.PharmaTabulator) {
                window.PharmaTabulator.destroyLocalAll();
            }
            body.html('<div class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin mr-2"></i>Loading report...</div>');
            $.get(url + (url.indexOf('?') === -1 ? '?' : '&') + 'embedded=1')
                .done(function (html) { body.html(html); initializeReportTable(); })
                .fail(function () { body.html('<div class="alert alert-danger mb-0">Unable to load this report. Please try again.</div>'); });
        }

        $(document).on('click', '.open-report-button', function () {
            var button = $(this);
            $('#reportViewerTitle').text(button.data('report-title'));
            $('#reportViewerModal').modal('show');
            loadReport(button.data('report-url'));
        });

        $(document).on('click', '.open-generation-report', function () {
            var button = $(this);
            $('#reportViewerTitle').text(button.data('report-title'));
            var url = button.data('report-url');

            function openGenerationReport() {
                $('#reportViewerModal').modal('show');
                loadReport(url);
            }

            var generationModal = $('#reportGenerationsModal');
            generationModal.off('hidden.bs.modal.reportGeneration').one('hidden.bs.modal.reportGeneration', openGenerationReport);
            generationModal.modal('hide');
            if (!generationModal.hasClass('show')) {
                window.setTimeout(openGenerationReport, 0);
            }
        });

        $(document).on('submit', '#reportViewerBody .report-filter-form', function (event) {
            event.preventDefault();
            var form = $(this);
            loadReport(form.attr('action') + '?' + form.serialize());
        });

        $(document).on('submit', '#reportViewerBody .generation-filter-form', function (event) {
            event.preventDefault();
            var form = $(this);
            var body = $('#reportViewerBody');
            body.find('.generation-filter-form button[type="submit"]').prop('disabled', true);
            $.post(form.attr('action') + '?embedded=1', form.serialize())
                .done(function (html) { body.html(html); initializeReportTable(); })
                .fail(function () { body.prepend('<div class="alert alert-danger">Unable to generate this report. Check both dates and try again.</div>'); });
        });
    })();
</script>
@endpush
