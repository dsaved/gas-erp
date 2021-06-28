module.exports = async function good_standings(sqlConn) {
    await query("TRUNCATE petroleum_good_standing");
    const good_standings = await query(`SELECT MIN(rep.date) date_from, MAX(rep.date) date_to, rep.omc omc, SUM(rep.amount) amount, SUM(icum_dcl.amount) dcl_amount_icum,
    SUM(ped.expected_declaration_amount) ped_amount
    FROM ghana_gov_omc_receipt rep LEFT JOIN petroleum_icums_declaration icum_dcl ON rep.omc=icum_dcl.omc
    LEFT JOIN petroleum_expected_declaration ped ON rep.omc=ped.omc GROUP BY rep.omc
    UNION
    SELECT (SELECT date_from FROM tax_window WHERE code=icum_dcl.window_code LIMIT 1) date_from,
    (SELECT date_to FROM tax_window WHERE code=icum_dcl.window_code LIMIT 1) date_to, icum_dcl.omc omc, SUM(rep.amount) amount, SUM(icum_dcl.amount) dcl_amount_icum,
    SUM(ped.expected_declaration_amount) ped_amount
    FROM ghana_gov_omc_receipt rep RIGHT JOIN petroleum_icums_declaration icum_dcl ON rep.omc=icum_dcl.omc
    RIGHT JOIN petroleum_expected_declaration ped ON rep.omc=ped.omc WHERE rep.id IS NULL GROUP BY icum_dcl.omc
    UNION
    SELECT ped.date_from date_from, ped.date_to date_to, ped.omc omc, SUM(rep.amount) amount, SUM(icum_dcl.amount) dcl_amount_icum,
    SUM(ped.expected_declaration_amount) ped_amount
    FROM ghana_gov_omc_receipt rep RIGHT JOIN petroleum_icums_declaration icum_dcl ON rep.omc=icum_dcl.omc
    RIGHT JOIN petroleum_expected_declaration ped ON rep.omc=ped.omc WHERE rep.id IS NULL GROUP BY ped.omc, date_from, date_to ORDER BY date_from`).catch(error => {
        console.log(error);
    });

    await asyncForEach(good_standings, async(good_standing, index) => {
        if (good_standing.omc && good_standing.omc !== "null") {
            const amount = good_standing.amount;
            const dcl_amount_icum = good_standing.dcl_amount_icum;
            const exp_declaration_amount = good_standing.ped_amount;

            const date_from = good_standing.date_from;
            const date_to = good_standing.date_to;

            const difference_amount_receipt_icums = (amount - dcl_amount_icum);
            const difference_amount_expected_icums = (amount - exp_declaration_amount);
            const flagged = (difference_amount_expected_icums > 0 || difference_amount_expected_icums < 0) || (difference_amount_receipt_icums > 0 || difference_amount_receipt_icums < 0) ? 1 : 0;

            const insertData = {
                date_from: date_from,
                date_to: date_to,
                omc: good_standing.omc,
                amount: amount,
                dcl_amount_icum: dcl_amount_icum,
                exp_dcl_amount: exp_declaration_amount,
                difference_amount_receipt_icums: difference_amount_receipt_icums,
                difference_amount_expected_icums: difference_amount_expected_icums,
                flagged: flagged,
            }
            await insert("petroleum_good_standing", insertData);
        }
    }).catch(error => {
        console.log(error)
    });
    console.log("Done computing good standings")

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