<template>
  <div class="text-center mt-5">
    <div>
      {{ trans('choose_tickets_in_sector') }} <b>{{ transName(section.title) }}</b>
    </div>
    <div>{{ trans('price') }}: <b>{{ tickets[0].price | formatNumber }} ₸</b></div>
    <div class="text-center mt-3">
      <a class="plus-btn" :class="{ disabled: amount < 1 }" @click="changeAmount('-')">-</a>
      <span class="amount-span">{{ amount }}</span>
      <a class="minus-btn" :class="{ disabled: amount >= max }" @click="changeAmount('+')">+</a>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: "SelectEnterSectionTicketsAmount",
  props: {
    tickets: {
      type: Array
    },
    section: {
      type: Object
    },
  },
  computed: {
    ...mapState(['cart', 'ticketsLimit']),
    max() {
      return Math.min(this.tickets.length, this.ticketsLimit)
    },
    amount() {
      return this.cart.reduce((a, v) => (v.section_id === this.section.id ? a + 1 : a), 0)
    }
  },
  methods: {
    changeAmount(sign) {
      if(sign === '+' && this.amount >= this.max) return
      if(sign === '-' && this.amount <= 0) return
      let ticket = {
        price: this.tickets[0].price,
        section_id: this.section.id
      }
      if(this.tickets[0].seat_id) ticket.seat_id = this.tickets[0].seat_id
      ticket.section_name = this.section.title
      this.$store.commit('updateCart', { sign, ticket })
    }
  }
}
</script>
