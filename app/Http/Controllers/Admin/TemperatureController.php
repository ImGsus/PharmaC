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
                ->addColumn('action', function ($reading) {
                    if ($reading->source !== 'manual') {
                        return '';
                    }

                    $location = htmlspecialchars($reading->location, ENT_QUOTES, 'UTF-8');
                    $recordedAt = optional($reading->recorded_at)->format('Y-m-d\TH:i');
                    $edit = '<button type="button" class="dropdown-item temperature-edit-btn"'
                        .' data-route="'.route('temperature.update', $reading).'"'
                        .' data-location="'.$location.'"'
                        .' data-temperature="'.$reading->temperature.'"'
                        .' data-minimum="'.$reading->minimum_temperature.'"'
                        .' data-maximum="'.$reading->maximum_temperature.'"'
                        .' data-recorded-at="'.$recordedAt.'"'
                        .' data-notes="'.htmlspecialchars($reading->notes ?? '', ENT_QUOTES, 'UTF-8').'">'
                        .'<i class="fas fa-edit mr-2"></i>Edit</button>';
                    $delete = '<a data-id="'.$reading->id.'" data-route="'.route('temperature.destroy', $reading).'"'
                        .' href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger">'
                        .'<i class="fas fa-trash mr-2"></i>Delete</a>';

                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle row-action-modal-trigger"'
                        .' data-action-title="Temperature Actions" data-context-label="Location" data-context-value="'.$location.'"'
                        .' aria-haspopup="true" aria-expanded="false" aria-label="Temperature actions"><i class="fa fa-ellipsis-v"></i></button>'
                        .'<div class="dropdown-menu dropdown-menu-right">'.$edit.'<div class="dropdown-divider"></div>'.$delete.'</div></div>';
                })
                ->rawColumns(['status', 'action'])
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
        if (($data['minimum_temperature'] ?? null) !== null
            && ($data['maximum_temperature'] ?? null) !== null
            && $data['minimum_temperature'] > $data['maximum_temperature']) {
            return back()->withErrors(['maximum_temperature' => 'The maximum temperature must be at least the minimum temperature.'])->withInput();
        }
        $data['recorded_by'] = $request->user()->id;
        $data['source'] = 'manual';
        TemperatureReading::create($data);

        return back()->with(notify('Temperature reading recorded'));
    }

    public function update(Request $request, TemperatureReading $reading)
    {
        abort_unless($reading->source === 'manual', 403);

        $data = $request->validate([
            'location' => 'required|string|max:150',
            'temperature' => 'required|numeric|between:-100,200',
            'minimum_temperature' => 'nullable|numeric|between:-100,200',
            'maximum_temperature' => 'nullable|numeric|between:-100,200',
            'recorded_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (($data['minimum_temperature'] ?? null) !== null && ($data['maximum_temperature'] ?? null) !== null
            && $data['minimum_temperature'] > $data['maximum_temperature']) {
            return back()->withErrors(['maximum_temperature' => 'The maximum temperature must be at least the minimum temperature.'])->withInput();
        }

        $reading->update($data);

        return back()->with(notify('Temperature reading updated'));
    }

    public function destroy(TemperatureReading $reading)
    {
        abort_unless($reading->source === 'manual', 403);
        $reading->delete();

        return response()->json(['success' => true]);
    }
}