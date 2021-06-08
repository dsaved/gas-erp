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
        'exportFileLog',
        'exportPetroleumInputAnalysis',
        'exportPetroleumInputReconciliation',
        'exportPetroleumNPAAnalysis',
        'exportPetroleumICUMSDifferences',
        'exportPetroleumDeptGood',
        'exportPetroleumWaybillAnalysis',
        'exportPetroleumWaybillReconcile',
        'exportPetroleumInletReport',
        'exportPetroleumSMLOutletReport',
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
        config.log('worker thread initialized');

        setInterval(() => {
            helpers.download_files();
        }, 5 * 1000);
        setInterval(() => {
            helpers.pump_product(sqlConn);
        }, 20 * 1000);

        initialize();
        setInterval(() => {
            initialize();
        }, 2000);

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
                            } else if (childJobDescription.export_type === "petroleum-import-analysis") {
                                workers.exportPetroleumInputAnalysis(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-import-reconciliation") {
                                workers.exportPetroleumInputReconciliation(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-npa-analysis") {
                                workers.exportPetroleumNPAAnalysis(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-icums-defferences") {
                                workers.exportPetroleumICUMSDifferences(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-good-standing") {
                                workers.exportPetroleumDeptGood(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-waybill-analysis") {
                                workers.exportPetroleumWaybillAnalysis(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-waybill-reconcile") {
                                workers.exportPetroleumWaybillReconcile(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-sml-inletreport") {
                                workers.exportPetroleumInletReport(childJobDescription, function(err, result) {
                                    if (result.isDone) {
                                        process.kill(result.id);
                                        currentJobExport--;
                                    }
                                })
                            } else if (childJobDescription.export_type === "petroleum-sml-outletreport") {
                                workers.exportPetroleumSMLOutletReport(childJobDescription, function(err, result) {
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