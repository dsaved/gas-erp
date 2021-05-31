var module = require('./config');
var config = module.configs;
var mysql = require('mysql');
const helpers = require('./functions/helper.js')
var workerFarm = require('worker-farm'),
    workers = workerFarm(require.resolve('./execute'), [
        'reconcile',
        'reconcileOMC',
        'compilePenalty',
        'importFile',
        'importReceiptFile',
        'importReceiptFileGhanaGov',
        'importManifest',
        'importDeclaration',
        'importOrders',
        'importPreorders',
        'importWaybills',
        'importICUMSDeclarations',
        'exportFile',
        'exportFileFallout',
        'exportFileNASummary',
        'exportFilePenalty',
        'exportFileLog'
    ]),
    maxJob = 4,
    currentJobR = 0,
    currentJobExport = 0,
    currentReceiptJobIm = 0,
    currentJobIm = 0

var isSqlConnected = false;
var sqlConn;
const Type = { IN: 0, OUT: 1 }

var fs = require('fs')
var path = require('path')

const configPath = path.join(process.cwd(), 'config.json');
fs.readFile(configPath, (error, db_config) => {
    if (error) { console.log(error); return; }
    // create mysql connection to database
    sqlConn = mysql.createConnection(JSON.parse(db_config));
    sqlConn.connect(function(err) {
        if (err) config.log(err);
        isSqlConnected = true;
        config.log('mySql connected');
        initialize();
        setInterval(() => {
            initialize();
        }, 2000);
        // setInterval(() => {
        //     pump_product(Type.IN);
        // }, 5000);
        // setInterval(() => {
        //     pump_product(Type.OUT);
        // }, 5000);
        config.log('worker thread initialized');


        //check missing or undeclared products
        var checkingundcl = false;
        var checkingmissing = false;
        setInterval(async() => {
            if (!checkingmissing) {
                checkingmissing = true;
                await helpers.missingProduct(sqlConn);
                checkingmissing = false;
            }
            if (!checkingundcl) {
                checkingundcl = true;
                await helpers.undeclaredProduct(sqlConn);
                checkingundcl = false;
            }
        }, 1000 * 3);

        //alert no sell for a week
        setInterval(() => {
            var time = getTime()
            if (time === "06:00:00") {
                helpers.alarmNosaleInWeek(sqlConn);
            }
        }, 10000);
    });
});

function initialize() {
    startReconcilation();
    fileImport();
    fileReceiptImport();
    fileExport();
    fileExportLog();
}

function startReconcilation() {
    if (currentJobR < maxJob) {
        var sqlQuery = "SELECT * FROM `reconcilation_status` WHERE `processing`='pending' LIMIT 1";
        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
            if (err) {
                config.log(err);
            } else {
                if (typeof(chatsHid[0]) != "undefined" && chatsHid[0] != null) {
                    var childJobDescription = chatsHid[0];
                    var sqlQuery = "UPDATE `reconcilation_status` SET `processing`='processing', status='initializing', description='reconciliation job  accepted - " +
                        getTime() + "' WHERE `processing`='pending' AND `id`=" + childJobDescription.id;
                    sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                        if (err) {
                            config.log(err);
                        } else {
                            if (childJobDescription.bank_type === "omc") {
                                workers.reconcileOMC(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobR--;
                                    }
                                })
                                currentJobR++;
                            } else if (childJobDescription.bank_type === "compile") {
                                workers.compilePenalty(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                    }
                                })
                            } else {
                                workers.reconcile(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobR--;
                                    }
                                })
                                currentJobR++;
                            }
                        }
                    });
                }
            }
        });
    }
}

function fileImport() {
    if (currentJobIm < maxJob) {
        var sqlQuery = "SELECT * FROM `file_upload_status` WHERE `processing`='pending' LIMIT 1";
        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
            if (err) {
                config.log(err);
            } else {
                if (typeof(chatsHid[0]) != "undefined" && chatsHid[0] != null) {
                    var childJobDescription = chatsHid[0];
                    var sqlQuery = "UPDATE `file_upload_status` SET `processing`='processing', status='initializing', description='importation job accepted - " +
                        getTime() + "' WHERE `processing`='pending' AND `id`=" + childJobDescription.id;
                    sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                        if (err) {
                            config.log(err);
                        } else {
                            workers.importFile(childJobDescription, function(err, result) {
                                if (result.isDone) {
                                    process.kill(result.id);
                                    currentJobIm--;
                                }
                            })
                            currentJobIm++;
                        }
                    });
                }
            }
        });
    }
}

function fileReceiptImport() {
    if (currentReceiptJobIm < maxJob) {
        var sqlQuery = "SELECT * FROM `file_upload_receipt_status` WHERE `processing`='pending' LIMIT 1";
        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
            if (err) {
                config.log(err);
            } else {
                if (typeof(chatsHid[0]) != "undefined" && chatsHid[0] != null) {
                    var childJobDescription = chatsHid[0];
                    var sqlQuery = "UPDATE `file_upload_receipt_status` SET `processing`='processing', status='initializing', description='importation job accepted - " +
                        getTime() + "' WHERE `processing`='pending' AND `id`=" + childJobDescription.id;
                    if (childJobDescription.type === "receipt") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importReceiptFile(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "ghana_gov_omc_receipt") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importReceiptFileGhanaGov(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "manifest_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importManifest(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "declaration_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importDeclaration(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "petroleum_order_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importOrders(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "petroleum_preorder_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importPreorders(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "waybill_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importWaybills(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    } else if (childJobDescription.type === "petroleum_icums_declaration_imp") {
                        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                            if (err) {
                                config.log(err);
                            } else {
                                workers.importICUMSDeclarations(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentReceiptJobIm--;
                                    }
                                })
                                currentReceiptJobIm++;
                            }
                        });
                    }
                }
            }
        });
    }
}

function fileExport() {
    if (currentJobExport < maxJob) {
        var sqlQuery = "SELECT * FROM `file_export_status` WHERE `processing`='pending' LIMIT 1";
        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
            if (err) {
                config.log(err);
            } else {
                if (typeof(chatsHid[0]) != "undefined" && chatsHid[0] != null) {
                    var childJobDescription = chatsHid[0];
                    var sqlQuery = "UPDATE `file_export_status` SET `processing`='processing', status='initializing', description='Export File job accepted - " +
                        getTime() + "' WHERE `processing`='pending' AND `id`=" + childJobDescription.id;
                    sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                        if (err) {
                            config.log(err);
                        } else {
                            if (childJobDescription.export_type === "OMC-FALLOUT") {
                                workers.exportFileFallout(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "PENALTY") {
                                workers.exportFilePenalty(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "NA-SUMARY") {
                                workers.exportFileNASummary(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else {
                                workers.exportFile(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            }
                            currentJobExport++;
                        }
                    });
                }
            }
        });
    }
}

function fileExportLog() {
    if (currentJobExport < maxJob) {
        var sqlQuery = "SELECT * FROM `file_export_status_logs` WHERE `processing`='pending' LIMIT 1";
        sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
            if (err) {
                config.log(err);
            } else {
                if (typeof(chatsHid[0]) != "undefined" && chatsHid[0] != null) {
                    var childJobDescription = chatsHid[0];
                    var sqlQuery = "UPDATE `file_export_status_logs` SET `processing`='processing', status='initializing', description='Ecport File job accepted - " +
                        getTime() + "' WHERE `processing`='pending' AND `id`=" + childJobDescription.id;
                    sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                        if (err) {
                            config.log(err);
                        } else {
                            workers.exportFileLog(childJobDescription, function(err, result) {
                                if (result.isDone) {
                                    process.kill(result.id);
                                    currentJobExport--;
                                }
                            })
                            currentJobExport++;
                        }
                    });
                }
            }
        });
    }
}

function pump_product(type) {
    var XLSX = require('xlsx');
    var excelData = [];
    var workbook = XLSX.readFile('inlet-outlet-test.xlsx', {
        // dateNF: "DD-MMM-YYYY",
        header: 1,
        defval: "",
        cellDates: true,
        cellNF: true,
        raw: true,
        dateNF: 'yyyy-mm-dd;@'
    });
    var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
        // console.log(excelRow);
        /**
         * modify array headers and remove special characters.
         * add the location of the array in the excel file for latter access,
         * incase an error occures
         */
    for (var index = 0; index < excelRow.length; index++) {
        var excel = excelRow[index];
        var newObject = {};
        for (var j in excel) {
            var newIndx = set_header(j);
            var value = excel[j].replace(/,/g, '');
            newObject[newIndx] = value;
            var today = new Date();
            today.setMinutes(today.getMinutes() + index);
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            if (newIndx === "date") {
                value = value.split("/");
                newObject[newIndx] = value[2] + "-" + value[0] + "-" + value[1] + " " + time;
            }
        }
        excelData.push(newObject);
    };

    if (type === Type.IN) {
        console.log("in flow detected");
        var deportsQty = [];
        for (var dd = 0; dd < excelData.length; dd++) {
            var data = excelData[dd];
            var nodepot = true;
            var noBDC = true;
            for (let index = 0; index < deportsQty.length; index++) {
                const element = deportsQty[index];
                if (element.identifyer === `${data.depot}-${data.product_type}`) {
                    nodepot = false;
                    deportsQty[index].volume = Number(element.volume) + Number(data.volume)
                }
                if (element.identifyer === `${data.bdc}-${data.product_type}`) {
                    noBDC = false;
                    deportsQty[index].volume = Number(element.volume) + Number(data.volume)
                }
            }
            if (nodepot) {
                deportsQty.push({ tank: "depot", identifyer: `${data.depot}-${data.product_type}`, product: data.product_type, depot: data.depot, bdc: data.bdc, volume: Number(data.volume) })
            }
            if (noBDC) {
                deportsQty.push({ tank: "bdc", identifyer: `${data.bdc}-${data.product_type}`, product: data.product_type, depot: data.depot, bdc: data.bdc, volume: Number(data.volume) })
            }
            var sqlQuery = "INSERT INTO `petroleum_inlet`(`datetime`, `depot`, `bdc`, `product_type`, `volume`)";
            sqlQuery += " VALUES ('" + data.date + "','" + data.depot + "','" + data.bdc + "','" + data.product_type + "'," + data.volume + ")";
            sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                if (err) console.log(err)
            });
        }

        //create or update Tank for Depot
        for (let deportIndex = 0; deportIndex < deportsQty.length; deportIndex++) {
            const element = deportsQty[deportIndex];
            var size = 90000000;
            var sqlQuery = `INSERT INTO petroleum_tanks (identifyer,depot,bdc,tank,product,volume,full) VALUES ('${element.identifyer}','${element.depot}','${element.bdc}','${element.tank}','${element.product}',${element.volume}, ${size})ON DUPLICATE KEY UPDATE full= ${size},volume = volume + ${element.volume};`;
            sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                if (err) console.log(err)
            });
        }
    }

    if (type === Type.OUT) {
        console.log("out flow detected");
        var deportsQty = [];
        for (var dd = 0; dd < excelData.length; dd++) {
            var data = excelData[dd];
            var nodepot = true;
            var noBDC = true;
            for (let index = 0; index < deportsQty.length; index++) {
                const element = deportsQty[index];
                if (element.identifyer === `${data.depot}-${data.product_type}`) {
                    nodepot = false;
                    deportsQty[index].volume = Number(element.volume) + Number(data.volume)
                    deportsQty[index].time = data.date
                }
                if (element.identifyer === `${data.bdc}-${data.product_type}`) {
                    noBDC = false;
                    deportsQty[index].volume = Number(element.volume) + Number(data.volume)
                    deportsQty[index].time = data.date
                }
            }
            if (nodepot) {
                deportsQty.push({ alarm: "depot", identifyer: `${data.depot}-${data.product_type}`, product: data.product_type, depot: data.depot, bdc: data.bdc, volume: Number(data.volume) })
            }
            if (noBDC) {
                deportsQty.push({ alarm: "bdc", identifyer: `${data.bdc}-${data.product_type}`, product: data.product_type, depot: data.depot, bdc: data.bdc, volume: Number(data.volume) })
            }
            var sqlQuery = "INSERT INTO `petroleum_outlet`(`datetime`, `depot`, `bdc`, `product_type`, `volume`)";
            sqlQuery += " VALUES ('" + data.date + "','" + data.depot + "','" + data.bdc + "','" + data.product_type + "'," + data.volume + ")";
            sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                if (err) console.log(err)
            });
        }

        //create or update Tank for Depot
        for (let deportIndex = 0; deportIndex < deportsQty.length; deportIndex++) {
            const element = deportsQty[deportIndex];

            // get the depot pumping the product and check if the tank is empty
            var sqlQuery = `SELECT * FROM petroleum_tanks WHERE identifyer = '${element.identifyer}' AND volume >= ${element.volume} LIMIT 1`;
            sqlConn.query(sqlQuery, function(err, tank, fields) {
                if (err) {
                    console.log(err)
                } else {
                    // console.log(tank)
                    if (tank.length == 0 || typeof(tank[0]) == "undefined" || tank[0] == null) {
                        //Pumping from empty tank
                        // console.log("Pumping from empty tank", element)
                        var alarmQry = `INSERT INTO petroleum_alarm_notification(time, type, message,depot, bdc, alarm, product, volume) VALUES ('${element.time}','discharge from empty tank','There was a discharge from an empty tank','${element.depot}','${element.bdc}','${element.alarm}','${element.product}',${element.volume})`;
                        sqlConn.query(alarmQry, function(err, tank, fields) {
                            if (err) {
                                console.log(err)
                            }
                        });
                    }
                }
            });

            var size = 90000000;
            var sqlQuery = `INSERT INTO petroleum_tanks (identifyer,depot,bdc,tank,product,volume,full) VALUES ('${element.identifyer}','${element.depot}','${element.bdc}','${element.tank}','${element.product}',0,${size})ON DUPLICATE KEY UPDATE full= ${size}, volume = IF(volume > 0, volume - ${element.volume}, 0);`;
            sqlConn.query(sqlQuery, function(err, chatsHid, fields) {
                if (err) console.log(err)
            });
        }
    }
}

function set_header(value) {
    value = value.toString().trim();
    value = value.split(" ").join("_");
    value = value.split("-").join("_");
    value = value.replace(/\./, '');
    return value.toLowerCase();
}

function getTime(dateTime) {
    function pad(s) { return (s < 10) ? '0' + s : s; }
    if (dateTime) {
        var today = new Date();
        var time = [pad(today.getFullYear()), pad(today.getMonth() + 1), today.getDate()].join('-') + " " + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
    var today = new Date();
    var time = pad(today.getHours()) + ":" + pad(today.getMinutes()) + ":" + pad(today.getSeconds());
    return time;
}