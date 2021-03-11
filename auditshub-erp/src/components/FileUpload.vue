<!-- =========================================================================================
    File Name: VxTour.vue
    Description: Tour Component
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div>
    <div class="flex flex-wrap items-center">
      <vs-progress
        v-if="fileuploading"
        :percent="progress_width"
        :color="progress_color"
      ></vs-progress>
      <input
        id="fileUpload"
        hidden
        type="file"
        :accept="allowedFileType"
        @change="uploadFile($event.target.files)"
      />
      <vs-button
        :color="color"
        :type="type"
        size="small"
        icon-pack="feather"
        icon="icon-upload"
        @click="chooseFiles()"
      >
        {{ uploadButtonLable }}
      </vs-button>
      <vs-spacer />
      <vs-button
        v-if="removeButton"
        color="danger"
        :type="type"
        size="small"
        icon-pack="feather"
        icon="icon-trash"
        @click="removeFileClicked()"
      >
        Remove
      </vs-button>
    </div>
    <p v-if="description" class="text-sm mt-2">{{ description }}</p>
    <p v-if="error_msg" class="text-danger text-sm mt-2">{{ error_msg }}</p>
    <p v-if="success_msg" class="text-success text-sm mt-2">
      {{ success_msg }}
    </p>
  </div>
</template>

<script>
export default {
	name: 'ds-file-upload',
	props: {
		uploadButtonLable: {
			default: 'Select File',
			type: String
		},
		description: {
			default: 'Allowed JPG, GIF or PNG. Max size of 800kB',
			type: String
		},
		allowedFileType: {
			default: '*/*',
			type: String
		},
		uploadUrl: {
			default: '',
			type: String
		},
		maxSize: {
			default: 2040,
			type: Number / String
		},
		oldfile: {
			default: '',
			type: String
		},
		multiple: {
			default: false,
			type: Boolean
		},
		removeButton: {
			default: false,
			type: Boolean
		},
		fileId: {
			default: null,
			type: String / Number
		},
		type: {
			default: 'border',
			type: String
		},
		color: {
			default: 'danger',
			type: String
		}
	},
	data () {
		return {
			progress_color: '',
			error_msg: '',
			success_msg: '',
			fileuploading: false, //only for filelink upload
			progress_width: 0, //only for filelink upload
			error: false //only for filelink upload
		}
	},
	watch: {
		progress_width () {
			if (this.progress_width >= 100) {
				this.progress_color = 'success'
			} else {
				this.progress_color = '#24c1a0'
			}
		},
		error () {
			if (this.error) {
				this.progress_color = 'danger'
			}
		}
	},
	mounted () {},
	methods: {
		chooseFiles () {
			document.getElementById('fileUpload').click()
		},
		removeFileClicked () {
			this.success_msg = ''
			this.error_msg = ''
			this.fileuploading = false
			this.$emit('remove-file')
		},
		convertBytes (bytes, decimals = 2) {
			if (bytes === 0) return 0

			const k = 1024
			const dm = decimals < 0 ? 0 : decimals

			return parseFloat((bytes / k).toFixed(dm))
		},
		uploadFile (value) {
			if (value.length < 1) return false
			this.success_msg = ''
			this.error_msg = ''
			if (this.multiple) {
				let continuee = true
				value.forEach((file) => {
					const size = this.convertBytes(file.size)
					if (size > parseFloat(this.maxSize)) {
						continuee = false
						this.error_msg = `one or more file size exceeded (${this.maxSize}kb)`
					}
				})

				if (!continuee) {
					return false
				}
			} else {
				const file = value[0]
				const size = this.convertBytes(file.size)
				if (size > parseFloat(this.maxSize)) {
					this.error_msg = `file size (${size}kb) exceeded (${this.maxSize}kb)`
					return false
				}
			}
			this.fileuploading = true
			const formData = new FormData()
			if (this.multiple) {
				formData.append('file[]', value)
			} else {
				formData.append('file', value[0])
			}
			if (this.fileId) {
				formData.append('id', this.fileId)
			}
			if (this.oldfile) {
				formData.append('oldfile', this.oldfile)
			}
			const config = {
				onUploadProgress: (progressEvent) => {
					const percentCompleted = Math.floor(
						(progressEvent.loaded * 100) / progressEvent.total
					)
					this.progress_width = percentCompleted
				}
			}
			this.postFile(this.uploadUrl, formData, config)
				.then((response) => {
					document.getElementById('fileUpload').value = ''
					this.$emit('completed', response.data)
					if (response.data.success == true) {
						this.$emit('file-link', response.data.location)
						this.success_msg = response.data.message
					} else {
						this.error = true
						this.error_msg = response.data.message
					}
					this.fileuploading = false
					this.error = false
					this.progress_width = 0
				})
				.catch((error) => {
					document.getElementById('fileUpload').value = ''
					console.log(error)
					this.error_msg = error.message
					this.error = true
					this.fileuploading = false
					this.progress_width = 0
				})
		}
	}
}
</script>

<style lang="scss">
</style>
