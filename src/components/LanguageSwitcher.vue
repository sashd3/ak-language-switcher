<!--
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcHeaderMenu id="ak-language-switcher-menu"
		class="ak-language-switcher"
		:aria-label="t('ak_language_switcher', 'Language')"
		:title="currentName">
		<template #trigger>
			<component :is="iconComponent"
				:size="iconSize"
				:stroke-width="iconStrokeWidth"
				:style="iconStyle" />
		</template>
		<div class="ak-language-switcher__container">
			<input v-model="search"
				class="ak-language-switcher__search"
				type="text"
				:aria-label="t('ak_language_switcher', 'Search languages')"
				:placeholder="t('ak_language_switcher', 'Search languages…')"
				@keydown.down.prevent="focusNext"
				@keydown.up.prevent="focusPrev">
			<ul class="ak-language-switcher__list"
				role="listbox"
				:aria-label="t('ak_language_switcher', 'Available languages')">
				<li v-for="(lang, index) in filteredLanguages"
					:key="lang.code"
					class="ak-language-switcher__item"
					:class="{ 'ak-language-switcher__item--active': lang.code === currentLanguage }"
					role="option"
					tabindex="0"
					:aria-selected="lang.code === currentLanguage"
					@click="switchLanguage(lang.code)"
					@keydown.enter.prevent="switchLanguage(lang.code)"
					@keydown.down.prevent="focusNext"
					@keydown.up.prevent="focusPrev">
					<span class="ak-language-switcher__name">{{ lang.name }}</span>
				</li>
			</ul>
		</div>
	</NcHeaderMenu>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import NcHeaderMenu from '@nextcloud/vue/dist/Components/NcHeaderMenu.js'
import { translate as t } from '@nextcloud/l10n'
import { ICON_MAP, Globe } from './icons.js'

export default {
	name: 'LanguageSwitcher',

	components: {
		NcHeaderMenu,
	},

	props: {
		mode: {
			type: String,
			required: true,
			validator: (v) => ['authenticated', 'public'].includes(v),
		},
	},

	data() {
		return {
			languages: loadState('ak_language_switcher', 'languages', []),
			currentLanguage: loadState('ak_language_switcher', 'currentLanguage', 'en'),
			iconName: loadState('ak_language_switcher', 'icon', 'Globe'),
			iconSize: loadState('ak_language_switcher', 'iconSize', 20),
			iconColor: loadState('ak_language_switcher', 'iconColor', ''),
			iconStrokeWidth: loadState('ak_language_switcher', 'iconStrokeWidth', 2),
			search: '',
		}
	},

	computed: {
		filteredLanguages() {
			if (!this.search) {
				return this.languages
			}
			const q = this.search.toLowerCase()
			return this.languages.filter(
				(l) => l.name.toLowerCase().includes(q) || l.code.toLowerCase().includes(q),
			)
		},
		currentName() {
			const lang = this.languages.find((l) => l.code === this.currentLanguage)
			return lang ? lang.name : this.currentLanguage
		},
		iconComponent() {
			return ICON_MAP[this.iconName] || Globe
		},
		iconStyle() {
			return this.iconColor ? { color: this.iconColor } : {}
		},
	},

	methods: {
		t,

		focusNext(e) {
			const items = this.$el.querySelectorAll('.ak-language-switcher__item')
			const current = document.activeElement
			const index = Array.from(items).indexOf(current)
			if (index < items.length - 1) {
				items[index + 1].focus()
			} else if (current.classList.contains('ak-language-switcher__search')) {
				items[0]?.focus()
			}
		},

		focusPrev(e) {
			const items = this.$el.querySelectorAll('.ak-language-switcher__item')
			const current = document.activeElement
			const index = Array.from(items).indexOf(current)
			if (index > 0) {
				items[index - 1].focus()
			} else if (index === 0) {
				this.$el.querySelector('.ak-language-switcher__search')?.focus()
			}
		},

		async switchLanguage(lang) {
			if (lang === this.currentLanguage) {
				return
			}
			if (this.mode === 'authenticated') {
				try {
					await axios.post(generateUrl('/apps/ak_language_switcher/language'), { lang })
				} catch (e) {
					console.error('Failed to set language', e)
					return
				}
			} else {
				const secure = window.location.protocol === 'https:' ? '; Secure' : ''
				document.cookie = `nc_language=${encodeURIComponent(lang)}; path=/; SameSite=Lax; max-age=86400${secure}`
			}
			window.location.reload()
		},
	},
}
</script>

<style>
.ak-language-switcher__container {
	padding: 12px;
	min-width: 220px;
	display: flex;
	flex-direction: column;
	max-height: 400px;
}

.ak-language-switcher__search {
	width: 100%;
	padding: 8px 12px;
	margin-bottom: 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	box-sizing: border-box;
	flex-shrink: 0;
}

.ak-language-switcher__list {
	list-style: none;
	margin: 0;
	padding: 0;
	overflow-y: auto;
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

#ak-language-switcher-menu ul li.ak-language-switcher__item {
	display: block !important;
	padding: 0 !important;
	margin: 0 !important;
	cursor: pointer !important;
	border-radius: var(--border-radius) !important;
	transition: background-color 0.15s ease !important;
	width: 100% !important;
	box-sizing: border-box !important;
	min-height: 44px !important;
	line-height: 44px !important;
	background-color: transparent !important;
}

#ak-language-switcher-menu ul li.ak-language-switcher__item:hover {
	background-color: var(--color-background-hover) !important;
}

#ak-language-switcher-menu ul li.ak-language-switcher__item:focus {
	background-color: var(--color-background-hover) !important;
	outline: 2px solid var(--color-primary-element) !important;
	outline-offset: -2px;
}

#ak-language-switcher-menu ul li.ak-language-switcher__item--active {
	background-color: var(--color-primary-element-light) !important;
	font-weight: bold !important;
}

#ak-language-switcher-menu ul li.ak-language-switcher__item--active:hover {
	background-color: var(--color-primary-element-hover) !important;
}

.ak-language-switcher__name {
	display: block;
	padding: 0 12px;
	cursor: pointer;
	text-transform: capitalize;
}

/* Public pages only: position before avatar menu */
#body-public #ak-language-switcher-menu {
	order: -1;
}
</style>
