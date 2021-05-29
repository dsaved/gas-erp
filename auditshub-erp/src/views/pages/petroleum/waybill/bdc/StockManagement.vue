<template>
  <div id="pag-tax-list">
    <div class="vx-col w-full my-5">
      <div class="flex">
        <vs-spacer />
        <div class="w-1/5 px-2">
          <span>BDC</span>
          <ajax-select
            placeholder="Select bdc"
            url="/bdc/options"
            :include="['All']"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            :selected="bdc"
            v-on:update:data="bdc = $event"
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
    </div>

    <vx-card title="BDC LIST" class="mt-5">
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
                  <th scope="col">BDC</th>
                  <th scope="col">Products</th>
                  <th scope="col">Level</th>
                  <th scope="col">Total Quantity</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(record, index) in sortedRecords"
                  :key="index"
                  class="tr-values vs-table--tr tr-table-state-null selected"
                  v-on:click="
                    linkto(
                      '/petroleum/waybill/stock-management/' + record.bdc
                    )
                  "
                >
                  <td scope="row" @click.stop="">
                    <vs-checkbox
                      v-model="selectedRecords"
                      :vs-value="record.id"
                      >{{ number(index) }}</vs-checkbox
                    >
                  </td>
                  <td>
                    {{ record.bdc }}
                  </td>
                  <td>
                    {{ record.product }}
                  </td>
                  <td>
                    <progress-bar
                      :options="{
                        progress: {
                          color:
                            record.percent > 80
                              ? '#036C82'
                              : record.percent < 20
                              ? '#B92510'
                              : '#192155',
                          backgroundColor: '#E6E6E6',
                          inverted: false,
                        },
                        layout: {
                          verticalTextAlign: 61,
                          horizontalTextAlign: 35,
                          zeroOffset: 0,
                          strokeWidth: 30,
                          progressPadding: 0,
                        },
                      }"
                      :value="parseFloat(record.level)"
                    />
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
import mStorage from "@/store/storage.js";
import ProgressBar from "vuejs-progress-bar";

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
  beforeRouteLeave(to, from, next) {
    if (this.getDataInterval) {
      clearInterval(this.getDataInterval);
      this.getDataInterval = null;
    }
    if (this.statuscheckExport) {
      clearInterval(this.statuscheckExport);
      this.statuscheckExport = null;
    }
    next();
  },
  props: {},
  components: { ProgressBar },
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
      getDataInterval: null,
      // export data starts here
      popupActiveExport: false,
      canCloseModalExport: false,
      reloadButtonExport: false,
      statuscheckExport: null,
      jobid: null,
      exportDesc: [],
      exportStatus: "",
      exportDetails: "",
      //receipt data list starts here
      pkey: "waybill-stock-mg-list",
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
      bdc: "All",
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
    const vm = this;
    this.getDataInterval = setInterval(function () {
      vm.getData(true);
    }, 1300);
  },
  watch: {
    currentPage: function () {
      mStorage.set(`${this.pkey}page`, this.currentPage);
      this.getData();
    },
    bdc: function () {
      this.getData();
    },
    result_per_page: function () {
      this.getData();
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
    getData(background) {
      if (!background) {
        this.loading = true;
      }
      this.post("/tanks/bdcs", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        bdc: this.bdc,
      })
        .then((response) => {
          this.loading = false;
          this.pagination = response.data.pagination;
          if (response.data.success == true) {
            this.assets_not_found = false;
            this.message = "";
            this.records = response.data.tanks;
          } else {
            this.assets_not_found = true;
            this.message = response.data.message;
            this.records = [];
            if (!background) {
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
          }
        })
        .catch((error) => {
          this.loading = false;
          if (!background) {
            this.$vs.notify({
              title: "Error!!!",
              text: `${error.message}`,
              sticky: true,
              border: "danger",
              color: "dark",
              duration: null,
              position: "bottom-left",
            });
          }
        });
    },
    beforeDestroy() {
      this.charts.forEach((chart) => {
        if (chart) {
          chart.dispose();
        }
      });
    },
  },
};
</script>
<style scoped>
.hello {
  width: 100%;
  height: 500px;
}
</style>