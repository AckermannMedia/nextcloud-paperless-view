import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const baseUrl = generateUrl('/apps/paperless_view')

export async function fetchDocuments(params = {}) {
	const response = await axios.get(`${baseUrl}/api/documents`, { params })
	return response.data
}

export async function searchDocuments(query, page = 1) {
	const response = await axios.get(`${baseUrl}/api/search`, {
		params: { query, page },
	})
	return response.data
}

export async function fetchTags() {
	const response = await axios.get(`${baseUrl}/api/tags`)
	return response.data.results || []
}

export async function fetchCorrespondents() {
	const response = await axios.get(`${baseUrl}/api/correspondents`)
	return response.data.results || []
}

export async function fetchDocumentTypes() {
	const response = await axios.get(`${baseUrl}/api/document_types`)
	return response.data.results || []
}

export function getThumbnailUrl(id) {
	return `${baseUrl}/api/documents/${id}/thumb`
}

export function getPreviewUrl(id) {
	return `${baseUrl}/api/documents/${id}/preview`
}

export function getDownloadUrl(id) {
	return `${baseUrl}/api/documents/${id}/download`
}
