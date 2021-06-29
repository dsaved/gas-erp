const names = require('./names_map.js');
var fs = require('fs')

module.exports = {
    //should run only once a day
    alarmNosaleInWeek(sqlConn) {
        var sqlQuery = `SELECT (SELECT name FROM depot WHERE code=depot) as depot_name,depot, (SELECT name FROM tax_schedule_products WHERE code=product) as product_name,product FROM petroleum_tanks WHERE volume > 999 Group by depot, product`;
        sqlConn.query(sqlQuery, function(err, depots, fields) {
            if (err) {
                console.log(err)
            } else {
                // console.log(depots)
                if (depots.length > 0) {
                    for (let index = 0; index < depots.length; index++) {
                        const depot = depots[index];
                        var alarmQry = `SELECT datetime FROM petroleum_outlet WHERE depot = '${depot.depot}' ORDER BY id DESC LIMIT 1`;
                        sqlConn.query(alarmQry, function(err, lastSold, fields) {
                            if (err) {
                                console.log(err)
                            } else {
                                // console.log(lastSold)
                                if (lastSold.length > 0) {
                                    const lastSoldData = lastSold[0];
                                    const lastSellDate = new Date(lastSoldData.datetime);
                                    const today = new Date();
                                    var diff = getDateDiff(today, lastSellDate);
                                    if (diff >= 7) {
                                        var alarmQry = `INSERT INTO petroleum_alarm_notification(time,type, message,depot,product) VALUES ('${getTime(true)}','not sold for a week','This Depot has not sold ${depot.product_name} for a week','${depot.depot}','${depot.product}')`;
                                        sqlConn.query(alarmQry, function(err, tank, fields) {
                                            if (err) {
                                                console.log(err)
                                            }
                                        });
                                    } else {
                                        var alarmQry = `DELETE FROM petroleum_alarm_notification  WHERE depot = '${depot.depot}' AND product='${depot.product}'`;
                                        sqlConn.query(alarmQry, function(err, tank, fields) {
                                            if (err) {
                                                console.log(err)
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }
                }
            }
        });

        function getDateDiff(d1, d2) {
            var t2 = d2.getTime();
            var t1 = d1.getTime();
            return parseInt((t2 - t1) / (24 * 3600 * 1000));
        }

        function getTime(dateTime) {
            function pad(s) { return (s < 10) ? '0' + s : s; }
            if (dateTime) {
                var today = new Date();
                var time = [pad(today.getFullYear()), pad(today.getMonth() + 1), today.getDate()].join('-') + " " + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                return time;
            }
            var today = new Date();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            return time;
        }
    },
    //missing products
    async missingProduct(sqlConn) {
        var sqlQuery = `SELECT dcl.* FROM petroleum_declaration dcl JOIN petroleum_manifest mft ON mft.ucr_number=dcl.ucr_number WHERE dcl.missing = 0`;
        var declarations = await query(sqlQuery).catch(error => { console.log(error) });
        // console.log(declarations)
        if (declarations.length > 0) {
            for (let index = 0; index < declarations.length; index++) {
                const declaration = declarations[index];
                var alarmQry = `SELECT * FROM petroleum_inlet WHERE bdc = '${declaration.importer_name}' AND volume = ${declaration.volume} AND product_type = '${declaration.product_type}' AND checked_msi =0 LIMIT 1`;
                var inlets = await query(alarmQry).catch(error => { console.log(error) });
                if (inlets.length > 0) {
                    const inlet = inlets[0];
                    var undeclaredQry = `UPDATE petroleum_declaration SET checked_dcl = 1, missing = 0 WHERE id=${declaration.id};`;
                    undeclaredQry += `UPDATE petroleum_inlet SET declared = 1 WHERE id=${inlet.id};`;
                    await query(undeclaredQry).catch(error => { console.log(error) });
                } else {
                    var undeclaredQry = `UPDATE petroleum_declaration SET missing = 1 WHERE id=${declaration.id};`;
                    await query(undeclaredQry).catch(error => { console.log(error) });
                }
            }
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
        return true;
    },
    //undeclared products
    async undeclaredProduct(sqlConn) {
        var sqlQuery = `SELECT * FROM petroleum_inlet WHERE declared = 0`;
        var inlets = await query(sqlQuery).catch(error => { console.log(error) });
        if (inlets.length > 0) {
            for (let index = 0; index < inlets.length; index++) {
                const inlet = inlets[index];
                var qryString = `SELECT * FROM petroleum_declaration WHERE importer_name = '${inlet.bdc}' AND volume = ${inlet.volume} AND product_type = '${inlet.product_type}' AND checked_dcl =0 LIMIT 1`;
                var declareds = await query(qryString).catch(error => { console.log(error) });
                if (declareds.length > 0) {
                    var product = declareds[0];
                    var undeclaredQry = `UPDATE petroleum_declaration SET checked_dcl = 1 WHERE id=${product.id};`;
                    undeclaredQry += `UPDATE petroleum_inlet SET declared = 1 WHERE id=${inlet.id};`;
                    await query(undeclaredQry).catch(error => { console.log(error) });
                }
            }
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
        return true;
    },
    //pump products
    async pump_product(sqlConn) {
        const capture_date = previousDay();
        const inflow = `./data/InflowData_${capture_date}.json`;
        const outflow = `./data/OutflowData_${capture_date}.json`;
        try {
            if (fs.existsSync(inflow)) {
                //get the inlet data flow from media server
                const jsonDataIn = fs.readFileSync(inflow)
                const inletData = JSON.parse(jsonDataIn);

                //check if the inlet data has data in it
                if (inletData.length > 0) {
                    var deportsQty = [];
                    for (var dd = 0; dd < inletData.length; dd++) {
                        const data = inletData[dd];
                        const dbDate = `${data.Date}`.split('T').join(" ");
                        var nodepot = true;
                        const depot = await names.depot_name(data.DepotName);
                        const product = await names.product_name(data.FuelType);

                        if (data.Flow > 999) {
                            //update depot quantity
                            for (let index = 0; index < deportsQty.length; index++) {
                                const element = deportsQty[index];
                                if (element.identifyer === `${depot.code}-${product.code}`) {
                                    nodepot = false;
                                    deportsQty[index].volume = Number(element.volume) + Number(data.Flow)
                                }
                            }

                            //create depot quantity
                            if (nodepot) {
                                deportsQty.push({ tank: "depot", unit: data.Units, identifyer: `${depot.code}-${product.code}`, product: product.code, depot: depot.code, volume: Number(data.Flow) })
                            }

                            //insert into petroleum inlet db
                            var sqlQuery = "INSERT INTO `petroleum_inlet`(`datetime`, `depot`, `product_type`, `volume`)";
                            sqlQuery += " VALUES ('" + dbDate + "','" + depot.code + "','" + product.code + "'," + data.Flow + ")";
                            await query(sqlQuery).catch(err => console.log(err));
                            await insertIgnore("depot", { name: depot.name, code: depot.code });
                            await insertIgnore("tax_schedule_products", { name: product.name, code: product.code });
                        }
                    }

                    //create or update Tank for Depot
                    for (let deportIndex = 0; deportIndex < deportsQty.length; deportIndex++) {
                        const element = deportsQty[deportIndex];
                        var size = 90000000;
                        var sqlQuery = `INSERT INTO petroleum_tanks (identifyer,depot,tank,product,volume,full,unit) VALUES ('${element.identifyer}','${element.depot}','${element.tank}','${element.product}',${element.volume}, ${size}, '${element.unit}')ON DUPLICATE KEY UPDATE full= ${size},volume =IF(volume < 0, ${element.volume}, volume + ${element.volume});`;
                        await query(sqlQuery).catch(err => console.log(err));
                    }
                    console.log("inlet flow done")
                    fs.unlinkSync(inflow)
                } else {
                    console.log("inlet flow done")
                    fs.unlinkSync(inflow)
                }
            }

            await timeout(15000);

            if (fs.existsSync(outflow)) {
                //get the oulet data flow from media server
                const jsonDataOut = fs.readFileSync(outflow)
                const outletData = JSON.parse(jsonDataOut);

                //check if the outlet data has data in it
                if (outletData.length > 0) {
                    var deportsQty = [];
                    for (var dd = 0; dd < outletData.length; dd++) {
                        const data = outletData[dd];
                        const dbDate = `${data.Date}`.split('T').join(" ");
                        const depot = await names.depot_name(data.DepotName);
                        const product = await names.product_name(data.FuelType);
                        var nodepot = true;

                        if (data.Flow > 999) {

                            //update depot quantity
                            for (let index = 0; index < deportsQty.length; index++) {
                                const element = deportsQty[index];
                                if (element.identifyer === `${depot.code}-${product.code}`) {
                                    nodepot = false;
                                    deportsQty[index].volume = Number(element.volume) + Number(data.Flow)
                                    deportsQty[index].time = dbDate
                                }
                            }

                            //create depot quantity
                            if (nodepot) {
                                deportsQty.push({ tank: "depot", unit: data.Units, identifyer: `${depot.code}-${product.code}`, product: product.code, depot: depot.code, volume: Number(data.Flow) })
                            }

                            //insert into petroleum outlet db
                            var sqlQuery = "INSERT INTO `petroleum_outlet`(`datetime`, `depot`, `product_type`, `volume`)";
                            sqlQuery += " VALUES ('" + dbDate + "','" + depot.code + "','" + product.code + "'," + data.Flow + ")";
                            await query(sqlQuery).catch(err => console.log(err));
                            await insertIgnore("depot", { name: depot.name, code: depot.code });
                            await insertIgnore("tax_schedule_products", { name: product.name, code: product.code });
                        }
                    }

                    //create or update Tank for Depot
                    for (let deportIndex = 0; deportIndex < deportsQty.length; deportIndex++) {
                        const element = deportsQty[deportIndex];

                        // get the depot pumping the product and check if the tank is empty
                        var sqlQuery = `SELECT * FROM petroleum_tanks WHERE identifyer = '${element.identifyer}' AND volume >= ${element.volume} LIMIT 1`;
                        var tank = await query(sqlQuery).catch(err => console.log(err));
                        console.log(`tanks: ${tank}`)
                        if (tank == null || tank.length == 0 || typeof(tank[0]) == "undefined" || tank[0] == null) {
                            //Pumping from empty tank
                            // console.log("Pumping from empty tank", element)
                            var alarmQry = `INSERT INTO petroleum_alarm_notification(time, type, message,depot, alarm, product, volume) VALUES ('${element.time}','discharge from empty tank','There was a discharge from an empty tank','${element.depot}','${element.tank}','${element.product}',${element.volume})`;
                            await query(alarmQry).catch(err => console.log(err));
                        }

                        var size = 90000000;
                        var sqlQuery1 = `INSERT INTO petroleum_tanks (identifyer,depot,tank,product,volume,full,unit) VALUES ('${element.identifyer}','${element.depot}','${element.tank}','${element.product}',0,${size},'${element.unit}')ON DUPLICATE KEY UPDATE full= ${size}, volume = IF(volume > 0, volume - ${element.volume}, 0);`;
                        await query(sqlQuery1).catch(err => console.log(err));
                    }
                    console.log("outlet flow done");
                    fs.unlinkSync(outflow)
                } else {
                    console.log("outlet flow done")
                    fs.unlinkSync(outletData)
                }
            }
        } catch (err) {
            console.error("no new data available" + err)
        }

        function timeout(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function previousDay() {
            function pad(s) { return (s < 10) ? '0' + s : s; }
            var today = new Date();
            today.setDate(today.getDate() - 1)
            var date = [pad(today.getDate()), pad(today.getMonth() + 1), pad(today.getFullYear())].join('-');
            return date;
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
    },
    //download file
    download_files() {
        const Client = require('ssh2').Client;
        const conn = new Client();
        const remoteDir = '';
        const lockFile = './data/lock.json';

        conn.on('ready', () => {
            conn.sftp((err, sftp) => {
                if (err) console.log(err);
                sftp.readdir(remoteDir, (err, list) => {
                    if (err) console.log(err);
                    // console.log(list);
                    list.forEach(item => {
                        let remoteFile = remoteDir + item.filename;
                        let date = item.filename.split('_')[1];
                        if (date === `${previousDay()}.json`) {
                            let localFile = './data/' + item.filename;
                            console.log('Downloading ' + remoteFile);
                            sftp.fastGet(remoteFile, localFile, (err) => {
                                if (err) console.log(err);
                                console.log('Downloaded to ' + localFile);
                                var configuration = fs.readFileSync(lockFile);
                                var conf = JSON.parse(configuration);
                                conf.last = `${previousDay()}`;
                                let data = JSON.stringify(conf, null, 2);
                                fs.writeFileSync(lockFile, data);
                            });
                        }
                    });
                });
            });
        });

        conn.on('error', (err) => {
            // console.error('SSH connection stream problem');
            // console.log(err);
        });

        try {
            fs.readFileSync(lockFile);
        } catch (error) {
            var newFiledata = { "last": "00-00-0000" };
            fs.writeFileSync(lockFile, JSON.stringify(newFiledata, null, 2));
        }
        var configuration = fs.readFileSync(lockFile);
        var conf = JSON.parse(configuration);

        console.log(conf.last)
        if (conf.last !== previousDay()) {
            console.log("ssh connecting...")
                //connect to ssh
            conn.connect({
                host: '172.30.60.150',
                port: 22,
                username: 'sml_ai',
                password: '@*+3$T!ni#',
            });
        }

        function previousDay() {
            function pad(s) { return (s < 10) ? '0' + s : s; }
            var today = new Date();
            today.setDate(today.getDate() - 1)
            var date = [pad(today.getDate()), pad(today.getMonth() + 1), pad(today.getFullYear())].join('-');
            return date;
        }
    }
}