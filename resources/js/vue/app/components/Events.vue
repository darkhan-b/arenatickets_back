<template>
  <div>
    <div v-for="event in events">
      {{ transName(event.title) }}
      <div v-for="t in event.timetables" @click="timetable">
        {{ t.date | formatDate }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    this.loadEvents()
  },
  methods: {
    loadEvents() {
      axios.get('/api/events').then(res => {
        this.events = res.data.events
      })
    },
  },
  data() {
    return {
      events: []
    }
  }
}
</script>

<style scoped>

</style>
