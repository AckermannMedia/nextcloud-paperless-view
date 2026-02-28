<template>
	<div class="document-grid">
		<DocumentCard v-for="doc in documents"
			:key="doc.id"
			:document="doc"
			:tags-map="tagsMap"
			:correspondents-map="correspondentsMap"
			:doc-types-map="docTypesMap"
			@preview="$emit('preview', doc)"
			@download="$emit('download', doc)" />
	</div>
</template>

<script>
import DocumentCard from './DocumentCard.vue'

export default {
	name: 'DocumentList',
	components: { DocumentCard },
	props: {
		documents: { type: Array, required: true },
		tagsMap: { type: Object, default: () => ({}) },
		correspondentsMap: { type: Object, default: () => ({}) },
		docTypesMap: { type: Object, default: () => ({}) },
	},
	emits: ['preview', 'download'],
}
</script>

<style scoped>
.document-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
	gap: 16px;
}
</style>
