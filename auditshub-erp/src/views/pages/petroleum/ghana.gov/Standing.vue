<template>
  <div>
    <vx-card title="NPA ANALYTICS">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <div class="w-full flex mb-4">
          <div class="w-1/4 px-2">
            <span>Date Range</span>
            <div class="w-full">
              <date-range-picker
                :opens="'right'"
                :closeOnEsc="true"
                :locale-data="{ firstDay: 1, format: 'dd-m-yyyy' }"
                :singleDatePicker="false"
                :linked-calendars="false"
                :showWeekNumbers="false"
                :showDropdowns="true"
                :autoApply="true"
                :alwaysShowCalendars="true"
                :appendToBody="true"
                v-model="date_range"
                class="w-full"
              >
                <template
                  v-slot:input="picker"
                >
                  {{ picker.startDate | date(true) }} -
                  {{ picker.endDate | date(true) }}
                </template>
              </date-range-picker>
            </div>
          </div>
          <div class="w-1/4 px-2">
            <span>OMC</span>
            <ajax-select
              placeholder="Select Condition"
              url="/omc/options_list"
              :include="['All']"
              :clearable="false"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="omc"
              v-on:update:data="omc = $event"
            />
          </div>
          <div class="w-1/5 px-2">
            <span>Status</span>
            <ajax-select
              placeholder="Select Status"
              :options="['All', 'Flagged', 'Not Flagged']"
              :clearable="false"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="status"
              v-on:update:data="status = $event"
            />
          </div>
          <div class="w-1/5 px-2">
            <span>Positives/Nagatives</span>
            <ajax-select
              placeholder="Select Status"
              :options="['All', 'Nagatives', 'Positives']"
              :clearable="false"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="nagatives"
              v-on:update:data="nagatives = $event"
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
        </div>
        <header class="flex mb-4">
          <div
            class="md:w-1/5 sm:w-full mx-1"
            v-if="AppActiveUser.access_level === 'admin'"
          >
            <vs-button
              color="dark"
              icon-pack="feather"
              v-if="canAdd()"
              class="w-full"
              @click="filterData()"
              icon="icon-upload-cloud"
              >Filter</vs-button
            >
          </div>
          <div class="md:w-1/5 sm:w-full mx-1">
            <vs-button
              color="danger"
              class="m-1 w-full"
              icon-pack="feather"
              @click="resetFilter()"
              icon="icon-refresh-cw"
              >Reset</vs-button
            >
          </div>
        </header>
      </div>
    </vx-card>

    <vx-card title="REPORT VIEW" class="mt-5">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <div class="con-tablex vs-table--content">
          <div class="vs-con-tbody vs-table--tbody">
            <table class="vs-table vs-table--tbody-table">
              <thead class="vs-table--thead">
                <tr>
                  <th scope="col" class="td-check">
                    <vs-checkbox v-model="selectAll">#</vs-checkbox>
                  </th>
                  <th scope="col">Date</th>
                  <th scope="col">OMC</th>
                  <th scope="col" class="text-right">Recipt Amount</th>
                  <th scope="col" class="text-right">ICUMS Amount</th>
                  <th scope="col" class="text-right">Amount Difference</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(record, index) in sortedRecords"
                  :key="index"
                  :class="['tr-values vs-table--tr tr-table-state-null selected',{'text-danger':record.flagged}]"
                >
                  <td scope="row" @click.stop="">
                    <vs-checkbox
                      v-model="selectedRecords"
                      :vs-value="record.id"
                      >{{ number(index) }}</vs-checkbox
                    >
                  </td>
                  <td>
                    {{ record.date }}
                  </td>
                  <td>
                      {{ record.omc }}
                  </td>
                  <td class="text-right">
                    {{ record.amount }}
                  </td>
                  <td class="text-right">
                    {{ record.dcl_amount }}
                  </td>
                  <td class="text-right">
                    {{ record.difference_amount }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <h3 class="text-center" v-show="message">{{ message }}</h3>
          <!---->
          <div class="con-pagination-table vs-table--pagination">
            <div
              class="vs-row md:flex w-full"
              style="justify-content: space-between"
            >
              <vs-spacer />
              <div
                class="vs-col vs-pagination--mb vs-xs-12 vs-sm-12 vs-lg-12 md:flex"
                style="
                  justify-content: flex-end;
                  align-items: center;
                  margin-left: 0%;
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
                    :max="6"
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
import Swal from "sweetalert2";
import mStorage from "@/store/storage.js";
import DateRangePicker from "vue2-daterange-picker";
import "vue2-daterange-picker/dist/vue2-daterange-picker.css";

export default {
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (
        to.meta &&
        to.meta.identity &&
        !vm.AppActiveUser.pages.includes(to.meta.identity)
      ) {
        if (to.meta.identities) {
          const found = to.meta.identities.some((r) =>
            vm.AppActiveUser.pages.includes(r)
          );
          if (!found) {
            vm.pushReplacement(vm.AppActiveUser.baseUrl);
          }
        } else {
          vm.pushReplacement(vm.AppActiveUser.baseUrl);
        }
      }
    });
  },
  components: {
    DateRangePicker,
  },
  data() {
    return {
      data: {
        type: Object,
        default: function () {
          return {};
        },
      },
      //file import section
      popupActive: false,
      statuscheck: null,
      errorStr: ["unknown jobid", "error"],
      importDesc: [],
      importDetails: "",
      importStatus: "",
      //preorder data list starts here
      pkey: "input-petroleum-analytics-list",
      message: "",
      numbering: 0,
      currentPage: 1,
      result_per_page: 20,
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
      product_type: "All",
      bdc: "All",
      depot: "All",
      omc: "All",
      status: "All",
      nagatives: "All",
      group_by: ["BDC"],
      date_range: {
        startDate: null,
        endDate: null,
      },
      records: [],
      search_timer: null,
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
        return this.filterObj(this.records, "").sort((a, b) => {
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
  mounted: function () {},
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page`, this.currentPage);
      this.filterData();
    },
    pagination: function () {
      this.numbering = this.pagination.start;
    },
  },
  methods: {
    resetFilter: function () {
      this.product_type = "All";
      this.bdc = "All";
      this.depot = "All";
      this.omc = "All";
      this.group_by = ["BDC"];
      this.status = "All";
      this.nagatives = "All";
      this.date_range = {
        startDate: null,
        endDate: null,
      };
    },
    number: function (num) {
      return this.numbering + num;
    },
    filterData() {
      this.showLoading("Getting results...");
      this.post("/ghana_gov/difference", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        product_type: this.product_type,
        bdc: this.bdc,
        group_by: this.group_by,
        depot: this.depot,
        omc: this.omc,
        status: this.status,
        nagatives: this.nagatives,
        date_range: this.date_range,
      })
        .then((response) => {
          this.closeLoading();
          this.pagination = response.data.pagination;
          if (response.data.success == true) {
            this.message = "";
            this.records = response.data.reports;
          } else {
            this.assets_not_found = true;
            this.message = response.data.message;
            this.records = [];
            this.$vs.notify({
              title: "Error!!!",
              text: `${response.data.message}`,
              sticky: true,
              border: "danger",
              color: "dark",
              duration: null,
              position: "bottom-left",
            });
          }
        })
        .catch((error) => {
          this.closeLoading();
          this.$vs.notify({
            title: "Error!!!",
            text: `${error.message}`,
            sticky: true,
            border: "danger",
            color: "dark",
            duration: null,
            position: "bottom-left",
          });
        });
    },
  },
};
</script>

<style lang="css" scoped>
.form-control.reportrange-text {
  padding: 8px 10px!important;
  border: 1px solid rgba(60, 60, 60, 0.26)!important;
  border-radius: 4px!important;
}
</style>