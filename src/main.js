import Vue from 'vue'
import LanguageSwitcher from './components/LanguageSwitcher.vue'

document.addEventListener('DOMContentLoaded', () => {
	const headerEnd = document.querySelector('.header-end')
	const publicMenu = document.getElementById('public-page-menu')

	let mountEl = null
	let mode = null

	if (headerEnd) {
		mode = 'authenticated'
		mountEl = document.createElement('div')
		mountEl.id = 'nc-language-switcher'
		const userMenu = document.getElementById('user-menu')
		if (userMenu) {
			headerEnd.insertBefore(mountEl, userMenu)
		} else {
			headerEnd.appendChild(mountEl)
		}
	} else if (publicMenu) {
		mode = 'public'
		mountEl = document.createElement('div')
		mountEl.id = 'nc-language-switcher'
		publicMenu.appendChild(mountEl)
	}

	if (mountEl && mode) {
		new Vue({
			el: mountEl,
			render: (h) => h(LanguageSwitcher, { props: { mode } }),
		})
	}
})
