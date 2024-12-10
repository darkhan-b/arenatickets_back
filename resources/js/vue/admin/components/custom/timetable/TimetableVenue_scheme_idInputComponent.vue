<template>
  <div class="col-12" v-if="loaded">
    <div class="form-group">
      <label class="required">Рассадка (площадка {{ object.venue.title.ru }})</label>
      <select :name="`venue_scheme_id`" class="form-control" v-model="object.venue_scheme_id">
        <option v-for="s in schemas" :key="`sc-option-${s.id}`" :value="s.id">[{{ s.id }}] {{ s.title.ru }}</option>
      </select>
    </div>
  </div>
</template>

<script>

export default {
  name: "TimetableVenue_scheme_idInputComponent",
  data() {
    return {
      schemas: [],
      loaded: false
    }
  },
  props: {
    object: {}
  },
  methods: {
  },
  async mounted() {
    let id = this.object.show_id
    if(id) {
      let res = await axios.get(`/admin/shows/${id}/afisha`)
      this.schemas = res.data.schemas
      this.loaded = true
    } else {
      this.loaded = true
    }
  }
}
</script>

<style scoped>

</style>
