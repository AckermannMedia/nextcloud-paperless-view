import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Plugin to inject CSS into JS entry points at runtime
function injectCssPlugin() {
	return {
		name: 'inject-css',
		generateBundle(options, bundle) {
			// Find CSS assets and their corresponding JS entries
			const cssFiles = {}
			for (const [fileName, chunk] of Object.entries(bundle)) {
				if (fileName.endsWith('.css') && chunk.type === 'asset') {
					cssFiles[fileName] = chunk
				}
			}

			for (const [fileName, chunk] of Object.entries(bundle)) {
				if (chunk.type === 'chunk' && chunk.isEntry) {
					// Inject code to load associated CSS files at runtime
					const baseName = fileName.replace('.js', '')
					const cssToLoad = Object.keys(cssFiles).filter(
						css => css.startsWith(baseName) || css.includes(baseName.split('-').pop()),
					)

					if (cssToLoad.length > 0 || Object.keys(cssFiles).length > 0) {
						// Load ALL CSS files for the entry point
						const cssLoadCode = Object.keys(cssFiles)
							.map(css => {
								const appPath = `/custom_apps/paperless_view/js/${css}`
								return `(function(){var l=document.createElement('link');l.rel='stylesheet';l.href='${appPath}';document.head.appendChild(l)})();`
							})
							.join('\n')
						chunk.code = cssLoadCode + '\n' + chunk.code
					}
				}
			}
		},
	}
}

export default defineConfig({
	plugins: [vue(), injectCssPlugin()],
	build: {
		outDir: 'js',
		emptyOutDir: true,
		minify: true,
		rollupOptions: {
			input: {
				main: resolve(__dirname, 'src/main.js'),
				admin: resolve(__dirname, 'src/admin.js'),
			},
			output: {
				entryFileNames: 'paperless_view-[name].js',
				chunkFileNames: 'paperless_view-[name].js',
				assetFileNames: 'paperless_view-[name].[ext]',
			},
		},
	},
})
