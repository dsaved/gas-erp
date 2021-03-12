/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


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
                    name: 'Reconciled',
                    page: 'Reconciled Receipts',
                    slug: 'Reconciled Receipts',
                    i18n: 'Reconciled Receipts',
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
    {
        url: null,
        name: 'Reconcile',
        page: 'Reconcile',
        icon: 'CommandIcon',
        i18n: 'Reconcile',
        submenu: [{
                url: '/petroleum/rcn/sml-npa',
                name: 'SML - NPA',
                page: 'SML - NPA Reconcilations',
                slug: 'sml-npa-reconciliation',
                i18n: 'SML - NPA'
            },
            {
                url: '/petroleum/rcn/npa-icoms',
                name: 'NPA - ICOMS',
                page: 'NPA - ICOMS Reconcilations',
                slug: 'npa-icoms-reconciliation',
                i18n: 'NPA ICOMS'
            },
            {
                url: '/petroleum/rcn/npa-npa',
                name: 'NPA - NPA',
                page: 'NPA - NPA Reconcilations',
                slug: 'npa-npa-reconciliation',
                i18n: 'NPA - NPA'
            }
        ]
    },
    {
        url: null,
        name: 'NPA',
        page: 'NPA',
        icon: 'ActivityIcon',
        i18n: 'NPA',
        submenu: [{
                url: '/petroleum/npa/reconciliation-report',
                name: 'Reconcilation Reports',
                page: 'NPA Reconcilation Reports',
                slug: 'reconciliation-reports',
                i18n: 'Reports'
            },
            {
                url: '/petroleum/npa/daily-liftings',
                name: 'Daily Lifting',
                page: 'NPA Daily Liftings',
                slug: 'npa-daily-lifting',
                i18n: 'DailyLiftings'
            }
        ]
    },
    {
        url: null,
        name: 'ICOMS (CEPS)',
        icon: 'ActivityIcon',
        page: 'ICOMS (CEPS)',
        i18n: 'ICOMS (CEPS)',
        submenu: [{
                url: '/petroleum/icoms/declearations',
                name: 'Declearations',
                page: 'ICOMS Declearations',
                slug: 'declearations',
                i18n: 'Declearations'
            },
            {
                url: '/petroleum/icoms/payments',
                name: 'Payments',
                page: 'ICOMS Payments',
                slug: 'icom-payments',
                i18n: 'Payments'
            },
            {
                url: '/petroleum/icoms/differences',
                name: 'Differences',
                page: 'ICOMS Differences',
                slug: 'icoms-differences',
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
            url: '/petroleum/sml/daily-liftings',
            name: 'Daily Liftings',
            page: 'SML Daily Liftings',
            slug: 'ecommerce-shop',
            i18n: 'DailyLifting'
        }]
    }


]