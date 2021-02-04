<template>
  <div class="cl">
    <vx-card title="Create role for system users">
      <p>
        You can create unique role for different users, just thick the allowed
        permission for each role you create.
      </p>
      <div class="mt-5">
        <form>
          <vs-input
            v-validate="'required'"
            placeholder="Role Title"
            name="Role Title"
            v-model="role"
            class="mt-5"
          />
          <span class="text-danger text-sm" v-show="errors.has('Role Title')">{{
            errors.first("Role Title")
          }}</span>

          <div class="demo-alignment">
            <vs-checkbox icon-pack="feather" icon="icon-plus" color="success"  v-model="permissions" vs-value="create"
              >Create</vs-checkbox
            >
            <vs-checkbox icon-pack="feather" icon="icon-book-open" color="primary"  disabled="true" v-model="permissions" vs-value="read"
              >Read</vs-checkbox
            >
            <vs-checkbox icon-pack="feather" icon="icon-edit-2" color="warning"  v-model="permissions" vs-value="update"
              >Update</vs-checkbox
            >
            <vs-checkbox icon-pack="feather" icon="icon-trash" color="danger"  v-model="permissions" vs-value="delete"
              >Delete</vs-checkbox
            >
          </div>
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
    id: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      loading: false,
      role: "",
      permissions: ["read"],
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
      return Number(this.id) !== 0;
    },
    resetForm() {
      this.role = "";
      this.permissions = [];
    },
    getData() {
      this.loading = true;
      this.post("/users/get_role/", {
        id: this.id,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            const role = response.data.role;
            this.role = role.role;
            this.permissions = role.permissions.split(',');
          } else {
            // this.back();
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
          // this.back();
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
            if(!this.canAdd()){
              return Swal.fire("Not Allowed!", "You do not have permission to add any record", "error");
            }
            this.showLoading("Adding role to system");
            this.post("/users/role_add", {
              role: this.role,
              permissions: this.permissions,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Role Added", result.data.message, "success");
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
            if(!this.canUpdate()){
              return Swal.fire("Not Allowed!", "You do not have permission to update any record", "error");
            }
            this.showLoading("Updating current role");
            this.post("/users/role_update", {
              id: this.id,
              role: this.role,
              permissions: this.permissions,
            })
              .then((result) => {
                console.log(result.data);
                this.closeLoading();
                if (result.data.success == true) {
                  Swal.fire("Role Updated", result.data.message, "success");
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
