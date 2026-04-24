import { onMounted, ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

export function useQuote() {
	const quote = ref<string | null>(null)
	const author = ref<string | null>(null)
	const loading = ref(true)
	const error = ref(false)

	onMounted(async () => {
		try {
			const url = generateOcsUrl('apps/dailyzenquote/quote')
			const response = await axios.get(url, {
				params: { format: 'json' },
				headers: { 'OCS-APIRequest': 'true' },
			})
			quote.value = response.data.ocs.data.quote
			author.value = response.data.ocs.data.author
		} catch {
			error.value = true
		} finally {
			loading.value = false
		}
	})

	return { quote, author, loading, error }
}
