<template>
  <div class="cl">
    <vx-card title="Create tax window">
      <p>
        This tax window will be used to calculate tax on each <b>product</b
        ><br />
        for the given period of time
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/3 w-full mb-2">
            <span>Select Tax window name</span>
              <ajax-select
                placeholder="Select Tax window name"
                :options="window_names"
                :clearable="false"
				:disabled="isEdit()"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="window_name"
                v-on:update:data="window_name = $event"
              />
              <vs-input
                v-validate="{ required: true }"
                name="window name"
				hidden
                v-model="window_name.label"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('window name')"
                >{{ errors.first("window name") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <datepicker
                class="mt-5 w-full"
                placeholder="Tax Date From"
                v-model="date_from"
                :disabled="isEdit()"
              ></datepicker>
              <vs-input
                v-validate="'required'"
                name="Date From"
                v-model="datefrom"
                type="date"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('Date From')"
                >{{ errors.first("Date From") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <datepicker
                class="mt-5 w-full"
                placeholder="Tax Date To"
                v-model="date_to"
                :disabled="isEdit()"
              ></datepicker>
              <vs-input
                v-validate="'required'"
                name="Date To"
                v-model="dateto"
                type="date"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('Date To')"
                >{{ errors.first("Date To") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <ajax-select
                placeholder="Select tast type"
                :options="[]"
                url="/taxtype/options"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="tax_type"
                v-on:update:data="tax_type = $event"
                c-class="mt-5 w-full"
              />
              <vs-input
                v-validate="'required'"
                name="tast type"
                v-model="tax_type.value"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('tast type')"
                >{{ errors.first("tast type") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <ajax-select
                placeholder="Select product"
                :options="[]"
                url="/taxproducts/options_list"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="tax_product"
                v-on:update:data="tax_product = $event"
                c-class="mt-5 w-full"
              />
              <vs-input
                v-validate="'required'"
                name="product"
                v-model="tax_product.value"
                hidden
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('product')"
                >{{ errors.first("product") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="{ required: true, regex: /^[+]?[0-9]+\.[0-9]+$/ }"
                label-placeholder="Tax rate"
                name="rate"
                v-model="rate"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('rate')">{{
                errors.first("rate")
              }}</span>
            </div>

            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-button
                v-if="isEdit()"
                type="filled"
                @click.prevent="updateForm"
                class="mt-5 block"
                id="button-with-loading"
                >Update</vs-button
              >
              <vs-button
                v-else
                type="filled"
                @click.prevent="submitForm"
                class="mt-5 block"
                >Submit</vs-button
              >
            </div>
          </div>
        </form>
      </div>
    </vx-card>
  </div>
</template>

<script>
// Import Swal
import Swal from "sweetalert2";
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
  props: {
    taxwindowid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      loading: false,
      tax_type: [],
      tax_product: [],
      rate: "",
      window_names: [
        { label: "Window 1", value: "W1" },
        { label: "Window 2", value: "W2" },
      ],
      window_name: { label: "", value: "" },
      date_from: "",
      date_to: "",
    };
  },
  watch: {
    loading() {
      if (this.loading) {
        this.$loader = this.$vs.loading({
          background: this.backgroundLoading,
          color: this.colorLoading,
          container: "#button-with-loading",
          scale: 0.45,
        });
      } else {
        this.$vs.loading.close("#button-with-loading > .con-vs-loading");
      }
    },
  },
  mounted() {
    if (this.isEdit()) {
      this.getData();
    }
  },
  computed: {
    datefrom() {
      return String(this.date_from);
    },
    dateto() {
      return String(this.date_to);
    },
  },
  methods: {
    isEdit() {
      return Number(this.taxwindowid) !== 0;
    },
    resetForm() {
      this.name = "";
      this.description = "";
    },
    getData() {
      this.loading = true;
      this.post("/taxwindow/get/", {
        id: this.taxwindowid,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            const result = response.data.windows[0];
            this.tax_type = result.tax_type;
            this.tax_product = result.tax_product;
            this.rate = result.rate;
            this.window_name = result.name;
            this.date_from = result.date_from;
            this.date_to = result.date_to;
          } else {
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
    submitForm() {
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if form have no errors
          if (!this.loading) {
            if (!this.canAdd()) {
              return Swal.fire(
                "Not Allowed!",
                "You do not have permission to add any record",
                "error"
              );
            }
            this.showLoading("Adding tax window to system");
            this.post("/taxwindow/add", {
              tax_type: this.tax_type.value,
              tax_product: this.tax_product.value,
              rate: this.rate,
              window_name: this.window_name.label,
              wincode: this.window_name.value,
              date_from: this.date_from,
              date_to: this.date_to,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Tax Window Added", result.data.message, "success");
                  this.resetForm();
                } else {
                  Swal.fire("Failed!", result.data.message, "error");
                }
              })
              .catch((error) => {
                this.closeLoading();
                Swal.fire("Failed!", error.message, "error");
              });
          }
        }
      });
    },
    updateForm() {
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if form have no errors
          if (!this.loading) {
            if (!this.canUpdate()) {
              return Swal.fire(
                "Not Allowed!",
                "You do not have permission to update any record",
                "error"
              );
            }
            this.showLoading("Updating current tax window");
            this.post("/taxwindow/update", {
              id: this.taxwindowid,
              tax_type: this.tax_type.value,
              tax_product: this.tax_product.value,
              rate: this.rate,
              window_name: this.window_name.label,
              wincode: this.window_name.value,
              date_from: this.date_from,
              date_to: this.date_to,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire(
                    "Tax window Updated",
                    result.data.message,
                    "success"
                  );
                } else {
                  Swal.fire("Failed!", result.data.message, "error");
                }
              })
              .catch((error) => {
                this.closeLoading();
                Swal.fire("Failed!", error.message, "error");
              });
          }
        }
      });
    },
  },
};
</script>
