import { createApp } from 'vue'
import Settings from './Settings.vue'

document.addEventListener('DOMContentLoaded', () => {
	const el = document.getElementById('dailyzenquote-settings')
	if (el === null) {
		return
	}
	const app = createApp(Settings)
	app.mount(el)
})
