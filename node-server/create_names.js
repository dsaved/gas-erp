var fs = require('fs');
var XLSX = require('xlsx');

// ??DEPOTS FROM SML
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


//PRODUCT NAME
// var workbook = XLSX.readFile("C:\\Users\\dsave\\Desktop\\errored-file1628097331769.xlsx", {
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
//         // range: 4,
//         dateNF: 'yyyy-mm-dd;@'
//     }));
// }
// var excelFile = [].concat.apply([], excelFile);

// var newFiledata = {};
// for (var dd = 0; dd < excelFile.length; dd++) {
//     const data = excelFile[dd];
//     var product_name = `${data.product_name}`.toLocaleLowerCase().trim();
//     if (!newFiledata.hasOwnProperty(product_name)) {
//         const code = +new Date() + dd;
//         newFiledata[product_name] = { name: data.product_name, code: data.product_cd }
//     }
// }
// fs.writeFileSync('C:\\Users\\dsave\\Desktop\\Products.json', JSON.stringify(newFiledata, null, 2));

//DEPOT NAME
// var workbook = XLSX.readFile("C:\\Users\\dsave\\Desktop\\errored-file1628092154903.xlsx", {
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
//         // range: 4,
//         dateNF: 'yyyy-mm-dd;@'
//     }));
// }
// var excelFile = [].concat.apply([], excelFile);

// var newFiledata = {};
// for (var dd = 0; dd < excelFile.length; dd++) {
//     const data = excelFile[dd];
//     var depot_name = `${data.depot_name}`.toLocaleLowerCase().trim();
//     if (!newFiledata.hasOwnProperty(depot_name)) {
//         const code = +new Date() + dd;
//         newFiledata[depot_name] = { name: data.depot_name }
//     }
// }
// fs.writeFileSync('C:\\Users\\dsave\\Desktop\\Depots.json', JSON.stringify(newFiledata, null, 2));

//OMC NAMES
var workbook = XLSX.readFile("C:\\Users\\dsave\\Desktop\\errored-file1628193408123.xlsx", {
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
        // range: 4,
        dateNF: 'yyyy-mm-dd;@'
    }));
}
var excelFile = [].concat.apply([], excelFile);

var newFiledata = {};
for (var dd = 0; dd < excelFile.length; dd++) {
    const data = excelFile[dd];
    var omc_name = `${data.declarant_name}`.toLocaleLowerCase().trim();
    if (!newFiledata.hasOwnProperty(omc_name)) {
        newFiledata[omc_name] = { name: data.declarant_name, tin: data.declarant_tin }
    }
}
fs.writeFileSync('C:\\Users\\dsave\\Desktop\\OMCNames.json', JSON.stringify(newFiledata, null, 2));


//BDC NAMES
var workbook = XLSX.readFile("C:\\Users\\dsave\\Desktop\\errored-file1628193408123.xlsx", {
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
        // range: 3,
        dateNF: 'yyyy-mm-dd;@'
    }));
}
var excelFile = [].concat.apply([], excelFile);

var newFiledata = {};
for (var dd = 0; dd < excelFile.length; dd++) {
    const data = excelFile[dd];
    var omc_name = `${data.bdc_name}`.toLocaleLowerCase().trim();
    if (!newFiledata.hasOwnProperty(omc_name)) {
        const code = +new Date() + dd;
        newFiledata[omc_name] = { name: data.bdc_name, code: `DBC${code}` }
    }
}
fs.writeFileSync('C:\\Users\\dsave\\Desktop\\DBCnames.json', JSON.stringify(newFiledata, null, 2));