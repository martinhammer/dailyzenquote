import { computed, onMounted, ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'

export type WidgetMode = 'quote' | 'events' | 'births_deaths' | 'all'

export type ItemKind = 'quote' | 'event' | 'birth' | 'death'

export interface ContentItem {
	kind: ItemKind
	text: string
	author?: string | null
}

interface OnThisDayEntry {
	text: string
}

interface OnThisDayResponse {
	events: OnThisDayEntry[]
	births: OnThisDayEntry[]
	deaths: OnThisDayEntry[]
}

// Fisher–Yates shuffle, returning a new array so the source order is untouched.
function shuffle<T>(input: T[]): T[] {
	const result = [...input]
	for (let i = result.length - 1; i > 0; i--) {
		const j = Math.floor(Math.random() * (i + 1))
		;[result[i], result[j]] = [result[j], result[i]]
	}
	return result
}

export function useWidgetContent() {
	const mode = loadState<WidgetMode>('dailyzenquote', 'mode', 'quote')
	const items = ref<ContentItem[]>([])
	const index = ref(0)
	const loading = ref(true)
	const error = ref(false)

	const currentItem = computed<ContentItem | null>(() => items.value[index.value] ?? null)
	const hasMultiple = computed(() => items.value.length > 1)

	function next() {
		if (items.value.length === 0) {
			return
		}
		index.value = (index.value + 1) % items.value.length
	}

	function prev() {
		if (items.value.length === 0) {
			return
		}
		index.value = (index.value - 1 + items.value.length) % items.value.length
	}

	async function fetchQuote(): Promise<ContentItem[]> {
		const url = generateOcsUrl('apps/dailyzenquote/quote')
		const response = await axios.get(url, {
			params: { format: 'json' },
			headers: { 'OCS-APIRequest': 'true' },
		})
		const data = response.data.ocs.data
		return [{ kind: 'quote', text: data.quote, author: data.author }]
	}

	async function fetchOnThisDay(): Promise<ContentItem[]> {
		const url = generateOcsUrl('apps/dailyzenquote/onthisday')
		const response = await axios.get(url, {
			params: { format: 'json' },
			headers: { 'OCS-APIRequest': 'true' },
		})
		const data: OnThisDayResponse = response.data.ocs.data

		const collected: ContentItem[] = []
		if (mode === 'events' || mode === 'all') {
			collected.push(...data.events.map((e): ContentItem => ({ kind: 'event', text: e.text })))
		}
		if (mode === 'births_deaths' || mode === 'all') {
			collected.push(...data.births.map((e): ContentItem => ({ kind: 'birth', text: e.text })))
			collected.push(...data.deaths.map((e): ContentItem => ({ kind: 'death', text: e.text })))
		}
		return shuffle(collected)
	}

	onMounted(async () => {
		try {
			items.value = mode === 'quote' ? await fetchQuote() : await fetchOnThisDay()
			error.value = items.value.length === 0
		} catch {
			error.value = true
		} finally {
			loading.value = false
		}
	})

	return { mode, items, index, currentItem, hasMultiple, next, prev, loading, error }
}
