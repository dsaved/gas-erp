<template>
  <div class="cl">
    <vx-card title="Create Depot">
      <p>
        create
        <b>DEPOT</b>. You can always modify the data later.
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="'required'"
                label-placeholder="Name"
                name="name"
				:disabled="isEdit()"
                v-model="name"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('name')">{{
                errors.first("name")
              }}</span>
            </div>
            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="''"
                label-placeholder="Email"
                name="email"
                v-model="email"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('email')">{{
                errors.first("email")
              }}</span>
            </div>
            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="''"
                label-placeholder="Phone"
                name="phone"
                v-model="phone"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('phone')">{{
                errors.first("phone")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/2 w-full mb-2">
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
		depotid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			loading: false,
			name: '',
			email: '',
			phone: ''
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
			return Number(this.depotid) !== 0
		},
		resetForm () {
			this.name = ''
			this.email = ''
			this.phone = ''
		},
		getData () {
			this.loading = true
			this.post('/depot/get/', {
				id: this.depotid
			})
				.then((response) => {
					this.loading = false
					if (response.data.success == true) {
						const result = response.data.omcs[0]
						this.name = result.name
						this.email = result.email
						this.phone = result.phone
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
						this.showLoading('Adding Depot to system')
						this.post('/depot/add', {
							name: this.name,
							email: this.email,
							phone: this.phone,
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire('Depot Added', result.data.message, 'success')
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
						this.showLoading('Updating current Depot')
						this.post('/depot/update', {
							id: this.depotid,
							name: this.name,
							email: this.email,
							phone: this.phone,
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Depot Updated',
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
