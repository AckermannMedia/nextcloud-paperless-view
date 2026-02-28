<template>
	<div class="search-bar">
		<NcTextField :value="modelValue"
			:label="'Dokumente durchsuchen...'"
			:show-trailing-button="modelValue !== ''"
			trailing-button-icon="close"
			@update:value="$emit('update:modelValue', $event)"
			@trailing-button-click="$emit('update:modelValue', '')">
			<template #icon>
				<span class="icon-search" />
			</template>
		</NcTextField>
	</div>
</template>

<script>
import NcTextField from '@nextcloud/vue/components/NcTextField'

export default {
	name: 'SearchBar',
	components: { NcTextField },
	props: {
		modelValue: { type: String, default: '' },
	},
	emits: ['update:modelValue'],
}
</script>

<style scoped>
.search-bar {
	margin-bottom: 8px;
}
</style>
