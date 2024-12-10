<template>
    <div>
        <div class="container-fluid" v-if="fade">
            <div class="row align-items-center">
                <div class="col-6">
                    <h6 class="mb-0">Добро пожаловать в админ панель</h6>
                </div>
<!--                <div class="col-6 text-right">-->
<!--                    <button class="btn btn-themed btn-info" @click="refreshData">{{ trans("refresh") }}</button>-->
<!--                </div>-->
            </div>
            <hr>
<!--            <line-chart-->
<!--                    v-if="fade"-->
<!--                    :chartdata="chartdata"-->
<!--                    :options="options"/>-->
<!--            <div class="row">-->
<!--                <div class="col-auto">-->
<!--                    <h6 class="mb-3 tab-toggles btn border btn-themed" @click="changeTo24" :class="{active: timeframe == 'day'}">За последние 24 часа</h6>-->
<!--                </div>-->
<!--                <div class="col-auto">-->
<!--                    <h6 class="mb-3 tab-toggles btn border btn-themed" @click="changeToTotal" :class="{active: timeframe == 'all'}">Итого</h6>-->
<!--                </div>-->
<!--            </div>-->

<!--            <div class="row">-->
<!--                <div class="col-lg-4 col-6 mb-4" v-for="(det,ind) in details">-->
<!--                    <router-link :to="det.url" class="layers card p-3">-->
<!--                        <div class="layer">-->
<!--                            <span class="admin-block-header">{{ trans(timeframe == 'all' ? 'total_'+ind : 'new_'+ind) }}</span>-->
<!--                        </div>-->
<!--                        <div class="mt-2">-->
<!--                            <span class="badge" :class="det.data > 0 ? 'badge-'+det.badge : 'badge-new'">{{ det.data | formatNumber }}</span>-->
<!--                        </div>-->
<!--                    </router-link>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
</template>

<script>
    // import LineChart from './Chart.vue'

    export default {
        components: {
            // LineChart
        },
        mounted() {
            this.fade = false
            this.loadData()
        },
        methods: {
            loadData() {
                axios.get('/admin/dashboard/details',{params: {timeframe: this.timeframe}}).then(res => {
                    console.log(res.data)
                    this.fade = true
                    // this.details = res.data.chart
                    this.chartdata = res.data.chart
                })
            },
            // refreshData(message = true) {
            //     window.loaderIcon()
            //     axios.get('/admin/dashboard/update',{params: { timeframe: this.timeframe}}).then(res => {
            //         this.fade = true
            //         this.details = res.data
            //         if(message) {
            //             window.noty()
            //         }
            //     })
            // },
            // changeTo24() {
            //     this.timeframe = 'day'
            //     this.refreshData(false)
            // },
            // changeToTotal() {
            //     this.timeframe = 'all'
            //     this.refreshData(false)
            // },
        },
        data: function() {
            return {
                details: null,
                fade: false,
                timeframe: 'day',
                chartdata: null,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                        line: {
                            tension: 0
                        }
                    }
                }

            }
        }
    }
</script>

<style scoped>
    /*.admin-block-header {*/
    /*    color: #72777a;*/
    /*    font-size: .875rem;*/
    /*}*/
    /*.badge {*/
    /*    font-size: 14px;*/
    /*}*/
    /*.tab-toggles {*/
    /*    color: #72777a;*/
    /*    cursor: pointer;*/
    /*}*/
    /*.tab-toggles.active {*/
    /*    color: #000;*/
    /*    background-color: white;*/

    /*}*/
</style>
