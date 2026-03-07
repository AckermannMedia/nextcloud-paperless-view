<template>
	<NcModal :show="true"
		size="large"
		:title="document.title"
		@close="onClose">
		<div class="preview-container">
			<div class="preview-header">
				<div class="preview-info">
					<h2>{{ document.title }}</h2>
					<div class="preview-meta">
						<span v-if="correspondentName">{{ correspondentName }}</span>
						<span v-if="docTypeName" class="meta-sep">{{ docTypeName }}</span>
						<span class="meta-sep">{{ formatDate(document.created) }}</span>
					</div>
					<div v-if="docTags.length > 0" class="preview-tags">
						<TagChip v-for="tag in docTags" :key="tag.id" :tag="tag" />
					</div>
				</div>
				<div class="preview-actions">
					<NcButton type="secondary" @click="$emit('download', document)">
						<template #icon><span class="icon-download" /></template>
						Download
					</NcButton>
					<NcButton v-if="paperlessUrl" type="tertiary" :href="paperlessDocUrl" target="_blank">
						In Paperless oeffnen
					</NcButton>
				</div>
			</div>
			<div class="preview-pdf">
				<div v-if="loading" class="preview-loading">
					<span class="icon-loading" />
					<p>PDF wird geladen...</p>
				</div>
				<div v-else-if="error" class="preview-error">
					<p>{{ error }}</p>
					<NcButton type="secondary" @click="loadPdf">Erneut versuchen</NcButton>
				</div>
				<iframe v-else-if="blobUrl" :src="blobUrl" :title="document.title" frameborder="0" />
			</div>
		</div>
	</NcModal>
</template>

<script>
import NcModal from '@nextcloud/vue/components/NcModal'
import NcButton from '@nextcloud/vue/components/NcButton'
import TagChip from './TagChip.vue'
import axios from '@nextcloud/axios'
import { getPreviewUrl } from '../services/api.js'

export default {
	name: 'DocumentPreview',
	components: { NcModal, NcButton, TagChip },
	props: {
		document: { type: Object, required: true },
		tagsMap: { type: Object, default: () => ({}) },
		correspondentsMap: { type: Object, default: () => ({}) },
		docTypesMap: { type: Object, default: () => ({}) },
	},
	emits: ['close', 'download'],
	data() {
		return {
			blobUrl: null,
			loading: true,
			error: null,
		}
	},
	computed: {
		correspondentName() {
			if (!this.document.correspondent) return null
			const c = this.correspondentsMap[this.document.correspondent]
			return c ? c.name : null
		},
		docTypeName() {
			if (!this.document.document_type) return null
			const d = this.docTypesMap[this.document.document_type]
			return d ? d.name : null
		},
		docTags() {
			if (!this.document.tags) return []
			return this.document.tags
				.map(id => this.tagsMap[id])
				.filter(Boolean)
		},
		paperlessUrl() {
			try {
				const el = document.getElementById('paperless-view-app')
				return el?.dataset?.paperlessUrl || null
			} catch {
				return null
			}
		},
		paperlessDocUrl() {
			if (!this.paperlessUrl) return '#'
			return `${this.paperlessUrl}/documents/${this.document.id}/details`
		},
	},
	mounted() {
		this.loadPdf()
	},
	beforeUnmount() {
		if (this.blobUrl) {
			URL.revokeObjectURL(this.blobUrl)
		}
	},
	methods: {
		async loadPdf() {
			this.loading = true
			this.error = null
			try {
				const response = await axios.get(getPreviewUrl(this.document.id), {
					responseType: 'arraybuffer',
				})
				const blob = new Blob([response.data], { type: 'application/pdf' })
				if (this.blobUrl) {
					URL.revokeObjectURL(this.blobUrl)
				}
				this.blobUrl = URL.createObjectURL(blob)
			} catch (e) {
				this.error = 'PDF konnte nicht geladen werden: ' + (e.message || 'Unbekannter Fehler')
			} finally {
				this.loading = false
			}
		},
		onClose() {
			if (this.blobUrl) {
				URL.revokeObjectURL(this.blobUrl)
				this.blobUrl = null
			}
			this.$emit('close')
		},
		formatDate(dateStr) {
			if (!dateStr) return ''
			const d = new Date(dateStr)
			return d.toLocaleDateString('de-DE', {
				year: 'numeric',
				month: 'long',
				day: 'numeric',
			})
		},
	},
}
</script>

<style scoped>
.preview-container {
	display: flex;
	flex-direction: column;
	height: 80vh;
	padding: 16px;
}

.preview-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 16px;
	gap: 16px;
}

.preview-info h2 {
	margin: 0 0 4px;
	font-size: 18px;
}

.preview-meta {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.meta-sep::before {
	content: ' \00b7 ';
}

.preview-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 4px;
	margin-top: 8px;
}

.preview-actions {
	display: flex;
	gap: 8px;
	flex-shrink: 0;
}

.preview-pdf {
	flex: 1;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	overflow: hidden;
	position: relative;
}

.preview-pdf iframe {
	width: 100%;
	height: 100%;
	border: none;
}

.preview-loading,
.preview-error {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	gap: 12px;
	color: var(--color-text-maxcontrast);
}
</style>
