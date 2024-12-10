<template>
  <div class="text-center mt-5">
    <div>
      Выберите количество билетов
    </div>
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
  name: "SelectTicketsAmount",
  props: {
    tickets: {
      type: Array
    },
    type: { // section or pricegroup
      required: true,
      type: String,
    },
    section: {
      type: Object
    },
    pricegroup: {
      type: Object
    }
  },
  computed: {
    ...mapState(['cart', 'ticketsLimit']),
    max() {
      return Math.min(this.tickets.length, this.ticketsLimit)
    },
    amount() {
      return this.cart.reduce((a, v) => (v[this.type+'_id'] === this[this.type].id ? a + 1 : a), 0)
    }
  },
  methods: {
    changeAmount(sign) {
      if(sign === '+' && this.amount >= this.max) return
      if(sign === '-' && this.amount <= 0) return
      let ticket = {
        price: this.tickets[0].price,
        [this.type+'_id']: this[this.type].id
      }
      if(this.type == 'section' && this.section) {
        ticket.section_name = this.section.title
      }
      this.$store.commit('updateCart', { sign, ticket })
    }
  }
}
</script>

<style scoped>

</style>
