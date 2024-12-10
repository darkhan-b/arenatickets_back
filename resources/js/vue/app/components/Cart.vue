<template>
  <div v-if="cart && cart.length > 0">
    <div class="pt-0 cart-wrapper" :style="`max-height: ${maxCartListH}px`">

      <div v-if="refundableShouldBeChosen">
        <div class="fw-bold note-14 mb-2 mt-md-0 mt-2">{{ trans('choose_tariff') }}:</div>
        <RadioGroup :label="trans('non_refundable_ticket')"
                    :active="refundable === 0"
                    @click.native="$store.commit('setRefundable', 0)"/>
        <RadioGroup :label="`${trans('refundable_ticket')} (+${event.refundable_fee}%)`"
                    :active="refundable === 1"
                    @click.native="$store.commit('setRefundable', 1)"/>
        <hr/>
      </div>

      <div class="order-label mb-2 d-none d-md-block">{{ trans('your_order') }}:</div>

      <div class="fw-bold note-14">
        {{ trans('tickets_of') }}: {{ cart.length }}
        <span v-if="refundableShouldBeChosen && refundable === 1" class="badge badge-success ms-2">{{ trans('refundable_tariff') }}</span>
        <span v-if="refundableShouldBeChosen && refundable === 0" class="badge badge-danger ms-2">{{ trans('nonrefundable_tariff') }}</span>
      </div>

      <div v-for="c in Object.keys(cartSummary)" class="cart-item">
        <div class="row align-items-center">
          <div class="col">
            <div class="section">{{ cartSummary[c].section }}</div>
            <div class="seat">{{ cartSummary[c].seat }}</div>
          </div>
          <div class="col-auto">
            <div class="price">
              {{ cartSummary[c].price * discountedCoefficient | formatNumber }} ₸
              <div v-if="discountedCoefficient < 1"
                   style="text-decoration: line-through; color: #7e8389;font-size: 12px;font-weight: 400;">
                {{ cartSummary[c].price | formatNumber }} ₸
              </div>
            </div>
          </div>
          <div class="col-auto" v-if="(!order || !order.vendor) && !qrCodeImage">
            <a class="pointer"
               @click="deleteTicket(cartSummary[c])">
              <img src="/images/trashbin.svg" alt="delete"/>
            </a>
          </div>
        </div>
      </div>

      <div class="cart-item border-bottom-0" v-if="serviceFee > 0">
        <div class="row align-items-center">
          <div class="col">{{ trans('service_fee') }}</div>
          <div class="col-auto">
            <template v-if="event && event.external_fee_type === 'percent'">{{ event.external_fee_value | formatNumber }}%</template>
            <template v-else>{{ serviceFee | formatNumber }} ₸</template>
          </div>
        </div>
      </div>

      <div class="cart-item border-bottom-0" v-if="refundableShouldBeChosen && refundable === 1">
        <div class="row align-items-center">
          <div class="col">{{ trans('refundable_fee') }}</div>
          <div class="col-auto">{{ event.refundable_fee | formatNumber }}%</div>
        </div>
      </div>

      <div class="cart-item border-bottom-0" v-if="promocodeDiscount > 0 && step === 3">
        <div class="row align-items-center">
          <div class="col">{{ trans('promocode') }}</div>
          <div class="col-auto text-primary">-{{ promocodeDiscount }}%</div>
        </div>
      </div>

      <div class="cart-item fw-bold border-bottom-0">
        <div class="row align-items-center">
          <div class="col">{{ trans('total') }}</div>
          <div class="col-auto">{{ totalWithFeeAndRefundable | formatNumber }} ₸</div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex"
import EventBus from "../../../event-bus"
import RadioGroup from "./RadioGroup.vue"

export default {
  name: "Cart",
  components: {RadioGroup},
  data() {
    return {
      maxCartListH: 0
    }
  },
  computed: {
    ...mapState([
      'cart',
      'step',
      'order',
      'event',
      'refundable',
      'showMobileDetails',
      'promocodeDiscount',
      'qrCodeImage'
    ]),
    ...mapGetters(['getCartSum', 'discountedCoefficient', 'refundableShouldBeChosen', 'totalWithFeeAndRefundable']),
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
    serviceFee() {
      if(!this.event || this.event.external_fee_value <= 0) return 0
      if(this.event.external_fee_type === 'absolute') {
        return Math.round(this.event.external_fee_value * this.cart.length)
      }
      if(this.event.external_fee_type === 'percent') {
        return Math.round(this.event.external_fee_value * this.getCartSum) / 100
      }
      return 0
    },
  },
  methods: {
    async deleteTicket(c) {
      this.$store.commit('updateCart', { sign: '-', ticket: {
          seat_id: c.type && c.type == 'enter' ? null : c.seat_id,
          section_id: c.section_id,
          pricegroup_id: c.pricegroup_id,
        }
      })
      if(c.order_item_id && this.step === 3) {
        EventBus.$emit('deleteOrderItem', c.order_item_id)
      }
      await this.$nextTick()
      if(!this.cart.length) this.$store.commit('setShowMobileDetails', false)
    },
    calcMaxCart() {
      this.$nextTick(() => {
        // this.maxCartListH = window.innerHeight - this.$refs.topCartHeader.clientHeight - 240
        this.maxCartListH = window.innerHeight - document.getElementById('top-cart-header').clientHeight - 240
      })
    },
  },
  mounted() {
    this.calcMaxCart()
    window.addEventListener("resize", this.calcMaxCart)
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.calcMaxCart)
  },
  watch: {
    $route() {
      this.$store.commit('setShowMobileDetails', false)
    },
    timetable() {
      this.calcMaxCart()
    },
    step() {
      this.calcMaxCart()
    }
  }

}
</script>

<style scoped lang="scss">
.cart-wrapper {
  overflow-y: scroll;
  overflow-x: hidden;
}
</style>
