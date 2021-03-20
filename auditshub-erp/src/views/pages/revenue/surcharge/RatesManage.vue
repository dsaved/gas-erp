<template>
  <div class="cl">
    <vx-card title="Create Surcharge Rate">
      <p>
        This surcharge rate will be used to calculate penalty on each
        <b>account transactions</b><br />
        for the given period of time
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/3 w-full mb-2">
              <vs-input
                v-validate="{ required: true }"
                label-placeholder="Name"
                name="name"
                v-model="name"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('name')">{{
                errors.first("name")
              }}</span>
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
              <datepicker
                class="mt-5 w-full"
                placeholder="Tax Date From"
                v-model="date_from"
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

            <div class="vx-col sm:w-1/1 w-full mb-2">
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
    rateid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      loading: false,
      name: "",
      rate: "",
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
      return Number(this.rateid) !== 0;
    },
    resetForm() {
      this.name = "";
      this.description = "";
    },
    getData() {
      this.loading = true;
      this.post("/surcharge/get/", {
        id: this.rateid,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            const result = response.data.surcharges[0];
            this.name = result.name;
            this.rate = result.rate;
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
            this.showLoading("Adding rate to system");
            this.post("/surcharge/add", {
              name: this.name,
              rate: this.rate,
              date_from: this.date_from,
              date_to: this.date_to,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Rate Added", result.data.message, "success").then(
                    (result) => {
                      if (result.isConfirmed) {
                        this.back();
                      }
                    }
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
            this.showLoading("Updating current rate");
            this.post("/surcharge/update", {
              id: this.taxscheduleid,
              name: this.name.value,
              rate: this.rate,
              date_from: this.date_from,
              date_to: this.date_to,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Rate Updated", result.data.message, "success");
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
