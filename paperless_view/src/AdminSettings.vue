<template>
	<div class="paperless-admin-settings">
		<h2>Paperless View Einstellungen</h2>
		<p class="settings-hint">
			Verbinden Sie Ihre Paperless-ngx Instanz mit Nextcloud.
		</p>

		<div class="field">
			<label for="paperless-url">Paperless URL</label>
			<NcTextField id="paperless-url"
				v-model="url"
				:label="'https://paperless.example.com'"
				:placeholder="'https://paperless.example.com'" />
			<p class="field-hint">Die interne URL Ihrer Paperless-ngx Instanz (ohne /api/).</p>
		</div>

		<div class="field">
			<label for="paperless-token">API Token</label>
			<NcPasswordField id="paperless-token"
				v-model="token"
				:label="'API Token'"
				:placeholder="'API Token'" />
			<p class="field-hint">
				Token aus Paperless unter Einstellungen &gt; Django Admin &gt; Auth Tokens.
			</p>
		</div>

		<div class="actions">
			<NcButton type="primary"
				:disabled="saving"
				@click="save">
				{{ saving ? 'Speichern...' : 'Speichern' }}
			</NcButton>
			<NcButton type="secondary"
				:disabled="testing"
				@click="testConnection">
				{{ testing ? 'Teste...' : 'Verbindung testen' }}
			</NcButton>
		</div>

		<NcNoteCard v-if="message" :type="messageType">
			{{ message }}
		</NcNoteCard>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcPasswordField from '@nextcloud/vue/components/NcPasswordField'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'

export default {
	name: 'AdminSettings',
	components: { NcButton, NcTextField, NcPasswordField, NcNoteCard },
	data() {
		return {
			url: '',
			token: '',
			saving: false,
			testing: false,
			message: '',
			messageType: 'success',
		}
	},
	mounted() {
		this.loadSettings()
	},
	methods: {
		async loadSettings() {
			try {
				const response = await axios.get(
					generateUrl('/apps/paperless_view/api/settings'),
				)
				this.url = response.data.paperless_url || ''
				this.token = response.data.paperless_token || ''
			} catch (e) {
				console.error('Failed to load settings', e)
			}
		},
		async save() {
			this.saving = true
			this.message = ''
			try {
				await axios.post(
					generateUrl('/apps/paperless_view/api/settings'),
					{
						paperless_url: this.url,
						paperless_token: this.token,
					},
				)
				this.message = 'Einstellungen gespeichert.'
				this.messageType = 'success'
			} catch (e) {
				this.message = 'Fehler beim Speichern: ' + (e.response?.data?.error || e.message)
				this.messageType = 'error'
			} finally {
				this.saving = false
			}
		},
		async testConnection() {
			this.testing = true
			this.message = ''
			try {
				// Save first, then test
				await this.save()
				const response = await axios.get(
					generateUrl('/apps/paperless_view/api/tags'),
				)
				if (response.data.results) {
					this.message = `Verbindung erfolgreich! ${response.data.results.length} Tags gefunden.`
					this.messageType = 'success'
				} else if (response.data.error) {
					this.message = 'Fehler: ' + response.data.error
					this.messageType = 'error'
				}
			} catch (e) {
				this.message = 'Verbindung fehlgeschlagen: ' + (e.response?.data?.error || e.message)
				this.messageType = 'error'
			} finally {
				this.testing = false
			}
		},
	},
}
</script>

<style scoped>
.paperless-admin-settings {
	max-width: 600px;
	padding: 20px;
}

.settings-hint {
	color: var(--color-text-maxcontrast);
	margin-bottom: 20px;
}

.field {
	margin-bottom: 16px;
}

.field label {
	display: block;
	font-weight: 600;
	margin-bottom: 4px;
}

.field-hint {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
}

.actions {
	display: flex;
	gap: 8px;
	margin: 20px 0;
}
</style>
