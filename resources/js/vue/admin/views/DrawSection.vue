<template>
  <div>
    <div class="container-fluid" v-if="fade">
      <div class="row">
        <div class="col-6">
          <h6>
            Сектор {{ section.title.ru }}
            <span v-if="section.scheme && section.scheme.venue">
                            ({{ section.scheme.venue.title.ru }}, {{ section.scheme.title.ru }})
                        </span>
          </h6>
          <section-scheme :seats="section.seats"
                          :selectedSeats="selectedSeats"
                          :mouse-selectable="true"
                          :section-id="section.id"
                          view="admin"
                          @rowPressed="rowPressed"
                          @maxRow="maxRow"
                          @seatPressed="seatPressed"/>
          <div class="mt-1">
            Итого мест: {{ section.seats.length }}
            <span v-if="selectedSeats.length" class="ml-3">Выделено мест: {{ selectedSeats.length }}</span>
          </div>
        </div>
        <div class="col-6">
          <div class="text-right">
            <router-link :to="'/scheme/'+section.venue_scheme_id"
                         class="btn btn-outline btn-themed btn-secondary">
              К площадке
            </router-link>
            <button :disabled="loading"
                    @click="save"
                    class="btn btn-info btn-themed btn-fill ml-2">
              Сохранить
            </button>
          </div>

          <div class="row align-items-center mt-3">
            <div class="col-2 mb-2">Мест в ряду</div>
            <div class="col-4 mb-2">
              <input type="number" v-model="seatsInRow" class="form-control"/>
            </div>
            <div class="col-2 mb-2">Отступ слева</div>
            <div class="col-4 mb-2">
              <input type="number" v-model="standardOffset" class="form-control"/>
            </div>
          </div>
          <div class="row align-items-center">
            <div class="col-2 mb-2">След ряд</div>
            <div class="col-4 mb-2">
              <input type="text" v-model="nextRow" class="form-control"/>
            </div>
            <div class="col-2 mb-2">След место</div>
            <div class="col-4 mb-2">
              <input type="number" v-model="nextSeat" class="form-control"/>
            </div>
          </div>
          <div>
            <div class="mb-2 mt-3">
              <button :disabled="loading"
                      v-if="selectedSeats.length > 0"
                      class="btn btn-themed btn-outline btn-danger"
                      @click="deleteSelected">Удалить выделенное</button>
              <button :disabled="loading"
                      v-if="selectedSeats.length > 0"
                      class="btn btn-outline btn-themed btn-secondary"
                      @click="removeSelection">Снять выделение</button>
            </div>
            <button :disabled="loading"
                    @click="addRow"
                    class="btn btn-outline btn-themed btn-secondary">
              + ряд
            </button>

            <button :disabled="loading"
                    @click="addSeat"
                    class="btn btn-outline btn-themed btn-secondary ml-2">
              + место
            </button>

            <button  v-if="selectedSeats.length > 0"
                     :disabled="loading"
                    @click="changeSeatDirection"
                    class="btn btn-outline btn-themed btn-secondary ml-2">
              < -- >
            </button>

            <a v-if="selectedSeats.length > 0"
               @click="move('left')"
               class="btn btn-outline btn-themed btn-secondary">
              <i class="ti-arrow-circle-left"></i>
            </a>
            <a v-if="selectedSeats.length > 0"
               @click="move('right')"
               class="btn btn-outline btn-themed btn-secondary">
              <i class="ti-arrow-circle-right"></i>
            </a>
            <a v-if="selectedSeats.length > 0"
               @click="move('up')"
               class="btn btn-outline btn-themed btn-secondary">
              <i class="ti-arrow-circle-up"></i>
            </a>
            <a v-if="selectedSeats.length > 0"
               @click="move('down')"
               class="btn btn-outline btn-themed btn-secondary">
              <i class="ti-arrow-circle-down"></i>
            </a>
            <div class="d-inline-block ms-3" v-if="selectedSeats.length > 0">
              <div class="d-inline-block">
                Отступ движения
              </div>
              <div class="d-inline-block ms-2">
                <input type="number" v-model="moveValue" class="form-control"/>
              </div>
            </div>
            <div v-if="lastSeat">
              <div class="mt-4 mb-2">Последнее место:</div>
              <div class="row align-items-center">
                <div class="col-2 mb-2">Ряд</div>
                <div class="col-4 mb-2">
                  <input type="text" v-model="lastSeat.row" class="form-control"/>
                </div>
                <div class="col-2 mb-2">Место</div>
                <div class="col-4 mb-2">
                  <input type="number" v-model="lastSeat.seat" class="form-control"/>
                </div>
                <div class="col-2 mb-2">X</div>
                <div class="col-4 mb-2">
                  <input type="number" v-model="lastSeat.x" class="form-control"/>
                </div>
                <div class="col-2 mb-2">Y</div>
                <div class="col-4 mb-2">
                  <input type="number" v-model="lastSeat.y" class="form-control"/>
                </div>
              </div>
            </div>
            <div v-if="selectedSeats.length > 0">

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import SectionScheme from "../../app/components/SectionScheme"

export default {
  components: {
    SectionScheme
  },
  data() {
    return {
      lastSeat: null,
      section: null,
      fade: false,
      loading: false,
      rows: [],
      nextRow: 1,
      bottomRowIndex: 1,
      nextSeat: 1,
      standardOffset: 30,
      seatsInRow: 10,
      moveValue: 10,
      widthOfSeat: 20,
      marginBetweenSeats: 10,
      selectedSeats: [],
      fixRow: false
    }
  },
  mounted() {
    this.loadSeats()
  },
  methods: {
    loadSeats() {
      axios.get('/admin/section/'+this.$route.params.id).then(res => {
        this.fade = true
        this.section = res.data
      })
    },
    save() {
      this.loading = true
      window.loaderIcon()
      axios.post('/admin/section/'+this.$route.params.id, this.section).then(res => {
        this.section = res.data
        this.lastSeat = null
        this.selectedSeats = []
        this.loading = false
        window.noty()
      })
    },
    addRow() {
      this.widthOfSeat = Number(this.widthOfSeat)
      this.marginBetweenSeats = Number(this.marginBetweenSeats)
      for(var i = Number(this.nextSeat); i < (Number(this.seatsInRow) + Number(this.nextSeat)); i++) {
        this.section.seats.push({
          row: this.nextRow,
          seat: i,
          x: Number(this.standardOffset) + (i * (this.widthOfSeat + this.marginBetweenSeats)),
          y: Number(isNaN(this.nextRow) ? this.bottomRowIndex : this.nextRow) * (this.widthOfSeat + this.marginBetweenSeats)
        })
      }
      this.save()
    },
    addSeat() {
      this.section.seats.push({
        row: this.nextRow,
        seat: this.nextSeat,
        x: Number(this.standardOffset) + (this.nextSeat * (this.widthOfSeat + this.marginBetweenSeats)),
        y: Number(isNaN(this.nextRow) ? this.bottomRowIndex : this.nextRow) * (this.widthOfSeat + this.marginBetweenSeats)
      })
      this.nextSeat++
      this.fixRow = true
      this.$nextTick(() => {
        this.fixRow = true
        this.save()
      })
    },
    seatPressed(seat) {
      if(!seat.id) { return }
      const exists = this.selectedSeats.includes(seat.id)
      if (exists) {
        this.selectedSeats = this.selectedSeats.filter((c) => { return c !== seat.id })
        this.lastSeat = null
      } else {
        this.selectedSeats.push(seat.id)
        this.lastSeat = seat
      }
    },
    rowPressed(row) {
      this.section.seats.forEach((item) => {
        if(item.row == row) {
          this.seatPressed(item)
        }
      })
    },
    deleteSelected() {
      this.loading = true
      window.loaderIcon()
      axios.post('/admin/section/'+this.$route.params.id+'/deleteSeats', { seats: this.selectedSeats }).then(res => {
        this.section = res.data
        this.selectedSeats = []
        this.loading = false
        window.noty()
      })
    },
    maxRow(row) {
      this.bottomRowIndex = row ? (row + 1) : 1
      if(this.fixRow) {
        this.fixRow = false
        return
      }
      this.nextRow = row ? (row + 1) : 1
    },
    move(direction) {
      this.moveValue = parseInt(this.moveValue)
      this.section.seats.forEach((item) => {
        if(this.selectedSeats.includes(item.id)) {
          switch(direction) {
            case 'left':
              item.x = item.x - this.moveValue;
              break;
            case 'right':
              item.x = item.x + this.moveValue;
              break;
            case 'up':
              item.y = item.y - this.moveValue;
              break;
            case 'down':
              item.y = item.y + this.moveValue;
              break;
          }
        }
      })
    },
    removeSelection() {
      this.selectedSeats = []
      this.lastSeat = null
    },
    changeSeatDirection() {
      const rowsToChange = []
      this.section.seats.forEach(seat => {
        if(this.selectedSeats.includes(seat.id) && !rowsToChange.includes(seat.row)) {
          rowsToChange.push(seat.row)
          let rowSeats = this.section.seats.filter(s => s.row === seat.row).sort((a, b) => (Number(a.seat) > Number(b.seat)) ? 1 : -1)
          let mirrored = JSON.parse(JSON.stringify(rowSeats)).sort((a, b) => (Number(a.seat) < Number(b.seat)) ? 1 : -1)
          rowSeats.forEach((rs, ind) => {
            rs.x = mirrored[ind].x
          }) 
        }
      })
    }
  },
  watch:{
    $route (to, from) {
    },
    selectedSeats(val) {
    }
  },
}
</script>
