<template>
  <div id="pag-tax-list">
    <div class="vx-col w-full my-5">
      <div class="flex">
        <vs-spacer />
        <div class="w-1/4 px-2">
          <span>Product Type</span>
          <ajax-select
            placeholder="Select product type"
            :include="['All']"
            url="/taxproducts/options_list"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            :selected="product_type"
            v-on:update:data="product_type = $event"
          />
        </div>
        <!-- <vs-button
          type="filled"
          @click.prevent="exportWarn"
          class="mt-5"
          color="primary"
          id="button-with-loading"
          icon-pack="feather"
          icon="icon-file"
          >Export Excel</vs-button
        > -->
      </div>
    </div>
    <div class="vx-col w-full">
      <h3 class="text-center" v-show="message">{{ message }}</h3>
      <div v-show="loading">
        <div style="margin-top: 1.5rem" class="loading">
          <div class="effect-1 effects"></div>
          <div class="effect-2 effects"></div>
          <div class="effect-3 effects"></div>
        </div>
      </div>
    </div>
    <div class="vx-row">
      <div
        v-for="(record, index) in sortedRecords"
        :key="index"
        class="vx-col w-full sm:w-1/1 lg:w-1/2 mb-base"
      >
        <vx-card>
          <div class="vx-row">
            <div class="vx-col w-1/3">
              <h5 class="mb-2" :style="'color:#192155'">
                {{ record.depot_name }}
              </h5>
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
                    type: 'cylinder',
                    verticalTextAlign: 61,
                    horizontalTextAlign: 35,
                    zeroOffset: 0,
                    strokeWidth: 30,
                    progressPadding: 0,
                  },
                }"
                :value="parseFloat(record.percent)"
              />
              <p class="text-cemter">
                <b :style="'color:#192155'">{{ record.product_name }}</b>
              </p>
            </div>
            <div class="vx-col w-1/2">
              <div class="flex justify-between flex-wrap">
                <p class="text-cemter">
                  <b :style="'color:#192155'"
                    >Current Quantity: {{ record.volume }}
                    {{ record.unit === "L" ? "Liters" : record.unit }}</b
                  >
                </p>
                <router-link
                  v-if="record.invalidpump"
                  class="mt-4 p-1 mr-2 shadow-lg w-full text-danger"
                  :to="'invalid/' + record.depot + '/' + record.product"
                  >Invalid discharge detected</router-link
                >
                <p v-else class="mt-4 p-1 mr-2 shadow-lg w-full text-primary">
                  Product out flow normal
                </p>
                <p
                  v-if="record.notsold"
                  class="mt-4 p-2 mr-2 shadow-lg w-full text-danger"
                >
                  This product has not been sold for a week
                </p>
                <p v-else class="mt-4 p-2 mr-2 shadow-lg w-full text-primary">
                  Product sale stat. normal
                </p>
              </div>
            </div>
          </div>
        </vx-card>
      </div>
    </div>
    <div class="vx-col w-full">
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
  props: {
    depot: {
      type: String,
      required: true,
    },
  },
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
      pkey: "petroleum-sm-list",
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
      product_type: {label:"All", value: "All"},
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
    product_type: function () {
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
    getData(background) {
      if (!background) {
        this.loading = true;
      }
      this.post("/tanks/depot", {
        result_per_page: this.result_per_page,
        page: this.currentPage,
        depot: this.depot,
        product_type: this.product_type.value,
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