import Vue from 'vue'
import AdminSettings from './AdminSettings.vue'

const el = document.getElementById('nc-language-switcher-admin')
if (el) {
	new Vue({
		el,
		render: (h) => h(AdminSettings),
	})
}
