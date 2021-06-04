<template>
  <div>
    <vx-card title="RECONCILIATION BTW MANIFEST & DECLARATION">
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
          <div class="w-1/4 px-2">
            <span>BDC</span>
            <ajax-select
              placeholder="Select BDC"
              :options="[]"
              url="/bdc/options_list"
              :clearable="false"
              :include="['All']"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              :selected="bdc"
              v-on:update:data="bdc = $event"
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
        <div class="flex mb-4">
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
                  <th scope="col">Arrival Date</th>
                  <th scope="col">Product</th>
                  <th scope="col">BDC</th>
                  <th scope="col">Manifest Vol.</th>
                  <th scope="col">Declaration Vol.</th>
                  <th scope="col">Vol. Difference</th>
                  <th scope="col" class="text-right">Manifest Amount</th>
                  <th scope="col" class="text-right">Declaration Amount</th>
                  <th scope="col" class="text-right">
                    Amount Difference
                  </th>
                  <th scope="col">Cleared in (days)</th>
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
                    {{ record.arrival_date }}
                  </td>
                  <td>
                    {{ record.manifest_product }}
                  </td>
                  <td>
                    {{ record.manifest_omc }}
                  </td>
                  <td>
                    {{ record.manifest_volume }}
                  </td>
                  <td>
                    {{ record.declaration_volume }}
                  </td>
                  <td>
                    {{ record.difference_volume }}
                  </td>
                  <td class="text-right">
                    {{ record.manifest_amount }}
                  </td>
                  <td class="text-right">
                    {{ record.declaration_amount }}
                  </td>
                  <td class="text-right">
                    {{ record.difference_amount }}
                  </td>
                  <td>
                    {{ record.days }}
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
    
    <vs-popup
      background-color="rgba(200,200,200,.8)"
      persistent
      :button-close-hidden="true"
      title="File Export In Progress"
      :active.sync="showExportPopup"
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
            v-if="reloadExportPopup"
            color="primary"
            icon-pack="feather"
            icon="icon-refresh-cw"
            @click="statusCheckFileExport()"
            class="mx-1"
            >reload</vs-button
          >

          <vs-button
            v-if="canCloseExportPopup"
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
      // export data starts here
      showExportPopup: false,
      canCloseExportPopup: false,
      reloadExportPopup: false,
      statusCheckExport: null,
      exportJobID: null,
      errorStr: ["unknown jobid", "error"],
      exportDesc: [],
      exportStatus: "",
      exportDetails: "",
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
      idf_condition: "All",
      declaration_condition: "All",
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
  mounted: function () {
    this.currentPage = Number(mStorage.get(`${this.pkey}page`)) || 1;
    this.filterData();
  },
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
      this.idf_condition = "All";
      this.declaration_condition = "All";
      this.group_by = ["BDC"];
      this.status = "All";
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
      this.post("/inputreconciliation", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        product_type: this.product_type,
        bdc: this.bdc,
        group_by: this.group_by,
        idf_condition: this.idf_condition,
        declaration_condition: this.declaration_condition,
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
    //Export starts here
    formatDesc(data) {
      let error = false;
      this.errorStr.forEach((item) => {
        if (data && data.toLowerCase().includes(item)) {
          error = true;
        }
      });
      if (error) {
        return `<span class="text-danger">-> ${data}<br/></span> `;
      }
      return `<span class="text-primary">-> ${data}<br/></span> `;
    },
    clearLog() {
      this.showExportPopup = false;
      this.canCloseExportPopup = false;
      this.reloadExportPopup = false;
      this.exportJobID = null;
      this.exportDesc = [];
      this.exportStatus = "";
      if (this.statusCheckExport) {
        clearInterval(this.statusCheckExport);
      }
    },
    statusCheckFileExport() {
      this.canCloseExportPopup = false;
      this.reloadExportPopup = false;
      const vm = this;
      this.statusCheckExport = setInterval(function () {
        vm.checkStatus();
      }, 1200);
    },
    pushDescription(data) {
      if (!this.exportDesc.includes(data)) {
        this.exportDesc.push(data);
      }
    },
    async exportWarn() {
      const { value: filename } = await Swal.fire({
        title: "Export Summary",
        text: "You are about to export this report",
        icon: "question",
        input: "text",
        showCancelButton: true,
        confirmButtonColor: "#0d6723",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, continue!",
        inputPlaceholder: "Save file as?",
        inputValue: `${this.status}-input-reconciliation-${new Date().getTime()}`,
        inputValidator: (value) => {
          if (!value) {
            return "You need to write file name!";
          }
        },
      });

      if (filename) {
        this.export(filename);
      }
    },
    export(filename) {
      this.showLoading("Sending Request For File Export");
      this.post("/options/start_export", {
        filename: filename,
        export_type: "petroleum-import-reconciliation",
        config_data: JSON.stringify({
          product_type: this.product_type,
          bdc: this.bdc,
          group_by: this.group_by,
          idf_condition: this.idf_condition,
          declaration_condition: this.declaration_condition,
          status: this.status,
          date_range: this.date_range,
        }),
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success === true) {
            this.selectedRecords = [];
            this.showExportPopup = true;
            this.pushDescription(response.data.message);
            this.exportStatus = "Initializing";
            this.exportJobID = response.data.jobid;
            this.statusCheckFileExport();
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          Swal.fire("Failed!", error.message, "error");
        });
    },
    checkStatus() {
      this.post("/options/file_export_status", {
        jobid: this.exportJobID,
      })
        .then((response) => {
          const data = response.data;
          if (data.success) {
            const status = data.status;
            this.pushDescription(status.description);
            this.exportStatus = status.status;
            this.exportDetails = status.details;
            if (status.status.toLowerCase() === "completed") {
              clearInterval(this.statusCheckExport);
              this.exportStatus = "";
              this.canCloseExportPopup = true;
            }
            if (status.status.toLowerCase().includes("error")) {
              clearInterval(this.statusCheckExport);
              this.exportStatus = "";
              this.canCloseExportPopup = true;
            }
          } else {
            this.pushDescription(response.data.message);
            this.exportStatus = "";
            clearInterval(this.statusCheckExport);
            this.canCloseExportPopup = true;
          }
        })
        .catch((error) => {
          this.pushDescription("a network error has occured");
          clearInterval(this.statusCheckExport);
          this.exportStatus = "";
          this.canCloseExportPopup = true;
          this.reloadExportPopup = true;
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