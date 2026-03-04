// SVG icons as Vue 2 functional components (Lucide-style: 24x24, stroke-based)

function icon(name, paths) {
	return {
		name,
		functional: true,
		props: {
			size: { type: [Number, String], default: 24 },
			strokeWidth: { type: [Number, String], default: 2 },
		},
		render(h, { props, data }) {
			return h('svg', {
				attrs: {
					xmlns: 'http://www.w3.org/2000/svg',
					width: props.size,
					height: props.size,
					viewBox: '0 0 24 24',
					fill: 'none',
					stroke: 'currentColor',
					'stroke-width': String(props.strokeWidth),
					'stroke-linecap': 'round',
					'stroke-linejoin': 'round',
				},
				style: data.style || {},
				class: data.class,
			}, paths.map(p => {
				if (typeof p === 'string') {
					return h('path', { attrs: { d: p } })
				}
				return h(p.tag, { attrs: p.attrs })
			}))
		},
	}
}

export const Globe = icon('Globe', [
	'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z',
	{ tag: 'line', attrs: { x1: '2', y1: '12', x2: '22', y2: '12' } },
	'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z',
])

export const Languages = icon('Languages', [
	'M5 8l6 6',
	'M4 14l6-6 2-3',
	{ tag: 'line', attrs: { x1: '2', y1: '5', x2: '14', y2: '5' } },
	{ tag: 'line', attrs: { x1: '7', y1: '2', x2: '8', y2: '2' } },
	'M22 22l-5-10-5 10',
	{ tag: 'line', attrs: { x1: '14', y1: '18', x2: '20', y2: '18' } },
])

export const Earth = icon('Earth', [
	{ tag: 'circle', attrs: { cx: '12', cy: '12', r: '10' } },
	'M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20',
	{ tag: 'line', attrs: { x1: '2', y1: '12', x2: '22', y2: '12' } },
])

export const MessageCircle = icon('MessageCircle', [
	'M7.9 20A9 9 0 1 0 4 16.1L2 22z',
])

export const LetterType = icon('LetterType', [
	{ tag: 'line', attrs: { x1: '4', y1: '7', x2: '4', y2: '4' } },
	{ tag: 'line', attrs: { x1: '4', y1: '4', x2: '20', y2: '4' } },
	{ tag: 'line', attrs: { x1: '20', y1: '4', x2: '20', y2: '7' } },
	{ tag: 'line', attrs: { x1: '9', y1: '20', x2: '15', y2: '20' } },
	{ tag: 'line', attrs: { x1: '12', y1: '4', x2: '12', y2: '20' } },
])

export const BookOpen = icon('BookOpen', [
	'M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z',
	'M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z',
])

export const ICON_MAP = { Globe, Languages, Earth, MessageCircle, LetterType, BookOpen }
export const ICON_OPTIONS = Object.keys(ICON_MAP)
