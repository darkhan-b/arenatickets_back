<template>
  <div>
    <div class="top-sidebar">
      <div class="row g-0 w-100 align-items-center">
        <div class="col-md-auto col">
          <button v-if="showBack" class="btn-themed btn-back me-3" @click="back">
            <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0.292893 7.29289C-0.0976314 7.68342 -0.0976315 8.31658 0.292893 8.70711L6.65685 15.0711C7.04738 15.4616 7.68054 15.4616 8.07107 15.0711C8.46159 14.6805 8.46159 14.0474 8.07107 13.6569L2.41421 8L8.07107 2.34315C8.46159 1.95262 8.46159 1.31946 8.07107 0.928932C7.68054 0.538407 7.04738 0.538407 6.65686 0.928931L0.292893 7.29289ZM15 7L1 7L1 9L15 9L15 7Z"/>
            </svg>
            <!--            <img src="/images/left3.svg" alt="back"/>-->
          </button>
        </div>
        <div class="col">
          <div class="left__logoholder">
            <img src="/images/logo.png" class="left__logo" alt="логотип"/>
          </div>
        </div>
        <div class="col-auto" v-if="settings">
          <div class="top__contacts" :class="{ rightPadding: withoutIframe != 1 }">
            <a class="d-block phone" :href="'tel:'+(transName(settings.phone.body)).replace(/\D/g,'')">
              <img src="/images/phone2.svg" class="me-1" alt="phone"/>
              <span class="d-md-inline d-none">{{ transName(settings.phone.body) }}</span>
            </a>
            <div class="ps-4 ms-1 schedule d-md-block d-none">{{ transName(settings.schedule.body) }}</div>
          </div>
        </div>
        <div class="col-auto" v-if="withoutIframe == 1">
          <a @click="backToSite" class="non-iframe-close">
            <img src="/images/close.svg"/>
          </a>
        </div>
      </div>

    </div>

    <div class="d-md-none d-block event-info">
      <div class="row g-0">
        <div class="col-8">
          <div class="left__event" v-if="event">{{ transName(event.title) }}</div>
          <div v-if="timetable">
            <div class="d-inline-block me-2">{{ timetable.date | formatDate }}</div>
            <div v-if="timetable.discount" class="discount-bubble">{{ trans('discount') }} {{ timetable.discount }}%</div>
          </div>
          <div class="text-muted" v-if="venue">{{ transName(venue.title) }}</div>
        </div>
        <div class="col-4" v-if="event && event.teaser">
          <img class="w-100" :src="event.teaser" :alt="event.title.ru"/>
        </div>
      </div>
      <div class="mt-1 d-mb-block d-none" v-if="selectedSection && transName(selectedSection.note)">
        <div class="alert alert-warning mb-1 px-2 py-2">{{ transName(selectedSection.note) }}</div>
      </div>

      <div class="d-md-none d-block position-relative" style="padding-bottom: 2px" v-if="cart && cart.length">
        <div class="mobile-toggler mb-0 px-3 d-flex flex-column justify-content-center"
             :class="{
                danger: refundableShouldBeChosen && refundable === null,
                success: refundableShouldBeChosen && refundable === 1
        }">
          <div class="row align-items-center" @click="$store.commit('toggleShowMobileDetails')">
            <div class="col">
              {{ trans('your_order') }}: <span class="text-lowercase">{{ trans('tickets_of') }}</span> - {{ cart.length }}, {{ trans('sum') }} -
              <template v-if="step === 3">{{ totalWithFeeAndRefundable | formatNumber }} ₸</template>
              <template v-else>{{ getCartSum | formatNumber }} ₸</template>
              <div v-if="refundableShouldBeChosen" class="mb-1">
                <span v-if="refundable === null" class="badge badge-danger-outline">{{ trans('choose_tariff') }}</span>
                <span v-if="refundable === 1" class="badge badge-success">{{ trans('refundable_tariff') }}</span>
                <span v-if="refundable === 0" class="badge badge-danger">{{ trans('nonrefundable_tariff') }}</span>
              </div>
            </div>
            <div class="col-auto">
              <a class="rotation"
                 :class="{ rotated: showMobileDetails }">
                <img src="/images/down3.svg"/>
              </a>
            </div>
          </div>
        </div>
        <Cart ref="cart"
              class="px-3 bg-white position-absolute cart-list"
              :class="{ shown: showMobileDetails, ref: refundableShouldBeChosen }"/>
      </div>

    </div>

    <div class="d-md-none d-block" v-if="selectedSection && transName(selectedSection.note)">
      <div class="alert alert-warning px-3 py-2">{{ transName(selectedSection.note) }}</div>
    </div>

  </div>
</template>

<script>
import {mapGetters, mapState} from 'vuex'
import Cart from '../Cart'

export default {
  components: {Cart},
  computed: {
    ...mapState([
      'step',
      'cart',
      'order',
      'timetable',
      'settings',
      'event',
      'venue',
      'selectedSectionIndex',
      'scheme',
      'confirmingEmail',
      'showMobileDetails',
      'qrCodeImage',
      'refundable'
    ]),
    ...mapGetters([
      'getCartSum',
      'refundableShouldBeChosen',
      'serviceFee',
      'totalWithFee',
      'totalWithFeeAndRefundable'
    ]),
    showBack() {
      if(!this.timetable || this.qrCodeImage) return false
      if(this.timetable.type === 'pricegroups' && this.step > 2) return true
      if(this.timetable.type === 'sections' && this.step > 1) return true
      return false
    },
    selectedSection() {
      if(this.selectedSectionIndex === null || !this.scheme) return null
      return this.scheme.sections[this.selectedSectionIndex] ?? null
    }
  },
  data() {
    return {
      withoutIframe: this.$route.query.mobile,
      // menu: [
      //     { title: 'hall_scheme', mob_title: "hall", note: 'choose_sector' },
      //     { title: 'sector_scheme', mob_title: "sector", note: 'choose_seats' },
      //     { title: 'checkout_w', mob_title: "checkout_w", note: 'fill_data' },
      // ]
    }
  },
  mounted() {
    window.addEventListener('message', this.processMessage);
    this.$store.commit('setWithoutIframe', this.withoutIframe)
  },
  beforeDestroy() {
    window.removeEventListener('message', this.processMessage)
  },
  methods: {
    async back() {
      this.$store.commit('setQRCodeImage', null)
      if(this.confirmingEmail) {
        this.$store.commit('setConfirmingEmail', false)
        return
      }
      if(this.step === 3 && this.order) {
        await this.cancelOrder()
        return
      }
      this.$store.commit('setStep', 1)
    },
    async cancelOrder(sendClose = false) {
      window.loaderIcon()
      await axios.delete(`/api/order/${this.order.id}/${this.order.hash}`)
      this.$store.commit('setStep', 1)
      this.$store.commit('setOrder', null)
      if(sendClose) {
        if(window && window.parent) {
          window.parent.postMessage(JSON.stringify({
            action: 'close'
          }), '*');
        }
      }
      this.$router.push(`/${this.timetable.show_id}/${this.timetable.id}`)
    },
    processMessage(message) {
      // const data = message.data;
      // try {
      // const decoded = JSON.parse(data);
      // console.log(decoded);
      // if(decoded.action === 'cancelOrder' && this.order && this.step === 3) {
      // this.cancelOrder(true)
      // }
      // } catch (e) {}
    },
    async backToSite() {
      if(this.step === 3 && this.order) {
        await this.cancelOrder()
      }
      location.href = 'https://topbilet.kz/ru/event/'+this.event.slug
    }
  }
}
</script>

<style scoped lang="scss">
.event-info {
  font-size: 13px;
  padding: 5px 20px 5px 20px;
  border-bottom: 1px solid #D7DADD;
  background: #fff;
}
.alert-warning {
  font-size: 11px;
  text-align: center;
  border-radius: 0;
  color: #000;
  font-weight: 500;
}
.rotation img {
  transition: transform 0.15s ease-in;
}
.rotated img {
  transform: rotate(180deg);
}
.cart-list {
  transition: all ease-in 0.2s;
  top: 36px;
  overflow: hidden;
  left: 0;
  width: 100%;
  max-height: 0;
  z-index: 20;
  &.shown {
    max-height: 100vh;
    box-shadow: 0px 1px 25px rgba(92, 96, 104, 0.25);
  }
  &.ref {
    top: 46px;
  }
}
.mobile-toggler {
  border: 1px solid #D6D9DC;
  border-radius: 8px;
  font-weight: bold;
  min-height: 34px;
  z-index: 21;
  margin-top: 5px;
  &.danger {
    border-color: #FC385B;
  }
  &.success {
    border-color: #27AE60;
  }
}
</style>
