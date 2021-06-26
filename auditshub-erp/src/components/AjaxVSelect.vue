<!-- =========================================================================================
    File Name: VxTour.vue
    Description: Tour Component
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <v-select
    @search="fetchOptions"
    :placeholder="placeholder"
    :options="optionPull"
    :dir="dir"
    :clearable="clearable"
    v-bind:value="selected"
    @input="handleChange"
    :class="cClass"
	:disabled="disabled"
    :filterable="filterable"
    :multiple="multiple"
  />
</template>

<script>
export default {
	name: 'ajax-select',
	props: {
		selected: {
			default: '',
			required: true
		},
		url: {
			default: null,
			required: false,
			type: String
		},
		placeholder: {
			default: 'Select option',
			type: String
		},
		dir: {
			default: 'ltr',
			type: String
		},
		cClass: {
			default: 'w-full',
			type: String
		},
		filterable: {
			type: Boolean,
			default: true
		},
		options: {
			default: () => [],
			type: Object / Array
		},
		include: {
			default: () => [],
			type: Object / Array
		},
		clearable: {
			default: true,
			type: Boolean
		},
		multiple: {
			default: false,
			type: Boolean
		},
		disabled: {
			default: false,
			type: Boolean
		}
	},
	data () {
		return {
			optionPull: [],
			search_timer: null
		}
	},
	mounted () {
		if (this.options.length > 0) {
			this.optionPull = this.include.concat(this.options)
		}
		if (this.url) {
			this.post(`${this.url}`, {})
				.then((response) => {
					const data = response.data
					if (data.length > 0) {
						this.optionPull = this.include.concat(data)
					} else {
						this.optionPull = this.include.concat(this.options)
					}
				})
				.catch((error) => {
					console.log(error)
				})
		}
	},
	watch: {},
	methods: {
		handleChange (e) {
			this.$emit('update:data', e)
		},
		fetchOptions (search, loading) {
			loading(true)
			if (this.search_timer) {
				clearTimeout(this.search_timer)
			}
			const vm = this
			this.search_timer = setTimeout(function () {
				vm.search(loading, search, vm)
			}, 800)
		},
		search (loading, search, vm) {
			if (this.url) {
				vm.post(`${vm.url}`, { search })
					.then((response) => {
						loading(false)
						const data = response.data
						if (data.length > 0) {
							vm.optionPull = vm.include.concat(data)
						} else {
							vm.optionPull = vm.include.concat(vm.options)
						}
					})
					.catch((error) => {
						loading(false)
						console.log(error)
					})
			} else {
				loading(false)
			}
		}
	}
}
</script>

<style lang="scss">
</style>
