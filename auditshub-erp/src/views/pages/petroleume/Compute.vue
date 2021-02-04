<template>
  <div id="pag-tax-list">
    <vx-card ref="filterCard" title="Compute" class="user-list-filters mb-8">
      <div class="flex mb-4" v-for="(compute, index) in computes" :key="index">
        <ajax-select
          placeholder="Select Product"
          :options="[]"
          :clearable="false"
          url="/taxproducts/options"
          :dir="$vs.rtl ? 'rtl' : 'ltr'"
          :selected="compute.product"
          v-on:update:data="compute.product = $event"
          c-class="w-1/3 px-2"
        />
        <vs-input
          placeholder="Litters"
          name="litters"
          v-model="compute.litters"
          class="w-1/3 px-2"
        />
        <datepicker
          class="w-1/3 px-2"
          placeholder="Date"
          v-model="compute.taxdate"
        ></datepicker>

        <vs-button
          v-if="computes.length > 1"
          type="filled"
          color="danger"
          @click.prevent="removecompute(index)"
          class="px-2"
          id="button-with-loading"
          icon-pack="feather"
          icon="icon-trash"
          ></vs-button
        >
      </div>
      <div class="vx-row flex flex-wrap-reverse">
        <div class="vx-col sm:w-1/3 w-full mb-2 flex flex-wrap-reverse">
          <vs-button
            type="filled"
            @click.prevent="addcompute"
            class="mt-5"
            color="warning"
            id="button-with-loading"
            icon-pack="feather"
            icon="icon-plus"
            >Add</vs-button
          >

          <vs-button
            type="filled"
            color="dark"
            @click.prevent="calculate"
            class="mt-5 mx-1"
            id="button-with-loading"
            icon-pack="feather"
            icon="icon-cpu"
            >Calculate</vs-button
          >
        </div>
      </div>
    </vx-card>

    <div v-show="loading">
      <p></p>
      <div style="margin-top: 1.5rem" class="loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>

    <vx-card
      v-for="(rec, index) in records"
      :key="index"
      :title="rec.product"
      class="my-4"
    >
      <p></p>
      <div class="vs-component vs-con-table stripe vs-table-primary">
        <div class="con-tablex vs-table--content">
          <div class="vs-con-tbody vs-table--tbody">
            <table class="vs-table vs-table--tbody-table">
              <thead class="vs-table--thead">
                <tr>
                  <th scope="col">Tax</th>
                  <th scope="col">Calculation</th>
                  <th scope="col" class="text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(record, index) in rec.computations"
                  :key="index"
                  class="tr-values vs-table--tr tr-table-state-null selected"
                >
                  <td>{{ record.tax | title }}</td>
                  <td>{{ record.calculation }}</td>
                  <td class="text-right">{{ record.amount }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <h3 class="text-center" v-show="message">{{ message }}</h3>
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
                <div class="text-semibold">
                  <b>GHS {{ rec.total }}</b>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </vx-card>

    <vx-card
      v-if="total"
      title="Grand Total"
      class="user-list-filters mb-8"
    >
      <strong>GHS {{total}}</strong>
    </vx-card>
  </div>
</template>

<script>
// Import Swal
import Swal from "sweetalert2";
import mStorage from "../../../store/storage";
import Datepicker from "vuejs-datepicker";

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
  components: {
    Datepicker,
  },
  data() {
    return {
      pkey: "tax-schedule-page-key",
      message: "",
      total: "",
      computes: [
        {
          litters: "",
          taxdate: "",
          product: [],
        },
      ],
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
      records: [],
      search_timer: null,
    };
  },
  computed: {},
  mounted: function () {},
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
    selectedRecords: function (newVal, oldVal) {
      if (this.selectedRecords.length > 0) {
        this.deletebutton = true;
      } else {
        this.deletebutton = false;
      }
    },
  },
  methods: {
    addcompute: function () {
      this.computes.push({
        litters: "",
        taxdate: "",
        product: [],
      });
    },
    removecompute: function (index) {
      this.computes.splice(index, 1);
    },
    calculate() {
      this.loading = true;
      this.post("/compute/", {
        computes: this.computes,
      })
        .then((response) => {
          this.loading = false;
          console.log(response.data);
          this.message = "";
          this.records = response.data.computes;
          this.total = response.data.total;
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
