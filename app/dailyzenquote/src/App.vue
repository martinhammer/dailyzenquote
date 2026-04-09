<script setup lang="ts">
import { onMounted, ref } from 'vue'
import axios from '@nextcloud/axios'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcContent from '@nextcloud/vue/components/NcContent'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'

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
</script>

<template>
	<NcContent app-name="dailyzenquote">
		<NcAppContent :class="$style.content">
			<div v-if="loading" :class="$style.loading">
				<NcLoadingIcon :size="64" />
			</div>
			<NcEmptyContent
				v-else-if="error"
				:name="t('dailyzenquote', 'Could not load quote')"
				:description="t('dailyzenquote', 'Please check your network connection and try again.')" />
			<figure v-else :class="$style.figure">
				<blockquote :class="$style.quote">
					<p>{{ quote }}</p>
				</blockquote>
				<figcaption :class="$style.author">
					— {{ author }}
				</figcaption>
			</figure>
		</NcAppContent>
	</NcContent>
</template>

<style module>
.content {
	display: flex;
	justify-content: center;
	align-items: center;
}

.loading {
	display: flex;
	justify-content: center;
	align-items: center;
	padding: 64px;
}

.figure {
	max-width: 600px;
	padding: 48px 32px;
	text-align: center;
	margin: auto;
}

.quote p {
	font-size: 1.5rem;
	font-style: italic;
	line-height: 1.6;
	margin: 0 0 24px;
	color: var(--color-main-text);
}

.author {
	font-size: 1rem;
	color: var(--color-text-maxcontrast);
	font-style: normal;
}
</style>
