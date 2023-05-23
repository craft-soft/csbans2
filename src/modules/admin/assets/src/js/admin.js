/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

(function($) {
	$.fn.extend({
		adminActions: function() {
			const buttons = $(this).find('button[data-system-action]');
			buttons.on('click', function(e) {
				e.preventDefault();
				const button = $(this);
				const confirmText = button.data('confirm-text');
				const text = button.html();
				if (confirmText && !confirm(confirmText)) {
					return;
				}
				button.attr('disabled', true);
				button.html('<i class="fa-solid fa-spinner fa-pulse"></i>');
				$.post('/admin/system/actions', {action: button.data('system-action')}).then(function() {
					button.attr('disabled', false);
					button.removeAttr('disabled');
					button.html(text);
				});
			});
		},
		appVersion: function() {
			const appVersion = $(this);
			if (appVersion.length) {
				$.ajax({
					url: UrlManager.createUrl('admin/system/version'),
					type: 'GET',
					showNoty: false
				}).then(function(response) {
					appVersion.html('');
					appVersion.text(response.text);
					appVersion.addClass('text-' + response.type);
				});
			}
		},
		amxadminsForm: function() {
			const form = $(this);
			const days = form.find('#amxadmin-expireddate');
			const forever = form.find('#amxadmin-forever');
			const serversCheckboxes = $('.amx-admin-server-enabled');
			serversCheckboxes.each(function () {
				const $this = $(this);
				const blocks = $('[data-server-id=' + $this.data('server') + ']');
				if (!$this.is(':checked')) {
					blocks.hide();
				}
				$this.on('click', function () {
					if (!$this.is(':checked')) {
						blocks.hide();
					} else {
						blocks.show();
					}
				});
			});
			if (forever.is(':checked')) {
				days.attr("disabled", true);
			}
			forever.on('click', function () {
				if ($(this).prop("checked")) {
					days.attr("disabled", true);
					days.attr("readonly", true);
				} else {
					days.removeAttr('disabled');
					days.removeAttr('readonly');
				}
			});
		},
	});
	$(document).ready(function() {
		$('#amxadmins-form').amxadminsForm();
		$('#appVersion').appVersion();
		$('#admin-system-actions').adminActions();
		$(document).on('click', '[data-ip-modal]', function(event) {
			event.preventDefault();
			const $this = $(this);
			const modalTemplate = `
<div class="modal fade" id="ipViewModal" tabindex="-1" aria-labelledby="ipViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="ipViewModalLabel"></h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div id="map"></div>
          <ul id="info"></ul>
      </div>
    </div>
  </div>
</div>`;
			let ipModal = $('#ipViewModal');
			if (!ipModal.length) {
				ipModal = $(modalTemplate);
				$(document.body).append(ipModal);
			}
			const label = ipModal.find('#ipViewModalLabel');
			const info = ipModal.find('#info');
			const map = ipModal.find('#map');
			ipModal.modal();
			ipModal.on('hidden.bs.modal', function() {
				label.text('');
				info.html('');
				map.html('');
			});
			$.ajax({
				type: 'POST',
				url: UrlManager.createUrl('/admin/tools/ip-info'),
				showNoty: false,
				showLoading: true,
				data: {ip: $this.data('ip')}
			}).then(function(response) {
				if (response.error) {
					toastr.error(response.error);
					return;
				}
				ipModal.modal('show');
				label.text(response.label);
				if (response.info) {
					for (let i = 0, iMax = response.info.length; i < iMax; i++) {
						info.append($(`<li><strong>${response.info[i].label}:</strong> ${response.info[i].value}</li>`));
					}
				}
				if (response.coords && typeof window.ymaps !== 'undefined') {
					setTimeout(function() {
						map.css('min-height', '480px');
						ymaps.ready(function() {
							var myMap = new ymaps.Map(map[0], {
								center: [
									response.coords.lat,
									response.coords.lon
								],
								zoom: 10
							});
						});
					}, 300);
				}
			});
		});
	});
})(jQuery);
(() => {
	'use strict'

	const storedTheme = localStorage.getItem('admin-theme')
	const getPreferredTheme = () => {
		if (storedTheme) {
			return storedTheme
		}
		return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
	}
	const setTheme = function (theme) {
		if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			document.documentElement.setAttribute('data-bs-theme', 'dark')
		} else {
			document.documentElement.setAttribute('data-bs-theme', theme)
		}
	}
	setTheme(getPreferredTheme())
	const showActiveTheme = theme => {
		const activeThemeIcon = document.querySelector('.theme-icon-active i')
		const btnToActive = document.querySelector('[data-bs-theme-value="' + theme + '"]')
		const svgOfActiveBtn = btnToActive.querySelector('i').getAttribute('class')
		document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
			element.classList.remove('active')
		})
		btnToActive.classList.add('active')
		activeThemeIcon.setAttribute('class', svgOfActiveBtn)
	}
	window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
		if (storedTheme !== 'light' || storedTheme !== 'dark') {
			setTheme(getPreferredTheme())
		}
	})
	window.addEventListener('DOMContentLoaded', () => {
		showActiveTheme(getPreferredTheme())
		document.querySelectorAll('[data-bs-theme-value]')
			.forEach(toggle => {
				toggle.addEventListener('click', () => {
					const theme = toggle.getAttribute('data-bs-theme-value')
					localStorage.setItem('admin-theme', theme)
					setTheme(theme)
					showActiveTheme(theme)
				})
			})
		const sidebarWrapper = document.querySelector('.sidebar-wrapper')
		if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
			OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
				scrollbars: {
					theme: 'os-theme-light',
					autoHide: 'leave',
					clickScroll: true
				}
			})
		}
	})
})()