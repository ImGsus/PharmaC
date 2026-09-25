<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemperatureReading;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class TemperatureController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $readings = TemperatureReading::query();
            // Keep newest-first as the default until the user sorts a column,
            // otherwise the base latest() would neutralize the requested sort.
            if (! $request->filled('order')) {
                $readings->latest('recorded_at');
            }

            return DataTables::of($readings)
                ->editColumn('recorded_at', function ($reading) {
                    return optional($reading->recorded_at)->format('d M Y H:i');
                })
                ->editColumn('temperature', function ($reading) {
                    return $reading->temperature.'°';
                })
                ->editColumn('source', function ($reading) {
                    return ucfirst((string) $reading->source);
                })
                ->addColumn('safe_range', function ($reading) {
                    return ($reading->minimum_temperature ?? '-').' to '.($reading->maximum_temperature ?? '-');
                })
                ->addColumn('status', function ($reading) {
                    return '<span class="badge badge-'.($reading->is_out_of_range ? 'danger' : 'success').'">'
                        .($reading->is_out_of_range ? 'Out of range' : 'Within range').'</span>';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('admin.temperature.index', [
            'title' => 'temperature monitoring',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location' => 'required|string|max:150',
            'temperature' => 'required|numeric|between:-100,200',
            'minimum_temperature' => 'nullable|numeric|between:-100,200',
            'maximum_temperature' => 'nullable|numeric|between:-100,200',
            'recorded_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        $data['recorded_by'] = $request->user()->id;
        $data['source'] = 'manual';
        TemperatureReading::create($data);

        return back()->with(notify('Temperature reading recorded'));
    }
}