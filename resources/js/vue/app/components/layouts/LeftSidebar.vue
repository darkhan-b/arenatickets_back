<template>
  <div class="left-sidebar space-for-next" v-if="!qrCodeImage">

    <div id="top-cart-header" ref="topCartHeader">

      <div class="d-md-block d-none px-4">
        <div class="row g-0">
          <div class="col-md-8">
            <div class="left__event" v-if="event">{{ transName(event.title) }}</div>
            <div v-if="timetable">
              <div class="d-inline-block me-2">{{ timetable.date | formatDate }}</div>
              <div v-if="timetable.discount" class="discount-bubble">{{ trans('discount') }} {{ timetable.discount }}%</div>
            </div>
            <div class="text-muted" v-if="venue">{{ transName(venue.title) }}</div>
          </div>
          <div class="col-md-4" v-if="event && event.teaser">
            <img :src="event.teaser"
                 class="w-100"
                 :alt="event.title.ru"/>
          </div>
        </div>
      </div>

      <hr class="d-md-block d-none" v-if="event || timetable" :class="{ 'mb-0': (selectedSection && transName(selectedSection.note)) }"/>

      <div class="d-md-block d-none" v-if="selectedSection && transName(selectedSection.note)">
        <div class="alert alert-warning">{{ transName(selectedSection.note) }}</div>
      </div>

    </div>

    <div v-if="cart && cart.length > 0"
         class="mt-md-3 px-4 cart-list"
         :style="`bottom: ${bottomHeight}px`">
      <Cart/>
    </div>

    <div class="left__next px-md-4 px-3"
         id="bottomPart"
         ref="bottomPart"
         v-if="getCartSum > 0">

      <div v-if="refundableShouldBeChosen && refundable === null" class="mb-2 text-center note-14">{{ trans('choose_tariff_to_continue') }}</div>

      <button class="btn-themed w-100"
              v-if="!order"
              id="continue-btn"
              :disabled="loading || !refundableValid"
              @click="generateOrder">
        {{ trans('continue') }}
      </button>
      <div class="row align-items-center" v-if="order">
        <div class="col-auto">
          <OrderCounter/>
        </div>
        <div class="col">
          <button class="btn-themed w-100"
                  id="order-btn"
                  :disabled="loading || !checkoutFormValid || qrCodeImage"
                  @click="checkout">
            {{ trans('to_pay') }}
          </button>
        </div>
      </div>
      <div v-if="step === 3" class="small mt-1 text-center">
        <span class="text-muted">{{ trans('by_pressing_this_button_you_agree_with') }} </span>
        <a href="https://topbilet.kz/ru/pages/confidentiality" class="text-primary" target="_blank">{{ trans('data_processing_policy') }}</a>
      </div>

    </div>

    <!--    <div class="shadow" -->
    <!--         @click="$store.commit('toggleShowMobileDetails')" -->
    <!--         v-if="showMobileDetails"/>-->

  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import OrderCounter from "../OrderCounter"
import EventBus from "../../../../event-bus"
import Cart from '../Cart'

export default {
  components: {Cart, OrderCounter},
  computed: {
    ...mapState([
      'event',
      'cart',
      'timetable',
      'settings',
      'order',
      'venue',
      'checkoutFormValid',
      'step',
      'selectedSectionIndex',
      'showMobileDetails',
      'scheme',
      'loading',
      'qrCodeImage',
      'refundable'
    ]),
    ...mapGetters([
      'getCartSum',
      'discountedCoefficient',
      'refundableValid',
      'refundableShouldBeChosen',
      'serviceFee',
      'totalWithFee'
    ]),
    cartSummary() {
      let arr = {}
      let sections = {}
      let pricegroups = {}
      this.cart.forEach(item => {
        if(item.row && item.seat) {
          arr['seat_'+item.seat_id] = {
            order_item_id: item.order_item_id,
            section: this.transName(item.section_name),
            seat: this.trans('row')+': '+item.row+', '+this.trans('seat')+': '+item.seat,
            price: item.price,
            seat_id: item.seat_id,
            section_id: item.section_id,
            pricegroup_id: null,
            type: 'seats'
          }
          return
        }
        if(item.section_id) {
          sections[item.section_id] = (sections[item.section_id] ? sections[item.section_id] : 0) + 1
          for(var i = 1; i <= sections[item.section_id]; i++) {
            arr['section_'+item.section_id+'_'+i] = {
              order_item_id: item.order_item_id,
              section: this.transName(item.section_name),
              seat: this.trans('entrance'),
              seat_id: item.ticket_id,
              section_id: item.section_id,
              pricegroup_id: null,
              price: item.price,
              type: 'enter'
            }
          }
          return
        }
        if(item.pricegroup_id) {
          pricegroups[item.pricegroup_id] = (pricegroups[item.pricegroup_id] ? pricegroups[item.pricegroup_id] : 0) + 1
          for(var i = 1; i <= pricegroups[item.pricegroup_id]; i++) {
            arr['pg_' + item.pricegroup_id+'_'+i] = {
              order_item_id: item.order_item_id,
              section: this.transName(item.pricegroup_name),
              seat: this.trans('entrance'),
              seat_id: null,
              section_id: null,
              pricegroup_id: item.pricegroup_id,
              price: item.price,
              type: 'pricegroup'
            }
          }
        }
      })
      return arr
    },
    selectedSection() {
      if(this.selectedSectionIndex === null || !this.scheme) return null
      return this.scheme.sections[this.selectedSectionIndex] ?? null
    }
  },
  data() {
    return {
      bottomHeight: 0,
      // maxCartListH: 0
    }
  },
  mounted() {
  },
  beforeDestroy() {
  },
  methods: {
    getBottomHeight() {
      this.bottomHeight = this.$refs.bottomPart ? this.$refs.bottomPart.clientHeight : 0;
    },
    deleteTicket(c) {
      this.$store.commit('updateCart', { sign: '-', ticket: {
          seat_id: c.type && c.type == 'enter' ? null : c.seat_id,
          section_id: c.section_id,
          pricegroup_id: c.pricegroup_id,
        }
      })
      if(c.order_item_id && this.step === 3) {
        EventBus.$emit('deleteOrderItem', c.order_item_id)
      }
      this.$nextTick(() => {
        if(!this.cart.length) this.$store.commit('setShowMobileDetails', false)
      })
    },
    checkout() {
      if(!this.order) return
      this.$store.commit('setLoading', true)
      EventBus.$emit('fillOrder')
    },
    generateOrder() {
      this.$store.commit('setLoading', true)
      this.$store.dispatch('generateOrder').then(res => {
        window.loaderIcon(false)
        this.$store.commit('setLoading', false)
        // this.$store.commit('setSelectedSectionIndex', null)
        this.$router.push('/order/'+res.id+'/'+res.hash)
      }).catch(() => {
        this.$store.commit('setLoading', false)
      })
    },
  },
  watch: {
    showMobileDetails() {
      this.getBottomHeight()
    },
  }
}
</script>

<style scoped lang="scss">
#order-btn {
  background: #27AE60;
}
.alert-warning {
  font-size: 13px;
  border-radius: 0;
  color: #000;
  text-align: center;
  font-weight: 500;
}
@media(max-width: 768px) {
  .cart-list {
    max-height: 0;
    overflow: hidden;
    background: white;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 12;
    transition: all ease-in 0.2s;
    .left__title {
      padding-top: 20px;
    }
    &.shown {
      max-height: 100vh;
    }
  }
  .shadow {
    position: fixed;
    z-index: 11;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background: rgba(0,0,0,0.2);
  }
}
</style>
