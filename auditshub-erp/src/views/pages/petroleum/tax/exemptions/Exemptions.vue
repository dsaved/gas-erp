<template>
  <div id="pag-tax-list">
    <vx-card ref="filterCard" title="Filters" class="user-list-filters mb-8">
      <div class="vx-row">
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">Filter OMC</label>
          <ajax-select
            placeholder="Filter OMC"
            :options="[]"
            :clearable="false"
            :include="[{ label: 'All', value: 'all' }]"
            url="/omc/options"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            :selected="omc_filter"
            v-on:update:data="omc_filter = $event"
            c-class="mb-4 md:mb-0"
          />
        </div>
      </div>
    </vx-card>

    <vx-card title="Tax Exemptions">
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-primary">
        <header class="header-table vs-table--header my-3">
          <div
            class="flex flex-wrap-reverse items-center data-list-btn-container"
          >
            <vs-button
              color="primary"
              type="border"
              icon-pack="feather"
              v-if="canAdd()"
              icon="icon-plus"
              v-on:click="linkto('/petroleum/exemptions/add')"
              >Add Exemption</vs-button
            >
            <vs-button
              class="mx-2"
              color="danger"
              type="border"
              icon-pack="feather"
              icon="icon-trash"
              v-if="deletebutton && canDelete()"
              v-on:click="deleteWarn()"
              >Delete Exemption</vs-button
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
              placeholder="Search exemptions..."
            />
            <v-select
              placeholder="Result count"
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
                  <th scope="col">OMC</th>
                  <th scope="col">Product</th>
                  <th scope="col">Litters</th>
                  <th scope="col">From</th>
                  <th scope="col">To</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(record, index) in sortedRecords"
                  :key="index"
                  v-on:click="linkto('/petroleum/exemptions/' + record.id)"
                  class="tr-values vs-table--tr tr-table-state-null selected"
                >
                  <td scope="row" @click.stop="">
                    <vs-checkbox
                      v-model="selectedRecords"
                      :vs-value="record.id"
                      >{{ number(index) }}</vs-checkbox
                    >
                  </td>
                  <td>{{ record.omc | title }}</td>
                  <td>{{ record.tax_product | title }}</td>
                  <td>{{ record.litters }}</td>
                  <td>
                    <vs-chip
                      transparent
                      :color="getclasscolor()"
                      class="product-order-status"
                      >{{ record.date_from }}</vs-chip
                    >
                  </td>
                  <td>
                    <vs-chip
                      transparent
                      :color="getclasscolor()"
                      class="product-order-status"
                      >{{ record.date_to }}</vs-chip
                    >
                  </td>
                  <td>
                    <feather-icon
                      icon="EditIcon"
                      svgClasses="w-5 h-6 hover:text-primary stroke-current"
                      v-if="canUpdate()"
                      @click.stop="
                        linkto('/petroleum/exemptions/' + record.id)
                      "
                    />
                    <feather-icon
                      icon="TrashIcon"
                      svgClasses="w-5 h-5 hover:text-danger stroke-current"
                      class="ml-2"
                      v-if="canDelete()"
                      @click.stop="deleteWarnSingle(record.id)"
                    />
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
      pkey: "exemptions-page-key",
      message: "",
      omcfilter: "",
      omc_filter: { label: "All", value: "all" },
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
    this.getData();
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page`, this.currentPage);
      this.getData();
    },
    omc_filter: function (data) {
      if (
        null === data ||
        data === "" ||
        typeof data.value === "undefined" ||
        data.value === "all"
      ) {
        this.omcfilter = "";
      } else {
        this.omcfilter = data.value;
      }
      this.getData();
    },
    result_per_page: function () {
      this.getData();
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
      }, 500);
    },
    getData() {
      this.loading = true;
      this.post("/taxexemption/", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        search: this.search,
        omcfilter: this.omcfilter,
      })
        .then((response) => {
          this.loading = false;
          console.log(response.data);
          if (response.data.success == true) {
            this.message = "";
            this.records = response.data.exemptions;
          } else {
            this.records =[]
            this.message = response.data.message;
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
    deleteWarn() {
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
        confirmButtonColor: "#3cc879",
        cancelButtonColor: "#ea5455",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete(this.selectedRecords);
        }
      });
    },
    deleteWarnSingle(id) {
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
        confirmButtonColor: "#3cc879",
        cancelButtonColor: "#ea5455",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete([id]);
        }
      });
    },
    delete: function (ids) {
      this.showLoading("Deleting product(s), hang on a bit...");
      this.post("/taxexemption/delete", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire(
              "Deleted!",
              "The product(s) has been deleted.",
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
    getOrderStatusColor(status) {
      if (status === "create") return "success";
      if (status === "delete") return "danger";
      if (status === "update") return "warning";
      return "primary";
    },
  },
};
</script>

<style lang="scss">
#page-tax-list {
  .user-list-filters {
    .vs__actions {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-58%);
    }
  }
}
</style>
