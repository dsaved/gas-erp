/**
 * export model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.reconcile = async function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');

    var totalAccount = 0;
    var workingOn = 0;
    var proccessID = process.pid;
    var reconcilingWith = "";

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });


    const start = async() => {
        try {
            /**
             * get the accounts statements for the main accounts
             * @param mainTransNoOffset this hold all the transactions of 
             * the main account  account
             */
            var sqlQuery = "";
            if (data.transtype === "credit") {
                sqlQuery = "SELECT credit_amount, post_date,particulars, id, account_id,offset_acc_no FROM `statements` WHERE `account_id` = " + data.account +
                    " AND (credit_amount > 0 || credit_amount < 0)";
            } else if (data.transtype === "debit") {
                sqlQuery = "SELECT debit_amount, post_date,particulars, id, account_id,offset_acc_no FROM `statements` WHERE `account_id` = " + data.account +
                    " AND (debit_amount > 0 || debit_amount < 0)";
            }
            var mainTransNoOffset = await query(sqlQuery).catch(error => {
                config.log(error);
                updateStatus("error", "job error: could not get results", "completed");
                callback(null, { isDone: true, id: proccessID });
            });
            if (typeof(mainTransNoOffset) == "undefined") {
                await updateStatus("Error", "error: no statements found for main account - " + getTime(), "completed");
                config.log("error: no statements found for main account  - " + getTime());
                return callback(null, { isDone: true, id: proccessID });
            }

            await updateStatus("processing account statements ", "done processing account statements  - " + getTime());
            config.log("done processing account statements  - " + getTime());
            config.log("processing account statements ");
            totalAccount = mainTransNoOffset.length;
            workingOn = 0;
            await asyncForEach(mainTransNoOffset, async(statements, index) => {
                workingOn = index + 1;
                update("reconcilation_status", "id", data.id, {
                    proccessing_account: workingOn,
                    total_account: totalAccount,
                });
                /**
                 * get the transaction in the list of child accounts that its offset account 
                 * matches that of the parent's transaction offset.
                 */
                var stm = "";
                if (data.transtype === "credit") {
                    stm = "SELECT debit_amount, post_date, particulars,id, account_id,offset_acc_no FROM `statements` WHERE `account_id` IN (" + data.ids + ")" +
                        " AND  `debit_amount` = " + statements.credit_amount +
                        " AND  (DATE(`post_date`) BETWEEN '" + statements.post_date + "' AND DATE_ADD('" + statements.post_date + "', INTERVAL " + data.intval + " DAY))" +
                        " AND `status` != 'locked' LIMIT 1;";
                } else if (data.transtype === "debit") {
                    stm = "SELECT credit_amount, post_date, particulars,id, account_id,offset_acc_no FROM `statements` WHERE `account_id` IN (" + data.ids + ")" +
                        " AND  `credit_amount` = " + statements.debit_amount +
                        " AND  (DATE(`post_date`) BETWEEN '" + statements.post_date + "' AND DATE_ADD('" + statements.post_date + "', INTERVAL " + data.intval + " DAY))" +
                        " AND `status` != 'locked' LIMIT 1;";
                }
                var resultStatement = await query(stm).catch(error => {
                    config.log(error);
                    updateStatus("error", "job error: " + error, "completed");
                    callback(null, { isDone: true, id: proccessID });
                });
                if (resultStatement && typeof(resultStatement[0]) != "undefined" && resultStatement[0] != null) {
                    var reslutStm = resultStatement[0];
                    // config.log("statements match: " + reslutStm.id);
                    /**
                     * transaction found therefore lock the transaction
                     * and remove it from the list of unauthorized transaction
                     */
                    var sql = "";
                    if (data.transtype === "credit") {
                        sql = "Call create_log(" + statements.account_id + "," + statements.credit_amount + ",'" + statements.post_date + "','" + statements.particulars + "'," + statements.id + "," + reslutStm.account_id + "," + reslutStm.debit_amount + ",'" + reslutStm.post_date + "','" + reslutStm.particulars + "'," + reslutStm.id + "," + data.intval + ")";
                    } else if (data.transtype === "debit") {
                        sql = "Call create_log(" + statements.account_id + "," + statements.debit_amount + ",'" + statements.post_date + "','" + statements.particulars + "'," + statements.id + "," + reslutStm.account_id + "," + reslutStm.credit_amount + ",'" + reslutStm.post_date + "','" + reslutStm.particulars + "'," + reslutStm.id + "," + data.intval + ")";
                    }
                    await query(sql).catch(error => {
                        config.log(error);
                        updateStatus("error", "job error: cannot update statement for matched transaction", "completed");
                        callback(null, { isDone: true, id: proccessID });
                    });
                } else {
                    // config.log("statements no match");
                    // flag the statement because no record was found
                    var sql = "";
                    if (data.transtype === "credit") {
                        sql = "Call flag_transaction(" + statements.account_id + ",''," + statements.id + "," + statements.credit_amount + "," + data.intval + ")";
                    } else if (data.transtype === "debit") {
                        sql = "Call flag_transaction(" + statements.account_id + ",''," + statements.id + "," + statements.debit_amount + "," + data.intval + ")";
                    }
                    await query(sql).catch(error => {
                        config.log(error);
                        updateStatus("error", "job error: cannot update statement for no match transaction", "completed");
                        callback(null, { isDone: true, id: proccessID });
                    })
                }
            });

            await updateStatus("completed", " reconciliation completed - " + getTime(), "completed");
            config.log("reconciliation completed - " + getTime());
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: " + error.message + " - " + getTime(), "completed");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    // updateStatus("error", "account not found in current job", "completed");
    async function updateStatus(status, desc, jobstatus) {
        await update("reconcilation_status", "id", data.id, {
            description: desc || "",
            reconciling_with: reconcilingWith,
            proccessing_account: workingOn,
            total_account: totalAccount,
            status: status || "",
            processing: jobstatus || "processing"
        });
    }

    function bgQuery(sqlQuery) {
        sqlConn.query(sqlQuery, function(err, result, fields) {
            if (err) {
                config.log(err)
            }
        });
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: update error", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: " + error, "completed");
            callback(null, { isDone: true, id: proccessID });
        })
    }

    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

//OMC Reconcilation Section
exports.reconcileOMC = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var totalAccount = 0;
    var workingOn = 0;
    var proccessID = process.pid;
    var reconcilingWith = "";

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    const start = async() => {
        try {
            /**
             * get the omc receipts for the main accounts
             * @param mainReceiptsData this hold all the receipts of 
             * the main omc with offset account
             */
            var sqlQuery = "SELECT omcr.bank,omcr.bank_id, omcr.id,omcr.amount, omcr.date, o.name as omc_name, o.id as omc_id FROM `omc_receipt` omcr LEFT JOIN omc o ON omcr.omc_id=o.id WHERE `omc_id` = " + data.account +
                " AND amount > 0";
            var mainReceiptsData = await query(sqlQuery).catch(error => {
                config.log(error);
                updateStatus("error", "job error: cannot read OMC receipts", "completed");
                callback(null, { isDone: true, id: proccessID });
            });

            if (typeof(mainReceiptsData) == "undefined") {
                updateStatus("Error", "error: no receipts found for OMC - " + getTime(), "completed");
                config.log("error:  no receipts found for OMC - " + getTime());
                return callback(null, { isDone: true, id: proccessID });
            }


            await updateStatus("processing OMC receipts", "reconciliation started - " + getTime());
            config.log("reconciliation started - " + getTime());
            config.log("processing OMC Receipts");
            totalAccount = mainReceiptsData.length;
            workingOn = 0;
            await asyncForEach(mainReceiptsData, async(receipts, index) => {
                workingOn = index + 1;
                update("reconcilation_status", "id", data.id, {
                    proccessing_account: workingOn,
                    total_account: totalAccount,
                });
                config.log("processing receipts: " + receipts.id);

                /**
                 * useing the bank id find the accounts in the bank and check 
                 */
                var stm = `SELECT credit_amount, post_date,particulars, id, account_id,offset_acc_no FROM statements WHERE account_id IN (SELECT id FROM accounts WHERE bank = ${receipts.bank_id}) ` +
                    `AND  credit_amount = ${receipts.amount} AND  (DATE(post_date) BETWEEN '${receipts.date}' AND DATE_ADD('${receipts.date}', INTERVAL ${data.intval} DAY)) AND receipt_status != 'locked' LIMIT 1;`;
                var resultStatement = await query(stm).catch(error => {
                    config.log(error);
                    updateStatus("error", "job error: internal query failed", "completed");
                    callback(null, { isDone: true, id: proccessID });
                });
                if (resultStatement != null && typeof(resultStatement[0]) != "undefined" && resultStatement[0] != null) {
                    var $found = resultStatement[0];
                    var resultbankQuery = await query(`SELECT acc.name,acc.acc_num1,acc.acc_num2,acc.bank as bank_id, bnk.name as bank_name FROM accounts acc LEFT JOIN banks bnk ON bnk.id=acc.bank WHERE acc.id=${$found.account_id}`).catch(error => {
                        config.log(error);
                    });
                    if (resultbankQuery != null && typeof(resultbankQuery[0]) != "undefined" && resultbankQuery[0] != null) {
                        let accountInfor = resultbankQuery[0];
                        let account_number = accountInfor.acc_num1;
                        if (account_number === null || account_number === "") {
                            account_number = accountInfor.acc_num2;
                        }
                        let omc_amount = receipts.amount;
                        let omc_date = receipts.date;
                        let omc = receipts.omc_name;
                        let omc_id = receipts.omc_id;
                        let bank = accountInfor.bank_name;
                        let bank_id = accountInfor.bank_id;
                        let account = accountInfor.name;
                        let account_id = $found.account_id;
                        let description = $found.particulars;
                        let credit_amount = $found.credit_amount;
                        let creadit_date = $found.post_date;
                        let intVal = data.intval;
                        //Transaction found, therefor lock that childs account transaction
                        var sql = `UPDATE omc_receipt SET omc_receipt.status='done' WHERE omc_receipt.id = ${receipts.id};`;
                        sql += `UPDATE statements SET statements.receipt_status='locked' WHERE statements.id = ${$found.id};`;
                        sql += "INSERT INTO `audits_logs_omc`( `amount`, `date`, `omc`, `bank`, `bank_id`, `account`, `account_id`, `account_number`, `description`, `credit_amount`, `creadit_date`, `intval`, `omc_id`) ";
                        sql += `VALUES (${omc_amount},'${omc_date}','${omc}','${bank}',${bank_id},'${account}',${account_id},'${account_number}','${description}',${credit_amount},'${creadit_date}',${intVal},${omc_id})`;
                        console.log(sql)
                        await query(sql).catch(error => {
                            config.log(error);
                            updateStatus("error", "job error: cannot create log for matched receipt", "completed");
                            callback(null, { isDone: true, id: proccessID });
                        });
                    } else {
                        updateStatus("error", "job error: cannot create log for matched receipt", "completed");
                        callback(null, { isDone: true, id: proccessID });
                    }
                } else {
                    var sql = `UPDATE omc_receipt SET omc_receipt.status='flagged' WHERE omc_receipt.id = ${receipts.id};`;
                    await query(sql).catch(error => {
                        config.log(error);
                        updateStatus("error", "job error: cannot flag receipts", "completed");
                        callback(null, { isDone: true, id: proccessID });
                    })
                }
            });

            await updateStatus("completed", " reconciliation completed - " + getTime(), "completed");
            config.log("reconciliation completed - " + getTime());
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: " + error.message + " - " + getTime(), "completed");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    // updateStatus("error", "account not found in current job", "completed");
    async function updateStatus(status, desc, jobstatus) {
        await update("reconcilation_status", "id", data.id, {
            description: desc || "",
            reconciling_with: reconcilingWith,
            proccessing_account: workingOn,
            total_account: totalAccount,
            status: status || "",
            processing: jobstatus || "processing"
        });
    }

    function bgQuery(sqlQuery) {
        sqlConn.query(sqlQuery, function(err, result, fields) {
            if (err) {
                config.log(err)
            }
        });
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: update error", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: " + error, "completed");
            callback(null, { isDone: true, id: proccessID });
        })
    }

    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importFile = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "debit_amount" || newIndx == "credit_amount" || newIndx == "balance") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    if (newIndx == "offset_acc_no") {
                        var value = excel[i].split("-").join("");
                        newObject[newIndx] = value;
                    }

                    if (newIndx == "particulars") {
                        var value = excel[i].replace(/'/g, "-");
                        value = value.split("'").join("");
                        value = value.split("\"").join("");
                        value = value.replace(/\\\\/g, "");
                        value = value.replace(/\\/g, "");
                        newObject[newIndx] = value;
                    }
                    newObject["account_id"] = data.account_id;
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });

            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var fileCheckError = false,
                fileCheckContinue = false,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 * The excel data contains empty lines with only discription
                 * when this occures add that data to the previous  array and move to the
                 *next array.
                 */
                if (!('post_date' in excelData[i])) {
                    if (excelDataToInsert && typeof(excelDataToInsert[lastKey - 1].particulars) != "undefined") {
                        var particulars = excelDataToInsert[lastKey - 1].particulars;
                        var newpateculars = excelData[i].particulars;
                        excelDataToInsert[lastKey - 1].particulars = particulars + " " + newpateculars;
                        fileCheckContinue = true;
                    }
                    continue;
                }

                /**
                 *When both crdit and debit amount are available 
                 * stop the loop and throw a warning
                 */
                if (Number(excelData[i]['debit_amount']) > 0 && Number(excelData[i]['credit_amount']) > 0) {
                    fileCheckError = true;
                    await updateStatus("Error", "Error: Cannot have both credit and debit in same transaction statement: On line  " + excelData[i]['location']);
                    return callback(null, { isDone: true, id: proccessID });
                }
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            /**
             * using the first data in the system, check if the excel file is the right
             * one to be added to the currrent account
             */
            if (!fileCheckError) {
                total = excelDataToInsert.length;
                var cmpData = excelDataToInsert[0];
                var postDate = cmpData.post_date;
                var debit_amount = cmpData.debit_amount;
                var credit_amount = cmpData.credit_amount;
                var balance = cmpData.balance;

                var dbResult = await query("SELECT balance,reference,post_date FROM `statements` WHERE `account_id`=" + data.account_id + " ORDER BY `id` DESC");

                /**
                 * check if the query returned a result
                 */
                if (dbResult && typeof(dbResult[0]) != "undefined" && dbResult[0] != null) {
                    var result = dbResult[0];

                    /**
                     * check if the last transaction date already exist in the 
                     * the database
                     */
                    if (result.post_date === postDate) {
                        await updateStatus("Error", "Error In statemen. Date already exist. (" + postDate + ") for this account. reference: " + result.reference);
                    }

                    /**
                     * check if the opening balace in the excel is correct
                     */
                    else if (debit_amount > 0 && (result.balance - debit_amount) != balance) {
                        await updateStatus("Error", "Error: Opening balance is incorrect for the uploaded statement. closing balance: " + result.balance + ", opening balance: " + balance + " reference: " + result.reference);
                    }

                    /**
                     * check if the opening balace in the excel is correct
                     */
                    else if (credit_amount > 0 && (result.balance - debit_amount) != balance) {
                        await updateStatus("Error", "Error: Opening balance is incorrect for the uploaded statement. closing balance: " + result.balance + ", opening balance: " + balance + " reference: " + result.reference);
                    }

                    /**
                     * process the excel by inserting the data into the database
                     */
                    else {
                        await updateStatus("Creating Transactions", "Validation successful - " + getTime());
                        await asyncForEach(excelDataToInsert, async(insertal, index) => {
                            current = index + 1;
                            //insert data to db
                            var sqlStm1 = await updateSql("file_upload_status", "id", data.id, { current: current, total: total });
                            var sqlStm2 = await insertSql("statements", insertal);
                            await executeStatement(sqlStm1.toString() + sqlStm2.toString());
                        });
                        await updateStatus("completed", "All done - " + getTime(), "completed");
                    }
                }
                /**
                 * process the excel by inserting the data into the database
                 */
                else {
                    await updateStatus("Creating Transactions", "Validation successful - " + getTime());
                    await asyncForEach(excelDataToInsert, async(insertal, index) => {
                        current = index + 1;
                        //insert data to db
                        var sqlStm1 = await updateSql("file_upload_status", "id", data.id, { current: current, total: total });
                        var sqlStm2 = await insertSql("statements", insertal);
                        await executeStatement(sqlStm1.toString() + sqlStm2.toString());
                    });
                    await updateStatus("completed", "All done - " + getTime(), "completed");
                }
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        if (value.toString().trim().toLowerCase() === "transaction date") {
            value = "Post Date";
        }
        if (value.toString().trim().toLowerCase() === "description") {
            value = "Particulars";
        }
        if (value.toString().trim().toLowerCase().includes("debit")) {
            value = "Debit Amount";
        }
        if (value.toString().trim().toLowerCase().includes("credit")) {
            value = "Credit Amount";
        }
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    async function updateSql(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }
        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val};`;
        return sql;
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing",
            sqlQuery = "UPDATE `file_upload_status` SET " + " `description` = '" + desc + "',  `status` = '" + status + "',  `total` = '" + total + "',  `current` = '" + current + "' , `processing` = '" + jobstatus + "' WHERE `id`=" + data.id;
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: " + error, "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.credit_amount)) {
            data.credit_amount = 0;
        }
        if (isNaN(data.debit_amount)) {
            data.debit_amount = 0;
        }
        if (isNaN(data.balance)) {
            data.balance = 0;
        }
        if (typeof(data.offset_acc_no) == "undefined") {
            data.offset_acc_no = '';
        }
        if (typeof(data.reference) == "undefined") {
            data.reference = '';
        }

        if (typeof(data.particulars) == "undefined") {
            data.particulars = '-';
        }
        var sqlQuery = "INSERT INTO `" + table + "`(`account_id`, `post_date`, `particulars`, `reference`, `value_date`, `debit_amount`, `credit_amount`, `balance`, `offset_acc_no`)  " +
            "VALUES(" + data.account_id + ",'" + data.post_date + "','" + data.particulars + "','" + data.reference + "','" + data.value_date + "'," + data.debit_amount + "," + data.credit_amount + "," + data.balance + ",'" + data.offset_acc_no + "'); ";
        return sqlQuery;
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importReceiptFile = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "amount") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["omc_id"] = data.account_id;
                    newObject["bank_id"] = data.bank_id;
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var newDate = await convertDate(excelData[i]['date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['date'] = newDate;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    var sqlStm1 = await updateSql("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    var sqlStm2 = await insertSql("omc_receipt", insertal);
                    await executeStatement(sqlStm1.toString() + sqlStm2.toString());
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    async function updateSql(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }
        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val};`;
        return sql;
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing",
            sqlQuery = "UPDATE `file_upload_receipt_status` SET " + " `description` = '" + desc + "',  `status` = '" + status + "',  `total` = '" + total + "',  `current` = '" + current + "' , `processing` = '" + jobstatus + "' WHERE `id`=" + data.id;
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: " + error, "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.amount)) {
            data.amount = 0;
        }
        var sqlQuery = "INSERT INTO `" + table + "`(`bank`, `omc_id`, `date`, `declaration_number`, `receipt_number`, `mode_of_payment`,`amount`,`bank_id`)  " +
            "VALUES('" + data.bank + "'," + data.omc_id + ",'" + data.date + "','" + data.declaration_number + "','" + data.receipt_number + "','" + data.mode_of_payment + "'," + data.amount + ",'" + data.bank_id + "'); ";
        return sqlQuery;
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importManifest = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "amount") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var newDate = await convertDate(excelData[i]['arrival_date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['date'] = newDate;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    await update("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    await insertSql("petroleum_manifest", insertal);
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.split("\r").join("");
        value = value.split("\n").join("");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing";

        const udateData = {
            description: desc,
            status: status,
            total: total,
            current: current,
            processing: jobstatus
        };
        await update('file_upload_receipt_status', 'id', data.id, udateData);
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.amount)) {
            data.value = 0;
        }
        const insertData = {
            arrival_date: data.date,
            vessel_name: data.vessel_name,
            vessel_number: data.vessel_number,
            product_type: data.product_type,
            volume: data.volume,
            amount: data.amount,
            exporter_name: data.exporter_name,
            importer_name: data.importer_name,
            ucr_number: data.ucr_number
        };
        await insertIgnore("tax_schedule_products", { name: data.product_type });
        await insertIgnore("bdc", { name: data.importer_name });
        return await insert(table, insertData);
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insertIgnore(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT IGNORE INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importDeclaration = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "amount") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var declaration_date = await convertDate(excelData[i]['declaration_date']);
                var cleared_date = await convertDate(excelData[i]['cleared_date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['declaration_date'] = declaration_date;
                excelData[i]['cleared_date'] = cleared_date;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    await update("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    await insertSql("petroleum_declaration", insertal);
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.split("\r").join("");
        value = value.split("\n").join("");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing";

        const udateData = {
            description: desc,
            status: status,
            total: total,
            current: current,
            processing: jobstatus
        };
        await update('file_upload_receipt_status', 'id', data.id, udateData);
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.amount)) {
            data.value = 0;
        }
        const insertData = {
            declaration_date: data.declaration_date,
            clearing_agent: data.clearing_agent,
            product_type: data.product_type,
            volume: data.volume,
            importer_name: data.importer_name,
            ucr_number: data.ucr_number,
            amount: data.amount,
            hs_code: data.hs_code,
            cleared_date: data.cleared_date,
            idf_application_number: data.idf_application_number,
            idf_amount: data.idf_amount
        };
        await insertIgnore("tax_schedule_products", { name: data.product_type });
        await insertIgnore("bdc", { name: data.importer_name });
        return await insert(table, insertData);
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insertIgnore(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT IGNORE INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importOrders = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "unit_price") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var order_date = await convertDate(excelData[i]['order_date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['order_date'] = order_date;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    await update("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    await insertSql("petroleum_order", insertal);
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.split("\r").join("");
        value = value.split("\n").join("");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing";

        const udateData = {
            description: desc,
            status: status,
            total: total,
            current: current,
            processing: jobstatus
        };
        await update('file_upload_receipt_status', 'id', data.id, udateData);
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.unit_price)) {
            data.unit_price = 0;
        }
        const insertData = {
            order_date: data.order_date,
            product_type: data.product_type,
            transporter: data.transporter,
            reference_number: data.reference_number,
            vehicle_number: data.vehicle_number,
            volume: data.volume,
            unit_price: data.unit_price,
            depot: data.depot,
            bdc: data.bdc,
            omc: data.omc
        };
        await insertIgnore("tax_schedule_products", { name: data.product_type });
        await insertIgnore("bdc", { name: data.bdc });
        await insertIgnore("omc", { name: data.omc });
        await insertIgnore("depot", { name: data.depot });
        return await insert(table, insertData);
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insertIgnore(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT IGNORE INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importPreorders = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "unit_price") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var preorder_date = await convertDate(excelData[i]['preorder_date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['preorder_date'] = preorder_date;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    await update("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    await insertSql("petroleum_preorder", insertal);
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.split("\r").join("");
        value = value.split("\n").join("");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing";

        const udateData = {
            description: desc,
            status: status,
            total: total,
            current: current,
            processing: jobstatus
        };
        await update('file_upload_receipt_status', 'id', data.id, udateData);
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.unit_price)) {
            data.unit_price = 0;
        }
        const insertData = {
            preorder_date: data.preorder_date,
            product_type: data.product_type,
            reference_number: data.reference_number,
            volume: data.volume,
            depot: data.depot,
            bdc: data.bdc,
            omc: data.omc
        };
        await insertIgnore("tax_schedule_products", { name: data.product_type });
        await insertIgnore("bdc", { name: data.bdc });
        await insertIgnore("omc", { name: data.omc });
        await insertIgnore("depot", { name: data.depot });
        return await insert(table, insertData);
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insertIgnore(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT IGNORE INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}


/**
 * import model for file import
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.importWaybills = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var XLSX = require('xlsx');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var excelData = [];
    var excelDataToInsert = [];
    var filePath = data.path;

    const start = async() => {
        try {
            var workbook = XLSX.readFile(filePath, {
                // dateNF: "DD-MMM-YYYY",
                header: 1,
                defval: "",
                cellDates: true,
                cellNF: true,
                raw: true,
                dateNF: 'yyyy-mm-dd;@'
            });
            var excelRow = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[workbook.SheetNames[0]], { raw: false, dateNF: 'yyyy-mm-dd;@' })
            await updateStatus("Cleaning data", "File read successfully - " + getTime());

            /**
             * modify array headers and remove special characters.
             * add the location of the array in the excel file for latter access,
             * incase an error occures
             */
            await asyncForEach(excelRow, async(excel, index) => {
                var newObject = {};
                for (var i in excel) {
                    var newIndx = set_header(i);

                    var value = excel[i].replace(/,/g, '');
                    newObject[newIndx] = value;
                    if (newIndx == "volume") {
                        var value = excel[i].replace(/,/g, '');
                        value = value.split(" ").join("");
                        if (value.includes("(") && value.includes(")")) {
                            value = value.split("(").join("-");
                            value = value.split(")").join("");
                        }
                        newObject[newIndx] = value;
                    }
                    newObject["location"] = index + 2;
                }
                excelData.push(newObject);
            }).catch(error => {
                config.log(error)
                updateStatus("Error", "error cleaning file, it may contain invalid characters");
                callback(null, { isDone: true, id: proccessID });
            });

            /**
             * Delete the uploaded file.
             * reason is for saving space
             */
            fs.unlink(filePath, (err) => {
                if (err) config.log(' Error deleting file:: ' + err);
            });
            await updateStatus("Analyzing data", "done cleaning data - " + getTime());

            var noErrorInFile = true,
                lastKey = 0,
                i = 0;
            for (i = 0; i < excelData.length; i++) {
                /**
                 *validate accounting_date and account must not be empty
                 */
                var date = await convertDate(excelData[i]['date']);
                // if (excelData[i] && (null === excelData[i]['date']) || newDate === 'NaN/NaN/NaN') {
                //     noErrorInFile = false;
                //     await updateStatus("Error", "Error: Invalid date detected On line  " + excelData[i]['location']);
                //     return false;
                // }
                excelData[i]['date'] = date;
                excelDataToInsert.push(excelData[i]);
                lastKey++
            }
            await updateStatus("Validating file", "Analyzing succesful - " + getTime());

            if (noErrorInFile) {
                total = excelDataToInsert.length;

                await updateStatus("Creating Receipts", "Validation successful - " + getTime());
                await asyncForEach(excelDataToInsert, async(insertal, index) => {
                    current = index + 1;
                    //insert data to db
                    await update("file_upload_receipt_status", "id", data.id, { current: current, total: total });
                    await insertSql("petroleum_waybill", insertal);
                });
                await updateStatus("completed", "All done - " + getTime(), "completed");
            }
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: proccessing file eror- " + getTime(), "completed");
        } finally {
            callback(null, { isDone: true, id: proccessID });
            config.log("task done ");
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function convertDate(inputDate) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputDate)
        return [pad(d.getFullYear()), pad(d.getMonth() + 1), d.getDate()].join('/')
    }

    /**
     * Set the excel header to approprate format 
     * @param value the value of the header that needs formating
     */
    function set_header(value) {
        value = value.toString().trim();
        value = value.split(" ").join("_");
        value = value.split("-").join("_");
        value = value.split("\r").join("");
        value = value.split("\n").join("");
        value = value.replace(/\./, '');
        return value.toLowerCase();
    }

    /**
     * update the current job status
     * @param status the current status of the job
     * @param jobstatus the overall status of the job
     */
    async function updateStatus(status, desc, jobstatus) {
        var status = status || "",
            desc = desc || "",
            jobstatus = jobstatus || "processing";

        const udateData = {
            description: desc,
            status: status,
            total: total,
            current: current,
            processing: jobstatus
        };
        await update('file_upload_receipt_status', 'id', data.id, udateData);
    }

    /**
     * db insert statement
     * @param table the name of the table to insert the record to 
     * @param dbData the data to insert
     */
    async function insertSql(table, dbData) {
        var data = dbData;
        if (isNaN(data.unit_price)) {
            data.unit_price = 0;
        }
        const insertData = {
            date: data.date,
            product_type: data.product_type,
            vehicle_number: data.vehicle_number,
            volume: data.volume,
            transporter: data.transporter,
            destination: data.destination,
            driver: data.driver,
            depot: data.depot,
            bdc: data.bdc,
            omc: data.omc
        };
        await insertIgnore("tax_schedule_products", { name: data.product_type });
        await insertIgnore("bdc", { name: data.bdc });
        await insertIgnore("omc", { name: data.omc });
        await insertIgnore("depot", { name: data.depot });
        return await insert(table, insertData);
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }


    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insertIgnore(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT IGNORE INTO \`${table}\`${columns} ${values}`;
        const results = await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        })
        return results.insertId;
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            console.log('\x1b[31m%s\x1b[0m', error)
            callback(null, {
                isDone: true,
                id: proccessID
            });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * export model for file export
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.exportFile = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var XLSX = require('xlsx');
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var filePath = data.path + "downloads/docs/";

    const start = async() => {
        try {
            var dataHeaders = [
                "Date",
                "Reference",
                "Description",
                "Amount",
                "ORG Response",
                "ORG Comment",
                "BOG Response",
                "BOG Comment",
                "Account Name",
                "Old Account Number",
                "New Account Number",
            ];
            var transferTypes = ["Authorized Transfer", "Reversal", "Bank Charges", "Unknown Transfer", "Unresponded"];
            var AuthorizedData = [dataHeaders];
            var ReversalData = [dataHeaders];
            var BankchargesData = [dataHeaders];
            var UnknownData = [dataHeaders];
            var Unresponded = [dataHeaders];

            const workbook = XLSX.utils.book_new();
            workbook.Props = {
                Title: data.filename,
                Subject: "Responses For Infractions",
                Author: "Strategic Mobilisation Ghana Limited",
                Company: "Strategic Mobilisation Ghana Limited",
                CreatedDate: new Date(),
            }
            workbook.SheetNames.push(transferTypes[0]);
            workbook.SheetNames.push(transferTypes[1]);
            workbook.SheetNames.push(transferTypes[2]);
            workbook.SheetNames.push(transferTypes[3]);
            workbook.SheetNames.push(transferTypes[4]);

            fs.mkdir(filePath, { recursive: true }, (err) => {
                if (err) throw err;
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'Export job accepted - " + getTime() + "','gathering transactions and responses','');");
            /**
             * get the statements for all unauthorized transafers
             * @param exportDataUT this hold all the transactions of 
             * the accounts selected
             */
            var sqlQuery = "SELECT st.id,st.particulars as description,st.`account_id`,st.post_date,st.reference,ut.amount,ac.name,ac.acc_num1,ac.acc_num2,(SELECT GROUP_CONCAT(comment SEPARATOR '\n\n') as comment FROM comments WHERE statement_id = st.id AND reviewedby='org') AS org_comment,(SELECT GROUP_CONCAT(type SEPARATOR '\n\n') as type FROM comments WHERE statement_id = st.id AND reviewedby='org') AS org_response_type,(SELECT GROUP_CONCAT(comment SEPARATOR '\n\n') as comment FROM comments WHERE statement_id = st.id AND reviewedby='bog') AS bog_comment,(SELECT GROUP_CONCAT(type SEPARATOR '\n\n') as type FROM comments WHERE statement_id = st.id AND reviewedby='bog') AS bog_response_type FROM `statements` as st INNER JOIN unauthorized_transfers as ut ON st.account_id=ut.account_from AND st.id=ut.statement_id INNER JOIN accounts as ac ON ac.id=st.account_id WHERE st.`account_id` IN (" + data.ids + ") AND ut.softdelete=0";
            var exportDataUT = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: cannot read selected account from database - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done gathering transactions and responses - " + getTime() + "','spliting records into various categories','');");
            total = exportDataUT.length;

            //loop through all transaction comments and 
            //add them to there approprate array
            await asyncForEach(exportDataUT, async(exportData, index) => {
                current = index + 1;
                await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'spliting records into various categories','processing','');");
                var newObject = [
                    exportData.post_date,
                    exportData.reference,
                    exportData.description,
                    exportData.amount,
                    exportData.org_response_type,
                    exportData.org_comment,
                    exportData.bog_response_type,
                    exportData.bog_comment,
                    exportData.name,
                    exportData.acc_num1,
                    exportData.acc_num2,
                ];
                console.log(exportData.org_response_type);
                //Check the type of response and move them to the right array
                if (exportData.org_response_type) {
                    let status = exportData.org_response_type.split("\n\n");
                    if (status[0] = transferTypes[0]) {
                        AuthorizedData.push(newObject);
                    } else if (status[0] = transferTypes[1]) {
                        ReversalData.push(newObject);
                    } else if (status[0] = transferTypes[2]) {
                        BankchargesData.push(newObject);
                    } else if (status[0] = transferTypes[3]) {
                        UnknownData.push(newObject);
                    } else {
                        Unresponded.push(newObject);
                    }
                } else {
                    Unresponded.push(newObject);
                }
            }).catch(error => {
                config.log(error)
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            //add the array of different files to work here wooksheet
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done spliting records into various categories - " + getTime() + "','adding file data to worksheets','');");
            WS_AuthorizedData = XLSX.utils.aoa_to_sheet(AuthorizedData)
            WS_ReversalData = XLSX.utils.aoa_to_sheet(ReversalData)
            WS_BankchargesData = XLSX.utils.aoa_to_sheet(BankchargesData)
            WS_UnknownData = XLSX.utils.aoa_to_sheet(UnknownData)
            WS_Unresponded = XLSX.utils.aoa_to_sheet(Unresponded)

            workbook.Sheets[transferTypes[0]] = WS_AuthorizedData;
            workbook.Sheets[transferTypes[1]] = WS_ReversalData;
            workbook.Sheets[transferTypes[2]] = WS_BankchargesData;
            workbook.Sheets[transferTypes[3]] = WS_UnknownData;
            workbook.Sheets[transferTypes[4]] = WS_Unresponded;
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done adding file data to worksheets - " + getTime() + "','preparing file for download','');");

            //save the worksheet for download
            var wookbookFile = '../omc-api/downloads/docs/' + data.filename + '.xlsx';
            // write the workbook object to a file
            XLSX.writeFile(workbook, filePath + data.filename + '.xlsx');
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'File ready for download - " + getTime() + "','completed','completed');");

            //create the download data by inserting it into the database
            //so users can see the file in the download area and download it.
            await executeStatement("INSERT INTO `file_download`(`filename`, `link`) VALUES ('" + data.filename + "','" + wookbookFile + "');");
        } catch (error) {
            console.error(error);
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error occured: proccessing file eror- " + getTime() + "','Error','completed');");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * export model for file export
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.exportFileFallout = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var XLSX = require('xlsx');
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child export fallout: ' + data.id);
            start();
        });
    });

    var filePath = data.path + "downloads/docs/";

    const start = async() => {
        try {
            var dataHeaders = [
                "BANK",
                "DATE",
                "MODE OF PAYMENT",
                "AMOUNT"
            ];
            var AuthorizedData = [dataHeaders];

            const workbook = XLSX.utils.book_new();
            workbook.Props = {
                Title: data.filename,
                Subject: "OMC Unrecognized receipts",
                Author: "Strategic Mobilisation Ghana Limited",
                Company: "Strategic Mobilisation Ghana Limited",
                CreatedDate: new Date(),
            }
            workbook.SheetNames.push("sheet1");

            fs.mkdir(filePath, { recursive: true }, (err) => {
                if (err) throw err;
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'Export job accepted - " + getTime() + "','gathering receipts','');");
            /**
             * get the statements for all unauthorized transafers
             * @param exportDataUT this hold all the transactions of 
             * the accounts selected
             */
            var sqlQuery = `SELECT * FROM omc_receipt WHERE omc_id=${data.ids} and status='flagged'`;
            var exportDataUT = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: cannot read selected omc from database - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done gathering receipts - " + getTime() + "','spliting records into various categories','');");
            total = exportDataUT.length;

            //loop through all transaction comments and 
            //add them to there approprate array
            await asyncForEach(exportDataUT, async(exportData, index) => {
                current = index + 1;
                await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'spliting records into various categories','processing','');");
                var newObject = [
                    exportData.bank,
                    exportData.date,
                    exportData.mode_of_payment,
                    exportData.amount,
                ];
                AuthorizedData.push(newObject);
            }).catch(error => {
                config.log(error)
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            //add the array of different files to work here wooksheet
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done spliting records into various categories - " + getTime() + "','adding file data to worksheets','');");
            WS_AuthorizedData = XLSX.utils.aoa_to_sheet(AuthorizedData)

            workbook.Sheets["sheet1"] = WS_AuthorizedData;
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done adding file data to worksheets - " + getTime() + "','preparing file for download','');");

            //save the worksheet for download
            var wookbookFile = '../omc-api/downloads/docs/' + data.filename + '.xlsx';
            // write the workbook object to a file
            XLSX.writeFile(workbook, filePath + data.filename + '.xlsx');
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'File ready for download - " + getTime() + "','completed','completed');");

            //create the download data by inserting it into the database
            //so users can see the file in the download area and download it.
            await executeStatement("INSERT INTO `file_download`(`filename`, `link`) VALUES ('" + data.filename + "','" + wookbookFile + "');");
        } catch (error) {
            console.error(error);
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error occured: proccessing file eror- " + getTime() + "','Error','completed');");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * export model for file export
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.exportFileNASummary = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var XLSX = require('xlsx');
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child export fallout: ' + data.id);
            start();
        });
    });

    var filePath = data.path + "downloads/docs/";

    const start = async() => {
        try {
            const exportData = JSON.parse(data.ids);
            const bank_id = exportData.id;
            const export_date = exportData.date;
            const is_flagged = exportData.flagged;
            var dataHeaders = [
                "OMC",
                "DATE",
                "MODE OF PAYMENT",
                "AMOUNT"
            ];
            var AuthorizedData = [dataHeaders];

            const workbook = XLSX.utils.book_new();
            workbook.Props = {
                Title: data.filename,
                Subject: "Summary",
                Author: "Strategic Mobilisation Ghana Limited",
                Company: "Strategic Mobilisation Ghana Limited",
                CreatedDate: new Date(),
            }
            workbook.SheetNames.push(export_date);

            fs.mkdir(filePath, { recursive: true }, (err) => {
                if (err) throw err;
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'Export job accepted - " + getTime() + "','gathering receipts','');");
            /**
             * get the statements for all unauthorized transafers
             * @param exportDataUT this hold all the transactions of 
             * the accounts selected
             */
            var other = "";
            if (is_flagged) {
                other = "AND omc_r.status='flagged' ";
            }
            var sqlQuery = `SELECT b.name as bank_name, o.name, omc_r.mode_of_payment, omc_r.bank, omc_r.date, omc_r.amount, omc_r.id FROM omc_receipt as omc_r LEFT JOIN omc as o ON o.id=omc_r.omc_id LEFT JOIN banks as b ON b.id=omc_r.bank_id WHERE omc_r.bank_id = ${bank_id} AND omc_r.date='${export_date}'  ${other}`;
            var exportDataUT = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: cannot read selected omc from database - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done gathering receipts - " + getTime() + "','spliting records into various categories','');");
            total = exportDataUT.length;

            //loop through all transaction comments and 
            //add them to there approprate array
            await asyncForEach(exportDataUT, async(exportData, index) => {
                current = index + 1;
                await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'spliting records into various categories','processing','');");
                var newObject = [
                    exportData.name,
                    exportData.date,
                    exportData.mode_of_payment,
                    exportData.amount,
                ];
                AuthorizedData.push(newObject);
            }).catch(error => {
                config.log(error)
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            //add the array of different files to work here wooksheet
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done spliting records into various categories - " + getTime() + "','adding file data to worksheets','');");
            WS_AuthorizedData = XLSX.utils.aoa_to_sheet(AuthorizedData)

            workbook.Sheets[export_date] = WS_AuthorizedData;
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done adding file data to worksheets - " + getTime() + "','preparing file for download','');");

            //save the worksheet for download
            var wookbookFile = '../omc-api/downloads/docs/' + data.filename + '.xlsx';
            // write the workbook object to a file
            XLSX.writeFile(workbook, filePath + data.filename + '.xlsx');
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'File ready for download - " + getTime() + "','completed','completed');");

            //create the download data by inserting it into the database
            //so users can see the file in the download area and download it.
            await executeStatement("INSERT INTO `file_download`(`filename`, `link`) VALUES ('" + data.filename + "','" + wookbookFile + "');");
        } catch (error) {
            console.error(error);
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error occured: proccessing file eror- " + getTime() + "','Error','completed');");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * export model for file export
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.exportFilePenalty = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var XLSX = require('xlsx');
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child export penalty: ' + data.id);
            start();
        });
    });

    var filePath = data.path + "downloads/docs/";

    const start = async() => {
        try {
            var dataHeaders = [
                "Date",
                "Transactions",
                "Total Credit",
                "Total Debit",
                "Cumulative Previous Balance",
                "future Date",
                "Total Cash Available",
                "Cash Available",
                "Total Debit Cash",
                "Untransfered Funds",
                "Penalty",
                "Calculation",
            ];

            const workbook = XLSX.utils.book_new();
            workbook.Props = {
                Title: data.filename,
                Subject: "Surcharges",
                Author: "Strategic Mobilisation Ghana Limited",
                Company: "Strategic Mobilisation Ghana Limited",
                CreatedDate: new Date(),
            }

            fs.mkdir(filePath, { recursive: true }, (err) => {
                if (err) throw err;
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'Export job accepted - " + getTime() + "','gathering transactions and responses','');");
            /**
             * get the statements for all unauthorized transafers
             * @param exportDataUT this hold all the transactions of 
             * the accounts selected
             */
            var sqlQuery = "SELECT ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive, su.*  FROM surcharge as su LEFT JOIN `accounts` as ac on ac.id=su.account_id Order By `name`";
            var exportDataUT = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: cannot read selected account from database - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done gathering transactions and responses - " + getTime() + "','spliting records into various categories','');");
            total = exportDataUT.length;

            var sheets = [];
            await asyncForEach(exportDataUT, async(exportData, index) => {
                const name = `${exportData.name}`.slice(0, 31);
                if (!workbook.SheetNames.includes(name)) {
                    workbook.SheetNames.push(name);
                    sheets.push({ name: name, fields: dataHeaders, data: [] });
                }
            });

            //loop through all transaction comments and 
            //add them to there approprate array
            await asyncForEach(exportDataUT, async(exportData, index) => {
                current = index + 1;
                await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'spliting records into various categories','processing','');");
                var newObject = [
                    exportData.date,
                    exportData.transactions,
                    exportData.total_credit,
                    exportData.total_debit,
                    exportData.comulative_balnace_previous,
                    exportData.intwodays_date,
                    exportData.total_cash_available,
                    exportData.cash_available,
                    exportData.total_debit_cash,
                    exportData.untransfered_founds,
                    exportData.penalty,
                    exportData.calculation,
                ];

                const name = `${exportData.name}`.slice(0, 31);
                let indx = sheets.findIndex((el) => el.name === name);
                if (indx != -1) {
                    sheets[indx].data.push(newObject);
                }
            }).catch(error => {
                config.log(error)
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            //add the array of different files to work here wooksheet
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done spliting records into various categories - " + getTime() + "','adding file data to worksheets','');");

            await asyncForEach(sheets, async(sheet, index) => {
                if (sheet.data) {
                    let fileData = [sheet.fields].concat(sheet.data);
                    console.log(sheets)
                    console.log(workbook.Sheets[sheet.name])
                    workbook.Sheets[sheet.name] = XLSX.utils.aoa_to_sheet(fileData);
                }
            }).catch(error => {
                config.log(error)
                executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error adding sheets to workbook - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'done adding file data to worksheets - " + getTime() + "','preparing file for download','');");

            //save the worksheet for download
            var wookbookFile = '../omc-api/downloads/docs/' + data.filename + '.xlsx';
            // write the workbook object to a file
            XLSX.writeFile(workbook, filePath + data.filename + '.xlsx');
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'File ready for download - " + getTime() + "','completed','completed');");

            //create the download data by inserting it into the database
            //so users can see the file in the download area and download it.
            await executeStatement("INSERT INTO `file_download`(`filename`, `link`) VALUES ('" + data.filename + "','" + wookbookFile + "');");
        } catch (error) {
            console.error(error);
            await executeStatement("Call update_file_export(" + data.id + ", " + current + "," + total + " ,'error occured: proccessing file eror- " + getTime() + "','Error','completed');");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

/**
 * export model for file export
 * @param data is a variable holding the current job
 * @param callback this is a method called when the job is finished in otherto terminate the process.
 */
exports.exportFileLog = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var proccessID = process.pid;
    var XLSX = require('xlsx');
    var total = 0;
    var current = 0;

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for child reconcile: ' + data.id);
            start();
        });
    });

    var filePath = data.path + "downloads/docs/";

    const start = async() => {
        try {
            var dataHeaders = [
                "Account Name",
                "Account Number",
                "Debit Amount",
                "Debit Date",
                "Description",
                "Bank",
                "",
                "Account Name",
                "Account Number",
                "Credit Amount",
                "Credit Date",
                "Description",
                "Bank",
            ];
            var exportFiles = {};
            var transferTypes = [];
            var sqlQuery = "SELECT account_name_from FROM `audits_logs` WHERE  `account_id_from` IN (" + data.ids + ") GROUP BY account_name_from ";
            var sheetNames = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'Could not read sheet name from database- " + getTime() + "','error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            if (typeof(sheetNames) != "undefined") {
                await asyncForEach(sheetNames, async(sheets, index) => {
                    const name = `${sheets.account_name_from}`.slice(0, 31);
                    transferTypes.push(name);
                }).catch(error => {
                    config.log(error)
                    executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                    callback(null, { isDone: true, id: proccessID });
                });
            }

            transferTypes.forEach(name => {
                //type of sheets with its initial headers
                exportFiles[name] = [dataHeaders]
            });

            const workbook = XLSX.utils.book_new();
            workbook.Props = {
                Title: data.filename,
                Subject: "GHANA AUDIT LOG",
                Author: "Strategic Mobilisation Ghana Limited",
                Company: "Strategic Mobilisation Ghana Limited",
                CreatedDate: new Date(),
            }

            transferTypes.forEach(transferType => {
                //add worksheets to workbook
                workbook.SheetNames.push(transferType);
            });

            fs.mkdir(filePath, { recursive: true }, (err) => {
                if (err) throw err;
            });
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'Export job accepted - " + getTime() + "','gathering transactions and responses','');");

            /**
             * get the statements for all unauthorized transafers
             * @param exportDataUT this hold all the transactions of 
             * the accounts selected
             */
            var sqlQuery = "SELECT * FROM `audits_logs` WHERE `account_id_from` IN (" + data.ids + ")";
            var exportDataUT = await query(sqlQuery).catch(error => {
                config.log(error);
                executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'error: cannot read selected account from database - " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'done gathering transactions and responses - " + getTime() + "','spliting records into various categories','');");
            total = exportDataUT.length;

            //loop through all transaction comments and 
            //add them to there approprate array
            await asyncForEach(exportDataUT, async(exportData, index) => {
                current = index + 1;
                await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'spliting records into various categories','processing','');");
                var dataArray = [
                    exportData.account_name_from,
                    exportData.account_number_from,
                    exportData.debit_amount,
                    exportData.debit_date,
                    exportData.description_from,
                    exportData.bank_from,
                    "",
                    exportData.account_name_to,
                    exportData.account_number_to,
                    exportData.credit_amount,
                    exportData.credit_date,
                    exportData.description_to,
                    exportData.bank_to,
                ];
                //Check the type of response and move them to the right array
                const name = `${exportData.account_name_from}`.slice(0, 31);
                exportFiles[name].push(dataArray);
            }).catch(error => {
                config.log(error)
                executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'error: could not process data- " + getTime() + "','Error','completed');");
                callback(null, { isDone: true, id: proccessID });
            });

            //add the array of different files to work here wooksheet
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'done spliting records into various categories - " + getTime() + "','adding file data to worksheets','');");
            transferTypes.forEach(element => {
                workbook.Sheets[element] = XLSX.utils.aoa_to_sheet(exportFiles[element])
            });
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'done adding file data to worksheets - " + getTime() + "','preparing file for download','');");

            //save the worksheet for download
            var wookbookFile = '../omc-api/downloads/docs/' + data.filename + '.xlsx';
            // write the workbook object to a file
            XLSX.writeFile(workbook, filePath + data.filename + '.xlsx');
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'File ready for download - " + getTime() + "','completed','completed');");

            //create the download data by inserting it into the database
            //so users can see the file in the download area and download it.
            await executeStatement("INSERT INTO `file_download`(`filename`, `link`) VALUES ('" + data.filename + "','" + wookbookFile + "');");
        } catch (error) {
            console.error(error);
            await executeStatement("Call file_export_status_logs(" + data.id + ", " + current + "," + total + " ,'error occured: proccessing file eror- " + getTime() + "','Error','completed');");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    /**
     * async function forEach loop
     * @param array the array to loop through
     * @param arrayCallback returns the value, index and array itself to be used
     */
    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    async function executeStatement(sqlQuery) {
        await query(sqlQuery).catch(error => {
            config.log(error);
            updateStatus("error", "job error: cannot insert record ", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * sql statemen query
     * @param sqlQuery raw query
     */
    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}

//Compile Penalty Section
exports.compilePenalty = function(data, callback) {
    var module = require('./config');
    var config = module.configs;
    var mysql = require('mysql');
    var fs = require('fs');
    var path = require('path');
    var totalAccount = 0;
    var workingOn = 0;
    var proccessID = process.pid;
    var reconcilingWith = "";

    var sqlConn;
    const configPath = path.join(process.cwd(), 'config.json');
    fs.readFile(configPath, (error, db_config) => {
        if (error) { console.log(error); return; }
        // create mysql connection to database
        sqlConn = mysql.createConnection(JSON.parse(db_config));
        sqlConn.connect(function(err) {
            if (err) config.log(err);
            isSqlConnected = true;
            config.log('mySql connected for compilation: ' + data.id);
            start();
        });
    });

    const start = async() => {
        try {
            /**
             * get the omc receipts for the main accounts
             * @param mainReceiptsData this hold all the receipts of 
             * the main omc with offset account
             */
            const account_id_to_compile = data.ids;
            var sqlQuery = `SELECT COUNT(id) transactions, SUM(credit_amount) total_credit, SUM(debit_amount) total_debit, SUM(balance) total_balance, stm.* FROM statements stm WHERE stm.status !='flagged' AND stm.account_id= ${account_id_to_compile} GROUP by account_id, post_date ORDER by post_date`;
            var mainReceiptsData = await query(sqlQuery).catch(error => {
                config.log(error);
                updateStatus("error", "job error: cannot read account statements", "completed");
                callback(null, { isDone: true, id: proccessID });
            });

            if (typeof(mainReceiptsData) == "undefined") {
                updateStatus("Error", "error: no statements found  - " + getTime(), "completed");
                config.log("error:  no statements found - " + getTime());
                return callback(null, { isDone: true, id: proccessID });
            }

            await updateStatus("processing statements", "compilation started - " + getTime());
            config.log("compilation started - " + getTime());
            config.log("processing statements");
            totalAccount = mainReceiptsData.length;
            workingOn = 0;
            await asyncForEach(mainReceiptsData, async(statement, index) => {
                workingOn = index + 1;
                update("reconcilation_status", "id", data.id, {
                    proccessing_account: workingOn,
                    total_account: totalAccount,
                });
                // config.log("processing statements: " + receipts.id);

                //set variables for computation
                const account_id = statement.account_id
                const total_credit = statement.total_credit;
                const total_debit = statement.total_debit;
                const total_balance = statement.total_balance;
                const transactions = statement.transactions;

                //get the current date
                const today_date = statement.post_date;
                var currentDate = new Date(today_date);
                // console.log("current date", today_date);
                const _gMonth = currentDate.getMonth() + 1;
                const _month = _gMonth < 10 ? "0" + _gMonth : _gMonth;
                const _day = currentDate.getDate() < 10 ? "0" + currentDate.getDate() : currentDate.getDate();
                const todays_date = currentDate.getFullYear() + "-" + _month + "-" + _day;
                // console.log("current date", currentDate.getFullYear() + "-" + _month + "-" + _day);

                currentDate.setDate(currentDate.getDate() + 2);
                const gMonth = currentDate.getMonth() + 1;
                const month = gMonth < 10 ? "0" + gMonth : gMonth;
                const day = currentDate.getDate() < 10 ? "0" + currentDate.getDate() : currentDate.getDate();
                const intwodays_date = currentDate.getFullYear() + "-" + month + "-" + day;

                //query the db for previous balance
                var balanceQry = `SELECT * FROM statements WHERE status !='flagged' AND account_id= ${account_id_to_compile} AND post_date < '${todays_date}' ORDER by post_date DESC LIMIT 1`;
                var balanceData = await query(balanceQry).catch(error => {
                    config.log(error);
                    updateStatus("error", "job error: cannot read previous day balance", "completed");
                    callback(null, { isDone: true, id: proccessID });
                });
                // console.log(balanceQry)

                var comulative_balnace_previous = 0.0;
                if (balanceData != null && typeof(balanceData[0]) != "undefined" && balanceData[0] != null) {
                    comulative_balnace_previous = balanceData[0].balance;
                }
                // console.log(comulative_balnace_previous)

                const total_cash_available = total_credit + comulative_balnace_previous;
                const cash_available = total_cash_available - total_debit;

                const found = mainReceiptsData.find(el => el.post_date === intwodays_date);
                var total_debit_cash = 0.0;
                if (found) {
                    total_debit_cash = found.total_debit;
                }

                const untransfered_founds = cash_available - total_debit_cash;
                var stm = `SELECT rate FROM surcharge_rate  WHERE date_from <= '${todays_date}' AND date_to >= '${todays_date}'`;
                var rateResult = await query(stm).catch(error => {
                    config.log(error);
                    updateStatus("error", "job error: internal query failed, cannot get rate", "completed");
                    callback(null, { isDone: true, id: proccessID });
                });
                if (rateResult != null && typeof(rateResult[0]) != "undefined" && rateResult[0] != null) {
                    const surcharge_rate = rateResult[0].rate;
                    const penalty = untransfered_founds * surcharge_rate;
                    const calculation = `(((${total_credit} + ${comulative_balnace_previous}) - ${total_debit}) - ${total_debit_cash}) * ${surcharge_rate}`;
                    // console.log(calculation);
                    // console.log(penalty);
                    // console.log((((994262.5 + 102098315.58) - 1166234.74) - 0) * 0.0871);
                    const dataToInsert = {
                        date: todays_date,
                        account_id: account_id,
                        total_credit: total_credit,
                        total_debit: total_debit,
                        comulative_balnace_previous: comulative_balnace_previous,
                        transactions: transactions,
                        intwodays_date: intwodays_date,
                        total_cash_available: total_cash_available,
                        cash_available: cash_available,
                        total_debit_cash: total_debit_cash,
                        untransfered_founds: untransfered_founds,
                        penalty: penalty,
                        calculation: calculation,
                    }
                    await insert("surcharge", dataToInsert);
                }
            });

            await updateStatus("completed", " compilatiion completed - " + getTime(), "completed");
            config.log("compilatiion completed - " + getTime());
        } catch (error) {
            console.error(error);
            await updateStatus("Error", "error occured: " + error.message + " - " + getTime(), "completed");
        } finally {
            config.log("task done ");
            callback(null, { isDone: true, id: proccessID });
        }
    }

    async function asyncForEach(array, arrayCallback) {
        for (let index = 0; index < array.length; index++) {
            await arrayCallback(array[index], index, array);
        }
    }

    // updateStatus("error", "account not found in current job", "completed");
    async function updateStatus(status, desc, jobstatus) {
        await update("reconcilation_status", "id", data.id, {
            description: desc || "",
            reconciling_with: reconcilingWith,
            proccessing_account: workingOn,
            total_account: totalAccount,
            status: status || "",
            processing: jobstatus || "processing"
        });
    }

    function bgQuery(sqlQuery) {
        sqlConn.query(sqlQuery, function(err, result, fields) {
            if (err) {
                config.log(err)
            }
        });
    }

    /**
     * update in to the database
     * @param {String} table The name of the table to insert into
     * @param {String} column the column in the db to mach
     * @param val value used to match the column
     * @param {Object} data the object containing key and values to insert
     */
    async function update(table, column, val, data) {
        // set up an empty array to contain the WHERE conditions
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            values.push(`\`${key}\` = '${data[key]}'`);
        });
        // convert the where array into a string of , clauses
        values = values.join(' , ');
        // check the val type is string and set it as string 
        if (typeof(val) == "string") {
            val = `'${val}'`;
        }

        const sql = `UPDATE \`${table}\` SET ${values} WHERE \`${column}\`= ${val}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: update error", "completed");
            callback(null, { isDone: true, id: proccessID });
        });
    }

    /**
     * insert in to the database
     * @param {String} table The name of the table to insert into
     * @param {Object} data the object containing key and values to insert
     */
    async function insert(table, data) {
        if (!table) return;
        if (!data) return;
        // set up an empty array to contain the  columns and values
        let columns = [];
        let values = [];
        // Iterate over each key / value in the object
        Object.keys(data).forEach(function(key) {
            // if the value is an empty string, do not use
            if ('' === data[key]) {
                return;
            }
            // if we've made it this far, add the clause to the array of conditions
            columns.push(`\`${key}\``);
            values.push(`'${data[key]}'`);
        });
        // convert the columns array into a string of
        columns = "(" + columns.join(' , ') + ")";
        // convert the values array into a string 
        values = "VALUES (" + values.join(' , ') + ");";
        //construct the insert statement
        const sql = `INSERT INTO \`${table}\`${columns} ${values}`;
        await query(sql).catch(error => {
            config.log(error);
            updateStatus("error", "job error: " + error, "completed");
            callback(null, { isDone: true, id: proccessID });
        })
    }

    function query(sqlQuery) {
        return new Promise(function(resolve, reject) {
            sqlConn.query(sqlQuery, function(err, result, fields) {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }

    function getTime() {
        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return time;
    }
}