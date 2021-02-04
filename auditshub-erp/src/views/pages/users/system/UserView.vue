
<template>
  <div id="page-user-view">
    <vs-alert
      color="danger"
      title="User Not Found"
      :active.sync="user_not_found"
    >
      <span>User record with id: {{ userid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'Users' }" class="text-inherit underline"
          >All Users</router-link
        >
      </span>
    </vs-alert>

    <div id="user-data" v-if="user_found">
      <vx-card title="Account" class="mb-base">
        <!-- Avatar -->
        <div class="vx-row">
          <!-- Avatar Col -->
          <div class="vx-col" id="avatar-col">
            <div v-if="photo" class="con-img mb-2 mt-3">
              <img
                key="onlineImg"
                :src="photo"
                alt="user-img"
                width="100"
                height="100"
                class="rounded-full shadow-md cursor-pointer block"
              />
            </div>
          </div>

          <!-- Information - Col 1 -->
          <div class="vx-col flex-1" id="account-info-col-1">
            <table>
              <tr>
                <td class="font-semibold">Username</td>
                <td>@{{ user.username }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Name</td>
                <td>{{ user.fullname }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Email</td>
                <td>{{ user.email }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 1 -->

          <!-- Information - Col 2 -->
          <div class="vx-col flex-1" id="account-info-col-2">
            <table>
              <tr>
                <td class="font-semibold">Mobile</td>
                <td>{{ user.phone }}</td>
              </tr>
              <tr v-if="user.role && user.role.label">
                <td class="font-semibold">Role</td>
                <td>{{ user.role.label }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Base Url</td>
                <td>{{ user.baseurl }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 2 -->
          <div class="vx-col w-full flex" id="account-manage-buttons">
            <vs-button
              icon-pack="feather"
              icon="icon-edit"
              class="mr-4"
              v-if="canUpdate()"
              :to="{ name: 'Update User', params: { userid: userid } }"
              >Edit</vs-button
            >
            <vs-button
              type="border"
              color="danger"
              icon-pack="feather"
              icon="icon-trash"
              v-if="canDelete()"
              @click="deleteWarnSingle(userid)"
              >Delete</vs-button
            >
          </div>
        </div>
      </vx-card>

      <!-- Permissions -->
      <vx-card v-if="user.role && user.role.permissions">
        <div class="vx-row">
          <div class="vx-col w-full">
            <div class="flex items-end px-3">
              <feather-icon svgClasses="w-6 h-6" icon="LockIcon" class="mr-2" />
              <span class="font-medium text-lg leading-none">Permissions</span>
            </div>
            <vs-divider />
          </div>
        </div>

        <div class="block overflow-x-auto">
          <table class="w-full permissions-table">
            <tr>
              <th
                v-for="(permission, index) in user.role.permissions.split(',')"
                :key="index"
              >
                <vs-chip
                  :color="getOrderStatusColor(permission)"
                  class="product-order-status"
                  >{{ permission | title }}</vs-chip
                >
              </th>
            </tr>
          </table>
        </div>
      </vx-card>
    </div>
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
    userid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      user_not_found: false,
      user_found: false,
      user: {
        selectedPages: [],
        username: "_ _ _ _ _ _",
        fullname: "_ _ _ _ _ _   _ _ _ _ _ _",
        email: "_ _ _ _ _ _",
        phone: "_ _ _ _ _ _",
        password: "",
        photo: "",
        baseurl: null,
        confirm_password: "",
        organization: null,
        role: null,
        access_level: "",
        user_type: null,
      },
    };
  },
  mounted: function () {
    this.getData();
  },
  computed: {
    photo(){
      let picture = require('@/assets/images/portrait/small/default.png');
      if(this.hasdata(this.user.photo)){
        picture = this.user.photo;
      }
      return picture;
    }
  },
  watch: {},
  methods: {
    getData() {
      this.showLoading("getting user infomation");
      this.post("/users/get/", {
        id: this.userid,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.user_found = true;
            console.log(response.data);
            this.user = response.data.users[0];
          } else {
            this.user_not_found = true;
            this.$vs.notify({
              title: "Error!!!",
              text: `${response.data.message}`,
              sticky: true,
              color: "danger",
              duration: null,
              position: "bottom-left",
            });
          }
        })
        .catch((error) => {
          this.closeLoading();
          this.$vs.notify({
            title: "Error!!!",
            text: `${error.message}`,
            sticky: true,
            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          this.user_not_found = true;
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
      this.showLoading("Deleting user, hang on a bit...");
      this.post("/users/delete", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire("Deleted!", "The user has been deleted.", "success").then(
              (result) => {
                if (result.isConfirmed) {
                  this.back();
                }
              }
            );
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          Swal.fire("Failed!", error.message, "error");
        });
    },
  },
};
</script>