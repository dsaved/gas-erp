
<template>
  <div id="omc-org-view">
    <vs-alert
      color="danger"
      title="OMC Not Found"
      :active.sync="user_not_found"
    >
      <span>OMC record with name: {{ name }} not found. </span>
      <span>
        <span>Check </span
        ><router-link
          :to="{ name: 'waybill-expected-declaration' }"
          class="text-inherit underline"
          >All OMCs</router-link
        >
      </span>
    </vs-alert>

    <div id="omc-data">
      <vx-card :title="name + ' Expected Declarations'">
        <p></p>
        <div class="vs-component vs-con-table stripe vs-table-secondary">
          <header class="md:flex my-3">
            <div class="w-1/5 px-2">
              <span>Show BDC?</span>
              <ajax-select
                placeholder="Select option"
                :options="['Show', 'Hide']"
                :clearable="false"
                :dir="$vs.rtl ? 'rtl' : 'ltr'"
                :selected="show_bdc"
                v-on:update:data="show_bdc = $event"
              />
            </div>
            <vs-spacer />
            <div class="md:w-1/4 sm:w-full mx-1 vx-row">
              <vs-button
                color="dark"
                icon-pack="feather"
                icon="icon-file"
                v-if="sortedRecords"
                class="mr-1"
                @click="_exportPDF()"
              >
                PDF
              </vs-button>
              <vs-button
                color="warning"
                icon-pack="feather"
                icon="icon-file"
                v-if="sortedRecords"
                @click="exportExcel()"
              >
                EXCEL
              </vs-button>
            </div>
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
            <b>{{ name }}</b>
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

          <!-- Total -->
          <div class="mtop5">
            <div class="vs-component vs-con-table stripe vs-table-primary mb-5">
              <div class="con-tablex vs-table--content">
                <div class="vs-con-tbody vs-table--tbody">
                  <table class="vs-table vs-table--tbody-table">
                    <thead class="vs-table--thead">
                      <tr>
                        <th scope="col">Grand Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        class="tr-values vs-table--tr tr-table-state-null selected"
                      >
                        <td>
                          <b>GHS {{ total }}</b>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </vx-card>
      </section>
    </div>
  </div>
</template>

<script>
// Import Swal
import XLSX from "xlsx";

import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;

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
    name: {
      type: String / Number,
      default: "",
    },
  },
  data() {
    return {
      user_not_found: false,
      user_found: false,
      //receipt data list starts here
      pkey: "waybill-expected-declaration-list-key",
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
      show_bdc: "Hide",
    };
  },
  components: {},
  computed: {
    selectAll: {
      get() {
        return this.records
          ? this.selectedRecords.length == this.records.length
          : false;
      },
      set(value) {
        const selected = [];

        if (value) {
          this.records.forEach(function (record) {
            selected.push(record.id);
          });
        }
        this.selectedRecords = selected;
      },
    },
    sortedRecords() {
      try {
        return this.filterObj(this.records, this.search).sort((a, b) => {
          let modifier = 1;
          if (this.currentSortDir === "desc") modifier = -1;
          if (a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
          if (a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
          return 0;
        });
      } catch (error) {
        console.warn(error);
      }
    },
    year() {
      const year = [];
      for (let index = 1990; index < new Date().getFullYear(); index++) {
        year.push(index);
      }
      return year;
    },
  },
  mounted() {
    this.getLiftings();
  },
  watch: {
    show_bdc: function () {
      this.getLiftings();
    },
  },
  methods: {
    exportExcel() {
      const liftings = ["Tax", "Quantity", "Amount"];
      const vm = this;
      this.showLoading("getting file ready for download");

      const exportFiles = [[this.name]];
      this.sortedRecords.forEach((data, index) => {
        exportFiles.push([""]);
        exportFiles.push([""]);
        exportFiles.push([data.product]);
        exportFiles.push(liftings);
        data.computations.forEach((compute, ind) => {
          exportFiles.push([
            compute.tax,
            compute.calculation,
            `GHS ${parseFloat(compute.amount)
              .toFixed(2)
              .replace(/\d(?=(\d{3})+\.)/g, "$&,")}`,
          ]);
        });
        exportFiles.push([
          "Sub total",
          "",
          `GHS ${parseFloat(data.total)
            .toFixed(2)
            .replace(/\d(?=(\d{3})+\.)/g, "$&,")}`,
        ]);
      });

      exportFiles.push([""]);
      exportFiles.push([""]);
      exportFiles.push([""]);
      exportFiles.push(["Grand Total"]);
      exportFiles.push([`GHS ${this.total}`]);

      const workbook = XLSX.utils.book_new();
      workbook.Props = {
        Title: this.name,
        Subject: "OMC REPORT",
        Author: "Strategic Mobilisation Ghana Limited",
        Company: "Strategic Mobilisation Ghana Limited",
        CreatedDate: new Date(),
      };
      workbook.SheetNames.push("report");
      workbook.Sheets["report"] = XLSX.utils.aoa_to_sheet(exportFiles);
      const wopts = { bookType: "xlsx", bookSST: false, type: "binary" };
      const wbout = XLSX.write(workbook, wopts);
      // XLSX.writeFile(workbook, "report.xlsx");
      this.closeLoading();
      this.savebytes([this.s2ab(wbout)], "report.xlsx");
    },
    _exportPDF() {
      this.showLoading("getting file ready for download");
      const docDefinition = {
        footer(currentPage, pageCount) {
          return `${currentPage.toString()} of ${pageCount}`;
        },
        header(currentPage, pageCount, pageSize) {
          return [
            {
              text: "GHANA AUDIT SERVICE",
              alignment: "left",
            },
            {
              canvas: [
                { type: "rect", x: 170, y: 32, w: pageSize.width - 170, h: 40 },
              ],
            },
          ];
        },
        watermark: {
          text: "GHANA AUDIT SERVICE",
          color: "green",
          opacity: 0.1,
          bold: true,
          italics: false,
        },
        content: [],
        styles: {
          header: {
            fontSize: 17,
            bold: true,
          },
          subheader: {
            fontSize: 14,
            bold: true,
          },
          quote: {
            italics: true,
          },
          small: {
            fontSize: 8,
          },
        },
        defaultStyle: {
          fontSize: 12,
          bold: false,
        },
      };

      // ADD OMC TILE TO DOCUMENT
      docDefinition.content.push({
        text: `${this.name}\n\n`,
        style: "header",
      });

      // ADD TAX TO DOCUMENT
      this.sortedRecords.forEach((data, index) => {
        docDefinition.content.push({
          text: data.product,
          style: "subheader",
        });
        const table = {
          table: {
            widths: ["auto", "*", "auto"],
            headerRows: 1,
            body: [
              [
                { text: "Tax", bold: true },
                { text: "Quantity", bold: true },
                { text: "Amount", bold: true },
              ],
            ],
          },
          layout: {
            fillColor(rowIndex, node, columnIndex) {
              return rowIndex === 0 ? "#CCCCCC" : null;
            },
          },
        };
        data.computations.forEach((compute, ind) => {
          table.table.body.push([
            { text: compute.tax, noWrap: true },
            compute.calculation,
            { text: `GHS ${compute.amount}`, noWrap: true },
          ]);
        });
        table.table.body.push(["Sub Total", "", `GHS ${data.total}`]);
        docDefinition.content.push(table);
        //ADD LINE BREAKE
        docDefinition.content.push({
          text: "\n",
        });
      });

      //ADD LINE BREAKE
      docDefinition.content.push({
        text: "\n\n",
      });

      // ADD TOTAL SECTION
      docDefinition.content.push({
        table: {
          widths: ["*"],
          headerRows: 1,
          body: [
            [{ text: "Grand Total", bold: true }],
            [{ text: `GHS ${this.total}` }],
          ],
        },
        layout: {
          fillColor(rowIndex, node, columnIndex) {
            return rowIndex === 0 ? "#CCCCCC" : null;
          },
        },
      });

      const vm = this;
      setTimeout(() => {
        vm.closeLoading();
        pdfMake.createPdf(docDefinition).download();
      }, 300);
    },
    getLiftings() {
      this.loading = true;
      this.post("/waybills/expected_declaration", {
        omc: this.name,
        show_bdc: this.show_bdc,
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
    savebytes: (function () {
      const a = document.createElement("a");
      document.body.appendChild(a);
      a.style = "display: none";
      return function (data, name) {
        let blob = new Blob(data, { type: "octet/stream" }),
          url = window.URL.createObjectURL(blob);
        a.href = url;
        a.download = name;
        a.click();
        window.URL.revokeObjectURL(url);
      };
    })(),
    s2ab(s) {
      const buf = new ArrayBuffer(s.length);
      const view = new Uint8Array(buf);
      for (let i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xff;
      return buf;
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