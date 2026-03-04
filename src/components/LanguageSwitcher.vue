<template>
	<NcHeaderMenu id="nc-language-switcher-menu"
		class="nc-language-switcher"
		:aria-label="t('nc_language_switcher', 'Language')"
		:title="currentName">
		<template #trigger>
			<span class="nc-language-switcher__trigger">{{ currentFlag }}</span>
		</template>
		<div class="nc-language-switcher__list">
			<input v-model="search"
				class="nc-language-switcher__search"
				type="text"
				:placeholder="t('nc_language_switcher', 'Search languages…')">
			<ul>
				<li v-for="lang in filteredLanguages"
					:key="lang.code"
					class="nc-language-switcher__item"
					:class="{ 'nc-language-switcher__item--active': lang.code === currentLanguage }"
					@click="switchLanguage(lang.code)">
					<span class="nc-language-switcher__name">{{ lang.name }}</span>
					<span class="nc-language-switcher__code">{{ lang.code }}</span>
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
			languages: loadState('nc_language_switcher', 'languages', []),
			currentLanguage: loadState('nc_language_switcher', 'currentLanguage', 'en'),
			isLoggedIn: loadState('nc_language_switcher', 'isLoggedIn', false),
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

		currentFlag() {
			// Show a globe icon as trigger
			return '🌐'
		},
	},

	methods: {
		t,

		async switchLanguage(lang) {
			if (lang === this.currentLanguage) {
				return
			}

			if (this.isLoggedIn) {
				try {
					await axios.post(generateUrl('/apps/nc_language_switcher/language'), { lang })
				} catch (e) {
					console.error('Failed to set language', e)
					return
				}
			} else {
				// Public/anonymous: set cookie for Accept-Language override
				document.cookie = `nc_language=${lang}; path=/; SameSite=Lax; max-age=31536000`
			}

			window.location.reload()
		},
	},
}
</script>

<style scoped>
.nc-language-switcher__trigger {
	font-size: 20px;
	line-height: 1;
}

.nc-language-switcher__list {
	padding: 8px;
	min-width: 200px;
	max-height: 400px;
	overflow-y: auto;
}

.nc-language-switcher__search {
	width: 100%;
	padding: 8px;
	margin-bottom: 4px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	box-sizing: border-box;
}

.nc-language-switcher__list ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.nc-language-switcher__item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 12px;
	cursor: pointer;
	border-radius: var(--border-radius);
}

.nc-language-switcher__item:hover {
	background-color: var(--color-background-hover);
}

.nc-language-switcher__item--active {
	background-color: var(--color-primary-element-light);
	font-weight: bold;
}

.nc-language-switcher__name {
	flex: 1;
}

.nc-language-switcher__code {
	color: var(--color-text-maxcontrast);
	font-size: 0.85em;
	margin-left: 8px;
}
</style>
