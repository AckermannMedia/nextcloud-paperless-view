import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
	plugins: [vue()],
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
