<template>
	<div class="document-card" @click="$emit('preview')">
		<div class="card-thumb">
			<img :src="thumbUrl"
				:alt="document.title"
				loading="lazy"
				@error="thumbError = true">
			<div v-if="thumbError" class="thumb-fallback">
				<span class="icon-file" />
			</div>
		</div>
		<div class="card-body">
			<h4 class="card-title" :title="document.title">
				{{ document.title }}
			</h4>
			<div v-if="correspondentName" class="card-correspondent">
				{{ correspondentName }}
			</div>
			<div class="card-date">
				{{ formatDate(document.created) }}
			</div>
			<div v-if="docTags.length > 0" class="card-tags">
				<TagChip v-for="tag in docTags" :key="tag.id" :tag="tag" :small="true" />
			</div>
		</div>
		<div class="card-actions">
			<NcButton type="tertiary"
				:aria-label="'Download'"
				@click.stop="$emit('download')">
				<template #icon>
					<span class="icon-download" />
				</template>
			</NcButton>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'
import TagChip from './TagChip.vue'
import { getThumbnailUrl } from '../services/api.js'

export default {
	name: 'DocumentCard',
	components: { NcButton, TagChip },
	props: {
		document: { type: Object, required: true },
		tagsMap: { type: Object, default: () => ({}) },
		correspondentsMap: { type: Object, default: () => ({}) },
		docTypesMap: { type: Object, default: () => ({}) },
	},
	emits: ['preview', 'download'],
	data() {
		return { thumbError: false }
	},
	computed: {
		thumbUrl() {
			return getThumbnailUrl(this.document.id)
		},
		correspondentName() {
			if (!this.document.correspondent) return null
			const c = this.correspondentsMap[this.document.correspondent]
			return c ? c.name : null
		},
		docTags() {
			if (!this.document.tags) return []
			return this.document.tags
				.map(id => this.tagsMap[id])
				.filter(Boolean)
		},
	},
	methods: {
		formatDate(dateStr) {
			if (!dateStr) return ''
			const d = new Date(dateStr)
			return d.toLocaleDateString('de-DE', {
				year: 'numeric',
				month: '2-digit',
				day: '2-digit',
			})
		},
	},
}
</script>

<style scoped>
.document-card {
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	overflow: hidden;
	cursor: pointer;
	transition: box-shadow 0.2s, border-color 0.2s;
	display: flex;
	flex-direction: column;
}

.document-card:hover {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-thumb {
	width: 100%;
	height: 180px;
	overflow: hidden;
	background: var(--color-background-dark);
	position: relative;
}

.card-thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.thumb-fallback {
	position: absolute;
	inset: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 48px;
	color: var(--color-text-maxcontrast);
}

.card-body {
	padding: 10px 12px;
	flex: 1;
}

.card-title {
	font-size: 14px;
	font-weight: 600;
	margin: 0 0 4px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.card-correspondent {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 2px;
}

.card-date {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 6px;
}

.card-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 4px;
}

.card-actions {
	display: flex;
	justify-content: flex-end;
	padding: 4px 8px 8px;
}
</style>
