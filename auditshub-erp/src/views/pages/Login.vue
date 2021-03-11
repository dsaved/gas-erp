<!-- =========================================================================================
    File Name: Login.vue
    Description: Login Page
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <div
    class="h-screen flex w-full bg-img vx-row no-gutter items-center justify-center"
    id="page-login"
  >
    <div class="vx-col sm:w-1/2 md:w-1/2 lg:w-3/4 xl:w-3/5 sm:m-0 m-4">
      <vx-card>
        <div slot="no-body" class="full-page-bg-color">
          <div class="vx-row no-gutter justify-center items-center">
            <div class="vx-col hidden lg:block lg:w-1/2">
              <img
                src="@/assets/images/pages/login.png"
                alt="login"
                class="mx-auto"
              />
            </div>

            <div class="vx-col sm:w-full md:w-full lg:w-1/2 d-theme-dark-bg">
              <div class="p-8 login-tabs-container">
                <div class="vx-card__title mb-4">
                  <h4 class="mb-4">Login</h4>
                  <p>Welcome back, please login to your account.</p>
                </div>

                <form v-on:keyup.enter="login">
                  <vs-input
                    name="text"
                    icon-no-border
                    icon="icon icon-user"
                    icon-pack="feather"
                    label-placeholder="Username, Email Or Phone"
                    v-model="identification"
                    class="w-full"
                  />

                  <vs-input
                    type="password"
                    name="password"
                    icon-no-border
                    icon="icon icon-lock"
                    icon-pack="feather"
                    label-placeholder="Password"
                    v-model="access"
                    class="w-full mt-6"
                  />

                  <vs-button
                    v-on:click="login()"
                    id="button-with-loading"
                    class="float-right my-5"
                    >Login</vs-button
                  >

                  <vs-divider></vs-divider>

                  <div
                    class="social-login-buttons flex flex-wrap items-center mt-4"
                  >
                    <div v-html="response"></div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </vx-card>
    </div>
  </div>
</template>

<script>
// Import Storage
import mStorage from '@/store/storage.js'

// Vue Router
import router from '../../router'

export default {
	data () {
		return {
			identification: '',
			access: '',
			response: '',
			backgroundLoading: 'primary',
			colorLoading: '#fff',
			loading: false
		}
	},
	watch: {
		loading () {
			if (this.loading) {
				this.$vs.loading({
					background: this.backgroundLoading,
					color: this.colorLoading,
					container: '#button-with-loading',
					scale: 0.45
				})
			} else {
				this.$vs.loading.close('#button-with-loading > .con-vs-loading')
			}
		}
	},
	computed: {},
	methods: {
		login () {
			if (!this.loading) {
				this.response = ''
				if (!this.hasdata(this.identification)) {
					setTimeout(() => {
						this.response =
              '<span style=\'color:red\'>Please provide user Identity</span>'
					}, 200)
					return false
				}

				if (!this.hasdata(this.access)) {
					setTimeout(() => {
						this.response =
              '<span style=\'color:red\'>Password Is Required</span>'
					}, 200)
					return false
				}
				this.loading = true
				this.post_no_secure('/userauth/login', {
					user: this.identification,
					password: this.access
				})
					.then((result) => {
						console.log(result.data)
						this.loading = false
						if (result.data.success == true) {
							if (this.hasdata(result.data.authorization_token)) {
								mStorage.set(
									`${this.storage_key  }gas_authorization`,
									result.data.authorization_token
								)
							}
							const user = result.data.user
							this.response =
                `<span style='color:green'>${ 
                	result.data.message 
                } ${ 
                	user.username 
                }</span>`

							const userData = {
								id:user.id,
								name: user.fullname,
								username: user.username,
								email: user.email,
								photo: user.photo,
								access_level: user.access_level,
								baseUrl: user.baseurl,
								types: result.data.types,
								permissions: user.permissions,
								pages: user.pages
							}
							this.setUser(userData)
							setTimeout(() => {
								router.replace(userData.baseUrl)
							}, 1000)
						} else {
							this.response =
                `<span style='color:red'>${  result.data.message  }</span>`
						}
					})
					.catch((error) => {
						let e
						if (error.response) {
							e = error.response.data
						} else if (error.request) {
							// The request was made but no response was received
							e = { success: false, message: error.request }
						} else {
							// Something happened in setting up the request that triggered an Error
							e = { success: false, message: error.message }
						}
						this.loading = false
						this.response = `<span style='color:red'>${  e.message  }</span>`
						console.log(error)
					})
			}
		}
	}
}
</script>