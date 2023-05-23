/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */
'use strict';
(function($) {
	$.fn.extend({
		banList: function() {
			const grid = $(this);
			const pjax = grid.closest('[data-pjax-container]');
			const updateFlags = function() {
				const flags = $('[data-ban-flag]');
				flags.each(function() {
					const flag = $(this);
					$.ajax({
						type: 'POST',
						url: UrlManager.createUrl('bans/flag', {id: flag.data('ban-flag')}),
						showNoty: false,
					}).then(function(response) {
						if (response !== null) {
							flag.attr('src', response.url);
							flag.attr('title', response.country);
						}
					});
				});
			};
			return {
				updateFlags: updateFlags,
				pjax: pjax.length ? pjax : null
			};
		},
		serverOnlineInfo: function() {
			$(this).each(function() {
				const $this = $(this);
				const ip = $this.data('server-ip');
				const port = $this.data('server-port');
				const type = $this.data('server-type');
				if (!ip && !port && !type) {
					return;
				}
				const offlineContainerCssClass = $this.data('offline-class');
				const offlineMessage = $this.data('offline-message');
				let updateInterval = $this.data('update-interval');
				if (typeof updateInterval === 'undefined') {
					updateInterval = appParams.serverRequestInterval();
				}
				const elements = {
					status: $this.find('[data-server-status]'),
					hostname: $this.find('[data-server-hostname]'),
					modIcon: $this.find('[data-server-mod-icon]'),
					osIcon: $this.find('[data-server-os-icon]'),
					vacIcon: $this.find('[data-server-vac-icon]'),
					totalPlayers: $this.find('[data-server-total-players]'),
					maxPlayers: $this.find('[data-server-max-payers]'),
					onlinePlayers: $this.find('[data-server-online-payers]'),
					map: $this.find('[data-server-map]'),
					mapImage: $this.find('[data-server-map-image]'),
					nextMap: $this.find('[data-server-next-map]'),
					nextMapTime: $this.find('[data-server-next-map-time]'),
				};
				let playersSort = '';
				const playersList = $this.find('[data-server-players-list]');
				const playerTemplateElement = $this.find('[data-server-players-player-template]');
				const playerTemplateNode = $(playerTemplateElement.prop('content'));
				const playersSortLinks = $this.find('[data-server-players-sort]');
				if (playersSortLinks.length) {
					let playersSortField;
					let playersSortAsc = true;
					playersSortLinks.on('click', function (e) {
						e.preventDefault();
						const sortLink = $(this);
						const sortField = sortLink.data('server-players-sort');
						if (sortField !== playersSortField) {
							playersSortField = sortField;
						}
						playersSort = playersSortField;
						if (!playersSortAsc) {
							playersSort = '-' + playersSort;
						}
						playersSortAsc = !playersSortAsc;
						getData();
					});
				}
				const setImage = function(elementsKey, icon) {
					if (typeof elements[elementsKey] !== 'undefined' && elements[elementsKey].length && !elements[elementsKey].find('img').length) {
						const iconElement = $('<img>');
						iconElement.attr('src', icon);
						elements[elementsKey].html(iconElement);
					}
				};
				const getData = function() {
					$.ajax({
						type: 'POST',
						url: UrlManager.createUrl('servers/online-data'),
						data: {
							game: type,
							ip: ip,
							port: port,
							sort: playersSort
						},
						showNoty: false,
					}).then(function(response) {
						if (offlineContainerCssClass) {
							$this.removeClass(offlineContainerCssClass);
						}
						if (!response || !response.online) {
							if (offlineContainerCssClass) {
								$this.addClass(offlineContainerCssClass);
							}
							if (offlineMessage) {
								const field = elements[offlineMessage.field];
								if (field.length) {
									field.text(offlineMessage.message);
								}
							}
							return;
						}
						const players = response.players.players;
						const playersOrders = response.players.orders;
						if (players.length) {
							playersList.html('');
						}
						if (!$.isEmptyObject(playersOrders) && playersSortLinks.length) {
							const icons = playersSortLinks.find('.sort-icon');
							if (icons.length) {
								icons.each(function () {
									$(this).remove();
								});
							}
						}
						$.each(playersOrders, function (field, direction) {
							const sortLink = $('[data-server-players-sort="'+field+'"]');
							if (sortLink.length) {
								const iconUpClass = field === 'name' ? 'fa-sort-alpha-up' : 'fa-sort-numeric-up';
								const iconDownClass = field === 'name' ? 'fa-sort-alpha-down-alt' : 'fa-sort-numeric-down-alt';
								let icon = sortLink.find('.sort-icon');
								if (!icon.length) {
									icon = $('<i class="sort-icon fas"></i>');
									sortLink.append(icon);
								}
								icon.removeClass(iconUpClass).removeClass(iconDownClass);
								if (direction === 'desc') {
									icon.addClass(iconDownClass);
									playersSort = '-' + playersSort;
								} else {
									icon.addClass(iconUpClass);
								}
							}
						});
						const map = response.map;
						const statusName = response.status;
						const osIcon = response.osIcon;
						const gameIcon = response.gameIcon;
						const vacIcon = response.vacIcon;
						delete response.status;
						delete response.players;
						delete response.map;
						delete response.osIcon;
						delete response.gameIcon;
						delete response.vacIcon;
						if (osIcon) {
							setImage('osIcon', osIcon);
						}
						if (gameIcon) {
							setImage('modIcon', gameIcon);
						}
						if (vacIcon) {
							setImage('vacIcon', vacIcon);
						}
						elements.status.html(statusName);
						elements.map.html(map.current);
						elements.nextMap.html(map.next);
						elements.nextMapTime.html(map.timeLeft);
						if (map.image && map.image.length && elements.mapImage.length) {
							elements.mapImage.attr('src', map.image);
						}
						$.each(response, function(key, val) {
							if (typeof elements[key] !== 'undefined' && val) {
								elements[key].text(val);
							}
						});
						if (playerTemplateElement && players.length) {
							for (let i = 0, iMax = players.length; i < iMax; i++) {
								const playerTemplate = playerTemplateNode.clone();
								playerTemplate.find('[data-server-players-player-name]').text(players[i].name);
								playerTemplate.find('[data-server-players-player-score]').text(players[i].score);
								playerTemplate.find('[data-server-players-player-time]').text(players[i].formattedTime);
								playersList.append(playerTemplate);
							}
						}
					});
				};
				getData();
				if (updateInterval) {
					setInterval(getData, updateInterval);
				}
			});
		},
		banAddContent: function() {
			$(this).each(function() {
				const $this = $(this);
				const pjaxContainer = $this.data('pjax-container');
				const form = $this.find('form');
				const modal = $this.find('.modal');
				$(modal).on('hidden.bs.modal', function() {
					form.trigger('reset');
				})
				form.on('beforeSubmit', function(event) {
					event.preventDefault();
					const form = $(this);
					const fields = form.serializeArray();
					const formData = new FormData();
					for (let i = 0, iMax = fields.length; i < iMax; i++) {
						formData.append(fields[i].name, fields[i].value);
					}
					const file = form.find('input[type="file"]');
					if (file.length && file[0] && file[0].files && file[0].files[0]) {
						formData.append(file.attr('name'), file[0].files[0]);
					}
					$.pjax({
						container: pjaxContainer,
						type: 'POST',
						data: formData,
						push: false,
						replace: false,
						scrollTo: false,
						processData: false,
						contentType: false
					}).done(function() {
						modal.modal('hide');
						form.trigger('reset');
					});
					return false;
				});
			});
		},
	});
	$(document).ready(function() {
		$('[data-ban-add-content]').banAddContent();
		$('[data-server-info]').serverOnlineInfo();
		$(document).on('click', '[data-download-file]', function(event) {
			event.preventDefault();
			const $this = $(this);
			const url = $this.attr('href');
			const pjaxContainer =  $this.closest('[data-pjax-container]');
			if (!pjaxContainer.length) {
				document.location.href = url;
				return;
			}
			$.ajax({
				url: $this.attr('href'),
				type: 'GET',
				xhrFields: { responseType: 'blob' },
				dataType: 'binary',
			}).then(function(response, status, xhr) {
				const contentDispositionHeader = xhr.getResponseHeader('content-disposition');
				let headerFileName = contentDispositionHeader.substring(contentDispositionHeader.indexOf('=') + 1);
				headerFileName = headerFileName.replace(/(")/g, '')
					.replace(/;/g, '')
					.replace(/\+/g, ' ');
				const url = window.URL.createObjectURL(new Blob([response]));
				const linkElement = document.createElement('a');
				linkElement.href = url;
				linkElement.setAttribute('download', decodeURIComponent(headerFileName));
				document.body.appendChild(linkElement);
				linkElement.click();
				document.body.removeChild(linkElement);
				$.pjax.reload('#' + pjaxContainer.attr('id'));
			});
		});
		$(document).on('click', '[data-click-url]', function(e) {
			e.preventDefault();
			document.location.href = $(this).data('click-url');
		});
		const banlist = $('[data-banlist]').banList();
		banlist.updateFlags();
		if (banlist.pjax) {
			banlist.pjax.on('pjax:end', function() {
				banlist.updateFlags();
			})
		}
	});
})(jQuery)
