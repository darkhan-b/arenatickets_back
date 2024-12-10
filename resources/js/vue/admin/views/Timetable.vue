<template>
  <div>
    <div class="container-fluid" v-if="fade && ticketsCount">
      <h6>{{ timetable.show.title.ru }} - {{ venue.title.ru }}: {{ timetable.date | formatDate }}</h6>
      <hr/>
      <div v-if="Object.keys(ticketsCount).length === 0 && !selectedType">
        <div>Выберите тип билетов</div>
        <div class="mt-3">
          <button @click="setType('sections')" class="btn-outline-info btn" style="width: 250px">По секторам</button>
          <div class="text-muted mt-1">Пользователь видит схему площадки, выбирает сектор, внутри которого либо кол-во входных мест, либо конкретные сиденья</div>
        </div>
        <div class="mt-3">
          <button @click="setType('pricegroups')" class="btn-outline-primary btn" style="width: 250px">По ценовым группам</button>
          <div class="text-muted mt-1">Пользователь не видит площадки, а выбирает билеты из ценовой группы</div>
        </div>
      </div>
      <div v-else>
        <set-prices-sections v-if="timetable.type == 'sections'"
                             :venue="venue"
                             :scheme="scheme"
                             :ticketsCount="ticketsCount"
                             :closedSections="closedSections"
                             :timetable="timetable">
        </set-prices-sections>
        <set-prices-pricegroups v-if="timetable.type == 'pricegroups'"
                                :venue="venue"
                                :scheme="scheme"
                                :ticketsCount="ticketsCount"
                                :timetable="timetable">
        </set-prices-pricegroups>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  mounted() {
    this.loadTimetable()
    $('.tooltip').hide();
  },
  methods: {
    loadTimetable() {
      axios.get('/admin/timetable/'+this.$route.params.id).then(res => {
        this.fade = true
        this.timetable = res.data.timetable
        this.venue = res.data.venue
        this.scheme = res.data.scheme
        this.ticketsCount = Array.isArray(res.data.tickets) ? {} : res.data.tickets
        this.closedSections = res.data.closedSections
      })
    },
    setType(type) {
      window.loaderIcon()
      axios.post('/admin/timetable/'+this.$route.params.id+'/type', { type }).then(res => {
        this.selectedType = true
        this.timetable.type = type
      })
    }
  },

  computed: {
  },
  props: {
  },
  data() {
    return {
      timetable: null,
      venue: null,
      scheme: null,
      ticketsCount: null,
      selectedType: false,
      fade: false,
      closedSections: []
    }
  },
}
</script>

<style scoped>

</style>
