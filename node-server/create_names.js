var fs = require('fs');
var XLSX = require('xlsx');
// const jsonDataIn = fs.readFileSync('C:\\Users\\dsave\\Desktop\\OutflowData_19-06-2021.json')
// const inletData = JSON.parse(jsonDataIn);

// var newFiledata = [];
// for (var dd = 0; dd < inletData.length; dd++) {
//     const data = inletData[dd];

//     if (!newFiledata.includes(data.DepotName)) {
//         newFiledata.push(data.DepotName);
//     }
// }

// fs.writeFileSync('C:\\Users\\dsave\\Desktop\\newfile.json', JSON.stringify(newFiledata, null, 2));


// var workbook = XLSX.readFile("C:\\Users\\dsave\\Downloads\\03.Petroleum BOE Report.xlsx", {
//     // dateNF: "DD-MMM-YYYY",
//     header: 1,
//     defval: "",
//     cellDates: true,
//     cellNF: true,
//     raw: true,
//     dateNF: 'yyyy-mm-dd;@'
// });

// var excelFile = [];
// var pages = workbook.SheetNames;
// for (var i = 0; i < pages.length; i++) {
//     var name = pages[i];
//     excelFile.push(XLSX.utils.sheet_to_row_object_array(workbook.Sheets[name], {
//         raw: false,
//         range: 4,
//         dateNF: 'yyyy-mm-dd;@'
//     }));
// }
// var excelFile = [].concat.apply([], excelFile);

// var newFiledata = {};
// for (var dd = 0; dd < excelFile.length; dd++) {
//     const data = excelFile[dd];
//     var omc_name = `${data.DECLARANT_NAME}`.toLocaleLowerCase().trim();
//     if (!newFiledata.hasOwnProperty(omc_name)) {
//         newFiledata[omc_name] = { name: data.DECLARANT_NAME, tin: data.DECLARANT_TIN }
//     }
// }
// fs.writeFileSync('C:\\Users\\dsave\\Desktop\\OMCNames.json', JSON.stringify(newFiledata, null, 2));

var workbook = XLSX.readFile("C:\\Users\\dsave\\Downloads\\04.Petroleum Order Report.xlsx", {
    // dateNF: "DD-MMM-YYYY",
    header: 1,
    defval: "",
    cellDates: true,
    cellNF: true,
    raw: true,
    dateNF: 'yyyy-mm-dd;@'
});

var excelFile = [];
var pages = workbook.SheetNames;
for (var i = 0; i < pages.length; i++) {
    var name = pages[i];
    excelFile.push(XLSX.utils.sheet_to_row_object_array(workbook.Sheets[name], {
        raw: false,
        range: 3,
        dateNF: 'yyyy-mm-dd;@'
    }));
}
var excelFile = [].concat.apply([], excelFile);

var newFiledata = {};
for (var dd = 0; dd < excelFile.length; dd++) {
    const data = excelFile[dd];
    var omc_name = `${data.BDC_NAME}`.toLocaleLowerCase().trim();
    if (!newFiledata.hasOwnProperty(omc_name)) {
        const code = +new Date() + dd;
        newFiledata[omc_name] = { name: data.BDC_NAME, code: `DBC${code}` }
    }
}
fs.writeFileSync('C:\\Users\\dsave\\Desktop\\DBCnames.json', JSON.stringify(newFiledata, null, 2));