<template>
  <div class="text-center">
    <div class="otp-content mx-auto">
      <h4>{{ trans('enter_code_set_to_email') }} {{ email }}</h4>
      <div class="mt-4">
        <v-otp-input
            ref="otpInput"
            input-classes="otp-input"
            class="justify-content-center"
            separator=""
            :num-inputs="4"
            :should-auto-focus="true"
            :is-input-num="true"
            v-model="otp"
            @on-change="handleOnChange"
            @on-complete="handleOnComplete"
        />
      </div>
      <div v-if="wrongOtp" class="alert alert-danger mt-3">
        {{ trans('wrong_otp') }}
      </div>
      <div class="mt-4">
        <button class="btn-themed"
                @click="checkOTP">
          {{ trans('ready') }}
        </button>
      </div>
      <div class="mt-3" v-if="started">
        <a class="text-primary pointer"
           :class="{ inactive: secondsLeft > 0}"
           @click="sendAgain">
          {{ trans('send_again') }}
          <span class="ml-2" v-if="secondsLeft > 0">({{ secondsLeft }})</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex"

export default {
  name: "OTP",
  props: {
    email: {
      type: String,
      required: true
    }
  },
  computed: {
    ...mapState([
      'order',
    ]),
  },
  data() {
    return {
      otp: '',
      timer: null,
      secondsLeft: 0,
      wrongOtp: false,
      started: false
    }
  },
  methods: {
    async checkOTP() {
      let res = await axios.post(`/api/order/${this.order.id}/${this.order.hash}/email/confirm/check`, { code: this.otp })
      if(res && res.data) {
        this.$emit('confirmed')
      } else {
        this.wrongOtp = true
      }
    },
    async sendAgain() {
      if(this.secondsLeft > 0) return
      let res = await axios.post(`/api/order/${this.order.id}/${this.order.hash}/email/confirm/generate`, { email: this.email })
      if(res.data) {
        this.secondsLeft = 60
        this.launchTimer()
      }
    },
    launchTimer() {
      if(this.timer) clearInterval(this.timer)
      this.timer = setInterval(() => {
        this.secondsLeft--
        if(this.secondsLeft <= 0) {
          clearInterval(this.timer)
        }
      }, 1000)
      this.started = true
    },
    handleOnChange(val) {
      this.otp = val
    },
    handleOnComplete(val) {
      this.otp = val
    }
  },
  mounted() {
    this.sendAgain()
  },
  watch: {
    otp(val) {
      this.wrongOtp = false
      if(val.length === 4) this.checkOTP()
    }
  }
}
</script>

<style scoped>
.inactive {
  opacity: 0.5;
}
.btn-themed {
  min-width: 200px;
}
.otp-content {
  max-width: 500px;
}
#otp-input {
  text-align: center;
  font-size: 32px;
  letter-spacing: 10px;
}
</style>