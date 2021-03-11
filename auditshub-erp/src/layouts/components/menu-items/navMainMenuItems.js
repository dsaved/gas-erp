/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


export default [
	// {
	//     url: "/",
	//     name: "Home",
	//     slug: "home",
	//     page: "neutral",
	//     icon: "HomeIcon",
	// },
	{
		url: '/',
		name: 'Home',
		slug: 'home',
		page: 'Home',
		icon: 'HomeIcon'
	}, {
		url: '/revenue',
		name: 'Revenue',
		slug: 'revenue',
		page: 'Revenue',
		icon: 'DatabaseIcon'
	}, {
		url: null,
		name: 'Regimes',
		icon: 'GridIcon',
		page: 'Regimes',
		i18n: 'Regimes',
		submenu: [
			{
				url: '/petroleum',
				name: 'Petroleum',
				slug: 'petroleum',
				page: 'Petroleum'
			}
		]
	},
	{
		header: 'System',
		icon: 'PackageIcon',
		i18n: 'System',
		items: [
			{
				url: null,
				name: 'Accounts',
				icon: 'UsersIcon',
				page: 'Accounts',
				i18n: 'accounts',
				submenu: [
					{
						url: '/accounts/bdc',
						name: 'BDC Representative',
						slug: 'bdc-representatives',
						icon: 'MessageSquareIcon',
						page: 'BDC users',
						i18n: 'BDC'
					},
					{
						url: '/accounts/omc-org/',
						name: 'OMC Organization',
						slug: 'omc-organization',
						icon: 'PackageIcon',
						page: 'OMC Organization Manage',
						i18n: 'OMC Organization Manage'
					},
					{
						url: '/accounts/omc/',
						name: 'OMC Representative',
						slug: 'omc-representatives',
						icon: 'MessageSquareIcon',
						page: 'OMC users',
						i18n: 'OMC'
					},
					{
						url: '/accounts/users',
						name: 'Users',
						slug: 'system-users',
						icon: 'MessageSquareIcon',
						page: 'System Users',
						i18n: 'Users'
					},
					{
						url: '/accounts/roles',
						name: 'User roles',
						slug: 'system-users-role',
						icon: 'MessageSquareIcon',
						page: 'System User Role',
						i18n: 'Users Role'
					}
				]
			}
		]
	}, {
		url: '/banks',
		name: 'Banks',
		slug: 'banks',
		icon: 'ColumnsIcon',
		page: 'Banks',
		i18n: 'banks'
	}, {
		url: null,
		name: 'Bank Accounts',
		icon: 'LayoutIcon',
		page: 'Bank Accounts',
		i18n: 'bankaccounts',
		submenu: [
			{
				url: '/accounts/bank-account',
				name: 'Accounts',
				slug: 'bank-account',
				icon: 'CreditCardIcon',
				page: 'Bank Account',
				i18n: 'BDC'
			}, {
				url: '/accounts/category',
				name: 'Categories',
				slug: 'account-category',
				icon: 'GridIcon',
				page: 'Account Categories',
				i18n: 'BDC'
			}
		]
	}, {
		url: '/organizations',
		name: 'Organization',
		slug: 'organization',
		icon: 'PackageIcon',
		page: 'Organizations',
		i18n: 'Organization'
	}
]