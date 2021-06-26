module.exports = async function compute_declarations(sqlConn) {
    await query("TRUNCATE petroleum_expected_declaration");
    const waybills = await query("SELECT * FROM petroleum_waybill").catch(error => {
        console.log(error);
    });

    var computedTaxes = [];
    await asyncForEach(waybills, async(waybill, index) => {
        const Query = `SELECT tw.*, tt.name tax FROM tax_window tw
        JOIN tax_type tt ON tw.tax_type = tt.id 
        WHERE tw.tax_product = '${waybill.product_type}'
        AND tw.date_from <= '${waybill.date}' AND tw.date_to >= '${waybill.date}'`;
        const tax_tax_types = await query(Query).catch(error => {
            console.log(error);
        });
        if (tax_tax_types.length > 0) {
            for (let index = 0; index < tax_tax_types.length; index++) {
                const tax_tax_type = tax_tax_types[index];
                computedTaxes.push({
                    product_code: waybill.product_type,
                    depot: waybill.depot,
                    bdc: waybill.bdc,
                    omc: waybill.omc,
                    volume: waybill.volume,
                    expected_declaration_amount: waybill.volume * tax_tax_type.rate,
                    rate: tax_tax_type.rate,
                    window_code: tax_tax_type.code,
                    date_from: tax_tax_type.date_from,
                    date_to: tax_tax_type.date_to,
                    tax: tax_tax_type.tax,
                });
            }
        }
    }).catch(error => {
        console.log(error)
    });

    await asyncForEach(computedTaxes, async(computedTax, index) => {
        await insert("petroleum_expected_declaration", computedTax);
    });
    console.log("Done computing expected declaration")

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
        })
        return results.insertId;
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
}