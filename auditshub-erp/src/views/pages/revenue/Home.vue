<template>
  <div class="vx-row">
    <div class="vx-col w-full sm:w-1/3">
      <statistics-card-line
        hideChart
        class="mb-base"
        icon="BookmarkIcon"
        icon-right
        :statistic="bogunauth"
        statisticTitle="BANK OF GHANA"
        :statistic-description="totalbogunauth"
        color="success"
      />
    </div>

    <div class="vx-col w-full sm:w-1/3">
      <statistics-card-line
        hideChart
        class="mb-base"
        icon="BookmarkIcon"
        icon-right
        :statistic="otherunauth"
        statisticTitle="OTHER BANKS"
        :statistic-description="totalotherunauth"
        color="danger"
      />
    </div>

    <div class="vx-col w-full sm:w-1/3">
      <statistics-card-line
        hideChart
        class="mb-base"
        icon="BookmarkIcon"
        icon-right
        :statistic="totaltunauth"
        statisticTitle="TOTAL BOG &amp; ORG"
        :statistic-description="totalunauthbank"
        color="warning"
      />
    </div>
    <div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/2">
      <vx-card :title="seriesA_details.name" class="mb-base">
        <p>{{ seriesA_details.stats_for }}</p>
        <template slot="actions">
          <feather-icon
            icon="SettingsIcon"
            svgClasses="w-6 h-6 text-grey"
          ></feather-icon>
        </template>
        <div slot="no-body" class="p-6 pb-0">
          <vue-apex-charts
            type="line"
            height="266"
            :options="chartOptionsA"
            :series="seriesA"
          />
        </div>
      </vx-card>
    </div>
    <div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/2">
      <vx-card :title="seriesB_details.name" class="mb-base">
        <p>{{ seriesB_details.stats_for }}</p>
        <template slot="actions">
          <feather-icon
            icon="SettingsIcon"
            svgClasses="w-6 h-6 text-grey"
          ></feather-icon>
        </template>
        <div slot="no-body" class="p-6 pb-0">
          <vue-apex-charts
            type="line"
            height="266"
            :options="chartOptionsB"
            :series="seriesB"
          />
        </div>
      </vx-card>
    </div>
  </div>
</template>

<script>
import StatisticsCardLine from '@/components/statistics-cards/StatisticsCardLine.vue'
import VueApexCharts from 'vue-apexcharts'

export default {
	components: {
		VueApexCharts,
		StatisticsCardLine
	},
	data () {
		return {
			bogunauth: 0,
			otherunauth: 0,
			totaltunauth: 0,
			totalbogunauth: '0',
			totalotherunauth: '0',
			totalunauthbank: '0',
			seriesA_details: {},
			seriesB_details: {},
			seriesA: [],
			seriesB: [],
			chartOptionsA: {
				chart: {
					toolbar: { show: false },
					dropShadow: {
						enabled: true,
						top: 10,
						left: 0,
						blur: 4,
						opacity: 0.1
					}
				},
				stroke: {
					curve: 'smooth',
					width: 4
				},
				grid: {
					borderColor: '#ebebeb'
				},
				legend: {
					show: false
				},
				colors: ['#df87f2'],
				fill: {
					type: 'gradient',
					gradient: {
						shade: 'dark',
						inverseColors: false,
						gradientToColors: ['#7367F0'],
						shadeIntensity: 1,
						type: 'horizontal',
						opacityFrom: 1,
						opacityTo: 1,
						stops: [0, 100, 100, 100]
					}
				},
				markers: {
					size: 0,
					hover: {
						size: 5
					}
				},
				xaxis: {
					labels: {
						style: {
							cssClass: 'text-grey fill-current'
						}
					},
					axisTicks: {
						show: false
					},
					categories: [
						'Jan',
						'Feb',
						'Mar',
						'Apr',
						'May',
						'Jun',
						'July',
						'Aug',
						'Sep',
						'Oct',
						'Nov',
						'Dec'
					],
					axisBorder: {
						show: false
					}
				},
				yaxis: {
					tickAmount: 5,
					labels: {
						style: {
							cssClass: 'text-grey fill-current'
						},
						formatter (val) {
							return val > 999 ? `${(val / 1000).toFixed(1)}k` : val
						}
					}
				},
				tooltip: {
					x: { show: false }
				}
			},
			chartOptionsB: {
				chart: {
					toolbar: { show: false },
					dropShadow: {
						enabled: true,
						top: 10,
						left: 0,
						blur: 4,
						opacity: 0.1
					}
				},
				stroke: {
					curve: 'smooth',
					dashArray: [0, 8],
					width: [4, 2]
				},
				grid: {
					borderColor: '#e7e7e7'
				},
				legend: {
					show: false
				},
				colors: ['#00db89'],
				fill: {
					type: 'gradient',
					gradient: {
						shade: 'dark',
						inverseColors: false,
						gradientToColors: ['#00b5b5'],
						shadeIntensity: 1,
						type: 'horizontal',
						opacityFrom: 1,
						opacityTo: 1,
						stops: [0, 100, 100, 100]
					}
				},
				markers: {
					size: 0,
					hover: {
						size: 5
					}
				},
				xaxis: {
					labels: {
						style: {
							cssClass: 'text-grey fill-current'
						}
					},
					axisTicks: {
						show: false
					},
					categories: [
						'Jan',
						'Feb',
						'Mar',
						'Apr',
						'May',
						'Jun',
						'July',
						'Aug',
						'Sep',
						'Oct',
						'Nov',
						'Dec'
					],
					axisBorder: {
						show: false
					}
				},
				yaxis: {
					tickAmount: 5,
					labels: {
						style: {
							cssClass: 'text-grey fill-current'
						},
						formatter (val) {
							return val > 999 ? `${(val / 1000).toFixed(1)}k` : val
						}
					}
				},
				tooltip: {
					x: { show: false }
				}
			}
		}
	},
	created () {
		this.post('/home/', {
			access_type: this.AppActiveUser.access_level,
			user_id: this.AppActiveUser.id
		})
			.then((response) => {
				const data = response.data
				this.bogunauth = data.bogunauth
				this.otherunauth = data.otherunauth
				this.totaltunauth = data.totaltunauth
				this.totalbogunauth = data.totalbogunauth
				this.totalotherunauth = data.totalotherunauth
				this.totalunauthbank = data.totalunauthbank
				this.seriesA_details = data.statsbog
				this.seriesB_details = data.statsorg
				this.seriesA = data.statsbog.stats
				this.seriesB = data.statsorg.stats
			})
			.catch((error) => {
				console.log(error)
			})
	}
}
</script>
