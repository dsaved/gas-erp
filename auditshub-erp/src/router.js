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
                        identity: 'Home', //this should match then page in navMainMenuItems.js
                        breadcrumb: [{ title: 'Home', active: true }],
                        homeurl: '/',
                        pageTitle: 'Strategic Mobilisation Ghana Limited'
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
                        identity: 'BDC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'BDC Users', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/bdc/add',
                    name: 'Add BDC User',
                    component: () =>
                        import ('./views/pages/users/bdc/UserManage.vue'),
                    meta: {
                        identity: 'BDC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'BDC Users', url: '/accounts/bdc' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/bdc/:userid',
                    name: 'Update BDC User',
                    component: () =>
                        import ('./views/pages/users/bdc/UserManage.vue'),
                    meta: {
                        identity: 'BDC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'BDC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'BDC Users', url: '/accounts/bdc' },
                            { title: 'Details', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // DEPOT MANGE MENU START HERE
                // =============================================================================

                {
                    path: '/accounts/depot',
                    name: 'depot-list',
                    component: () =>
                        import ('./views/pages/depot/Depot.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Depots', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/depot/add',
                    name: 'depot-add',
                    component: () =>
                        import ('./views/pages/depot/DepotManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Depots', url: '/accounts/depot' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/depot/:depotid',
                    name: 'depot-edit',
                    component: () =>
                        import ('./views/pages/depot/DepotManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Depots', url: '/accounts/depot' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                {
                    path: '/accounts/depot/:depotid/view',
                    name: 'depot-view',
                    component: () =>
                        import ('./views/pages/depot/DepotView.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Depots', url: '/accounts/depot' },
                            { title: 'Veiw', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // BDC ORGANIZATION MANGE MENU START HERE
                // =============================================================================

                {
                    path: '/accounts/bdc-org',
                    name: 'bdc-org-list',
                    component: () =>
                        import ('./views/pages/bdc-organization/BDC.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'BDCs', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/bdc-org/add',
                    name: 'bdc-org-add',
                    component: () =>
                        import ('./views/pages/bdc-organization/BDCManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'BDCs', url: '/accounts/bdc-org' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/bdc-org/:bdcid',
                    name: 'bdc-org-edit',
                    component: () =>
                        import ('./views/pages/bdc-organization/BDCManage.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'BDCs', url: '/accounts/bdc-org' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
                },
                {
                    path: '/accounts/bdc-org/:bdcid/view',
                    name: 'bdc-org-view',
                    component: () =>
                        import ('./views/pages/bdc-organization/BDCView.vue'),
                    meta: {
                        identity: 'OMC Organization Manage', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'BDCs', url: '/accounts/bdc-org' },
                            { title: 'Veiw', active: true }
                        ],
                        homeurl: '/accounts',
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
                            { title: 'OMCs', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
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
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    }
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
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
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
                            { title: 'Veiw', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
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
                        identity: 'OMC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'OMC Users', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/omc/add',
                    name: 'Add OMC User',
                    component: () =>
                        import ('./views/pages/users/omc/UserManage.vue'),
                    meta: {
                        identity: 'OMC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'OMC Users', url: '/accounts/omc' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/omc/:userid',
                    name: 'Update OMC User',
                    component: () =>
                        import ('./views/pages/users/omc/UserManage.vue'),
                    meta: {
                        identity: 'OMC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'OMC users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'System Users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Users', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/users/add',
                    name: 'Add User',
                    component: () =>
                        import ('./views/pages/users/system/UserManage.vue'),
                    meta: {
                        identity: 'System Users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Users', url: '/accounts/users' },
                            { title: 'Add User', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/users/:userid',
                    name: 'Update User',
                    component: () =>
                        import ('./views/pages/users/system/UserManage.vue'),
                    meta: {
                        identity: 'System Users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'System Users', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Account Profile', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                            { title: 'Home' },
                            { title: 'User Role', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                },
                {
                    path: '/accounts/roles/add',
                    name: 'User Role Add',
                    component: () =>
                        import ('./views/pages/users/UserRoleManage.vue'),
                    meta: {
                        identity: 'System User Role', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                        breadcrumb: [{ title: 'Home', url: '/petroleum' }],
                        homeurl: '/petroleum',
                        pageTitle: 'Petroleum'
                    }
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
                            { title: 'Tax Type', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true
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
                            { title: 'Products', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true
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
                            { title: 'Tax Schedules', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true
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
                            { title: 'Exemptions', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    }
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
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true
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
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Tax'
                    },
                    props: true
                }, {
                    path: '/petroleum/monitor',
                    name: 'petroleum-monitor-jobs',
                    component: () =>
                        import ('./views/pages/revenue/system/Monitor.vue'),
                    meta: {
                        identity: 'neutral', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'neutral', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                            { title: 'OMCs', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    }
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
                            { title: 'Veiw', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true
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
                            { title: 'Fall Out Receipts', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    }
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
                            { title: 'View', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true
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
                            { title: 'Receipt', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true
                },
                // =============================================================================
                // OMC NATIONAL SUMMARY MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/omc/reconciled',
                    name: 'omc-reconciled-national-summary',
                    component: () =>
                        import ('./views/pages/petroleum/omc/national-summary/reconciled/Banks.vue'),
                    meta: {
                        identity: 'All Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'All Receipts', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'NATIONAL SUMMARY'
                    }
                },
                {
                    path: '/petroleum/omc/reconciled/:bankid/:date',
                    name: 'omc-reconciled-national-summary-view',
                    component: () =>
                        import ('./views/pages/petroleum/omc/national-summary/reconciled/BYOMC.vue'),
                    meta: {
                        identity: 'All Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'All Receipts', url: '/petroleum/omc/reconciled' },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'NATIONAL SUMMARY'
                    },
                    props: true
                },
                {
                    path: '/petroleum/omc/unreconciled',
                    name: 'omc-unreconciled-national-summary',
                    component: () =>
                        import ('./views/pages/petroleum/omc/national-summary/unreconciled/Banks.vue'),
                    meta: {
                        identity: 'Unreconciled Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Unreconciled Receipts', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'NATIONAL SUMMARY'
                    }
                },
                {
                    path: '/petroleum/omc/unreconciled/:bankid/:date',
                    name: 'omc-unreconciled-national-summary-view',
                    component: () =>
                        import ('./views/pages/petroleum/omc/national-summary/unreconciled/BYOMC.vue'),
                    meta: {
                        identity: 'Unreconciled Receipts',
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Unreconciled Receipts', url: '/petroleum/omc/unreconciled' },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'NATIONAL SUMMARY'
                    },
                    props: true
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
                            { title: 'Reconcilation Log', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC Logs'
                    }
                }, {
                    path: '/petroleum/omc/logs/:accountid',
                    name: 'omc-reconcile-logs-transactions',
                    component: () =>
                        import ('./views/pages/petroleum/omc/logs/Transactions.vue'),
                    meta: {
                        identity: 'Reconcilation Log',
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
                            { title: 'Compute', active: true }
                        ],
                        homeurl: '/accounts',
                        pageTitle: 'Accounts'
                    },
                    props: true
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
                            { title: 'Reconcile', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true
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
                            { title: 'Receipt', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'OMC'
                    },
                    props: true
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
                    path: '/petroleum/rcn/npa-icums',
                    name: 'npa-icums',
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
                    path: '/petroleum/npa/analytics',
                    name: 'npa-analytics',
                    component: () =>
                        import ('./views/pages/petroleum/npa/Analytics.vue'),
                    meta: {
                        identity: 'NPA Analytics', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Analytics', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Analytics'
                    }
                }, {
                    path: '/petroleum/npa/preorders',
                    name: 'npa-preorders',
                    component: () =>
                        import ('./views/pages/petroleum/npa/Preorders.vue'),
                    meta: {
                        identity: 'Preorders', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Preorders', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Preorders'
                    }
                },
                {
                    path: '/petroleum/npa/orders',
                    name: 'npa-orders',
                    component: () =>
                        import ('./views/pages/petroleum/npa/Orders.vue'),
                    meta: {
                        identity: 'Orders', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Orders', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Orders'
                    }
                },
                // =============================================================================
                // INPUT MODEL MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/input/analytics',
                    name: 'input-analytics',
                    component: () =>
                        import ('./views/pages/petroleum/input/Analytics.vue'),
                    meta: {
                        identity: 'Input Analytics', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Input Analytics', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Input Analytics'
                    }
                }, {
                    path: '/petroleum/input/manifest',
                    name: 'input-manifest',
                    component: () =>
                        import ('./views/pages/petroleum/input/Manifest.vue'),
                    meta: {
                        identity: 'Manifest', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Manifest', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Manifest'
                    }
                }, {
                    path: '/petroleum/input/declaration',
                    name: 'input-declaration',
                    component: () =>
                        import ('./views/pages/petroleum/input/Declearations.vue'),
                    meta: {
                        identity: 'Declaration', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Declaration', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Declaration'
                    }
                }, {
                    path: '/petroleum/input/reconciliation',
                    name: 'input-declearations',
                    component: () =>
                        import ('./views/pages/petroleum/input/Reconciliation.vue'),
                    meta: {
                        identity: 'Reconciliation', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Reconciliation', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Reconciliation'
                    }
                },
                // =============================================================================
                // ICOMS-CEPS MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/icums/declearations',
                    name: 'declearations',
                    component: () =>
                        import ('./views/pages/petroleum/icums/Declearations.vue')
                },
                {
                    path: '/petroleum/icums/payments',
                    name: 'payments',
                    component: () =>
                        import ('./views/pages/petroleum/icums/Payments.vue')
                },
                {
                    path: '/petroleum/icums/differences',
                    name: 'differences',
                    component: () =>
                        import ('./views/pages/petroleum/icums/Differences.vue')
                },
                // =============================================================================
                // SML MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/sml/inlet',
                    name: 'sml-inlet',
                    component: () =>
                        import ('./views/pages/petroleum/sml/Inlet.vue'),
                    meta: {
                        identity: 'Inlet', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Inlet', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
                }, {
                    path: '/petroleum/sml/inlet-report',
                    name: 'sml-inlet-report',
                    component: () =>
                        import ('./views/pages/petroleum/sml/InletReport.vue'),
                    meta: {
                        identity: 'Inlet Report', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Inlet Report', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
                }, {
                    path: '/petroleum/sml/outlet',
                    name: 'sml-outlet',
                    component: () =>
                        import ('./views/pages/petroleum/sml/Outlet.vue'),
                    meta: {
                        identity: 'Outlet', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Outlet', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
                }, {
                    path: '/petroleum/sml/outlet-report',
                    name: 'sml-outlet-report',
                    component: () =>
                        import ('./views/pages/petroleum/sml/OutletReport.vue'),
                    meta: {
                        identity: 'Outlet Report', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Outlet Report', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
                }, {
                    path: '/petroleum/sml/undeclared-product',
                    name: 'sml-undeclared-product',
                    component: () =>
                        import ('./views/pages/petroleum/sml/UndeclaredProducts.vue'),
                    meta: {
                        identity: 'Undeclared Products', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Undeclared Products', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
                }, {
                    path: '/petroleum/sml/missing-product',
                    name: 'sml-missing-product',
                    component: () =>
                        import ('./views/pages/petroleum/sml/MissingProducts.vue'),
                    meta: {
                        identity: 'Missing Products', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Missing Products', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'SML'
                    }
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
                            { title: 'Compute Tax Schedule', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Compute'
                    }
                },
                // =============================================================================
                // STOCK MANAGEMENT MENU START HERE
                // =============================================================================
                {
                    path: '/petroleum/stock-management',
                    name: 'stock-management',
                    component: () =>
                        import ('./views/pages/petroleum/StockManagement.vue'),
                    meta: {
                        identity: 'Stock Management', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Stock Management', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Compute'
                    }
                },
                {
                    path: '/petroleum/stock-management/:depot',
                    name: 'stock-management-depot',
                    component: () =>
                        import ('./views/pages/petroleum/StockManagementDepot.vue'),
                    meta: {
                        identity: 'Stock Management', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Stock Management', url: '/petroleum/stock-management' },
                            { title: 'Products', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Compute'
                    },
                    props: true
                },
                {
                    path: '/petroleum/stock-management/invalid/:depot/:product',
                    name: 'stock-management-depot',
                    component: () =>
                        import ('./views/pages/petroleum/StockManagementInvalid.vue'),
                    meta: {
                        identity: 'Stock Management', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home', url: '/petroleum' },
                            { title: 'Stock Management', url: '/petroleum/stock-management' },
                            { title: 'Invalid Discharge', active: true }
                        ],
                        homeurl: '/petroleum',
                        pageTitle: 'Compute'
                    },
                    props: true
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
                        identity: 'Account Profile', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'My Profile', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Accounts'
                    }
                }
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
                        identity: 'Home', //this should match then page in navMainMenuItems.js
                        breadcrumb: [{ title: 'Home', active: true }],
                        homeurl: '/revenue',
                        pageTitle: 'Strategic Mobilisation Ghana Limited'
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
                        identity: 'Audit Responses', //this should match then page in navMainMenuItems.js
                        breadcrumb: [{ title: 'Home', active: true }],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Responses'
                    }
                }, {
                    path: '/revenue/responses/:accountid',
                    name: 'audit-responses-transaction',
                    component: () =>
                        import ('./views/pages/revenue/responses/Transactions.vue'),
                    meta: {
                        identity: 'Audit Responses', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Audit Responses', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
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
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/accounts' },
                            { title: 'Reconcile', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Bank Accounts'
                    },
                    props: true
                },
                // =============================================================================
                // SURCHARGE START HERE
                // =============================================================================
                {
                    path: '/revenue/rates',
                    name: 'Rates',
                    component: () =>
                        import ('./views/pages/revenue/surcharge/Rates.vue'),
                    meta: {
                        identity: 'Rates',
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Rates', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Surcharge'
                    }
                },
                {
                    path: '/revenue/rates/add',
                    name: 'add-tax-schedule',
                    component: () =>
                        import ('./views/pages/revenue/surcharge/RatesManage.vue'),
                    meta: {
                        identity: 'Rates', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Surcharge Rate', url: '/revenue/rates' },
                            { title: 'Add', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Surcharge'
                    }
                },
                {
                    path: '/revenue/rates/:rateid',
                    name: 'edit-tax-schedule',
                    component: () =>
                        import ('./views/pages/revenue/surcharge/RatesManage.vue'),
                    meta: {
                        identity: 'Rates', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Surcharge Rate', url: '/revenue/rates' },
                            { title: 'Edit', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Surcharge'
                    },
                    props: true
                },
                // ******************************************************************************************
                {
                    path: '/revenue/penalty',
                    name: 'Penalty',
                    component: () =>
                        import ('./views/pages/revenue/surcharge/Penalties.vue'),
                    meta: {
                        identity: 'Penalty',
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Penalty', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Surcharge'
                    }
                }, {
                    path: '/revenue/penalty/:accountid/view',
                    name: 'Penalty-view',
                    component: () =>
                        import ('./views/pages/revenue/surcharge/PenaltyView.vue'),
                    meta: {
                        identity: 'Penalty',
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Penalty', url: '/revenue/penalty' },
                            { title: 'View', active: true }
                        ],
                        homeurl: '/',
                        pageTitle: 'Surcharge'
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
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    }
                },
                {
                    path: '/revenue/unauthorized/bog/:accountid',
                    name: 'bog-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Recipients.vue'),
                    meta: {
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/bog/:accountid/list/:offset_account',
                    name: 'bog-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/bog/:accountid/list',
                    name: 'bog-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/bog' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'BOG Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/bog/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'bog-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                {
                    path: '/revenue/unauthorized/bog/:accountid/list//v/:page/:stmid',
                    name: 'bog-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/bog/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Unauthorized Bank of Ghana', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    }
                },
                {
                    path: '/revenue/unauthorized/org/:accountid',
                    name: 'org-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Recipients.vue'),
                    meta: {
                        identity: 'Unauthorized Other Banks', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/org/:accountid/list/:offset_account',
                    name: 'org-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Other Banks', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/org/:accountid/list',
                    name: 'org-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Other Banks', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/org' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Org Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/org/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'org-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Other Banks', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                {
                    path: '/revenue/unauthorized/org/:accountid/list//v/:page/:stmid',
                    name: 'org-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/org/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Other Banks', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    }
                },
                {
                    path: '/revenue/unauthorized/hidden/:accountid',
                    name: 'hidden-account-unauthorized',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Recipients.vue'),
                    meta: {
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/hidden/:accountid/list/:offset_account',
                    name: 'hidden-account-unauthorized-offset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/hidden/:accountid/list',
                    name: 'hidden-account-unauthorized-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Transactions.vue'),
                    meta: {
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Accounts', url: '/revenue/unauthorized/hidden' },
                            { title: 'Recieving Account', active: false },
                            { title: 'Transactions', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Hidden Unauthorized'
                    },
                    props: true
                },
                {
                    path: '/revenue/unauthorized/hidden/:accountid/list/:offset_account/v/:page/:stmid',
                    name: 'hidden-account-unauthorized-view-statement',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                {
                    path: '/revenue/unauthorized/hidden/:accountid/list//v/:page/:stmid',
                    name: 'hidden-account-unauthorized-view-statemant-nooffset',
                    component: () =>
                        import ('./views/pages/revenue/unauthorized/hidden/Statement.vue'),
                    meta: {
                        identity: 'Unauthorized Hidden Transactions', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Audit Logs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Logs', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'Audit Logs'
                    }
                },
                {
                    path: '/revenue/logs/:accountid',
                    name: 'audits-logs-transactions',
                    component: () =>
                        import ('./views/pages/revenue/logs/Transactions.vue'),
                    meta: {
                        identity: 'Audit Logs', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'Account Profile', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
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
                        identity: 'neutral', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Monitor', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                },
                {
                    path: '/revenue/downloads',
                    name: 'revenue-file-download',
                    component: () =>
                        import ('./views/pages/revenue/system/Downloads.vue'),
                    meta: {
                        identity: 'neutral', //this should match then page in navMainMenuItems.js
                        breadcrumb: [
                            { title: 'Home' },
                            { title: 'Downloads', active: true }
                        ],
                        homeurl: '/revenue',
                        pageTitle: 'System'
                    }
                }

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