<template>
  <div class="cl">
    <vx-card title="Create Account Category">
      <p>
        The Account Category you create here will be ued as an option list when creating
        <b>Accounts</b>. <br />
        You can always modify the data later.
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/2 w-full mb-2">
              <vs-input
                v-validate="'required'"
                label-placeholder="Account Category Name"
                name="name"
                v-model="name"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('name')">{{
                errors.first("name")
              }}</span>

              <vs-button
                v-if="isEdit()"
                type="filled"
                @click.prevent="updateForm"
                class="mt-5 block"
                id="button-with-loading"
                >Update</vs-button
              >
              <vs-button
                v-else
                type="filled"
                @click.prevent="submitForm"
                class="mt-5 block"
                >Submit</vs-button
              >
            </div>
          </div>
        </form>
      </div>
    </vx-card>
  </div>
</template>

<script>
// Import Swal
import Swal from 'sweetalert2'

export default {
	beforeRouteEnter (to, from, next) {
		next((vm) => {
			if (
				to.meta &&
        to.meta.identity &&
        !vm.AppActiveUser.pages.includes(to.meta.identity)
			) {
				vm.pushReplacement(vm.AppActiveUser.baseUrl)
			}
		})
	},
	props: {
		catid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			loading: false,
			name: ''
		}
	},
	watch: {
		loading () {
			if (this.loading) {
				this.$loader = this.$vs.loading({
					background: this.backgroundLoading,
					color: this.colorLoading,
					container: '#button-with-loading',
					scale: 0.45
				})
			} else {
				this.$vs.loading.close('#button-with-loading > .con-vs-loading')
			}
		}
	},
	mounted () {
		if (this.isEdit()) {
			this.getData()
		}
	},
	methods: {
		isEdit () {
			return Number(this.catid) !== 0
		},
		resetForm () {
			this.name = ''
		},
		getData () {
			this.loading = true
			this.post('/accountcategory/get/', {
				id: this.catid
			})
				.then((response) => {
					this.loading = false
					if (response.data.success == true) {
						this.name = response.data.account_category[0].name
					} else {
						this.$vs.notify({
							title: 'Error!!!',
							text: `${response.data.message}`,
							sticky: true,
							border: 'danger',
							color: 'dark',
							duration: null,
							position: 'bottom-left'
						})
					}
				})
				.catch((error) => {
					this.loading = false
					this.$vs.notify({
						title: 'Error!!!',
						text: `${error.message}`,
						sticky: true,
						border: 'danger',
						color: 'dark',
						duration: null,
						position: 'bottom-left'
					})
				})
		},
		submitForm () {
			this.$validator.validateAll().then((result) => {
				if (result) {
					// if form have no errors
					if (!this.loading) {
						if (!this.canAdd()) {
							return Swal.fire(
								'Not Allowed!',
								'You do not have permission to add any record',
								'error'
							)
						}
						this.showLoading('Adding Account Category to system')
						this.post('/accountcategory/create', {
							name: this.name
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire('Account Category Added', result.data.message, 'success')
									this.resetForm()
								} else {
									Swal.fire('Failed!', result.data.message, 'error')
								}
							})
							.catch((error) => {
								this.closeLoading()
								Swal.fire('Failed!', error.message, 'error')
							})
					}
				}
			})
		},
		updateForm () {
			this.$validator.validateAll().then((result) => {
				if (result) {
					// if form have no errors
					if (!this.loading) {
						if (!this.canUpdate()) {
							return Swal.fire(
								'Not Allowed!',
								'You do not have permission to update any record',
								'error'
							)
						}
						this.showLoading('Updating current Account Category')
						this.post('/accountcategory/update', {
							id: this.catid,
							name: this.name
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Account Category Updated',
										result.data.message,
										'success'
									)
								} else {
									Swal.fire('Failed!', result.data.message, 'error')
								}
							})
							.catch((error) => {
								this.closeLoading()
								Swal.fire('Failed!', error.message, 'error')
							})
					}
				}
			})
		}
	}
}
</script>
