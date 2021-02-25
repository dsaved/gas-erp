<template>
  <div class="cl">
    <vx-card title="Create tax type">
      <p>
        The tax product you create here will be ued as an option list when
        creating
        <b>Tax Schedule</b>. <br />
        You can always modify the data later.
      </p>
      <div class="mt-5">
        <form>
          <div class="vx-row">
            <div class="vx-col sm:w-1/2 w-full mb-2">
              <vs-input
                v-validate="'required'"
                label-placeholder="Tax product Name"
                name="name"
                v-model="name"
                class="mt-5 w-full"
              />
              <span class="text-danger text-sm" v-show="errors.has('name')">{{
                errors.first("name")
              }}</span>

              <vs-textarea
                v-validate="'required'"
                name="description"
                label="Description"
                v-model="description"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('description')"
                >{{ errors.first("description") }}</span
              >

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
  props: {
    taxproductid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      loading: false,
      name: "",
      description: "",
    };
  },
  watch: {
    loading: function () {
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
  mounted: function () {
    if (this.isEdit()) {
      this.getData();
    }
  },
  methods: {
    isEdit() {
      return Number(this.taxproductid) !== 0;
    },
    resetForm() {
      this.name = "";
      this.description = "";
    },
    getData() {
      this.loading = true;
      this.post("/taxproducts/get/", {
        id: this.taxproductid,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            const result = response.data.products[0];
            this.name = result.name;
            this.description = result.description;
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
            this.showLoading("Adding tax product to system");
            this.post("/taxproducts/add", {
              name: this.name,
              description: this.description,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Tax type Added", result.data.message, "success");
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
            this.showLoading("Updating current tax product");
            this.post("/taxproducts/update", {
              id: this.taxproductid,
              name: this.name,
              description: this.description,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire(
                    "Tax product Updated",
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
