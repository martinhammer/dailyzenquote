<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import { useQuote } from './composables/useQuote.ts'

const { quote, author, loading, error } = useQuote()
</script>

<template>
	<div :class="$style.widget">
		<div v-if="loading" :class="$style.loading">
			<NcLoadingIcon :size="32" />
		</div>
		<p v-else-if="error" :class="$style.error">
			{{ t('dailyzenquote', 'Could not load quote') }}
		</p>
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

.error {
	color: var(--color-error);
	text-align: center;
	margin: 0;
}
</style>
