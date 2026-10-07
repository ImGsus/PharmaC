/**
 * PharmaC - Multi-Step Purchase & Box Expiry Declaration Handler
 */
(function (window, $) {
    'use strict';

    function initPurchaseMultiStep(formSelector, modalSelector) {
        var $form = $(formSelector || 'form.purchase-multi-step-form');
        if (!$form.length) return;

        var $modal = modalSelector ? $(modalSelector) : $form.closest('.modal');

        var $step1 = $form.find('.purchase-step-1');
        var $step2 = $form.find('.purchase-step-2');
        var $btnNext = $form.find('.purchase-step-next-btn');
        var $btnPrev = $form.find('.purchase-step-prev-btn');
        var $btnSubmit = $form.find('.purchase-step-submit-btn');

        var $categorySelect = $form.find('select[name="category"]');
        var $supplierSelect = $form.find('select[name="supplier"]');
        var $noExpiryCheckbox = $form.find('input[name="no_expiry"]');
        var $hiddenPrimaryExpiry = $form.find('.primary-expiry-date-hidden');

        var $inputItemQty = $form.find('input[name="item_quantity"]');
        var $inputBoxes = $form.find('input[name="packaging_box"]');
        var $inputPerBox = $form.find('input[name="quantity_per_box"]');
        var $inputTotal = $form.find('input[name="total_quantity"]');

        var $step2SummaryProduct = $form.find('.step2-summary-product');
        var $step2SummaryCategory = $form.find('.step2-summary-category');
        var $step2SummaryBoxes = $form.find('.step2-summary-boxes');
        var $step2SummaryPerBox = $form.find('.step2-summary-per-box');
        var $step2SummaryTotal = $form.find('.step2-summary-total');

        var $step2NoExpiryWrap = $form.find('.step2-no-expiry-wrap');
        var $step2ExpiryContent = $form.find('.step2-expiry-content');
        var $boxExpiriesContainer = $form.find('.box-expiries-container');
        var $step2BoxCountBadge = $form.find('.step2-box-count-badge');
        var $looseItemsWrap = $form.find('.loose-items-expiry-wrap');
        var $looseItemsQty = $form.find('.loose-items-qty');
        var $looseExpiryInput = $form.find('input[name="loose_expiry"]');

        var $masterExpiryInput = $form.find('.master-expiry-input');
        var $btnApplyAll = $form.find('.btn-apply-all-expiry');
        var $earliestExpiryDisplay = $form.find('.earliest-expiry-display');

        var defaultModalTitle = '';
        if ($modal.length && $modal.find('.modal-title').length) {
            defaultModalTitle = $modal.find('.modal-title').html();
        }

        function showNotification(message, bg) {
            if (window.Snackbar) {
                window.Snackbar.show({
                    text: message,
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: bg || '#0f766e',
                    duration: 3500
                });
            }
        }

        // 1. Total quantity calculation
        function computeTotal() {
            var item = parseInt($inputItemQty.val() || 0, 10);
            var boxes = parseInt($inputBoxes.val() || 0, 10);
            var perBox = parseInt($inputPerBox.val() || 0, 10);
            if (isNaN(item) || item < 0) item = 0;
            if (isNaN(boxes) || boxes < 0) boxes = 0;
            if (isNaN(perBox) || perBox < 0) perBox = 0;
            var total = item + (boxes * perBox);
            $inputTotal.val(total);
        }

        $inputItemQty.on('input change', computeTotal);
        $inputBoxes.on('input change', computeTotal);
        $inputPerBox.on('input change', computeTotal);
        computeTotal();

        // 2. Expiry status synchronization
        function isNoExpiryActive() {
            var catOption = $categorySelect.find('option:selected');
            var catNoExpiry = catOption.data('no-expiry') === 1 || catOption.data('no-expiry') === '1';
            return catNoExpiry || $noExpiryCheckbox.is(':checked');
        }

        function syncExpiryState(categoryChanged) {
            var catOption = $categorySelect.find('option:selected');
            var catNoExpiry = catOption.data('no-expiry') === 1 || catOption.data('no-expiry') === '1';
            $noExpiryCheckbox.prop('disabled', catNoExpiry);
            if (catNoExpiry) {
                $noExpiryCheckbox.prop('checked', true);
            } else if (categoryChanged) {
                $noExpiryCheckbox.prop('checked', false);
            }
        }

        $categorySelect.on('change', function () {
            syncExpiryState(true);
        });
        $noExpiryCheckbox.on('change', function () {
            syncExpiryState(false);
        });
        syncExpiryState(false);

        // 3. Supplier autofill
        $supplierSelect.on('change', function () {
            var $opt = $(this).find('option:selected');
            var prod = $opt.data('product') || '';
            var cat = $opt.data('category') || '';
            var cost = $opt.data('cost') || '';
            var expiry = $opt.data('expiry') || '';

            if (prod) $form.find('input[name="product"]').val(prod);
            if (cat) {
                $categorySelect.val(cat);
                if ($.fn.select2 && $categorySelect.hasClass('select2-hidden-accessible')) {
                    $categorySelect.trigger('change.select2');
                }
                syncExpiryState(true);
            }
            if (cost) $form.find('input[name="cost_price"]').val(cost);
            if (expiry) {
                $masterExpiryInput.val(expiry);
            }
            computeTotal();
        });

        // 4. Calculate earliest expiry date
        function recalcEarliestExpiry() {
            if (isNoExpiryActive()) {
                $hiddenPrimaryExpiry.val('');
                $earliestExpiryDisplay.text('No expiry');
                return;
            }

            var dates = [];
            $boxExpiriesContainer.find('.box-expiry-item-input').each(function () {
                var d = $(this).val();
                if (d && d.trim() !== '') {
                    dates.push(d.trim());
                }
            });

            var looseD = $looseExpiryInput.val();
            if (looseD && looseD.trim() !== '') {
                dates.push(looseD.trim());
            }

            if (dates.length > 0) {
                dates.sort();
                var earliest = dates[0];
                $hiddenPrimaryExpiry.val(earliest);

                try {
                    var parsed = new Date(earliest + 'T00:00:00');
                    if (!isNaN(parsed.getTime())) {
                        var options = { day: '2-digit', month: 'short', year: 'numeric' };
                        $earliestExpiryDisplay.text(parsed.toLocaleDateString('en-GB', options));
                    } else {
                        $earliestExpiryDisplay.text(earliest);
                    }
                } catch (e) {
                    $earliestExpiryDisplay.text(earliest);
                }
            } else {
                $hiddenPrimaryExpiry.val('');
                $earliestExpiryDisplay.text('Not set');
            }
        }

        // 5. Apply to all boxes handler
        $btnApplyAll.on('click', function (e) {
            e.preventDefault();
            var targetDate = $masterExpiryInput.val();
            if (!targetDate) {
                var firstDate = $boxExpiriesContainer.find('.box-expiry-item-input').first().val();
                if (firstDate) {
                    targetDate = firstDate;
                    $masterExpiryInput.val(targetDate);
                } else if ($looseExpiryInput.val()) {
                    targetDate = $looseExpiryInput.val();
                    $masterExpiryInput.val(targetDate);
                }
            }

            if (!targetDate) {
                showNotification('Please choose an expiry date in Quick Action first.', '#e7515a');
                $masterExpiryInput.focus();
                return;
            }

            $boxExpiriesContainer.find('.box-expiry-item-input').val(targetDate).trigger('change');
            if ($looseItemsWrap.is(':visible') || parseInt($inputItemQty.val() || 0, 10) > 0) {
                $looseExpiryInput.val(targetDate).trigger('change');
            }

            recalcEarliestExpiry();
            showNotification('Applied ' + targetDate + ' to all expiry fields.', '#10b981');
        });

        // 6. Step 1 -> Step 2 transition
        $btnNext.on('click', function (e) {
            e.preventDefault();

            // Validate Step 1 fields
            var step1Inputs = $step1.find('input[required], select[required]');
            var firstInvalid = null;

            step1Inputs.each(function () {
                if (!this.checkValidity()) {
                    if (!firstInvalid) firstInvalid = this;
                }
            });

            if (firstInvalid) {
                firstInvalid.focus();
                if (typeof firstInvalid.reportValidity === 'function') {
                    firstInvalid.reportValidity();
                }
                showNotification('Please fill in all required fields before proceeding.', '#e7515a');
                return;
            }

            computeTotal();
            var noExpiry = isNoExpiryActive();
            var productName = $form.find('input[name="product"]').val() || '--';
            var catName = $categorySelect.find('option:selected').text() || '--';
            var numBoxes = parseInt($inputBoxes.val() || 0, 10);
            var perBox = parseInt($inputPerBox.val() || 0, 10);
            var itemQty = parseInt($inputItemQty.val() || 0, 10);
            if (isNaN(numBoxes) || numBoxes < 0) numBoxes = 0;
            if (isNaN(perBox) || perBox < 0) perBox = 0;
            if (isNaN(itemQty) || itemQty < 0) itemQty = 0;

            if (numBoxes > 0 && perBox <= 0) {
                $inputPerBox.focus();
                showNotification('Please specify the quantity inside per packaging box.', '#e7515a');
                return;
            }

            var totalQty = itemQty + (numBoxes * perBox);
            if (totalQty <= 0) {
                if (itemQty <= 0 && numBoxes <= 0) {
                    $inputItemQty.focus();
                    showNotification('Please enter at least an Item quantity or Packaging box quantity.', '#e7515a');
                    return;
                }
                showNotification('Total quantity must be greater than 0.', '#e7515a');
                return;
            }

            // Populate Step 2 summary
            $step2SummaryProduct.text(productName);
            $step2SummaryCategory.text(catName);
            $step2SummaryBoxes.text(numBoxes);
            $step2SummaryPerBox.text(perBox);
            $step2SummaryTotal.text(totalQty);
            $step2BoxCountBadge.text(numBoxes);

            if (noExpiry) {
                $step2NoExpiryWrap.removeClass('d-none');
                $step2ExpiryContent.addClass('d-none');
                $boxExpiriesContainer.empty();
                $looseItemsWrap.addClass('d-none');
                $hiddenPrimaryExpiry.val('');
                $earliestExpiryDisplay.text('No expiry');
            } else {
                $step2NoExpiryWrap.addClass('d-none');
                $step2ExpiryContent.removeClass('d-none');

                // Preserve existing dates if any
                var currentDates = [];
                $boxExpiriesContainer.find('.box-expiry-item-input').each(function () {
                    currentDates.push($(this).val());
                });

                $boxExpiriesContainer.empty();

                if (numBoxes > 0) {
                    for (var i = 1; i <= numBoxes; i++) {
                        var existingVal = currentDates[i - 1] || $masterExpiryInput.val() || '';
                        var boxCard = $(
                            '<div class="col-sm-6 col-lg-4 mb-3">' +
                                '<div class="card box-expiry-card border h-100 shadow-none">' +
                                    '<div class="card-body p-3 d-flex flex-column justify-content-between">' +
                                        '<div class="d-flex align-items-center justify-content-between mb-2">' +
                                            '<span class="badge badge-primary font-weight-normal px-2 py-1"><i class="fas fa-box mr-1"></i> Packaging Box #' + i + '</span>' +
                                            '<span class="small text-muted">Box ' + i + ' of ' + numBoxes + '</span>' +
                                        '</div>' +
                                        '<div class="form-group mb-0">' +
                                            '<label class="small text-muted mb-1 font-weight-bold">Expire Date <span class="text-danger">*</span></label>' +
                                            '<input type="date" name="box_expiries[]" class="form-control form-control-sm box-expiry-item-input" value="' + existingVal + '" required>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>'
                        );
                        $boxExpiriesContainer.append(boxCard);
                    }
                } else {
                    $boxExpiriesContainer.append(
                        '<div class="col-12"><div class="alert alert-light border py-2 text-muted small"><i class="fas fa-info-circle mr-1"></i> No packaging boxes declared (loose items only).</div></div>'
                    );
                }

                // Loose items
                if (itemQty > 0) {
                    $looseItemsWrap.removeClass('d-none');
                    $looseItemsQty.text(itemQty);
                    if (!$looseExpiryInput.val() && $masterExpiryInput.val()) {
                        $looseExpiryInput.val($masterExpiryInput.val());
                    }
                } else {
                    $looseItemsWrap.addClass('d-none');
                }

                // Bind change event to new box inputs
                $boxExpiriesContainer.find('.box-expiry-item-input').on('input change', recalcEarliestExpiry);
                $looseExpiryInput.on('input change', recalcEarliestExpiry);

                recalcEarliestExpiry();
            }

            // Smooth transition from Step 1 to Step 2
            $step1.addClass('step-animating-out');
            window.setTimeout(function () {
                $step1.hide().removeClass('step-animating-out');
                $step2.show().addClass('step-animating-in');
                window.setTimeout(function () {
                    $step2.removeClass('step-animating-in');
                }, 200);

                if ($modal.length && $modal.find('.modal-title').length) {
                    $modal.find('.modal-title').html('Add Purchase <span aria-hidden="true">-&gt;</span> <span class="text-primary">Expiry Details</span>');
                }
            }, 150);
        });

        // 7. Step 2 -> Step 1 transition ("Back")
        $btnPrev.on('click', function (e) {
            e.preventDefault();
            $step2.addClass('step-animating-out');
            window.setTimeout(function () {
                $step2.hide().removeClass('step-animating-out');
                $step1.show().addClass('step-animating-in');
                window.setTimeout(function () {
                    $step1.removeClass('step-animating-in');
                }, 200);

                if ($modal.length && $modal.find('.modal-title').length) {
                    if (defaultModalTitle) {
                        $modal.find('.modal-title').html(defaultModalTitle);
                    } else {
                        $modal.find('.modal-title').html('Add Purchase');
                    }
                }
            }, 150);
        });

        // 8. Reset to Step 1 on Modal Close
        if ($modal.length) {
            $modal.on('hidden.bs.modal', function () {
                $step2.hide().removeClass('step-animating-out step-animating-in');
                $step1.show().removeClass('step-animating-out step-animating-in');
                if (defaultModalTitle) {
                    $modal.find('.modal-title').html(defaultModalTitle);
                }
            });
        }

        // 9. Form Submission validation
        $form.on('submit', function (e) {
            if (!isNoExpiryActive()) {
                recalcEarliestExpiry();
                var earliest = $hiddenPrimaryExpiry.val();
                if (!earliest) {
                    e.preventDefault();
                    showNotification('Please specify expiration dates for your items / packaging boxes.', '#e7515a');
                    return false;
                }
            }
            $btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        });
    }

    window.initPurchaseMultiStep = initPurchaseMultiStep;

    $(document).ready(function () {
        $('form.purchase-multi-step-form').each(function () {
            initPurchaseMultiStep(this);
        });
    });
})(window, jQuery);
