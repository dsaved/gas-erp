<template>
  <div class="cl">
    <vs-alert
      color="danger"
      title="Account Not Found"
      :active.sync="accoun_not_found"
    >
      <span>Account record with id: {{ accountid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="{ name: 'rbank-account' }"
          class="text-inherit underline"
          >All Accounts</router-link
        >
      </span>
    </vs-alert>

    <vx-card v-if="accoun_found" :title="data.name">
      <p>
        <small>Owned by: {{ notNull(data.owner) }}</small
        ><br />
        <span v-html="accountNumbers(data)"></span> <br />
        <small>record created on: {{ data.created }}</small>
      </p>
      <div class="vx-row mt-5">
        <h5 class="vx-col sm:w-1/1 w-full mb-2">
          <strong>Information</strong>
        </h5>
        <div class="vx-col sm:w-1/3 w-full mb-2 ml-5">
          <h6>
            <b>Bank Type:</b> <br />
            <small>{{ data.bank_type }}</small>
          </h6>
          <br />
          <h6>
            <b>Bank:</b> <br />
            <small>{{ notNull(data.bank) }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/4 w-full mb-2">
          <h6>
            <b>status:</b><br />
            <small>{{ data.status }}</small>
          </h6>
          <br />
          <h6>
            <b>Inactive Date:</b><br />
            <small>{{ data.date_inactive }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/3 w-full mb-2">
          <h6>
            <b>Balance:</b><br />
            <small>{{ data.balance }}</small>
          </h6>
          <br />
          <h6>
            <b>Date:</b><br />
            <small>{{ data.post_date }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/1 w-full mb-2 flex">
          <vs-spacer />
          <vs-button
            color="dark"
            class="mx-1 mt-5 block"
            v-if="canAdd()"
            @click="linkto('/revenue/accounts/' + accountid + '/reconcile')"
            >Use For Reconcilation</vs-button
          >
        </div>
      </div>
    </vx-card>

    <vx-card :title="data.name + ' Transactions'" class="mt-5">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-secondary">
        <header class="header-table vs-table--header my-3">
          <div
            class="flex flex-wrap-reverse items-center data-list-btn-container"
          >
            <vs-button
              color="warning"
              icon-pack="feather"
              v-if="canAdd()"
              @click="popupActive = true"
              icon="icon-upload-cloud"
              >Upload Statements</vs-button
            >
          </div>
          <div
            class="flex flex-wrap-reverse items-center data-list-btn-container"
          >
            <vs-input
              id="text"
              type="text"
              class="mx-1"
              v-model="search"
              placeholder="Search transactions"
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
                  <th scope="col">Post Date</th>
                  <th scope="col">Reference</th>
                  <th scope="col" class="text-right">Debit</th>
                  <th scope="col" class="text-right">credit</th>
                  <th scope="col" class="text-right">Balance</th>
                  <th scope="col">Offset Acc.</th>
                  <th scope="col">Created</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(record, index) in sortedRecords"
                  :key="index"
                  v-on:click="
                    linkto(
                      '/revenue/accounts/' +
                        accountid +
                        '/view/transaction/' +
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
                    {{ record.post_date }}
                  </td>
                  <td>
                    {{ record.reference }}
                  </td>
                  <td class="text-right">
                    {{ record.debit_amount }}
                  </td>
                  <td class="text-right">
                    {{ record.credit_amount }}
                  </td>
                  <td class="text-right">
                    {{ record.balance }}
                  </td>
                  <td>
                    {{ record.offset_acc_no }}
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
              style="justify-content: space-between; display: flex; width: 100%"
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

    <vs-popup
      background-color="rgba(200,200,200,.8)"
      persistent
      :title="'Upload Statement for ' + data.name"
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
        Please select the file containing the list of <b>statements</b> you wish
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
          upload-button-lable="Upload Statements"
          type="relief"
          color="primary"
          max-size="3072"
          :file-id="accountid"
          description="Allowed XLSX and XLX, Max size of 3MB"
          upload-url="/statements/import/"
          allowed-file-type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
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
import mStorage from "../../../../store/storage";

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
    accountid: {
      type: String / Number,
      default: 0,
    },
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      loading: false,
      accoun_not_found: false,
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
      //receipt data list starts here
      pkey: "stm-trans-list-key",
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
    this.currentPage =
      Number(mStorage.get(`${this.pkey}page${this.accountid}`)) || 1;
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page${this.accountid}`, this.currentPage);
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
      this.post("/statements/account_statement", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
        id: this.accountid,
      })
        .then((response) => {
          this.loading = false;
          console.log(response.data);
          if (response.data.success == true) {
            this.message = "";
            this.records = response.data.transactions;
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
    notNull: function (data) {
      if (null != data && data.label) {
        return data.label;
      }
      return "";
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
    getData() {
      this.loading = true;
      this.post("/bankaccounts/editbank", {
        id: this.accountid,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            this.accoun_found = true;
            this.data = response.data.bankaccounts[0];
            this.getReceipt();
          } else {
            this.accoun_not_found = true;
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
          this.accoun_not_found = true;
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
    delete: function (ids) {
      this.showLoading("Deleting Account, hang on a bit...");
      this.post("/bankaccounts/delete", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.$vs.notify({
              title: "Error!!!",
              text: "The Account has been deleted.",
              sticky: true,
              border: "danger",
              color: "dark",
              duration: null,
              position: "bottom-left",
            });
            this.back();
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
      this.post("/statements/import_status", {
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
