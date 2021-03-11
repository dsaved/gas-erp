/*=========================================================================================
  File Name: globalComponents.js
  Description: Here you can register components globally
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


import Vue from 'vue'
import VxTooltip from './layouts/components/vx-tooltip/VxTooltip.vue'
import VxCard from './components/vx-card/VxCard.vue'
import VxList from './components/vx-list/VxList.vue'
import FileUpload from './components/FileUpload.vue'
import VxBreadcrumb from './layouts/components/VxBreadcrumb.vue'
import AjaxVSelect from './components/AjaxVSelect.vue'
import FeatherIcon from './components/FeatherIcon.vue'
import VxInputGroup from './components/vx-input-group/VxInputGroup.vue'
import vSelect from 'vue-select'

Vue.component(VxTooltip.name, VxTooltip)
Vue.component(VxCard.name, VxCard)
Vue.component(VxList.name, VxList)
Vue.component(VxBreadcrumb.name, VxBreadcrumb)
Vue.component(FileUpload.name, FileUpload)
Vue.component(FeatherIcon.name, FeatherIcon)
Vue.component(VxInputGroup.name, VxInputGroup)
Vue.component(AjaxVSelect.name, AjaxVSelect)

// Set the components prop default to return our fresh components
vSelect.props.components.default = () => ({
	Deselect: {
		render: createElement => createElement('feather-icon', {
			props: {
				icon: 'XIcon',
				svgClasses: 'w-4 h-4 mt-1'
			}
		})
	},
	OpenIndicator: {
		render: createElement => createElement('feather-icon', {
			props: {
				icon: 'ChevronDownIcon',
				svgClasses: 'w-5 h-5'
			}
		})
	}
})

Vue.component('v-select', vSelect)