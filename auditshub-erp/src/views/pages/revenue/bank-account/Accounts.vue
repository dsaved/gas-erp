
<template>
  <div id="omc-org-view">
    <div id="omc-data">
      <vx-card title="Bank Accounts">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <vs-button
              type="relief"
              color="danger"
              icon-pack="feather"
              icon="icon-file-minus"
              v-if="deletebutton && canDelete()"
              @click="emptyWarn()"
              >Empty Accounts(s)</vs-button
            >
            <vs-spacer />
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
                    v-on:click="
                      linkto('/revenue/accounts/' + record.id + '/view')
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
  </div>
</template>

<script>
// Import Swal
import Swal from "sweetalert2";
import mStorage from "@/store/storage.js";

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
  data() {
    return {
      //receipt data list starts here
      pkey: "rev-account-bank-list-key",
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
      search_timer: null,
      bank_type: { value: "all", label: "All" },
      bank_name: { value: "all", label: "All" },
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
            selected.push(record.id);
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
    photo() {
      return require("@/assets/images/portrait/small/default.png");
    },
  },
  mounted: function () {
    this.currentPage = Number(mStorage.get(`${this.pkey}page`)) || 1;
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page`, this.currentPage);
      this.getData();
    },
    result_per_page: function () {
      this.getData(true);
    },
    bank_type: function (newVal, oldVal) {
      this.getData(true);
    },
    bank_name: function (newVal, oldVal) {
      this.getData(true);
    },
    filter_category: function (newVal, oldVal) {
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
    //reconciliation starts here
    getData: function (scroll) {
      this.loading = true;
      this.post("/bankaccounts/", {
        bid: 0,
        access_type: this.AppActiveUser.access_level,
        user_id: this.AppActiveUser.id,
        page: this.currentPage,
        result_per_page: this.result_per_page,
        bank_type: this.bank_type.value,
        bank_name: this.bank_name.label,
        filter_category: this.filter_category.value,
        search: this.search,
      })
        .then((response) => {
          console.log(response.data);
          this.records = [];
          this.loading = false;
          this.message = response.data.message;
          this.pagination = response.data.pagination;
          if (response.data.success) {
            this.records = response.data.bankaccounts;
          }
        })
        .catch((error) => {
          this.hasData = false;
          this.loading = false;
          console.log(error);
        });
    },
    emptyWarn: function () {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, empty it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.empty(this.selectedRecords);
        }
      });
    },
    empty: function (ids) {
      this.showLoading("Removing account(s) statements, please wait");
      this.post("/bankaccounts/empty_statement", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire(
              "Emptied!",
              "The account(s) has been emptied.",
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

<style lang="scss">
.loadingDot:after {
  content: " .";
  font-size: 50px;
  line-height: 0;
  animation: dots 1s steps(5, end) infinite;
}
</style>