
<template>
  <div id="omc-org-view">
    <vs-alert
      color="danger"
      title="OMC Not Found"
      :active.sync="user_not_found"
    >
      <span>OMC record with id: {{ omcid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'omc-list' }" class="text-inherit underline"
          >All OMCs</router-link
        >
      </span>
    </vs-alert>

    <div id="omc-data" v-if="user_found">
      <vx-card title="Oil Marketing Company" class="mb-base">
        <!-- Avatar -->
        <div class="vx-row">
          <!-- Avatar Col -->
          <div class="vx-col" id="avatar-col">
            <div v-if="photo" class="con-img mb-2 mt-3">
              <img
                key="omc-image"
                :src="photo"
                alt="omc-img"
                width="100"
                height="100"
                class="rounded-full shadow-md cursor-pointer block"
              />
            </div>
          </div>

          <!-- Information - Col 1 -->
          <div class="vx-col flex-1" id="account-info-col-1">
            <table>
              <tr>
                <td class="font-semibold">Name</td>
                <td>{{ omc.name }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Email</td>
                <td>{{ omc.email }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Phone</td>
                <td>{{ omc.phone }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 1 -->

          <!-- Information - Col 2 -->
          <div class="vx-col flex-1" id="account-info-col-2">
            <table>
              <tr>
                <td class="font-semibold">Location</td>
                <td>{{ omc.location }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Region</td>
                <td>{{ omc.region }}</td>
              </tr>
              <tr>
                <td class="font-semibold">District</td>
                <td>{{ omc.district }}</td>
              </tr>
            </table>
          </div>
        </div>
      </vx-card>

      <vx-card :title="omc.name + '\'s fallout receipts'">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <vs-button
              type="relief"
              color="warning"
              icon-pack="feather"
              icon="icon-file-text"
              v-if="AppActiveUser.access_level === 'admin'"
              @click="exportWarn()"
              >Export</vs-button
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
                placeholder="Search receipts"
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
                    <th scope="col">Bank</th>
                    <!-- <th scope="col">Declaration Number &amp; Receipt Number</th> -->
                    <th scope="col">Mode of Payment</th>
                    <th scope="col" class="text-right">Amount</th>
                    <th scope="col">Date</th>
                    <th scope="col">Created</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in sortedRecords"
                    :key="index"
                    v-on:click="
                      linkto(
                        '/petroleum/omc/fallouts/' +
                          omcid +
                          '/view/receipt/' +
                          record.id +
                          '/' +
                          number(index)
                      )
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
                      {{ record.bank | title }}
                    </td>
                    <!-- <td>
                      <b>{{ record.declaration_number }}</b> <br />
                      {{ record.receipt_number }}
                    </td> -->
                    <td>
                      {{ record.mode_of_payment }}
                    </td>
                    <td class="text-right">
                      {{ record.amount }}
                    </td>
                    <td>
                      {{ record.date }}
                    </td>
                    <td>
                      {{ record.created }}
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
      title="File Export In Progress"
      :active.sync="popupActive"
    >
      <p>
        <span
          v-for="(desc, index) in importDesc"
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
  beforeRouteLeave(to, from, next) {
    if (this.statuscheck) {
      clearInterval(this.statuscheck);
      this.statuscheck = null;
    }
    next();
  },
  props: {
    omcid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      user_not_found: false,
      user_found: false,
      omc: {},
      // export data starts here
      popupActive: false,
      canCloseModal: false,
      reloadButton: false,
      statuscheck: null,
      jobid: null,
      errorStr: ["unknown jobid", "error"],
      importDesc: [],
      exportStatus: "",
      exportDetails: "",
      //receipt data list starts here
      pkey: "omc-fallout-receipt-list-key",
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
    photo() {
      return require("@/assets/images/portrait/small/default.png");
    },
  },
  mounted: function () {
    this.currentPage =
      Number(mStorage.get(`${this.pkey}page${this.omcid}`)) || 1;
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page${this.omcid}`, this.currentPage);
      this.getReceipt();
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
        vm.getReceipt();
      }, 800);
    },
    getReceipt() {
      this.loading = true;
      this.post("/omcfallout/receipts/", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
        id: this.omcid,
      })
        .then((response) => {
          this.loading = false;
          console.log(response.data);
          if (response.data.success == true) {
            this.message = "";
            this.records = response.data.receipts;
          } else {
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
          this.pagination = response.data.pagination;
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
    getData() {
      this.showLoading("getting OMC infomation");
      this.post("/omcfallout/get", {
        id: this.omcid,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.user_found = true;
            this.omc = response.data.omcs[0];
            this.getReceipt();
          } else {
            this.user_not_found = true;
            this.$vs.notify({
              title: "Error!!!",
              text: `${response.data.message}`,
              sticky: true,
              color: "danger",
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
            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          this.user_not_found = true;
        });
    },
    //reconciliation starts here
    clearLog: function () {
      this.popupActive = false;
      this.canCloseModal = false;
      this.reloadButton = false;
      this.jobid = null;
      this.importDesc = [];
      this.exportStatus = "";
      if (this.statuscheck) {
        clearInterval(this.statuscheck);
      }
    },
    statusCheckFileExport: function () {
      this.canCloseModal = false;
      this.reloadButton = false;
      const vm = this;
      this.statuscheck = setInterval(function () {
        vm.checkStatus();
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
    exportWarn: async function () {
      const { value: filename } = await Swal.fire({
        title: "Export OMC Receipts",
        text: "You are about to export this receipts",
        icon: "question",
        input: "text",
        showCancelButton: true,
        confirmButtonColor: "#0d6723",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, continue!",
        inputPlaceholder: "Save file as?",
        inputValue: this.omc.name+"-"+new Date().getTime(),
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
    export: function (filename) {
      this.showLoading("Sending Request For File Export");
      this.post("/omcfallout/start_export", {
        omc_id: this.omcid,
        filename: filename,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.selectedRecords = [];
            this.popupActive = true;
            this.pushDescription(response.data.message);
            this.exportStatus = "Initializing";
            this.jobid = response.data.jobid;
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
    checkStatus: function () {
      this.post("/omcfallout/file_export_status", {
        jobid: this.jobid,
      })
        .then((response) => {
          var data = response.data;
          if (data.success) {
            var status = data.status;
            this.pushDescription(status.description);
            this.exportStatus = status.status;
            this.exportDetails = status.details;
            if (status.status.toLowerCase() == "completed") {
              clearInterval(this.statuscheck);
              this.exportStatus = "";
              this.canCloseModal = true;
            }
            if (status.status.toLowerCase().includes("error")) {
              clearInterval(this.statuscheck);
              this.exportStatus = "";
              this.canCloseModal = true;
            }
          } else {
            this.pushDescription(response.data.message);
            this.exportStatus = "";
            clearInterval(this.statuscheck);
            this.canCloseModal = true;
          }
        })
        .catch((error) => {
          this.pushDescription("a network error has occured");
          clearInterval(this.statuscheck);
          this.exportStatus = "";
          this.canCloseModal = true;
          this.reloadButton = true;
          console.log(error);
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