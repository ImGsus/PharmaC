@push('page-css')
    <!-- Tabulator (shared light/dark tables) -->
    <link rel="stylesheet" href="{{asset('assets/plugins/tabulator/css/tabulator.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/tabulator-theme.css')}}">
@endpush

@push('page-js')
    <script src="{{asset('assets/plugins/tabulator/js/tabulator.min.js')}}" data-turbo-eval="false"></script>
    <script src="{{asset('assets/js/tabulator-server.js')}}" data-turbo-eval="false"></script>
@endpush