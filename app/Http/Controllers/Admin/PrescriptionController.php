<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\OrganizedFileStorage;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $prescriptions = Prescription::query();
            // Keep newest-first as the default until the user sorts a column,
            // otherwise the base latest() would neutralize the requested sort.
            if (! $request->filled('order')) {
                $prescriptions->latest();
            }

            return DataTables::of($prescriptions)
                ->editColumn('created_at', function ($prescription) {
                    return optional($prescription->created_at)->format('d M Y');
                })
                ->editColumn('prescriber_name', function ($prescription) {
                    return $prescription->prescriber_name ?: '-';
                })
                ->editColumn('prescription_number', function ($prescription) {
                    return $prescription->prescription_number ?: '-';
                })
                ->editColumn('status', function ($prescription) {
                    $badge = $prescription->status === 'approved'
                        ? 'success'
                        : ($prescription->status === 'rejected' ? 'danger' : 'warning');

                    return '<span class="badge badge-'.$badge.'">'.ucfirst((string) $prescription->status).'</span>';
                })
                ->addColumn('verification', function ($prescription) {
                    $status = (string) $prescription->status;
                    $options = [
                        'pending' => 'Pending',
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ];

                    $select = '<select name="status" class="form-control form-control-sm mr-2">';
                    foreach ($options as $value => $label) {
                        $select .= '<option value="'.$value.'"'.($status === $value ? ' selected' : '').'>'.$label.'</option>';
                    }
                    $select .= '</select>';

                    // Same inline form the old table rendered (Blade @csrf/@method equivalents).
                    return '<form method="POST" action="'.route('prescriptions.status', $prescription).'" class="form-inline">'
                        .'<input type="hidden" name="_token" value="'.csrf_token().'">'
                        .'<input type="hidden" name="_method" value="PATCH">'
                        .$select
                        .'<input name="verification_notes" class="form-control form-control-sm mr-2" placeholder="Notes">'
                        .'<button class="btn btn-sm btn-outline-primary">Save</button>'
                        .'</form>';
                })
                ->rawColumns(['status', 'verification'])
                ->make(true);
        }

        return view('admin.prescriptions.index', [
            'title' => 'prescription verification',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription_number' => 'nullable|string|max:100',
            'patient_name' => 'required|string|max:150',
            'prescriber_name' => 'nullable|string|max:150',
            'issued_at' => 'nullable|date',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = 'system/prescriptions/'.app(OrganizedFileStorage::class)->store($request->file('document'), 'prescriptions');
        }
        unset($data['document']);
        $data['submitted_by'] = $request->user()->id;
        $data['status'] = 'pending';
        Prescription::create($data);

        return back()->with(notify('Prescription submitted for verification'));
    }

    public function updateStatus(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'verification_notes' => 'nullable|string|max:2000',
        ]);
        $data['verified_by'] = $request->user()->id;
        $data['verified_at'] = now();
        $prescription->update($data);

        return back()->with(notify('Prescription verification updated'));
    }
}