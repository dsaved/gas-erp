/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


export default [{
        url: '/revenue',
        name: 'Home',
        slug: 'home',
        page: 'Home',
        icon: 'HomeIcon'
    }, {
        url: '/revenue/accounts',
        name: 'Bank Accounts',
        slug: 'banks',
        page: 'Revenue Bank Accounts',
        icon: 'LayoutIcon'
    }, {
        url: '/revenue/responses',
        name: 'Audit Responses',
        slug: 'audit-response',
        page: 'Audit Responses',
        icon: 'MessageSquareIcon'
    }, {
        url: null,
        name: 'Surcharges',
        slug: 'surcharges',
        page: 'Surcharges',
        icon: 'CreditCardIcon',
        i18n: 'Surcharges',
        submenu: [{
                url: '/revenue/rates',
                name: 'Rates',
                page: 'Rates',
                slug: 'Rates',
                i18n: 'Rates'
            },
            {
                url: '/revenue/penalty',
                name: 'Penalty',
                page: 'Penalty',
                slug: 'Penalty',
                i18n: 'Penalty'
            },
        ]
    },
    {
        url: null,
        name: 'Unauthorized',
        icon: 'HelpCircleIcon',
        page: 'Unauthorized',
        i18n: 'Unauthorized',
        submenu: [{
            url: '/revenue/unauthorized/bog',
            name: 'Bank of Ghana',
            slug: 'bank-of-ghana',
            page: 'Unauthorized Bank of Ghana',
            i18n: 'bank-of-ghana'
        }, {
            url: '/revenue/unauthorized/org',
            name: 'Organizations',
            slug: 'other-banks',
            page: 'Unauthorized Other Banks',
            i18n: 'other-banks'
        }, {
            url: '/revenue/unauthorized/hidden',
            name: 'Hidden Transactions',
            slug: 'hidden-transactions',
            page: 'Unauthorized Hidden Transactions',
            i18n: 'hidden-transactions'
        }]
    },
    {
        url: '/revenue/logs',
        name: 'Audit Logs',
        slug: 'audit-response',
        page: 'Audit Logs',
        icon: 'ListIcon'
    }
]