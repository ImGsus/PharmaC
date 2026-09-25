$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

(function($) {
    "use strict";

    if ($.fn.select2 && $.fn.select2.amd) {
        $.fn.select2.amd.require(['select2/results'], function (Results) {
            if (Results && Results.prototype && Results.prototype.highlightFirstItem) {
                Results.prototype.highlightFirstItem = function () {
                    this.$results.find('.select2-results__option--highlighted').removeClass('select2-results__option--highlighted');
                    this.$results.find('.select2-results__option--selected').removeClass('select2-results__option--selected');
                };
            }
        });
    }

    var themeKey = 'pharmacy-theme';

    function updateLogoForTheme(isDark) {
        var $logo = $('#site-logo');
		if ($logo.length) {
			var customLogo = $logo.data('custom-logo');
			var lightLogo = $logo.data('light-logo');
			var darkLogo = $logo.data('dark-logo');
			if (isDark) {
				$logo.attr('src', darkLogo || customLogo || lightLogo);
			} else {
				$logo.attr('src', customLogo || lightLogo);
			}
        }

		var $plainLogo = $('#plain-site-logo');
		if ($plainLogo.length) {
			$plainLogo.attr('src', isDark ? $plainLogo.data('dark-logo') : $plainLogo.data('light-logo'));
        }
    }

	function applyTheme(isDark) {
		var root = document.documentElement;
		var body = document.body;

		if (root) {
			root.classList.toggle('dark-mode', isDark);
			root.setAttribute('data-theme', isDark ? 'dark' : 'light');
			root.style.colorScheme = isDark ? 'dark' : 'light';
		}

		if (body) {
			body.classList.toggle('dark-mode', isDark);
			body.setAttribute('data-theme', isDark ? 'dark' : 'light');
		}

		localStorage.setItem(themeKey, isDark ? 'dark' : 'light');

		$('#loginThemeToggle').toggleClass('is-dark', isDark);
		$('#dashboardThemeToggle').toggleClass('is-dark', isDark);

		$('#loginThemeToggle, #dashboardThemeToggle')
			.attr('aria-checked', isDark ? 'true' : 'false')
			.attr('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');

		updateLogoForTheme(isDark);
		document.dispatchEvent(new CustomEvent('pharmacy:theme-changed', { detail: { isDark: isDark } }));
	}

	$(document).ready(function() {
		var storedTheme = localStorage.getItem(themeKey);
		var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		var isDark = storedTheme === 'dark' || (storedTheme !== 'light' && prefersDark);

		applyTheme(isDark);
		document.documentElement.classList.remove('theme-initializing');

		$('#loginThemeToggle, #dashboardThemeToggle')
			.off('click.theme')
			.on('click.theme', function() {
				var nextIsDark = !document.documentElement.classList.contains('dark-mode');
				applyTheme(nextIsDark);
			});
	});
	
	// Variables declarations
	
	var $wrapper = $('.main-wrapper');
	var $pageWrapper = $('.page-wrapper');
	var $slimScrolls = $('.slimscroll');

	// alert
	$("div.alert").not('.login-validation-alerts .alert, .form-validation-alerts .alert').delay(3000).slideUp(750);
	
	// Sidebar
	var Sidemenu = function() {
		this.$menuItem = $('#sidebar-menu a');
	};
	// select2
	function initCommonWidgets() {
		if (!$.fn.select2) {
			return;
		}

		$('.select2').each(function() {
			// Destroy existing select2 instance if present
			if ($(this).hasClass('select2-hidden-accessible')) {
				$(this).select2('destroy');
			}
			// Initialize/reinitialize select2
			$(this).select2({
				placeholder: 'Select an option'
			});
		});
	}

	window.pharmacyCommonWidgetsInit = initCommonWidgets;
	initCommonWidgets();

	var activeDataTableRequests = {};
	$(document).on('preXhr.dt', function(event, settings) {
		var requestKey = settings.ajax && typeof settings.ajax === 'string'
			? settings.ajax
			: settings.sAjaxSource || settings.nTable?.id;

		if (requestKey) {
			settings._pharmacyRequestKey = requestKey;
		}
	});

	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		var requestData = options.data;
		var isDataTableRequest = requestData && (
			(typeof requestData === 'string' && /(draw|start|length|search%5B|search\[)/.test(requestData)) ||
			(typeof requestData === 'object' && ('draw' in requestData || 'start' in requestData || 'length' in requestData))
		);

		if (!isDataTableRequest) {
			return;
		}

		var requestKey = options.url;
		if (activeDataTableRequests[requestKey]) {
			activeDataTableRequests[requestKey].abort();
		}
		activeDataTableRequests[requestKey] = jqXHR;
		jqXHR.always(function() {
			if (activeDataTableRequests[requestKey] === jqXHR) {
				delete activeDataTableRequests[requestKey];
			}
		});
	});

	function setSelect2HoverStyle(element) {
		var isDark = $('body').hasClass('dark-mode');
		element.style.setProperty('background-color', isDark ? '#1f2937' : '#00d0f1', 'important');
		element.style.setProperty('color', isDark ? '#ffffff' : '#111827', 'important');
	}

	function clearSelect2HoverStyle(element) {
		element.style.removeProperty('background-color');
		element.style.removeProperty('color');
	}

	$('.select2').on('select2:open', function () {
		setTimeout(function () {
			$('.select2-results__option--selected').removeClass('select2-results__option--selected');
			$('.select2-results__option--highlighted').removeClass('select2-results__option--highlighted');
			$('.select2-results__option--selectable').each(function () {
				clearSelect2HoverStyle(this);
			});
		}, 20);
	});

	$(document).on('mouseenter', '.select2-results__option--selectable', function () {
		setSelect2HoverStyle(this);
	});

	$(document).on('mouseleave', '.select2-results__option--selectable', function () {
		clearSelect2HoverStyle(this);
	});
		
	function init() {
		var $this = Sidemenu;
		function getMenuKey(link) {
			return $.trim($(link).find('span').first().text() || link.textContent).toLowerCase();
		}

		$(document).off('click.sidebarMenu', '#sidebar-menu a').on('click.sidebarMenu', '#sidebar-menu a', function(e) {
			if (!$(this).parent().hasClass('submenu')) {
				return;
			}

			var menuKey = getMenuKey(this);
			var isOpening = !$(this).hasClass('subdrop');
			e.preventDefault();
			if(!$(this).hasClass('subdrop')) {
				$(this).addClass('sidebar-label-hidden');
				$('#sidebar-menu > ul > li.submenu > a').not(this).removeClass('active subdrop');
				$('#sidebar-menu > ul > li.submenu > ul').not($(this).next('ul')).stop(true, true).slideUp(200);
				$(this).next('ul').stop(true, true).slideDown(350);
				$(this).removeClass('active').addClass('subdrop');
			} else if($(this).hasClass('subdrop')) {
				$(this).removeClass('subdrop sidebar-label-hidden').trigger('blur');
				$(this).next('ul').stop(true, true).slideUp(350);
			}

			window.pharmacyOpenMenu = isOpening ? menuKey : '';
		});

		$(document).off('click.sidebarMenuOutside').on('click.sidebarMenuOutside', function(e) {
			if ($(e.target).closest('#sidebar-menu').length) {
				return;
			}

			var $openLinks = $('#sidebar-menu > ul > li.submenu > a.subdrop');
			if (!$('body').hasClass('mini-sidebar')) {
				var $activeSubmenu = $('#sidebar-menu ul ul a.active').parents('li.submenu').first();
				if (!$activeSubmenu.length || $openLinks.is($activeSubmenu.children('a:first'))) {
					return;
				}

				$openLinks.removeClass('active subdrop sidebar-label-hidden').trigger('blur');
				$openLinks.next('ul').stop(true, true).slideUp(200);
				$activeSubmenu.children('a:first').addClass('active subdrop');
				$activeSubmenu.children('ul:first').stop(true, true).slideDown(200);
				window.pharmacyOpenMenu = getMenuKey($activeSubmenu.children('a:first'));
				return;
			}

			var $activeSubmenu = $('#sidebar-menu ul ul a.active').parents('li.submenu').first();
			$openLinks.removeClass('active subdrop sidebar-label-hidden').trigger('blur');
			$openLinks.next('ul').stop(true, true).slideUp(200);
			if ($activeSubmenu.length) {
				$activeSubmenu.children('a:first').addClass('active');
				window.pharmacyOpenMenu = getMenuKey($activeSubmenu.children('a:first'));
			} else {
				window.pharmacyOpenMenu = '';
			}
		});

		$(document).off('click.sidebarSubmenuItem', '#sidebar-menu ul ul a').on('click.sidebarSubmenuItem', '#sidebar-menu ul ul a', function() {
			var $parentSubmenu = $(this).closest('li.submenu');
			var $parentLink = $parentSubmenu.children('a:first');

			// Keep the parent menu open while Turbo replaces the page.
			window.pharmacyOpenMenu = getMenuKey($parentLink);
			$parentLink.addClass('active subdrop');
			$parentSubmenu.children('ul:first').stop(true, true).show();
		});

		var $activeSubmenu = $('#sidebar-menu ul ul a.active').parents('li.submenu').first();
		$('#sidebar-menu > ul > li.submenu > a').removeClass('active subdrop');
		var $openSubmenu = $activeSubmenu;

		if ($openSubmenu.length) {
			$openSubmenu.children('a:first').addClass('active');
			if (!$('body').hasClass('mini-sidebar')) {
				$openSubmenu.children('a:first').addClass('subdrop');
				$openSubmenu.children('ul:first').show();
			}
		}
	}

	window.pharmacySidebarInit = init;
	
	// Sidebar Initiate
	init();
	
	// Mobile menu sidebar overlay
	
	$('body').append('<div class="sidebar-overlay"></div>');
	$(document).on('click', '#mobile_btn', function() {
		$wrapper.toggleClass('slide-nav');
		$('.sidebar-overlay').toggleClass('opened');
		$('html').addClass('menu-opened');
		return false;
	});
	
	// Sidebar overlay
	
	$(".sidebar-overlay").on("click", function () {
		$wrapper.removeClass('slide-nav');
		$(".sidebar-overlay").removeClass("opened");
		$('html').removeClass('menu-opened');
	});
	
	// Page Content Height
	
	if($('.page-wrapper').length > 0 ){
		var height = $(window).height();	
		$(".page-wrapper").css("min-height", height);
	}
	
	// Page Content Height Resize
	
	$(window).resize(function(){
		if($('.page-wrapper').length > 0 ){
			var height = $(window).height();
			$(".page-wrapper").css("min-height", height);
		}
	});
	
	
	
	// Datetimepicker
	
	if($('.datetimepicker').length > 0 ){
		$('.datetimepicker').datetimepicker({
			format: 'DD/MM/YYYY',
			icons: {
				up: "fa fa-angle-up",
				down: "fa fa-angle-down",
				next: 'fa fa-angle-right',
				previous: 'fa fa-angle-left'
			}
		});
		$('.datetimepicker').on('dp.show',function() {
			$(this).closest('.table-responsive').removeClass('table-responsive').addClass('temp');
		}).on('dp.hide',function() {
			$(this).closest('.temp').addClass('table-responsive').removeClass('temp')
		});
	}

	// Tooltip
	
	if($('[data-toggle="tooltip"]').length > 0 ){
		$('[data-toggle="tooltip"]').tooltip();
	}
	
   
	
	// Email Inbox

	if($('.clickable-row').length > 0 ){
		$(document).on('click', '.clickable-row', function() {
			window.location = $(this).data("href");
		});
	}

	// Check all email
	
	$(document).on('click', '#check_all', function() {
		$('.checkmail').click();
		return false;
	});
	if($('.checkmail').length > 0) {
		$('.checkmail').each(function() {
			$(this).on('click', function() {
				if($(this).closest('tr').hasClass('checked')) {
					$(this).closest('tr').removeClass('checked');
				} else {
					$(this).closest('tr').addClass('checked');
				}
			});
		});
	}
	
	// Mail important
	
	$(document).on('click', '.mail-important', function() {
		$(this).find('i.fa').toggleClass('fa-star').toggleClass('fa-star-o');
	});
	
	
	
    // Product thumb images

    if ($('.proimage-thumb li a').length > 0) {
        var full_image = $(this).attr("href");
        $(".proimage-thumb li a").click(function() {
            full_image = $(this).attr("href");
            $(".pro-image img").attr("src", full_image);
            $(".pro-image img").parent().attr("href", full_image);
            return false;
        });
    }

    // Lightgallery

    if ($('#pro_popup').length > 0) {
        $('#pro_popup').lightGallery({
            thumbnail: true,
            selector: 'a'
        });
    }
	
	// Sidebar Slimscroll
	function initSidebarSlimScroll() {
		var $currentSlimScrolls = $('.slimscroll');
		if ($currentSlimScrolls.length === 0) {
			return;
		}

		$currentSlimScrolls.each(function() {
			var $item = $(this);
			if ($item.data('slimscroll-initialized')) {
				$item.slimScroll({ destroy: true });
				$item.removeData('slimscroll-initialized');
			}
		});

		$currentSlimScrolls.slimScroll({
			height: 'auto',
			width: '100%',
			position: 'right',
			size: '7px',
			color: '#ccc',
			allowPageScroll: false,
			wheelStep: 10,
			touchScrollStep: 100
		});
		$currentSlimScrolls.data('slimscroll-initialized', true);

		var wHeight = $(window).height() - 60;
		$currentSlimScrolls.height(wHeight);
		$('.sidebar .slimScrollDiv').height(wHeight);
	}

	window.pharmacySidebarSlimScrollInit = initSidebarSlimScroll;
	initSidebarSlimScroll();
	$(window).off('resize.sidebarSlimScroll').on('resize.sidebarSlimScroll', function() {
		var rHeight = $(window).height() - 60;
		$('.slimscroll').height(rHeight);
		$('.sidebar .slimScrollDiv').height(rHeight);
	});
	
	// Small Sidebar
	var sidebarStateKey = 'pharmacy-sidebar-collapsed';

	function updateSidebarToggleState() {
		var isCollapsed = $('body').hasClass('mini-sidebar');
		$('#toggle_btn').attr('aria-expanded', isCollapsed ? 'false' : 'true');
		try {
			localStorage.setItem(sidebarStateKey, isCollapsed ? 'true' : 'false');
		} catch (error) {
		}
	}

	$(document).on('click', '#toggle_btn', function() {
		if($('body').hasClass('mini-sidebar')) {
			$('body').removeClass('mini-sidebar');
			var openMenuKey = window.pharmacyOpenMenu || '';
			var $activeSubmenu = $('#sidebar-menu li.submenu').filter(function() {
				var link = $(this).children('a:first');
				var label = $.trim(link.find('span').first().text() || link.text()).toLowerCase();
				return openMenuKey && label === openMenuKey;
			}).first();
			if (!$activeSubmenu.length) {
				$activeSubmenu = $('#sidebar-menu ul ul a.active').parents('li.submenu').first();
			}
			$('#sidebar-menu > ul > li.submenu > a').removeClass('active subdrop');
			if ($activeSubmenu.length) {
				$activeSubmenu.children('a:first').addClass('subdrop');
				if ($activeSubmenu.find('ul a.active').length) {
					$activeSubmenu.children('a:first').addClass('active');
				}
				$activeSubmenu.children('ul:first').stop(true, true).show();
			}
		} else {
			$('body').addClass('mini-sidebar');
			$('#sidebar-menu > ul > li.submenu > a').removeClass('subdrop');
			$('#sidebar-menu > ul > li.submenu > ul').stop(true, true).hide();
		}
		updateSidebarToggleState();

		// Let responsive widgets (Chart.js, DataTables) re-measure after the
		// sidebar width transition finishes; otherwise they keep their stale
		// pixel widths and leave a gap next to the content.
		if (window.pharmacySidebarReflowTimer) {
			clearTimeout(window.pharmacySidebarReflowTimer);
		}
		window.pharmacySidebarReflowTimer = setTimeout(function () {
			window.dispatchEvent(new Event('resize'));
		}, 450);

		return false;
	});
	$(document).ready(function(){
		
		// Delete confirmation modal (delegated: rows are re-rendered by Tabulator/AJAX/Turbo)
		$(document).off('click.pharmacyDelete').on('click.pharmacyDelete', '.deletebtn, #deletebtn', function (e){
			e.preventDefault();
			$('#delete_id').val($(this).data('id'));
			$('#deleteConfirmModal').modal('show');
		});
		
		
	});
		
})(jQuery);