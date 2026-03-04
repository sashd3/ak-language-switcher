<template>
	<div class="ak-language-switcher-admin">
		<div class="ak-language-switcher-admin__header">
			<h2>Language Switcher</h2>
			<NcButton type="tertiary"
				:aria-label="t('ak_language_switcher', 'Help')"
				@click="showHelp = true">
				<template #icon>
					<span class="ak-language-switcher-admin__help-icon">?</span>
				</template>
			</NcButton>
		</div>

		<NcDialog v-if="showHelp"
			:name="t('ak_language_switcher', 'Language Switcher Help')"
			@closing="showHelp = false">
			<div class="ak-language-switcher-admin__help-content">
				<h3>{{ t('ak_language_switcher', 'Overview') }}</h3>
				<p>{{ t('ak_language_switcher', 'This app adds a language switcher to the Nextcloud header bar. It works for both logged-in users and anonymous visitors on public share pages.') }}</p>

				<h3>{{ t('ak_language_switcher', 'How it works') }}</h3>
				<p><strong>{{ t('ak_language_switcher', 'Logged-in users:') }}</strong> {{ t('ak_language_switcher', 'Selecting a language saves it as the user preference. This is the same setting as in Personal Settings > Language.') }}</p>
				<p><strong>{{ t('ak_language_switcher', 'Public/Share pages:') }}</strong> {{ t('ak_language_switcher', 'A temporary cookie is set (24h) that overrides the browser language detection. On first visit, the browser language is used as usual.') }}</p>

				<h3>{{ t('ak_language_switcher', 'Admin settings') }}</h3>
				<ul>
					<li>{{ t('ak_language_switcher', 'Enable/Disable: Show or hide the language switcher globally.') }}</li>
					<li>{{ t('ak_language_switcher', 'Icon: Choose from 6 icons. Adjust size, stroke width, and color.') }}</li>
					<li>{{ t('ak_language_switcher', 'Allowed languages: Restrict which languages appear in the switcher. Leave empty to show all installed languages.') }}</li>
				</ul>

				<h3>{{ t('ak_language_switcher', 'Requirements') }}</h3>
				<p>{{ t('ak_language_switcher', 'Nextcloud 27 or newer. The PHP intl extension is recommended for native language names.') }}</p>
			</div>
		</NcDialog>

		<div class="ak-language-switcher-admin__toggle">
			<NcCheckboxRadioSwitch :checked.sync="enabled"
				type="switch"
				@update:checked="dirty = true">
				{{ t('ak_language_switcher', 'Enable language switcher') }}
			</NcCheckboxRadioSwitch>
		</div>

		<div v-if="enabled">
			<h3>{{ t('ak_language_switcher', 'Icon') }}</h3>

			<div class="ak-language-switcher-admin__icon-select">
				<button v-for="name in iconOptionNames"
					:key="name"
					class="ak-language-switcher-admin__icon-btn"
					:class="{ 'ak-language-switcher-admin__icon-btn--active': icon === name }"
					:title="name"
					@click="icon = name; dirty = true">
					<component :is="allIcons[name]" :size="24" />
				</button>
			</div>

			<div class="ak-language-switcher-admin__icon-settings">
				<div class="ak-language-switcher-admin__slider-row">
					<span class="ak-language-switcher-admin__slider-label">{{ t('ak_language_switcher', 'Size') }}</span>
					<input v-model.number="iconSize"
						type="range"
						min="14"
						max="32"
						@input="dirty = true">
					<span class="ak-language-switcher-admin__slider-value">{{ iconSize }}px</span>
				</div>

				<div class="ak-language-switcher-admin__slider-row">
					<span class="ak-language-switcher-admin__slider-label">{{ t('ak_language_switcher', 'Stroke') }}</span>
					<input v-model.number="iconStrokeWidth"
						type="range"
						min="0.5"
						max="4"
						step="0.5"
						@input="dirty = true">
					<span class="ak-language-switcher-admin__slider-value">{{ iconStrokeWidth }}</span>
				</div>

				<div class="ak-language-switcher-admin__color-row">
					<span class="ak-language-switcher-admin__slider-label">{{ t('ak_language_switcher', 'Color') }}</span>
					<input v-model="iconColor"
						type="color"
						@input="dirty = true">
					<button v-if="iconColor"
						class="ak-language-switcher-admin__reset"
						@click="iconColor = ''; dirty = true">
						{{ t('ak_language_switcher', 'Reset') }}
					</button>
				</div>
			</div>

			<div class="ak-language-switcher-admin__preview">
				<span>{{ t('ak_language_switcher', 'Preview:') }}</span>
				<component :is="allIcons[icon]"
					:size="iconSize"
					:stroke-width="iconStrokeWidth"
					:style="iconColor ? { color: iconColor } : {}" />
			</div>

			<h3>{{ t('ak_language_switcher', 'Allowed languages') }}</h3>
			<p class="ak-language-switcher-admin__hint">
				{{ t('ak_language_switcher', 'Select which languages are available in the switcher. If none are selected, all languages will be shown.') }}
			</p>

			<div class="ak-language-switcher-admin__search">
				<input v-model="search"
					type="text"
					:placeholder="t('ak_language_switcher', 'Search languages…')">
			</div>

			<div class="ak-language-switcher-admin__list">
				<NcCheckboxRadioSwitch v-for="lang in filteredLanguages"
					:key="lang.code"
					:checked="isAllowed(lang.code)"
					@update:checked="toggleLanguage(lang.code, $event)">
					{{ lang.name }}
				</NcCheckboxRadioSwitch>
			</div>
		</div>

		<Transition name="fade">
			<div v-if="dirty || saved" class="ak-language-switcher-admin__save-bar">
				<NcButton type="primary"
					:disabled="saving"
					@click="save">
					{{ saving ? t('ak_language_switcher', 'Saving…') : t('ak_language_switcher', 'Save') }}
				</NcButton>
				<span v-if="saved" class="ak-language-switcher-admin__saved">
					✓ {{ t('ak_language_switcher', 'Saved') }}
				</span>
			</div>
		</Transition>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcDialog from '@nextcloud/vue/dist/Components/NcDialog.js'
import { translate as t } from '@nextcloud/l10n'
import { ICON_MAP, ICON_OPTIONS } from '../components/icons.js'

export default {
	name: 'AdminSettings',

	components: {
		NcCheckboxRadioSwitch,
		NcButton,
		NcDialog,
	},

	data() {
		return {
			enabled: loadState('ak_language_switcher', 'adminEnabled', true),
			allowedLanguages: loadState('ak_language_switcher', 'adminAllowedLanguages', []),
			allLanguages: loadState('ak_language_switcher', 'adminAllLanguages', []),
			icon: loadState('ak_language_switcher', 'adminIcon', 'Globe'),
			iconSize: loadState('ak_language_switcher', 'adminIconSize', 20),
			iconColor: loadState('ak_language_switcher', 'adminIconColor', ''),
			iconStrokeWidth: loadState('ak_language_switcher', 'adminIconStrokeWidth', 2),
			iconOptionNames: ICON_OPTIONS,
			allIcons: ICON_MAP,
			search: '',
			dirty: false,
			saving: false,
			saved: false,
			showHelp: false,
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
			this.dirty = true
		},

		async save() {
			this.saving = true
			this.saved = false
			try {
				await axios.post(generateUrl('/apps/ak_language_switcher/admin/settings'), {
					enabled: this.enabled,
					allowedLanguages: this.allowedLanguages,
					icon: this.icon,
					iconSize: this.iconSize,
					iconColor: this.iconColor,
					iconStrokeWidth: this.iconStrokeWidth,
				})
				this.dirty = false
				this.saved = true
				setTimeout(() => { window.location.reload() }, 1000)
			} catch (e) {
				console.error('Failed to save settings', e)
			}
			this.saving = false
		},
	},
}
</script>

<style scoped>
.ak-language-switcher-admin {
	padding: 20px;
	max-width: 800px;
}

.ak-language-switcher-admin__header {
	display: flex;
	align-items: center;
	gap: 8px;
}

.ak-language-switcher-admin__header h2 {
	margin: 0;
}

.ak-language-switcher-admin__help-icon {
	font-weight: bold;
	font-size: 16px;
}

.ak-language-switcher-admin__help-content {
	padding: 0 16px 16px;
}

.ak-language-switcher-admin__help-content h3 {
	margin-top: 16px;
	margin-bottom: 4px;
}

.ak-language-switcher-admin__help-content p {
	margin: 4px 0;
}

.ak-language-switcher-admin__help-content ul {
	margin: 4px 0;
	padding-left: 20px;
}

.ak-language-switcher-admin__toggle {
	margin: 16px 0;
}

.ak-language-switcher-admin__hint {
	color: var(--color-text-maxcontrast);
	margin-bottom: 12px;
}

.ak-language-switcher-admin__icon-select {
	display: flex;
	gap: 8px;
	margin-bottom: 16px;
}

.ak-language-switcher-admin__icon-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	border: 2px solid var(--color-border);
	border-radius: var(--border-radius);
	background: none;
	cursor: pointer;
	color: var(--color-main-text);
}

.ak-language-switcher-admin__icon-btn:hover {
	background-color: var(--color-background-hover);
}

.ak-language-switcher-admin__icon-btn--active {
	border-color: var(--color-primary-element);
	background-color: var(--color-primary-element-light);
}

/* Slider rows: aligned labels, sliders, values */
.ak-language-switcher-admin__icon-settings {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 16px;
}

.ak-language-switcher-admin__slider-row,
.ak-language-switcher-admin__color-row {
	display: grid;
	grid-template-columns: 70px 200px 50px;
	align-items: center;
	gap: 12px;
}

.ak-language-switcher-admin__slider-label {
	font-size: 14px;
	color: var(--color-main-text);
}

.ak-language-switcher-admin__slider-value {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

/* NC-themed range slider */
.ak-language-switcher-admin__slider-row input[type="range"] {
	-webkit-appearance: none;
	appearance: none;
	width: 100%;
	height: 6px;
	background: var(--color-background-dark);
	border-radius: 3px;
	outline: none;
}

.ak-language-switcher-admin__slider-row input[type="range"]::-webkit-slider-thumb {
	-webkit-appearance: none;
	appearance: none;
	width: 16px;
	height: 16px;
	border-radius: 50%;
	background: var(--color-primary-element);
	cursor: pointer;
	border: none;
}

.ak-language-switcher-admin__slider-row input[type="range"]::-moz-range-thumb {
	width: 16px;
	height: 16px;
	border-radius: 50%;
	background: var(--color-primary-element);
	cursor: pointer;
	border: none;
}

/* NC-themed color picker: round */
.ak-language-switcher-admin__color-row input[type="color"] {
	-webkit-appearance: none;
	appearance: none;
	width: 32px;
	height: 32px;
	padding: 2px;
	border: 2px solid var(--color-border);
	border-radius: 50%;
	cursor: pointer;
	background: none;
	overflow: hidden;
}

.ak-language-switcher-admin__color-row input[type="color"]::-webkit-color-swatch-wrapper {
	padding: 0;
}

.ak-language-switcher-admin__color-row input[type="color"]::-webkit-color-swatch {
	border: none;
	border-radius: 50%;
}

.ak-language-switcher-admin__color-row input[type="color"]::-moz-color-swatch {
	border: none;
	border-radius: 50%;
}

.ak-language-switcher-admin__reset {
	background: none;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-pill, 20px);
	padding: 4px 12px;
	cursor: pointer;
	font-size: 12px;
	color: var(--color-main-text);
	justify-self: start;
}

.ak-language-switcher-admin__reset:hover {
	background-color: var(--color-background-hover);
}

.ak-language-switcher-admin__preview {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 20px;
	padding: 8px 12px;
	border: 1px dashed var(--color-border);
	border-radius: var(--border-radius);
	background: var(--color-background-dark);
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.ak-language-switcher-admin__search input {
	width: 100%;
	max-width: 400px;
	padding: 8px;
	margin-bottom: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
}

.ak-language-switcher-admin__list {
	max-height: 400px;
	overflow-y: auto;
}

/* Save bar */
.ak-language-switcher-admin__save-bar {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-top: 20px;
	padding: 12px 0;
}

.ak-language-switcher-admin__saved {
	color: var(--color-success);
	font-size: 14px;
}

.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
	opacity: 0;
}
</style>
