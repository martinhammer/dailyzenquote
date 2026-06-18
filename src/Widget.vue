<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import { useWidgetContent } from './composables/useWidgetContent.ts'

const { mode, currentItem, hasMultiple, index, items, next, prev, loading, error } = useWidgetContent()

const labels = {
	event: t('dailyzenquote', 'Event'),
	birth: t('dailyzenquote', 'Born'),
	death: t('dailyzenquote', 'Died'),
}

const label = computed(() => {
	// In events-only mode every entry is an event, so the label is redundant;
	// only the mixed modes (births_deaths, all) need it to distinguish entries.
	if (mode === 'events') {
		return null
	}
	const kind = currentItem.value?.kind
	return kind && kind !== 'quote' ? labels[kind] : null
})
</script>

<template>
	<div :class="$style.widget">
		<div v-if="loading" :class="$style.loading">
			<NcLoadingIcon :size="32" />
		</div>
		<NcEmptyContent
			v-else-if="error || !currentItem"
			:name="t('dailyzenquote', 'Could not load content')"
			:description="t('dailyzenquote', 'Please try again later.')">
			<template #icon>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
					<path d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" />
				</svg>
			</template>
		</NcEmptyContent>

		<figure v-else-if="currentItem.kind === 'quote'" :class="$style.figure">
			<blockquote :class="$style.quote">{{ currentItem.text }}</blockquote>
			<figcaption :class="$style.author">— {{ currentItem.author }}</figcaption>
		</figure>

		<div v-else :class="[$style.carousel, 'dzq-carousel']">
			<NcButton
				v-if="hasMultiple"
				:aria-label="t('dailyzenquote', 'Previous')"
				variant="tertiary-no-background"
				@click="prev">
				<template #icon>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="currentColor">
						<path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
					</svg>
				</template>
			</NcButton>

			<div :class="$style.card">
				<span v-if="label" :class="$style.label">{{ label }}</span>
				<p :class="$style.text">{{ currentItem.text }}</p>
				<span v-if="hasMultiple" :class="$style.counter">{{ index + 1 }} / {{ items.length }}</span>
			</div>

			<NcButton
				v-if="hasMultiple"
				:aria-label="t('dailyzenquote', 'Next')"
				variant="tertiary-no-background"
				@click="next">
				<template #icon>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="currentColor">
						<path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
					</svg>
				</template>
			</NcButton>
		</div>
	</div>
</template>

<style>
.icon-dailyzenquote {
	background-image: url('../img/app-dark.svg');
	filter: var(--background-invert-if-dark);
}

/*
 * Trim the carousel arrow buttons down to (roughly) their icon so the chevrons
 * sit near the widget edges, freeing horizontal space for the entry text.
 * The chevron glyph itself is unchanged.
 */
.dzq-carousel .button-vue {
	min-width: 28px !important;
	width: 28px !important;
}
</style>

<style module>
.widget {
	padding: 12px 16px;
	/*
	 * Full-bleed: widen the widget beyond the dashboard panel's content box and
	 * offset it symmetrically, so the carousel arrows reach into the panel's own
	 * padding toward the box edge. The 28px is the tuning knob (keep width's half
	 * in sync: calc(100% + 2 * knob)).
	 */
	width: calc(100% + 56px);
	margin-inline: -28px;
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

.carousel {
	display: flex;
	align-items: center;
	gap: 0;
	width: 100%;
	/* Pull the arrows outward into the widget padding to widen the text column. */
	margin-inline: -16px;
}

.card {
	flex: 1 1 auto;
	min-width: 0;
	text-align: center;
	/* Keep the entry text a few px clear of the arrows. */
	padding-inline: 6px;
}

.label {
	display: inline-block;
	margin-bottom: 6px;
	padding: 2px 10px;
	border-radius: var(--border-radius-pill, 100px);
	background-color: var(--color-primary-element-light);
	color: var(--color-primary-element-light-text);
	font-size: 0.8rem;
	font-weight: bold;
	text-transform: uppercase;
	letter-spacing: 0.03em;
}

.text {
	line-height: 1.5;
	margin: 0 0 8px;
	font-size: 1.4rem;
	color: var(--color-main-text);
}

.counter {
	font-size: 0.85rem;
	color: var(--color-text-maxcontrast);
}
</style>
