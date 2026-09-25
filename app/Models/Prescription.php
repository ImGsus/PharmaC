<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'submitted_by', 'verified_by', 'prescription_number', 'patient_name',
        'prescriber_name', 'issued_at', 'document_path', 'status',
        'verification_notes', 'verified_at',
    ];

    protected $casts = ['issued_at' => 'date', 'verified_at' => 'datetime'];

    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
}