/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */
(() => {
	'use strict'
	window.appParams = {
		params: {},
		configure(params) {
			this.params = params
		},
		getParams() {
			return this.params;
		},
		getParam(name) {
			if (typeof this.params[name] === 'undefined') {
				throw new Error(`param ${name} not found`);
			}
			return this.params[name];
		},
		serverRequestInterval() {
			try {
				return this.getParam('server_data_interval') * 1000;
			} catch (e) {
				return 3000;
			}
		},
	};
})()
