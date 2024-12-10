<template>
  <div class="position-relative">
    <Venue class="mb-3"
           :scheme="scheme"
           view="user"
           :pan-enabled="false"
           v-if="selectedSectionIndex === null && scheme"
           :selectedSectionIndex="selectedSectionIndex"/>
    <div v-if="selectedSectionIndex !== null">
      <section-scheme
          v-if="type == 'seats'"
          :seats="seats"
          :prices="prices"
          view="user"
          :section-id="section ? section.id : null"
          :selectedSeats="selectedSeats"
          :mouse-selectable="false"
          @seatPressed="seatPressed"
      />
      <select-enter-section-tickets-amount v-if="type == 'enter'"
                                           :tickets="tickets"
                                           :section="scheme.sections[selectedSectionIndex]"/>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import SchemeViewSwitcher from "./SchemeViewSwitcher"

export default {
  name: "SelectTicketsSections",
  components: {
    SchemeViewSwitcher
  },
  watch: {
    selectedSectionIndex(val) {
      if(val !== null) {
        this.loadSection()
        this.$store.commit('setStep', 2)
      } else {
        this.$store.commit('setStep', 1)
      }
      this.$store.commit('setSelectedSectionIndex', val)
    },
    step(val) {
      if(val == 1) {
        this.$store.commit('setSeats', [])
        this.$store.commit('setTickets', [])
        this.$store.commit('setPrices', {})
        this.type = null
        this.selectedSectionIndex = null
      }
    }
  },
  computed: {
    ...mapState([
      'venue',
      'scheme',
      'timetable',
      'seats',
      'prices',
      'tickets',
      'cart',
      'step',
      'pricegroups',
      'schemeView',
      'testMode'
    ]),
    stateSelectedSectionIndex() {
        return this.$store.state.selectedSectionIndex
    },
    section() {
      return this.selectedSectionIndex !== null ? this.scheme.sections[this.selectedSectionIndex] : null
    },
    selectedSeats() {
      return this.cart.reduce((a, o) => (o.seat_id && a.push(o.seat_id), a), [])
    }
  },
  methods: {
    loadSection() {
      if(!this.section) return
      window.loaderIcon()
      axios.get('/api/timetable/'+this.timetable.id+'/section/'+this.section.id).then(res => {
        let data = res.data
        this.$store.commit('setSeats', data.seats)
        this.$store.commit('setTickets', data.tickets)
        this.$store.commit('setPrices', data.prices)
        this.type = data.type
        // if(data.tickets.length > 0) {
          // let ticket = data.tickets[0]
          // this.type = ticket.seat_id ? 'seats' : 'enter'
        // }
        // for apis that load all seats at once
        // if(this.timetable.api && ['lermontov'].includes(this.timetable.api)) {
        //   this.type = 'seats'
        //   this.populateTicketsFromSectionLoad()
        // }
      })
    },
    populateTicketsFromSectionLoad() {
      let data = this.pricegroups[this.section.id]
      if(!data) return
      let newseats = []
      this.seats.forEach(item => {
        let ticket = data.seats[this.section.id+'_'+item.row+'_'+item.seat]
        if(ticket) {
          item.ticket = ticket
        }
        newseats.push(item)
      })
      this.$store.commit('setPrices', data.prices)
      this.$store.commit('setSeats', newseats)
    },
    seatPressed(seat) {
      console.log(seat)
      if(!seat.ticket || seat.ticket.blocked || seat.ticket.sold) {
        return
      }
      let sign = this.selectedSeats.includes(seat.id) ? '-' : '+'
      let ticket = {
        price: seat.ticket.price,
        seat_id: seat.id,
        section_id: this.section.id,
        section_name: this.section.title,
        row: seat.row,
        seat: seat.seat,
        ticket_id: seat.ticket.id
      }
      this.$store.commit('updateCart', { sign, ticket })
    },
  },
  mounted() {
    if(this.stateSelectedSectionIndex !== null) {
        this.selectedSectionIndex = this.stateSelectedSectionIndex
    }
  },
  data() {
    return {
      selectedSectionIndex: null,
      type: null,
    }
  }
}
</script>