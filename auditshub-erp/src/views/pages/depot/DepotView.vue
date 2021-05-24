
<template>
  <div id="depot-view">
    <vs-alert
      color="danger"
      title="DBC Not Found"
      :active.sync="user_not_found"
    >
      <span>DBC record with id: {{ depotid }} not found. </span>
      <span>
        <span>Check </span
        ><router-link :to="{ name: 'depot-list' }" class="text-inherit underline"
          >All OMCs</router-link
        >
      </span>
    </vs-alert>

    <div id="depot-data" v-if="user_found">
      <vx-card title="Bulk Distribution Company" class="mb-base">
        <!-- Avatar -->
        <div class="vx-row">
          <!-- Avatar Col -->
          <div class="vx-col" id="avatar-col">
            <div v-if="photo" class="con-img mb-2 mt-3">
              <img
                key="depot-image"
                :src="photo"
                alt="depot-img"
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
                <td>{{ depot.name }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Email</td>
                <td>{{ depot.email }}</td>
              </tr>
            </table>
          </div>
          <!-- /Information - Col 1 -->

          <!-- Information - Col 2 -->
          <div class="vx-col flex-1" id="account-info-col-2">
            <table>
              <tr>
                <td class="font-semibold">Phone</td>
                <td>{{ depot.phone }}</td>
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
              :to="{ name: 'depot-edit', params: { depotid: depotid } }"
              >Edit</vs-button
            >
            <vs-button
              type="border"
              color="danger"
              icon-pack="feather"
              icon="icon-trash"
              class="mx-1"
              v-if="canDelete()"
              @click="deleteWarnSingle(depotid)"
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
		depotid: {
			type: String / Number,
			default: 0
		}
	},
	data () {
		return {
			user_not_found: false,
			user_found: false,
			depot: {}
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
			this.showLoading('getting DBC infomation')
			this.post('/depot/get', {
				id: this.depotid
			})
				.then((response) => {
					this.closeLoading()
					if (response.data.success == true) {
						this.user_found = true
						this.depot = response.data.omcs[0]
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