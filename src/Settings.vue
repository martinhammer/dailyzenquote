<script setup lang="ts">
import { ref } from 'vue'
import { t } from '@nextcloud/l10n'
import { loadState } from '@nextcloud/initial-state'
import { generateOcsUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const options = [
	{ value: 'quote', label: t('dailyzenquote', 'Daily Zen Quote') },
	{ value: 'events', label: t('dailyzenquote', 'On This Day - Events') },
	{ value: 'births_deaths', label: t('dailyzenquote', 'On This Day - Notable Births and Deaths') },
	{ value: 'all', label: t('dailyzenquote', 'On This Day - All') },
]

const mode = ref(loadState('dailyzenquote', 'mode', 'quote'))
const saving = ref(false)

async function save(value) {
	const previous = mode.value
	mode.value = value
	saving.value = true
	try {
		const url = generateOcsUrl('apps/dailyzenquote/settings/mode')
		await axios.put(url, { mode: value }, {
			params: { format: 'json' },
			headers: { 'OCS-APIRequest': 'true' },
		})
		showSuccess(t('dailyzenquote', 'Setting saved'))
	} catch {
		mode.value = previous
		showError(t('dailyzenquote', 'Could not save setting'))
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcSettingsSection
		:name="t('dailyzenquote', 'Daily Zen Quote')"
		:description="t('dailyzenquote', 'Choose the content to display on the the dashboard widget.')">
		<NcCheckboxRadioSwitch
			v-for="option in options"
			:key="option.value"
			:model-value="mode"
			:value="option.value"
			:disabled="saving"
			name="dailyzenquote-mode"
			type="radio"
			@update:model-value="save(option.value)">
			{{ option.label }}
		</NcCheckboxRadioSwitch>
		<NcNoteCard type="info">
			<p>
				{{ t('dailyzenquote', 'Inspirational quotes provided by') }}
				<a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer">{{ t('dailyzenquote', 'ZenQuotes API') }}</a>.
			</p>
			<p>
				{{ t('dailyzenquote', 'Historical event data provided by') }}
				<a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer">{{ t('dailyzenquote', 'ZenQuotes API') }}</a>.
			</p>
		</NcNoteCard>
	</NcSettingsSection>
</template>
