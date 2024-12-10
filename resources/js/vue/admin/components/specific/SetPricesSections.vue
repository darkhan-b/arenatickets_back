<template>
  <div class="row admin-scheme-view">

    <div :class="{'col-7': selectedSectionIndex === null, 'col-4': selectedSectionIndex !== null }">
      <Venue class="mb-3"
             :scheme="scheme"
             v-if="scheme"
             view="admin"
             :pan-enabled="false"
             :closedSections="closedSections"
             :selectedSectionIndex="selectedSectionIndex"
      />
      <div v-if="selectedSectionIndex !== null">
        <div class="mb-2">
          <div class="d-inline-block radio-wrapper">
            <label for="seats-r">По местам</label>
            <input type="radio" v-model="type" :disabled="!changeType" value="seats" id="seats-r" name="sectionType"/>
          </div>
          <div class="d-inline-block pl-2 radio-wrapper">
            <label for="enter-r">Входные</label>
            <input type="radio" v-model="type" :disabled="!changeType" value="enter" id="enter-r" name="sectionType"/>
          </div>
        </div>
        <div v-if="type == 'seats'">
          <div>Выделено: {{ selectedSeats.length }}</div>
          <a tabindex="#" class="text-underlined pointer" @click="selectedSeats = []">Снять выделение</a>
          <a tabindex="#" class="text-underlined pointer pl-2" @click="selectAllWithoutTickets">Места без билетов</a>
          <a tabindex="#" class="text-underlined pointer pl-2" @click="selectAllWithTickets">Все билеты</a>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-3">Цена</div>
          <div class="col-9 mb-2"><input type="number" v-model="price" class="form-control"/></div>
        </div>
        <div class="row align-items-center mt-3" v-if="type == 'enter'">
          <div class="col-3">Кол-во билетов</div>
          <div class="col-9 mb-2"><input type="number" v-model="amountOfEnterTickets" class="form-control"/></div>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-3">Комментарий</div>
          <div class="col-9 mb-2">
            <textarea v-model="comment" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-3">&nbsp;</div>
          <div class="col-9 mb-2">
            <div class="checkbox-wrapper">
              <input class="me-1" type="checkbox" :checked="isSectionClosed" id="closeSection" @input="closeSectionToggle"/>
              <label for="closeSection">{{ isSectionClosed ? "Открыть" : "Закрыть" }}</label>
            </div>
          </div>
        </div>
        <div class="text-right mt-5" v-if="(selectedSeats.length > 0 || type == 'enter')">
          <a tabindex="#"  v-if="type == 'seats' && selectedSeats.length > 0"
             class="btn btn-themed btn-outline btn-danger"
             @click="deleteSelected">
            <i class="ti-trash"></i> выделенные
          </a>

          <div class="dropdown d-inline-block align-middle ml-3" v-if="invitationsToBeCreated && price > 0">
            <a role="button"
               class="btn btn-info btn-themed btn-fill dropdown-toggle"
               id="dropdownMenuButton"
               @click="showPIndex = !showPIndex"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">
              Пригласительные ({{ invitationsToBeCreated }})
            </a>
            <div class="dropdown-menu" :class="{ show: showPIndex }" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item pointer" @click="saveTickets(true, true)">Прятать цену</a>
              <a class="dropdown-item pointer" @click="saveTickets(true, false)">Оставить цену</a>
            </div>
          </div>

          <a class="btn btn-info btn-themed btn-fill ml-2" @click="saveTickets(false)" v-if="price > 0">
            {{ type == 'seats' ? 'Добавить' : 'Установить'}}
          </a>
        </div>
      </div>
    </div>
    <div :class="{'col-5': selectedSectionIndex === null, 'col-8': selectedSectionIndex !== null }">
      <section-scheme v-if="!loading && type == 'seats'"
                      :seats="seats"
                      :prices="prices"
                      view="admin"
                      :section-id="group_id"
                      :selectedSeats="selectedSeats"
                      :mouse-selectable="true"
                      @rowPressed="rowPressed"
                      @seatPressed="seatPressed"
      />
      <div v-if="type == 'enter'">
        <div v-for="t in tickets" class="ticket-icon">
          <i class="ti-ticket"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Venue from '../../../app/components/Venue'
import SectionScheme from "../../../app/components/SectionScheme"

export default {
  components: {
    SectionScheme,
    Venue
  },
  name: "SetPricesSections",
  props: {
    ticketsCount: {
      type: Object
    },
    timetable: {
      type: Object
    },
    venue: {
      type: Object
    },
    scheme: {
      type: Object
    },
    closedSections: {
      type: Array
    }
  },
  data() {
    return {
      loading: true,
      initialLoaded: false,
      type: 'seats',
      changeType: true,
      prices: {},
      fade: false,
      seats: [],
      tickets: [],
      selectedSeats: [],
      amountOfEnterTickets: 0,
      comment: '',
      price: 0,
      selectedSectionIndex: null,
      showPIndex: false
    }
  },
  watch:{
    selectedSectionIndex(val) {
      this.selectedSeats = []
      this.loadSection()
    },
    type() {
      this.selectedSeats = []
      this.showPIndex = false
    }
  },
  methods: {
    loadSection() {
      this.loading = true
      this.initialLoaded = false
      axios.get('/admin/timetable/'+this.timetable.id+'/group/'+this.group_id).then(res => {
        this.processData(res)
      })
    },
    saveTickets(invitationOrder = false, hidePrice = false) {
      if(!this.group_id) return
      if(invitationOrder) {
        let res = confirm(`Будет создано ${this.invitationsToBeCreated} билетов, которые будут оформлены как пригласительные, продолжить?`)
        if(!res) return
      }
      this.loading = true
      window.loaderIcon()
      axios.post('/admin/timetable/'+this.timetable.id+'/group/'+this.group_id+'/tickets', {
        type: this.type,
        amount: this.amountOfEnterTickets,
        price: this.price,
        seats: this.selectedSeats,
        comment: this.comment,
        invitationOrder,
        hidePrice
      }).then(res => {
        if(res && invitationOrder) {
          window.location = res.data.ticketsLink
          return
        }
        this.processData(res)
        window.noty()
      })
    },
    deleteSelected() {
      this.loading = true
      window.loaderIcon()
      axios.post('/admin/timetable/'+this.timetable.id+'/group/'+this.group_id+'/tickets/delete', {
        seats: this.selectedSeats
      }).then(res => {
        this.processData(res)
        window.noty()
      })
    },
    processData(data) {
      // console.log(data.data)
      this.selectedSeats = []
      this.seats = data.data.seats
      this.tickets = data.data.tickets
      this.amountOfEnterTickets = 0
      this.price = 0
      if(this.tickets.length > 0) {
        this.changeType = false
        let ticket = this.tickets[0]
        this.type = ticket.seat_id ? 'seats' : 'enter'
        if(this.type == 'enter') {
          this.amountOfEnterTickets = this.tickets.length
          this.price = ticket.price
        }
      } else {
        this.changeType = true
      }
      this.prices = data.data.prices
      this.loading = false
      this.initialLoaded = true
    },
    rowPressed(row) {
      this.seats.forEach((item) => {
        if(item.row == row) {
          this.seatPressed(item)
        }
      })
    },
    seatPressed(seat) {
      const exists = this.selectedSeats.includes(seat.id)
      if (exists) {
        this.selectedSeats = this.selectedSeats.filter((c) => { return c !== seat.id })
      } else {
        this.selectedSeats.push(seat.id)
      }
    },
    selectAllWithoutTickets() {
      this.selectedSeats = []
      this.seats.forEach((item) => {
        if(!item.ticket) {
          this.seatPressed(item)
        }
      })

    },
    selectAllWithTickets() {
      this.selectedSeats = []
      this.seats.forEach((item) => {
        if(item.ticket) {
          this.seatPressed(item)
        }
      })
    },
    closeSectionToggle(e) {
      if(!this.group_id) return
      axios.post(`/admin/timetable/${this.timetable.id}/section/${this.group_id}/toggle/${(e.target.checked ? 1 : 0)}`).then(res => {
        console.log(res.data)
        this.$parent.closedSections = res.data
      })
    }
  },
  computed: {
    group_id() {
      return this.selectedSectionIndex != null ? this.scheme.sections[this.selectedSectionIndex].id : null
    },
    withoutTicketAmongSelectedSeats() {
      return this.selectedSeats.filter(x => {
        let seat = this.seats.find(s => s.id === x)
        return seat && !seat.ticket
      })
    },
    invitationsToBeCreated() {
      return this.type === 'seats' ? this.withoutTicketAmongSelectedSeats.length : this.amountOfEnterTickets
    },
    isSectionClosed() {
      if(!this.group_id) return false
      return !!this.closedSections.find(x => x.section_id === this.group_id)
    }
  },
}
</script>

<style scoped lang="scss">
.ticket-icon {
  display: inline-block;
  border: 1px solid #eee;
  padding: 5px;
  margin: 4px;
  background: #fff;
  border-radius: 4px;
  font-size: 19px;
}
</style>
