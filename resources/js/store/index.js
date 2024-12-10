import Vue from 'vue'
import Vuex from 'vuex'
import { getGoogleCID } from "../helpers"

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    venue: null,
    scheme: null,
    event: null,
    timetable: null,
    order: null,
    seats: [],
    tickets: [],
    pricegroups: {},
    prices: {},
    step: 1,
    checkouttime: 0,
    cart: [],
    user: null,
    settings: null,
    checkoutFormValid: false,
    schemeView: 'plan', // plan or list
    showRights: [],
    ticketsLimit: 5,
    config: {},
    testMode: false,
    selectedSectionIndex: null,
    confirmingEmail: false,
    loading: false,
    withoutIframe: false,
    source: null,
    showMobileDetails: false,
    promocodeDiscount: 0,
    refundable: null,
    qrCodeImage: null
  },
  getters:{
    getCartSum(state, getters) {
      return state.cart.reduce((a, o) => (a = a + (o.price * getters.discountedCoefficient), a), 0)
    },
    hasKassirRights(state) {
      return state.user && state.user.permissionsList && state.user.permissionsList.includes('kassa')
    },
    isOrganizerForShow(state) {
      return state.showRights && state.showRights.includes('organizer')
    },
    discountRate(state) {
      if(!state.timetable) return 0
      return (Math.round(state.timetable.discount) / 100)
    },
    discountedCoefficient(state, getters) {
      return (1 - getters.discountRate)
    },
    refundableShouldBeChosen(state) {
      return state.event?.refundable_fee > 0
    },
    refundableValid(state, getters) {
      const shouldBeChosen = getters.refundableShouldBeChosen
      if(!shouldBeChosen) return true
      return state.refundable !== null
    },
    serviceFee(state, getters) {
      if(!state.event || state.event.external_fee_value <= 0) return 0
      if(state.event.external_fee_type === 'absolute') {
        return Math.round(state.event.external_fee_value * state.cart.length)
      }
      if(state.event.external_fee_type === 'percent') {
        return Math.round(state.event.external_fee_value * getters.getCartSum) / 100
      }
      return 0
    },
    totalWithFee(state, getters) {
      return getters.getCartSum + getters.serviceFee
    },
    refundableFee(state, getters) {
      if(!getters.refundableShouldBeChosen) return 0;
      if(state.refundable !== 1) return 0;
      return Math.round((state.event.refundable_fee * getters.totalWithFee) / 100)
    },
    totalWithFeeAndRefundable(state, getters) {
      return getters.totalWithFee + getters.refundableFee
    }
  },
  mutations: {
    setVenue(state, venue) {
      state.venue = venue
    },
    setScheme(state, scheme) {
      state.scheme = scheme
    },
    setEvent(state, event) {
      state.event = event
    },
    setTimetable(state, timetable) {
      state.timetable = timetable
    },
    setSeats(state, seats) {
      state.seats = seats
    },
    setPrices(state, prices) {
      state.prices = prices
    },
    setTickets(state, tickets) {
      state.tickets = tickets
    },
    setPricegroups(state, pricegroups) {
      state.pricegroups = pricegroups
    },
    setOrder(state, order) {
      state.order = order
    },
    setUser(state, user) {
      state.user = user
    },
    setConfig(state, data) {
      state.config = data
    },
    setSource(state, data) {
      state.source = data
    },
    setShowRights(state, value) {
      state.showRights = value
      if(value && value.includes('organizer')) {
        state.ticketsLimit = 50
      }
    },
    setSettings(state, settings) {
      state.settings = settings
    },
    setCheckoutTime(state, time) {
      state.checkouttime = time
    },
    setLoading(state, value) {
      state.loading = value
    },
    setTestMode(state, value) {
      state.testMode = value
    },
    setStep(state, step) {
      state.step = step
    },
    setCheckoutFormValid(state, value) {
      state.checkoutFormValid = value
    },
    setSchemeView(state, value) {
      state.schemeView = value
    },
    setSelectedSectionIndex(state, value) {
      state.selectedSectionIndex = value
    },
    setConfirmingEmail(state, value) {
      state.confirmingEmail = value
    },
    setWithoutIframe(state, value) {
      state.withoutIframe = value
    },
    setShowMobileDetails(state, value) {
      state.showMobileDetails = value
    },
    setPromocodeDiscount(state, value) {
      state.promocodeDiscount = value
    },
    setQRCodeImage(state, value) {
      state.qrCodeImage = value
    },
    toggleShowMobileDetails(state) {
      state.showMobileDetails = !state.showMobileDetails
    },
    updateCart(state, { sign, ticket }) {
      if(sign === '+' && state.cart.length < state.ticketsLimit) {
        state.cart.push(ticket)
        window.dataLayer.push({
          event: 'add_to_cart',
          login: !!state.user,
          client_id: state.user?.id || null,
          user_id: getGoogleCID(),
          ecommerce: {
            event: 'add_to_cart',
            currency: 'KZT',
            items: [
              {
                item_id: ticket.ticket_id,
                item_name: `Билет на ${(state.event?.title?.ru || '')}`,
                price: ticket.price,
                quantity: 1,
              }
            ]
          }
        })
      }
      if(sign === '-') {
        const conditions = ['seat_id','section_id','pricegroup_id']
        let removed = false
        state.cart.forEach((item, index) => {
          if(!removed) {
            let found_prop = false
            conditions.forEach(cond => {
              if(ticket[cond] && !removed && !found_prop) {
                found_prop = true
                if(item[cond] == ticket[cond]) {
                  removed = true
                  state.cart.splice(index, 1)
                }
              }
            })
          }
        })
      }
      // console.log(state.cart)
    },
    emptyCart(state) {
      state.cart = []
      state.refundable = null
    },
    setRefundable(state, value) {
      state.refundable = value
    }
  },
  actions: {
    generateOrder({commit, state}) {
      window.loaderIcon()
      return new Promise((resolve, reject) => {
        axios.post('/api/order/init', {
          cart: state.cart,
          cid: getGoogleCID(),
          source: state.source,
          timetable_id: state.timetable.id,
          is_refundable: state.refundable === null ? 1 : state.refundable
        }).then(res => {
          window.loaderIcon(false)
          if(res.data.error) {
            window.noty(res.data.error, 'error')
            resolve(res.data.order)
            return
          }
          commit('setOrder', res.data.order)
          resolve(res.data.order)
        }).catch((err) => {
          window.loaderIcon(false)
          reject(err)
        })
      })
    },
    getSettings({commit, state}, { showId }) {
      window.loaderIcon()
      return new Promise((resolve, reject) => {
        axios.get(`/api/widget/settings?show_id=${showId}`).then(res => {
          commit('setSettings', res.data.data.settings)
          commit('setUser', res.data.data.user)
          commit('setConfig', res.data.data.config)
          commit('setShowRights', res.data.data.showRights)
          resolve(res.data.data)
        }).catch((err) => {
          reject(err)
        })
      })
    }
  },
  modules: {
  }
})
