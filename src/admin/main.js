/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import AdminSettings from './AdminSettings.vue'

const el = document.getElementById('ak-language-switcher-admin')
if (el) {
	new Vue({
		el,
		render: (h) => h(AdminSettings),
	})
}
