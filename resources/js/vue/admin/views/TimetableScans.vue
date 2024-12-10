<template>
  <div class="container-fluid" v-if="scansData">
    <h6 v-if="show">{{ show.title.ru }} ({{ show.datePlaceString }})</h6>
    <hr/>
    <div v-if="scansData">
      <div>Билетов продано: <b>{{ scansData.ticketsSold }}</b></div>
      <div>Билетов прошло: <b>{{ scansData.ticketsScanned }}</b></div>
      <div>Сканирований сделано: <b>{{ scansData.scansMade }}</b></div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TimetableScans",
  data() {
    return {
      scansData: null,
    }
  },
  mounted() {
    this.loadScans()
  },
  computed: {
    show() {
      return this.timetable ? this.timetable.show : null 
    },
    timetable() {
      return this.scansData ? this.scansData.timetable : null
    }
  },
  methods: {
    loadScans() {
      axios.get(`/admin/timetable/${this.$route.params.id}/scans`).then(res => {
        this.scansData = res.data
      })
    }
  }
}
</script>

<style scoped>

</style>