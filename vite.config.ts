import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'path'

export default createAppConfig(
	{
		widget: resolve(join('src', 'widget.ts')),
		settings: resolve(join('src', 'settings.ts')),
	},
	{
		createEmptyCSSEntryPoints: true,
		// `js/` is wiped on every build by default; opt `css/` in too, otherwise
		// hashed chunks from earlier builds pile up there forever.
		emptyOutputDirectory: { additionalDirectories: ['css'] },
		extractLicenseInformation: true,
		thirdPartyLicense: false,
	},
)
