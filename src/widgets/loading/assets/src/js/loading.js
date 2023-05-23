/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

'use strict';
(function($) {
	const loadingContainer = $('#lds-roller-overlay');
	$.ajaxSetup({
		showLoading: false,
		beforeSend: function(context, config) {
			if (config.showLoading) {
				loadingContainer.show();
			}
		},
		complete() {
			loadingContainer.hide();
		},
	});
})(jQuery);