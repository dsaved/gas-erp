/*=========================================================================================
  File Name: state.js
  Description: Vuex Store - state
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

import navbarSearchAndPinList from '@/layouts/components/navbar/navbarSearchAndPinList'
import themeConfig from '@/../themeConfig.js'

// /////////////////////////////////////////////
// Helper
// /////////////////////////////////////////////

// *From Auth - Data will be received from auth provider
const userDefaults = {
    id: 0, // From Auth
    name: '',
    photo: require('@/assets/images/portrait/small/default.png'), // From Auth
    email: '',
    access_level: '',
    types: [],
    permissions: [],
    pages: []
}


// Check if device is touch device
// This is used to remove perfect scrollbar from touch devices
// Using Dynamic components
const is_touch_device = () => {
    const prefixes = ' -webkit- -moz- -o- -ms- '.split(' ')
    const mq = function(query) {
        return window.matchMedia(query).matches
    }

    if (('ontouchstart' in window) || window.DocumentTouch) {
        return true
    }

    // include the 'heartz' as a way to have a non matching MQ to help terminate the join
    // https://git.io/vznFH
    const query = ['(', prefixes.join('touch-enabled),('), 'heartz', ')'].join('')
    return mq(query)
}


// /////////////////////////////////////////////
// State
// /////////////////////////////////////////////

const state = {
    AppActiveUser: userDefaults,
    bodyOverlay: false,
    isVerticalNavMenuActive: true,
    is_touch_device: is_touch_device(),
    mainLayoutType: themeConfig.mainLayoutType || 'vertical',
    navbarSearchAndPinList,
    reduceButton: themeConfig.sidebarCollapsed,
    verticalNavMenuWidth: 'default',
    verticalNavMenuItemsMin: false,
    scrollY: 0,
    starredPages: navbarSearchAndPinList['pages'].data.filter((page) => page.is_bookmarked),
    theme: themeConfig.theme || 'light',
    themePrimaryColor: themeConfig.primary,

    // Can be used to get current window with
    // Note: Above breakpoint state is for internal use of sidebar & navbar component
    windowWidth: null
}

export default state