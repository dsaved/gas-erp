<template>
  <div class="cl">
    <vs-alert
      color="danger"
      title="Account Not Found"
      :active.sync="accoun_not_found"
    >
      <span>Account record with id: {{ accountid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="{ name: 'bank-account' }"
          class="text-inherit underline"
          >All Accounts</router-link
        >
      </span>
    </vs-alert>

    <vx-card v-if="accoun_found" :title="data.name">
      <p>
        <small>Owned by: {{ notNull(data.owner) }}</small
        ><br />
        <span v-html="accountNumbers(data)"></span> <br />
        <small>record created on: {{ data.created }}</small>
      </p>
      <div class="vx-row mt-5">
        <h5 class="vx-col sm:w-1/1 w-full mb-2">
          <strong>Information</strong>
        </h5>
        <div class="vx-col sm:w-1/3 w-full mb-2 ml-5">
          <h6>
            <b>Bank Type:</b> <br />
            <small>{{ data.bank_type }}</small>
          </h6>
          <br />
          <h6>
            <b>Bank:</b> <br />
            <small>{{ notNull(data.bank) }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/4 w-full mb-2">
          <h6>
            <b>status:</b><br />
            <small>{{ data.status }}</small>
          </h6>
          <br />
          <h6>
            <b>Inactive Date:</b><br />
            <small>{{ data.date_inactive }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/3 w-full mb-2">
          <h6>
            <b>Balance:</b><br />
            <small>{{ data.balance }}</small>
          </h6>
          <br />
          <h6>
            <b>Date:</b><br />
            <small>{{ data.post_date }}</small>
          </h6>
        </div>
        <div class="vx-col sm:w-1/1 w-full mb-2 flex">
          <vs-button
            v-if="canUpdate()"
            type="filled"
            @click.prevent="linkto('/accounts/bank-account/' + accountid)"
            class="mt-5 block"
            >Edit</vs-button
          >
          <vs-button
            color="danger"
            class="mx-1 mt-5 block"
            v-if="canDelete()"
            @click="deleteWarnSingle(accountid)"
            >Delete</vs-button
          >
        </div>
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
  props: {
    accountid: {
      type: String / Number,
      default: 0,
    },
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      loading: false,
      accoun_not_found: false,
      accoun_found: false,
      data: {
        type: Object,
        default: function () {
          return {};
        },
      },
    };
  },
  computed: {},
  mounted: function () {
    this.getData();
  },
  methods: {
    notNull: function (data) {
      if (null != data && data.label) {
        return data.label;
      }
      return "";
    },
    accountNumbers: function (account) {
      var acnts = ``;
      if (this.hasdata(account.acc_num1) && this.hasdata(account.acc_num2)) {
        acnts += account.acc_num1 + ` | ` + account.acc_num1;
      } else if (this.hasdata(account.acc_num1)) {
        acnts += account.acc_num1;
      } else if (this.hasdata(account.acc_num2)) {
        acnts += account.acc_num2;
      }
      if (account.status == "Inactive") {
        acnts += ` - <span class="text-danger">Inactive</span> `;
      } else {
        acnts += ` - <span class="text-primary">Active</span> `;
      }
      return acnts;
    },
    getData() {
      this.loading = true;
      this.post("/bankaccounts/editbank", {
        id: this.accountid,
      })
        .then((response) => {
          this.loading = false;
          if (response.data.success == true) {
            this.accoun_found = true;
            this.data = response.data.bankaccounts[0];
          } else {
            this.accoun_not_found = true;
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
          this.accoun_not_found = true;
        });
    },
    deleteWarnSingle: function (id) {
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
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.delete([id]);
        }
      });
    },
    delete: function (ids) {
      this.showLoading("Deleting Account, hang on a bit...");
      this.post("/bankaccounts/delete", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.$vs.notify({
              title: "Error!!!",
              text: "The Account has been deleted.",
              sticky: true,
              border: "danger",
              color: "dark",
              duration: null,
              position: "bottom-left",
            });
            this.back();
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
