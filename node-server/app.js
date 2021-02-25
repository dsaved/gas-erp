var module = require('./config');
var config = module.configs;
var mysql = require('mysql');
var workerFarm = require('worker-farm'),
    workers = workerFarm(require.resolve('./execute'), ['reconcile', 'reconcileOMC', 'importFile', 'exportFile', 'exportFileFallout', 'exportFileLog', 'importReceiptFile']),
    maxJob = 4,
    currentJobR = 0,
    currentJobExport = 0,
    currentReceiptJobIm = 0,
    currentJobIm = 0

var isSqlConnected = false;
var sqlConn;

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
        config.log('worker thread initialized');
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
                    var sqlQuery = "UPDATE `reconcilation_status` SET `processing`='processing', status='initializing', description='reconcilation job  accepted - " +
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

function getTime() {
    var today = new Date();
    var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
    return time;
}