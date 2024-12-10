<template>
  <div>
    <div class="row mb-md-4 mb-4 mt-md-4 mt-3 align-items-center" v-if="!qrCodeImage">
      <div class="col">
        <h1 class="mb-0">{{ trans('order_checkout') }}</h1>
      </div>
    </div>

    <div v-if="orderNotFound" >
      <div class="alert alert-danger">
        {{ trans('order_not_found') }}
      </div>
    </div>

    <div v-if="order && !confirmingEmail && !qrCodeImage" class="checkout-form">

      <PayMethods v-model="form.pay_system"
                  class="mb-4"/>

      <div class="bigger-text">{{ trans('enter_your_contact_details') }}:</div>

      <div class="form-group">
        <label class="label-ch" for="email-ch">Email:</label>
        <div class="row align-items-center">
          <div class="col-md-7 col-12">
            <input type="email"
                   id="email-ch"
                   class="form-control"
                   v-model="form.email"/>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="label-ch" for="phone-ch">{{ trans('phone') }}:</label>
        <div class="row align-items-center">
          <div class="col-md-7 col-12">
            <input type="text"
                   id="phone-ch"
                   class="form-control"
                   v-model="form.phone"/>
          </div>
          <div class="col-12">
            <div class="note-ch">{{ trans('for_operative_feedback') }}.</div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="label-ch" for="name-ch">{{ trans('name') }}:</label>
        <div class="row align-items-center">
          <div class="col-md-7 col-12">
            <input type="text"
                   id="name-ch"
                   class="form-control"
                   v-model="form.name"/>
          </div>
        </div>
      </div>

      <div class="form-group" v-if="timetable.hasPromocodes">
        <label class="label-ch" for="promo-ch">{{ trans('promocode') }}:</label>
        <div class="row align-items-center">
          <div class="col-md-7 col-12">
            <div class="position-relative">
              <input type="text"
                     id="promo-ch"
                     class="form-control"
                     v-model="form.promocode"/>
              <button @click="promocodeCheck"
                      :disabled="!form.promocode"
                      class="btn btn-themed btn-outline-secondary btn-check-promocode">
                {{ trans('check') }}
              </button>
            </div>
            <div v-if="promocodeDiscount" class="small text-primary">{{ trans('promocode_found') }}: {{ promocodeDiscount }}%</div>
            <div v-if="promocode && promocodeNotFound" class="small text-danger">{{ trans('promocode_not_found') }}</div>
          </div>
        </div>
      </div>

      <div class="form-group" v-if="canHaveComment">
        <label class="label-ch" for="comment-ch">{{ trans('comment') }}:</label>
        <div class="row align-items-center">
          <div class="col-md-7 col-12">
            <input type="text"
                   id="comment-ch"
                   class="form-control"
                   v-model="form.comment"/>
          </div>
        </div>
      </div>

    </div>

    <div v-if="order && qrCodeImage">
      <h1 class="mb-0 mt-4">{{ trans('scan_for_payment') }}:</h1>
      <div class="mt-4">
        <img :src="`data:image/png;base64,${qrCodeImage}`" style="width: 300px" alt="qr"/>
      </div>
    </div>

    <KaspiForm :order="order" ref="kform"/>

    <OTP v-if="order && confirmingEmail"
         class="mt-5"
         :email="form.email"
         @confirmed="otpConfirmed"/>

  </div>
</template>

<script>
import {mapGetters, mapState} from 'vuex'
import EventBus from '../../../event-bus'
import OrderCounter from "./OrderCounter"
import PayMethods from "./PayMethods"
import OTP from "./OTP"
import KaspiForm from "./KaspiForm"
import { getGoogleCID } from "../../../helpers"

export default {
  name: "Checkout",
  components: {
    KaspiForm,
    OTP,
    PayMethods,
    OrderCounter
  },
  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        comment: '',
        pay_system: 'card',
        promocode: ''
        // pay_system: 'kaspi',
      },
      orderNotFound: false,
      emailConfirmed: true,
      promocodeNotFound: false,
      // emailConfirmed: false,
    }
  },
  computed: {
    ...mapState([
      'cart',
      'timetable',
      'order',
      'event',
      'user',
      'checkouttime',
      'confirmingEmail',
      'withoutIframe',
      'promocodeDiscount',
      'qrCodeImage',
      'refundable'
    ]),
    ...mapGetters([
      'hasKassirRights',
      'isOrganizerForShow',
      'discountRate',
      'totalWithFeeAndRefundable'
    ]),
    valid() {
      return this.form.name
          && this.form.phone
          && this.form.email
          && this.isValidEmail
    },
    isValidEmail() {
      return String(this.form.email).toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    email() {
      return this.form.email
    },
    canHaveComment() {
      return ['invitation', 'invitation_hide'].includes(this.form.pay_system)
    },
    promocode() {
      return this.form.promocode
    }
  },
  created() {
    this.$store.commit('setStep', 3)
    if(this.hasKassirRights) {
      this.form.pay_system = 'cash'
    }
    this.loadOrder()
    EventBus.$on('fillOrder', this.orderFill)
    EventBus.$on('deleteOrderItem', this.deleteOrderItem)
    this.populateFromUser()
  },
  beforeDestroy() {
    EventBus.$off('fillOrder', this.orderFill)
    EventBus.$off('deleteOrderItem', this.deleteOrderItem)
  },
  methods: {
    loadOrder() {
      window.loaderIcon()
      axios.get(`/api/order/${this.$route.params.id}/${this.$route.params.hash}`).then(res => {
        let order = res.data.order
        this.$store.commit('setOrder', order)
        this.$store.commit('setTimetable', order.timetable)
        this.$store.commit('setEvent', order.timetable.show)
        this.$store.commit('emptyCart')
        this.$store.commit('setRefundable', order.is_refundable ? 1 : 0)
        order.order_items.forEach(item => {
          let sign = "+"
          let seatId = item.vendor_seat_id ? String(item.vendor_seat_id) : item.seat_id
          if(['showmarket'].includes(order.vendor)) seatId = item.seat_id // for all the vendors that do not have own scheme
          let ticket = {
            order_item_id:  item.id,
            ticket_id:      item.vendor_seat_id ? item.vendor_seat_id : item.ticket_id,
            seat_id:        seatId,
            section_id:     item.section_id,
            row:            item.row,
            seat:           item.seat,
            price:          item.original_price,
            pricegroup_id:  item.pricegroup_id,
            pricegroup_name:item.pricegroup ? item.pricegroup.title : {},
            section_name:   item.section ? item.section.title : {}
          }
          this.$store.commit('updateCart', { sign, ticket })
        })
        this.form.name = order.name
        this.form.email = order.email
        this.form.phone = order.phone
        this.form.comment = order.comment
        if(!order.name) {
          this.populateFromUser()
        }
        if(window && window.parent) {
          window.parent.postMessage(JSON.stringify({
            action: 'setOrder',
            data:   order
          }), '*');
        }
      }).catch((e) => {
        this.orderNotFound = true
      })
    },
    deleteOrderItem(id) {
      window.loaderIcon()
      axios.delete(`/api/order/${this.order.id}/${this.order.hash}/item/${id}`).then(() => {
        this.loadOrder()
      })
    },
    populateFromUser() {
      if(this.user) {
        this.form.name  = this.user.name
        this.form.email = this.user.email
        this.form.phone = this.user.phone
      }
    },
    async orderFill() {
      if(!this.emailConfirmed) {
        this.$store.commit('setConfirmingEmail', true)
        return
      }
      window.loaderIcon()
      if(!this.canHaveComment) this.form.comment = ''
      axios.post(`/api/order/${this.order.id}/${this.order.hash}/fill`, {
        ...this.form,
        timetable_id: this.timetable.id,
        is_refundable: this.refundable === null ? 1 : this.refundable,
        mobile: this.withoutIframe
      }).then(res => {
        this.$store.commit('setLoading', false)
        window.dataLayer.push({
          event: 'begin_checkout',
          login: !!this.user,
          client_id: this.user?.id || null,
          user_id: getGoogleCID(),
          value: Number(this.totalWithFeeAndRefundable),
          payment_type: this.form.pay_system,
          ecommerce: {
            event: 'begin_checkout',
            currency: 'KZT',
            items: this.getCartItemsForDataLayer()
          }
        })
        let d = res.data
        if(d && d.success && ['kaspi'].includes(this.form.pay_system) && this.withoutIframe) {
          this.$refs.kform.submit()
          return
        }
        if(d && d.success && ['kaspi'].includes(this.form.pay_system) && !this.withoutIframe && d.qr && d.qr.qrCodeImage) {
          this.$store.commit('setQRCodeImage', d.qr.qrCodeImage)
          window.parent.postMessage(JSON.stringify({
            action: 'setOrder',
            data: null
          }), '*');
          return
        }
        if(d && d.success && d.redirect) {
          if(window && window.parent) {
            if(['card'].includes(this.form.pay_system)) {
              window.parent.postMessage(JSON.stringify({
                action: 'addClass',
                data: 'pt-5'
              }), '*');
            }
            if(['invitation', 'invitation_hide', 'cash', 'kaspi'].includes(this.form.pay_system)) {
              window.parent.postMessage(JSON.stringify({
                action: 'setOrder',
                data: null
              }), '*');
            }
          }
          window.location = d.redirect
          return
        }
        if(d && d.error) {
          window.noty(d.error,'error')
          return
        }
        window.noty('Что-то пошло не так','error')
      }).catch(e => {
        this.$store.commit('setLoading', false)
      })
    },
    otpConfirmed() {
      this.emailConfirmed = true
      this.$store.commit('setConfirmingEmail', false)
      this.orderFill()
    },
    promocodeCheck() {
      window.loaderIcon()
      axios.post('/api/promocode', { timetable_id: this.timetable.id, promocode: this.form.promocode }).then(res => {
        this.$store.commit('setPromocodeDiscount', res.data)
        if(res.data === 0) {
          this.promocodeNotFound = true
        }
      }).catch((e) => {
        if(e.response.data) {
          window.noty(e.response.data,'error')
        }
        console.log('error', e.response.data)
      })
    },
    getCartItemsForDataLayer() {
      const items = []
      this.cart.forEach(item => {
        items.push({
          item_id: item.ticket_id,
          item_name: `Билет на ${(this.event?.title?.ru || '')}`,
          price: Number(item.price),
          quantity: 1,
          item_category: this.event?.category?.title?.ru || ''
        })
      })
      return items
    }
  },
  watch: {
    valid(val) {
      this.$store.commit('setCheckoutFormValid', val)
    },
    email(val) {
      this.emailConfirmed = true
      // this.emailConfirmed = this.user && this.user.email == val
    },
    promocode() {
      this.$store.commit('setPromocodeDiscount', 0)
      this.promocodeNotFound = false
    }
  },
  metaInfo() {
    return {
      title: this.trans('order_checkout'),
      meta: []
    }
  },
  mounted() {
    window.dataLayer.push({
      event: 'view_cart',
      login: !!this.user,
      client_id: this.user?.id || null,
      user_id: getGoogleCID(),
      ecommerce: {
        event: 'view_cart',
        currency: 'KZT',
        items: this.getCartItemsForDataLayer()
      }
    })
  }
}
</script>

<style scoped>
.checkout-form {
  padding-bottom: 160px;
}
.btn-check-promocode {
  background: transparent;
  border: 1px solid #4bb0fe;
  color: #4bb0fe;
  height: 40px;
  padding: 0 20px;
  position: absolute;
  right: 3px;
  top: 3px;
  z-index: 4;
}

@media(max-width: 768px) {
  .btn-check-promocode {
    padding: 0 15px;
    height: 37px;
  }
}
</style>
