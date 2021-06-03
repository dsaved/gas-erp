module.exports = {
    //should run only once a day
    alarmNosaleInWeek(sqlConn) {
        var sqlQuery = `SELECT depot as name, product FROM petroleum_tanks Group by depot, product`;
        sqlConn.query(sqlQuery, function(err, depots, fields) {
            if (err) {
                console.log(err)
            } else {
                // console.log(depots)
                if (depots.length > 0) {
                    for (let index = 0; index < depots.length; index++) {
                        const depot = depots[index];
                        var alarmQry = `SELECT * FROM petroleum_outlet WHERE depot = '${depot.name}' ORDER BY id DESC LIMIT 1`;
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
                                        var alarmQry = `INSERT INTO petroleum_alarm_notification(time,type, message,depot,product) VALUES ('${getTime(true)}','not sold for a week','This Depot has not sold ${depot.product} for a week','${depot.name}','${depot.product}')`;
                                        sqlConn.query(alarmQry, function(err, tank, fields) {
                                            if (err) {
                                                console.log(err)
                                            }
                                        });
                                    } else {
                                        var alarmQry = `DELETE FROM petroleum_alarm_notification  WHERE depot = '${depot.name}' AND product='${depot.product}'`;
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
}