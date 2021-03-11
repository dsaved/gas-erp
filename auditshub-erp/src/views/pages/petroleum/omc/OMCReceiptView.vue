
<template>
  <div id="receipts-org-view">
    <vs-alert
      color="danger"
      title="Receipt Not Found"
      :active.sync="user_not_found"
    >
      <span> Receipt with id: {{ receiptid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="'/petroleum/omc/' + omcid + '/view'"
          class="text-inherit underline"
          >Receipts</router-link
        >
      </span>
    </vs-alert>

    <div id="receipts-data" v-if="user_found">
      <vx-card class="mb-base">
        <div class="invoice-box">
          <table cellpadding="0" cellspacing="0">
            <tr class="top">
              <td colspan="2">
                <table>
                  <tr>
                    <td class="title">
                      <h1>
                        <b>{{ receipts.bank }}</b>
                      </h1>
                    </td>

                    <td>
                      Receipt #: {{ receipts.receipt_number }}<br />
                      Declaration #: {{ receipts.declaration_number }}<br />
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr class="information">
              <td colspan="2">
                <table>
                  <tr>
                    <td>
                      This receipt was<br />
                      issued by <br />
                      {{ receipts.bank }}
                    </td>

                    <td>
                      {{ receipts.name }}<br />
                      {{ receipts.email }}<br />
                      {{ receipts.phone }}<br />
                      {{ receipts.location }} {{ receipts.region }}
                      {{ receipts.district }}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr class="heading">
              <td>Payment Method</td>

              <td>Date</td>
            </tr>

            <tr class="details">
              <td>{{ receipts.mode_of_payment }}</td>

              <td>{{ receipts.date }}</td>
            </tr>
            <tr class="total">
              <td></td>

              <td>Total Amount: GHS {{ receipts.amount }}</td>
            </tr>
          </table>
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
		omcid: {
			type: String / Number,
			default: 0
		},
		receiptid: {
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
			user_not_found: false,
			user_found: false,
			receipts: {},
			//receipt data list starts here
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
		getData () {
			this.showLoading('getting receipt infomation')
			this.post('/receipts/receipt_omc', {
				result_per_page: this.result_per_page,
				page: this.currentPage,
				id: this.receiptid,
				omcid: this.omcid
			})
				.then((response) => {
					console.log(response.data)
					this.closeLoading()
					this.pagination = response.data.pagination
					if (response.data.success == true) {
						this.receipts = response.data.receipts[0]
						if (this.receipts.id !== this.receiptid) {
							this.$router.replace({
								name: 'omc-view-receipt',
								params: {
									omcid: this.omcid,
									receiptid: this.receipts.id,
									page: this.pagination.page
								}
							})
						}
						this.user_found = true
					} else {
						this.user_not_found = true
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
					this.user_not_found = true
				})
		}
	}
}
</script>

<style lang="scss">
.invoice-box table {
  width: 100%;
  line-height: inherit;
  text-align: left;
}

.invoice-box table td {
  padding: 5px;
  vertical-align: top;
}

.invoice-box table tr td:nth-child(2) {
  text-align: right;
}

.invoice-box table tr.top table td {
  padding-bottom: 20px;
}

.invoice-box table tr.top table td.title {
  font-size: 45px;
  line-height: 45px;
  color: #333;
}

.invoice-box table tr.information table td {
  padding-bottom: 40px;
}

.invoice-box table tr.heading td {
  background: #eee;
  border-bottom: 1px solid #ddd;
  font-weight: bold;
}

.invoice-box table tr.details td {
  padding-bottom: 20px;
}

.invoice-box table tr.item td {
  border-bottom: 1px solid #eee;
}

.invoice-box table tr.item.last td {
  border-bottom: none;
}

.invoice-box table tr.total td:nth-child(2) {
  border-top: 2px solid #eee;
  font-weight: bold;
}

@media only screen and (max-width: 600px) {
  .invoice-box table tr.top table td {
    width: 100%;
    display: block;
    text-align: center;
  }

  .invoice-box table tr.information table td {
    width: 100%;
    display: block;
    text-align: center;
  }
}

/** RTL **/
.rtl {
  direction: rtl;
  font-family: Tahoma, "Helvetica Neue", "Helvetica", Helvetica, Arial,
    sans-serif;
}

.rtl table {
  text-align: right;
}

.rtl table tr td:nth-child(2) {
  text-align: left;
}
</style>