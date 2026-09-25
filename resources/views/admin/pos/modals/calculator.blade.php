{{-- Calculator modal — included from admin.pos.orders --}}
<div class="modal fade" id="calculatorModal" tabindex="-1" role="dialog" aria-labelledby="calculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calculatorModalLabel"><i class="fas fa-calculator"></i> Calculator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="calc-display" class="calc-display" value="0" readonly aria-label="Calculator display">
                <div class="calculator-grid">
                    <button type="button" class="btn" data-calc="7">7</button>
                    <button type="button" class="btn" data-calc="8">8</button>
                    <button type="button" class="btn" data-calc="9">9</button>
                    <button type="button" class="btn btn-calc-op" data-calc="/">÷</button>

                    <button type="button" class="btn" data-calc="4">4</button>
                    <button type="button" class="btn" data-calc="5">5</button>
                    <button type="button" class="btn" data-calc="6">6</button>
                    <button type="button" class="btn btn-calc-op" data-calc="*">&times;</button>

                    <button type="button" class="btn" data-calc="1">1</button>
                    <button type="button" class="btn" data-calc="2">2</button>
                    <button type="button" class="btn" data-calc="3">3</button>
                    <button type="button" class="btn btn-calc-op" data-calc="-">&minus;</button>

                    <button type="button" class="btn" data-calc="0">0</button>
                    <button type="button" class="btn" data-calc=".">.</button>
                    <button type="button" class="btn btn-calc-equals" data-calc="=">=</button>
                    <button type="button" class="btn btn-calc-op" data-calc="+">+</button>

                    <button type="button" class="btn btn-calc-clear" data-calc="C">Clear</button>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    <i class="fas fa-info-circle"></i> Press <strong>=</strong> to send the result to the Payment field.
                </p>
            </div>
        </div>
    </div>
</div>

@once
@push('page-js')
<script>
(function(){
    var display = document.getElementById('calc-display');
    var expression = '';
    if (!display) return;

    function refresh() {
        display.value = expression === '' ? '0' : expression;
    }

    function applyEquals() {
        try {
            // Strip invalid chars (allow digits, ops, decimal, parens)
            var safe = expression.replace(/[^0-9+\-*/(). ]/g, '');
            var result = Function('"use strict"; return (' + (safe || '0') + ')')();
            if (result === Infinity || result === -Infinity || isNaN(result)) {
                expression = 'Error';
            } else {
                expression = String(Number(Number(result).toFixed(6)));
                // Pipe to Payment field if present
                var pay = document.getElementById('payment_amount');
                if (pay) pay.value = Number(Number(result).toFixed(2));
            }
        } catch (e) {
            expression = 'Error';
        }
        refresh();
    }

    document.querySelectorAll('#calculatorModal [data-calc]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var v = btn.getAttribute('data-calc');
            if (v === 'C') { expression = ''; refresh(); return; }
            if (v === '=') { applyEquals(); return; }
            if (expression === 'Error') expression = '';
            expression += v;
            refresh();
        });
    });
})();
</script>
@endpush
@endonce
