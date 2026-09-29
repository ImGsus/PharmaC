@push('page-css')
    <!-- DataTables -->
    <link href="{{asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    @if (request()->routeIs('sales.report', 'purchases.report'))
        <link href="{{asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    @endif
    <!-- Responsive datatable examples -->
    <link href="{{asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }

        .table-responsive > table,
        table.datatable {
            width: 100% !important;
            max-width: 100%;
            table-layout: fixed;
        }

        .table-responsive > table th,
        .table-responsive > table td,
        table.datatable th,
        table.datatable td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-responsive > table:has(th.action-btn) th:last-child,
        .table-responsive > table:has(th.action-btn) td:last-child,
        table.datatable:has(th.action-btn) th:last-child,
        table.datatable:has(th.action-btn) td:last-child {
            width: 90px !important;
            min-width: 90px;
            text-align: center;
            white-space: nowrap;
        }
    </style>
@endpush

@push('page-js')
    <!-- Required datatable js -->
    <script src="{{asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js')}}" data-turbo-eval="false"></script>
    <script src="{{asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}" data-turbo-eval="false"></script>
    @if (request()->routeIs('sales.report', 'purchases.report'))
        <!-- Buttons examples -->
        <script src="{{asset('assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/jszip/jszip.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/pdfmake/build/pdfmake.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/pdfmake/build/vfs_fonts.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/datatables.net-buttons/js/buttons.print.min.js')}}" data-turbo-eval="false"></script>
        <script src="{{asset('assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')}}" data-turbo-eval="false"></script>
    @endif
    <!-- Responsive examples -->
    <script src="{{asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}" data-turbo-eval="false"></script>
    <script src="{{asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}" data-turbo-eval="false"></script>
    <script>
        $.fn.dataTable.defaults.searchDelay = 350;
        $.fn.dataTable.defaults.autoWidth = false;
        $.fn.dataTable.defaults.language.search = '<span class="sr-only">Search:</span>_INPUT_';

        $(document).on('init.dt', function (event, settings) {
            var table = new $.fn.dataTable.Api(settings);
            window.setTimeout(function () {
                table.columns.adjust();
            }, 0);
        });

        function adjustPharmacyDataTables() {
            if (!$.fn.DataTable) return;

            $.fn.DataTable.tables({ api: true }).columns.adjust();
        }

        $(window).on('resize', adjustPharmacyDataTables);
        document.addEventListener('turbo:load', adjustPharmacyDataTables);
    </script>
    @endpush

