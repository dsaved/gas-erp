/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

``
export default [{
        url: '/petroleum',
        name: 'Home',
        slug: 'home',
        page: 'Petroleum Home',
        icon: 'HomeIcon'
    }, {
        url: null,
        name: 'Tax',
        icon: 'BarChart2Icon',
        page: 'Tax',
        i18n: 'Tax',
        submenu: [{
                url: '/petroleum/tax-type',
                name: 'Tax Type',
                page: 'Tax Type',
                slug: 'tax-type',
                i18n: 'SetUpTaxType'
            }, {
                url: '/petroleum/tax-products',
                name: 'Products',
                page: 'Tax Products',
                slug: 'sml-npa-reconciliation',
                i18n: 'SetupProducts'
            },
            {
                url: '/petroleum/tax-schedule',
                name: 'Tax Schedule',
                page: 'Tax Schedule',
                slug: 'tax-schedule',
                i18n: 'TaxSchadule'
            }, {
                url: '/petroleum/exemptions',
                name: 'Exemptions',
                page: 'Exemptions',
                slug: 'exemptions',
                i18n: 'Exemptions'
            },
            {
                url: '/petroleum/tax-window',
                name: 'Tax Window',
                page: 'Tax Window',
                slug: 'tax-window',
                i18n: 'TaxWindow'
            }
        ]
    }, {
        url: '/petroleum/compute',
        name: 'Compute',
        page: 'Compute Tax Schedule',
        slug: 'compute',
        icon: 'CpuIcon',
        i18n: 'Compute'
    },
    {
        url: null,
        name: 'OMC',
        page: 'OMC',
        icon: 'OctagonIcon',
        i18n: 'OMC',
        submenu: [{
                url: '/petroleum/omc',
                name: 'All OMC',
                page: 'All OMC',
                slug: 'All OMC',
                i18n: 'All OMC'
            },
            {
                url: '/petroleum/omc/fallouts',
                name: 'Fall Out Receipts',
                page: 'Fall Out Receipts',
                slug: 'Fall Out Receipts',
                i18n: 'Fall Out Receipts'
            },
            {
                url: '/petroleum/omc/logs',
                name: 'Reconcilation Log',
                page: 'Reconcilation Log',
                slug: 'Reconcilation Log',
                i18n: 'Reconcilation Log'
            }, {
                url: null,
                name: 'National Summary',
                page: 'National Summary',
                icon: 'FolderIcon',
                i18n: 'National Summary',
                submenu: [{
                    url: '/petroleum/omc/reconciled',
                    name: 'All Receipts',
                    page: 'All Receipts',
                    slug: 'All Receipts',
                    i18n: 'All Receipts',
                }, {
                    url: '/petroleum/omc/unreconciled',
                    name: 'Unreconciled',
                    page: 'Unreconciled Receipts',
                    slug: 'Unreconciled Receipts',
                    i18n: 'Unreconciled Receipts'
                }]
            }
        ]
    },
    // {
    //     url: null,
    //     name: 'Reconcile',
    //     page: 'Reconcile',
    //     icon: 'CommandIcon',
    //     i18n: 'Reconcile',
    //     submenu: [{
    //             url: '/petroleum/rcn/sml-npa',
    //             name: 'SML - NPA',
    //             page: 'SML - NPA Reconciliations',
    //             slug: 'sml-npa-reconciliation',
    //             i18n: 'SML - NPA'
    //         },
    //         {
    //             url: '/petroleum/rcn/npa-icums',
    //             name: 'NPA - ICUMS',
    //             page: 'NPA - ICOMS Reconciliations',
    //             slug: 'npa-icums-reconciliation',
    //             i18n: 'NPA ICOMS'
    //         },
    //         {
    //             url: '/petroleum/rcn/npa-npa',
    //             name: 'NPA - NPA',
    //             page: 'NPA - NPA Reconciliations',
    //             slug: 'npa-npa-reconciliation',
    //             i18n: 'NPA - NPA'
    //         }
    //     ]
    // },
    {
        url: null,
        name: 'Petroleum Input',
        page: 'Petroleum Input',
        icon: 'DropletIcon',
        i18n: 'Petroleum Input',
        submenu: [{
                url: '/petroleum/input/analytics',
                name: 'Analytics',
                page: 'Input Analytics',
                slug: 'Input Analytics',
                i18n: 'Input Analytics'
            }, {
                url: '/petroleum/input/manifest',
                name: 'Manifest',
                page: 'Manifest',
                slug: 'Manifest',
                i18n: 'Manifest'
            },
            {
                url: '/petroleum/input/declaration',
                name: 'Declaration',
                page: 'Declaration',
                slug: 'Declaration',
                i18n: 'Declaration'
            },
            {
                url: '/petroleum/input/reconciliation',
                name: 'Reconciliation',
                page: 'Reconciliation',
                slug: 'Reconciliation',
                i18n: 'Reconciliation'
            }
        ]
    }, {
        url: '/petroleum/stock-management',
        name: 'Stock Management',
        page: 'Stock Management',
        slug: 'Stock Management',
        icon: 'DatabaseIcon',
        i18n: 'Stock Management'
    },
    {
        url: null,
        name: 'NPA',
        page: 'NPA',
        icon: 'ActivityIcon',
        i18n: 'NPA',
        submenu: [
            /* {
                            url: '/petroleum/npa/analytics',
                            name: 'Analytics',
                            page: 'NPA Analytics',
                            slug: 'NPA Analytics',
                            i18n: 'NPA Analytics'
                        }, {
                            url: '/petroleum/npa/preorders',
                            name: 'Preorders',
                            page: 'Preorders',
                            slug: 'Preorders',
                            i18n: 'Preorders'
                        }, */
            {
                url: '/petroleum/npa/orders',
                name: 'Orders',
                page: 'Orders',
                slug: 'Orders',
                i18n: 'Orders'
            }
        ]
    },
    {
        url: null,
        name: 'ICUMS (CEPS)',
        icon: 'ActivityIcon',
        page: 'ICOMS (CEPS)',
        i18n: 'ICUMS (CEPS)',
        submenu: [{
                url: '/petroleum/icums/declearations',
                name: 'Declearations',
                page: 'ICOMS Declearations',
                slug: 'declearations',
                i18n: 'Declearations'
            },
            {
                url: '/petroleum/icums/differences',
                name: 'Differences',
                page: 'ICOMS Differences',
                slug: 'icums-differences',
                i18n: 'Differences'
            }
        ]
    },
    {
        url: null,
        name: 'SML',
        page: 'SML',
        icon: 'ActivityIcon',
        i18n: 'SML',
        submenu: [{
                url: '/petroleum/sml/inlet',
                name: 'Inlet',
                page: 'Inlet',
                slug: 'Inlet',
                i18n: 'Inlet'
            }, {
                url: '/petroleum/sml/inlet-report',
                name: 'Inlet Report',
                page: 'Inlet Report',
                slug: 'Inlet Report',
                i18n: 'Inlet Report'
            }, {
                url: '/petroleum/sml/outlet',
                name: 'Outlet',
                page: 'Outlet',
                slug: 'Outlet',
                i18n: 'Outlet'
            }, {
                url: '/petroleum/sml/outlet-report',
                name: 'Outlet Report',
                page: 'Outlet Report',
                slug: 'Outlet Report',
                i18n: 'Outlet Report'
            },
            // {
            //     url: '/petroleum/sml/undeclared-product',
            //     name: 'Undeclared Products',
            //     page: 'Undeclared Products',
            //     slug: 'Undeclared Products',
            //     i18n: 'Undeclared Products'
            // },
            // {
            //     url: '/petroleum/sml/missing-product',
            //     name: 'Missing Products',
            //     page: 'Missing Products',
            //     slug: 'Missing Products',
            //     i18n: 'Missing Products'
            // },
        ]
    }, {
        url: null,
        name: 'Waybill',
        page: 'Waybill',
        icon: 'FileMinusIcon',
        i18n: 'Waybill',
        submenu: [{
                url: '/petroleum/waybills',
                name: 'Waybills',
                page: 'Waybills',
                slug: 'Waybills',
                i18n: 'Waybills'
            }, {
                url: '/petroleum/waybill/analytics',
                name: 'Analytics',
                page: 'Waybill Analytics',
                slug: 'Analytics',
                i18n: 'Analytics'
            }, {
                url: '/petroleum/waybill/reconcile',
                name: 'Reconcile',
                page: 'Waybill Reconciliations',
                slug: 'Waybill-reconciliation',
                i18n: 'Waybill'
            },
            {
                url: '/petroleum/waybill/expected-declaration',
                name: 'Expected Declaration',
                page: 'Waybill Expected Declaration',
                slug: 'expected-declaration',
                i18n: 'Expected Declaration'
            },
            /* {
                           url: '/petroleum/waybill/stock-management',
                           name: 'Stock Management',
                           page: 'Waybill Stock Management',
                           slug: 'Stock Management',
                           icon: 'DatabaseIcon',
                           i18n: 'Stock Management'
                       }, */
        ]
    }, {
        url: null,
        name: 'Ghana.gov',
        page: 'Ghana.gov',
        icon: 'StopCircleIcon',
        i18n: 'Ghana.gov',
        submenu: [{
            url: '/petroleum/ghana.gov/reciepts',
            name: 'Reciepts',
            page: 'Ghana.gov Reciepts',
            slug: 'Ghana.gov Reciepts',
            i18n: 'Ghana.gov Reciepts'
        }, {
            url: '/petroleum/ghana.gov/standing',
            name: 'Debt Potfolio / Good Standing',
            page: 'Debt Potfolio / Good Standing',
            slug: 'Debt Potfolio / Good Standing',
            i18n: 'Debt Potfolio / Good Standing'
        }, ]
    }
]