import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import cssInjectedByJsPlugin from 'vite-plugin-css-injected-by-js'

const entryName = process.env.ENTRY || 'main'

const entries = {
	main: resolve(__dirname, 'src/main.js'),
	admin: resolve(__dirname, 'src/admin.js'),
}

export default defineConfig({
	plugins: [
		vue(),
		cssInjectedByJsPlugin(),
	],
	define: {
		'process.env.NODE_ENV': JSON.stringify('production'),
		'process.env': '{}',
		appName: JSON.stringify('paperless_view'),
		appVersion: JSON.stringify('1.0.0'),
	},
	build: {
		outDir: 'js',
		emptyOutDir: false,
		minify: true,
		lib: {
			entry: entries[entryName],
			name: `PaperlessView_${entryName}`,
			formats: ['iife'],
			fileName: () => `paperless_view-${entryName}.js`,
		},
		rollupOptions: {
			output: {
				// Ensure everything is inlined into a single file
				inlineDynamicImports: true,
			},
		},
	},
})
