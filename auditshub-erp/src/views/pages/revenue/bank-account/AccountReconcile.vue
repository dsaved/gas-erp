
<template>
  <div id="bankaccounts-org-view">
    <vs-alert
      color="danger"
      title="Account Not Found"
      :active.sync="record_not_found"
    >
      <span>Account with id: {{ accountid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="{ name: 'rbank-account' }"
          class="text-inherit underline"
          >All Accounts</router-link
        >
      </span>
    </vs-alert>

    <div id="bankaccounts-data" v-if="record_found">
      <vx-card :title="data.name">
        <p>
          <small>Owned by: {{ notNull(data.owner) }}</small
          ><br />
          <span v-html="accountNumbers(data)"></span> <br />
          <small>record created on: {{ data.created }}</small>
        </p>
        <div class="vx-row mt-5">
          <h5 class="vx-col sm:w-1/1 w-full mb-2">
            <strong>Information</strong>
          </h5>
          <div class="vx-col sm:w-1/3 w-full mb-2 ml-5">
            <h6>
              <b>Bank Type:</b> <br />
              <small>{{ data.bank_type }}</small>
            </h6>
            <br />
            <h6>
              <b>Bank:</b> <br />
              <small>{{ notNull(data.bank) }}</small>
            </h6>
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <h6>
              <b>status:</b><br />
              <small>{{ data.status }}</small>
            </h6>
            <br />
            <h6>
              <b>Inactive Date:</b><br />
              <small>{{ data.date_inactive }}</small>
            </h6>
          </div>
          <div class="vx-col sm:w-1/3 w-full mb-2">
            <h6>
              <b>Balance:</b><br />
              <small>{{ data.balance }}</small>
            </h6>
            <br />
            <h6>
              <b>Date:</b><br />
              <small>{{ data.post_date }}</small>
            </h6>
          </div>
          <!-- /Information - Col 2 -->
          <div class="vx-col w-full flex" id="account-manage-buttons">
            <vs-spacer />
            <vs-button
              type="relief"
              color="dark"
              icon-pack="feather"
              icon="icon-shuffle"
              v-if="canAdd()"
              @click="startReconcilationWarn()"
              >Start Reconcilation</vs-button
            >
          </div>
        </div>
      </vx-card>

      <vx-card title="Bank Accounts" class="mt-5">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <div class="w-full flex mb-4">
            <div class="w-1/3 px-2">
              <span>Bank Type</span>
              <ajax-select
                placeholder="Select bank type"
                :options="[
                  { value: 'all', label: 'All' },
                  { value: 'bank of ghana', label: 'Bank of Ghana' },
                  { value: 'other banks', label: 'Other Banks' },
                ]"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="bank_type"
                v-on:update:data="bank_type = $event"
              />
            </div>
            <div class="w-1/3 px-2">
              <span>Bank Name</span>
              <ajax-select
                placeholder="Select bank name"
                :options="[]"
                url="/bankaccounts/options_otherbanks"
                :clearable="false"
                :include="[{ value: 'all', label: 'All' }]"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="bank_name"
                v-on:update:data="bank_name = $event"
              />
            </div>
            <div class="w-1/5 px-2">
              <span>Result per page</span>
              <v-select
                placeholder="Result count"
                :clearable="false"
                :options="[
                  '10',
                  '20',
                  '30',
                  '40',
                  '50',
                  '60',
                  '70',
                  '80',
                  '90',
                  '100',
                ]"
                v-model="result_per_page"
              />
            </div>
            <div class="w-1/3 px-2">
              <span>Bank Category</span>
              <ajax-select
                placeholder="Select Category"
                :options="[]"
                :include="[{ value: 'all', label: 'All' }]"
                url="/bankaccounts/options_category"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="filter_category"
                v-on:update:data="filter_category = $event"
              />
            </div>
          </div>
          <header class="header-table vs-table--header my-3">
            <div
              class="flex flex-wrap-reverse items-center data-list-btn-container"
            >
              <vs-button
                color="primary"
                icon-pack="feather"
                v-if="deletebutton && canAdd()"
                @click="map_account()"
                icon="icon-plus"
                class="mx-1"
                >Add Account</vs-button
              >
              <vs-button
                color="danger"
                icon-pack="feather"
                class="mx-1"
                v-if="deletebutton && canDelete()"
                @click="unmap_account()"
                icon="icon-minus"
                >Remove Account</vs-button
              >
            </div>
            <div
              class="flex flex-wrap-reverse items-center data-list-btn-container"
            >
              <vs-input
                id="text"
                type="text"
                class="mx-1"
                v-model="search"
                placeholder="Search accounts"
              />
            </div>
          </header>
          <div class="w-full flex mb-4">
            <div class="w-1/3 px-2">
              <span>Category for reconciliation</span>
              <ajax-select
                placeholder="Reconcile With"
                :options="[]"
                url="/bankaccounts/options_category"
                :clearable="false"
                :multiple="true"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="category_group"
                v-on:update:data="category_group = $event"
              />
              <span class="text-secondary"
                >when selected only accounts in this category will be used for
                reconciliation.</span
              >
            </div>
          </div>
          <div class="con-tablex vs-table--content">
            <div class="vs-con-tbody vs-table--tbody">
              <table class="vs-table vs-table--tbody-table">
                <thead class="vs-table--thead">
                  <tr>
                    <th scope="col" class="td-check">
                      <vs-checkbox v-model="selectAll">#</vs-checkbox>
                    </th>
                    <th scope="col">Account</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Bank</th>
                    <th scope="col" class="text-right">Balance</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in sortedRecords"
                    :key="index"
                    class="tr-values vs-table--tr tr-table-state-null selected"
                  >
                    <td scope="row" @click.stop="">
                      <vs-checkbox
                        v-model="selectedRecords"
                        :vs-value="record.id"
                        >{{ number(index) }}</vs-checkbox
                      >
                    </td>
                    <td>
                      <b>{{ record.name | title }}</b> <br />
                      {{ record.acc_num1 }} <br />
                      {{ record.acc_num2 }}
                      <b v-if="record.aschiled" class="text-primary">Mapped</b>
                    </td>
                    <td>
                      {{ record.owner_name }}
                    </td>
                    <td>
                      {{ record.bank_name }}
                    </td>
                    <td class="text-right">
                      {{ record.account_balance }}
                    </td>
                    <td>
                      {{ record.post_date }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <h3 class="text-center" v-show="message">{{ message }}</h3>
            <div v-show="loading">
              <div style="margin-top: 1.5rem" class="loading">
                <div class="effect-1 effects"></div>
                <div class="effect-2 effects"></div>
                <div class="effect-3 effects"></div>
              </div>
            </div>
            <!---->
            <div class="con-pagination-table vs-table--pagination">
              <div
                class="vs-row"
                style="
                  justify-content: space-between;
                  display: flex;
                  width: 100%;
                "
              >
                <div
                  class="vs-col vs-pagination--mb vs-xs-12 vs-sm-12 vs-lg-6"
                  style="
                    justify-content: flex-start;
                    display: flex;
                    align-items: center;
                    margin-left: 0%;
                    width: 100%;
                  "
                ></div>
                <div
                  class="vs-col vs-pagination--mb vs-xs-12 vs-sm-12 vs-lg-12"
                  style="
                    justify-content: flex-end;
                    display: flex;
                    align-items: center;
                    margin-left: 0%;
                    width: 100%;
                  "
                >
                  <div class="text-muted">
                    Page {{ pagination.page }} of {{ pagination.pages }}.
                    {{ pagination.start }} - {{ pagination.end }} of
                    {{ pagination.total }} Results &nbsp;
                  </div>
                  <div class="con-vs-pagination vs-pagination-primary">
                    <vs-pagination
                      color="danger"
                      v-if="pagination.haspages"
                      v-model="currentPage"
                      :total="pagination.pages"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </vx-card>
    </div>

    <vs-popup
      background-color="rgba(200,200,200,.8)"
      persistent
      :button-close-hidden="true"
      title="Reconcilation In Progress"
      :active.sync="popupActive"
    >
      <p>
        Reconciling Account <b>{{ data.name }} Receipts</b>
      </p>
      <p>
        <span
          v-for="(desc, index) in importDesc"
          :key="index"
          v-html="formatDesc(desc)"
          ><br
        /></span>
      </p>
      <p v-if="hasdata(reconcilationStatus)" class="text-info loadingDot">
        {{ reconcilationStatus }}
      </p>

      <p>
        <span class="text-secondary"
          ><b>{{ reconcileDetails }}</b></span
        >
      </p>
      <vs-row>
        <vs-col
          vs-offset="8"
          vs-type="flex"
          vs-justify="center"
          vs-align="center"
          vs-w="4"
        >
          <vs-button
            v-if="reloadButton"
            color="primary"
            icon-pack="feather"
            icon="icon-refresh-cw"
            @click="startCheckReconcilationStatus()"
            class="mx-1"
            >reload</vs-button
          >

          <vs-button
            v-if="canCloseModal"
            color="danger"
            icon-pack="feather"
            icon="icon-x"
            @click="clearLog()"
            class="mx-1"
            >Close</vs-button
          >
        </vs-col>
      </vs-row>
    </vs-popup>
  </div>
</template>

<script>
// Import Swal
import Swal from 'sweetalert2'
import mStorage from '@/store/storage.js'

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
	beforeRouteLeave (to, from, next) {
		if (this.statuscheck) {
			clearInterval(this.statuscheck)
			this.statuscheck = null
		}
		next()
	},
	props: {
		accountid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			record_not_found: false,
			record_found: false,
			data: {
				type: Object,
				default () {
					return {}
				}
			},
			//reconciliation datat section
			popupActive: false,
			canCloseModal: false,
			reloadButton: false,
			search_timer: null,
			statuscheck: null,
			jobid: null,
			errorStr: ['unknown jobid', 'error'],
			importDesc: [],
			reconcilationStatus: '',
			reconcileDetails: '',
			//receipt data list starts here
			pkey: 'account-trans-rec-list-key',
			message: '',
			numbering: 0,
			currentPage: 1,
			result_per_page: 20,
			loading: true,
			interval: null,
			transType: null,
			deletebutton: false,
			pagination: {
				haspages: false,
				page: 0,
				start: 0,
				end: 0,
				total: 0,
				pages: 0,
				hasNext: false,
				hasPrevious: false
			},
			reconcileWith: [],
			selectedRecords: [],
			search: '',
			records: [],
			bank_type: { value: 'all', label: 'All' },
			bank_name: { value: 'all', label: 'All' },
			category_group: [],
			banks: [],
			categories: [],
			filter_category: { value: 'all', label: 'All' }
		}
	},
	computed: {
		selectAll: {
			get () {
				return this.records ? this.selectedRecords.length === this.records.length : false
			},
			set (value) {
				const selected = []

				if (value) {
					this.records.forEach(function (record) {
						selected.push(record.id)
					})
				}
				this.selectedRecords = selected
			}
		},
		sortedRecords () {
			try {
				return this.filterObj(this.records, this.search).sort((a, b) => {
					let modifier = 1
					if (this.currentSortDir === 'desc') modifier = -1
					if (a[this.currentSort] < b[this.currentSort]) return -1 * modifier
					if (a[this.currentSort] > b[this.currentSort]) return 1 * modifier
					return 0
				})
			} catch (error) {
				console.warn(error)
			}
		},
		photo () {
			return require('@/assets/images/portrait/small/default.png')
		}
	},
	mounted () {
		this.currentPage =
      Number(mStorage.get(`${this.pkey}page${this.accountid}`)) || 1
		this.getData()
	},
	watch: {
		currentPage () {
			mStorage.set(`${this.pkey}page${this.accountid}`, this.currentPage)
			this.loadAccounts()
		},
		result_per_page () {
			this.loadAccounts(true)
		},
		bank_type () {
			this.loadAccounts(true)
		},
		bank_name () {
			this.loadAccounts(true)
		},
		filter_category () {
			this.loadAccounts(true)
		},
		search (newVal, oldVal) {
			this.startSearch(newVal, oldVal)
		},
		pagination () {
			this.numbering = this.pagination.start
		},
		selectedRecords () {
			if (this.selectedRecords.length > 0) {
				this.deletebutton = true
			} else {
				this.deletebutton = false
			}
		}
	},
	methods: {
		notNull (data) {
			if (null !== data && data.label) {
				return data.label
			}
			return ''
		},
		accountNumbers (account) {
			let acnts = ''
			if (this.hasdata(account.acc_num1) && this.hasdata(account.acc_num2)) {
				acnts += `${account.acc_num1  } | ${  account.acc_num1}`
			} else if (this.hasdata(account.acc_num1)) {
				acnts += account.acc_num1
			} else if (this.hasdata(account.acc_num2)) {
				acnts += account.acc_num2
			}
			if (account.status === 'Inactive') {
				acnts += ' - <span class="text-danger">Inactive</span> '
			} else {
				acnts += ' - <span class="text-primary">Active</span> '
			}
			return acnts
		},
		number (num) {
			return this.numbering + num
		},
		startSearch () {
			if (this.search_timer) {
				clearTimeout(this.search_timer)
			}
			const vm = this
			this.search_timer = setTimeout(function () {
				vm.loadAccounts()
			}, 800)
		},
		getData () {
			this.showLoading('getting Account infomation')
			this.post('/bankaccounts/editbank', {
				id: this.accountid
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success === true) {
						this.record_found = true
						this.data = response.data.bankaccounts[0]
						this.loadAccounts()
					} else {
						this.record_not_found = true
						this.$vs.notify({
							title: 'Error!!!',
							text: `${response.data.message}`,
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
					}
				})
				.catch((error) => {
					this.closeLoading()
					this.$vs.notify({
						title: 'Error!!!',
						text: `${error.message}`,
						sticky: true,
						color: 'danger',
						duration: null,
						position: 'bottom-left'
					})
					this.record_not_found = true
				})
		},
		//reconciliation starts here
		loadAccounts () {
			this.loading = true
			this.post('/bankaccounts/reconcile', {
				bid: this.accountid,
				access_type: this.AppActiveUser.access_level,
				user_id: this.AppActiveUser.id,
				page: this.currentPage,
				result_per_page: this.result_per_page,
				bank_type: this.bank_type.value,
				bank_name: this.bank_name.label,
				filter_category: this.filter_category.value,
				search: this.search
			})
				.then((response) => {
					console.log(response.data)
					this.records = []
					this.loading = false
					this.message = response.data.message
					this.pagination = response.data.pagination
					if (response.data.success) {
						this.records = response.data.bankaccounts
					}
				})
				.catch((error) => {
					this.hasData = false
					this.loading = false
					console.log(error)
				})
		},
		startCheckReconcilationStatus () {
			this.canCloseModal = false
			this.reloadButton = false
			const vm = this
			this.statuscheck = setInterval(function () {
				vm.checkReconcilationStatus()
			}, 1200)
		},
		formatDesc (data) {
			let error = false
			this.errorStr.forEach((item) => {
				if (data && data.toLowerCase().includes(item)) {
					error = true
				}
			})
			if (error) {
				return `<span class="text-danger">-> ${data}<br/></span> `
			}
			return `<span class="text-primary">-> ${data}<br/></span> `
		},
		pushDescription (data) {
			if (!this.importDesc.includes(data)) {
				this.importDesc.push(data)
			}
		},
		clearLog () {
			this.popupActive = false
			this.canCloseModal = false
			this.reloadButton = false
			this.jobid = null
			this.importDesc = []
			this.reconcilationStatus = ''
			if (this.statuscheck) {
				clearInterval(this.statuscheck)
			}
		},
		checkReconcilationStatus () {
			this.post('/bankaccounts/reconcilation_status', {
				jobid: this.jobid
			})
				.then((response) => {
					console.log(response.data)
					const data = response.data
					if (data.success) {
						const status = data.status
						this.pushDescription(status.description)
						this.reconcilationStatus = status.status
						this.reconcileDetails = status.details
						if (status.status.toLowerCase() === 'completed') {
							clearInterval(this.statuscheck)
							this.reconcilationStatus = ''
							this.canCloseModal = true
						}
						if (status.status.toLowerCase().includes('error')) {
							clearInterval(this.statuscheck)
							this.reconcilationStatus = ''
							this.canCloseModal = true
						}
					} else {
						this.pushDescription(response.data.message)
						this.reconcilationStatus = ''
						clearInterval(this.statuscheck)
						this.canCloseModal = true
					}
				})
				.catch((error) => {
					this.pushDescription('a network error has occured')
					clearInterval(this.statuscheck)
					this.reconcilationStatus = ''
					this.canCloseModal = true
					this.reloadButton = true
					console.log(error)
				})
		},
		map_account () {
			Swal.fire({
				title: 'Are you sure?',
				text: 'You are about to make this account a sub account',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, map it!'
			}).then((result) => {
				if (result.isConfirmed) {
					this.reconcileWith = this.selectedRecords
					this.showLoading('Mapping account to Account')
					this.post('/bankaccounts/add_to_sub_account', {
						account: this.accountid,
						id: this.reconcileWith,
						type: this.data.bank_type
					})
						.then((response) => {
							this.closeLoading()
							if (response.data.success === true) {
								this.selectedRecords = []
								this.loadAccounts()
								Swal.fire(
									'Account(s) mapped',
									response.data.message,
									'success'
								)
							} else {
								Swal.fire('Failed!', response.data.message, 'error')
							}
						})
						.catch((error) => {
							this.closeLoading()
							Swal.fire('Failed!', error.message, 'error')
						})
				}
			})
		},
		unmap_account () {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you realy want to remove this as sub account?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, unmap it!'
			}).then((result) => {
				if (result.isConfirmed) {
					this.reconcileWith = this.selectedRecords
					this.showLoading('Unmapping account from Account')
					this.post('/bankaccounts/remove_sub_account', {
						account: this.accountid,
						id: this.reconcileWith,
						type: this.data.bank_type
					})
						.then((response) => {
							this.closeLoading()
							if (response.data.success === true) {
								this.selectedRecords = []
								this.loadAccounts()
								Swal.fire(
									'Account(s) unmapped',
									response.data.message,
									'success'
								)
							} else {
								Swal.fire('Failed!', response.data.message, 'error')
							}
						})
						.catch((error) => {
							this.closeLoading()
							Swal.fire('Failed!', error.message, 'error')
						})
				}
			})
		},
		async startReconcilationWarn () {
			const inputOptions = {
				0: 'Same Day Date',
				1: '1 Day Interval'
			}
			for (let index = 2; index <= 100; index++) {
				inputOptions[index] = `${index  } Days Interval`
			}
			const currentVal = this.interval !== null ? this.interval : ''
			const { value: interval, isDismissed: isDismissed } = await Swal.fire({
				title: 'Confirm Reconcilation',
				text: 'You are about to start reconciliation for this account!',
				icon: 'question',
				input: 'select',
				inputOptions,
				showCancelButton: true,
				inputValue: currentVal,
				confirmButtonColor: '#0d6723',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, continue!',
				inputPlaceholder: 'Select Date Interval',
				inputValidator: () => {}
			})
			if (!isDismissed) {
				this.interval = interval
				this.choseTransactionType()
			}
		},
		async choseTransactionType () {
			const inputOptions = {
				credit: 'Credit Agaist Debit',
				debit: 'Debit Agaist Credit'
			}
			const currentVal = 'debit'
			const {
				value: transactionType,
				isDismissed: isDismissed
			} = await Swal.fire({
				title: 'Confirm transaction type',
				text: 'select transaction type to rconcile parent agaist child account',
				icon: 'question',
				input: 'select',
				inputOptions,
				showCancelButton: true,
				inputValue: currentVal,
				confirmButtonColor: '#0d6723',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, continue!',
				inputPlaceholder: 'Select type',
				inputValidator: () => {}
			})
			if (!isDismissed) {
				this.transType = transactionType
				this.startReconcilation()
			}
		},
		startReconcilation () {
			let cat = 0
			if (!this.hasdata(this.category_group)) {
				cat = 0
			} else {
				const catArray = []
				this.category_group.forEach((category) => {
					catArray.push(category.value)
				})
				cat = catArray
			}

			this.showLoading('Sending Request For Reconcilation')
			this.post('/bankaccounts/start_reconcilation', {
				account: this.accountid,
				interval: this.interval,
				transtype: this.transType,
				user_id: this.AppActiveUser.id,
				access_type: this.AppActiveUser.access_level,
				category_group: cat
			})
				.then((response) => {
					console.log(response.data)
					this.closeLoading()
					if (response.data.success === true) {
						this.popupActive = true
						this.pushDescription(response.data.message)
						this.reconcilationStatus = 'Initializing'
						this.jobid = response.data.jobid
						this.startCheckReconcilationStatus()
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		}
	}
}
</script>

<style lang="scss">
.loadingDot:after {
  content: " .";
  font-size: 50px;
  line-height: 0;
  animation: dots 1s steps(5, end) infinite;
}
</style>