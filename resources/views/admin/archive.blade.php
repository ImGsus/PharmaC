@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Archive</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Archive</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="archive-table" class="table table-striped table-bordered table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Deleted Date</th>
                                <th>Related Records</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archivedSuppliers as $archive)
                                <tr>
                                    <td>{{ $archive->supplier_name }}</td>
                                    <td>{{ $archive->archived_at->format('d M, Y H:i') }}</td>
                                    @if($archive->is_generic ?? false)
                                        <td>Archived {{ class_basename($archive->data['type'] ?? 'record') }}</td>
                                    @elseif(($archive->data['type'] ?? null) === 'product')
                                        <td>1 product</td>
                                    @else
                                        <td>{{ count($archive->data['purchases'] ?? []) }} purchase(s)</td>
                                    @endif
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle archive-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Archive actions">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if(($archive->is_generic ?? false) && in_array($archive->model_type ?? '', [\App\Models\Product::class, \App\Models\Sale::class], true))
                                                <form action="{{ route('backup.archive.generic.recover', $archive->id) }}" method="post">
                                                    @csrf
                                                    <button class="dropdown-item text-success" type="submit"><i class="fe fe-rotate-ccw mr-2"></i>Restore</button>
                                                </form>
                                                @endif
                                                @if($archive->is_generic ?? false)
                                                <form action="{{ route('backup.archive.generic.destroy', $archive->id) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit"><i class="fe fe-trash mr-2"></i>Delete Forever</button>
                                                </form>
                                                @else
                                                <form action="{{ route('backup.archive.recover', $archive) }}" method="post">
                                                    @csrf
                                                    <button class="dropdown-item text-success" type="submit"><i class="fe fe-rotate-ccw mr-2"></i>Recover</button>
                                                </form>
                                                <form action="{{ route('backup.archive.destroy', $archive) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit"><i class="fe fe-trash mr-2"></i>Delete Forever</button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No archived data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
        if (!window.PharmaTabulator) return;

        window.PharmaTabulator.fromDom({
            el: 'archive-table',
            key: 'archive-table',
            preserveHtml: true,
            pageLength: 10
        });
    });
</script>
@endpush


