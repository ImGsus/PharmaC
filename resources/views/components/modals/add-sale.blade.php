<!-- Add Sale Modal -->
<div class="modal fade" id="add_sales" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sell Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row form-row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Choose <span class="text-danger">*</span></label>
                            <select class="form-select form-control" disabled>
                                <option selected>POS</option>
                            </select>
                        </div>
                    </div>
                </div>
                <a href="{{ route('pos.orders') }}" class="btn btn-primary btn-block">Go</a>
            </div>
        </div>
    </div>
</div>
<!-- /ADD Sale Modal -->