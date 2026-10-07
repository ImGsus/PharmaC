(function ($) {
    'use strict';
    if (!$) return;

    function ensureFadeClass(target) {
        var $modals = target ? $(target) : $('.modal');
        $modals.each(function () {
            if (!this.classList.contains('fade')) {
                this.classList.add('fade');
            }
        });
    }

    $(document).on('show.bs.modal', '.modal', function () {
        if (window.pharmacyInstantModalHide) return;
        var modal = this;
        if (!modal.classList.contains('fade')) {
            modal.classList.add('fade');
        }
        var $content = $(modal).find('.modal-content');
        if ($content.length) {
            $content.removeClass('animate__animated animate__fadeOut animate__fadeIn');
            void $content[0].offsetWidth;
            $content.addClass('animate__animated animate__fadeIn');
        }
    });

    $(document).on('shown.bs.modal', '.modal', function () {
        if (window.pharmacyInstantModalHide) return;
        var $content = $(this).find('.modal-content');
        if ($content.length && !$content.hasClass('animate__fadeIn')) {
            $content.removeClass('animate__animated animate__fadeOut');
            void $content[0].offsetWidth;
            $content.addClass('animate__animated animate__fadeIn');
        }
    });

    $(document).on('hide.bs.modal', '.modal', function () {
        if (window.pharmacyInstantModalHide) return;
        var $content = $(this).find('.modal-content');
        if ($content.length && !$content.hasClass('animate__fadeOut')) {
            $content.removeClass('animate__fadeIn').addClass('animate__fadeOut');
            void $content[0].offsetWidth;
        }
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        var $content = $(this).find('.modal-content');
        if ($content.length) {
            $content.removeClass('animate__animated animate__fadeOut animate__fadeIn');
        }
    });

    $(document).ready(function () {
        ensureFadeClass();
    });

    document.addEventListener('turbo:load', function () {
        ensureFadeClass();
    });

    document.addEventListener('turbo:before-render', function () {
        $('.modal-content').removeClass('animate__animated animate__fadeOut animate__fadeIn');
    });
})(window.jQuery);
