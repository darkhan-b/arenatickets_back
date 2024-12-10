<template>
  <a class="btn" @click="toTimetables">
    <i class='ti-eye grey-color' data-toggle='tooltip' title='Сеансы'></i>
  </a>
</template>

<script>
export default {
  name: "ShowTDLinkComponent",
  props: {
    object: {
      type: Object
    }
  },
  methods: {
    toTimetables() {
      this.$router.push({ path: 'timetable', query: { show_id: this.object.id } })
    }
  }
}
</script>

<style scoped>

</style>
