@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
    .prescription-intake-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(260px, .8fr); gap: 24px; }
    .prescription-modal-fields { display: grid; gap: 0 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .prescription-modal-fields .form-group { min-width: 0; }
    .prescription-modal-fields .form-control { min-height: 42px; }
    .prescription-modal-fields .prescription-rx-field { grid-column: 1 / -1; }
    .prescription-review-layout { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); }
    .prescription-ocr-viewer { align-items: flex-start; background: #101820; border: 1px solid #394553; border-radius: 5px; display: flex; justify-content: center; max-height: 68vh; min-height: 240px; overflow: auto; padding: 8px; }
    .prescription-ocr-image-stage { display: inline-block; line-height: 0; max-width: 100%; position: relative; }
    .prescription-ocr-image { display: block; height: auto; max-height: 64vh; max-width: 100%; width: auto; }
    .prescription-ocr-word-box { background: rgba(0, 168, 132, .16); border: 1px solid rgba(0, 168, 132, .8); box-sizing: border-box; cursor: help; margin: 0; min-height: 2px; min-width: 2px; padding: 0; position: absolute; z-index: 1; }
    .prescription-ocr-word-box:hover,
    .prescription-ocr-word-box:focus { background: rgba(255, 193, 7, .35); border-color: #ffc107; outline: 2px solid #ffc107; z-index: 2; }
    .prescription-ocr-empty { color: #a8b3c4; font-size: 13px; line-height: 1.5; margin: auto; text-align: center; }
    .prescription-document-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
    .prescription-document-input { display: none; }
    .prescription-image-source { flex: 0 0 auto; }
    .prescription-image-source .dropdown-menu { min-width: 180px; padding: 6px; }
    .prescription-image-source .dropdown-menu button,
    .prescription-image-source .dropdown-menu label { background: transparent; border: 0; color: #344054; cursor: pointer; display: block; font-weight: 400; margin: 0; padding: 9px 12px; text-align: left; width: 100%; }
    .prescription-image-source .dropdown-menu button:hover,
    .prescription-image-source .dropdown-menu label:hover { background: #f1f5f9; }
    .prescription-document-file-name { color: #64748b; flex: 1 1 180px; font-size: 13px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .prescription-review-note { color: #64748b; font-size: 13px; margin: 8px 0 0; }
    .prescription-preview { align-items: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; color: #64748b; display: flex; justify-content: center; min-height: 280px; overflow: hidden; padding: 12px; text-align: center; }
    .prescription-preview img { display: none; max-height: 360px; max-width: 100%; object-fit: contain; }
    .prescription-preview.has-image img { display: block; }
    .prescription-preview.has-image .prescription-preview-placeholder { display: none; }
    .prescription-ocr-confidence { color: #64748b; font-size: 12px; font-weight: 500; }
    .prescription-modal-note { color: #64748b; font-size: 13px; }
    .prescription-analysis-modal .modal-body { line-height: 1.5; padding: 24px; }
    .prescription-analysis-modal .modal-footer { gap: 8px; padding: 16px 24px; }
    .prescription-analysis-modal .prescription-analysis-choice { align-items: flex-start; border-radius: 8px; box-sizing: border-box; display: flex; flex-direction: column; height: auto; justify-content: center; line-height: 1.5; margin: 0 0 12px; min-height: 76px; padding: 12px 16px; white-space: normal; }
    .prescription-analysis-modal .prescription-analysis-choice strong { display: block; margin-bottom: 4px; }
    .prescription-analysis-modal .prescription-analysis-choice small { display: block; font-size: 13px; line-height: 1.5; white-space: normal; }
    .prescription-analysis-modal .prescription-analysis-intro { margin-bottom: 18px; }
    .prescription-analysis-modal .prescription-credential-list { display: grid; gap: 10px; margin: 16px 0; }
    .prescription-analysis-modal .prescription-credential-choice { align-items: center; display: flex; justify-content: space-between; padding: 14px 16px; text-align: left; }
    .prescription-analysis-modal .prescription-credential-row { align-items: stretch; display: flex; gap: 8px; }
    .prescription-analysis-modal .prescription-credential-row .prescription-credential-choice { flex: 1 1 auto; min-width: 0; }
    .prescription-analysis-modal .prescription-credential-delete { flex: 0 0 auto; }
    .prescription-analysis-modal .prescription-credential-empty { border: 1px dashed #94a3b8; border-radius: 8px; color: #64748b; margin: 16px 0; padding: 16px; }
    .prescription-analysis-modal .prescription-key-warning { line-height: 1.55; margin-bottom: 16px; padding: 14px 16px; }
    .prescription-analysis-modal .prescription-analysis-cancel,
    body.dark-mode .prescription-analysis-modal .prescription-analysis-cancel { background: transparent !important; border-color: #64748b !important; color: #cbd5e1 !important; }
    .prescription-analysis-modal .prescription-analysis-cancel:hover,
    .prescription-analysis-modal .prescription-analysis-cancel:focus { background: #334155 !important; color: #fff !important; }
    body:not(.dark-mode) .prescription-analysis-modal .prescription-analysis-cancel { color: #475569 !important; }
    body:not(.dark-mode) .prescription-analysis-modal .prescription-analysis-cancel:hover,
    body:not(.dark-mode) .prescription-analysis-modal .prescription-analysis-cancel:focus { background: #e2e8f0 !important; color: #1e293b !important; }
    body.dark-mode .prescription-analysis-modal .prescription-credential-empty { color: #cbd5e1; }
    .prescription-match-list { display: grid; gap: 8px; }
    .prescription-match { align-items: center; border: 1px solid #e2e8f0; border-radius: 5px; display: flex; gap: 12px; justify-content: space-between; padding: 10px 12px; }
    .prescription-match-name { font-weight: 600; }
    .prescription-match-meta { color: #64748b; font-size: 12px; }
    .prescription-patient-detail-link { color: #10b981; font-weight: 600; text-decoration: none; }
    .prescription-patient-detail-link:hover,
    .prescription-patient-detail-link:focus { color: #34d399; text-decoration: underline; }
    #prescription-table th.actions,
    #prescription-table td.actions { box-sizing: border-box; min-width: 90px; text-align: center; width: 90px; }
    .prescription-action-button,
    .prescription-action-button:hover,
    .prescription-action-button:focus,
    .prescription-action-button.show { box-shadow: 0 3px 2px -2px rgba(96, 165, 250, .8), 0 4px 3px -2px rgba(96, 165, 250, .8) !important; }
    .prescription-action-modal .modal-dialog { max-width: 420px; width: min(420px, calc(100vw - 32px)); }
    .prescription-action-modal .prescription-action-heading { min-width: 0; }
    .prescription-action-modal .prescription-action-patient { color: #64748b; display: block; font-size: 13px; font-weight: 500; margin-top: 4px; overflow-wrap: anywhere; }
    .prescription-action-modal .modal-body { max-height: min(65vh, 480px); overflow-y: auto; padding: 8px 0; }
    .prescription-action-modal .prescription-action-modal-content { min-width: 0; }
    .prescription-action-modal .dropdown-item { padding: 10px 20px; white-space: normal; }
    .prescription-action-modal .prescription-action-form { min-width: 0; }
    .prescription-action-modal .prescription-action-form .dropdown-item { padding-left: 20px; }
    .prescription-action-modal .prescription-action-form > div { padding: 8px 16px 4px; }
    .prescription-action-form { min-width: 220px; }
    .prescription-action-form .dropdown-item { background: transparent; border: 0; text-align: left; width: 100%; }
    .prescription-action-form .dropdown-item:hover,
    .prescription-action-form .dropdown-item:focus { background: #f1f5f9; }
    body.dark-mode .prescription-review-note,
    body.dark-mode .prescription-match-meta,
    body.dark-mode .prescription-ocr-confidence,
    body.dark-mode .prescription-modal-note { color: #a8b3c4; }
    body.dark-mode .prescription-preview,
    body.dark-mode .prescription-modal-fields .form-control { background: #111827; border-color: #475569; color: #dbe4f0; }
    body.dark-mode .prescription-match { border-color: #475569; }
    body.dark-mode .prescription-image-source > .btn { border-color: #69d9aa; color: #69d9aa; }
    body.dark-mode .prescription-image-source > .btn:hover,
    body.dark-mode .prescription-image-source > .btn:focus { background-color: #69d9aa; color: #10231d; }
    body.dark-mode .prescription-image-source .dropdown-menu { background: #252b33; border-color: #475569; }
    body.dark-mode .prescription-image-source .dropdown-menu button,
    body.dark-mode .prescription-image-source .dropdown-menu label { color: #e2e8f0; }
    body.dark-mode .prescription-image-source .dropdown-menu button:hover,
    body.dark-mode .prescription-image-source .dropdown-menu label:hover { background: #334155; color: #69d9aa; }
    body.dark-mode .prescription-document-file-name { color: #cbd5e1; }
    .prescription-camera-modal { align-items: center; background: rgba(15, 23, 42, .78); display: none; inset: 0; justify-content: center; padding: 20px; position: fixed; z-index: 1060; }
    .prescription-camera-modal.is-open { display: flex; }
    .prescription-camera-dialog { background: #fff; border-radius: 8px; box-shadow: 0 12px 40px rgba(15, 23, 42, .3); max-height: 100%; overflow: auto; width: min(640px, 100%); }
    .prescription-camera-header,
    .prescription-camera-footer { align-items: center; display: flex; gap: 8px; padding: 14px 18px; }
    .prescription-camera-header { border-bottom: 1px solid #e5e7eb; justify-content: space-between; }
    .prescription-camera-footer { border-top: 1px solid #e5e7eb; justify-content: flex-end; }
    .prescription-camera-close { background: transparent; border: 0; color: #64748b; cursor: pointer; font-size: 28px; line-height: 1; padding: 0; }
    .prescription-camera-body { padding: 18px; text-align: center; }
    .prescription-camera-body video,
    .prescription-camera-body img { background: #111827; border-radius: 6px; display: block; max-height: 60vh; object-fit: contain; width: 100%; }
    .prescription-camera-body img { display: none; }
    .prescription-camera-message { color: #64748b; margin: 12px 0 0; }
    #prescription-camera-canvas,
    #prescription-camera-retake,
    #prescription-camera-use { display: none; }
    body.dark-mode .prescription-camera-dialog { background: #252b33; color: #e2e8f0; }
    body.dark-mode .prescription-camera-header,
    body.dark-mode .prescription-camera-footer { border-color: #475569; }
    body.dark-mode .prescription-camera-message { color: #a8b3c4; }
    body.dark-mode .prescription-action-button,
    body.dark-mode .prescription-action-button:hover,
    body.dark-mode .prescription-action-button:focus,
    body.dark-mode .prescription-action-button.show { box-shadow: 0 3px 2px -2px rgba(134, 239, 172, .8), 0 4px 3px -2px rgba(134, 239, 172, .8) !important; }
    body.dark-mode .prescription-action-form .dropdown-item:hover,
    body.dark-mode .prescription-action-form .dropdown-item:focus { background: #334155; color: #69d9aa; }
    body.dark-mode .prescription-action-modal .modal-content { background: #252b33; color: #e2e8f0; }
    body.dark-mode .prescription-action-modal .modal-header { border-color: #475569; }
    body.dark-mode .prescription-action-modal .close { color: #e2e8f0; text-shadow: none; }
    body.dark-mode .prescription-action-modal .prescription-action-patient { color: #a8b3c4; }
    @media (max-width: 767.98px) {
        .prescription-intake-grid,
        .prescription-review-layout,
        .prescription-modal-fields { grid-template-columns: minmax(0, 1fr); }
        .prescription-modal-fields .prescription-rx-field { grid-column: auto; }
        .prescription-preview { min-height: 180px; }
        .prescription-ocr-viewer { align-items: center; flex-direction: column; }
        .prescription-ocr-image-stage { margin-left: auto; margin-right: auto; }
        .prescription-ocr-empty { flex: 0 0 auto; width: 100%; }
    }
    @media (max-width: 576px) {
        .prescription-camera-footer { flex-wrap: wrap; }
    }
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Prescription Verification</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Prescriptions</li>
    </ul>
</div>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header"><h4 class="card-title">Submit Prescription</h4></div>
    <div class="card-body">
        <div class="prescription-intake-grid">
            <div>
                <form method="POST" action="{{ route('prescriptions.store') }}" enctype="multipart/form-data" id="prescription-submit-form">
                    @csrf
                    <input id="prescription-number" type="hidden" name="prescription_number">
                    <input id="prescription-patient" type="hidden" name="patient_name">
                    <input id="prescription-prescriber" type="hidden" name="prescriber_name">
                    <input id="prescription-issued" type="hidden" name="issued_at">
                    <input id="prescription-ocr-details" type="hidden" name="ocr_details">

                    <div class="form-group mb-2">
                        <label for="prescription-document">Prescription Document</label>
                        <div class="prescription-document-actions">
                            <input id="prescription-document" type="file" name="document" accept="image/jpeg,image/png,image/webp,application/pdf" class="prescription-document-input">
                            <div class="prescription-image-source dropdown">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-image mr-1" aria-hidden="true"></i> Add Photo
                                </button>
                                <div class="dropdown-menu">
                                    <label for="prescription-document"><i class="fas fa-folder-open mr-1" aria-hidden="true"></i> Choose file</label>
                                    <button type="button" id="prescription-camera-button"><i class="fas fa-camera mr-1" aria-hidden="true"></i> Take a photo</button>
                                </div>
                            </div>
                            <span id="prescription-document-file-name" class="prescription-document-file-name">No file chosen</span>
                            <button type="button" id="prescription-analyze-button" class="btn btn-outline-primary" disabled>
                                <i class="fas fa-search mr-1" aria-hidden="true"></i> Analyze Image
                            </button>
                            <button type="submit" class="btn btn-primary">Submit for Verification</button>
                        </div>
                        <p class="prescription-review-note">OCR suggestions are drafts only. Confirm all medicine names and instructions against the original before verification.</p>
                    </div>
                </form>

                <div id="prescription-camera-modal" class="prescription-camera-modal" aria-hidden="true">
                    <div class="prescription-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="prescription-camera-title">
                        <div class="prescription-camera-header">
                            <h5 id="prescription-camera-title" class="mb-0">Take prescription photo</h5>
                            <button type="button" class="prescription-camera-close" id="prescription-camera-close" aria-label="Close camera">&times;</button>
                        </div>
                        <div class="prescription-camera-body">
                            <video id="prescription-camera-video" autoplay playsinline></video>
                            <canvas id="prescription-camera-canvas"></canvas>
                            <img id="prescription-camera-preview" alt="Captured prescription preview">
                            <p id="prescription-camera-message" class="prescription-camera-message" role="status" aria-live="polite"></p>
                        </div>
                        <div class="prescription-camera-footer">
                            <button type="button" class="btn btn-light" id="prescription-camera-cancel">Close</button>
                            <button type="button" class="btn btn-primary" id="prescription-camera-capture">Capture</button>
                            <button type="button" class="btn btn-secondary" id="prescription-camera-retake">Retake</button>
                            <button type="button" class="btn btn-success" id="prescription-camera-use">Use photo</button>
                        </div>
                    </div>
                </div>
                <div class="modal fade prescription-analysis-modal" id="prescription-analysis-mode-modal" tabindex="-1" role="dialog" aria-labelledby="prescription-analysis-mode-title" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="prescription-analysis-mode-title">Choose analysis method</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="prescription-analysis-method-choices" class="prescription-analysis-panel">
                                    <p class="prescription-analysis-intro">Choose how to read this prescription image.</p>
                                    <button type="button" id="prescription-use-built-in-ocr" class="btn btn-outline-primary btn-block text-left prescription-analysis-choice">
                                        <strong>Built-in OCR</strong><small>Runs on this server, works offline, and may be less accurate.</small>
                                    </button>
                                    <button type="button" id="prescription-use-ai-cloud" class="btn btn-outline-success btn-block text-left prescription-analysis-choice">
                                        <strong>AI Cloud</strong><small>Choose an online AI provider for image analysis.</small>
                                    </button>
                                </div>
                                <div id="prescription-ai-cloud-panel" class="prescription-analysis-panel d-none">
                                    <p class="prescription-analysis-intro">Select an AI Cloud provider.</p>
                                    <button type="button" id="prescription-select-gemini" class="btn btn-outline-success btn-block text-left prescription-analysis-choice">
                                        <strong>Google Gemini</strong><small>Use a saved Gemini API key profile.</small>
                                    </button>
                                </div>
                                <div id="prescription-gemini-profiles-panel" class="prescription-analysis-panel d-none">
                                    <p class="prescription-analysis-intro">Choose a saved Google Gemini profile to start analyzing.</p>
                                    <div id="prescription-gemini-profile-list" class="prescription-credential-list"></div>
                                    <p id="prescription-gemini-profile-empty" class="prescription-credential-empty d-none">No Gemini profiles yet. Create one to securely save a key for your account.</p>
                                    <button type="button" id="prescription-create-gemini-profile" class="btn btn-outline-primary">
                                        <i class="fas fa-plus mr-1" aria-hidden="true"></i> Create API key profile
                                    </button>
                                    <div id="prescription-gemini-profile-feedback" class="alert alert-danger d-none mt-3" role="alert"></div>
                                </div>
                                <div id="prescription-gemini-create-panel" class="prescription-analysis-panel d-none">
                                    <div class="alert alert-warning prescription-key-warning">
                                        Your key is encrypted before it is saved and is only available to your account. Google will receive the prescription image when you select this profile to analyze.
                                    </div>
                                    <div class="alert alert-danger prescription-key-warning">
                                        Google may use data submitted through the free tier to improve its products. Avoid sending identifiable patient information unless your privacy, consent, and regulatory requirements allow it. Built-in OCR keeps image analysis on your server.
                                    </div>
                                    <div class="form-group">
                                        <label for="prescription-gemini-profile-name">Profile name</label>
                                        <input type="text" id="prescription-gemini-profile-name" class="form-control" maxlength="80" autocomplete="off" placeholder="e.g. My Gemini key">
                                    </div>
                                    <div class="form-group">
                                        <label for="prescription-gemini-api-key">Gemini API key</label>
                                        <input type="password" id="prescription-gemini-api-key" class="form-control" autocomplete="off" spellcheck="false">
                                        <small class="form-text text-muted">Use this page over HTTPS. The key is stored encrypted on the app server and is never displayed again.</small>
                                    </div>
                                    <div id="prescription-gemini-key-feedback" class="alert alert-danger d-none" role="alert"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary prescription-analysis-cancel" data-dismiss="modal">Cancel</button>
                                <button type="button" id="prescription-analysis-back" class="btn btn-outline-secondary prescription-analysis-cancel d-none">Back</button>
                                <button type="button" id="prescription-save-gemini-profile" class="btn btn-success d-none">Create profile</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="prescription-analysis-feedback" class="alert d-none" role="status" aria-live="polite"></div>
            </div>

            <aside>
                <h5 class="mb-2">Document preview</h5>
                <div id="prescription-preview" class="prescription-preview">
                    <img id="prescription-preview-image" alt="Selected prescription preview">
                    <div id="prescription-preview-placeholder" class="prescription-preview-placeholder">Choose a prescription image or PDF to preview it here.</div>
                </div>
            </aside>
        </div>
    </div>
</div>

<div class="modal fade" id="prescription-review-modal" tabindex="-1" role="dialog" aria-labelledby="prescription-review-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="prescription-review-title">Review prescription details</h5>
                    <span id="prescription-ocr-confidence" class="prescription-ocr-confidence"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="prescription-review-layout">
                    <div>
                        <strong class="d-block mb-2">Selected prescription</strong>
                        <div class="prescription-ocr-viewer">
                            <div id="prescription-ocr-image-stage" class="prescription-ocr-image-stage d-none">
                                <img id="prescription-ocr-image" class="prescription-ocr-image" alt="Selected prescription with OCR word regions">
                                <div id="prescription-ocr-word-overlay"></div>
                            </div>
                            <p id="prescription-ocr-empty" class="prescription-ocr-empty">Choose an image to inspect its recognized text regions.</p>
                        </div>
                        <p id="prescription-review-image-note" class="prescription-modal-note mt-2">Hover or focus a highlighted word to inspect what OCR read there.</p>
                    </div>
                    <div>
                        <p class="prescription-modal-note">Check and correct each extracted value against the original prescription. OCR suggestions are not verified medical instructions.</p>
                        <div class="prescription-modal-fields">
                            <div class="form-group"><label for="ocr-patient-name">Patient Name</label><input id="ocr-patient-name" data-prescription-field="patient_name" class="form-control"></div>
                            <div class="form-group"><label for="ocr-age">Age</label><input id="ocr-age" data-prescription-field="age" class="form-control"></div>
                            <div class="form-group"><label for="ocr-birth-date">Birth Date</label><input id="ocr-birth-date" data-prescription-field="birth_date" class="form-control"></div>
                            <div class="form-group"><label for="ocr-consult-date">Consult Date</label><input id="ocr-consult-date" data-prescription-field="consult_date" class="form-control"></div>
                            <div class="form-group"><label for="ocr-patient-type">Patient Type</label><input id="ocr-patient-type" data-prescription-field="patient_type" class="form-control"></div>
                            <div class="form-group"><label for="ocr-patient-number">Patient No</label><input id="ocr-patient-number" data-prescription-field="patient_no" class="form-control"></div>
                            <div class="form-group"><label for="ocr-consultation-number">Consultation No</label><input id="ocr-consultation-number" data-prescription-field="consultation_no" class="form-control"></div>
                            <div class="form-group"><label for="ocr-appointment-date">Appointment Date</label><input id="ocr-appointment-date" data-prescription-field="appointment_date" class="form-control"></div>
                            <div class="form-group prescription-rx-field"><label for="ocr-rx">Rx</label><textarea id="ocr-rx" data-prescription-field="rx" class="form-control" rows="5"></textarea></div>
                        </div>
                        <div id="prescription-modal-feedback" class="alert d-none" role="status" aria-live="polite"></div>
                        <div class="mt-3">
                            <strong class="d-block mb-2">Possible catalog matches</strong>
                            <div id="prescription-match-list" class="prescription-match-list"></div>
                            <button type="button" id="prescription-rematch-button" class="btn btn-outline-primary btn-sm mt-2"><i class="fas fa-sync-alt mr-1" aria-hidden="true"></i> Recheck catalog</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="prescription-apply-details" class="btn btn-primary">Use reviewed details</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive"><div id="prescription-table" class="tabulator-table-wrap"></div></div>
    </div>
</div>

<div class="modal fade prescription-action-modal" id="prescription-action-modal" tabindex="-1" role="dialog" aria-labelledby="prescription-action-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="prescription-action-heading">
                    <h5 class="modal-title" id="prescription-action-modal-title">Prescription actions</h5>
                    <span class="prescription-action-patient">Patient &rarr; <span id="prescription-action-patient-name"></span></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="prescription-action-modal-content" class="prescription-action-modal-content"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
(function () {
    var fileInput = document.getElementById('prescription-document');
    var analyzeButton = document.getElementById('prescription-analyze-button');
    var feedback = document.getElementById('prescription-analysis-feedback');
    var modalFeedback = document.getElementById('prescription-modal-feedback');
    var matchList = document.getElementById('prescription-match-list');
    var rematchButton = document.getElementById('prescription-rematch-button');
    var applyDetailsButton = document.getElementById('prescription-apply-details');
    var preview = document.getElementById('prescription-preview');
    var previewImage = document.getElementById('prescription-preview-image');
    var previewPlaceholder = document.getElementById('prescription-preview-placeholder');
    var modalImage = document.getElementById('prescription-ocr-image');
    var imageStage = document.getElementById('prescription-ocr-image-stage');
    var wordOverlay = document.getElementById('prescription-ocr-word-overlay');
    var imageEmpty = document.getElementById('prescription-ocr-empty');
    var previewUrl = null;
    var correctedPreviewUrl = null;
    var cameraPreviewUrl = null;
    var cameraStream = null;
    var cameraRequest = 0;
    var capturedPhoto = null;
    var analysisReady = false;
    var doctorName = '';
    var licenseNumber = '';
    var modal = $('#prescription-review-modal');
    var prescriptionFields = Array.from(document.querySelectorAll('[data-prescription-field]'));
    var fileName = document.getElementById('prescription-document-file-name');
    var cameraButton = document.getElementById('prescription-camera-button');
    var cameraModal = document.getElementById('prescription-camera-modal');
    var cameraVideo = document.getElementById('prescription-camera-video');
    var cameraCanvas = document.getElementById('prescription-camera-canvas');
    var cameraPreview = document.getElementById('prescription-camera-preview');
    var cameraMessage = document.getElementById('prescription-camera-message');
    var cameraCaptureButton = document.getElementById('prescription-camera-capture');
    var cameraRetakeButton = document.getElementById('prescription-camera-retake');
    var cameraUseButton = document.getElementById('prescription-camera-use');
    var captureCheckId = 0;
    var analysisModeModal = $('#prescription-analysis-mode-modal');
    var geminiApiKeyInput = document.getElementById('prescription-gemini-api-key');
    var geminiKeyFeedback = document.getElementById('prescription-gemini-key-feedback');
    var analysisMethodChoices = document.getElementById('prescription-analysis-method-choices');
    var aiCloudPanel = document.getElementById('prescription-ai-cloud-panel');
    var geminiProfilesPanel = document.getElementById('prescription-gemini-profiles-panel');
    var geminiCreatePanel = document.getElementById('prescription-gemini-create-panel');
    var geminiProfileNameInput = document.getElementById('prescription-gemini-profile-name');
    var geminiProfileList = document.getElementById('prescription-gemini-profile-list');
    var geminiProfileEmpty = document.getElementById('prescription-gemini-profile-empty');
    var geminiProfileFeedback = document.getElementById('prescription-gemini-profile-feedback');
    var analysisModeTitle = document.getElementById('prescription-analysis-mode-title');
    var analysisBackButton = document.getElementById('prescription-analysis-back');
    var saveGeminiProfileButton = document.getElementById('prescription-save-gemini-profile');
    var analysisPanels = {
        methods: { element: analysisMethodChoices, title: 'Choose analysis method' },
        cloud: { element: aiCloudPanel, title: 'AI Cloud' },
        gemini: { element: geminiProfilesPanel, title: 'Google Gemini' },
        create: { element: geminiCreatePanel, title: 'Create Gemini profile' }
    };
    var currentAnalysisPanel = 'methods';

    function setCameraMessage(message) {
        cameraMessage.textContent = message || '';
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (track) { track.stop(); });
            cameraStream = null;
        }
        cameraVideo.srcObject = null;
    }

    function resetCameraView() {
        captureCheckId++;
        if (cameraPreviewUrl) {
            URL.revokeObjectURL(cameraPreviewUrl);
            cameraPreviewUrl = null;
        }
        cameraVideo.style.display = 'block';
        cameraPreview.style.display = 'none';
        cameraCaptureButton.style.display = 'inline-block';
        cameraRetakeButton.style.display = 'none';
        cameraUseButton.style.display = 'none';
        cameraUseButton.disabled = false;
        capturedPhoto = null;
    }

    function closeCamera() {
        cameraRequest++;
        stopCamera();
        cameraModal.classList.remove('is-open');
        cameraModal.setAttribute('aria-hidden', 'true');
        resetCameraView();
        setCameraMessage('');
    }

    function rotatePhotoBlob(blob, degrees) {
        return new Promise(function (resolve, reject) {
            var imageUrl = URL.createObjectURL(blob);
            var image = new Image();
            image.onload = function () {
                URL.revokeObjectURL(imageUrl);
                var quarterTurn = degrees === 90 || degrees === 270;
                var canvas = document.createElement('canvas');
                canvas.width = quarterTurn ? image.naturalHeight : image.naturalWidth;
                canvas.height = quarterTurn ? image.naturalWidth : image.naturalHeight;
                var context = canvas.getContext('2d');
                if (!context) {
                    reject(new Error('Could not prepare the corrected photo.'));
                    return;
                }
                context.translate(canvas.width / 2, canvas.height / 2);
                context.rotate(-degrees * Math.PI / 180);
                context.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);
                canvas.toBlob(function (correctedBlob) {
                    if (correctedBlob) {
                        resolve(correctedBlob);
                    } else {
                        reject(new Error('Could not prepare the corrected photo.'));
                    }
                }, 'image/jpeg', 0.92);
            };
            image.onerror = function () {
                URL.revokeObjectURL(imageUrl);
                reject(new Error('Could not read the captured photo.'));
            };
            image.src = imageUrl;
        });
    }

    async function checkCapturedPhotoOrientation(blob) {
        var payload = new FormData();
        payload.append('document', new File([blob], 'prescription-camera.jpg', { type: 'image/jpeg' }));
        payload.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        var response = await fetch(@json(route('prescriptions.analyze')), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: payload
        });
        var result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Could not check this photo.');
        if (!result.recognized_text || !result.recognized_text.trim()) {
            throw new Error(result.message || 'No readable text was found in this photo.');
        }
        return result;
    }

    async function openCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            cameraModal.classList.add('is-open');
            cameraModal.setAttribute('aria-hidden', 'false');
            resetCameraView();
            cameraVideo.style.display = 'none';
            cameraCaptureButton.style.display = 'none';
            setCameraMessage('Camera capture requires a supported browser and a secure connection.');
            return;
        }

        cameraModal.classList.add('is-open');
        cameraModal.setAttribute('aria-hidden', 'false');
        resetCameraView();
        stopCamera();
        var request = ++cameraRequest;
        setCameraMessage('Allow camera access when your browser asks.');
        try {
            var stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false
            });
            if (request !== cameraRequest) {
                stream.getTracks().forEach(function (track) { track.stop(); });
                return;
            }
            cameraStream = stream;
            cameraVideo.srcObject = cameraStream;
            setCameraMessage('');
        } catch (error) {
            if (request !== cameraRequest) return;
            console.error('Unable to access prescription camera:', error);
            cameraVideo.style.display = 'none';
            cameraCaptureButton.style.display = 'none';
            setCameraMessage('Camera access was unavailable. Check browser permission and try again.');
        }
    }

    function capturePhoto() {
        if (!cameraVideo.videoWidth || !cameraVideo.videoHeight) {
            setCameraMessage('The camera is still starting. Please try again.');
            return;
        }
        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;
        cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);
        cameraCanvas.toBlob(function (blob) {
            if (!blob) {
                setCameraMessage('The photo could not be captured. Please try again.');
                return;
            }
            capturedPhoto = blob;
            var checkId = ++captureCheckId;
            cameraPreviewUrl = URL.createObjectURL(blob);
            cameraPreview.src = cameraPreviewUrl;
            cameraVideo.style.display = 'none';
            cameraPreview.style.display = 'block';
            cameraCaptureButton.style.display = 'none';
            cameraRetakeButton.style.display = 'inline-block';
            cameraUseButton.style.display = 'inline-block';
            cameraUseButton.disabled = true;
            setCameraMessage('Checking photo orientation and readability…');

            checkCapturedPhotoOrientation(blob).then(async function (result) {
                if (checkId !== captureCheckId) return;
                var rotation = Number(result.orientation_rotation || 0);
                if (result.orientation_corrected && [90, 180, 270].includes(rotation)) {
                    var correctedPhoto = await rotatePhotoBlob(blob, rotation);
                    if (checkId !== captureCheckId) return;
                    capturedPhoto = correctedPhoto;
                    URL.revokeObjectURL(cameraPreviewUrl);
                    cameraPreviewUrl = URL.createObjectURL(capturedPhoto);
                    cameraPreview.src = cameraPreviewUrl;
                    setCameraMessage('Photo corrected to the readable position. Review it, then use the photo.');
                } else {
                    setCameraMessage('Photo orientation checked. Review it, then use the photo.');
                }
            }).catch(function (error) {
                if (checkId !== captureCheckId) return;
                console.warn('Unable to automatically check prescription photo orientation:', error);
                setCameraMessage('Could not automatically check orientation. You can still use this photo and analyze it afterward.');
            }).finally(function () {
                if (checkId === captureCheckId) cameraUseButton.disabled = false;
            });
        }, 'image/jpeg', 0.9);
    }

    function useCapturedPhoto() {
        if (!capturedPhoto) return;
        var file = new File([capturedPhoto], 'prescription-camera.jpg', { type: 'image/jpeg' });
        var transfer = new DataTransfer();
        transfer.items.add(file);
        fileInput.files = transfer.files;
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        closeCamera();
    }

    cameraButton.addEventListener('click', openCamera);
    cameraCaptureButton.addEventListener('click', capturePhoto);
    cameraRetakeButton.addEventListener('click', function () {
        resetCameraView();
        setCameraMessage('');
    });
    cameraUseButton.addEventListener('click', useCapturedPhoto);
    document.getElementById('prescription-camera-close').addEventListener('click', closeCamera);
    document.getElementById('prescription-camera-cancel').addEventListener('click', closeCamera);
    cameraModal.addEventListener('click', function (event) {
        if (event.target === cameraModal) closeCamera();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && cameraModal.classList.contains('is-open')) closeCamera();
    });

    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = 'alert alert-' + type;
    }

    function clearAnalysis() {
        feedback.className = 'alert d-none';
        modalFeedback.className = 'alert d-none';
        document.getElementById('prescription-ocr-confidence').textContent = '';
        matchList.replaceChildren();
        wordOverlay.replaceChildren();
        imageStage.classList.add('d-none');
        imageEmpty.textContent = 'Choose an image to inspect its recognized text regions.';
        imageEmpty.classList.remove('d-none');
        prescriptionFields.forEach(function (field) { field.value = ''; });
        document.getElementById('prescription-ocr-details').value = '';
        document.getElementById('prescription-patient').value = '';
        document.getElementById('prescription-prescriber').value = '';
        document.getElementById('prescription-issued').value = '';
        analysisReady = false;
        doctorName = '';
        licenseNumber = '';
        analyzeButton.innerHTML = '<i class="fas fa-search mr-1" aria-hidden="true"></i> Analyze Image';
    }

    function setModalFeedback(message, type) {
        modalFeedback.textContent = message;
        modalFeedback.className = 'alert alert-' + type;
    }

    function populateReviewFields(text) {
        var labelToField = {
            patientname: 'patient_name', age: 'age', birthdate: 'birth_date',
            consultdate: 'consult_date', patienttype: 'patient_type', patientno: 'patient_no',
            consultationno: 'consultation_no', appointmentdate: 'appointment_date', rx: 'rx'
        };
        prescriptionFields.forEach(function (field) { field.value = ''; });
        doctorName = '';
        licenseNumber = '';
        var populatedAny = false;

        (text || '').split(/\r?\n/).forEach(function (line) {
            var match = line.match(/^\s*([^:]+):\s*(.*)$/);
            if (!match) return;
            var label = match[1].trim();
            var normalizedLabel = label.toLowerCase().replace(/[^a-z]/g, '');
            if (labelToField[normalizedLabel]) {
                var field = document.querySelector('[data-prescription-field="' + labelToField[normalizedLabel] + '"]');
                if (field) {
                    field.value = match[2].trim();
                    populatedAny = populatedAny || field.value !== '';
                }
            } else if (normalizedLabel === 'doctorname') {
                doctorName = match[2].trim();
                populatedAny = populatedAny || doctorName !== '';
            } else if (normalizedLabel === 'licenseno') {
                licenseNumber = match[2].trim();
                populatedAny = populatedAny || licenseNumber !== '';
            }
        });

        if (text.trim() && !populatedAny) {
            var rxField = document.querySelector('[data-prescription-field="rx"]');
            if (rxField) rxField.value = text.trim();
        }
    }

    function setReviewModalReadOnly(readOnly) {
        prescriptionFields.forEach(function (field) {
            field.readOnly = readOnly;
        });
        applyDetailsButton.classList.toggle('d-none', readOnly);
        document.getElementById('prescription-review-title').textContent = readOnly
            ? 'Prescription details'
            : 'Review prescription details';
        document.getElementById('prescription-review-image-note').textContent = readOnly
            ? 'Original submitted prescription. OCR word highlights are available during analysis.'
            : 'Hover or focus a highlighted word to inspect what OCR read there.';
    }

    $(document).off('click.prescriptionDetails', '.prescription-patient-detail-link')
        .on('click.prescriptionDetails', '.prescription-patient-detail-link', function () {
            var details;
            try {
                details = JSON.parse(this.getAttribute('data-details') || '{}');
            } catch (error) {
                showFeedback('Could not load the saved prescription details.', 'warning');
                return;
            }

            setReviewModalReadOnly(true);
            document.getElementById('prescription-ocr-confidence').textContent = '';
            var ocrDetails = details.ocr_details || {};
            var values = Object.assign({}, ocrDetails, {
                patient_name: details.patient_name || ocrDetails.patient_name || '',
                consult_date: ocrDetails.consult_date || details.issued_at || ''
            });
            prescriptionFields.forEach(function (field) {
                field.value = values[field.dataset.prescriptionField] || '';
            });
            doctorName = ocrDetails.doctor_name || details.prescriber_name || '';
            licenseNumber = ocrDetails.license_no || '';

            wordOverlay.replaceChildren();
            if (details.document_url) {
                modalImage.src = details.document_url;
                imageStage.classList.remove('d-none');
                imageEmpty.classList.add('d-none');
            } else {
                modalImage.removeAttribute('src');
                imageStage.classList.add('d-none');
                imageEmpty.textContent = 'No prescription document was attached.';
                imageEmpty.classList.remove('d-none');
            }
            modal.modal('show');
        });

    function getReviewedDetails() {
        var details = {};
        prescriptionFields.forEach(function (field) {
            details[field.dataset.prescriptionField] = field.value.trim();
        });
        details.doctor_name = doctorName;
        details.license_no = licenseNumber;
        return details;
    }

    function reviewedTextForCatalog() {
        var details = getReviewedDetails();
        return details.rx ? 'Rx: ' + details.rx : '';
    }

    function renderMatches(matches) {
        matchList.replaceChildren();
        if (!matches.length) {
            var empty = document.createElement('p');
            empty.className = 'text-muted mb-0';
            empty.textContent = 'No catalog candidates. Verify the prescription manually.';
            matchList.appendChild(empty);
            return;
        }

        matches.forEach(function (match) {
            var card = document.createElement('div');
            card.className = 'prescription-match';
            var details = document.createElement('div');
            var name = document.createElement('div');
            name.className = 'prescription-match-name';
            name.textContent = match.name;
            var meta = document.createElement('div');
            meta.className = 'prescription-match-meta';
            meta.textContent = 'Catalog candidate · ' + match.confidence + '% text similarity · Stock: ' + match.stock;
            details.append(name, meta);
            var price = document.createElement('strong');
            price.textContent = Number(match.price || 0).toFixed(2);
            card.append(details, price);
            matchList.appendChild(card);
        });
    }

    function renderOcrWordBoxes(words, imageSize) {
        wordOverlay.replaceChildren();
        var imageWidth = Number(imageSize && imageSize.width);
        var imageHeight = Number(imageSize && imageSize.height);
        if (!Array.isArray(words) || !words.length || !imageWidth || !imageHeight) {
            imageEmpty.textContent = 'Word regions are unavailable for this image. You can still review the extracted fields.';
            imageEmpty.classList.remove('d-none');
            imageStage.classList.remove('d-none');
            return;
        }

        words.forEach(function (word) {
            if (!word.text || !word.width || !word.height) return;
            var box = document.createElement('button');
            box.type = 'button';
            box.className = 'prescription-ocr-word-box';
            box.style.left = (Number(word.left) / imageWidth * 100) + '%';
            box.style.top = (Number(word.top) / imageHeight * 100) + '%';
            box.style.width = (Number(word.width) / imageWidth * 100) + '%';
            box.style.height = (Number(word.height) / imageHeight * 100) + '%';
            box.title = word.text + (word.confidence == null ? '' : ' · OCR confidence ' + word.confidence + '%');
            box.setAttribute('aria-label', box.title);
            wordOverlay.appendChild(box);
        });

        imageEmpty.classList.add('d-none');
        imageStage.classList.remove('d-none');
    }

    function rotateSelectedPreview(degrees) {
        if (![90, 180, 270].includes(Number(degrees)) || !previewUrl) return;

        var image = new Image();
        image.onload = function () {
            var canvas = document.createElement('canvas');
            var quarterTurn = degrees === 90 || degrees === 270;
            canvas.width = quarterTurn ? image.naturalHeight : image.naturalWidth;
            canvas.height = quarterTurn ? image.naturalWidth : image.naturalHeight;
            var context = canvas.getContext('2d');
            if (!context) return;

            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(-degrees * Math.PI / 180);
            context.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);
            canvas.toBlob(function (blob) {
                if (!blob) return;
                if (correctedPreviewUrl) URL.revokeObjectURL(correctedPreviewUrl);
                correctedPreviewUrl = URL.createObjectURL(blob);
                previewImage.src = correctedPreviewUrl;
                modalImage.src = correctedPreviewUrl;
            }, 'image/jpeg', 0.92);
        };
        image.src = previewUrl;
    }

    fileInput.addEventListener('change', function () {
        fileName.textContent = fileInput.files && fileInput.files[0] ? fileInput.files[0].name : 'No file chosen';
        clearAnalysis();
        analyzeButton.disabled = true;
        preview.classList.remove('has-image');
        previewImage.removeAttribute('src');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        if (correctedPreviewUrl) URL.revokeObjectURL(correctedPreviewUrl);
        correctedPreviewUrl = null;
        previewUrl = null;

        var file = fileInput.files && fileInput.files[0];
        if (!file) {
            previewPlaceholder.textContent = 'Choose a prescription image or PDF to preview it here.';
            return;
        }

        if (file.type.indexOf('image/') === 0) {
            previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            modalImage.src = previewUrl;
            preview.classList.add('has-image');
            imageStage.classList.remove('d-none');
            imageEmpty.classList.add('d-none');
            analyzeButton.disabled = false;
        } else {
            modalImage.removeAttribute('src');
            previewPlaceholder.textContent = 'PDF selected: ' + file.name + '. Submit it for manual verification; OCR analysis currently supports images.';
        }
    });

    async function runPrescriptionAnalysis(engine, credentialId, credentialName) {
        var file = fileInput.files && fileInput.files[0];
        if (!file || file.type.indexOf('image/') !== 0) return;

        analyzeButton.disabled = true;
        analyzeButton.textContent = 'Analyzing…';
        clearAnalysis();
        var result;

        try {
            showFeedback(
                engine === 'gemini'
                    ? 'Sending image to Google Gemini for analysis…'
                    : 'Reading image and checking possible catalog matches…',
                'info'
            );
            var payload = new FormData();
            payload.append('document', file);
            payload.append('engine', engine);
            payload.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if (engine === 'gemini') payload.append('credential_id', credentialId);

            var response = await fetch(@json(route('prescriptions.analyze')), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: payload
            });
            result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Prescription analysis failed.');
            if (!result.recognized_text || !result.recognized_text.trim()) {
                showFeedback(result.message || 'No readable text was found. Try a sharper, brighter photo and make sure the full prescription is in frame.', 'warning');
                return;
            }

            populateReviewFields(result.recognized_text || '');
            renderMatches(result.matches || []);
            renderOcrWordBoxes(result.ocr_words || [], result.image_size);
            if (result.orientation_corrected) {
                rotateSelectedPreview(result.orientation_rotation);
            }
            var confidence = document.getElementById('prescription-ocr-confidence');
            confidence.textContent = result.analysis_method === 'gemini'
                ? 'Analyzed with Google Gemini · ' + credentialName
                : (result.ocr_confidence == null ? '' : 'OCR confidence: ' + result.ocr_confidence + '%');
            if (result.analysis_method !== 'gemini' && Number(result.ocr_confidence || 0) < 60) {
                var rxField = document.querySelector('[data-prescription-field="rx"]');
                if (rxField) rxField.value = result.recognized_text;
            }
            analysisReady = true;
            analyzeButton.innerHTML = '<i class="fas fa-eye mr-1" aria-hidden="true"></i> See result';
            showFeedback(
                (result.orientation_corrected ? 'Image orientation was adjusted automatically for OCR. ' : '')
                    + 'Analysis complete. Select See result to review and edit the extracted fields.',
                'success'
            );
        } catch (error) {
            showFeedback(error.message || 'Prescription analysis failed. You can still submit the document for manual verification.', 'warning');
        } finally {
            analyzeButton.disabled = !fileInput.files || !fileInput.files.length;
            if (!analysisReady) {
                analyzeButton.innerHTML = '<i class="fas fa-search mr-1" aria-hidden="true"></i> Analyze Image';
            }
        }
    }

    document.getElementById('prescription-use-built-in-ocr').addEventListener('click', function () {
        analysisModeModal.modal('hide');
        runPrescriptionAnalysis('tesseract', null, '');
    });

    function showAnalysisPanel(name) {
        currentAnalysisPanel = name;
        Object.keys(analysisPanels).forEach(function (panelName) {
            analysisPanels[panelName].element.classList.toggle('d-none', panelName !== name);
        });
        analysisModeTitle.textContent = analysisPanels[name].title;
        analysisBackButton.classList.toggle('d-none', name === 'methods');
        saveGeminiProfileButton.classList.toggle('d-none', name !== 'create');
    }

    async function loadGeminiCredentials() {
        geminiProfileFeedback.className = 'alert alert-danger d-none mt-3';
        geminiProfileFeedback.textContent = '';
        geminiProfileList.replaceChildren();
        geminiProfileEmpty.classList.add('d-none');

        try {
            var response = await fetch(@json(route('prescriptions.gemini-credentials.index')), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Could not load saved Gemini profiles.');

            if (!result.credentials.length) {
                geminiProfileEmpty.classList.remove('d-none');
                return;
            }

            result.credentials.forEach(function (credential) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-success prescription-credential-choice';
                button.textContent = credential.name;
                button.addEventListener('click', function () {
                    analysisModeModal.modal('hide');
                    runPrescriptionAnalysis('gemini', credential.id, credential.name);
                });

                var row = document.createElement('div');
                row.className = 'prescription-credential-row';
                row.appendChild(button);

                var removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-outline-danger prescription-credential-delete';
                removeButton.textContent = 'Remove';
                removeButton.setAttribute('aria-label', 'Remove Gemini profile ' + credential.name);
                removeButton.addEventListener('click', function () {
                    deleteGeminiCredential(credential);
                });
                row.appendChild(removeButton);
                geminiProfileList.appendChild(row);
            });
        } catch (error) {
            geminiProfileFeedback.textContent = error.message || 'Could not load saved Gemini profiles.';
            geminiProfileFeedback.classList.remove('d-none');
        }
    }

    async function deleteGeminiCredential(credential) {
        if (!window.confirm('Remove the saved Gemini profile "' + credential.name + '"?')) return;

        try {
            var response = await fetch(@json(url('prescriptions/gemini-credentials')) + '/' + encodeURIComponent(credential.id), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            var result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Could not remove this Gemini profile.');

            await loadGeminiCredentials();
            geminiProfileFeedback.className = 'alert alert-success mt-3';
            geminiProfileFeedback.textContent = 'Gemini profile removed.';
        } catch (error) {
            geminiProfileFeedback.textContent = error.message || 'Could not remove this Gemini profile.';
            geminiProfileFeedback.className = 'alert alert-danger mt-3';
        }
    }

    document.getElementById('prescription-use-ai-cloud').addEventListener('click', function () {
        showAnalysisPanel('cloud');
    });

    document.getElementById('prescription-select-gemini').addEventListener('click', function () {
        showAnalysisPanel('gemini');
        loadGeminiCredentials();
    });

    document.getElementById('prescription-create-gemini-profile').addEventListener('click', function () {
        geminiProfileNameInput.value = '';
        geminiApiKeyInput.value = '';
        geminiKeyFeedback.className = 'alert alert-danger d-none';
        geminiKeyFeedback.textContent = '';
        showAnalysisPanel('create');
        geminiProfileNameInput.focus();
    });

    analysisBackButton.addEventListener('click', function () {
        if (currentAnalysisPanel === 'create') {
            showAnalysisPanel('gemini');
        } else if (currentAnalysisPanel === 'gemini') {
            showAnalysisPanel('cloud');
        } else {
            showAnalysisPanel('methods');
        }
    });

    saveGeminiProfileButton.addEventListener('click', async function () {
        var name = geminiProfileNameInput.value.trim();
        var apiKey = geminiApiKeyInput.value.trim();
        geminiKeyFeedback.className = 'alert alert-danger d-none';
        geminiKeyFeedback.textContent = '';

        if (!name || !apiKey) {
            geminiKeyFeedback.textContent = 'Enter a profile name and Gemini API key.';
            geminiKeyFeedback.classList.remove('d-none');
            return;
        }
        if (window.location.protocol !== 'https:' && !['localhost', '127.0.0.1'].includes(window.location.hostname)) {
            geminiKeyFeedback.textContent = 'Open this page over HTTPS before saving an API key.';
            geminiKeyFeedback.classList.remove('d-none');
            return;
        }

        saveGeminiProfileButton.disabled = true;
        try {
            var response = await fetch(@json(route('prescriptions.gemini-credentials.store')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: name, api_key: apiKey })
            });
            var result = await response.json();
            if (!response.ok) {
                var validationError = result.errors && Object.values(result.errors)[0] && Object.values(result.errors)[0][0];
                throw new Error(validationError || result.message || 'Could not save this Gemini profile.');
            }

            geminiProfileNameInput.value = '';
            geminiApiKeyInput.value = '';
            showAnalysisPanel('gemini');
            await loadGeminiCredentials();
            geminiProfileFeedback.className = 'alert alert-success mt-3';
            geminiProfileFeedback.textContent = 'Profile created. Select its name to analyze this image.';
        } catch (error) {
            geminiKeyFeedback.textContent = error.message || 'Could not save this Gemini profile.';
            geminiKeyFeedback.classList.remove('d-none');
        } finally {
            geminiApiKeyInput.value = '';
            saveGeminiProfileButton.disabled = false;
        }
    });

    analyzeButton.addEventListener('click', function () {
        if (analysisReady) {
            setReviewModalReadOnly(false);
            modal.modal('show');
            return;
        }

        var file = fileInput.files && fileInput.files[0];
        if (!file || file.type.indexOf('image/') !== 0) return;
        showAnalysisPanel('methods');
        geminiApiKeyInput.value = '';
        geminiProfileNameInput.value = '';
        geminiKeyFeedback.className = 'alert alert-danger d-none mt-3';
        geminiKeyFeedback.textContent = '';
        geminiProfileFeedback.className = 'alert alert-danger d-none mt-3';
        geminiProfileFeedback.textContent = '';
        analysisModeModal.modal('show');
    });

    rematchButton.addEventListener('click', async function () {
        if (!reviewedTextForCatalog()) {
            setModalFeedback('Enter or correct the Rx medicine text before checking the catalog.', 'warning');
            return;
        }

        rematchButton.disabled = true;
        setModalFeedback('Checking your corrected Rx against the active catalog…', 'info');
        try {
            var response = await fetch(@json(route('prescriptions.match-catalog')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ recognized_text: reviewedTextForCatalog() })
            });
            var result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Catalog matching failed.');
            renderMatches(result.matches || []);
            setModalFeedback(result.message || 'Catalog check complete. Verify candidates against the original.', 'success');
        } catch (error) {
            setModalFeedback(error.message || 'Catalog matching failed.', 'warning');
        } finally {
            rematchButton.disabled = false;
        }
    });

    applyDetailsButton.addEventListener('click', function () {
        setReviewModalReadOnly(false);
        var details = getReviewedDetails();
        document.getElementById('prescription-patient').value = details.patient_name;
        document.getElementById('prescription-prescriber').value = details.doctor_name;
        document.getElementById('prescription-issued').value = details.consult_date;
        document.getElementById('prescription-ocr-details').value = JSON.stringify(details);
        modal.modal('hide');
        showFeedback('Reviewed details are ready to submit. Confirm them against the original prescription.', 'success');
    });
})();
</script>
<script>
    $(document).ready(function () {
        var actionTrigger = null;
        $('#prescription-table')
            .on('click.prescriptionActionModal', '.prescription-action-button', function (event) {
                event.preventDefault();
                var menu = this.nextElementSibling;
                if (!menu || !menu.classList.contains('dropdown-menu')) return;

                actionTrigger = this;
                actionTrigger.setAttribute('aria-expanded', 'true');
                document.getElementById('prescription-action-patient-name').textContent = actionTrigger.getAttribute('data-patient-name') || 'Pending review';
                document.getElementById('prescription-action-modal-content').innerHTML = menu.innerHTML;
                $('#prescription-action-modal').modal('show');
            });
        $('#prescription-action-modal').on('hidden.bs.modal', function () {
            if (actionTrigger) {
                actionTrigger.setAttribute('aria-expanded', 'false');
                actionTrigger = null;
            }
            document.getElementById('prescription-action-modal-content').replaceChildren();
        });

        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'prescription-table',
                url: "{{ route('prescriptions.index') }}",
                placeholder: 'No prescriptions submitted.',
                columns: [
                    {title: 'Date', field: 'created_at'},
                    {title: 'Patient', field: 'patient_name', formatter: 'html'},
                    {title: 'Status', field: 'status', formatter: 'html'},
                    {title: 'Actions', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 120, hozAlign: 'center'}
                ]
            });
        }
    });
</script>
@endpush