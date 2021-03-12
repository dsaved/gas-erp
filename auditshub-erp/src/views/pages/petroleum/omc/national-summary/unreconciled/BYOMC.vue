
<template>
  <div id="omc-org-view">
    <vs-alert
      color="danger"
      title="OMC Not Found"
      :active.sync="user_not_found"
    >
      <span>Bank record with id: {{ bankid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'omc-unreconciled-national-summary' }" class="text-inherit underline"
          >All Banks</router-link
        >
      </span>
    </vs-alert>

    <div id="omc-data" v-if="user_found">
      <vx-card :title="bank">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="header-table vs-table--header my-3">
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
                    <th scope="col">OMC</th>
                    <!-- <th scope="col">Declaration Number &amp; Receipt Number</th> -->
                    <th scope="col">Mode of Payment</th>
                    <th scope="col" class="text-right">Amount</th>
                    <th scope="col">Date</th>
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
                      {{ record.name | title }}
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

import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;

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
    bankid: {
      type: String,
      default: 0,
    },
    date: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      user_not_found: false,
      user_found: false,
      bank: "",
      //receipt data list starts here
      pkey: "omc-nationalsummary-bd-unreconciled-list-key",
      message: "",
      numbering: 0,
      currentPage: 1,
      result_per_page: 20,
      loading: false,
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
      Number(mStorage.get(`${this.pkey}page${this.bankid}`)) || 1;
    this.getData();
  },
  watch: {
    currentPage() {
      mStorage.set(`${this.pkey}page${this.bankid}`, this.currentPage);
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
    getData() {
      this.showLoading("getting infomation");
      this.post("/nationalsummary/getsingle_unreconciled/", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
        id: this.bankid,
        date: this.date,
      })
        .then((response) => {
          this.closeLoading();
          console.log(response.data);
          this.user_found = response.data.success;
          if (response.data.success == true) {
            this.message = "";
            this.records = response.data.omc;
            this.bank = response.data.bank;
          } else {
            this.message = response.data.message;
            this.records = [];
            this.user_not_found = true;
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

<style lang="scss">
.loadingDot:after {
  content: " .";
  font-size: 50px;
  line-height: 0;
  animation: dots 1s steps(5, end) infinite;
}
</style>