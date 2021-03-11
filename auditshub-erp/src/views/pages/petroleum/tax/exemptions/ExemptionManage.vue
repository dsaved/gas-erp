<template>
  <div class="cl">
    <vx-card title="Create tax Exemption">
      <p>
        Creating an <b>exemption</b
        > for a particuler OMC will exclude that OMC from paying for that tax<br />
        for the given period of time
      </p>
      <div class="mt-5">
        <form :key="form_key">
          <div class="vx-row">
            <div class="vx-col sm:w-1/2 w-full mb-2">
              <ajax-select
                placeholder="Select omc"
                :options="[]"
                url="/omc/options"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="omc"
                v-on:update:data="omc = $event"
                c-class="mt-5 w-full"
              />
              <vs-input
                v-validate="'required'"
                name="omc"
                v-model="omc.value"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('omc')"
                >{{ errors.first("omc") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/2 w-full mb-2">
              <ajax-select
                placeholder="Select product"
                :options="[]"
                url="/taxproducts/options"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="tax_product"
                v-on:update:data="tax_product = $event"
                c-class="mt-5 w-full"
              />
              <vs-input
                v-validate="'required'"
                name="product"
                v-model="tax_product.value"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('product')"
                >{{ errors.first("product") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="{ required: true, regex: /^[+]?[0-9]+\.[0-9]+$/ }"
                label-placeholder="Litters"
                name="litters"
                v-model="litters"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('litters')">{{
                errors.first("litters")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <datepicker
                class="mt-5 w-full"
                placeholder="Tax Date From"
                v-model="date_from"
              ></datepicker>
              <vs-input
                v-validate="'required'"
                name="Date From"
                v-model="datefrom"
                type="date"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('Date From')"
                >{{ errors.first("Date From") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <datepicker
                class="mt-5 w-full"
                placeholder="Tax Date To"
                v-model="date_to"
              ></datepicker>
              <vs-input
                v-validate="'required'"
                name="Date To"
                v-model="dateto"
                type="date"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('Date To')"
                >{{ errors.first("Date To") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
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
import Datepicker from 'vuejs-datepicker'

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
	components: {
		Datepicker
	},
	props: {
		exemptionid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			loading: false,
			omc: [],
			tax_product: [],
			litters: '',
			date_from: '',
			date_to: '',
			form_key: '1'
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
	computed: {
		datefrom () {
			return String(this.date_from)
		},
		dateto () {
			return String(this.date_to)
		}
	},
	methods: {
		isEdit () {
			return Number(this.exemptionid) !== 0
		},
		resetForm () {
			this.form_key = new Date().toString()
		},
		getData () {
			this.loading = true
			this.post('/taxexemption/get/', {
				id: this.exemptionid
			})
				.then((response) => {
					this.loading = false
					if (response.data.success == true) {
						console.log(response.data)
						const result = response.data.exemptions[0]
						this.omc = result.omc
						this.tax_product = result.tax_product
						this.litters = result.litters
						this.date_from = result.date_from
						this.date_to = result.date_to
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
						this.showLoading('Adding tax Exemption to system')
						this.post('/taxexemption/add', {
							omc: this.omc.value,
							tax_product: this.tax_product.value,
							litters: this.litters,
							date_from: this.date_from,
							date_to: this.date_to
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Tax Exemption Added',
										result.data.message,
										'success'
									)
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
						this.showLoading('Updating current tax exemption')
						this.post('/taxexemption/update', {
							id: this.exemptionid,
							omc: this.omc.value,
							tax_product: this.tax_product.value,
							litters: this.litters,
							date_from: this.date_from,
							date_to: this.date_to
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Tax Exemption Updated',
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
