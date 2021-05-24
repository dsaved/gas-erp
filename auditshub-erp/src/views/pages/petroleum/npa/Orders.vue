<template>
  <div class="cl">
    <vs-alert
      color="danger"
      title="Order Not Found"
      :active.sync="assets_not_found"
    >
      <span>Order not found. Please add</span>
      <span v-if="AppActiveUser.access_level === 'admin'">
        or
        <span
          @click="popupActive = true"
          class="text-inherit underline cursor-pointer"
          >Upload Order</span
        >
      </span>
    </vs-alert>

    <vx-card title="DECLARATIONS" class="mt-5">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <header class="md:flex my-3">
          <div
            class="md:w-1/5 sm:w-full mx-1"
            v-if="AppActiveUser.access_level === 'admin'"
          >
            <vs-button
              color="dark"
              icon-pack="feather"
              v-if="canAdd()"
              @click="popupActive = true"
              icon="icon-upload-cloud"
              class="w-full"
              >Upload Order</vs-button
            >
          </div>
          <div
            class="md:w-1/5 sm:w-full mx-1"
            v-if="!assets_not_found && AppActiveUser.access_level === 'admin'"
          >
            <vs-button
              color="danger"
              icon-pack="feather"
              class="w-full"
              v-if="canDelete()"
              @click="deleteWarn()"
              icon="icon-trash"
              >Remove All</vs-button
            >
          </div>
          <vs-spacer />
          <div class="md:w-1/5 sm:w-full">
            <vs-input
              id="text"
              type="text"
              class="m-1 w-full"
              v-model="search"
              placeholder="Search Order"
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
                  <th scope="col">Order Date</th>
                  <th scope="col">OMC</th>
                  <th scope="col">Product</th>
                  <th scope="col">Volume</th>
                  <th scope="col">BDC</th>
                  <th scope="col">Depot</th>
                  <th scope="col" class="text-right">Unit Price</th>
                  <th scope="col">Transporter</th>
                  <th scope="col">Vehicle No.</th>
                  <th scope="col">Reference No.</th>
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
                    {{ record.order_date | dateyear(true) }}
                  </td>
                  <td>
                    {{ record.omc }}
                  </td>
                  <td>
                    {{ record.product_type }}
                  </td>
                  <td>
                    {{ record.volume }}
                  </td>
                  <td>
                    {{ record.bdc }}
                  </td>
                  <td>
                    {{ record.depot }}
                  </td>
                  <td class="text-right">
                    {{ record.unit_price }}
                  </td>
                  <td>
                    {{ record.transporter }}
                  </td>
                  <td>
                    {{ record.vehicle_number }}
                  </td>
                  <td>
                    {{ record.reference_number }}
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
      title="Upload Order"
      :active.sync="popupActive"
    >
      <p v-if="hasdata(importStatus)">
        <vs-progress
          indeterminate
          color="rgb(164, 69, 15)"
          :height="2"
        ></vs-progress>
      </p>
      <p>
        Please select the file containing the <b>Order</b> list you wish
        to import.
      </p>
      <p v-if="hasdata(importDesc)">
        <span
          v-for="(desc, index) in importDesc"
          :key="index"
          v-html="formatDesc(desc)"
          ><br
        /></span>
      </p>
      <p v-if="hasdata(importStatus)" class="text-secondary loadingDot">
        {{ importStatus }}
      </p>

      <p class="mt-4">
        <ds-file-upload
          upload-button-lable="Upload Order"
          type="relief"
          color="primary"
          max-size="5072"
          description="Allowed XLSX and XLX, Max size of 5MB"
          upload-url="/order/import/"
          allowed-file-type="excel"
          v-on:completed="uploadCompleted"
        />
      </p>
      <p>
        <span class="text-secondary"
          ><b>{{ importDetails }}</b></span
        >
      </p>
      <vs-row>
        <vs-col
          vs-offset="10"
          vs-type="flex"
          vs-justify="center"
          vs-align="center"
          vs-w="2"
        >
          <vs-button
            color="danger"
            icon-pack="feather"
            @click="clearLog()"
            icon="icon-x"
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
import Datepicker from "vuejs-datepicker";
import mStorage from "@/store/storage.js";

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
	beforeRouteLeave (to, from, next) {
		if (this.statuscheck) {
			clearInterval(this.statuscheck)
			this.statuscheck = null
		}
		next()
	},
  components: {
    Datepicker,
  },
  data() {
    return {
      loading: false,
      assets_not_found: false,
      accoun_found: false,
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
      //order data list starts here
      pkey: "npa-orders-list",
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
    search: function (newVal, oldVal) {
      this.startSearch(newVal, oldVal);
    },
    pagination: function () {
      this.numbering = this.pagination.start;
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
    getData() {
      this.loading = true;
      this.post("/order", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
      })
        .then((response) => {
          this.loading = false;
          this.pagination = response.data.pagination;
          if (response.data.success == true) {
            this.assets_not_found = false;
            this.message = "";
            this.records = response.data.orders;
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
          this.loading = false;
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
    deleteWarnSingle: function (id) {
      if (!this.canDelete()) {
        return Swal.fire(
          "Not Allowed!",
          "You do not have permission to delete any record",
          "error"
        );
      }
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete([id]);
        }
      });
    },
    deleteWarn: function () {
      if (!this.canDelete()) {
        return Swal.fire(
          "Not Allowed!",
          "You do not have permission to delete any record",
          "error"
        );
      }
      Swal.fire({
        title: "Are you sure?",
        html: `<p>you are about to remove all orders from the system!</p><span class="text-warning">You won't be able to revert this! </span>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, remove it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete();
        }
      });
    },
    delete: function () {
      this.showLoading("Deleting Order, hang on a bit...");
      this.post("/order/delete", {})
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.$vs.notify({
              title: "Error!!!",
              text: "The Order has been deleted.",
              sticky: true,
              border: "danger",
              color: "dark",
              duration: null,
              position: "bottom-left",
            });
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
      this.post("/order/import_status", {
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
