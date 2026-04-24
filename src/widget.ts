import { createApp } from 'vue'
import Widget from './Widget.vue'

declare global {
	interface Window {
		OCA: {
			Dashboard: {
				register: (id: string, callback: (el: HTMLElement) => void) => void
			}
		}
	}
}

document.addEventListener('DOMContentLoaded', () => {
	window.OCA.Dashboard.register('dailyzenquote', (el: HTMLElement) => {
		const app = createApp(Widget)
		app.mount(el)
	})
})
