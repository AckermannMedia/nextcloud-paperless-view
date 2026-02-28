<template>
	<span class="tag-chip" :style="chipStyle">
		{{ tag.name }}
	</span>
</template>

<script>
const TAG_COLORS = {
	1: '#a6cee3',
	2: '#1f78b4',
	3: '#b2df8a',
	4: '#33a02c',
	5: '#fb9a99',
	6: '#e31a1c',
	7: '#fdbf6f',
	8: '#ff7f00',
	9: '#cab2d6',
	10: '#6a3d9a',
	11: '#ffff99',
	12: '#b15928',
	13: '#8dd3c7',
}

export default {
	name: 'TagChip',
	props: {
		tag: { type: Object, required: true },
		small: { type: Boolean, default: false },
	},
	computed: {
		chipStyle() {
			const color = this.tag.color || TAG_COLORS[this.tag.id % 13 + 1] || '#ccc'
			return {
				backgroundColor: color + '33',
				color,
				borderColor: color + '66',
				fontSize: this.small ? '11px' : '12px',
				padding: this.small ? '1px 6px' : '2px 8px',
			}
		},
	},
}
</script>

<style scoped>
.tag-chip {
	display: inline-block;
	border-radius: 12px;
	border: 1px solid;
	font-weight: 500;
	white-space: nowrap;
}
</style>
