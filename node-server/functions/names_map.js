module.exports = {
    async depot_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "dsaved limited": "Dsave DB"
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return `${name}`.toUpperCase().trim();
        }

        //return the maped name
        return name_map[name];
    },
    async omc_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "dsaved limited": "Dsave DB"
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return `${name}`.toUpperCase().trim();
        }

        //return the maped name
        return name_map[name];
    },
    async bdc_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "dsaved limited": "Dsave DB"
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return `${name}`.toUpperCase().trim();
        }

        //return the maped name
        return name_map[name];
    },
    async product_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "pms (petrol)": "PMS",
            "pms": "PMS",
            "diesel": "DIESEL",
            "vpower": "VPOWER",
            "premix": "PREMIX",
            "aviation turbine kerosene": "KEROSENE",
            "kerosene": "KEROSENE",
            "kero": "KEROSENE",
            "lpg": "LPG",
            "atk": "ATK",
            "unified": "UNIFIED",
            "ago Mines": "AGO MINES",
            "ago rig": "AGO RIG",
            "rfo": "RFO",
            "mgo": "MGO",
            "mgo Foreign": "MGO FOREIGN",
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return `${name}`.toUpperCase().trim();
        }

        //return the maped name
        return name_map[name];
    },
}