<template>
  <div class="pricegroup-row">
    <div class="row align-items-center">
      <div class="col">
        <div class="pricegroup-title">
          {{ transName(pricegroup.pricegroup.title) }}
        </div>
        <div class="text-muted d-block d-md-none">
          {{ trans('left') }}: {{ pricegroup.cnt | formatNumber }}
        </div>
      </div>
      <div class="col-3 col-lg-2 d-md-block d-none">
        <div class="text-muted">
          {{ trans('left') }}: {{ pricegroup.cnt | formatNumber }}
        </div>
      </div>
      <div class="col-auto">
        <div class="pricegroup-price">
          {{ pricegroup.pricegroup.price | formatNumber }} ₸
        </div>
      </div>
      <div class="col-auto">
        <div class="d-flex align-items-center">
          <a class="plus-btn" :class="{ disabled: amount < 1 }" @click="changeAmount('-')">-</a>
          <span class="amount-span">{{ amount }}</span>
          <a class="minus-btn" :class="{ disabled: amount >= max }" @click="changeAmount('+')">+</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: "SelectPricegroupTicketsAmount",
  props: {
    pricegroup: {
      type: Object
    }
  },
  computed: {
    ...mapState(['cart', "ticketsLimit"]),
    max() {
      return Math.min(this.pricegroup.cnt, this.ticketsLimit)
    },
    amount() {
      return this.cart.reduce((a, v) => (v.pricegroup_id === this.pricegroup.pricegroup.id ? a + 1 : a), 0)
    }
  },
  methods: {
    changeAmount(sign) {
      if(sign === '+' && this.amount >= this.max) return
      if(sign === '-' && this.amount <= 0) return
      let ticket = {
        price: this.pricegroup.pricegroup.price,
        pricegroup_id: this.pricegroup.pricegroup.id
      }
      ticket.pricegroup_name = this.pricegroup.pricegroup.title
      this.$store.commit('updateCart', { sign, ticket })
    }
  }
}
</script>
