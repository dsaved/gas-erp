<template>
  <div class="cl">
    <vx-card title="Create Account ">
      <p>
        The Account you create here will be used for reconciliation in the revenue section <br />
        You can always modify the data later.
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="'required'"
                label-placeholder="Account Name"
                name="name"
                v-model="name"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('name')">{{
                errors.first("name")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <div class="vs-con-input hasValue" style="position: relative">
                <span
                  class="input-span-placeholder vs-input--placeholder"
                  style="top: -10px"
                >
                  Owner
                </span>
              </div>
              <ajax-select
                placeholder="Select owner"
                :options="[]"
                url="/bankaccounts/options_owners"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="owner"
                c-class="mt-5 w-full"
                v-on:update:data="owner = $event"
              />
              <vs-input
                v-validate="'required'"
                name="owner"
                v-model="owner.value"
                hidden
              />
              <span class="text-danger text-sm" v-show="errors.has('owner')">{{
                errors.first("owner")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <div class="vs-con-input hasValue" style="position: relative">
                <span
                  class="input-span-placeholder vs-input--placeholder"
                  style="top: -10px"
                >
                  Bank Type
                </span>
              </div>
              <v-select
                :clearable="false"
                placeholder="Select bak type"
                :options="types"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                v-model="bank_type"
                class="mt-5 w-full"
              />
              <vs-input
                v-validate="'required'"
                name="bank type"
                v-model="bank_type"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('bank type')"
                >{{ errors.first("bank type") }}</span
              >
            </div>

            <div
              class="vx-col sm:w-1/3 w-full mb-2"
              v-if="bank_type == types[1]"
            >
              <div class="vs-con-input hasValue" style="position: relative">
                <span
                  class="input-span-placeholder vs-input--placeholder"
                  style="top: -10px"
                >
                  Bank
                </span>
              </div>
              <ajax-select
                placeholder="Select bank"
                :options="[]"
                url="/bankaccounts/options_otherbanks"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="bank"
                c-class="mt-5 w-full"
                v-on:update:data="bank = $event"
              />
              <vs-input
                v-validate="'required'"
                name="bank"
                v-model="bank.value"
                hidden
              />
              <span class="text-danger text-sm" v-show="errors.has('bank')">{{
                errors.first("bank")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="'min:5|max:19|numeric'"
                label-placeholder="Old Account Number"
                name="old account number"
                v-model="acc_num1"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('old account number')"
                >{{ errors.first("old account number") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="'min:5|max:15|numeric'"
                label-placeholder="New Account Number"
                name="new account number"
                v-model="acc_num2"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('new account number')"
                >{{ errors.first("new account number") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <div class="vs-con-input hasValue" style="position: relative">
                <span
                  class="input-span-placeholder vs-input--placeholder"
                  style="top: -10px"
                >
                  Account category
                </span>
              </div>
              <ajax-select
                placeholder="Select account category"
                :options="[]"
                url="/bankaccounts/options_category"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="category"
                c-class="mt-5 w-full"
                v-on:update:data="category = $event"
              />
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2" v-if="isEdit()">
              <div class="vs-con-input hasValue" style="position: relative">
                <span
                  class="input-span-placeholder vs-input--placeholder"
                  style="top: -10px"
                >
                  Account status
                </span>
              </div>
              <v-select
                placeholder="Select account status"
                :options="['Active', 'Inactive']"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                class="mt-5 w-full"
                v-model="status"
              />
            </div>

            <div v-if="status=='Inactive'" class="vx-col sm:w-1/3 w-full mb-2">
              <datepicker
                class="mt-5 w-full"
                placeholder="Inactive Date"
                v-model="date_inactive"
              ></datepicker>
              <vs-input
                v-validate="'required'"
                name="Inactive Date"
                v-model="dateinactive"
                type="date"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('Inactive Date')"
                >{{ errors.first("Inactive Date") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/1 w-full mb-2">
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
	props: {
		accountid: {
			type: String / Number,
			default: 0
		}
	},
	components: {
		Datepicker
	},
	data () {
		return {
			loading: false,
			name: '',
			bank_type: '',
			acc_num1: '',
			acc_num2: '',
			status: '',
			date_inactive: '',
			owner: [],
			bank: [],
			category: [],
			bogbank: {},
			types: ['Bank Of Ghana', 'Other Banks']
		}
	},
	computed: {
		dateinactive () {
			return String(this.date_inactive)
		}
	},
	watch: {
		bank_type () {
			if (this.bank_type == this.types[0]) {
				this.bank = this.bogbank
			}
		}
	},
	mounted () {
		this.loadOptions()
		if (this.isEdit()) {
			this.getData()
		}
	},
	methods: {
		isEdit () {
			return Number(this.accountid) !== 0
		},
		resetForm () {
			this.name = ''
		},
		loadOptions () {
			this.post('/bankaccounts/bogbank', {})
				.then((response) => {
					this.bogbank = response.data
				})
				.catch((error) => {
					console.log(error)
				})
		},
		getData () {
			this.loading = true
			this.post('/bankaccounts/editbank', {
				id: this.accountid
			})
				.then((response) => {
					this.loading = false
					if (response.data.success == true) {
						const account = response.data.bankaccounts[0]

						this.name = account.name
						this.acc_num1 = account.acc_num1
						this.acc_num2 = account.acc_num2
						this.status = account.status
						this.owner = account.owner
						this.category = account.category
						if (account.bank_type == this.types[0]) {
							this.bogbank = account.bank
						}
						this.bank_type = account.bank_type
						this.bank = account.bank
						this.date_inactive = account.date_inactive
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
				if (!this.hasdata(this.acc_num1) && !this.hasdata(this.acc_num2)) {
					this.$vs.notify({
						title: 'Error!!!',
						text: 'Please provide atleast one account number ',
						sticky: true,
						color: 'danger',
						duration: null,
						position: 'bottom-left'
					})
					return
				}
				if (this.bank_type == this.types[0]) {
					if (this.hasdata(this.acc_num1) && !this.acc_num1.startsWith('1')) {
						this.$vs.notification({
							title: 'Error!!!',
							text: 'Invalid old account number ',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
					if (this.hasdata(this.acc_num2) && !this.acc_num2.startsWith('1')) {
						this.$vs.notify({
							title: 'Error!!!',
							text: 'Invalid new account number ',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
					if (!this.bank || !this.bank.value) {
						this.$vs.notify({
							title: 'Error!!!',
							text: 'No Bank of ghana exist',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
				}

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
						this.showLoading('Adding Account  to system')
						this.post('/bankaccounts/create', {
							name: this.name,
							bank_type: this.bank_type,
							acc_num1: this.acc_num1,
							acc_num2: this.acc_num2,
							owner: this.owner.value,
							bank: this.bank.value,
							category: this.category.value
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Account  Added',
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
				if (!this.hasdata(this.acc_num1) && !this.hasdata(this.acc_num2)) {
					this.$vs.notify({
						title: 'Error!!!',
						text: 'Please provide atleast one account number ',
						sticky: true,
						color: 'danger',
						duration: null,
						position: 'bottom-left'
					})
					return
				}
				if (this.bank_type == this.types[0]) {
					if (this.hasdata(this.acc_num1) && !this.acc_num1.startsWith('1')) {
						this.$vs.notification({
							title: 'Error!!!',
							text: 'Invalid old account number ',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
					if (this.hasdata(this.acc_num2) && !this.acc_num2.startsWith('1')) {
						this.$vs.notify({
							title: 'Error!!!',
							text: 'Invalid new account number ',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
					if (!this.bank || !this.bank.value) {
						this.$vs.notify({
							title: 'Error!!!',
							text: 'No Bank of ghana exist',
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						return
					}
				}

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
						this.showLoading('Updating current Account ')
						this.post('/bankaccounts/update', {
							id: this.accountid,
							name: this.name,
							bank_type: this.bank_type,
							acc_num1: this.acc_num1,
							acc_num2: this.acc_num2,
							owner: this.owner.value,
							bank: this.bank.value,
							category: this.category.value,
							status: this.status,
							date_inactive: this.date_inactive
						})
							.then((result) => {
								console.log(result.data)
								this.closeLoading()
								if (result.data.success == true) {
									Swal.fire(
										'Account  Updated',
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
