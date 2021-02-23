
<template>
  <div id="omc-org-view">
    <vs-alert
      color="danger"
      title="OMC Not Found"
      :active.sync="user_not_found"
    >
      <span>OMC record with id: {{ omcid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'omc-list' }" class="text-inherit underline"
          >All OMCs</router-link
        >
      </span>
    </vs-alert>

    <div id="omc-data" v-if="user_found">
      <vx-card :title="omc.name + ' liftings'">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="md:flex my-3">
            <div class="md:w-1/5 sm:w-full mx-1">
              <form>
                <div class="vx-row">
                  <input
                    id="uploadLiftingsOMC"
                    hidden
                    :multiple="false"
                    type="file"
                    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                    @change="proccessFile($event)"
                  />
                  <vs-button
                    color="dark"
                    icon-pack="feather"
                    icon="icon-upload"
                    v-if="canAdd()"
                    @click="chooseFiles()"
                  >
                    Select Liftings
                  </vs-button>
                </div>
              </form>
            </div>
            <vs-spacer />
            <div class="md:w-1/5 sm:w-full mx-1">
              <vs-button
                color="dark"
                icon-pack="feather"
                icon="icon-file"
                v-if="sortedRecords"
                @click="exportPDF()"
              >
                Export PDF
              </vs-button>
            </div>
            <!-- <div class="md:w-1/5 sm:w-full">
              <v-select
                placeholder="Year From"
                :options="year"
                class="w-full"
                :dir="dir"
                :clearable="false"
                v-model="fromyear"
                :filterable="false"
                :multiple="false"
              />
            </div>
            <div class="md:w-1/5 sm:w-full mx-1">
              <v-select
                placeholder="Year To"
                :options="year"
                :dir="dir"
                class="w-full"
                :clearable="false"
                v-model="toyear"
                :filterable="false"
                :multiple="false"
              />
            </div>
            <div class="md:w-1/5 sm:w-full">
              <vs-button
                color="primary"
                icon-pack="feather"
                @click="popupActive = true"
                icon="icon-filter"
                >Filter</vs-button
              >
            </div> -->
          </header>
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

      <section id="pdf-content">
        <!-- PDF Content Here -->
        <vx-card class="my-5" v-if="sortedRecords">
          <!-- product liftings -->
          <h3>
            <b>{{ omc.name }}</b>
          </h3>
          <div
            v-for="(rec, index) in sortedRecords"
            :key="index + 'lift'"
            class="mt-5"
          >
            <div class="vs-component vs-con-table stripe vs-table-primary mb-5">
              <div class="con-tablex vs-table--content">
                <h4>
                  <b>{{ rec.product }}</b>
                </h4>
                <div class="vs-con-tbody vs-table--tbody">
                  <table class="vs-table vs-table--tbody-table">
                    <thead class="vs-table--thead">
                      <tr>
                        <th scope="col">Tax</th>
                        <th scope="col">Quantity</th>
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
                        <td class="text-right">GHS{{ record.amount }}</td>
                      </tr>
                      <tr
                        class="tr-values vs-table--tr tr-table-state-null selected"
                      >
                        <td>Sub Total</td>
                        <td></td>
                        <td class="text-right">
                          <b>GHS {{ rec.total }}</b>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- receipt -->
          <h3 class="mtop5"><b>RECEIPTS</b></h3>
          <div
            v-for="(rec, index) in receipts"
            :key="index + 'receipt'"
            class="mt-5"
          >
            <div class="vs-component vs-con-table stripe vs-table-primary mb-5">
              <div class="con-tablex vs-table--content">
                <h4>
                  <b>{{ rec.name }}</b>
                </h4>
                <div class="vs-con-tbody vs-table--tbody">
                  <table class="vs-table vs-table--tbody-table">
                    <thead class="vs-table--thead">
                      <tr>
                        <th scope="col">Bank</th>
                        <th scope="col" class="text-right">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="(record, index) in rec.payments"
                        :key="index"
                        class="tr-values vs-table--tr tr-table-state-null selected"
                      >
                        <td>{{ record.bank | title }}</td>
                        <td class="text-right">GHS {{ record.amount }}</td>
                      </tr>
                      <tr
                        class="tr-values vs-table--tr tr-table-state-null selected"
                      >
                        <td>Sub Total</td>
                        <td class="text-right">
                          <b
                            >GHS
                            {{
                              parseFloat(rec.subtotal)
                                .toFixed(2)
                                .replace(/\d(?=(\d{3})+\.)/g, "$&,")
                            }}</b
                          >
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="mtop5">
            <div class="vs-component vs-con-table stripe vs-table-primary mb-5">
              <div class="con-tablex vs-table--content">
                <div class="vs-con-tbody vs-table--tbody">
                  <table class="vs-table vs-table--tbody-table">
                    <thead class="vs-table--thead">
                      <tr>
                        <th scope="col">Grand Total</th>
                        <th scope="col">Receipt Total</th>
                        <th scope="col">Remaining Balance</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        class="tr-values vs-table--tr tr-table-state-null selected"
                      >
                        <td>
                          <b>GHS {{ total }}</b>
                        </td>
                        <td>
                          <b>GHS {{ receipt_total_amount }}</b>
                        </td>
                        <td>
                          <b>GHS {{ remaining_balance }}</b>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <strong
              >Remaining payment balance is GHS {{ remaining_balance }}</strong
            >
          </div>
        </vx-card>
      </section>
    </div>
  </div>
</template>

<script>
// Import Swal
import XLSX from "xlsx";
import jsPDF from "jspdf";
import html2canvas from 'html2canvas'

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
  beforeRouteLeave(to, from, next) {
    if (this.statuscheck) {
      clearInterval(this.statuscheck);
      this.statuscheck = null;
    }
    next();
  },
  props: {
    omcid: {
      type: String / Number,
      default: 0,
    },
  },
  data() {
    return {
      user_not_found: false,
      user_found: false,
      omc: {},
      //receipt data list starts here
      pkey: "omc-orgreceipt-lifting-list-key",
      message: "",
      total: "",
      receipt_total_amount: "",
      remaining_balance: "",
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
      liftings: [],
      computes: [],
      search: "",
      records: [],
      receipts: [],
      search_timer: null,
      fromyear: "",
      toyear: "",
    };
  },
  components: {},
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
    year: function () {
      let year = [];
      for (let index = 1990; index < new Date().getFullYear(); index++) {
        year.push(index);
      }
      return year;
    },
  },
  mounted: function () {
    this.getData();
  },
  watch: {},
  methods: {
    exportPDF() {
      const vm = this;
      this.showLoading("getting file ready for download");
      const pageC = document.getElementById("pdf-content")
      setTimeout(() => {
        html2canvas(pageC, {
          logging: false,
        }).then((canvas) => {
          let pdf = new jsPDF("p", "mm", "a4"); //A4 portrait
          let ctx = canvas.getContext("2d");
          let a4w = 190; //A4 size, 210mm x 297mm, with custom margins on each side, display area 190x260
          let a4h = 260;
          let imgHeight = Math.floor((a4h * canvas.width) / a4w); //Convert the pixel height of a page image according to the A4 display ratio
          let renderedHeight = 0;
          while (renderedHeight < canvas.height) {
            let page = document.createElement("canvas");
            page.width = canvas.width;
            page.height = Math.min(imgHeight, canvas.height - renderedHeight); //Less than one page
            //Crop the specified area with getImageData and draw it to the canvas object created earlier
            var a = page.getContext("2d");

            a.putImageData(
              ctx.getImageData(
                0,
                renderedHeight,
                canvas.width,
                Math.min(imgHeight, canvas.height - renderedHeight)
              ),
              0,
              0
            );
            pdf.addImage(
              page.toDataURL("image/jpeg", 2.0),
              "JPEG",
              10,
              10,
              a4w,
              Math.min(a4h, (a4w * page.height) / page.width)
            ); //Add image to the page, keep 10mm margin

            renderedHeight += imgHeight;
            if (renderedHeight < canvas.height){ pdf.addPage();} //If there is content later, add an empty page
            // delete page;
          }
          vm.closeLoading();
          pdf.save("report.pdf");
        });
      }, 300);
    },
    getLiftings() {
      this.loading = true;
      this.post("/liftings/", {
        computes: this.computes,
        id: this.omcid,
      })
        .then((response) => {
          this.loading = false;
          this.records = response.data.computes;
          this.total = response.data.total;
          this.receipts = response.data.receipts;
          this.receipt_total_amount = response.data.receipt_total_amount;
          this.remaining_balance = response.data.remaining_balance;
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
    getData() {
      this.showLoading("getting OMC infomation");
      this.post("/omc/get", {
        id: this.omcid,
      })
        .then((response) => {
          this.closeLoading();
          if (response.data.success == true) {
            this.user_found = true;
            this.omc = response.data.omcs[0];
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
    //file import function starts here
    chooseFiles() {
      try {
        document.getElementById("uploadLiftingsOMC").click();
      } catch (error) {}
    },
    proccessFile(e) {
      this.loading = true;
      const vm = this;
      var files = e.target.files,
        f = files[0];
      var reader = new FileReader();
      reader.onload = function (e) {
        var data = new Uint8Array(e.target.result);
        var workbook = XLSX.read(data, { type: "array" });
        vm.liftings = XLSX.utils.sheet_to_row_object_array(
          workbook.Sheets[workbook.SheetNames[0]],
          { raw: false }
        );
        /* DO SOMETHING WITH workbook HERE */
        var excelData = [];
        vm.liftings.forEach((compute) => {
          excelData.push(compute);
        });

        function set_header(value) {
          value = value.toString().trim();
          value = value.split(" ").join("_");
          value = value.split("-").join("_");
          value = value.replace(/\./, "");
          return value.toLowerCase();
        }

        vm.computes = [];
        excelData.forEach((excel, index) => {
          var newObject = {};
          for (var i in excel) {
            var newIndx = set_header(i);

            var value = excel[i].replace(/,/g, "");
            newObject[newIndx] = value;
            if (newIndx == "amount") {
              var value = excel[i].replace(/,/g, "");
              value = value.split(" ").join("");
              if (value.includes("(") && value.includes(")")) {
                value = value.split("(").join("-");
                value = value.split(")").join("");
              }
              newObject[newIndx] = value;
            }
            newObject["location"] = index + 2;
          }
          vm.computes.push(newObject);
        });
        vm.getLiftings();
      };
      reader.readAsArrayBuffer(f);
    },
  },
};
</script>

<style lang="css" scoped>
table,
th,
td {
  border: solid 1px #626262;
  padding: 5px;
}
table {
  border-collapse: collapse;
}

thead tr {
  background: #c8c8c8;
}
.vs-table--thead {
  position: unset;
}
.mtop5 {
  margin-top: 5.5rem !important;
}
</style>