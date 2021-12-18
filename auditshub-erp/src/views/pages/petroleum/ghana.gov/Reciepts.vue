
<template>
  <div id="omc-org-view">
    <div id="omc-data">
      <vx-card title="Receipts">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
            <div
              class="
                flex flex-wrap-reverse
                items-center
                data-list-btn-container
              "
            >
              <vs-button
                color="dark"
                icon-pack="feather"
                v-if="canAdd()"
                @click="popupActive = true"
                icon="icon-upload-cloud"
                >Upload Receipts</vs-button
              >
            </div>
            <div
              class="
                flex flex-wrap-reverse
                items-center
                data-list-btn-container
              "
            >
              <vs-button
                color="danger"
                icon-pack="feather"
                class="ml-2"
                v-if="canDelete() && records.length > 0"
                @click="deleteWarnSingle"
                icon="icon-trash"
                >Remove
                {{ selectedRecords.length > 0 ? "Selected" : "All" }}</vs-button
              >
            </div>
            <vs-spacer />
            <div
              class="
                flex flex-wrap-reverse
                items-center
                data-list-btn-container
              "
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
                    <th scope="col">OMC</th>
                    <th scope="col">TIN</th>
                    <th scope="col">Bank</th>
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
                    :class="[
                      'tr-values vs-table--tr tr-table-state-null selected',
                      { 'text-danger': record.status === 'flagged' },
                    ]"
                  >
                    <td scope="row" @click.stop="">
                      <vs-checkbox
                        v-model="selectedRecords"
                        :vs-value="record.id"
                        >{{ number(index) }}</vs-checkbox
                      >
                    </td>
                    <td>
                      {{ record.omc | title }}
                    </td>
                    <td>
                      {{ record.omc_tin }}
                    </td>
                    <td>
                      {{ record.bank | title }}
                    </td>
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
      :title="'Upload Receipts '"
      :active.sync="popupActive"
      :key="popupActive + '121'"
    >
      <p v-if="hasdata(importStatus)">
        <vs-progress
          indeterminate
          color="rgb(164, 69, 15)"
          :height="2"
        ></vs-progress>
      </p>
      <p>
        Please select the file containing the list of <b>receipts</b> you wish
        to import.
      </p>
      <p v-if="hasdata(importDesc)">
        <span
          v-for="(desc, index) in importDesc"
          :key="index"
          v-html="formatDesc(desc)"
          ><br
        /></span>
        <vs-button
          color="dark"
          icon-pack="feather"
          size="small"
          v-if="downloadLink.length > 5"
          icon="icon-download"
          class="w-full m-1"
          @click="download(downloadLink)"
          >Download errored file</vs-button
        >
      </p>
      <p v-if="hasdata(importStatus)" class="text-secondary loadingDot">
        {{ importStatus }}
      </p>

      <p class="mt-4">
        <ds-file-upload
          upload-button-lable="Upload Receipts"
          type="relief"
          color="primary"
          max-size="10072"
          description="Allowed XLSX and XLX, Max size of 3MB"
          upload-url="/ghana_gov/import/"
          allowed-file-type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
          v-on:completed="uploadCompleted"
        />
      </p>
      <p>
        <span class="text-secondary"
          ><b>{{ importDetails }}</b></span
        >
      </p>
      <vs-row class="mt-4">
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
  data() {
    return {
      omc: {},
      //file import section
      popupActive: false,
      statuscheck: null,
      errorStr: ["unknown jobid", "error"],
      importDesc: [],
      importDetails: "",
      importStatus: "",
      //receipt data list starts here
      pkey: "ghana.gov-receipt-list-key",
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
      banksoption: null,
      downloadLink: "",
    };
  },
  computed: {
    selectAll: {
      get() {
        return this.records
          ? this.selectedRecords.length == this.records.length
          : false;
      },
      set(value) {
        const selected = [];

        if (value) {
          this.records.forEach(function (record) {
            selected.push(record.id);
          });
        }
        this.selectedRecords = selected;
      },
    },
    sortedRecords() {
      try {
        return this.filterObj(this.records, this.search).sort((a, b) => {
          let modifier = 1;
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
  mounted() {
    this.currentPage =
      Number(mStorage.get(`${this.pkey}page${this.omcid}`)) || 1;
    this.getReceipt();
  },
  watch: {
    currentPage() {
      mStorage.set(`${this.pkey}page${this.omcid}`, this.currentPage);
      this.getReceipt();
    },
    search(newVal, oldVal) {
      this.startSearch(newVal, oldVal);
    },
    pagination() {
      this.numbering = this.pagination.start;
    },
  },
  methods: {
    number(num) {
      return this.numbering + num;
    },
    startSearch(newVal, oldVal) {
      if (this.search_timer) {
        clearTimeout(this.search_timer);
      }
      const vm = this;
      this.search_timer = setTimeout(function () {
        vm.getReceipt();
      }, 800);
    },
    download(file) {
      const link = file.replace("../omc-api/", "");
      const win = window.open(`${this.site_link}/${link}`, "_blank");
      win.focus();
    },
    getReceipt() {
      this.loading = true;
      this.post("/ghana_gov/", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
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
    deleteWarnSingle() {
      if (!this.canDelete()) {
        return Swal.fire(
          "Not Allowed!",
          "You do not have permission to delete any record",
          "error"
        );
      }
      Swal.fire({
        title: "Are you sure?",
        html: `<p>you are about to remove ${
          this.selectedRecords.length > 0 ? "selected" : "all"
        } reciepts from the system!</p><span class="text-warning">You won't be able to revert this! </span>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3cc879",
        cancelButtonColor: "#ea5455",
        confirmButtonText: "Yes, remove them!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete();
        }
      });
    },
    delete() {
      this.showLoading("Removing OMC Receipts, hang on a bit...");
      this.post("/ghana_gov/delete", {
        ids: this.selectedRecords,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire(
              "Removed!",
              "The OMC Receipts has been deleted.",
              "success"
            ).then((result) => {
              if (result.isConfirmed) {
                // this.back();
                this.getReceipt();
              }
            });
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
    uploadCompleted(data) {
      this.downloadLink = "";
      if (data.success == true) {
        this.pushDescription(data.message);
        this.importStatus = "Reading File Content";
        this.checkImportStatus(data.jobid);
      } else {
        Swal.fire("Failed!", data.message, "error");
      }
    },
    checkImportStatus(id) {
      const vm = this;
      this.statuscheck = setInterval(function () {
        vm.checkStatusForImport(id);
      }, 1200);
    },
    formatDesc(data) {
      let error = false;
      this.errorStr.forEach((item) => {
        if (data && data.toLowerCase().includes(item)) {
          error = true;
        }
      });
      if (error) {
        if (data.includes("errored")) {
          this.downloadLink = data;
          return `<span class="text-danger">-> Error occured during data import<br/></span> `;
        }
        return `<span class="text-danger">->${data}<br/></span> `;
      }
      return `<span class="text-primary">->${data}<br/></span> `;
    },
    pushDescription(data) {
      if (!this.importDesc.includes(data)) {
        this.importDesc.push(data);
      }
    },
    clearLog() {
      this.banksoption = null;
      this.popupActive = false;
      this.importDesc = [];
      this.importStatus = "";
      if (this.statuscheck) {
        clearInterval(this.statuscheck);
      }
    },
    checkStatusForImport(id) {
      this.post("/ghana_gov/import_status", {
        jobid: id,
      })
        .then((response) => {
          const data = response.data;
          if (data.success) {
            const status = data.status;
            this.pushDescription(status.description);
            this.importStatus = status.status;
            this.importDetails = status.details;
            if (status.status.toLowerCase() == "completed") {
              this.getReceipt();
              clearInterval(this.statuscheck);
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

<style lang="scss">
.loadingDot:after {
  content: " .";
  font-size: 50px;
  line-height: 0;
  animation: dots 1s steps(5, end) infinite;
}
</style>