<template>
  <div v-if="timetable">
    <template v-if="timetableAvailableForSale">
      <div v-if="timetable.type == 'sections'">
        <select-tickets-sections/>
      </div>
      <div v-if="timetable.type == 'pricegroups'">
        <select-tickets-pricegroups/>
      </div>
    </template>
    <template v-if="timetable.sale_starts_soon">
      <div class="alert alert-warning">{{ trans('sale_starts_soon') }}</div>
    </template>
    <template v-if="timetable.temporary_blocked">
      <div class="alert alert-warning">{{ trans('sales_temporary_blocked') }}</div>
    </template>
    <template v-if="timetable.salesFinished">
      <div class="alert alert-warning">{{ trans('sales_finished') }}</div>
    </template>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  created() {
    const source = this.$route.query.source
    if(source) this.$store.commit('setSource', source)
    this.loadTimetable()
  },
  methods: {
    loadTimetable() {
      if(!this.tid) {
        return
      }
      window.loaderIcon()
      axios.get('/api/timetable/'+this.tid).then(res => {
        this.$store.commit('setVenue', res.data.venue)
        this.$store.commit('setScheme', res.data.scheme)
        this.$store.commit('setTimetable', res.data.timetable)
        this.$store.commit('setPricegroups', res.data.tickets)
        this.$store.commit('setEvent', res.data.timetable.show)
        // this.$store.commit('setStep', 1)
        // this.$store.commit('setSelectedSectionIndex', null)
        if(window && window.parent) {
          window.parent.postMessage(JSON.stringify({
            action: 'setOrder',
            data: null
          }), '*');
        }
      })
    },
  },
  computed: {
    ...mapState(['timetable','event','pricegroups']),
    timetableAvailableForSale() {
      return !this.timetable.sale_starts_soon 
          && !this.timetable.temporary_blocked
          && !this.timetable.salesFinished
          && this.timetable.active
    }
  },
  data() {
    return {
      tid: this.$route.params.timetableid,
      fade: false,
      selectedSectionIndex: null
    }
  },
  metaInfo() {
    return {
      title: (this.event && this.timetable) ? (this.transName(this.event.title) + ' - '+this.timetable.date) : '',
      meta: []
    }
  }
}
</script>

<style scoped>

</style>
