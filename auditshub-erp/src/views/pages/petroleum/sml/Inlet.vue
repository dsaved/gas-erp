<template>
  <div>
    <vx-card title="INLET">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <div class="w-full flex mb-4">
          <div class="w-1/3 px-2">
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
                <template v-slot:input="picker">
                  {{ picker.startDate | date(true) }} -
                  {{ picker.endDate | date(true) }}
                </template>
              </date-range-picker>
            </div>
          </div>
          <div class="w-1/4 px-2">
            <span>Product Type</span>
            <ajax-select
              placeholder="Select bank type"
              :include="['All']"
              url="/taxproducts/options_list"
              :clearable="false"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="product_type"
              v-on:update:data="product_type = $event"
            />
          </div>
          <div class="w-1/5 px-2">
            <span>Depot</span>
            <ajax-select
              placeholder="Select Condition"
              url="/depot/options"
              :include="['All']"
              :clearable="false"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="depot"
              v-on:update:data="depot = $event"
            />
          </div>
        </div>
        <div class="w-full flex mb-4">
          <!-- <div class="w-1/5 px-2">
            <span>Group Result By</span>
            <ajax-select
              placeholder="Select Groupping"
              :options="['BDC', 'Depot', 'Product type']"
              :clearable="false"
              :multiple="true"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="group_by"
              v-on:update:data="group_by = $event"
            />
          </div> -->
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
          <div class="md:w-1/5 sm:w-full mx-1 mt-4">
            <vs-button
              color="danger"
              class="m-1 w-full"
              icon-pack="feather"
              @click="resetFilter()"
              icon="icon-refresh-cw"
              >Reset</vs-button
            >
          </div>
        </div>
      </div>
    </vx-card>

    <vx-card title="RESULT VIEW" class="mt-5">
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
                  <th scope="col">Inlet Date Time</th>
                  <th scope="col">Depot</th>
                  <th scope="col">Product</th>
                  <th scope="col">Volume</th>
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
                    {{ record.datetime }}
                  </td>
                  <td>
                    {{ record.depot }}
                  </td>
                  <td>
                    {{ record.product_type }}
                  </td>
                  <td>
                    {{ record.volume }}
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
      pkey: "SML--inlet--model-list",
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
  mounted() {
    this.currentPage = Number(mStorage.get(`${this.pkey}page`)) || 1;
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page`, this.currentPage);
      this.getData();
    },
    pagination: function () {
      this.numbering = this.pagination.start;
    },
    product_type: function () {
      this.getData();
    },
    bdc: function () {
      this.getData();
    },
    depot: function () {
      this.getData();
    },
    date_range: function () {
      this.getData();
    },
    result_per_page: function () {
      this.getData();
    },
  },
  methods: {
    resetFilter: function () {
      this.product_type = "All";
      this.omc = "All";
      this.depot = "All";
      this.status = "All";
      this.date_range = {
        startDate: null,
        endDate: null,
      };
      this.getData();
    },
    number: function (num) {
      return this.numbering + num;
    },
    getData() {
      this.showLoading("Getting results...");
      this.post("/inlet/inlets", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        product_type: this.product_type,
        depot: this.depot,
        omc: this.omc,
        status: this.status,
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
    //file import function starts here
    uploadCompleted: function (data) {
      if (data.success == true) {
        this.pushDescription(data.message);
        this.importStatus = "Reading File Content";
        this.checkImportStatus(data.jobid);
      } else {
        Swal.fire("Failed!", data.message, "error");
      }
    },
    checkImportStatus: function (id) {
      const vm = this;
      this.statuscheck = setInterval(function () {
        vm.checkStatusForImport(id);
      }, 1200);
    },
    formatDesc: function (data) {
      var error = false;
      this.errorStr.forEach((item) => {
        if (data && data.toLowerCase().includes(item)) {
          error = true;
        }
      });
      if (error) {
        return `<span class="text-danger">->${data}<br/></span> `;
      }
      return `<span class="text-primary">->${data}<br/></span> `;
    },
    pushDescription: function (data) {
      if (!this.importDesc.includes(data)) {
        this.importDesc.push(data);
      }
    },
    clearLog: function () {
      this.popupActive = false;
      this.importDesc = [];
      this.importStatus = "";
      if (this.statuscheck) {
        clearInterval(this.statuscheck);
      }
    },
    checkStatusForImport: function (id) {
      this.post("/preorder/import_status", {
        jobid: id,
      })
        .then((response) => {
          var data = response.data;
          if (data.success) {
            var status = data.status;
            this.pushDescription(status.description);
            this.importStatus = status.status;
            this.importDetails = status.details;
            if (status.status.toLowerCase() == "completed") {
              clearInterval(this.statuscheck);
              this.getData();
              this.importStatus = "";
              this.importDetails = "";
            }
            if (status.status.toLowerCase().includes("error")) {
              clearInterval(this.statuscheck);
              this.importStatus = "";
            }
          } else {
            this.pushDescription(response.data.message);
            this.importStatus = "";
            clearInterval(this.statuscheck);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style lang="css" scoped>
.form-control.reportrange-text {
  padding: 8px 10px !important;
  border: 1px solid rgba(60, 60, 60, 0.26) !important;
  border-radius: 4px !important;
}
</style>