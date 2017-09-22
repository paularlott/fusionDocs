$(document).ready(function() {

	/**
	 * Make header sticky
	 */
	$('#pageHeader').makeSticky({offset: 1});

	/**
	 * Main menu navigation
	 */
	$('.mainMenu li, #slideInMenu li').each(function () {
		if ($(this).find('ul').length) {
			$(this).find('a, .folder').first().append('<span class="sub"></span>');
			$(this).find('.sub').click(function (e) {
				e.preventDefault();
				$(this).closest('li').find('ul').first().slideToggle();
				$(this).toggleClass('open');
			});
		}
	});

	// Open the active menu
	$('.mainMenu .active').each(function () {
		var e = $(this).closest('ul');
		while (!e.hasClass('mainMenu')) {
			e.show();
			e.parent().find('.folder .sub, a .sub').first().addClass('open');
			e = e.parent().closest('ul');
		}
	});

	// Search
	$('#viewSearch').on('click', function() {
		$('.searchForm').parent().toggleClass('openMobileSearch');
	});

	// Change version
	$('#versionSelectControl').on('change', function() {
		window.location.href = $(this).val();
	});

});
