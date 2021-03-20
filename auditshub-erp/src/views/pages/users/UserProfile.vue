<template>
  <vs-tabs
    :position="isSmallerScreen ? 'top' : 'left'"
    class="tabs-shadow-none"
    id="profile-tabs"
    :key="isSmallerScreen"
  >
    <!-- GENERAL -->
    <vs-tab
      icon-pack="feather"
      icon="icon-user"
      :label="!isSmallerScreen ? 'General' : ''"
    >
      <div class="tab-general md:ml-4 md:mt-0 mt-4 ml-0">
        <vx-card no-shadow>
          <!-- Img Row -->
          <div class="flex flex-wrap items-center mb-base">
            <vs-avatar :src="user.photo" size="70px" class="mr-4 mb-4" />
            <div>
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

          <div id="userInfo" class="vx-row">
            <!-- Info -->
            <div class="vx-col sm:w-1/2 w-full mb-2">
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
            <div class="vx-col sm:w-1/2 w-full mb-2">
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
            <div class="vx-col sm:w-1/2 w-full mb-2">
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
            <div class="vx-col sm:w-1/2 w-full mb-2">
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
          </div>
          <!-- Save & Reset Button -->
          <div class="flex flex-wrap items-center justify-end">
            <vs-button @click="saveUserInfo()" class="ml-auto mt-2">Save Changes</vs-button>
          </div>
        </vx-card>
      </div>
    </vs-tab>
    <vs-tab
      icon-pack="feather"
      icon="icon-lock"
      :label="!isSmallerScreen ? 'Change Password' : ''"
    >
      <div class="tab-change-pwd md:ml-4 md:mt-0 mt-4 ml-0">
        <vx-card no-shadow>
          <div id="userPassword" class="vx-row">

            <div class="vx-col sm:w-1/1 w-full mb-2">
              <vs-input
                type="password"
                v-validate="'required|min:5|max:10|is:'+user.password"
                label-placeholder="Old Password"
                name="old password"
                v-model="old_password"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('old password')"
                >{{ errors.first("old password") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/2 w-full mb-2">
              <vs-input
                type="password"
                v-validate="'required|min:5|max:10'"
                ref="password"
                label-placeholder="New Password"
                name="password"
                v-model="new_password"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('password')"
                >{{ errors.first("password") }}</span
              >
            </div>

            <div class="vx-col sm:w-1/2 w-full mb-2">
              <vs-input
                type="password"
                v-validate="'required|min:5|max:10|confirmed:password'"
                data-vv-as="password"
                label-placeholder="Confirm Password"
                name="confirm_password"
                v-model="confirm_new_password"
                class="mt-5 w-full"
              />
              <span
                class="text-danger text-sm"
                v-show="errors.has('confirm_password')"
                >{{ errors.first("confirm_password") }}</span
              >
            </div>
          </div>

          <!-- Save & Reset Button -->
          <div class="flex flex-wrap items-center justify-end">
            <vs-button @click="saveUserPassword()" class="ml-auto mt-2">Save Changes</vs-button>
          </div>
        </vx-card>
      </div>
    </vs-tab>
  </vs-tabs>
</template>

<script>
// Import Swal
import Swal from 'sweetalert2'

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
	props: {
		userid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			old_password: '',
			new_password: '',
			confirm_new_password: '',
			user: {
				id: 0,
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
				access_level: '',
				user_type: null
			}
		}
	},
	beforeMount () {
		this.user.id = this.AppActiveUser.id
	},
	mounted () {
		this.getData()
	},
	computed: {
		isSmallerScreen () {
			return this.$store.state.windowWidth < 768
		},
		showRemoveImage () {
			return this.hasdata(this.user.photo)
		}
	},
	watch: {},
	methods: {
		setImage (photo) {
			this.user.photo = photo
		},
		getData () {
			this.showLoading('getting user infomation')
			this.post('/users/profile/', {
				id: this.user.id
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
		saveUserPassword () {
			this.$validator.validateScopes('userPassword').then((result) => {
				if (result) {
					this.changePassword()
				}
			})
		},
		saveUserInfo () {
			this.$validator.validateScopes('userInfo').then((result) => {
				if (result) {
					this.updateUser()
				}
			})
		},
		updateUser () {
			this.showLoading('updating your account now')
			this.post('/users/update_profile/', {
				id: this.user.id,
				username: this.user.username,
				fullname: this.user.fullname,
				email: this.user.email,
				phone: this.user.phone,
				photo: this.user.photo
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						Swal.fire(
							'Account Updated!',
							response.data.message,
							'success'
						)
						this.storeData('gas_username', this.user.username)
						this.storeData('gas_name', this.user.fullname)
						this.storeData('gas_email', this.user.email)
						this.storeData('gas_userphoto', this.user.photo)
					} else {
						Swal.fire('Failed!', response.data.message, 'error')
					}
				})
				.catch((error) => {
					this.closeLoading()
					Swal.fire('Failed!', error.message, 'error')
				})
		},
		changePassword () {
			this.showLoading('updating your account password now')
			this.post('/users/changepass/', {
				id: this.user.id,
				email: this.user.email,
				cpassword: this.old_password,
				password: this.new_password
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						Swal.fire(
							'Password Updated!',
							`${response.data.message}. <br>You need to revalidate your login credentials.`,
							'success'
						)
						const vm = this
						setTimeout(() => {
							vm.logUserOut()
						}, 3000)
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
				id: this.hasdata(this.user.id) ? this.user.id : null
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
#profile-tabs {
  .vs-tabs--content {
    padding: 0;
  }
}
</style>