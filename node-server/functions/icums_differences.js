module.exports = async function icums_differences(sqlConn) {
    await query("TRUNCATE petroleum_icums_differences");
    const differences = await query(`SELECT pid.window_code window_code, pid.product_type product_type, pid.omc omc, SUM(pid.amount) amount,
    SUM(ped.expected_declaration_amount) expected_declaration_amount FROM petroleum_icums_declaration pid 
    LEFT JOIN petroleum_expected_declaration ped ON ped.omc=pid.omc AND ped.window_code=pid.window_code
    GROUP BY omc, window_code
    UNION
    SELECT ped.window_code window_code, ped.product_code product_type, ped.omc omc, SUM(pid.amount) amount,
    SUM(ped.expected_declaration_amount) expected_declaration_amount FROM petroleum_icums_declaration pid
    RIGHT JOIN petroleum_expected_declaration ped ON ped.omc=pid.omc AND ped.window_code=pid.window_code
    GROUP BY omc, window_code ORDER BY omc`).catch(error => {
        console.log(error);
    });

    await asyncForEach(differences, async(difference, index) => {
        const exp_declaration_amount = difference.expected_declaration_amount;
        const amount = difference.amount;
        const date = difference.window_code;
        const difference_amount = (amount - exp_declaration_amount);
        const flagged = (difference_amount > 0 || difference_amount < 0) ? 1 : 0;
        const way_declaration_amount = (exp_declaration_amount);
        const icum_declaration_amount = (amount);

        const insertData = {
            date: date,
            omc: difference.omc,
            amount: icum_declaration_amount,
            exp_declaration_amount: way_declaration_amount,
            difference_amount: difference_amount,
            flagged: flagged,
        }
        await insert("petroleum_icums_differences", insertData);
    }).catch(error => {
        console.log(error)
    });
    console.log("Done computing icums differences")

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