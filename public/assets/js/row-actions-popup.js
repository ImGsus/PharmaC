(function ($) {
    'use strict';

    if (!$) return;

    var popupSelector = '#rowActionPopup';
    var returnContext = null;
    var pendingCommand = null;
    var editFrame = null;
    var editTable = null;


    function getPopup() {
        return $(popupSelector);
    }

    function clearReturnContext() {
        var $popup = getPopup();
        var trigger = $popup.data('row-action-trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        returnContext = null;
        pendingCommand = null;
        $popup.removeData('row-action-menu').removeData('row-action-trigger');
    }

    function openPopup(trigger) {
        var $trigger = $(trigger);
        var $menu = $trigger.closest('.btn-group').children('.dropdown-menu').first();
        if (!$menu.length) return;

        var $popup = getPopup();
        var $content = $popup.find('.row-action-popup-menu').empty();
        $menu.children().each(function (index) {
            var $item = $(this).clone(false);
            $item.attr('data-row-action-index', index);
            $item.on('click.rowActionPopup', handlePopupActionClick);
            $content.append($item);
        });

        var title = $trigger.attr('data-action-title') || 'Actions';
        var prefix = $trigger.attr('data-context-label') || 'Name';
        var value = $trigger.attr('data-context-value') || '';
        var secondary = $trigger.attr('data-context-secondary') || $trigger.attr('data-action-category') || '';

        $popup.find('#rowActionPopupTitle').text(title);
        $popup.find('.row-action-context-prefix').text(prefix);
        $popup.find('.row-action-context-value').text(value);
        if (secondary) {
            $popup.find('.row-action-context-secondary').html(' &rarr; ' + $('<div>').text(secondary).html()).show();
        } else {
            $popup.find('.row-action-context-secondary').empty().hide();
        }

        $popup.data('row-action-trigger', trigger);
        $popup.data('row-action-menu', $menu[0]);
        $trigger.attr('aria-expanded', 'true');
        $popup.modal('show');
    }

    function handlePopupActionClick(event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        var $popup = getPopup();
        var menu = $popup.data('row-action-menu');
        var index = Number($(this).attr('data-row-action-index'));
        var $original = menu ? $(menu).children().eq(index) : $();
        if (!$original.length) return;

        var isDelete = $(this).hasClass('text-danger') || $(this).attr('id') === 'deletebtn' || $(this).find('#deletebtn').length > 0;
        if (isDelete) {
            returnContext = null;
            pendingCommand = {
                element: $original[0],
                trigger: null,
                isDelete: true
            };
            $popup.modal('hide');
            return;
        }

        pendingCommand = {
            element: $original[0],
            trigger: $popup.data('row-action-trigger')
        };
        $popup.modal('hide');
    }

    function handlePopupHidden() {
        var $popup = $(this);
        $popup.find('.row-action-popup-menu').empty();
        if (!pendingCommand) {
            var currentTrigger = $popup.data('row-action-trigger');
            if (currentTrigger && !returnContext) {
                currentTrigger.setAttribute('aria-expanded', 'false');
            }
            $popup.removeData('row-action-menu').removeData('row-action-trigger');
            return;
        }

        var command = pendingCommand;
        pendingCommand = null;
        if (command.isDelete) {
            $popup.removeData('row-action-menu').removeData('row-action-trigger');
            $(command.element).trigger('click');
            return;
        }
        var trigger = command.trigger;
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        returnContext = {
            trigger: trigger,
            modal: null
        };

        if ($(command.element).hasClass('row-action-iframe-edit')) {
            window.setTimeout(function () {
                showEditFrame(command.element);
            }, 50);
            return;
        }

        window.setTimeout(function () {
            $(command.element).trigger('click');
        }, 50);
    }

    function handleOtherModalShow() {
        if (returnContext) {
            returnContext.modal = this;
        }
        if (getPopup().hasClass('show')) {
            getPopup().modal('hide');
        }
    }

    function handleOtherModalHidden() {
        var $modal = $(this);
        if ($modal.data('row-action-switching')) {
            $modal.removeData('row-action-switching');
            return;
        }
        var editTrigger = this.id === 'rowActionEditPopup'
            ? $modal.data('row-action-return-trigger')
            : null;
        if (this.id === 'rowActionEditPopup') {
            editTable = null;
            $modal.removeData('row-action-return-trigger');
        }
        if ($modal.data('row-action-no-return') || $modal.data('action-submitted')) {
            $modal.removeData('row-action-no-return').removeData('action-submitted');
            returnContext = null;
            return;
        }
        if (editTrigger && document.documentElement.contains(editTrigger)) {
            returnContext = null;
            window.setTimeout(function () {
                openPopup(editTrigger);
            }, 50);
            return;
        }
        if (!returnContext || returnContext.modal !== this) return;
        var context = returnContext;
        window.setTimeout(function () {
            if (returnContext !== context || context.modal !== $modal[0]) return;
            var trigger = context.trigger;
            returnContext = null;
            if (trigger && document.documentElement.contains(trigger)) {
                openPopup(trigger);
            }
        }, 50);
    }

    function bindModalEvents() {
        getPopup()
            .off('hidden.bs.modal.rowActionPopup')
            .on('hidden.bs.modal.rowActionPopup', handlePopupHidden);
        $('#rowActionPopup')
            .off('shown.bs.modal.rowActionPopup')
            .on('shown.bs.modal.rowActionPopup', function () {
                if (pendingCommand) {
                    $(this).modal('hide');
                }
            });
        $('.modal').not(popupSelector)
            .off('show.bs.modal.rowActionPopup hidden.bs.modal.rowActionPopup')
            .on('show.bs.modal.rowActionPopup', handleOtherModalShow)
            .on('hidden.bs.modal.rowActionPopup', handleOtherModalHidden);
    }

    function showEditFrame(link) {
        var $link = $(link);
        var $modal = $('#rowActionEditPopup');
        var frame = $modal.find('.row-action-edit-frame')[0];
        editFrame = frame;
        editTable = $link.attr('data-row-action-table') || '';
        var targetUrl = $link.attr('href');
        $modal.data('row-action-list-path', $link.attr('data-row-action-list-path') || '');
        $modal.find('#rowActionEditPopupTitle').text('Edit ' + ($link.attr('data-row-action-name') || 'Record'));
        if (returnContext) {
            returnContext.modal = $modal[0];
            $modal.data('row-action-return-trigger', returnContext.trigger);
        } else {
            $modal.removeData('row-action-return-trigger');
        }
        $(frame).off('load.rowActionPopup').on('load.rowActionPopup', handleEditFrameNavigation);
        if (frame.dataset.rowActionLoadedUrl !== targetUrl) {
            frame.classList.remove('row-action-edit-frame-ready');
            frame.dataset.rowActionLoadedUrl = '';
            frame.src = targetUrl;
        }
        $modal.modal('show');
    }

    function handleEditFrameNavigation() {
        var frame = this;
        try {
            var frameWindow = frame.contentWindow;
            var frameDocument = frame.contentDocument;
            if (!frameDocument || !frameDocument.head) return;

            if (!frameDocument.getElementById('row-action-edit-frame-style')) {
                var style = frameDocument.createElement('style');
                style.id = 'row-action-edit-frame-style';
                style.textContent = [
                    '.header,.sidebar,.page-header,footer{display:none!important}',
                    '.page-wrapper{margin:0!important;padding:0!important;min-height:0!important}',
                    '.main-wrapper{min-height:0!important}',
                    '.content.container-fluid{padding:16px!important}',
                    'body{background:transparent!important}',
                    '.content .card{margin-bottom:0!important}'
                ].join('');
                frameDocument.head.appendChild(style);
            }
            frame.classList.add('row-action-edit-frame-ready');
            frame.dataset.rowActionLoadedUrl = frameWindow.location.href;
            frameDocument.addEventListener('turbo:load', function () {
                handleEditFrameNavigation.call(frame);
            }, {once: true});

            var $modal = $('#rowActionEditPopup');
            var expectedPath = $modal.data('row-action-list-path');
            var actualPath = frameWindow.location.pathname.replace(/\/+$/, '');
            expectedPath = (expectedPath || '').replace(/\/+$/, '');
            if (expectedPath && actualPath === expectedPath) {
                frame.dataset.rowActionLoadedUrl = '';
                $modal.data('row-action-no-return', true);
                $modal.modal('hide');
                if (window.PharmaTabulator && editTable) {
                    window.PharmaTabulator.reload(editTable);
                }
                if (window.Snackbar) {
                    Snackbar.show({text: 'Changes saved successfully.', pos: 'top-right', actionTextColor: '#fff', backgroundColor: '#8dbf42'});
                }
            }
        } catch (error) {
            console.error('Unable to load the edit popup frame:', error);
        }
    }

    $(document)
        .off('.rowActionPopup')
        .on('click.rowActionPopup', '.row-action-modal-trigger', function (event) {
            event.preventDefault();
            event.stopPropagation();
            openPopup(this);
        })
        .on('click.rowActionPopup', '.row-action-iframe-edit', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showEditFrame(this);
        });

    function bindRowActionHandlers() {
        bindModalEvents();
    }

    bindRowActionHandlers();
    document.removeEventListener('turbo:load', bindRowActionHandlers);
    document.addEventListener('turbo:load', bindRowActionHandlers);

    $(document)
        .off('turbo:before-cache.rowActionPopup turbo:before-render.rowActionPopup')
        .on('turbo:before-cache.rowActionPopup turbo:before-render.rowActionPopup', clearReturnContext);
})(window.jQuery);
