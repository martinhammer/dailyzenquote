<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import { useQuote } from './composables/useQuote.ts'

const { quote, author, loading, error } = useQuote()
</script>

<template>
	<div :class="$style.widget">
		<div v-if="loading" :class="$style.loading">
			<NcLoadingIcon :size="32" />
		</div>
		<NcEmptyContent
			v-else-if="error"
			:name="t('dailyzenquote', 'Could not load quote')"
			:description="t('dailyzenquote', 'Please try again later.')">
			<template #icon>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
					<path d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" />
				</svg>
			</template>
		</NcEmptyContent>
		<figure v-else :class="$style.figure">
			<blockquote :class="$style.quote">{{ quote }}</blockquote>
			<figcaption :class="$style.author">— {{ author }}</figcaption>
		</figure>
	</div>
</template>

<style>
.icon-dailyzenquote {
	background-image: url('../img/app-dark.svg');
	filter: var(--background-invert-if-dark);
}
</style>

<style module>
.widget {
	padding: 12px 16px;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	box-sizing: border-box;
}

.loading {
	display: flex;
	justify-content: center;
	align-items: center;
}

.figure {
	text-align: center;
	margin: 0;
}

.quote {
	line-height: 1.5;
	margin: 0 0 8px;
	font-size: 1.5rem;
	color: var(--color-main-text);
}

.author {
	font-size: 0.95rem;
	color: var(--color-text-maxcontrast);
}
</style>
