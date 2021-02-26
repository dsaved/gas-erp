/*=========================================================================================
  File Name: router.js
  Description: Routes for vue-router. Lazy loading is enabled.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

const router = new Router({
    mode: 'history',
    base: process.env.BASE_URL,
    scrollBehavior() {
        return { x: 0, y: 0 }
    },
    routes: [

        {
            // =============================================================================
            // MAIN LAYOUT ROUTES
            // =============================================================================
            path: '',
            component: () =>
                import ('./layouts/main/Main.vue'),
            children: [
                // =============================================================================
                // Theme Routes
                // =============================================================================
                {
                    path: '/',
                    name: 'home',
                    component: () =>
                        import ('./views/Home.vue'),
                    meta: {
                        identity: "Home", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', active: true },
                        ],
                        homeurl: '/',
                        pageTitle: 'Ghana Audit Service'
                    }
                },
                // =============================================================================
                // USERS MENU START HERE
                // BDC MENU START HERE
                // =============================================================================
                {
                    path: '/accounts/bdc',
                    name: 'BDCs',
                    component: () =>
                        import ('./views/pages/users/bdc/BDCs.vue'),
                    meta: {
                        identity: "BDC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'BDC Users', active: true },
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/bdc/add',
                    name: 'Add BDC User',
                    component: () =>
                        import ('./views/pages/users/bdc/UserManage.vue'),
                    meta: {
                        identity: "BDC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'BDC Users', url: '/accounts/bdc' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/bdc/:userid',
                    name: 'Update BDC User',
                    component: () =>
                        import ('./views/pages/users/bdc/UserManage.vue'),
                    meta: {
                        identity: "BDC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'BDC Users', url: '/accounts/bdc' },
                            { title: 'Update User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                {
                    path: '/accounts/bdc/:userid/view',
                    name: 'User BDC Details',
                    component: () =>
                        import ('./views/pages/users/bdc/UserView.vue'),
                    meta: {
                        identity: "BDC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'BDC Users', url: '/accounts/bdc' },
                            { title: 'Details', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // OMC ORGANIZATION MANGE MENU START HERE
                // =============================================================================

                {
                    path: '/accounts/omc-org',
                    name: 'omc-org-list',
                    component: () =>
                        import ('./views/pages/omc-organization/OMC.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'OMCs', active: true },
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/omc-org/add',
                    name: 'omc-org-add',
                    component: () =>
                        import ('./views/pages/omc-organization/OMCManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'OMCs', url: '/accounts/omc-org' },
                            { title: 'Add', active: true },
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/omc-org/:omcid',
                    name: 'omc-org-edit',
                    component: () =>
                        import ('./views/pages/omc-organization/OMCManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'OMCs', url: '/accounts/omc-org' },
                            { title: 'Edit', active: true },
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true,
                },
                {
                    path: '/accounts/omc-org/:omcid/view',
                    name: 'omc-org-view',
                    component: () =>
                        import ('./views/pages/omc-organization/OMCView.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'OMCs', url: '/accounts/omc-org' },
                            { title: 'Veiw', active: true },
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true,
                },
                // =============================================================================
                // USERS OMC START HERE
                // =============================================================================
                {
                    path: '/accounts/omc',
                    name: 'OMCs',
                    component: () =>
                        import ('./views/pages/users/omc/OMCs.vue'),
                    meta: {
                        identity: "OMC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'OMC Users', active: true },
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/omc/add',
                    name: 'Add OMC User',
                    component: () =>
                        import ('./views/pages/users/omc/UserManage.vue'),
                    meta: {
                        identity: "OMC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'OMC Users', url: '/accounts/omc' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/omc/:userid',
                    name: 'Update OMC User',
                    component: () =>
                        import ('./views/pages/users/omc/UserManage.vue'),
                    meta: {
                        identity: "OMC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'OMC Users', url: '/accounts/omc' },
                            { title: 'Update User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                {
                    path: '/accounts/omc/:userid/view',
                    name: 'User OMC Details',
                    component: () =>
                        import ('./views/pages/users/omc/UserView.vue'),
                    meta: {
                        identity: "OMC users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'OMC Users', url: '/accounts/omc' },
                            { title: 'Details', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // USERS SYSTEM MENU START HERE
                // =============================================================================
                {
                    path: '/accounts/users',
                    name: 'Users',
                    component: () =>
                        import ('./views/pages/users/system/User.vue'),
                    meta: {
                        identity: "System Users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Users', active: true },
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/users/add',
                    name: 'Add User',
                    component: () =>
                        import ('./views/pages/users/system/UserManage.vue'),
                    meta: {
                        identity: "System Users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Users', url: '/accounts/users' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/users/:userid',
                    name: 'Update User',
                    component: () =>
                        import ('./views/pages/users/system/UserManage.vue'),
                    meta: {
                        identity: "System Users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Users', url: '/accounts/users' },
                            { title: 'Update User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                {
                    path: '/accounts/users/:userid/view',
                    name: 'User Details',
                    component: () =>
                        import ('./views/pages/users/system/UserView.vue'),
                    meta: {
                        identity: "System Users", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Users', url: '/accounts/users' },
                            { title: 'Details', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // USERS PROFILE START HERE
                // =============================================================================
                {
                    path: '/accounts/profile',
                    name: 'User Pofile',
                    component: () =>
                        import ('./views/pages/users/UserProfile.vue'),
                    meta: {
                        identity: "Account Profile", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'My Profile', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                // =============================================================================
                // ROLE MENU START HERE
                // =============================================================================
                {
                    path: '/accounts/roles',
                    name: 'User Role',
                    component: () =>
                        import ('./views/pages/users/UserRole.vue'),
                    meta: {
                        identity: 'System User Role', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'User Role', active: true },
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                },
                {
                    path: '/accounts/roles/add',
                    name: 'User Role Add',
                    component: () =>
                        import ('./views/pages/users/UserRoleManage.vue'),
                    meta: {
                        identity: 'System User Role', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'User Role', url: '/accounts/roles' },
                            { title: 'Add Role', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/roles/:id',
                    name: 'User Role Manage',
                    component: () =>
                        import ('./views/pages/users/UserRoleManage.vue'),
                    meta: {
                        identity: 'System User Role', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'User Role', url: '/accounts/roles' },
                            { title: 'Edit Role', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // Banks MENU START HERE
                // =============================================================================

                {
                    path: '/banks',
                    name: 'banks',
                    component: () =>
                        import ('./views/pages/banks/Banks.vue'),
                    meta: {
                        identity: 'Banks',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Banks', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Banks'
                    }
                }, {
                    path: '/banks/add',
                    name: 'ass-bank',
                    component: () =>
                        import ('./views/pages/banks/BankManage.vue'),
                    meta: {
                        identity: 'Banks',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Banks', url: '/banks' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Banks'
                    }
                }, {
                    path: '/banks/:bankid',
                    name: 'edit-bank',
                    component: () =>
                        import ('./views/pages/banks/BankManage.vue'),
                    meta: {
                        identity: 'Banks',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Banks', url: '/banks' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Banks'
                    },
                    props: true
                },
                // =============================================================================
                // BANK CATEGORY MENU START HERE
                // =============================================================================

                {
                    path: '/accounts/category',
                    name: 'accounts-category',
                    component: () =>
                        import ('./views/pages/banks/AccountCategories.vue'),
                    meta: {
                        identity: 'Account Categories',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Categories', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts Category'
                    }
                }, {
                    path: '/accounts/category/add',
                    name: 'add-category',
                    component: () =>
                        import ('./views/pages/banks/AccountCategoryManage.vue'),
                    meta: {
                        identity: 'Account Categories',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Categories', url: '/accounts/category' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts Category'
                    }
                }, {
                    path: '/accounts/category/:catid',
                    name: 'edit-category',
                    component: () =>
                        import ('./views/pages/banks/AccountCategoryManage.vue'),
                    meta: {
                        identity: 'Account Categories',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Categories', url: '/accounts/category' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts Category'
                    },
                    props: true
                },
                // =============================================================================
                // BANK ACCOUNTS MENU START HERE
                // =============================================================================

                {
                    path: '/accounts/bank-account',
                    name: 'bank-account',
                    component: () =>
                        import ('./views/pages/bank-account/Accounts.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    }
                }, {
                    path: '/accounts/bank-account/add',
                    name: 'add-bank-account',
                    component: () =>
                        import ('./views/pages/bank-account/AccountManage.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/accounts/bank-account' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    }
                }, {
                    path: '/accounts/bank-account/:accountid',
                    name: 'edit-bank-account',
                    component: () =>
                        import ('./views/pages/bank-account/AccountManage.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/accounts/bank-account' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                }, {
                    path: '/accounts/bank-account/:accountid/view',
                    name: 'view-bank-account',
                    component: () =>
                        import ('./views/pages/bank-account/AccountView.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/accounts/bank-account' },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // Banks MENU START HERE
                // =============================================================================

                {
                    path: '/organizations',
                    name: 'organizations',
                    component: () =>
                        import ('./views/pages/organizations/Organizations.vue'),
                    meta: {
                        identity: 'Organizations',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Organizations', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Organizations'
                    }
                }, {
                    path: '/organizations/add',
                    name: 'add-organizations',
                    component: () =>
                        import ('./views/pages/organizations/OrganizationManage.vue'),
                    meta: {
                        identity: 'Organizations',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Organizations', url: '/organizations' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Organizations'
                    }
                }, {
                    path: '/organizations/:orgid',
                    name: 'edit-organizations',
                    component: () =>
                        import ('./views/pages/organizations/OrganizationManage.vue'),
                    meta: {
                        identity: 'Organizations',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Organizations', url: '/organizations' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Organizations'
                    },
                    props: true
                }
            ]
        },
        {
            // =============================================================================
            // PATROLEUME LAYOUT ROUTES
            // =============================================================================
            path: '/petroleum',
            component: () =>
                import ('./layouts/main/Petroleum.vue'),
            children: [
                // =============================================================================
                // Theme Routes
                // =============================================================================
                {
                    path: '/petroleum',
                    name: 'petroleum home',
                    component: () =>
                        import ('./views/pages/petroleum/Home.vue'),
                    meta: {
                        identity: 'Petroleum', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Petroleum'
                    },
                },
                // =============================================================================
                // TASK MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/tax-type',
                    name: 'Tax List',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-type/Tax.vue'),
                    meta: {
                        identity: 'Tax Type', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Type', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-type/add',
                    name: 'add-tax-type',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-type/TaxManage.vue'),
                    meta: {
                        identity: 'Tax Type', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Type', url: '/petroleum/tax-type' },
                            { title: 'Add', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-type/:taxtypeid',
                    name: 'edit-tax-type',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-type/TaxManage.vue'),
                    meta: {
                        identity: 'Tax Type', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Type', url: '/petroleum/tax-type' },
                            { title: 'Edit', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/tax-products',
                    name: 'Tax Product List',
                    component: () =>
                        import ('./views/pages/petroleum/tax/products/Products.vue'),
                    meta: {
                        identity: 'Tax Products', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Products', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-product/add',
                    name: 'add-tax-product',
                    component: () =>
                        import ('./views/pages/petroleum/tax/products/ProductManage.vue'),
                    meta: {
                        identity: 'Tax Products', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Products', url: '/petroleum/tax-products' },
                            { title: 'Add', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-product/:taxproductid',
                    name: 'edit-tax-product',
                    component: () =>
                        import ('./views/pages/petroleum/tax/products/ProductManage.vue'),
                    meta: {
                        identity: 'Tax Products', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Products', url: '/petroleum/tax-products' },
                            { title: 'Edit', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/tax-schedule',
                    name: 'Tax Schadules',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-schedule/TaxSchadules.vue'),
                    meta: {
                        identity: 'Tax Schedule', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Schedules', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-schedule/add',
                    name: 'add-tax-schedule',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-schedule/TaxScheduleManage.vue'),
                    meta: {
                        identity: 'Tax Schedule', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Schedules', url: '/petroleum/tax-schedule' },
                            { title: 'Add', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/tax-schedule/:taxscheduleid',
                    name: 'edit-tax-schedule',
                    component: () =>
                        import ('./views/pages/petroleum/tax/tax-schedule/TaxScheduleManage.vue'),
                    meta: {
                        identity: 'Tax Schedule', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Tax Schedules', url: '/petroleum/tax-schedule' },
                            { title: 'Edit', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/exemptions',
                    name: 'Exemptions',
                    component: () =>
                        import ('./views/pages/petroleum/tax/exemptions/Exemptions.vue'),
                    meta: {
                        identity: 'Exemptions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Exemptions', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                },
                {
                    path: '/petroleum/exemptions/add',
                    name: 'add-exemptions',
                    component: () =>
                        import ('./views/pages/petroleum/tax/exemptions/ExemptionManage.vue'),
                    meta: {
                        identity: 'Exemptions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Exemptions', url: '/petroleum/exemptions' },
                            { title: 'Add', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/exemptions/:exemptionid',
                    name: 'edit-exemptions',
                    component: () =>
                        import ('./views/pages/petroleum/tax/exemptions/ExemptionManage.vue'),
                    meta: {
                        identity: 'Exemptions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Exemptions', url: '/petroleum/exemptions' },
                            { title: 'Edit', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true,
                }, {
                    path: '/petroleum/monitor',
                    name: 'petroleum-monitor-jobs',
                    component: () =>
                        import ('./views/pages/revenue/system/Monitor.vue'),
                    meta: {
                        identity: "neutral", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Monitor', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                }, {
                    path: '/petroleum/downloads',
                    name: 'petroleum-file-download',
                    component: () =>
                        import ('./views/pages/revenue/system/Downloads.vue'),
                    meta: {
                        identity: "neutral", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Downloads', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                },
                // =============================================================================
                // OMC ORGANIZATION MENU START HERE
                // =============================================================================

                {
                    path: '/petroleum/omc',
                    name: 'omc-list',
                    component: () =>
                        import ('./views/pages/petroleum/omc/OMC.vue'),
                    meta: {
                        identity: 'All OMC', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'OMCs', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                },
                {
                    path: '/petroleum/omc/:omcid/view',
                    name: 'omc-view',
                    component: () =>
                        import ('./views/pages/petroleum/omc/OMCView.vue'),
                    meta: {
                        identity: 'OMCs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'OMCs', url: '/petroleum/omc' },
                            { title: 'Veiw', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true,
                },
                // =============================================================================
                // OMC RECONCILE MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/omc/fallouts',
                    name: 'omc-reconciliation-fallout',
                    component: () =>
                        import ('./views/pages/petroleum/omc/fallout/OMC.vue'),
                    meta: {
                        identity: 'Fall Out Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Fall Out Receipts', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                },
                {
                    path: '/petroleum/omc/fallouts/:omcid/view',
                    name: 'omc-reconciliation-fallout-view',
                    component: () =>
                        import ('./views/pages/petroleum/omc/fallout/OMCView.vue'),
                    meta: {
                        identity: 'Fall Out Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Fall Out Receipts', url: '/petroleum/omc/fallouts' },
                            { title: 'View', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/omc/fallouts/:omcid/view/receipt/:receiptid/:page',
                    name: 'omc-view-receipt-fallout',
                    component: () =>
                        import ('./views/pages/petroleum/omc/fallout/OMCReceiptView.vue'),
                    meta: {
                        identity: 'OMCs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Fall Out Receipts', url: '/petroleum/omc/fallouts' },
                            { title: 'Veiw', active: true },
                            { title: 'Receipt', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true,
                },
                // =============================================================================
                // OMC RECONCILE LOG MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/omc/logs',
                    name: 'omc-reconcile-logs',
                    component: () =>
                        import ('./views/pages/petroleum/omc/logs/Accounts.vue'),
                    meta: {
                        identity: 'Reconcilation Log',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Reconcilation Log', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC Logs'
                    },
                }, {
                    path: '/petroleum/omc/logs/:accountid',
                    name: 'omc-reconcile-logs-transactions',
                    component: () =>
                        import ('./views/pages/petroleum/omc/logs/Transactions.vue'),
                    meta: {
                        identity: "Reconcilation Log",
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Reconcilation Log', active: true },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC Logs'
                    },
                    props: true
                },
                // =============================================================================
                // COMPUTE MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/omc/:omcid/compute',
                    name: 'omc-org-compute',
                    component: () =>
                        import ('./views/pages/petroleum/omc/OMCComputeLiftings.vue'),
                    meta: {
                        identity: 'OMCs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'OMCs', url: '/petroleum/omc' },
                            { title: 'Veiw', active: true },
                            { title: 'Compute', active: true },
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/omc/:omcid/reconcile',
                    name: 'omc-reconcile',
                    component: () =>
                        import ('./views/pages/petroleum/omc/OMCReconcile.vue'),
                    meta: {
                        identity: 'OMCs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'OMCs', url: '/petroleum/omc' },
                            { title: 'View', active: true },
                            { title: 'Reconcile', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true,
                },
                {
                    path: '/petroleum/omc/:omcid/view/receipt/:receiptid/:page',
                    name: 'omc-view-receipt',
                    component: () =>
                        import ('./views/pages/petroleum/omc/OMCReceiptView.vue'),
                    meta: {
                        identity: 'OMCs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'OMCs', url: '/petroleum/omc' },
                            { title: 'Veiw', active: true },
                            { title: 'Receipt', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true,
                },
                // =============================================================================
                // RECONCILE MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/rcn/sml-npa',
                    name: 'sml-npa',
                    component: () =>
                        import ('./views/pages/petroleum/reconcile/SmlNpa.vue')
                },
                {
                    path: '/petroleum/rcn/npa-icoms',
                    name: 'npa-icoms',
                    component: () =>
                        import ('./views/pages/petroleum/reconcile/NpaIcoms.vue')
                },
                {
                    path: '/petroleum/rcn/npa-npa',
                    name: 'npa-npa',
                    component: () =>
                        import ('./views/pages/petroleum/reconcile/NpaNpa.vue')
                },
                // =============================================================================
                // NPA MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/npa/reconciliation-report',
                    name: 'reconciliation-report',
                    component: () =>
                        import ('./views/pages/petroleum/npa/ReconcilationReport.vue')
                },
                {
                    path: '/petroleum/npa/daily-liftings',
                    name: 'npa daily-liftings',
                    component: () =>
                        import ('./views/pages/petroleum/npa/DailyLiftings.vue')
                },
                // =============================================================================
                // ICOMS-CEPS MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/icoms/declearations',
                    name: 'declearations',
                    component: () =>
                        import ('./views/pages/petroleum/icoms/Declearations.vue')
                },
                {
                    path: '/petroleum/icoms/payments',
                    name: 'payments',
                    component: () =>
                        import ('./views/pages/petroleum/icoms/Payments.vue')
                },
                {
                    path: '/petroleum/icoms/differences',
                    name: 'differences',
                    component: () =>
                        import ('./views/pages/petroleum/icoms/Differences.vue')
                },
                // =============================================================================
                // SML MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/sml/daily-liftings',
                    name: 'sml daily-liftings',
                    component: () =>
                        import ('./views/pages/petroleum/icoms/Declearations.vue')
                },
                // =============================================================================
                // COMPUTE MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/compute',
                    name: 'compute',
                    component: () =>
                        import ('./views/pages/petroleum/Compute.vue'),
                    meta: {
                        identity: 'Compute Tax Schedule', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Compute Tax Schedule', active: true },
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Compute'
                    },
                },
                // =============================================================================
                // USERS PROFILE START HERE
                // =============================================================================
                {
                    path: '/petroleum/accounts/profile',
                    name: 'Petroleum User Pofile',
                    component: () =>
                        import ('./views/pages/users/UserProfile.vue'),
                    meta: {
                        identity: "Account Profile", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'My Profile', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
            ]
        },
        // =============================================================================
        // REVENUE PAGE LAYOUTS
        // =============================================================================
        {
            // =============================================================================
            // MAIN LAYOUT ROUTES
            // =============================================================================
            path: '/revenue',
            component: () =>
                import ('./layouts/main/Revenue.vue'),
            children: [
                // =============================================================================
                // Theme Routes
                // =============================================================================
                {
                    path: '/revenue',
                    name: 'revenue-home',
                    component: () =>
                        import ('./views/pages/revenue/Home.vue'),
                    meta: {
                        identity: "Home", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', active: true },
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Ghana Audit Service'
                    }
                },
                // =============================================================================
                // RESPONSES MENU START HERE
                // =============================================================================
                {
                    path: '/revenue/responses',
                    name: 'revenue-audit-responses',
                    component: () =>
                        import ('./views/pages/revenue/responses/Accounts.vue'),
                    meta: {
                        identity: "Audit Responses", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', active: true },
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Responses'
                    }
                }, {
                    path: '/revenue/responses/:accountid',
                    name: 'audit-responses-transaction',
                    component: () =>
                        import ('./views/pages/revenue/responses/Transactions.vue'),
                    meta: {
                        identity: "Audit Responses", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/responses' },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Responses'
                    },
                    props: true
                }, {
                    path: '/revenue/responses/:accountid/:stm/:page',
                    name: 'audit-responses-transaction-view',
                    component: () =>
                        import ('./views/pages/revenue/responses/Statement.vue'),
                    meta: {
                        identity: "Audit Responses", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/responses' },
                            { title: 'Transactions', url: null },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Responses'
                    },
                    props: true
                },
                // =============================================================================
                // BANK ACCOUNTS MENU START HERE
                // =============================================================================

                {
                    path: '/revenue/accounts',
                    name: 'rbank-account',
                    component: () =>
                        import ('./views/pages/revenue/bank-account/Accounts.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    }
                }, {
                    path: '/revenue/accounts/:accountid/view',
                    name: 'rview-bank-account',
                    component: () =>
                        import ('./views/pages/revenue/bank-account/AccountView.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/accounts' },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                }, {
                    path: '/revenue/accounts/:accountid/view/transaction/:transactionid/:page',
                    name: 'rview-bank-account-trans',
                    component: () =>
                        import ('./views/pages/revenue/bank-account/AccountTransactionView.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/accounts' },
                            { title: 'View', active: true },
                            { title: 'Transaction', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                }, {
                    path: '/revenue/accounts/:accountid/reconcile',
                    name: 'rreconcile-bank-account',
                    component: () =>
                        import ('./views/pages/revenue/bank-account/AccountReconcile.vue'),
                    meta: {
                        identity: 'Bank Account',
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/accounts' },
                            { title: 'Reconcile', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // BOG UNAUTHORIZED START HERE
                // =============================================================================
                {
                    path: '/revenue/unauthorized/bog',
                    name: 'bog-nauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Accounts.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    }
                }, {
                    path: '/revenue/unauthorized/bog/:accountid',
                    name: 'bog-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Recipients.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/bog/:accountid/list/:offset_account',
                    name: 'bog-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/bog/:accountid/list',
                    name: 'bog-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/bog/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'bog-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/bog/:accountid/list//v/:page/:stmid',
                    name: 'bog-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                },
                // =============================================================================
                // ORG UNAUTHORIZED START HERE
                // =============================================================================
                {
                    path: '/revenue/unauthorized/org',
                    name: 'org-Unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Accounts.vue'),
                    meta: {
                        identity: "Unauthorized Bank of Ghana", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    }
                }, {
                    path: '/revenue/unauthorized/org/:accountid',
                    name: 'org-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Recipients.vue'),
                    meta: {
                        identity: "Unauthorized Other Banks", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/org/:accountid/list/:offset_account',
                    name: 'org-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Other Banks", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/org/:accountid/list',
                    name: 'org-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Other Banks", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/org/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'org-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Other Banks", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/org/:accountid/list//v/:page/:stmid',
                    name: 'org-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Other Banks", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                },
                // =============================================================================
                // HIDDEN UNAUTHORIZED START HERE
                // =============================================================================
                {
                    path: '/revenue/unauthorized/hidden',
                    name: 'hidden-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Accounts.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    }
                }, {
                    path: '/revenue/unauthorized/hidden/:accountid',
                    name: 'hidden-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Recipients.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/hidden/:accountid/list/:offset_account',
                    name: 'hidden-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/hidden/:accountid/list',
                    name: 'hidden-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Transactions.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/hidden/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'hidden-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                }, {
                    path: '/revenue/unauthorized/hidden/:accountid/list//v/:page/:stmid',
                    name: 'hidden-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Statement.vue'),
                    meta: {
                        identity: "Unauthorized Hidden Transactions", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                },
                // =============================================================================
                // USERS PROFILE START HERE
                // =============================================================================
                {
                    path: '/revenue/logs',
                    name: 'audits-logs',
                    component: () =>
                        import ('./views/pages/revenue/logs/Accounts.vue'),
                    meta: {
                        identity: "Audit Logs", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Logs', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Logs'
                    }
                }, {
                    path: '/revenue/logs/:accountid',
                    name: 'audits-logs-transactions',
                    component: () =>
                        import ('./views/pages/revenue/logs/Transactions.vue'),
                    meta: {
                        identity: "Audit Logs", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Logs', url: '/revenue/logs' },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Logs'
                    },
                    props: true
                },
                // =============================================================================
                // USERS PROFILE START HERE
                // =============================================================================
                {
                    path: '/revenue/accounts/profile',
                    name: 'Revenue User Pofile',
                    component: () =>
                        import ('./views/pages/users/UserProfile.vue'),
                    meta: {
                        identity: "Account Profile", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'My Profile', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Accounts'
                    }
                },
                // =============================================================================
                // OTHER REVENUE MENU START HERE
                // =============================================================================
                {
                    path: '/revenue/monitor',
                    name: 'revenue-monitor-jobs',
                    component: () =>
                        import ('./views/pages/revenue/system/Monitor.vue'),
                    meta: {
                        identity: "neutral", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Monitor', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                }, {
                    path: '/revenue/downloads',
                    name: 'revenue-file-download',
                    component: () =>
                        import ('./views/pages/revenue/system/Downloads.vue'),
                    meta: {
                        identity: "neutral", //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', },
                            { title: 'Downloads', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                },

            ]
        },
        // =============================================================================
        // FULL PAGE LAYOUTS
        // =============================================================================
        {
            path: '',
            component: () =>
                import ('@/layouts/full-page/FullPage.vue'),
            children: [
                // =============================================================================
                // PAGES
                // =============================================================================
                {
                    path: '/login',
                    name: 'page-login',
                    component: () =>
                        import ('@/views/pages/Login.vue')
                },
                {
                    path: '/error-404',
                    name: 'page-error-404',
                    component: () =>
                        import ('@/views/pages/Error404.vue')
                }
            ]
        },
        // Redirect to 404 page, if no match found
        {
            path: '*',
            redirect: '/error-404'
        }
    ]
})

export default router