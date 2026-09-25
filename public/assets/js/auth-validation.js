(function ($) {
    function clearAlert(containerSelector) {
        $(containerSelector).empty();
    }

    function clearFieldState($field) {
        if (!$field || !$field.length) {
            return;
        }

        $field.removeClass('has-error');
        $field.find('input, textarea, select').removeAttr('aria-invalid').removeClass('is-invalid');
    }

    function fadeAlert(containerSelector, callback) {
        var $alert = $(containerSelector + ' .alert');
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

    function showFieldError(containerSelector, $field, message) {
        var $input = $field.find('input, textarea, select').first();
        if (!$field.length || !$input.length) {
            return;
        }

        clearAlert(containerSelector);
        $field.addClass('has-error');
        $input.attr('aria-invalid', 'true').addClass('is-invalid');

        $(containerSelector).append(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );

        $(containerSelector + ' .close').on('click', function() {
            clearAlert(containerSelector);
            clearFieldState($field);
        });

        $input.focus();
    }

    window.AuthValidation = {
        clearAlert: clearAlert,
        clearFieldState: clearFieldState,
        fadeAlert: fadeAlert,
        showFieldError: showFieldError
    };
})(jQuery);
