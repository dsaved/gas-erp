
<template>
  <div id="omc-org-view">
    <div id="omc-data">
      <vx-card :title="accountDetails.name">
        <p>
          <span v-html="accountNumbers(accountDetails)"></span>
        </p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <vs-button
              type="relief"
              color="warning"
              v-if="deletebutton && canDelete()"
              @click="hideWarn()"
              >Hide Statement(s)</vs-button
            >
            <vs-spacer />
            <div class="w-1/6 px-2">
              <span>ORG Status</span>
              <ajax-select
                placeholder="Select Category"
                :options="[
                  { value: 'all', label: 'All' },
                  { value: 'pending', label: 'Pending' },
                  { value: 'reviewed', label: 'Reviewed' },
                ]"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="org_status"
                v-on:update:data="org_status = $event"
              />
            </div>
            <div class="w-1/6 px-2">
              <span>BOG Status</span>
              <ajax-select
                placeholder="Select Category"
                :options="[
                  { value: 'all', label: 'All' },
                  { value: 'pending', label: 'Pending' },
                  { value: 'reviewed', label: 'Reviewed' },
                ]"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="bog_status"
                v-on:update:data="bog_status = $event"
              />
            </div>
            <div class="w-1/5 mx-1 px-2">
              <span>Search unauthorized</span>
              <vs-input
                id="text"
                type="text"
                class="mx-1"
                v-model="search"
                placeholder="Search unauthorized"
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
                    <th scope="col" class="td-check">
                      <vs-checkbox v-model="selectAll">#</vs-checkbox>
                    </th>
                    <th scope="col">Reference</th>
                    <th scope="col">Offset Account Number</th>
                    <th scope="col">Date</th>
                    <th scope="col" class="text-right">Debit Amount</th>
                    <th scope="col">ORG Reviewed</th>
                    <th scope="col">BOG Reviewed</th>
                    <th scope="col">Interval</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in sortedRecords"
                    :key="index"
                    v-on:click="
                      linkto(
                        '/revenue/unauthorized/org/' +
                          record.account_from +
                          '/list/' +
                          record.offset_account +
                          '/v/' +
                          number(index) +
                          '/' +
                          record.statement_id
                      )
                    "
                    class="tr-values vs-table--tr tr-table-state-null selected"
                  >
                    <td scope="row" @click.stop="">
                      <vs-checkbox
                        v-model="selectedRecords"
                        :vs-value="record.statement_id"
                        >{{ number(index) }}</vs-checkbox
                      >
                    </td>
                    <td>
                      <span>
                        <b>{{ record.reference }}</b></span
                      ><br />
                    </td>
                    <td>{{ record.offset_account }}</td>
                    <td>{{ record.post_date }}</td>
                    <td class="text-right">{{ record.amount }}</td>
                    <td>
                      <vs-chip
                        v-if="record.org_status"
                        transparent
                        :color="status(record.org_status)"
                      >
                        <vs-avatar :text="acronum(record.org_status)" />
                        {{ record.org_status | title }}
                      </vs-chip>
                    </td>
                    <td>
                      <vs-chip
                        v-if="record.bog_status"
                        transparent
                        :color="status(record.bog_status)"
                      >
                        <vs-avatar :text="acronum(record.bog_status)" />
                        {{ record.bog_status | title }}
                      </vs-chip>
                    </td>
                    <td>{{ record.intval }}</td>
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
// Import Swal
import Swal from "sweetalert2";
import mStorage from "../../../../../store/storage";

export default {
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (
        to.meta &&
        to.meta.identity &&
        !vm.AppActiveUser.pages.includes(to.meta.identity)
      ) {
        vm.pushReplacement(vm.AppActiveUser.baseUrl);
      }
    });
  },
  props: {
    accountid: {
      type: String / Number,
      require: true,
    },
    offset_account: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      //receipt data list starts here
      pkey: "org-unauthorized-list-key",
      message: "",
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
        hasPrevious: false,
      },
      selectedRecords: [],
      search: "",
      records: [],
      accountDetails: { name: "", status: "", acc_num1: "", acc_num2: "" },
      search_timer: null,
      org_status: { value: "all", label: "All" },
      bog_status: { value: "all", label: "All" },
      category_group: [],
      banks: [],
      categories: [],
      filter_category: { value: "all", label: "All" },
    };
  },
  computed: {
    selectAll: {
      get: function () {
        return this.records
          ? this.selectedRecords.length == this.records.length
          : false;
      },
      set: function (value) {
        var selected = [];

        if (value) {
          this.records.forEach(function (record) {
            selected.push(record.statement_id);
          });
        }
        this.selectedRecords = selected;
      },
    },
    sortedRecords: function () {
      try {
        return this.filterObj(this.records, this.search).sort((a, b) => {
          var modifier = 1;
          if (this.currentSortDir === "desc") modifier = -1;
          if (a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
          if (a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
          return 0;
        });
      } catch (error) {
        console.warn(error);
      }
    },
  },
  mounted: function () {
    this.currentPage =
      Number(
        mStorage.get(`${this.pkey}page${this.accountid}l${this.offset_account}`)
      ) || 1;
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(
        `${this.pkey}page${this.accountid}l${this.offset_account}`,
        this.currentPage
      );
      this.getData();
    },
    org_status: function () {
      this.getData(true);
    },
    bog_status: function () {
      this.getData(true);
    },
    result_per_page: function () {
      this.getData(true);
    },
    search: function (newVal, oldVal) {
      this.startSearch(newVal, oldVal);
    },
    pagination: function () {
      this.numbering = this.pagination.start;
    },
    selectedRecords: function (newVal, oldVal) {
      if (this.selectedRecords.length > 0) {
        this.deletebutton = true;
      } else {
        this.deletebutton = false;
      }
    },
  },
  methods: {
    status(status) {
      let color = "";
      if (status === "pending") {
        color = "warning";
      } else if (status === "reviewed") {
        color = "success";
      }
      return color;
    },
    accountNumbers: function (account) {
      var acnts = ``;
      if (this.hasdata(account.acc_num1) && this.hasdata(account.acc_num2)) {
        acnts += account.acc_num1 + ` | ` + account.acc_num1;
      } else if (this.hasdata(account.acc_num1)) {
        acnts += account.acc_num1;
      } else if (this.hasdata(account.acc_num2)) {
        acnts += account.acc_num2;
      }
      if (account.status == "Inactive") {
        acnts += ` - <span class="text-danger">Inactive</span> `;
      } else {
        acnts += ` - <span class="text-primary">Active</span> `;
      }
      return acnts;
    },
    number: function (num) {
      return this.numbering + num;
    },
    startSearch: function (newVal, oldVal) {
      if (this.search_timer) {
        clearTimeout(this.search_timer);
      }
      const vm = this;
      this.search_timer = setTimeout(function () {
        vm.getData();
      }, 800);
    },
    getData: function (scroll) {
      var user = this.AppActiveUser;
      var isbog = user.types[1] == "organization" ? "true" : "false";
      this.loading = true;
      this.post("/unauthorized/transactions/", {
        page: this.currentPage,
        result_per_page: this.result_per_page,
        org_status: this.org_status.value,
        bog_status: this.bog_status.value,
        account_id: this.accountid,
        access_type: user.access_level,
        offset_account: this.offset_account,
        user_id: user.id,
        search: this.search,
        isbog: isbog,
      })
        .then((response) => {
          console.log(response.data);
          this.records = [];
          this.loading = false;
          this.message = response.data.message;
          this.pagination = response.data.pagination;
          if (response.data.success) {
            this.records = response.data.unauthorized;
            var accountDetails = response.data.main_account;
            this.accountDetails = {
              name: accountDetails.name,
              status: accountDetails.status,
              acc_num1: accountDetails.acc_num1,
              acc_num2: accountDetails.acc_num2,
            };
          }
        })
        .catch((error) => {
          this.hasData = false;
          this.loading = false;
          console.log(error);
        });
    },
    hideWarn: function (account_id, offset_account) {
      Swal.fire({
        title: "Are you sure?",
        text: "This Infraction won't be available to organizations!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, hide it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.hide(this.selectedRecords);
        }
      });
    },
    hide: function (ids) {
      this.showLoading("Hidding unauthorized Infraction(s), please wait");
      this.post("/unauthorized/hide", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire(
              "Done!",
              "The unauthorized Inraction  has been hidden",
              "success"
            );
            this.selectedRecords = [];
            this.getData();
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          Swal.fire("Failed!", error.message, "error");
        });
    },
  },
};
</script>