<template>
  <div class="admincollapse w-100" :class="'collapse-'+tab">
    <div class="col-12" v-if="loaded">
      <div v-for="(timetable,rind) in timetables" :key="`line-${rind}`">
        <div class="row align-items-center">
          <div class="col-md-3 col-sm-4">
            <label>Дата</label>
            <datetime type="datetime"
                      format="yyyy-MM-dd HH:mm"
                      :value-zone="'UTC+6'"
                      :hour-step="1"
                      :minute-step="5"
                      :auto="true"
                      :phrases="{ok: 'Ок','cancel': 'Отмена'}"
                      v-model="timetable.date"
                      class="form-control"
                      :name="`timetables[${rind}][date]`"
                      :input-id="`timetables[${rind}][date]`">
            </datetime>
          </div>
          <div class="col-md-3 col-sm-4">
            <label>Зал</label>
            <select :name="`timetables[${rind}][venue_scheme_id]`" class="form-control" v-model="timetable.venue_scheme_id">
              <option v-for="s in schemas" :key="`sc-option-${s.id}`" :value="s.id">{{ s.title.ru }}</option>
            </select>
          </div>
          <input type="hidden"
                 :value="timetable.id"
                 :name="`timetables[${rind}][id]`"/>
          <div class="col-md-3">
<!--            <a class="pointer" @click="hideModal(timetable)">Билеты</a>-->
            <label :for="`active-${rind}`">Активно</label>
            <input type="checkbox"
                   v-model="timetable.active"
                   :name="`timetables[${rind}][active]`"
                   value="1"
                   :id="`active-${rind}`"/>
          </div>
          <div class="col-md-3">
            <div class="mt-3 text-end">
              <a @click="timetables.splice(rind, 1)" class="text-danger pointer">Удалить</a>
            </div>
          </div>
        </div>
        <hr/>
      </div>
      <div class="col-12" v-if="object.id && object.venue_id">
        <a class="btn btn-themed btn-outline-secondary"
           @click="addTimetable">
          Добавить дату
        </a>
      </div>
      <div v-else class="alert alert-warning">
        Чтобы добавить сеанс, вначале сохраните событие с выбранной площадкой проведения
      </div>
    </div>
  </div>
</template>

<script>

import moment from 'moment'

export default {
  name: "ShowAfishaTabComponent",
  props: {
    object: {},
    tab: {},
  },
  data() {
    return {
      loaded: false,
      timetables: [],
      schemas: [],
    }
  },
  async mounted() {
    let id = this.object.id
    if(id) {
      let res = await axios.get(`/admin/shows/${id}/afisha`)
      this.schemas = res.data.schemas
      res.data.timetables.forEach(ttb => {
        ttb.date = moment(ttb.date).toISOString()
        this.timetables.push(ttb)
      })
      this.loaded = true
    } else {
      this.loaded = true
    }
  },
  methods: {
    addTimetable() {
      this.timetables.push({
        id: null,
        date: null,
        active: 1
      })
    },
    hideModal(timetable) {
      $('#eloquent-modal').modal('hide')
      this.$router.push(`/timetable/${timetable.id}`)
    },
    // copy(obj) {
    //   return JSON.parse(JSON.stringify(obj))
    // },
  },
}
</script>

<style scoped>

</style>
