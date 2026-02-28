<template>
	<NcContent app-name="paperless_view">
		<NcAppNavigation>
			<template #list>
				<div class="paperless-sidebar">
					<SearchBar :model-value="searchQuery"
						@update:model-value="onSearch" />

					<div class="filter-section">
						<h3>Tags</h3>
						<div class="filter-list">
							<label v-for="tag in tags"
								:key="tag.id"
								class="filter-item">
								<input v-model="selectedTags"
									type="checkbox"
									:value="tag.id">
								<TagChip :tag="tag" />
							</label>
						</div>
					</div>

					<div class="filter-section">
						<h3>Korrespondenten</h3>
						<NcSelect v-model="selectedCorrespondent"
							:options="correspondentOptions"
							:placeholder="'Alle'"
							:clearable="true"
							label="label" />
					</div>

					<div class="filter-section">
						<h3>Dokumenttyp</h3>
						<NcSelect v-model="selectedDocType"
							:options="docTypeOptions"
							:placeholder="'Alle'"
							:clearable="true"
							label="label" />
					</div>
				</div>
			</template>
		</NcAppNavigation>

		<NcAppContent>
			<div class="paperless-content">
				<div v-if="!configured" class="empty-state">
					<NcEmptyContent name="Nicht konfiguriert"
						description="Bitte konfigurieren Sie die Paperless-ngx Verbindung in den Admin-Einstellungen.">
						<template #icon>
							<span class="icon-settings" />
						</template>
					</NcEmptyContent>
				</div>

				<div v-else-if="loading && documents.length === 0" class="loading-state">
					<NcLoadingIcon :size="64" />
					<p>Dokumente werden geladen...</p>
				</div>

				<div v-else-if="documents.length === 0" class="empty-state">
					<NcEmptyContent name="Keine Dokumente"
						description="Keine Dokumente gefunden.">
						<template #icon>
							<span class="icon-file" />
						</template>
					</NcEmptyContent>
				</div>

				<DocumentList v-else
					:documents="documents"
					:tags-map="tagsMap"
					:correspondents-map="correspondentsMap"
					:doc-types-map="docTypesMap"
					@preview="openPreview"
					@download="downloadDoc" />

				<div v-if="hasMore" class="load-more">
					<NcButton :disabled="loading"
						type="secondary"
						@click="loadMore">
						{{ loading ? 'Laden...' : 'Mehr laden' }}
					</NcButton>
				</div>
			</div>

			<DocumentPreview v-if="previewDoc"
				:document="previewDoc"
				:tags-map="tagsMap"
				:correspondents-map="correspondentsMap"
				:doc-types-map="docTypesMap"
				@close="previewDoc = null"
				@download="downloadDoc" />
		</NcAppContent>
	</NcContent>
</template>

<script>
import NcContent from '@nextcloud/vue/components/NcContent'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcSelect from '@nextcloud/vue/components/NcSelect'

import DocumentList from './components/DocumentList.vue'
import DocumentPreview from './components/DocumentPreview.vue'
import SearchBar from './components/SearchBar.vue'
import TagChip from './components/TagChip.vue'

import {
	fetchDocuments,
	searchDocuments,
	fetchTags,
	fetchCorrespondents,
	fetchDocumentTypes,
	getDownloadUrl,
} from './services/api.js'

export default {
	name: 'App',
	components: {
		NcContent,
		NcAppNavigation,
		NcAppContent,
		NcButton,
		NcEmptyContent,
		NcLoadingIcon,
		NcSelect,
		DocumentList,
		DocumentPreview,
		SearchBar,
		TagChip,
	},
	data() {
		return {
			configured: true,
			loading: false,
			documents: [],
			tags: [],
			correspondents: [],
			docTypes: [],
			searchQuery: '',
			selectedTags: [],
			selectedCorrespondent: null,
			selectedDocType: null,
			currentPage: 1,
			totalCount: 0,
			pageSize: 24,
			previewDoc: null,
			searchTimeout: null,
		}
	},
	computed: {
		tagsMap() {
			const map = {}
			this.tags.forEach(t => { map[t.id] = t })
			return map
		},
		correspondentsMap() {
			const map = {}
			this.correspondents.forEach(c => { map[c.id] = c })
			return map
		},
		docTypesMap() {
			const map = {}
			this.docTypes.forEach(d => { map[d.id] = d })
			return map
		},
		correspondentOptions() {
			return this.correspondents.map(c => ({ value: c.id, label: c.name }))
		},
		docTypeOptions() {
			return this.docTypes.map(d => ({ value: d.id, label: d.name }))
		},
		hasMore() {
			return this.documents.length < this.totalCount
		},
	},
	watch: {
		selectedTags() { this.resetAndLoad() },
		selectedCorrespondent() { this.resetAndLoad() },
		selectedDocType() { this.resetAndLoad() },
	},
	mounted() {
		this.loadMetadata()
		this.loadDocuments()
	},
	methods: {
		async loadMetadata() {
			try {
				const [tags, correspondents, docTypes] = await Promise.all([
					fetchTags(),
					fetchCorrespondents(),
					fetchDocumentTypes(),
				])
				this.tags = tags
				this.correspondents = correspondents
				this.docTypes = docTypes
			} catch (e) {
				console.error('Failed to load metadata', e)
			}
		},
		async loadDocuments() {
			this.loading = true
			try {
				const params = {
					page: this.currentPage,
					page_size: this.pageSize,
					ordering: '-created',
				}
				if (this.selectedTags.length > 0) {
					params.tags__id__in = this.selectedTags.join(',')
				}
				if (this.selectedCorrespondent) {
					params.correspondent__id = this.selectedCorrespondent.value
				}
				if (this.selectedDocType) {
					params.document_type__id = this.selectedDocType.value
				}

				let data
				if (this.searchQuery) {
					data = await searchDocuments(this.searchQuery, this.currentPage)
				} else {
					data = await fetchDocuments(params)
				}

				if (this.currentPage === 1) {
					this.documents = data.results || []
				} else {
					this.documents = [...this.documents, ...(data.results || [])]
				}
				this.totalCount = data.count || 0
			} catch (e) {
				if (e.response && e.response.status === 503) {
					this.configured = false
				}
				console.error('Failed to load documents', e)
			} finally {
				this.loading = false
			}
		},
		resetAndLoad() {
			this.currentPage = 1
			this.documents = []
			this.loadDocuments()
		},
		loadMore() {
			this.currentPage++
			this.loadDocuments()
		},
		onSearch(query) {
			this.searchQuery = query
			clearTimeout(this.searchTimeout)
			this.searchTimeout = setTimeout(() => {
				this.resetAndLoad()
			}, 400)
		},
		openPreview(doc) {
			this.previewDoc = doc
		},
		downloadDoc(doc) {
			window.open(getDownloadUrl(doc.id), '_blank')
		},
	},
}
</script>

<style scoped>
.paperless-sidebar {
	padding: 12px;
}

.filter-section {
	margin-top: 16px;
}

.filter-section h3 {
	font-size: 13px;
	font-weight: 600;
	margin-bottom: 6px;
	color: var(--color-text-maxcontrast);
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.filter-list {
	max-height: 200px;
	overflow-y: auto;
}

.filter-item {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 2px 0;
	cursor: pointer;
}

.filter-item input[type="checkbox"] {
	margin: 0;
}

.paperless-content {
	padding: 20px;
}

.loading-state,
.empty-state {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	min-height: 400px;
	gap: 16px;
}

.load-more {
	display: flex;
	justify-content: center;
	padding: 20px;
}
</style>
