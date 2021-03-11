
<template>
  <div id="file-download-revenue-org-view">
    <div id="file-download-revenue-data">
      <vx-card title="Running Jobs">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <vs-button
              type="relief"
              color="warning"
              icon-pack="feather"
              icon="icon-file-text"
              v-if="deletebutton && AppActiveUser.access_level === 'admin'"
              @click="exportWarn()"
              >Export</vs-button
            >
            <vs-spacer />
            <div class="w-1/5 mx-1 px-2">
              <span>Search Accounts</span>
              <vs-input
                id="text"
                type="text"
                class="mx-1"
                v-model="search"
                placeholder="Search accounts"
              />
            </div>
            <div class="w-1/6 ml-2 px-2">
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
          </header>

          <div class="con-tablex vs-table--content">
            <div class="vs-con-tbody vs-table--tbody">
              <table class="vs-table vs-table--tbody-table">
                <thead class="vs-table--thead">
                  <tr>
                    <th scope="col" colspan="12">
                      <small><b>Runing Jobs</b></small>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in sortedRecords"
                    :key="index"
                    class="tr-values vs-table--tr tr-table-state-null selected"
                  >
                    <td>
                      <small>{{ record.jobid }}</small>
                    </td>
                    <td>
                      <small
                        >{{ record.proccessing_account }} - Of -
                        {{ record.total_account }}</small
                      >
                    </td>
                    <td>
                      <span
                        v-if="record.description"
                        :class="`${color(record.description)}`"
                        >{{ record.description | title }}</span
                      >
                    </td>
                    <td>
                      <vs-chip
                        v-if="record.processing"
                        :color="status(record.processing)"
                      >
                        {{ record.processing | title }}
                      </vs-chip>
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
  </div>
</template>

<script>
import mStorage from '@/store/storage.js'

export default {
	beforeRouteEnter (to, from, next) {
		next((vm) => {
			if (
				to.meta &&
        to.meta.identity &&
        !vm.AppActiveUser.pages.includes(to.meta.identity)
			) {
				if (!vm.allowed_pages.includes(to.meta.identity)) {
					vm.pushReplacement(vm.AppActiveUser.baseUrl)
				}
			}
		})
	},
	beforeRouteLeave (to, from, next) {
		if (this.interval) {
			clearInterval(this.interval)
			this.interval = null
		}
		next()
	},
	data () {
		return {
			//receipt data list starts here
			pkey: 'revenue-filedownload-list-key',
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
			interval: null,
			search_timer: null
		}
	},
	computed: {
		selectAll: {
			get () {
				return this.records
					? this.selectedRecords.length == this.records.length
					: false
			},
			set (value) {
				const selected = []

				if (value) {
					this.records.forEach(function (record) {
						selected.push(record.account_id_from)
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
		this.currentPage = Number(mStorage.get(`${this.pkey}page`)) || 1
		this.getData()
		this.startInterval()
	},
	watch: {
		currentPage () {
			mStorage.set(`${this.pkey}page`, this.currentPage)
			this.getData()
		},
		result_per_page () {
			this.getData(true)
		},
		search (newVal, oldVal) {
			this.startSearch(newVal, oldVal)
		},
		pagination () {
			this.numbering = this.pagination.start
		}
	},
	methods: {
		status (status) {
			if (status == 'processing') {
				return 'warning'
			}
			if (status == 'pending') {
				return 'dark'
			}
			return 'success'
		},
		color (text) {
			if (text.toLowerCase().includes('error')) {
				return 'text-danger'
			}
			return ''
		},
		startInterval (newVal, oldVal) {
			if (this.interval) {
				clearInterval(this.interval)
				this.interval = null
			}
			const vm = this
			this.interval = setInterval(() => {
				vm.getData()
			}, 700)
		},
		startSearch (newVal, oldVal) {
			if (this.search_timer) {
				clearTimeout(this.search_timer)
			}
			const vm = this
			this.search_timer = setTimeout(function () {
				vm.getData()
			}, 800)
		},
		//reconciliation starts here
		getData (scroll) {
			const user = this.AppActiveUser
			this.loading = true
			this.post('/runningjobs/', {
				page: this.currentPage,
				result_per_page: this.result_per_page,
				search: this.search
			})
				.then((response) => {
					this.records = []
					this.loading = false
					this.message = response.data.message
					this.pagination = response.data.pagination
					if (response.data.success) {
						this.records = response.data.runingjobs
					}
				})
				.catch((error) => {
					this.hasData = false
					this.loading = false
					console.log(error)
				})
		}
	}
}
</script>