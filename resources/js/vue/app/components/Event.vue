<template>
  <div>
    <div v-if="event"  class="text-center">
      <div class="mt-3 mb-2">
        {{ transName(event.title) }}
      </div>
      <div v-for="t in event.timetables">
        <router-link  :to="`/${event.id}/${t.id}`"  class="timetable-btn">
          {{ t.date  | formatDate }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  created() {
    this.loadEvent()
    this.$store.commit('setStep', 1)
  },
  methods: {
    loadEvent() {
      axios.get('/api/event/'+this.eid).then(res => {
        this.$store.commit('setEvent', res.data.event)
      })
    },
  },

  computed: {
    ...mapState(['event'])
  },
  data() {
    return {
      eid: this.$route.params.eventid,
    }
  },
  metaInfo() {
    return {
      title: this.event ? this.transName(this.event.title) : '',
      meta: []
    }
  }
}
</script>

<style scoped>

</style>
