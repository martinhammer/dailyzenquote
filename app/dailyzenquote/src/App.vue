<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcContent from '@nextcloud/vue/components/NcContent'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import { useQuote } from './composables/useQuote.ts'

const { quote, author, loading, error } = useQuote()
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
