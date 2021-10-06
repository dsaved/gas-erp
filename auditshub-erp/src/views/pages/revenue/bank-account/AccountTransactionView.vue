
<template>
  <div id="transactions-org-view">
    <vs-alert
      color="danger"
      title="Transaction Not Found"
      :active.sync="record_not_found"
    >
      <span> Transaction with id: {{ transactionid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="'/revenue/accounts/' + accountid + '/view'"
          class="text-inherit underline"
          >Receipts</router-link
        >
      </span>
    </vs-alert>

    <div id="transactions-transactions" v-if="record_found">
      <vx-card class="mb-base">
      <h5><b>{{ transactions.account_name }}</b></h5>
      <p>
        <span v-html="accountNumbers(transactions)"></span> <br />
        <small>Owned by: {{ transactions.orgname }}</small><br />
        <small>account created on: {{ transactions.created }}</small>
      </p>
      <div class="vx-row mt-5">
        <h5 class="vx-col sm:w-1/1 w-full mb-2">
          <strong>Information</strong>
        </h5>
        <div class="vx-col sm:w-1/3 w-full mb-2 ml-5">
          <h6>
            <b>Post Date:</b> <br />
            <small>{{ transactions.post_date }}</small>
          </h6>
          <br />
          <h6>
            <b>Debit Amount:</b> <br />
            <small>{{ transactions.debit_amount }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/4 w-full mb-2">
          <h6>
            <b>Value Date:</b><br />
            <small>{{ transactions.value_date }}</small>
          </h6>
          <br />
          <h6>
            <b>Credit Amount:</b><br />
            <small>{{ transactions.credit_amount }}</small>
          </h6>
          <br />
        </div>
        <div class="vx-col sm:w-1/3 w-full mb-2">
          <h6>
            <b>Reference:</b><br />
            <small>{{ transactions.reference }}</small>
          </h6>
          <br />
          <h6>
            <b>Offset Account No.:</b><br />
            <small>{{ transactions.offset_acc_no }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/1 w-full mb-2 ml-5">
          <h6>
            <b>Description / Particulars:</b> <br />
            <small>{{ transactions.particulars }}</small>
          </h6>
        </div>
      </div>
      </vx-card>

      <vs-row vs-type="flex" vs-justify="space-between">
        <vs-col vs-type="flex" vs-justify="center" vs-align="center" vs-w="2">
          <vs-button
            @click="currentPage--"
            v-if="pagination.hasPrevious"
            color="rgb(16 22 58 / 82%)"
            type="relief"
            icon-pack="feather"
            icon="icon-arrow-left"
            >Previous</vs-button
          >
        </vs-col>

        <vs-col vs-type="flex" vs-justify="center" vs-align="center" vs-w="2">
          <vs-button
            @click="currentPage++"
            v-if="pagination.hasNext"
            color="rgb(16 22 58 / 82%)"
            type="relief"
            icon-pack="feather"
            icon="icon-arrow-right"
            icon-after
            >Next</vs-button
          >
        </vs-col>
      </vs-row>
    </div>
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
	props: {
		accountid: {
			type: String / Number,
			default: 0
		},
		transactionid: {
			type: String / Number,
			default: 0
		},
		page: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			record_not_found: false,
			record_found: false,
			transactions: {},
			//receipt transactions list starts here
			currentPage: 1,
			result_per_page: 1,
			loading: true,
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
			records: []
		}
	},
	computed: {},
	mounted () {
		if (this.page !== 0) {
			this.currentPage = this.page
		} else {
			this.getData()
		}
	},
	watch: {
		currentPage () {
			this.getData()
		}
	},
	methods: {
		accountNumbers (account) {
			let acnts = ''
			if (this.hasdata(account.acc_num1) && this.hasdata(account.acc_num2)) {
				acnts += `${account.acc_num1  } | ${  account.acc_num1}`
			} else if (this.hasdata(account.acc_num1)) {
				acnts += account.acc_num1
			} else if (this.hasdata(account.acc_num2)) {
				acnts += account.acc_num2
			}
			if (account.status == 'Inactive') {
				acnts += ' - <span class="text-danger">Inactive</span> '
			} else {
				acnts += ' - <span class="text-primary">Active</span> '
			}
			return acnts
		},
		getData () {
			this.showLoading('getting receipt infomation')
			this.post('/statements/statement_account', {
				result_per_page: this.result_per_page,
				page: this.currentPage,
				id: this.transactionid,
				accountid: this.accountid
			})
				.then((response) => {
					console.log(response.data)
					this.closeLoading()
					this.pagination = response.data.pagination
					if (response.data.success == true) {
						this.transactions = response.data.transactions[0]
						if (this.transactions.id !== this.transactionid) {
							this.$router.replace({
								name: 'rview-bank-account-trans',
								params: {
									accountid: this.accountid,
									transactionid: this.transactions.id,
									page: this.pagination.page
								}
							})
						}
						this.record_found = true
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
		}
	}
}
</script>

<style lang="scss">
</style>