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
              v-validate="'required'"
              name="Designation"
              class="w-full"
              label-placeholder="Designation"
              v-model="user.designation"
            />
            <span class="text-danger text-sm" v-show="errors.has('Designation')">{{
              errors.first("Designation")
            }}</span>
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
              v-validate="'required'"
              name="TIN"
              class="w-full"
              label-placeholder="TIN"
              v-model="user.tin"
            />
            <span class="text-danger text-sm" v-show="errors.has('TIN')">{{
              errors.first("TIN")
            }}</span>
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required'"
              name="Location"
              class="w-full"
              label-placeholder="Location"
              v-model="user.location"
            />
            <span class="text-danger text-sm" v-show="errors.has('Location')">{{
              errors.first("Location")
            }}</span>
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required'"
              name="Region"
              class="w-full"
              label-placeholder="Region"
              v-model="user.region"
            />
            <span class="text-danger text-sm" v-show="errors.has('Region')">{{
              errors.first("Region")
            }}</span>
          </div>
          <div class="vx-col sm:w-1/4 w-full mb-2">
            <vs-input
              v-validate="'required'"
              name="Distric"
              class="w-full"
              label-placeholder="Distric"
              v-model="user.district"
            />
            <span class="text-danger text-sm" v-show="errors.has('Distric')">{{
              errors.first("Distric")
            }}</span>
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
			user: {
				selectedPages: [],
				username: '',
				fullname: '',
				email: '',
				phone: '',
				password: '',
				photo: '',
				baseurl: '/petroleum/icoms/declearations',
				confirm_password: '',
				tin: '',
				location: '',
				region: '',
				district: '',
				designation: '',
				organization: 0,
				role: 0,
				access_level: 'bdc',
				user_type: 'bdc'
			}
		}
	},
	components: {
		VTree,
		VSelectTree
	},
	beforeMount () {},
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
		}
	},
	watch: {},
	methods: {
		setImage (photo) {
			this.user.photo = photo
		},
		search () {
			this.$refs.tree.searchNodes(this.searchword)
		},
		isEdit () {
			return Number(this.userid) !== 0
		},
		getData () {
			this.showLoading('getting user infomation')
			this.post('/users/bdc/', {
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
				if (result) {
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
				email: this.user.email,
				phone: this.user.phone,
				password: this.user.password,
				photo: this.user.photo,
				baseurl: this.user.baseurl,
				pages: this.user.selectedPages,
				tin: this.user.tin,
				location: this.user.location,
				region: this.user.region,
				district: this.user.district,
				designation: this.user.designation,
				organization: 0,
				user_type: 'bdc',
				access_level: this.user.access_level,
				role: 0
			})
				.then((response) => {
					console.log(response.data)
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
			this.showLoading('creating user')
			this.post('/users/update/', {
				id: this.user.id,
				username: this.user.username,
				fullname: this.user.fullname,
				email: this.user.email,
				phone: this.user.phone,
				password: this.user.password,
				photo: this.user.photo,
				baseurl: this.user.baseurl,
				pages: this.user.selectedPages,
				tin: this.user.tin,
				location: this.user.location,
				region: this.user.region,
				district: this.user.district,
				designation: this.user.designation,
				organization: 0,
				user_type: 'bdc',
				access_level: this.user.access_level,
				role: 0
			})
				.then((response) => {
					console.log(response.data)
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