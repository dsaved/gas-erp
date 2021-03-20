<template>
  <div class="cl">
    <vs-alert
      color="danger"
      title="Account Not Found"
      :active.sync="accoun_not_found"
    >
      <span>Account record with id: {{ accountid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="{ name: 'rbank-account' }"
          class="text-inherit underline"
          >All Accounts</router-link
        >
      </span>
    </vs-alert>

    <vx-card v-if="accoun_found" :title="data.name">
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
        </div>
        <div class="vx-col sm:w-1/4 w-full mb-2">
          <h6>
            <b>Bank:</b> <br />
            <small>{{ notNull(data.bank) }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/3 w-full mb-2">
          <h6>
            <b>Balance:</b><br />
            <small>{{ data.balance }}</small>
          </h6>
        </div>
      </div>
    </vx-card>

    <vx-card :title="'Total Surcharges: ' +total_surcharge" class="mt-5">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <header class="header-table vs-table--header my-3">
		<vs-spacer />
          <div
            class="flex flex-wrap-reverse items-center data-list-btn-container"
          >
            <vs-input
              id="text"
              type="text"
              class="mx-1"
              v-model="search"
              placeholder="Search date"
            />
          </div>
        </header>
        <div class="con-tablex vs-table--content">
          <div class="vs-con-tbody vs-table--tbody">
            <table class="vs-table vs-table--tbody-table">
              <thead class="vs-table--thead">
                <tr>
                  <th scope="col" class="td-check">
                    <vs-checkbox v-model="selectAll">#</vs-checkbox>
                  </th>
                  <th scope="col">Date</th>
                  <th scope="col" class="text-right">Penalty</th>
                  <th scope="col" class="text-right">Total Credit</th>
                  <th scope="col" class="text-right">Total Debit</th>
                  <th scope="col" class="text-right">Previous Cumulative balance</th>
                  <th scope="col" class="text-right">Untransfered Funds</th>
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
                    {{ record.date  }}
                  </td>
                  <td class="text-right">
                    {{ record.penalty }}
                  </td>
                  <td class="text-right">
                    {{ record.total_credit }}
                  </td>
                  <td class="text-right">
                    {{ record.total_debit }}
                  </td>
                  <td class="text-right">
                    {{ record.comulative_balnace_previous }}
                  </td>
                  <td class="text-right">
                    {{ record.untransfered_founds }}
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
              style="justify-content: space-between; display: flex; width: 100%"
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
</template>

<script>
// Import Swal
import Swal from 'sweetalert2'
import Datepicker from 'vuejs-datepicker'
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
	components: {
		Datepicker
	},
	data () {
		return {
			accoun_not_found: false,
			accoun_found: false,
			data: {
				type: Object,
				default () {
					return {}
				}
			},
			//receipt data list starts here
			pkey: 'surcharge-trans-list-key',
			message: '',
			numbering: 0,
			total_surcharge: '0.0',
			currentPage: 1,
			result_per_page: 20,
			loading: true,
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
			selectedRecords: [],
			search: '',
			records: [],
			search_timer: null
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
			this.getReceipt()
		},
		search (newVal, oldVal) {
			this.startSearch(newVal, oldVal)
		},
		pagination () {
			this.numbering = this.pagination.start
		}
	},
	methods: {
		number (num) {
			return this.numbering + num
		},
		startSearch () {
			if (this.search_timer) {
				clearTimeout(this.search_timer)
			}
			const vm = this
			this.search_timer = setTimeout(function () {
				vm.getReceipt()
			}, 800)
		},
		getReceipt () {
			this.loading = true
			this.post('/surcharge/charges', {
				result_per_page: this.result_per_page,
				page: this.currentPage,
				search: this.search,
				id: this.accountid
			})
				.then((response) => {
					this.loading = false
					this.message = ''
					if (response.data.success === true) {
						this.records = response.data.surcharge
						this.total_surcharge = response.data.total_surcharge
					} else {
						this.message = response.data.message
						this.records = []
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
					this.pagination = response.data.pagination
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
		getData () {
			this.loading = true
			this.post('/bankaccounts/editbank', {
				id: this.accountid
			})
				.then((response) => {
					this.loading = false
					if (response.data.success === true) {
						this.accoun_found = true
						this.data = response.data.bankaccounts[0]
						this.getReceipt()
					} else {
						this.accoun_not_found = true
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
					this.accoun_not_found = true
				})
		},
		deleteWarnSingle (id) {
			if (!this.canDelete()) {
				return Swal.fire(
					'Not Allowed!',
					'You do not have permission to delete any record',
					'error'
				)
			}
			Swal.fire({
				title: 'Are you sure?',
				text: 'You won\'t be able to revert this!',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!'
			}).then((result) => {
				if (result.isConfirmed) {
					this.delete([id])
				}
			})
		},
		delete (ids) {
			this.showLoading('Deleting Account, hang on a bit...')
			this.post('/bankaccounts/delete', {
				id: ids
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success === true) {
						this.$vs.notify({
							title: 'Error!!!',
							text: 'The Account has been deleted.',
							sticky: true,
							border: 'danger',
							color: 'dark',
							duration: null,
							position: 'bottom-left'
						})
						this.back()
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
