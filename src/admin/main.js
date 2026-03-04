import Vue from 'vue'
import AdminSettings from './AdminSettings.vue'

const el = document.getElementById('ak-language-switcher-admin')
if (el) {
	new Vue({
		el,
		render: (h) => h(AdminSettings),
	})
}
