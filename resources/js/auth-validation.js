(function ($) {
    function clearFieldState($field) {
        if (!$field || !$field.length) {
            return;
        }

        $field.removeClass('has-error');
        $field.find('input, textarea, select').removeAttr('aria-invalid').removeClass('is-invalid');
    }

    function clearAlert(alertSelector) {
        $(alertSelector).empty();
    }

    function fadeOutAlert(alertSelector, callback) {
        var $alert = $(alertSelector + ' .alert');
        if (!$alert.length) {
            if (typeof callback === 'function') {
                callback();
            }
            return;
        }

        $alert.stop(true, true).fadeOut(200, function() {
            $(this).remove();
            if (typeof callback === 'function') {
                callback();
            }
        });
    }

    function showFieldError(alertSelector, $field, message) {
        var $input = $field.find('input, textarea, select').first();
        if (!$field.length || !$input.length) {
            return;
        }

        clearAlert(alertSelector);
        $field.addClass('has-error');
        $input.attr('aria-invalid', 'true').addClass('is-invalid');

        $(alertSelector).html(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );

        $(alertSelector + ' .close').on('click', function() {
            clearAlert(alertSelector);
            clearFieldState($field);
        });

        $input.focus();
    }

    function bindInputReset(alertSelector, formSelector, fieldSelector) {
        var $form = $(formSelector);
        if (!$form.length) {
            return;
        }

        $form.on('input', fieldSelector, function() {
            var $field = $(this).closest(fieldSelector);
            if ($field.hasClass('has-error')) {
                clearFieldState($field);
            }
            fadeOutAlert(alertSelector);
        });
    }

    window.AuthValidation = {
        clearAlert: clearAlert,
        clearFieldState: clearFieldState,
        fadeOutAlert: fadeOutAlert,
        showFieldError: showFieldError,
        bindInputReset: bindInputReset
    };
})(jQuery);
