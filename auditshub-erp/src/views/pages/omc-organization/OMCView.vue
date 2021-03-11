
<template>
  <div id="omc-org-view">
    <vs-alert
      color="danger"
      title="OMC Not Found"
      :active.sync="user_not_found"
    >
      <span>OMC record with id: {{ omcid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'omc-org-list' }" class="text-inherit underline"
          >All OMCs</router-link
        >
      </span>
    </vs-alert>

    <div id="omc-data" v-if="user_found">
      <vx-card title="Oil Marketing Company" class="mb-base">
        <!-- Avatar -->
        <div class="vx-row">
          <!-- Avatar Col -->
          <div class="vx-col" id="avatar-col">
            <div v-if="photo" class="con-img mb-2 mt-3">
              <img
                key="omc-image"
                :src="photo"
                alt="omc-img"
                width="100"
                height="100"
                class="rounded-full shadow-md cursor-pointer block"
              />
            </div>
          </div>

          <!-- Information - Col 1 -->
          <div class="vx-col flex-1" id="account-info-col-1">
            <table>
              <tr>
                <td class="font-semibold">Name</td>
                <td>{{ omc.name }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Email</td>
                <td>{{ omc.email }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Phone</td>
                <td>{{ omc.phone }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 1 -->

          <!-- Information - Col 2 -->
          <div class="vx-col flex-1" id="account-info-col-2">
            <table>
              <tr>
                <td class="font-semibold">Location</td>
                <td>{{ omc.location }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Region</td>
                <td>{{ omc.region }}</td>
              </tr>
              <tr>
                <td class="font-semibold">District</td>
                <td>{{ omc.district }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 2 -->
          <div class="vx-col w-full flex" id="account-manage-buttons">
            <vs-button
              icon-pack="feather"
              icon="icon-edit"
              class="mx-1"
              v-if="canUpdate()"
              :to="{ name: 'omc-org-edit', params: { omcid: omcid } }"
              >Edit</vs-button
            >
            <vs-button
              type="border"
              color="danger"
              icon-pack="feather"
              icon="icon-trash"
              class="mx-1"
              v-if="canDelete()"
              @click="deleteWarnSingle(omcid)"
              >Delete</vs-button
            >
            <vs-spacer />
          </div>
        </div>
      </vx-card>
    </div>
  </div>
</template>

<script>
// Import Swal
import Swal from 'sweetalert2'

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
	beforeRouteLeave (to, from, next) {
		if (this.statuscheck) {
			clearInterval(this.statuscheck)
			this.statuscheck = null
		}
		next()
	},
	props: {
		omcid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			user_not_found: false,
			user_found: false,
			omc: {}
		}
	},
	computed: {
		photo () {
			return require('@/assets/images/portrait/small/default.png')
		}
	},
	mounted () {
		this.getData()
	},
	watch: { },
	methods: {
		getData () {
			this.showLoading('getting OMC infomation')
			this.post('/omc/get', {
				id: this.omcid
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						this.user_found = true
						this.omc = response.data.omcs[0]
					} else {
						this.user_not_found = true
						this.$vs.notify({
							title: 'Error!!!',
							text: `${response.data.message}`,
							sticky: true,
							color: 'danger',
							duration: null,
							position: 'bottom-left'
						})
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
					this.user_not_found = true
				})
		}
	}
}
</script>

<style lang="scss">
</style>