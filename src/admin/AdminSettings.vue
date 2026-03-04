<template>
	<div class="nc-language-switcher-admin">
		<h2>Language Switcher</h2>

		<div class="nc-language-switcher-admin__toggle">
			<NcCheckboxRadioSwitch :checked.sync="enabled"
				type="switch"
				@update:checked="save">
				{{ t('nc_language_switcher', 'Enable language switcher') }}
			</NcCheckboxRadioSwitch>
		</div>

		<div v-if="enabled" class="nc-language-switcher-admin__languages">
			<h3>{{ t('nc_language_switcher', 'Allowed languages') }}</h3>
			<p class="nc-language-switcher-admin__hint">
				{{ t('nc_language_switcher', 'Select which languages are available in the switcher. If none are selected, all languages will be shown.') }}
			</p>

			<div class="nc-language-switcher-admin__search">
				<input v-model="search"
					type="text"
					:placeholder="t('nc_language_switcher', 'Search languages…')">
			</div>

			<div class="nc-language-switcher-admin__list">
				<NcCheckboxRadioSwitch v-for="lang in filteredLanguages"
					:key="lang.code"
					:checked="isAllowed(lang.code)"
					@update:checked="toggleLanguage(lang.code, $event)">
					{{ lang.name }} ({{ lang.code }})
				</NcCheckboxRadioSwitch>
			</div>
		</div>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'AdminSettings',

	components: {
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			enabled: loadState('nc_language_switcher', 'adminEnabled', true),
			allowedLanguages: loadState('nc_language_switcher', 'adminAllowedLanguages', []),
			allLanguages: loadState('nc_language_switcher', 'adminAllLanguages', []),
			search: '',
		}
	},

	computed: {
		filteredLanguages() {
			if (!this.search) {
				return this.allLanguages
			}
			const q = this.search.toLowerCase()
			return this.allLanguages.filter(
				(l) => l.name.toLowerCase().includes(q) || l.code.toLowerCase().includes(q),
			)
		},
	},

	methods: {
		t,

		isAllowed(code) {
			return this.allowedLanguages.includes(code)
		},

		toggleLanguage(code, checked) {
			if (checked) {
				this.allowedLanguages.push(code)
			} else {
				this.allowedLanguages = this.allowedLanguages.filter((c) => c !== code)
			}
			this.save()
		},

		async save() {
			try {
				await axios.post(generateUrl('/apps/nc_language_switcher/admin/settings'), {
					enabled: this.enabled,
					allowedLanguages: this.allowedLanguages,
				})
			} catch (e) {
				console.error('Failed to save settings', e)
			}
		},
	},
}
</script>

<style scoped>
.nc-language-switcher-admin {
	padding: 20px;
}

.nc-language-switcher-admin__toggle {
	margin-bottom: 16px;
}

.nc-language-switcher-admin__hint {
	color: var(--color-text-maxcontrast);
	margin-bottom: 12px;
}

.nc-language-switcher-admin__search input {
	width: 100%;
	max-width: 400px;
	padding: 8px;
	margin-bottom: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
}

.nc-language-switcher-admin__list {
	max-height: 400px;
	overflow-y: auto;
}
</style>
