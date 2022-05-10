module.exports = {
    async depot_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "akwaaba": { name: "ADINKRA STORAGE COMPANY GHANA LIMITED", code: "WVTOR9TMAL" },
            "akwaaba oil refinery limited": { name: "ADINKRA STORAGE COMPANY GHANA LIMITED", code: "WVTOR9TMAL" },
            "adinkra storage company ghana limited": { name: "ADINKRA STORAGE COMPANY GHANA LIMITED", code: "WVTOR9TMAL" },

            "blue ocean ridge": { name: "BLUE OCEAN INVESTMENTS LIMITED (RIDGE)", code: "WVTOR9TRD9" },
            "blue ocean takoradi": { name: "BLUE OCEAN INVESTMENTS LIMITED (TAKORADI)", code: "WVTKD9TKBO" },
            "blue ocean tmpt": { name: "BLUE OCEAN INVESTMENTS LIMITED (TMPT)", code: "WVTOR9TPM1" },
            "blue ocean investments limited": { name: "BLUE OCEAN INVESTMENTS LIMITED (TMPT)", code: "WVTOR9TPM1" },

            "ghanstock takoradi": { name: "GHANSTOCK LIMITED", code: "WVTKD9TKGL" },
            "ghanstock limited": { name: "GHANSTOCK LIMITED", code: "WVTKD9TKGL" },

            "go energy main harbour takoradi": { name: "GO ENERGY MAIN HARBOUR TAKORADI", code: "WVTKD9TKNB" },
            "go energy naval base takoradi": { name: "GO ENERGY NAVAL BASE TAKORADI", code: "WVTKD9TKNB" },
            "takoradi bost": { name: "GO ENERGY", code: "WVTKD9TKNB" },
            "go energy": { name: "GO ENERGY", code: "WVTKD9TKNB" },

            "quantum gas atuabo": { name: "QUANTUM TERMINALS LIMITED", code: "WVTKD9QTL9" },
            "quantum terminals limited": { name: "QUANTUM TERMINALS LIMITED", code: "WVTKD9QTL9" },

            "chase logistics limited": { name: "CHASE LOGISTICS LIMITED", code: "WVTOR9CHS1" },
            "tema tank farm chase": { name: "CHASE LOGISTICS LIMITED", code: "WVTOR9CHS1" },

            "vena energy limited": { name: "VANA ENERGY LIMITED", code: "WVTOR9ACP1" },
            "vana energy limited": { name: "VANA ENERGY LIMITED", code: "WVTOR9ACP1" },

            "platon refinery": { name: "PLATON GAS OIL GHANA LIMITED", code: "WVTOR9PTG1" },
            "platon gas oil ghana limited": { name: "PLATON GAS OIL GHANA LIMITED", code: "WVTOR9PTG1" },

            "quantum oil terminals limited": { name: "QUANTUM OIL TERMINALS LIMITED", code: "WVTOR9T392" },
            "quantum oil terminal": { name: "QUANTUM OIL TERMINALS LIMITED", code: "WVTOR9T392" },

            "zen takoradi": { name: "ZEN TERMINALS LIMITED", code: "WVTKD9TKCO" },
            "zen terminals limited": { name: "ZEN TERMINALS LIMITED", code: "WVTKD9TKCO" },

            "bunkering service tema": { name: "TEMA OIL REFINERY (TOR) LIMITED", code: "WVTOR9TORA" },
            "tema oil refinery": { name: "TEMA OIL REFINERY (TOR) LIMITED", code: "WVTOR9TORA" },
            "tema oil refinery (tor) limited": { name: "TEMA OIL REFINERY (TOR) LIMITED", code: "WVTOR9TORA" },

            "bulk oil storage and transportation": { name: "BULK OIL STORAGE AND TRANSPORTATION", code: "WVTOR9ACP9" },
            "bost apd": { name: "BULK OIL STORAGE AND TRANSPORTATION", code: "WVTOR9ACP9" },
            "bolgatanga bost": { name: "BULK OIL STORAGE AND TRANSPORTATION", code: "WVTOR9ACP9" },

            "tema fuel company limited": { name: "TEMA FUEL COMPANY - FUEL TRADE", code: "WVTOR9FTL1" },
            "tema fuel company - fuel trade": { name: "TEMA FUEL COMPANY - FUEL TRADE", code: "WVTOR9FTL1" },

            "petroleum ware housing and supplies limited": { name: "PETROLEUM WARE HOUSING AND SUPPLIES LIMITED", code: "WVTOR9ACP2" },
            "old bauxite jetty": { name: "OLD BAUXITE JETTY", code: "WVTKD9TKDE" },
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return null;
        }

        //return the maped name
        return name_map[name];
    },
    async omc_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "bulk oil storage and transportation": {
                "name": "BULK OIL STORAGE AND TRANSPORTATION",
                "tin": "G0005117895"
            },
            "gat oil company limited": {
                "name": "GAT OIL COMPANY LIMITED",
                "tin": "C0004630432"
            },
            "agapet limited": {
                "name": "AGAPET LIMITED",
                "tin": "C0002864215"
            },
            "champion oil company limited": {
                "name": "CHAMPION OIL COMPANY LIMITED",
                "tin": "C0003168603"
            },
            "rigworld petroleum services limited": {
                "name": "RIGWORLD PETROLEUM SERVICES LIMITED",
                "tin": "C000755981X"
            },
            "deep petroleum limited": {
                "name": "DEEP PETROLEUM LIMITED",
                "tin": "C0006013392"
            },
            "maiga & hhm company limited": {
                "name": "MAIGA & HHM COMPANY LIMITED",
                "tin": "C0007706359"
            },
            "petra energy limited": {
                "name": "PETRA ENERGY LIMITED",
                "tin": "C0007634641"
            },
            "warren oil company limited": {
                "name": "WARREN OIL COMPANY LIMITED",
                "tin": "C0003313573"
            },
            "havilah oil ghana limited": {
                "name": "HAVILAH OIL GHANA LIMITED",
                "tin": "C0003268063"
            },
            "sonnidom limited": {
                "name": "SONNIDOM LIMITED",
                "tin": "C0003628892"
            },
            "sky petroleum limited": {
                "name": "SKY PETROLEUM LIMITED",
                "tin": "C0005108764"
            },
            "semanhyia oil limited": {
                "name": "SEMANHYIA OIL LIMITED",
                "tin": "C0005012449"
            },
            "santol energy limited": {
                "name": "SANTOL ENERGY LIMITED",
                "tin": "C0001199374"
            },
            "blanko oil company limited": {
                "name": "BLANKO OIL COMPANY LIMITED",
                "tin": "C0004345304"
            },
            "lillygold energy resources limited": {
                "name": "LILLYGOLD ENERGY RESOURCES LIMITED",
                "tin": "C0005705886"
            },
            "express petroleum limited": {
                "name": "EXPRESS PETROLEUM LIMITED",
                "tin": "C0005165644"
            },
            "big energy limited": {
                "name": "BIG ENERGY LIMITED",
                "tin": "C0002942615"
            },
            "chase petroleum ghana limited": {
                "name": "CHASE PETROLEUM GHANA LIMITED",
                "tin": "C0002840278"
            },
            "abagurugu oil company limited": {
                "name": "ABAGURUGU OIL COMPANY LIMITED",
                "tin": "C0003717496"
            },
            "da oil company limited": {
                "name": "DA OIL COMPANY LIMITED",
                "tin": "C0004395689"
            },
            "icon energy ltd": {
                "name": "ICON ENERGY LTD",
                "tin": "C0054712629"
            },
            "jet petroleum services limited": {
                "name": "JET PETROLEUM SERVICES LIMITED",
                "tin": "C0000555363"
            },
            "jo & ju company limited": {
                "name": "JO & JU COMPANY LIMITED",
                "tin": "C000403354X"
            },
            "kingsperp oil limited": {
                "name": "KINGSPERP OIL LIMITED",
                "tin": "C001606965X"
            },
            "mba global petroleum limited": {
                "name": "MBA GLOBAL PETROLEUM LIMITED",
                "tin": "C0037077104"
            },
            "royal rose oil company limited": {
                "name": "ROYAL ROSE OIL COMPANY LIMITED",
                "tin": "C0004700937"
            },
            "sephem oil company limited": {
                "name": "SEPHEM OIL COMPANY LIMITED",
                "tin": "C0001972154"
            },
            "torrid global company limited": {
                "name": "TORRID GLOBAL COMPANY LIMITED",
                "tin": "C0047563664"
            },
            "unique oil company limited": {
                "name": "UNIQUE OIL COMPANY LIMITED",
                "tin": "C0003165639"
            },
            "hak oil company limited": {
                "name": "HAK OIL COMPANY LIMITED",
                "tin": "C000549592X"
            },
            "fueltrade limited": {
                "name": "FUELTRADE LIMITED",
                "tin": "C0002862840"
            },
            "petrol xp ghana limited": {
                "name": "PETROL XP GHANA LIMITED",
                "tin": "C0021876134"
            },
            "total petroleum ghana limited": {
                "name": "TOTAL PETROLEUM GHANA LIMITED",
                "tin": "C0003140083"
            },
            "puma energy distribution ghana limited": {
                "name": "PUMA ENERGY DISTRIBUTION GHANA LIMITED",
                "tin": "C0003220419"
            },
            "ai energy and petroleum limited": {
                "name": "AI ENERGY AND PETROLEUM LIMITED",
                "tin": "C0003400964"
            },
            "so energy (ghana) limited": {
                "name": "SO ENERGY (GHANA) LIMITED",
                "tin": "C0003665054"
            },
            "vivo energy ghana limited": {
                "name": "VIVO ENERGY GHANA LIMITED",
                "tin": "C000327778X"
            },
            "goil company limited": {
                "name": "GOIL COMPANY LIMITED",
                "tin": "C0003156842"
            },
            "quantum petroleum limited": {
                "name": "QUANTUM PETROLEUM LIMITED",
                "tin": "C000313721X"
            },
            "strategic energies limited": {
                "name": "STRATEGIC ENERGIES LIMITED",
                "tin": "C0003137244"
            },
            "nasona oil company limited": {
                "name": "NASONA OIL COMPANY LIMITED",
                "tin": "C0004051580"
            },
            "petro sankofa limited": {
                "name": "PETRO SANKOFA LIMITED",
                "tin": "C0021485674"
            },
            "jd-link oil company limited": {
                "name": "JD-LINK OIL COMPANY LIMITED",
                "tin": "C0001851519"
            },
            "tel energy limited": {
                "name": "TEL ENERGY LIMITED",
                "tin": "C000241385X"
            },
            "dabemens limited": {
                "name": "DABEMENS LIMITED",
                "tin": "C0004164997"
            },
            "p & o energy company limited": {
                "name": "P & O ENERGY COMPANY LIMITED",
                "tin": "C0020054572"
            },
            "karela oil and gas company limited": {
                "name": "KARELA OIL AND GAS COMPANY LIMITED",
                "tin": "C0003538516"
            },
            "ibm petroleum limited": {
                "name": "IBM PETROLEUM LIMITED",
                "tin": "C0002623706"
            },
            "naagamni ghana limited": {
                "name": "NAAGAMNI GHANA LIMITED",
                "tin": "C0004134222"
            },
            "sawadigo oil company limited": {
                "name": "SAWADIGO OIL COMPANY LIMITED",
                "tin": "C0005665582"
            },
            "coegan ghana limited": {
                "name": "COEGAN GHANA LIMITED",
                "tin": "C0001773925"
            },
            "kabore oil limited": {
                "name": "KABORE OIL LIMITED",
                "tin": "C0002860465"
            },
            "alive gas services limited": {
                "name": "ALIVE GAS SERVICES LIMITED",
                "tin": "C0004407199"
            },
            "lambark gas company limited.": {
                "name": "LAMBARK GAS COMPANY LIMITED.",
                "tin": "C000534123X"
            },
            "glory oil company limited": {
                "name": "GLORY OIL COMPANY LIMITED",
                "tin": "C0003136930"
            },
            "rodo oil limited": {
                "name": "RODO OIL LIMITED",
                "tin": "C0031096557"
            },
            "amdaway oil company limited": {
                "name": "AMDAWAY OIL COMPANY LIMITED",
                "tin": "C0006073425"
            },
            "sap oil limited": {
                "name": "SAP OIL LIMITED",
                "tin": "C0010060782"
            },
            "beap energy ghana  limited": {
                "name": "BEAP ENERGY GHANA  LIMITED",
                "tin": "C0004002547"
            },
            "glasark oil company limited": {
                "name": "GLASARK OIL COMPANY LIMITED",
                "tin": "C0005791812"
            },
            "zoe petroleum limited": {
                "name": "ZOE PETROLEUM LIMITED",
                "tin": "C0004435184"
            },
            "aminasar oil company limited": {
                "name": "AMINASAR OIL COMPANY LIMITED",
                "tin": "C0003236684"
            },
            "ev oil company limited": {
                "name": "EV OIL COMPANY LIMITED",
                "tin": "C0004952073"
            },
            "mercy oil marketing company limited": {
                "name": "MERCY OIL MARKETING COMPANY LIMITED",
                "tin": "C0005149495"
            },
            "alinco oil company limited": {
                "name": "ALINCO OIL COMPANY LIMITED",
                "tin": "C0005072441"
            },
            "r & p oil company limited": {
                "name": "R & P OIL COMPANY LIMITED",
                "tin": "C0007063148"
            },
            "central brent petroleum limited": {
                "name": "CENTRAL BRENT PETROLEUM LIMITED",
                "tin": "C0004489365"
            },
            "runel oil limited": {
                "name": "RUNEL OIL LIMITED",
                "tin": "C000362885X"
            },
            "nextbons gas limited": {
                "name": "NEXTBONS GAS LIMITED",
                "tin": "C0004976355"
            },
            "cigo energy limited": {
                "name": "CIGO ENERGY LIMITED",
                "tin": "C003707671X"
            },
            "fraga oil ghana limited": {
                "name": "FRAGA OIL GHANA LIMITED",
                "tin": "C0003168727"
            },
            "kan royal services station and trading limited": {
                "name": "KAN ROYAL SERVICES STATION AND TRADING LIMITED",
                "tin": "C0002582112"
            },
            "superior oil company limited": {
                "name": "SUPERIOR OIL COMPANY LIMITED",
                "tin": "C0002788381"
            },
            "xpress gas limited": {
                "name": "XPRESS GAS LIMITED",
                "tin": "C0005235979"
            },
            "grid petroleum (gh) limited": {
                "name": "GRID PETROLEUM (GH) LIMITED",
                "tin": "C0000718572"
            },
            "maxxon petroleum limited": {
                "name": "MAXXON PETROLEUM LIMITED",
                "tin": "C0004925270"
            },
            "galaxy oil ghana limited": {
                "name": "GALAXY OIL GHANA LIMITED",
                "tin": "C0002443422"
            },
            "best petroleum ghana limited": {
                "name": "BEST PETROLEUM GHANA LIMITED",
                "tin": "C0010611797"
            },
            "gowell energy limited": {
                "name": "GOWELL ENERGY LIMITED",
                "tin": "C0030109523"
            },
            "mobik energy limited": {
                "name": "MOBIK ENERGY LIMITED",
                "tin": "C0014834863"
            },
            "zen petroleum limited": {
                "name": "ZEN PETROLEUM LIMITED",
                "tin": "C0000418757"
            },
            "star oil company limited": {
                "name": "STAR OIL COMPANY LIMITED",
                "tin": "C0002780968"
            },
            "bf petroleum limited": {
                "name": "BF PETROLEUM LIMITED",
                "tin": "C0002631490"
            },
            "brent petroleum limited": {
                "name": "BRENT PETROLEUM LIMITED",
                "tin": "C0010234012"
            },
            "frimps oil company limited": {
                "name": "FRIMPS OIL COMPANY LIMITED",
                "tin": "C000278808X"
            },
            "manbah gas company limited": {
                "name": "MANBAH GAS COMPANY LIMITED",
                "tin": "C0004032829"
            },
            "triple a lp gas limited": {
                "name": "TRIPLE A LP GAS LIMITED",
                "tin": "C0004115988"
            },
            "patrick k.a. bonney and company limited": {
                "name": "PATRICK K.A. BONNEY AND COMPANY LIMITED",
                "tin": "C0002004631"
            },
            "cost energy limited": {
                "name": "COST ENERGY LIMITED",
                "tin": "C004773700X"
            },
            "infin ghana limited": {
                "name": "INFIN GHANA LIMITED",
                "tin": "C0003139921"
            },
            "rootsenaf gas company limited": {
                "name": "ROOTSENAF GAS COMPANY LIMITED",
                "tin": "C000150732X"
            },
            "world gas company limited": {
                "name": "WORLD GAS COMPANY LIMITED",
                "tin": "C0004951506"
            },
            "perfect petroleum company limited": {
                "name": "PERFECT PETROLEUM COMPANY LIMITED",
                "tin": "C0034843922"
            },
            "dukes petroleum  company  limited": {
                "name": "DUKES PETROLEUM  COMPANY  LIMITED",
                "tin": "C0004908546"
            },
            "crown petroleum ghana limited": {
                "name": "CROWN PETROLEUM GHANA LIMITED",
                "tin": "C0002552434"
            },
            "safety petroleum limited": {
                "name": "SAFETY PETROLEUM LIMITED",
                "tin": "C0047031670"
            },
            "royal energy company limited": {
                "name": "ROYAL ENERGY COMPANY LIMITED",
                "tin": "C0004211340"
            },
            "excel oil company limited": {
                "name": "EXCEL OIL COMPANY LIMITED",
                "tin": "C0002486768"
            },
            "seam oil company limited": {
                "name": "SEAM OIL COMPANY LIMITED",
                "tin": "C0002758091"
            },
            "top oil company limited": {
                "name": "TOP OIL COMPANY LIMITED",
                "tin": "C0002631261"
            },
            "ocean oil company limited": {
                "name": "OCEAN OIL COMPANY LIMITED",
                "tin": "C0004395336"
            },
            "sotei energy limited": {
                "name": "SOTEI ENERGY LIMITED",
                "tin": "C0009217061"
            },
            "anasset company limited": {
                "name": "ANASSET COMPANY LIMITED",
                "tin": "C000510775X"
            },
            "jas petroleum limited": {
                "name": "JAS PETROLEUM LIMITED",
                "tin": "C0020100450"
            },
            "ready oil limited": {
                "name": "READY OIL LIMITED",
                "tin": "C0004050630"
            },
            "sayon energy company limited": {
                "name": "SAYON ENERGY COMPANY LIMITED",
                "tin": "C0031778542"
            },
            "k. i. energy limited": {
                "name": "K. I. ENERGY LIMITED",
                "tin": "C0003424308"
            },
            "aegis huile company limited": {
                "name": "AEGIS HUILE COMPANY LIMITED",
                "tin": "C0010240454"
            },
            "laminin bee ventures limited": {
                "name": "LAMININ BEE VENTURES LIMITED",
                "tin": "C0005149193"
            },
            "jp trustees limited": {
                "name": "JP TRUSTEES LIMITED",
                "tin": "C0008440255"
            },
            "frontier oil ghana limited": {
                "name": "FRONTIER OIL GHANA LIMITED",
                "tin": "C0002480182"
            },
            "shelleyco petroleum limited": {
                "name": "SHELLEYCO PETROLEUM LIMITED",
                "tin": "C0002952734"
            },
            "thomhcof energy limited": {
                "name": "THOMHCOF ENERGY LIMITED",
                "tin": "C0005929830"
            },
            "riseglobe energy limited": {
                "name": "RISEGLOBE ENERGY LIMITED",
                "tin": "C0030555299"
            },
            "hills oil marketing company limited": {
                "name": "HILLS OIL MARKETING COMPANY LIMITED",
                "tin": "C0001717464"
            },
            "nuru oil company limited": {
                "name": "NURU OIL COMPANY LIMITED",
                "tin": "C0001412655"
            },
            "baffour gas company limited": {
                "name": "BAFFOUR GAS COMPANY LIMITED",
                "tin": "C0005357985"
            },
            "veros petroleum limited": {
                "name": "VEROS PETROLEUM LIMITED",
                "tin": "C0043439527"
            },
            "first gas company limited": {
                "name": "FIRST GAS COMPANY LIMITED",
                "tin": "C0004437551"
            },
            "gab energy limited": {
                "name": "GAB ENERGY LIMITED",
                "tin": "C000778306X"
            },
            "viggo energy limited": {
                "name": "VIGGO ENERGY LIMITED",
                "tin": "C0023816937"
            },
            "nujenix company limited": {
                "name": "NUJENIX COMPANY LIMITED",
                "tin": "C0006440495"
            },
            "jusbro petroleum company limited": {
                "name": "JUSBRO PETROLEUM COMPANY LIMITED",
                "tin": "C0004919238"
            },
            "go-gas ventures limited": {
                "name": "GO-GAS VENTURES LIMITED",
                "tin": "C0005373867"
            },
            "petrocell limited": {
                "name": "PETROCELL LIMITED",
                "tin": "C0001447602"
            },
            "virgin petroleum limited": {
                "name": "VIRGIN PETROLEUM LIMITED",
                "tin": "C0004629493"
            },
            "gaso petroleum limited": {
                "name": "GASO PETROLEUM LIMITED",
                "tin": "C0003093689"
            },
            "midas oil & gas limited": {
                "name": "MIDAS OIL & GAS LIMITED",
                "tin": "C0004104056"
            },
            "trinity oil company limited": {
                "name": "TRINITY OIL COMPANY LIMITED",
                "tin": "C0003628612"
            },
            "reliance oil limited": {
                "name": "RELIANCE OIL LIMITED",
                "tin": "C0011030925"
            },
            "gb oil limited": {
                "name": "GB OIL LIMITED",
                "tin": "C0009791531"
            },
            "ap oil & gas ghana limited": {
                "name": "AP OIL & GAS GHANA LIMITED",
                "tin": "C0002787911"
            },
            "pacific oil ghana limited": {
                "name": "PACIFIC OIL GHANA LIMITED",
                "tin": "C0004627024"
            },
            "west africa petroleum company limited": {
                "name": "WEST AFRICA PETROLEUM COMPANY LIMITED",
                "tin": "C0004003551"
            },
            "blackrock energy limited": {
                "name": "BLACKROCK ENERGY LIMITED",
                "tin": "C0017286395"
            },
            "gulf energy ghana limited": {
                "name": "GULF ENERGY GHANA LIMITED",
                "tin": "C0001933876"
            },
            "oval energy company limited": {
                "name": "OVAL ENERGY COMPANY LIMITED",
                "tin": "C0050501399"
            },
            "annandale ghana limited": {
                "name": "ANNANDALE GHANA LIMITED",
                "tin": "C0005357993"
            },
            "nick petroleum ghana limited": {
                "name": "NICK PETROLEUM GHANA LIMITED",
                "tin": "C0004138481"
            },
            "concord oil limited": {
                "name": "CONCORD OIL LIMITED",
                "tin": "C0002935376"
            },
            "bg petroleum company limited": {
                "name": "BG PETROLEUM COMPANY LIMITED",
                "tin": "C0004728491"
            },
            "sama oil company limited": {
                "name": "SAMA OIL COMPANY LIMITED",
                "tin": "C0006427316"
            },
            "aminso energy limited": {
                "name": "AMINSO ENERGY LIMITED",
                "tin": "C0028304047"
            },
            "allied oil company limited": {
                "name": "ALLIED OIL COMPANY LIMITED",
                "tin": "C0002780755"
            },
            "andev company limited": {
                "name": "ANDEV COMPANY LIMITED",
                "tin": "C000421143X"
            },
            "unicorn petroleum limited": {
                "name": "UNICORN PETROLEUM LIMITED",
                "tin": "C0021790981"
            },
            "razs oil ghana limited": {
                "name": "RAZS OIL GHANA LIMITED",
                "tin": "C0021500193"
            },
            "shakainah ventures limited": {
                "name": "SHAKAINAH VENTURES LIMITED",
                "tin": "C0004292413"
            },
            "lucky oil company limited": {
                "name": "LUCKY OIL COMPANY LIMITED",
                "tin": "C000411390X"
            },
            "goodness energy limited": {
                "name": "GOODNESS ENERGY LIMITED",
                "tin": "C0005905168"
            },
            "oil-space ghana limited": {
                "name": "OIL-SPACE GHANA LIMITED",
                "tin": "C0000832618"
            },
            "joekona company limited": {
                "name": "JOEKONA COMPANY LIMITED",
                "tin": "C0005133335"
            },
            "norgaz petroleum limited": {
                "name": "NORGAZ PETROLEUM LIMITED",
                "tin": "C0000606189"
            },
            "kings energy limited": {
                "name": "KINGS ENERGY LIMITED",
                "tin": "C0001695703"
            },
            "radiance petroleum limited": {
                "name": "RADIANCE PETROLEUM LIMITED",
                "tin": "C0001038109"
            },
            "plus energy limited": {
                "name": "PLUS ENERGY LIMITED",
                "tin": "C0005574021"
            },
            "engen ghana limited": {
                "name": "ENGEN GHANA LIMITED",
                "tin": "C000316540X"
            },
            "n3 limited": {
                "name": "N3 LIMITED",
                "tin": "C0000637602"
            },
            "unity oil company limited": {
                "name": "UNITY OIL COMPANY LIMITED",
                "tin": "C0003137309"
            },
            "cash oil company limited": {
                "name": "CASH OIL COMPANY LIMITED",
                "tin": "C0004153669"
            },
            "petrosol ghana limited": {
                "name": "PETROSOL GHANA LIMITED",
                "tin": "C0003807274"
            },
            "maxx energy limited": {
                "name": "MAXX ENERGY LIMITED",
                "tin": "C0002554933"
            },
            "desert oil ghana limited": {
                "name": "DESERT OIL GHANA LIMITED",
                "tin": "C0005032369"
            },
            "trade-cross limited": {
                "name": "TRADE-CROSS LIMITED",
                "tin": "C0004121929"
            },
            "petroland limited": {
                "name": "PETROLAND LIMITED",
                "tin": "C0003085619"
            },
            "mighty gas company limited": {
                "name": "MIGHTY GAS COMPANY LIMITED",
                "tin": "C0005899990"
            },
            "la clem ghana limited": {
                "name": "LA CLEM GHANA LIMITED",
                "tin": "C0006560881"
            },
            "kaysens  gas  company limited": {
                "name": "KAYSENS  GAS  COMPANY LIMITED",
                "tin": "C0000897744"
            },
            "t-tekpor energy limited": {
                "name": "T-TEKPOR ENERGY LIMITED",
                "tin": "C0001714872"
            },
            "lone star gas company limited": {
                "name": "LONE STAR GAS COMPANY LIMITED",
                "tin": "C0003628787"
            },
            "wright gas company limited": {
                "name": "WRIGHT GAS COMPANY LIMITED",
                "tin": "C0005147751"
            },
            "next petroleum limited": {
                "name": "NEXT PETROLEUM LIMITED",
                "tin": "C0031621449"
            },
            "power fuels distribution company limited": {
                "name": "POWER FUELS DISTRIBUTION COMPANY LIMITED",
                "tin": "C0004155750"
            },
            "yokwa gas limited": {
                "name": "YOKWA GAS LIMITED",
                "tin": "C000321365X"
            },
            "kros energy limited": {
                "name": "KROS ENERGY LIMITED",
                "tin": "C0009595996"
            },
            "cent-eastern gas limited": {
                "name": "CENT-EASTERN GAS LIMITED",
                "tin": "C0004566882"
            },
            "benab oil company limited": {
                "name": "BENAB OIL COMPANY LIMITED",
                "tin": "C000416606X"
            },
            "titan petroleum limited": {
                "name": "TITAN PETROLEUM LIMITED",
                "tin": "C0046081046"
            },
            "l. link petroleum company limited": {
                "name": "L. LINK PETROLEUM COMPANY LIMITED",
                "tin": "C000485165X"
            },
            "compass oleum limited": {
                "name": "COMPASS OLEUM LIMITED",
                "tin": "C0000317853"
            },
            "orient energy limited": {
                "name": "ORIENT ENERGY LIMITED",
                "tin": "C0004809998"
            },
            "bello petroleum limited": {
                "name": "BELLO PETROLEUM LIMITED",
                "tin": "C0037790668"
            },
            "finest oil company limited": {
                "name": "FINEST OIL COMPANY LIMITED",
                "tin": "C0000459542"
            },
            "societe national burkinabe (sonabhy)": {
                "name": "SOCIETE NATIONAL BURKINABE (SONABHY)",
                "tin": "BF00002923H"
            },
            "eden petroleum limited": {
                "name": "EDEN PETROLEUM LIMITED",
                "tin": "C0023765259"
            },
            "janda ghana limited": {
                "name": "JANDA GHANA LIMITED",
                "tin": "C0006218334"
            },
            "west port petroleum limited": {
                "name": "WEST PORT PETROLEUM LIMITED",
                "tin": "C0039362515"
            }
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return null;
        }

        //return the maped name
        return name_map[name];
    },
    async bdc_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "bulk oil storage and transportation": {
                "name": "BULK OIL STORAGE AND TRANSPORTATION",
                "code": "DBC1628105170554"
            },
            "hask oil company limited": {
                "name": "HASK OIL COMPANY LIMITED",
                "code": "DBC1628168496944"
            },
            "vihama energy company limited": {
                "name": "VIHAMA ENERGY COMPANY LIMITED",
                "code": "DBC1624303275078"
            },
            "kpabulga energy limited": {
                "name": "KPABULGA ENERGY LIMITED",
                "code": "DBC1628179551090"
            },
            "petroleum ware housing and supplies limited": {
                "name": "PETROLEUM WARE HOUSING AND SUPPLIES LIMITED",
                "code": "DBC1624303275137"
            },
            "genysis global limited": {
                "name": "GENYSIS GLOBAL LIMITED",
                "code": "DBC1628104866511"
            },
            "battop energy limited": {
                "name": "BATTOP ENERGY LIMITED",
                "code": "DBC1628105170592"
            },
            "cirrus oil services limited": {
                "name": "CIRRUS OIL SERVICES LIMITED",
                "code": "DBC1624303275146"
            },
            "nenser petroleum ghana limited": {
                "name": "NENSER PETROLEUM GHANA LIMITED",
                "code": "DBC1624303275147"
            },
            "goenergy company limited": {
                "name": "GOENERGY COMPANY LIMITED",
                "code": "DBC1624303275151"
            },
            "med petroleum company limited": {
                "name": "MED PETROLEUM COMPANY LIMITED",
                "code": "DBC1624303275153"
            },
            "sage distribution limited": {
                "name": "SAGE DISTRIBUTION LIMITED",
                "code": "DBC1624303275203"
            },
            "chase petroleum ghana limited": {
                "name": "CHASE PETROLEUM GHANA LIMITED",
                "code": "DBC1624303275204"
            },
            "blue ocean investments limited": {
                "name": "BLUE OCEAN INVESTMENTS LIMITED",
                "code": "DBC1624303275208"
            },
            "alfapetro ghana limited": {
                "name": "ALFAPETRO GHANA LIMITED",
                "code": "DBC1624303275211"
            },
            "oilchannel limited": {
                "name": "OILCHANNEL LIMITED",
                "code": "DBC1624303275215"
            },
            "maranatha oil services limited": {
                "name": "MARANATHA OIL SERVICES LIMITED",
                "code": "DBC1624303275264"
            },
            "juwel energy limited": {
                "name": "JUWEL ENERGY LIMITED",
                "code": "DBC1624303275280"
            },
            "lhs ghana limited": {
                "name": "LHS GHANA LIMITED",
                "code": "DBC1624303275304"
            },
            "fueltrade limited": {
                "name": "FUELTRADE LIMITED",
                "code": "DBC1624303275306"
            },
            "nation services company limited": {
                "name": "NATION SERVICES COMPANY LIMITED",
                "code": "DBC1624303275319"
            },
            "woodfields energy resources limited": {
                "name": "WOODFIELDS ENERGY RESOURCES LIMITED",
                "code": "DBC1624303275518"
            },
            "eagle petroleum company limited": {
                "name": "EAGLE PETROLEUM COMPANY LIMITED",
                "code": "DBC1624303275520"
            },
            "matrix gas (ghana) limited": {
                "name": "MATRIX GAS (GHANA) LIMITED",
                "code": "DBC1624303275533"
            },
            "dominion international petroleum limited": {
                "name": "DOMINION INTERNATIONAL PETROLEUM LIMITED",
                "code": "DBC1624303275546"
            },
            "astra oil services limited": {
                "name": "ASTRA OIL SERVICES LIMITED",
                "code": "DBC1624303275550"
            },
            "glorymay petroleum limited company": {
                "name": "GLORYMAY PETROLEUM LIMITED COMPANY",
                "code": "DBC1624303275564"
            },
            "everstone energy limited": {
                "name": "EVERSTONE ENERGY LIMITED",
                "code": "DBC1624303275644"
            },
            "rama energy limited": {
                "name": "RAMA ENERGY LIMITED",
                "code": "DBC1624303275651"
            },
            "firm energy limited": {
                "name": "FIRM ENERGY LIMITED",
                "code": "DBC1624303275657"
            },
            "akwaaba oil refinery limited": {
                "name": "AKWAABA OIL REFINERY LIMITED",
                "code": "DBC1624303275756"
            },
            "oiltrade company limited": {
                "name": "OILTRADE COMPANY LIMITED",
                "code": "DBC1624303275813"
            },
            "globex energy limited": {
                "name": "GLOBEX ENERGY LIMITED",
                "code": "DBC1624303275877"
            },
            "mobile oil energy resources ghana limited": {
                "name": "MOBILE OIL ENERGY RESOURCES GHANA LIMITED",
                "code": "DBC1624303275917"
            },
            "stratcon energy and trading limited": {
                "name": "STRATCON ENERGY AND TRADING LIMITED",
                "code": "DBC1624303277162"
            },
            "lemla petroleum limited": {
                "name": "LEMLA PETROLEUM LIMITED",
                "code": "DBC1624303277180"
            },
            "platon gas oil ghana limited": {
                "name": "PLATON GAS OIL GHANA LIMITED",
                "code": "DBC1624303277249"
            },
            "rhema energy company limited": {
                "name": "RHEMA ENERGY COMPANY LIMITED",
                "code": "DBC1624303278074"
            },
            "misyl energy company limited": {
                "name": "MISYL ENERGY COMPANY LIMITED",
                "code": "DBC1624303278241"
            },
            "societe national burkinabe (sonabhy)": {
                "name": "SOCIETE NATIONAL BURKINABE (SONABHY)",
                "code": "DBC1624303279321"
            },
            "dome energy resources limited": {
                "name": "DOME ENERGY RESOURCES LIMITED",
                "code": "DBC1624303283381"
            },
            "s a energy limited": {
                "name": "S A ENERGY LIMITED",
                "code": "DBC1624303284094"
            },
            "mariaje linx investment limited": {
                "name": "MARIAJE LINX INVESTMENT LIMITED",
                "code": "DBC1624303284330"
            }
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return null;
        }

        //return the maped name
        return name_map[name];
    },
    async product_name(name) {
        name = `${name}`.toLowerCase().trim();
        const name_map = {
            "pms (petrol)": { name: "PMS", code: "2710113200" },
            "vpower": { name: "PMS", code: "2710113200" },
            "pms/vpower": { name: "PMS", code: "2710113200" },
            "pms": { name: "PMS", code: "2710113200" },
            "premix": { name: "PREMIX", code: "2710113900" },
            "pmx": { name: "PREMIX", code: "2710113900" },
            "atk": { name: "AVIATION TURBINE KEROSENE", code: "2710114100" },
            "aviation turbine kerosene": { name: "AVIATION TURBINE KEROSENE", code: "2710114100" },
            "kerosene": { name: "KEROSENE", code: "2710114200" },
            "kero": { name: "KEROSENE", code: "2710114200" },
            "naphtha": { name: "NAPHTHA", code: "2710111000" },
            "naptha": { name: "NAPHTHA", code: "2710111000" },
            "lpg": { name: "LPG", code: "2711130000" },
            "rfo": { name: "RFO", code: "2710115900" },
            "ago Mines": { name: "AGO", code: "2710115110" },
            "ago rig": { name: "AGO", code: "2710115110" },
            "mgo": { name: "MGO", code: "2710115120" },
            "mgo Foreign": { name: "MGO", code: "2710115120" },
            "unified": { name: "UNIFIED", code: "3710115120" },
            "diesel": { name: "DIESEL", code: "3710115121" },
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(name)) {
            return null;
        }

        //return the maped name
        return name_map[name];
    },
    async product_name_by_code(code) {
        code = `${code}`.toLowerCase().trim();
        const name_map = {
            "2710111000": { name: "NAPHTHA", code: "2710111000" },
            "2710113200": { name: "PMS", code: "2710113200" },
            "2710113900": { name: "PREMIX", code: "2710113900" },
            "2710114100": { name: "AVIATION TURBINE KEROSENE", code: "2710114100" },
            "2710114200": { name: "KEROSENE", code: "2710114200" },
            "2710115110": { name: "AGO", code: "2710115110" },
            "2710115120": { name: "MGO", code: "2710115120" },
            "2710115900": { name: "RFO", code: "2710115900" },
            "2711130000": { name: "LPG", code: "2711130000" },
            "3710115120": { name: "UNIFIED", code: "3710115120" },
            "3710115121": { name: "DIESEL", code: "3710115121" },
        };

        //check if name has been maped if not
        //return the name
        if (!name_map.hasOwnProperty(code)) {
            return null;
        }

        //return the maped name
        return name_map[code];
    },
}