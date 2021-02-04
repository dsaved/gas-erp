/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


export default [{
        url: '/petroleume',
        name: 'Home',
        slug: 'home',
        page: 'Petroleume Home',
        icon: 'HomeIcon'
    }, {
        url: null,
        name: 'Tax',
        icon: 'BarChart2Icon',
        page: 'Tax',
        i18n: 'Tax',
        submenu: [{
                url: '/petroleume/tax-type',
                name: 'Tax Type',
                page: 'Tax Type',
                slug: 'tax-type',
                i18n: 'SetUpTaxType'
            }, {
                url: '/petroleume/tax-products',
                name: 'Products',
                page: 'Tax Products',
                slug: 'sml-npa-reconcilation',
                i18n: 'SetupProducts'
            },
            {
                url: '/petroleume/tax-schedule',
                name: 'Tax Schedule',
                page: 'Tax Schedule',
                slug: 'tax-schedule',
                i18n: 'TaxSchadule'
            }, {
                url: '/petroleume/exemptions',
                name: 'Exemptions',
                page: 'Exemptions',
                slug: 'exemptions',
                i18n: 'Exemptions'
            }
        ]
    }, {
        url: '/petroleume/compute',
        name: 'Compute',
        page: 'Compute Tax Schedule',
        slug: 'compute',
        icon: 'CpuIcon',
        i18n: 'Compute'
    }, {
        url: '/petroleume/omc',
        name: 'OMC',
        page: 'OMCs',
        slug: 'OMCs',
        icon: 'OctagonIcon',
        i18n: 'OMCs'
    },
    {
        url: null,
        name: 'Reconcile',
        page: 'Reconcile',
        icon: 'CommandIcon',
        i18n: 'Reconcile',
        submenu: [{
                url: '/petroleume/rcn/sml-npa',
                name: 'SML - NPA',
                page: 'SML - NPA Reconcilations',
                slug: 'sml-npa-reconcilation',
                i18n: 'SML - NPA'
            },
            {
                url: '/petroleume/rcn/npa-icoms',
                name: 'NPA - ICOMS',
                page: 'NPA - ICOMS Reconcilations',
                slug: 'npa-icoms-reconcilation',
                i18n: 'NPA ICOMS'
            },
            {
                url: '/petroleume/rcn/npa-npa',
                name: 'NPA - NPA',
                page: 'NPA - NPA Reconcilations',
                slug: 'npa-npa-reconcilation',
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
                url: '/petroleume/npa/reconcilation-report',
                name: 'Reconcilation Reports',
                page: 'NPA Reconcilation Reports',
                slug: 'reconcilation-reports',
                i18n: 'Reports'
            },
            {
                url: '/petroleume/npa/daily-liftings',
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
                url: '/petroleume/icoms/declearations',
                name: 'Declearations',
                page: 'ICOMS Declearations',
                slug: 'declearations',
                i18n: 'Declearations'
            },
            {
                url: '/petroleume/icoms/payments',
                name: 'Payments',
                page: 'ICOMS Payments',
                slug: 'icom-payments',
                i18n: 'Payments'
            },
            {
                url: '/petroleume/icoms/differences',
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
            url: '/petroleume/sml/daily-liftings',
            name: 'Daily Liftings',
            page: 'SML Daily Liftings',
            slug: 'ecommerce-shop',
            i18n: 'DailyLifting'
        }]
    }


]