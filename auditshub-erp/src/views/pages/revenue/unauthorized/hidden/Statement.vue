
<template>
  <div id="record-hidden-view">
    <vs-alert
      color="danger"
      title="Transaction Not Found"
      :active.sync="record_not_found"
    >
      <span> Transaction with id: {{ stmid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="
            '/revenue/unauthorized/hidden/' + accountid + '/list/' + offset_account
          "
          class="text-inherit underline"
          >Transaction</router-link
        >
      </span>
    </vs-alert>

    <div id="record-record" v-if="record_found">
      <vx-card class="mb-base">
        <h5>
          <b>{{ record.name }}</b>
        </h5>
        <p>
          <span v-html="accountNumbers(record)"></span> <br />
          <small>Bank: {{ record.bank }}</small
          ><br />
          <small>Owned by: {{ record.owner }}</small
          ><br />
        </p>
        <div class="vx-row mt-5">
          <h5 class="vx-col sm:w-1/1 w-full mb-2">
            <strong>Information</strong>
          </h5>
          <div class="vx-col sm:w-1/3 w-full mb-2 ml-5">
            <h6>
              <b>Post Date:</b> <br />
              <small>{{ record.post_date }}</small>
            </h6>
            <br />
            <h6>
              <b>Debit Amount:</b> <br />
              <small>{{ record.debit_amount }}</small>
            </h6>
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <h6>
              <b>Value Date:</b><br />
              <small>{{ record.value_date }}</small>
            </h6>
            <br />
            <h6>
              <b>Credit Amount:</b><br />
              <small>{{ record.credit_amount }}</small>
            </h6>
            <br />
          </div>
          <div class="vx-col sm:w-1/3 w-full mb-2">
            <h6>
              <b>Reference:</b><br />
              <small>{{ record.reference }}</small>
            </h6>
            <br />
            <h6>
              <b>Offset Account No.:</b><br />
              <small>{{ record.offset_account }}</small>
            </h6>
          </div>
          <div class="vx-col sm:w-1/1 w-full mb-2 ml-5">
            <h6>
              <b>Description / Particulars:</b> <br />
              <small>{{ record.particulars }}</small>
            </h6>
          </div>
        </div>

        <div class="w-full flex mb-4">
          <vs-spacer />
          <vs-button
            @click="sendtobogNow()"
            v-if="record.escalated_to_bog == '0'"
            color="primary"
            type="relief"
            icon-pack="feather"
            icon="icon-mail"
            >Send To BOG</vs-button
          >
          <vs-button
            @click="unhideWarn()"
            color="warning"
            type="relief"
            icon-pack="feather"
            class="ml-2"
            icon="icon-eye"
            >Show</vs-button
          >
        </div>

        <div class="vx-row my-5">
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <h6>
              <b>Audit Observations</b> <br />
              <small class="text-danger">Unauthorized Transfer</small>
            </h6>
          </div>
          <div class="vx-col sm:w-1/1 w-full mb-2" v-if="!isbog">
            <vs-button
              v-if="responded && canshowBtn"
              v-on:click="responded = !responded"
              color="rgb(11, 189, 135)"
              >Edit Response</vs-button
            >
            <vs-button
              v-if="!responded && canshowBtn"
              v-on:click="responded = !responded"
              color="rgb(11, 189, 135)"
              >Cancel Edit</vs-button
            >
            <div
              class="vx-row m-0"
              v-if="AppActiveUser.access_level != 'admin' && !responded"
            >
              <div class="vx-col sm:w-1/1 w-full my-2">
                <strong>Select any of the reasons below as appropriate</strong>
                <div class="flex">
                  <vs-radio
                    class="mx-2"
                    v-model="review_type"
                    vs-value="Authorized Transfer"
                    >Authorized Transfer</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type"
                    vs-value="Reversal"
                    >Reversal</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type"
                    vs-value="Bank Charges"
                    >Bank Charges</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type"
                    vs-value="Unknown Transfer"
                    >Unknown Transfer</vs-radio
                  >
                  <vs-spacer />
                </div>
              </div>
              <div
                class="vx-col sm:w-1/1 w-full mb-2 vx-row my-3"
                v-if="review_type == 'Authorized Transfer'"
              >
                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label for="account_name"><b>Account Name</b></label>
                  <vs-input
                    type="text"
                    class="w-full"
                    v-model="account_name"
                    placeholder=""
                  />
                </div>
                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label for="account_number"><b>Account Number</b></label>
                  <vs-input
                    type="text"
                    class="w-full"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    v-model="account_number"
                    placeholder=""
                  />
                </div>

                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label><b>Date</b></label>
                  <datepicker
                    class="w-full"
                    placeholder=""
                    v-model="date"
                  ></datepicker>
                </div>

                <div class="vx-col sm:w-1/3 w-full mb-2 mt-2">
                  <label><b>Upload File (Authority Of Transfer)</b></label>
                  <ds-file-upload
                    upload-button-lable="Select File"
                    type="relief"
                    color="primary"
                    max-size="3072"
                    file-id="0"
                    description="Allowed image, exel, plain text, pdf, and Docements. Max size of 3MB"
                    upload-url="/hiddenunauthorized/upload_file/"
                    allowed-file-type="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint,text/plain, application/pdf, image/*"
                    v-on:completed="uploadCompleted"
                    :remove-button="showRemoveImage"
                    v-on:remove-file="deleteMedia"
                    :oldfile="file"
                  />
                </div>

                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <label for="comments"><b>Comments</b></label>
                  <vs-textarea v-model="comments" />
                </div>

                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <vs-button
                    @click="submitResponse()"
                    color="dark"
                    type="relief"
                    icon-pack="feather"
                    icon="icon-arrow-right"
                    icon-after
                    >Submit Report</vs-button
                  >
                </div>
              </div>
              <div
                class="vx-col sm:w-1/1 w-full mb-2 vx-row my-3"
                v-else-if="review_type == 'Reversal'"
              >
                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label><b>Credit Date</b></label>
                  <datepicker
                    class="w-full"
                    placeholder=""
                    v-model="date"
                  ></datepicker>
                </div>

                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label><b>Reference</b></label>
                  <vs-input
                    type="text"
                    class="w-full"
                    v-model="reference_no"
                    placeholder=""
                  />
                </div>

                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <label for="comments"><b>Comments</b></label>
                  <vs-textarea v-model="comments" />
                </div>
                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <vs-button
                    @click="submitResponse()"
                    color="dark"
                    type="relief"
                    icon-pack="feather"
                    icon="icon-arrow-right"
                    icon-after
                    >Submit Report</vs-button
                  >
                </div>
              </div>
              <div class="vx-col sm:w-1/1 w-full my-3" v-else>
                <vs-button
                  @click="submitResponse()"
                  color="dark"
                  type="relief"
                  icon-pack="feather"
                  icon="icon-arrow-right"
                  icon-after
                  >Submit Report</vs-button
                >
              </div>
            </div>
          </div>

          <!-- bog revie form belloow -->
          <div class="vx-col sm:w-1/1 w-full mb-2" v-if="isbog">
            <span
              v-if="
                AppActiveUser.access_level != 'admin' &&
                record.escalated_to_bog == 1 &&
                responded_bog &&
                canshowBtn_bog
              "
            >
              <vs-button
                v-on:click="responded_bog = !responded_bog"
                color="rgb(11, 189, 135)"
                >Edit Response</vs-button
              >
            </span>
            <span
              v-if="
                AppActiveUser.access_level != 'admin' &&
                record.escalated_to_bog == 1 &&
                !responded_bog &&
                canshowBtn_bog
              "
            >
              <vs-button
                v-on:click="responded_bog = !responded_bog"
                color="rgb(11, 189, 135)"
                >Cancel Edit</vs-button
              >
            </span>
            <div
              class="vx-row m-0"
              v-if="
                AppActiveUser.access_level != 'admin' &&
                record.escalated_to_bog == 1 &&
                !responded_bog
              "
            >
              <div class="vx-col sm:w-1/1 w-full mb-2">
                <strong>Select any of the reasons below as appropriate</strong>
                <div class="d-flex justify-contents-start">
                  <vs-radio
                    class="mx-2"
                    v-model="review_type_bog"
                    vs-value="Authorized Transfer"
                    >Authorized Transfer</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type_bog"
                    vs-value="Reversal"
                    >Reversal</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type_bog"
                    vs-value="Bank Charges"
                    >Bank Charges</vs-radio
                  >
                  <vs-radio
                    class="mx-2"
                    v-model="review_type_bog"
                    vs-value="Unknown Transfer"
                    >Unknown Transfer</vs-radio
                  >
                </div>
              </div>
              <div
                class="vx-col sm:w-1/1 w-full mb-2 vx-row my-3"
                v-if="review_type_bog == 'Authorized Transfer'"
              >
                <div class="vx-col sm:w-1/4 w-full mb-2">
                  <label for="account_name_bog"><b>Account Name</b></label>
                  <vs-input
                    type="text"
                    class="w-full"
                    v-model="account_name_bog"
                    placeholder=""
                  />
                </div>
                <div class="vx-col sm:w-1/4 w-full mb-2">
                  <label for="account_number_bog"><b>Account Number</b></label>
                  <vs-input
                    type="text"
                    class="w-full"
                    v-model="account_number_bog"
                    placeholder=""
                  />
                </div>

                <div class="vx-col sm:w-1/4 w-full mb-2">
                  <label for="date_bog"><b>Date</b></label>
                  <datepicker
                    class="w-full"
                    placeholder=""
                    v-model="date_bog"
                  ></datepicker>
                </div>

                <div class="vx-col sm:w-1/3 w-full mb-2 mt-2">
                  <label><b>Upload File (Authority Of Transfer)</b></label>
                  <ds-file-upload
                    upload-button-lable="Select File"
                    type="relief"
                    color="primary"
                    max-size="3072"
                    file-id="0"
                    description="Allowed image, exel, plain text, pdf, and Docements. Max size of 3MB"
                    upload-url="/hiddenunauthorized/upload_file/"
                    allowed-file-type="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint,text/plain, application/pdf, image/*"
                    v-on:completed="uploadCompleted_bog"
                    :remove-button="showRemoveImage_bog"
                    v-on:remove-file="deleteMedia_bog"
                    :oldfile="file_bog"
                  />
                </div>

                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <label for="comments_bog"><b>Comments</b></label>
                  <vs-textarea v-model="comments_bog" />
                </div>
                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <vs-button
                    @click="submitResponse_bog()"
                    color="dark"
                    type="relief"
                    icon-pack="feather"
                    icon="icon-arrow-right"
                    icon-after
                    >Submit Report</vs-button
                  >
                </div>
              </div>
              <div
                class="vx-col sm:w-1/1 w-full mb-2 vx-row my-3"
                v-else-if="review_type_bog == 'Reversal'"
              >
                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label for="date_bog"><b>Credit Date</b></label>
                  <datepicker
                    class="w-full"
                    placeholder=""
                    v-model="date_bog"
                  ></datepicker>
                </div>

                <div class="vx-col sm:w-1/3 w-full mb-2">
                  <label for="reference_no_bog"><b>Reference</b></label>
                  <vs-input
                    class="w-full"
                    v-model="reference_no_bog"
                    placeholder=""
                  />
                </div>

                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <label for="comments_bog"><b>Comments</b></label>
                  <vs-textarea v-model="comments_bog" />
                </div>
                <div class="vx-col sm:w-1/1 w-full mb-2">
                  <vs-button
                    @click="submitResponse_bog()"
                    color="dark"
                    type="relief"
                    icon-pack="feather"
                    icon="icon-arrow-right"
                    icon-after
                    >Submit Report</vs-button
                  >
                </div>
              </div>
              <div class="vx-col sm:w-1/1 w-full mb-2 my-3" v-else>
                <vs-button
                  @click="submitResponse_bog()"
                  color="dark"
                  type="relief"
                  icon-pack="feather"
                  icon="icon-arrow-right"
                  icon-after
                  >Submit Report</vs-button
                >
              </div>
            </div>
          </div>
          <div
            v-if="AppActiveUser.access_level == 'admin'"
            class="vx-col sm:w-1/1 w-full mb-2"
          >
            <div
              v-for="(review, index) in reviews"
              :key="index"
              class="review-bg vx-row py-3 mt-3"
            >
              <div
                v-if="review.type == 'Authorized Transfer'"
                class="vx-col sm:w-1/1 w-full mb-2 vx-row m-0"
              >
                <h6>
                  <b
                    >{{ review.reviewedby | capitalize }} Response (By
                    {{ review.fullname }})</b
                  >
                </h6>
                <div class="vx-col sm:w-1/1 w-full mb-2 my-3">
                  <b>Transfer type</b>: {{ review.type }}
                </div>
                <div
                  v-if="hasdata(review.account_name)"
                  class="vx-col sm:w-1/4 w-full mb-2"
                >
                  <label for="account_name"><b>Account Name</b></label>
                  <p>{{ review.account_name }}</p>
                </div>
                <div
                  v-if="hasdata(review.account_number)"
                  class="vx-col sm:w-1/4 w-full mb-2"
                >
                  <label for="account_number"><b>Account Number</b></label>
                  <p>{{ review.account_number }}</p>
                </div>

                <div
                  v-if="isDate(review.date)"
                  class="vx-col sm:w-1/4 w-full mb-2"
                >
                  <label><b>Date</b></label>
                  <p>{{ review.date }}</p>
                </div>

                <div
                  v-if="hasdata(review.file)"
                  class="vx-col sm:w-1/4 w-full mb-2"
                >
                  <label><b>Authority Of Transfer</b></label>
                  <vs-button
                    v-on:click="download(review.file)"
                    color="rgb(11, 189, 135)"
                    icon-pack="feather"
                    icon="icon-download"
                    icon-after
                  >
                    Download</vs-button
                  >
                </div>
                <div
                  v-if="hasdata(review.comment)"
                  class="vx-col sm:w-1/1 w-full mb-2"
                >
                  <label><b>Comments:</b></label>
                  <p>{{ review.comment }}</p>
                </div>
              </div>
              <div
                v-else-if="review.type == 'Reversal'"
                class="vx-col sm:w-1/1 w-full mb-2 vx-row m-0"
              >
                <h6>
                  <b
                    >{{ review.reviewedby | capitalize }} Response (By
                    {{ review.fullname }})</b
                  >
                </h6>
                <div class="vx-col sm:w-1/1 w-full mb-2 my-3">
                  <b>Transfer type</b>: {{ review.type }}
                </div>
                <div
                  v-if="isDate(review.date)"
                  class="vx-col sm:w-1/3 w-full mb-2"
                >
                  <label><b>Credit Date</b></label>
                  <p>{{ review.date }}</p>
                </div>

                <div
                  v-if="review.reference_no"
                  class="vx-col sm:w-1/3 w-full mb-2"
                >
                  <label><b>Reference</b></label>
                  <p>{{ review.reference_no }}</p>
                </div>
                <div v-if="review.comment" class="vx-col sm:w-1/1 w-full mb-2">
                  <label><b>Comments</b></label>
                  <p>{{ review.comment }}</p>
                </div>
              </div>
              <div v-else class="vx-col sm:w-1/1 w-full mb-2 vx-row">
                <h6>
                  <b
                    >{{ review.reviewedby | capitalize }} Response (By
                    {{ review.fullname }})</b
                  >
                </h6>
                <div class="vx-col sm:w-1/1 w-full mb-2 my-3">
                  <b>Transfer type</b>: {{ review.type }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </vx-card>

      <vs-row vs-type="flex" vs-justify="space-between">
        <vs-col vs-type="flex" vs-justify="center" vs-align="center" vs-w="2">
          <vs-button
            @click="currentPage--"
            v-if="pagination.hasPrevious"
            color="rgb(16 22 58 / 82%)"
            type="relief"
            icon-pack="feather"
            icon="icon-arrow-left"
            >Previous</vs-button
          >
        </vs-col>

        <vs-col vs-type="flex" vs-justify="center" vs-align="center" vs-w="2">
          <vs-button
            @click="currentPage++"
            v-if="pagination.hasNext"
            color="rgb(16 22 58 / 82%)"
            type="relief"
            icon-pack="feather"
            icon="icon-arrow-right"
            icon-after
            >Next</vs-button
          >
        </vs-col>
      </vs-row>
    </div>
  </div>
</template>

<script>
// Import Swal
import Swal from "sweetalert2";
import mStorage from "../../../../../store/storage";
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
    offset_account: {
      type: String,
      default: "",
    },
    stmid: {
      type: String / Number,
      default: 0,
    },
    page: {
      type: String / Number,
      default: 0,
    },
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      record_not_found: false,
      record_found: false,
      reviews: [],
      record: {
        type: Object,
        default: function () {
          return {};
        },
      },
      //transaction record list starts here
      currentPage: 1,
      result_per_page: 1,
      loading: true,
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
      isbog: false,
      review_type: null,
      reference_no: "", //BTA / Warrant / Advice (Reference No)
      date: "",
      comments: "",
      account_name: "",
      account_number: "",
      user_id: 0,
      responseID: null,
      file: "",
      canshowBtn: false,
      isEdited: false,
      imageuploading: false, //only for file upload
      progress_width: 0, //only for file upload
      error: false, //only for file upload
      responded: false,
      //bog review section
      review_type_bog: null,
      reference_no_bog: "", //BTA / Warrant / Advice (Reference No)
      date_bog: "",
      comments_bog: "",
      account_name_bog: "",
      account_number_bog: "",
      canshowBtn_bog: false,
      isEdited_bog: false,
      responseID_bog: null,
      file_bog: "",
      imageuploading_bog: false, //only for file upload
      progress_width_bog: 0, //only for file upload
      error_bog: false, //only for file upload
      responded_bog: false,
    };
  },
  computed: {
    showRemoveImage: function () {
      return this.hasdata(this.file);
    },
    showRemoveImage_bog: function () {
      return this.hasdata(this.file_bog);
    },
  },
  created() {
    this.isbog = this.AppActiveUser.types[1] == "organization" ? true : false;
  },
  mounted: function () {
    if (this.page !== 0) {
      this.currentPage = this.page;
    } else {
      this.getData();
    }
  },
  watch: {
    reviews: function (newVal, oldVal) {
      this.currentUserId = this.AppActiveUser.id;
      const indexORG = this.reviews.findIndex((el) => el.reviewedby === "org");
      if (indexORG > -1) {
        this.canshowBtn = true;
        this.responded = true;
        this.isEdited = true;
        var found = this.reviews[indexORG];
        this.review_type = found["type"];
        this.responseID = found["id"];
        this.reference_no = found["reference_no"];
        this.date = found["date"];
        this.comments = found["comment"];
        this.user_id = found["user_id"];
        this.account_name = found["account_name"];
        this.account_number = found["account_number"];
        this.file = found["file"];
      }
      const indexBOG = this.reviews.findIndex((el) => el.reviewedby === "bog");
      if (indexBOG > -1) {
        this.canshowBtn_bog = true;
        this.responded_bog = true;
        this.isEdited_bog = true;
        var found = this.reviews[indexBOG];
        this.review_type_bog = found["type"];
        this.responseID_bog = found["id"];
        this.reference_no_bog = found["reference_no"];
        this.date_bog = found["date"];
        this.comments_bog = found["comment"];
        this.account_name_bog = found["account_name"];
        this.account_number_bog = found["account_number"];
        this.file_bog = found["file"];
      }
    },
    currentPage: function () {
      this.resetData();
      this.getData();
    },
  },
  methods: {
    isDate: function (date) {
      return null != date && date != "" && date != "0000-00-00";
    },
    resetData: function () {
      this.canshowBtn = false;
      this.responded = false;
      this.isEdited = false;
      this.canshowBtn_bog = false;
      this.responded_bog = false;
      this.isEdited_bog = false;
      this.reference_no = "";
      this.review_type = null;
      this.review_type_bog = null;
      this.date = "";
      this.comments = "";
      this.account_name = "";
      this.account_number = "";
      this.file = "";
      this.reference_no_bog = "";
      this.date_bog = "";
      this.comments_bog = "";
      this.account_name_bog = "";
      this.account_number_bog = "";
      this.file_bog = "";
    },
    download: function (file) {
      var win = window.open(this.site_link + "/" + file, "_blank");
      win.focus();
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
    getData: function (scroll) {
      var user = this.AppActiveUser;
      var isbog = user.types[1] == "organization" ? "true" : "false";
      this.showLoading("getting transaction infomation");
      this.post("/hiddenunauthorized/get/", {
        page: this.currentPage,
        result_per_page: this.result_per_page,
        account_id: this.accountid,
        access_type: user.access_level,
        offset_account: this.offset_account,
        user_id: user.id,
        search: this.search,
        isbog: isbog,
        stmid: this.stmid,
      })
        .then((response) => {
          console.log(response.data);
          this.record = [];
          this.closeLoading();
          this.message = response.data.message;
          this.pagination = response.data.pagination;
          if (response.data.success) {
            this.record = response.data.transactions[0];
            this.reviews = this.record.reviews;
            if (this.record.statement_id !== this.stmid) {
              this.$router.replace(
                `/revenue/unauthorized/hidden/${this.accountid}/list/${this.offset_account}/v/${this.pagination.page}/${this.record.statement_id}`
              );
            }
            this.record_found = true;
          } else {
            this.record_not_found = true;
          }
        })
        .catch((error) => {
          this.closeLoading();
          console.log(error);
        });
    },
    submitResponse: function () {
      if (!this.hasdata(this.review_type)) {
        this.$vs.notify({
          title: "Error!!!",
          text: `Please select response type`,
          sticky: true,

          color: "danger",
          duration: null,
          position: "bottom-left",
        });
        return;
      }
      if (this.review_type == "Authorized Transfer") {
        if (!this.hasdata(this.account_name)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide account name`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(this.account_number)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide account number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        } else if (this.account_number.indexOf("-") !== -1) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide valid account number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(String(this.date))) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide date`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        this.reference_no = "";
      } else if (this.review_type == "Reversal") {
        if (!this.hasdata(this.reference_no)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide reference number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(String(this.date))) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please select credit date`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        this.account_name = "";
        this.account_number = "";
      } else {
        this.reference_no = "";
        this.date = "";
        this.comments = "";
        this.account_name = "";
        this.account_number = "";
      }
      var link = "submitresponse";
      if (this.isEdited) {
        link = "updateresponse";
      }
      this.showLoading("Submiting response, please wait");
      this.post("/hiddenunauthorized/" + link, {
        id: this.responseID,
        stmid: this.record.statement_id,
        file: this.file,
        account_name: this.account_name,
        account_number: this.account_number,
        user_id: this.AppActiveUser.id,
        account_from: this.accountid,
        review_type: this.review_type,
        reference_no: this.reference_no,
        date: this.date,
        reviewedby: "org",
        comments: this.comments,
      })
        .then((response) => {
          console.log(response.data);
          this.closeLoading();
          if (response.data.success == true) {
            this.responded = true;
            this.canshowBtn = true;
            this.isEdited = true;
            this.reviews = response.data.reviews;
            Swal.fire("Response submited!", response.data.message, "success");
            this.currentPage++;
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          Swal.fire("Failed!", error.message, "error");
          console.log(error);
        });
    },
    deleteMedia: function () {
      this.showLoading("Deleting file, please wait");
      this.post("/hiddenunauthorized/delete_file", {
        oldfile: this.hasdata(this.file) ? this.file : null,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.file = null;
            Swal.fire("Removed!", response.data.message, "success");
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          console.log(error);
          Swal.fire("Failed!", error.message, "error");
        });
    },
    submitResponse_bog: function () {
      if (!this.hasdata(this.review_type_bog)) {
        this.$vs.notify({
          title: "Error!!!",
          text: `Please select response type`,
          sticky: true,

          color: "danger",
          duration: null,
          position: "bottom-left",
        });
        return;
      }
      if (this.review_type_bog == "Authorized Transfer") {
        if (!this.hasdata(this.account_name_bog)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide account name`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(this.account_number_bog)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide account number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        } else if (this.account_number_bog.indexOf("-") !== -1) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide valid account number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(String(this.date_bog))) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide date`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        this.reference_no_bog = "";
      } else if (this.review_type_bog == "Reversal") {
        if (!this.hasdata(this.reference_no_bog)) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please provide reference number`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        if (!this.hasdata(String(this.date_bog))) {
          this.$vs.notify({
            title: "Error!!!",
            text: `Please select credit date`,
            sticky: true,

            color: "danger",
            duration: null,
            position: "bottom-left",
          });
          return;
        }
        this.account_name_bog = "";
        this.account_number_bog = "";
      } else {
        this.reference_no_bog = "";
        this.date_bog = "";
        this.comments_bog = "";
        this.account_name_bog = "";
        this.account_number_bog = "";
      }
      var link = "submitresponse";
      if (this.isEdited_bog) {
        link = "updateresponse";
      }
      this.showLoading("Submiting response, please wait");
      this.post("/hiddenunauthorized/" + link, {
        id: this.responseID_bog,
        stmid: this.record.statement_id,
        file: this.file_bog,
        account_name: this.account_name_bog,
        account_number: this.account_number_bog,
        user_id: this.AppActiveUser.id,
        account_from: this.accountid,
        review_type: this.review_type_bog,
        reference_no: this.reference_no_bog,
        date: this.date_bog,
        reviewedby: "bog",
        comments: this.comments_bog,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.responded_bog = true;
            this.canshowBtn_bog = true;
            this.isEdited_bog = true;
            this.reviews = response.data.reviews;
            Swal.fire("Response submited!", response.data.message, "success");
            this.currentPage++;
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          Swal.fire("Failed!", error.message, "error");
          console.log(error);
        });
    },
    uploadCompleted: function (data) {
      if (data.success == true) {
        this.file = data.relative;
        Swal.fire("Uploaded!", data.message, "success");
      } else {
        Swal.fire("Failed!", data.message, "error");
      }
    },
    uploadCompleted_bog: function (data) {
      if (data.success == true) {
        this.file_bog = data.relative;
        Swal.fire("Uploaded!", data.message, "success");
      } else {
        Swal.fire("Failed!", data.message, "error");
      }
    },
    deleteMedia_bog: function () {
      this.showLoading("Deleting file, please wait");
      this.post("/hiddenunauthorized/delete_file", {
        oldfile: this.hasdata(this.file_bog) ? this.file_bog : null,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.file = null;
            Swal.fire("Removed!", response.data.message, "success");
          } else {
            Swal.fire("Failed!", response.data.message, "error");
          }
        })
        .catch((error) => {
          this.closeLoading();
          console.log(error);
          Swal.fire("Failed!", error.message, "error");
        });
    },
    sendtobogNow: function () {
      this.showLoading("Sendong infraction to BOG, please wait");
      this.post("/hiddenunauthorized/send_to_bog", {
        stmid: this.record.statement_id,
        acc_id: this.accountid,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire("Completed!", "Infraction sent to BOG.", "success");
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
    unhideWarn: function (account_id, offset_account) {
      Swal.fire({
        title: "Are you sure?",
        text: "This Infraction will be made visible to organizations!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, unhide it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.unhide([this.record.statement_id]);
        }
      });
    },
    unhide: function (ids) {
      this.showLoading("Showing unauthorized Infraction(s), please wait");
      this.post("/hiddenunauthorized/unhide", {
        id: ids,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            Swal.fire(
              "Done!",
              "The unauthorized Inraction has been made visible",
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
  },
};
</script>

<style lang="scss">
.review-bg{
  background: #fafcff;
}
</style>