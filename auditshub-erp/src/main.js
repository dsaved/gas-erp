/*=========================================================================================
  File Name: main.js
  Description: main vue(js) file
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


import Vue from 'vue'
import App from './App.vue'

// Vuesax Component Framework
import Vuesax from 'vuesax'
import 'material-icons/iconfont/material-icons.css' //Material Icons
import 'vuesax/dist/vuesax.css' // Vuesax
Vue.use(Vuesax)


// axios
import axios from 'axios'

// Filters
import './filters/filters.js'


// Theme Configurations
import '../themeConfig.js'


// Globally Registered Components
import './globalComponents.js'


// Styles: SCSS
import './assets/scss/main.scss'


// Tailwind
import '@/assets/css/main.css'


// Vue Router
import router from './router'


// Vuex Store
import store from './store/store'

// Import Storage
import mStorage from './store/storage'

// Import Swal
import Swal from 'sweetalert2'

Vue.mixin({
            data() {
                return {
                    development: true,
                    site_link: 'https://omc-api.auditshub.com',
                    storage_key: 'gas-storage',
                    // site_link: 'http://localhost/gas/omc-api',
                    // storage_key: 'gas-storage-local',
                    axiosCancelSource: null,
                    allowed_pages: ['neutral']
                }
            },
            mounted() {
                // Clear the browser cache data in localStorage when closing the browser window
                // window.onbeforeunload = function(e) {
                //     var storage = window.localStorage;
                //     storage.clear()
                // }
                this.$nextTick(function() {})
            },
            beforeMount() {
                this.axiosCancelSource = axios.CancelToken.source()
                axios.defaults.headers.common['Authorization'] = `Bearer ${mStorage.get(`${this.storage_key  }gas_authorization`)}`
	},
	computed: {
		AppActiveUser () {
			return this.getUser()
		}
	},
	methods: {
		linkto (link) { router.push(link) },
		pushReplacement (link) { router.replace(link) },
		back () { router.go(-1) },
		capitalize (s) {
			if (typeof s !== 'string') return ''
			return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase()
		},
		checkLogin (alert) {
			let show = true
			if (alert && alert === true) {
				show = false
			}
			if (this.getUser().id === null) {
				const currentRouteName = this.$router.currentRoute.name
				if (currentRouteName !== 'page-login') {
					if (show) {
						this.$vs.notify({
							title: 'Cannot access that page!!!',
							text: 'Please login to gain access to that page',
							sticky: true,
							iconPack: 'feather',
							icon: 'icon-lock',
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
					}
					router.replace('/login')
				}
			}
		},
		filterObj (objectsArray, value, casee) {
			if (casee !== true) {
				casee = false
			}
			if (typeof objectsArray !== 'object') throw new Error('Not a valid ObjectArray')

			let filteredArray = [],
				added = [],
				search = '',
				keys = Object.keys(objectsArray[0])
			for (const i in objectsArray) {
				for (const k in keys) {
					const key = keys[k]
					if (objectsArray[i][key] === parseInt(objectsArray[i][key], 10)) {
						objectsArray[i][key] = objectsArray[i][key].toString()
					}
					if (typeof objectsArray[i][key] === 'string' && !casee) {
						search = objectsArray[i][key].toLowerCase()
						value = value.toLowerCase()
					} else {
						search = objectsArray[i][key]
					}

					if (typeof search === 'boolean') continue

					//console.log(search," key: "+key);
					if (typeof objectsArray[i][key] !== 'object' && typeof objectsArray[i][key] !== 'array' && search.indexOf(value) !== -1) {
						if (!added.includes(objectsArray[i])) {
							filteredArray.push(objectsArray[i])
							added.push(objectsArray[i])
						}
					}
				}
			}
			return filteredArray
		},
		hasdata (data) {
			return null !== data && data.length > 0
		},
		hasLogedIn () {
			if (this.getUser().id !== null) {
				const currentRouteName = router.currentRoute.name
				if (currentRouteName === 'Signin') {
					//todo redirect to approprate page
					router.replace('/')
				}
			}
		},
		canDelete () {
			return this.AppActiveUser.permissions.includes('delete')
		},
		canAdd () {
			return this.AppActiveUser.permissions.includes('create')
		},
		canUpdate () {
			return this.AppActiveUser.permissions.includes('update')
		},
		logUserOut () {
			this.removeUser()
			this.$vs.notify({
				title: 'Success',
				text: 'You have logged out of your account',
				sticky: true,
				iconPack: 'feather',
				icon: 'icon-thumbs-up',
				color: 'success',
				duration: null,
				position: 'bottom-left'
			})
			this.checkLogin(true)
		},
		key () {
			const key = `_${  Math.random().toString(36).substr(2, 9)}`
			return key
		},
		checkAuth (code) {
			if (code === 401) {
				this.axiosCancelSource.cancel('401 Unauthorize access')
				this.removeUser()
				this.$vs.notify({
					title: 'Unauthorized!!!',
					text: 'Your session has been terminated',
					sticky: true,
					iconPack: 'feather',
					icon: 'icon-log-out',
					color: 'danger',
					duration: null,
					position: 'bottom-left'
				})
				this.checkLogin(true)
                
			}
		},
		post (link, data) {
			const user = this.getUser()
			data.request_madeby_name = user.name
			data.request_madeby_id = user.id
			data.request_madeby_access_level = user.access_level
			return new Promise((resolve, reject) => {
				this.gettoken().then((token) => {
					axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
					axios.post(this.site_link + link, data, { cancelToken: this.axiosCancelSource.token }).then(result => {
						resolve(result)
					}).catch(error => {
						if (error.response) {
							// The request was made and the server responded with a status code
							reject(error.response.data)
							this.checkAuth(error.response.status)
						} else if (error.request) {
							// The request was made but no response was received
							reject({ success: false, message: error.request })
						} else {
							// Something happened in setting up the request that triggered an Error
							reject({ success: false, message: error })
						}
					})
				}).catch(error => {
					reject(error)
				})
			})
		},
		post_no_secure (link, data) {
			return new Promise((resolve, reject) => {
				axios.post(this.site_link + link, data, { cancelToken: this.axiosCancelSource.token }).then(result => {
					resolve(result)
				}).catch(error => {
					if (error.response) {
						// The request was made and the server responded with a status code
						reject(error.response.data)
						this.checkAuth(error.response.status)
					} else if (error.request) {
						// The request was made but no response was received
						reject({ success: false, message: error.request })
					} else {
						// Something happened in setting up the request that triggered an Error
						reject({ success: false, message: error })
					}
				})
			})
		},
		postFile (link, data, config) {
			const user = this.getUser()
			data.request_madeby_name = user.name
			data.request_madeby_id = user.id
			data.request_madeby_access_level = user.access_level
			return new Promise((resolve, reject) => {
				this.gettoken().then((token) => {
					axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
					axios.defaults.headers.common['Content-Type'] = 'multipart/form-data'
					axios.post(this.site_link + link, data, config).then(result => {
						resolve(result)
					}).catch(error => {
						if (error.response) {
							// The request was made and the server responded with a status code
							reject(error.response.data)
							this.checkAuth(error.response.status)
						} else if (error.request) {
							// The request was made but no response was received
							reject({ success: false, message: error.request })
						} else {
							// Something happened in setting up the request that triggered an Error
							reject({ success: false, message: error })
						}
					})
				}).catch(error => {
					reject(error)
				})
			})
		},
		get (link) {
			return new Promise((resolve, reject) => {
				this.gettoken().then((token) => {
					axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
					axios.get(this.site_link + link, { cancelToken: this.axiosCancelSource.token }).then(result => {
						resolve(result)
					}).catch(error => {
						if (error.response) {
							// The request was made and the server responded with a status code
							reject(error.response.data)
							this.checkAuth(error.response.status)
						} else if (error.request) {
							// The request was made but no response was received
							reject({ success: false, message: error.request })
						} else {
							// Something happened in setting up the request that triggered an Error
							reject({ success: false, message: error })
						}
					})
				}).catch(error => {
					reject(error)
				})
			})
		},
		gettoken () {
			return new Promise((resolve, reject) => {
				const token = mStorage.get(`${this.storage_key  }gas_authorization`)
				if (!token) {
					reject({ success: false, message: 'no token' })
				} else {
					resolve(token)
				}
			})
		},
		mutate (data, options) {
			const mutated = []
			data.forEach((element, index) => {
				mutated[index] = element
				options.forEach(data => {
					mutated[index][data.name] = data.value
				})
			})
			return mutated
		},
		parseJson (data) {
			return JSON.parse(data)
		},
		stringJson (data) {
			return JSON.stringify(data)
		},
		storeData (key, value) {
			mStorage.set(`${this.storage_key}${key}`, value)
		},
		setUser (user) {
			mStorage.set(`${this.storage_key}gas_userid`, user.id)
			mStorage.set(`${this.storage_key}gas_name`, user.name)
			mStorage.set(`${this.storage_key}gas_username`, user.username)
			mStorage.set(`${this.storage_key}gas_email`, user.email)
			mStorage.set(`${this.storage_key}gas_userphoto`, user.photo)
			mStorage.set(`${this.storage_key}gas_access_level`, user.access_level)
			mStorage.set(`${this.storage_key}gas_baseUrl`, user.baseUrl)
			mStorage.set(`${this.storage_key}gas_access_level_types`, JSON.stringify(user.types))
			mStorage.set(`${this.storage_key}gas_permissions`, JSON.stringify(user.permissions))
			mStorage.set(`${this.storage_key}gas_pages`, JSON.stringify(user.pages))
		},
		getUser () {
			return {
				id: mStorage.get(`${this.storage_key}gas_userid`),
				name: mStorage.get(`${this.storage_key}gas_name`),
				username: mStorage.get(`${this.storage_key}gas_username`),
				photo: mStorage.get(`${this.storage_key}gas_userphoto`) || require('@/assets/images/portrait/small/default.png'),
				email: mStorage.get(`${this.storage_key}gas_email`),
				baseUrl: mStorage.get(`${this.storage_key}gas_baseUrl`),
				access_level: mStorage.get(`${this.storage_key}gas_access_level`),
				types: JSON.parse(mStorage.get(`${this.storage_key}gas_access_level_types`)) || [],
				permissions: JSON.parse(mStorage.get(`${this.storage_key  }gas_permissions`)) || [],
				pages: JSON.parse(mStorage.get(`${this.storage_key}gas_pages`)) || []
			}
		},
		removeUser () {
			mStorage.remove(`${this.storage_key}gas_userid`)
			mStorage.remove(`${this.storage_key}gas_name`)
			mStorage.remove(`${this.storage_key}gas_username`)
			mStorage.remove(`${this.storage_key}gas_userphoto`)
			mStorage.remove(`${this.storage_key}gas_baseUrl`)
			mStorage.remove(`${this.storage_key}gas_email`)
			mStorage.remove(`${this.storage_key}gas_access_level`)
			mStorage.remove(`${this.storage_key}gas_access_level_types`)
			mStorage.remove(`${this.storage_key}gas_permissions`)
			mStorage.remove(`${this.storage_key}gas_pages`)
		},
		getRandomInt (min, max) {
			return Math.floor(Math.random() * (max - min)) + min
		},
		getOrderStatusColor (status) {
			if (status === 'create') return 'success'
			if (status === 'delete') return 'danger'
			if (status === 'update') return 'warning'
			return 'primary'
		},
		getclasscolor () {
			const arr = ['success', 'dark', 'warning', 'primary', 'danger']
			return arr[this.getRandomInt(0, arr.length - 1)]
		},
		acronum (str) {
			if (!str) return
			const matches = str.match(/\b(\w)/g)
			return matches.join('')
		},
		showLoading (data) {
			Swal.fire({
				title: 'Please Wait !',
				html: `${data}`,
				allowOutsideClick: false,
				willOpen: () => {
					Swal.showLoading()
				}
			})
		},
		closeLoading () {
			Swal.close()
		}
	}
})

// Vuejs - Vue wrapper for hammerjs
import { VueHammer } from 'vue2-hammer'
Vue.use(VueHammer)

// PrismJS
import 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'

// VeeValidate
import VeeValidate from 'vee-validate'
Vue.use(VeeValidate)

// Feather font icon
require('./assets/css/iconfont.css')

// Vue select css
// Note: In latest version you have to add it separately
// import 'vue-select/dist/vue-select.css';

Vue.config.productionTip = false

new Vue({
	router,
	store,
	render: h => h(App)
}).$mount('#app')