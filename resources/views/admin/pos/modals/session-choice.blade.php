<div class="modal fade" id="sessionChoiceModal" tabindex="-1" role="dialog" aria-labelledby="sessionChoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionChoiceModalLabel"><i class="fas fa-clock mr-1"></i> POS Session</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">Choose what you want to do with your POS session.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="pos-session-view-history">
                    <i class="fas fa-history mr-1"></i> View My POS Sessions
                </button>
                <button type="button" class="btn btn-warning" id="pos-session-start-now">
                    <i class="fas fa-play-circle mr-1"></i> Start Session
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-css')
<style>
body.dark-mode #sessionChoiceModal .modal-content {
    background-color: #1c2025;
    color: #e2e8f0;
    border: 1px solid #334155;
}
body.dark-mode #sessionChoiceModal .modal-header {
    border-bottom-color: #334155;
    background-color: #1c2025;
}
body.dark-mode #sessionChoiceModal .modal-footer {
    border-top-color: #334155;
    background-color: #1c2025;
}
body.dark-mode #sessionChoiceModal .close {
    color: #94a3b8 !important;
    text-shadow: none !important;
    opacity: 0.8 !important;
}
body.dark-mode #sessionChoiceModal .close:hover {
    color: #ffffff !important;
    opacity: 1 !important;
}
body.dark-mode #pos-session-view-history {
    background-color: #242c38 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode #pos-session-view-history:hover,
body.dark-mode #pos-session-view-history:focus {
    background-color: #334155 !important;
    border-color: #64748b !important;
    color: #ffffff !important;
}
</style>
@endpush
@endonce
