<template>
  <vx-card title="Create new users">
    <p>
      Create user to have access to the system, in order to manage the
      application. Note: each user you create can belong to only one role.
    </p>
    <div class="mt-5">
      <form autocomplete="off">
        <div class="vx-row">
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required|alpha_dash'"
              name="__920939usn"
              autocomplete="off"
              class="w-full"
              label-placeholder="Username"
              v-model="user.username"
            />
            <span
              class="text-danger text-sm"
              v-show="errors.has('__920939usn')"
              >{{ errors.first("__920939usn") }}</span
            >
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required'"
              name="Full Name"
              class="w-full"
              label-placeholder="Full Name"
              v-model="user.fullname"
            />
            <span
              class="text-danger text-sm"
              v-show="errors.has('Full Name')"
              >{{ errors.first("Full Name") }}</span
            >
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required|digits:10'"
              name="Phone Number"
              class="w-full"
              label-placeholder="Phone Number"
              v-model="user.phone"
            />
            <span
              class="text-danger text-sm"
              v-show="errors.has('Phone Number')"
              >{{ errors.first("Phone Number") }}</span
            >
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required|email'"
              name="__920939eml"
              class="w-full"
              label-placeholder="Email"
              v-model="user.email"
            />
            <span
              class="text-danger text-sm"
              v-show="errors.has('__920939eml')"
              >{{ errors.first("__920939eml") }}</span
            >
          </div>

          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              type="password"
              v-validate="'required|min:5|max:10'"
              ref="password"
              label-placeholder="Your Password"
              name="password"
              v-model="user.password"
              class="mt-5 w-full"
            />
            <span class="text-danger text-sm" v-show="errors.has('password')">{{
              errors.first("password")
            }}</span>
          </div>

          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              type="password"
              v-validate="'required|min:5|max:10|confirmed:password'"
              data-vv-as="password"
              label-placeholder="Confirm Password"
              name="confirm_password"
              v-model="user.confirm_password"
              class="mt-5 w-full"
            />
            <span
              class="text-danger text-sm"
              v-show="errors.has('confirm_password')"
              >{{ errors.first("confirm_password") }}</span
            >
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <div class="vs-con-input hasValue" style="position: relative">
              <span
                class="input-span-placeholder vs-input--placeholder"
                style="top: -10px"
              >
                User Type
              </span>
            </div>
            <v-select
              :clearable="false"
              placeholder="Select user type"
              :options="usertypes"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              v-model="user.user_type"
              class="mt-5 w-full"
            />
            <span class="text-danger text-sm" v-show="usertype_error">{{
              usertype_error
            }}</span>
          </div>
          <div
            v-if="
              user.user_type &&
              user.user_type.value &&
              user.user_type.value === 'bogorg'
            "
            class="vx-col sm:w-1/4 w-full mb-2"
          >
            <div class="vs-con-input hasValue" style="position: relative">
              <span
                class="input-span-placeholder vs-input--placeholder"
                style="top: -10px"
              >
                Organization
              </span>
            </div>
            <v-select
              :clearable="false"
              placeholder="Select Organization"
              :options="organizations"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              v-model="user.organization"
              class="mt-5 w-full"
            />
            <span class="text-danger text-sm" v-show="organization_error">{{
              organization_error
            }}</span>
          </div>
          <div
            v-if="
              user.user_type &&
              user.user_type.value &&
              user.user_type.value === 'system'
            "
            class="vx-col sm:w-1/4 w-full mb-2"
          >
            <div class="vs-con-input hasValue" style="position: relative">
              <span
                class="input-span-placeholder vs-input--placeholder"
                style="top: -10px"
              >
                User role
              </span>
            </div>
            <v-select
              :clearable="false"
              placeholder="Select Role"
              :options="roles"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              v-model="user.role"
              class="mt-5 w-full"
            />
            <span class="text-danger text-sm" v-show="role_error">{{
              role_error
            }}</span>
          </div>
          <div
            v-if="
              user.user_type &&
              user.user_type.value &&
              user.user_type.value === 'system'
            "
            class="vx-col sm:w-1/4 w-full mb-2"
          >
            <div class="vs-con-input hasValue" style="position: relative">
              <span
                class="input-span-placeholder vs-input--placeholder"
                style="top: -10px"
              >
                Base URL
              </span>
            </div>
            <v-select
              :clearable="false"
              placeholder="Select Base Url"
              :options="baseUrls"
              :dir="$vs.rtl ? 'rtl' : 'ltr'"
              v-model="user.baseurl"
              class="mt-5 w-full"
            />
            <span class="text-danger text-sm" v-show="baseUrl_error">{{
              baseUrl_error
            }}</span>
          </div>
        </div>
        <div class="vx-row">
          <vs-divider> <h4 class="my-3">Upload User Photo</h4> </vs-divider>
          <div v-if="user.photo" class="con-img mb-2 mt-3">
            <img
              key="onlineImg"
              :src="user.photo"
              alt="user-img"
              width="100"
              height="100"
              class="rounded-full shadow-md cursor-pointer block"
            />
          </div>
          <div
            class="vx-col sm:w-1/3 w-full mb-2 mt-3 flex flex-wrap items-center"
          >
            <ds-file-upload
              upload-button-lable="Upload Image"
              type="relief"
              color="primary"
              max-size="500"
              upload-url="/users/upload/"
              allowed-file-type="image/png, image/jpeg, image/gif"
              :remove-button="showRemoveImage"
              v-on:file-link="setImage"
              v-on:remove-file="deleteMedia"
              :fileId="userid"
              :oldfile="user.photo"
            />
          </div>
        </div>

        <div v-if="showpageSelect">
          <vs-divider>
            <h4 class="my-3">Asign User Page Access</h4>
          </vs-divider>
          <vs-input
            class="inputx tree-search-input float-left mr-2"
            placeholder="Search page..."
            v-on:keyup.enter="search"
            v-model.lazy="searchword"
          />
          <vs-button color="primary" type="filled" @click="search" class="mb-3"
            >Search</vs-button
          >
          <v-tree
            ref="tree"
            :data="pageObjecMenus"
            :halfcheck="true"
            :multiple="true"
            :allowGetParentNode="true"
            @node-select="nodeSelected"
            @node-check="nodeSelected"
          />
        </div>
        <vs-button
          type="filled"
          @click.prevent="validateAndSubmit"
          class="mt-5 block"
          >Submit</vs-button
        >
      </form>
    </div>
  </vx-card>
</template>


<script>
// Import Swal
import Swal from 'sweetalert2'
import { VTree, VSelectTree } from 'vue-tree-halower'

import mainItemsMenu from '@/layouts/components/menu-items/navMainMenuItems.js'
import petroleumeMenu from '@/layouts/components/menu-items/navPetroleumeMenuItems.js'
import revenueMenuItems from '@/layouts/components/menu-items/navRevenueMenuItems.js'

// For custom error message
import { Validator } from 'vee-validate'
const dict = {
	custom: {
		__920939usn: {
			required: 'Please enter Username',
			alpha_dash:
        'The Username field may contain alpha-numeric characters as well as dashes and underscores'
		},
		__920939eml: {
			required: 'The Email field is required',
			email: 'The Email field must be a valid email'
		}
	}
}

// register custom messages
Validator.localize('en', dict)

export default {
	beforeRouteEnter (to, from, next) {
		next((vm) => {
			if (
				to.meta &&
        to.meta.identity &&
        !vm.AppActiveUser.pages.includes(to.meta.identity)
			) {
				vm.pushReplacement(vm.AppActiveUser.baseUrl)
			}
		})
	},
	props: {
		userid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			searchword: '',
			pageObjecMenus: [],
			user: {
				selectedPages: [],
				username: '',
				fullname: '',
				email: '',
				phone: '',
				password: '',
				photo: '',
				baseurl: null,
				confirm_password: '',
				organization: null,
				role: null,
				access_level: 'admin',
				user_type: null
			},
			baseUrl_error: '',
			baseUrls: [
				{ label: 'Home', value: '/' },
				{ label: 'Petroleum', value: '/petroleum' },
				{ label: 'Revenue', value: '/revenue' }
			],
			organization_error: '',
			organizations: [],
			role_error: '',
			roles: [],
			usertype_error: '',
			usertypes: [
				// { label: "BDC Representative", value: "bdc" },
				// { label: "OMC Representative", value: "omc" },
				{ label: 'System', value: 'system' },
				{ label: 'Revenue (bog/org)', value: 'bogorg' }
			],
			showPageFor: ['system'],
			showpageSelect: false,
			pageArray: []
		}
	},
	components: {
		VTree,
		VSelectTree
	},
	beforeMount () {
		this.loadOptions()
	},
	mounted () {
		if (this.isEdit()) {
			this.getData()
		}
	},
	computed: {
		showRemoveImage () {
			return this.hasdata(this.user.photo)
		},
		baseurl () {
			return this.user.baseurl
		},
		organization () {
			return this.user.organization
		},
		role () {
			return this.user.role
		},
		user_type () {
			return this.user.user_type
		}
	},
	watch: {
		user_type () {
			this.usertype_error = ''
			if (
				this.user.user_type &&
        this.user.user_type.value &&
        !this.showPageFor.includes(this.user.user_type.value)
			) {
				this.user.baseurl = null
				this.user.selectedPages = []
				this.user.role = null
			}

			if (typeof this.user.user_type === 'string') {
				this.usertypes.forEach((type) => {
					if (type.value === this.user.user_type) {
						this.user.user_type = type
					}
				})
			}

			if (this.user.user_type.value === 'bogorg') {
				this.user.baseurl = { label: 'Revenue', value: '/revenue' }
			}
		},
		role () {
			this.role_error = ''
		},
		organization () {
			this.organization_error = ''
		},
		baseurl () {
			//TODO add other pages here for user to choose from
			this.baseUrl_error = ''
			this.pageArray = []
			this.pageArray = this.pageArray.concat(mainItemsMenu)
			this.pageArray = this.pageArray.concat(revenueMenuItems)
			this.pageArray = this.pageArray.concat(petroleumeMenu)

			if (
				typeof this.user.baseurl === 'string' &&
        this.user.baseurl.startsWith('/')
			) {
				this.baseUrls.forEach((url) => {
					if (url.value === this.user.baseurl) {
						this.user.baseurl = url
					}
				})
			}

			if (this.user.baseurl && this.user.baseurl.value) {
				if (this.user.baseurl.value === '/petroleum') {
					this.pageArray = petroleumeMenu
				}
				if (this.baseurl.value === '/revenue') {
					this.pageArray = revenueMenuItems
				}
				if (
					this.user.user_type &&
          this.user.user_type.value &&
          this.showPageFor.includes(this.user.user_type.value)
				) {
					this.showpageSelect = true
				} else {
					this.showpageSelect = false
				}
			} else {
				this.showpageSelect = false
			}
			this.pageObjecMenus = this.menus()
		},
		organization () {
			this.organization_error = ''
		}
	},
	methods: {
		menus () {
			function getPnName (array, vm) {
				const finalDataArray = []
				array.forEach((array, index) => {
					if (array.page && array.page !== 'neutral') {
						if (array.items) {
							var isChecked = vm.user.selectedPages.includes(array.page)
							finalDataArray.push({
								title: array.items.page,
								checked: isChecked,
								selected: isChecked,
								children: getPnName(array.items.submenu, vm)
							})
						} else if (array.submenu) {
							var isChecked = vm.user.selectedPages.includes(array.page)
							finalDataArray.push({
								title: array.page,
								checked: isChecked,
								selected: isChecked,
								children: getPnName(array.submenu, vm)
							})
						} else {
							var isChecked = vm.user.selectedPages.includes(array.page)
							finalDataArray.push({
								title: array.page,
								checked: isChecked,
								selected: isChecked
							})
						}
					} else if (array.items) {
						array.items.forEach((itemsArray, index) => {
							const isChecked = vm.user.selectedPages.includes(itemsArray.page)
							finalDataArray.push({
								title: itemsArray.page,
								checked: isChecked,
								selected: isChecked,
								children: getPnName(itemsArray.submenu, vm)
							})
						})
					} else if (array.submenu) {
						var isChecked = vm.user.selectedPages.includes(array.page)
						finalDataArray.push({
							title: array.page,
							checked: isChecked,
							selected: isChecked,
							children: getPnName(array.submenu, vm)
						})
					} else {
						var isChecked = vm.user.selectedPages.includes(array.page)
						finalDataArray.push({
							title: array.page,
							checked: isChecked,
							selected: isChecked
						})
					}
				})
				return finalDataArray
			}

			return getPnName(this.pageArray, this)
		},
		setImage (photo) {
			this.user.photo = photo
		},
		search () {
			this.$refs.tree.searchNodes(this.searchword)
		},
		nodeSelected (node) {
			console.log(node)
			if (
				(node.selected || node.checked) &&
        node.parent &&
        node.parent() &&
        node.parent().title
			) {
				console.log(typeof node.parent())
				this.user.selectedPages.push(node.parent().title)
			} else if (node.parent && node.parent() && node.parent().title) {
				if (this.user.selectedPages.includes(node.parent().title)) {
					const index = this.user.selectedPages.indexOf(node.parent().title)
					if (index > -1) {
						this.user.selectedPages.splice(index, 1)
					}
				}
			}
			if (node.selected || node.checked) {
				if (!this.user.selectedPages.includes(node.title)) {
					this.user.selectedPages.push(node.title)
				}
				if (node.children) {
					node.children.forEach((child) => {
						if (
							(child.selected || child.checked) &&
              !this.user.selectedPages.includes(child.title)
						) {
							this.user.selectedPages.push(child.title)
						}
					})
				}
			} else {
				if (this.user.selectedPages.includes(node.title)) {
					const index = this.user.selectedPages.indexOf(node.title)
					if (index > -1) {
						this.user.selectedPages.splice(index, 1)
					}
				}
				if (node.children) {
					node.children.forEach((child) => {
						if (this.user.selectedPages.includes(child.title)) {
							const index = this.user.selectedPages.indexOf(child.title)
							if (index > -1) {
								this.user.selectedPages.splice(index, 1)
							}
						}
						if (this.user.selectedPages.includes(node.title)) {
							const index = this.user.selectedPages.indexOf(node.title)
							if (index > -1) {
								this.user.selectedPages.splice(index, 1)
							}
						}
					})
				}
			}
		},
		loadOptions () {
			this.post('/users/options', {})
				.then((response) => {
					const options = response.data
					this.organizations = options.organizations
					this.roles = options.roles
				})
				.catch((error) => {
					console.log(error)
				})
		},
		isEdit () {
			return Number(this.userid) !== 0
		},
		getData () {
			this.showLoading('getting user infomation')
			this.post('/users/get/', {
				id: this.userid
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						this.user = response.data.users[0]
					} else {
						this.$vs.notify({
							title: 'Error!!!',
							text: `${response.data.message}`,
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
						const vm = this
						setTimeout(function () {
							// vm.back();
						}, 2000)
					}
				})
				.catch((error) => {
					this.closeLoading()
					this.$vs.notify({
						title: 'Error!!!',
						text: `${error.message}`,
						sticky: true,
						color: 'danger',
						duration: null,
						position: 'bottom-left'
					})
					const vm = this
					setTimeout(function () {
						// vm.back();
					}, 2000)
				})
		},
		validateAndSubmit () {
			if (this.isEdit()) {
				if (!this.canUpdate()) {
					return Swal.fire(
						'Not Allowed!',
						'You do not have permission to edit any record',
						'error'
					)
				}
			} else if (!this.canAdd()) {
				return Swal.fire(
					'Not Allowed!',
					'You do not have permission to add any record',
					'error'
				)
			}
			console.log(this.user.selectedPages)
			this.$validator.validateAll().then((result) => {
				if (
					!this.user.user_type ||
          (this.user.user_type && !this.hasdata(this.user.user_type.value))
				) {
					this.usertype_error = 'The User Type field is required'
					result = false
				}
				if (
					this.user.user_type &&
          this.user.user_type.value &&
          this.user.user_type.value === 'bogorg'
				) {
					if (
						!this.user.organization ||
            (this.user.organization &&
              !this.hasdata(this.user.organization.value))
					) {
						this.organization_error = 'The Organization field is required'
						result = false
					}
				}
				if (
					this.user.user_type &&
          this.user.user_type.value &&
          this.user.user_type.value === 'system'
				) {
					if (
						!this.user.role ||
            (this.user.role && !this.hasdata(this.user.role.value))
					) {
						this.role_error = 'The Role field is required'
						result = false
					}
				}
				if (
					this.user.user_type &&
          this.user.user_type.value &&
          this.user.user_type.value === 'system'
				) {
					if (
						!this.user.baseurl ||
            (this.user.baseurl && !this.hasdata(this.user.baseurl.value))
					) {
						this.baseUrl_error = 'The Base URL field is required'
						result = false
					}
				}
				if (result) {
					if (this.user.user_type.value === 'bogorg') {
						this.user.access_level = 'user'
					}
					// if form have no errors
					//check if active page is edit or add
					if (!this.isEdit()) {
						this.saveUser()
					} else {
						this.updateUser()
					}
				}
			})
		},
		saveUser () {
			this.showLoading('creating user')
			this.post('/users/create/', {
				username: this.user.username,
				fullname: this.user.fullname,
				organization:
          null != this.organization && null != this.user.organization.value
          	? this.user.organization.value
          	: 0,
				email: this.user.email,
				phone: this.user.phone,
				user_type:
          null != this.user.user_type && null != this.user.user_type.value
          	? this.user.user_type.value
          	: null,
				access_level: this.user.access_level,
				password: this.user.password,
				photo: this.user.photo,
				baseurl: this.user.baseurl.value,
				pages: this.user.selectedPages,
				role:
          null != this.user.role && null != this.user.role.value
          	? this.user.role.value
          	: 0
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						Swal.fire(
							'Account Created!',
							response.data.message,
							'success'
						).then((result) => {
							if (result.isConfirmed) {
								this.back()
							}
						})
						this.photo = null
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		},
		updateUser () {
			this.showLoading('updating user')
			this.post('/users/update/', {
				id: this.user.id,
				username: this.user.username,
				fullname: this.user.fullname,
				organization:
          null != this.organization && null != this.user.organization.value
          	? this.user.organization.value
          	: 0,
				email: this.user.email,
				phone: this.user.phone,
				user_type:
          null != this.user.user_type && null != this.user.user_type.value
          	? this.user.user_type.value
          	: null,
				access_level: this.user.access_level,
				password: this.user.password,
				photo: this.user.photo,
				baseurl: this.user.baseurl.value,
				pages: this.user.selectedPages,
				role:
          null != this.user.role && null != this.user.role.value
          	? this.user.role.value
          	: 0
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						Swal.fire('Account Updated!', response.data.message, 'success')
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		},
		deleteMedia () {
			this.showLoading('Deleting photo, please wait')
			this.post('/users/delete_file', {
				oldfile: this.hasdata(this.user.photo) ? this.user.photo : null,
				id: this.hasdata(this.userid) ? this.userid : null
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						this.user.photo = null
						Swal.fire('Deleted!', response.data.message, 'success')
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					console.log(error)
					Swal.fire('Failed!', error.message, 'error')
				})
		}
	}
}
</script>

<style lang="scss">
@import "@/assets/scss/vuexy/extraComponents/tree.scss";

.tree-box {
  background: #fff;
  position: relative;
  z-index: 9;

  .search-input {
    margin-top: 10px;
    width: 98%;
    display: block;
  }
}

.rmNode {
  background-color: rgba(var(--vs-danger), 0.15);
  color: rgba(var(--vs-danger), 1);
  line-height: 13px;
}

button.btn-async {
  background: rgba(var(--vs-warning), 0.15);
}

button.btn-delete {
  background: rgba(var(--vs-danger), 0.15);
}

[dir="ltr"] .halo-tree li {
  padding: 5px 5px 5px 24px !important;
}
</style>