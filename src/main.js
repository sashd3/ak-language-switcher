/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import LanguageSwitcher from './components/LanguageSwitcher.vue'

document.addEventListener('DOMContentLoaded', () => {
	const headerEnd = document.querySelector('.header-end')

	// Detect public page: body#body-public, or #public-page-menu, or .public-page-menu__wrapper
	const isPublicPage = document.body.id === 'body-public'
		|| document.getElementById('public-page-menu')
		|| document.querySelector('.public-page-menu__wrapper')

	let mountEl = null
	let mode = null

	if (isPublicPage && headerEnd) {
		mode = 'public'
		mountEl = document.createElement('div')
		mountEl.id = 'ak-language-switcher'
		// Insert before user menu (various IDs depending on NC app)
		const userMenu = document.getElementById('public-page-user-menu')
			|| document.getElementById('user-menu')
		if (userMenu) {
			headerEnd.insertBefore(mountEl, userMenu)
		} else {
			const firstNav = headerEnd.querySelector('nav')
			if (firstNav) {
				headerEnd.insertBefore(mountEl, firstNav)
			} else {
				headerEnd.appendChild(mountEl)
			}
		}
	} else if (headerEnd) {
		mode = 'authenticated'
		mountEl = document.createElement('div')
		mountEl.id = 'ak-language-switcher'
		const userMenu = document.getElementById('user-menu')
		if (userMenu) {
			headerEnd.insertBefore(mountEl, userMenu)
		} else {
			headerEnd.appendChild(mountEl)
		}
	}

	if (mountEl && mode) {
		new Vue({
			el: mountEl,
			render: (h) => h(LanguageSwitcher, { props: { mode } }),
		})
	}
})
