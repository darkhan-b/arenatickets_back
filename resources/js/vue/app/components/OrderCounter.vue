<template>
  <div class="countdown">
    <span v-if="checkouttime > 0">{{ minutes }}:{{ seconds }}</span>
    <span v-if="checkouttime <= 0">{{ trans('passed') }}</span>
  </div>
</template>

<script>
import moment from "moment"
import {mapState} from "vuex"

export default {
  name: "OrderCounter",
  computed: {
    ...mapState(['order', 'checkouttime', 'config']),
    minutes () {
      return String(Math.floor(this.checkouttime / 60)).padStart(2, '0')
    },
    seconds () {
      return String(this.checkouttime % 60).padStart(2, '0')
    },
    timeToCheckout() {
      return (this.config && this.config.order_time_limit ? this.config.order_time_limit : 20) * 60
    }
  },
  data() {
    return {
      timer: null,
    }
  },
  mounted() {
    this.launchTimer()
  },
  methods: {
    launchTimer() {
      if(this.order) {
        this.countdown()
        this.timer = setInterval(this.countdown, 1000)
      }
    },
    countdown() {
      if(!this.order) return
      let now = moment(new Date());
      let end = moment(this.order.created_at)
      this.$store.commit('setCheckoutTime', Math.round(this.timeToCheckout - moment.duration(now.diff(end)).asSeconds()))
      if(this.checkouttime <= 0 && this.timer) {
        clearInterval(this.timer)
      }
    },
  }
}
</script>

<style scoped>

</style>
