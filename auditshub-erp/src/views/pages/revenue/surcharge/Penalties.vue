
<template>
  <div id="omc-org-view">
    <div id="omc-data">
      <vx-card title="Bank Accounts">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <vs-button
              type="relief"
              color="primary"
              icon-pack="feather"
              icon="icon-file-minus"
              @click="compileWarn()"
              >Compile Penalties</vs-button
            >
            <vs-spacer />
            <vs-button
              type="relief"
              color="warning"
              icon-pack="feather"
              icon="icon-file-text"
              v-if="AppActiveUser.access_level === 'admin'"
              @click="exportWarn()"
              >Export</vs-button
            >
          </header>
          <div class="w-full flex mb-4">
            <div class="w-1/4 px-2">
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
            <div class="w-1/4 px-2">
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
            <div class="w-1/4 px-2">
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
            <div class="w-1/4 px-2">
              <span>Search accounts</span>
              <vs-input
                id="text"
                type="text"
                class="w-full mx-1"
                v-model="search"
                placeholder="Search ..."
              />
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
                    <th scope="col">Surcharges</th>
                    <th scope="col" class="text-right">Total Penalty</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in sortedRecords"
                    :key="index"
                    v-on:click="
                      linkto('/revenue/penalty/' + record.account_id + '/view')
                    "
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
                    </td>
                    <td>
                      {{ record.owner_name }}
                    </td>
                    <td>
                      {{ record.bank_name }}
                    </td>
                    <td>
                      {{ record.surcharge }}
                    </td>
                    <td class="text-right">
                      {{ record.penalty }}
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
      title="Compilation In Progress"
      :active.sync="popupCompileActive"
    >
      <p>
        Compiling all account penalties.
      </p>
      <p>
        <span
          v-for="(desc, index) in compileDesc"
          :key="index"
          v-html="formatDesc(desc)"
          ><br
        /></span>
      </p>
      <p v-if="hasdata(compilationStatus)" class="text-info loadingDot">
        {{ compilationStatus }}
      </p>

      <p>
        <span class="text-secondary"
          ><b>{{ compilationDetails }}</b></span
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
            v-if="compilationReloadButton"
            color="primary"
            icon-pack="feather"
            icon="icon-refresh-cw"
            @click="startCheckReconcilationStatus()"
            class="mx-1"
            >reload</vs-button
          >

          <vs-button
            v-if="canCloseCompileModal"
            color="danger"
            icon-pack="feather"
            icon="icon-x"
            @click="clearCompilationLog()"
            class="mx-1"
            >Close</vs-button
          >
        </vs-col>
      </vs-row>
    </vs-popup>

    <vs-popup
      background-color="rgba(200,200,200,.8)"
      persistent
      :button-close-hidden="true"
      title="File Export In Progress"
      :active.sync="popupActive"
    >
      <p>
        <span
          v-for="(desc, index) in exportDesc"
          :key="index"
          v-html="formatDesc(desc)"
          ><br
        /></span>
      </p>
      <p v-if="hasdata(exportStatus)" class="text-info loadingDot">
        {{ exportStatus }}
      </p>

      <p>
        <span class="text-secondary"
          ><b>{{ exportDetails }}</b></span
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
            @click="statusCheckFileExport()"
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
	data () {
		return {
			// export data starts here
			popupActive: false,
			canCloseModal: false,
			reloadButton: false,
			statuscheck: null,
			jobid: null,
			exportDesc: [],
			exportStatus: '',
			exportDetails: '',
			//compilation datat section
			popupCompileActive: false,
			canCloseCompileModal: false,
			compilationReloadButton: false,
			compileststuscheck: null,
			compileJobid: null,
			errorStr: ['unknown jobid', 'error'],
			compileDesc: [],
			compilationStatus: '',
			compilationDetails: '',
			//receipt data list starts here
			pkey: 'rev-account-bank-list-key',
			message: '',
			numbering: 0,
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
			search_timer: null,
			bank_type: { value: 'all', label: 'All' },
			bank_name: { value: 'all', label: 'All' },
			category_group: [],
			banks: [],
			categories: [],
			bank_accounts: {}
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
		this.currentPage = Number(mStorage.get(`${this.pkey}page`)) || 1
		this.getData()
	},
	watch: {
		currentPage () {
			mStorage.set(`${this.pkey}page`, this.currentPage)
			this.getData()
		},
		result_per_page () {
			this.getData(true)
		},
		bank_type () {
			this.getData(true)
		},
		bank_name () {
			this.getData(true)
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
		number (num) {
			return this.numbering + num
		},
		startSearch () {
			if (this.search_timer) {
				clearTimeout(this.search_timer)
			}
			const vm = this
			this.search_timer = setTimeout(function () {
				vm.getData()
			}, 800)
		},
		getData () {
			this.loading = true
			this.post('/surcharge/accounts', {
				page: this.currentPage,
				result_per_page: this.result_per_page,
				bank_type: this.bank_type.value,
				bank_name: this.bank_name.label,
				search: this.search
			})
				.then((response) => {
					console.log(response.data)
					this.records = []
					this.loading = false
					this.message = response.data.message
					this.pagination = response.data.pagination
					this.bank_accounts = response.data.bank_accounts
					if (response.data.success) {
						this.records = response.data.surcharge
					}
				})
				.catch((error) => {
					this.hasData = false
					this.loading = false
					console.log(error)
				})
		},
		//compilation  starts here
		compileWarn () {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Penalties will be recompiled if compiled before!',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, compile!'
			}).then((result) => {
				if (result.isConfirmed) {
					this.chooseAccount()
				}
			})
		},
		async chooseAccount () {
			const { value: account, isDismissed: isDismissed } = await Swal.fire({
				title: 'Select Account',
				text: 'Select the account to compute penalty',
				icon: 'question',
				input: 'select',
				inputOptions: this.bank_accounts,
				showCancelButton: true,
				confirmButtonColor: '#0d6723',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, continue!',
				inputPlaceholder: 'Select Account',
				inputValidator: () => {}
			})
			if (!isDismissed && account) {
				this.compile(account)
			}
		},
		compile (account) {
			this.showLoading('Sending compile request, hang on a bit')
			this.post('/surcharge/compile', {
				user_id: this.AppActiveUser.id,
				account_id: account
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success === true) {
						this.popupCompileActive = true
						this.compileDescription(response.data.message)
						this.compilationStatus = 'Initializing'
						this.compileJobid = response.data.jobid
						this.initCompileStatusCheck()
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		},
		initCompileStatusCheck () {
			this.canCloseCompileModal = false
			this.compilationReloadButton = false
			const vm = this
			this.compileststuscheck = setInterval(function () {
				vm.compileStatusCheck()
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
		compileDescription (data) {
			if (!this.compileDesc.includes(data)) {
				this.compileDesc.push(data)
			}
		},
		clearCompilationLog () {
			this.popupCompileActive = false
			this.canCloseCompileModal = false
			this.compilationReloadButton = false
			this.compileJobid = null
			this.compileDesc = []
			this.compilationStatus = ''
			if (this.compileststuscheck) {
				clearInterval(this.compileststuscheck)
			}
		},
		compileStatusCheck () {
			this.post('/surcharge/compilation_status', {
				jobid: this.compileJobid
			})
				.then((response) => {
					const data = response.data
					if (data.success) {
						const status = data.status
						this.compileDescription(status.description)
						this.compilationStatus = status.status
						this.compilationDetails = status.details
						if (status.status.toLowerCase() === 'completed') {
							this.getData()
							clearInterval(this.compileststuscheck)
							this.compilationStatus = ''
							this.canCloseCompileModal = true
						}
						if (status.status.toLowerCase().includes('error')) {
							clearInterval(this.compileststuscheck)
							this.compilationStatus = ''
							this.canCloseCompileModal = true
						}
					} else {
						this.compileDescription(response.data.message)
						this.compilationStatus = ''
						clearInterval(this.compileststuscheck)
						this.canCloseCompileModal = true
					}
				})
				.catch((error) => {
					this.compileDescription('a network error has occured')
					clearInterval(this.compileststuscheck)
					this.compilationStatus = ''
					this.canCloseCompileModal = true
					this.compilationReloadButton = true
					console.log(error)
				})
		},
		//reconciliation starts here
		clearLog () {
			this.popupActive = false
			this.canCloseModal = false
			this.reloadButton = false
			this.jobid = null
			this.exportDesc = []
			this.exportStatus = ''
			if (this.statuscheck) {
				clearInterval(this.statuscheck)
			}
		},
		statusCheckFileExport () {
			this.canCloseModal = false
			this.reloadButton = false
			const vm = this
			this.statuscheck = setInterval(function () {
				vm.checkStatus()
			}, 1200)
		},
		pushDescription (data) {
			if (!this.exportDesc.includes(data)) {
				this.exportDesc.push(data)
			}
		},
		async exportWarn () {
			const { value: filename } = await Swal.fire({
				title: 'Export Penalties',
				text: 'You are about to export all penalties',
				icon: 'question',
				input: 'text',
				showCancelButton: true,
				confirmButtonColor: '#0d6723',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, continue!',
				inputPlaceholder: 'Save file as?',
				inputValue: `Penalties-${new Date().getTime()}`,
				inputValidator: (value) => {
					if (!value) {
						return 'You need to write file name!'
					}
				}
			})

			if (filename) {
				this.export(filename)
			}
		},
		export (filename) {
			this.showLoading('Sending Request For File Export')
			this.post('/surcharge/start_export', {
				filename
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success === true) {
						this.selectedRecords = []
						this.popupActive = true
						this.pushDescription(response.data.message)
						this.exportStatus = 'Initializing'
						this.jobid = response.data.jobid
						this.statusCheckFileExport()
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		},
		checkStatus () {
			this.post('/surcharge/file_export_status', {
				jobid: this.jobid
			})
				.then((response) => {
					const data = response.data
					if (data.success) {
						const status = data.status
						this.pushDescription(status.description)
						this.exportStatus = status.status
						this.exportDetails = status.details
						if (status.status.toLowerCase() === 'completed') {
							clearInterval(this.statuscheck)
							this.exportStatus = ''
							this.canCloseModal = true
						}
						if (status.status.toLowerCase().includes('error')) {
							clearInterval(this.statuscheck)
							this.exportStatus = ''
							this.canCloseModal = true
						}
					} else {
						this.pushDescription(response.data.message)
						this.exportStatus = ''
						clearInterval(this.statuscheck)
						this.canCloseModal = true
					}
				})
				.catch((error) => {
					this.pushDescription('a network error has occured')
					clearInterval(this.statuscheck)
					this.exportStatus = ''
					this.canCloseModal = true
					this.reloadButton = true
					console.log(error)
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