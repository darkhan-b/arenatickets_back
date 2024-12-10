<template>
  <div>
    <div class="container-fluid" v-if="fade">
      <div class="row align-items-center">
        <div class="col-6">
          <h6 class="mb-0">Сформировать отчет</h6>
        </div>
      </div>
      <hr>
      <div style="max-width: 600px;">
        <div class="container mr-auto ml-0 px-0">
          <div class="row">
            <div class="col-6" :class="{ 'd-none': report_type === 'unsold' }">
              <div class="form-group">
                <label for="date_from">Дата от</label>
                <datetime type="date"
                          format="yyyy-MM-dd"
                          :value-zone="'UTC+6'"
                          :auto="true"
                          :phrases="{ok: 'Ок','cancel': 'Отмена'}"
                          v-model="date_from"
                          class="form-control"
                          id="date_from"
                          name="date_from">
                </datetime>
              </div>
            </div>
            <div class="col-6" :class="{ 'd-none': report_type === 'unsold' }">
              <div class="form-group">
                <label for="date_to">Дата до</label>
                <datetime type="date"
                          format="yyyy-MM-dd"
                          :value-zone="'UTC+6'"
                          :auto="true"
                          :phrases="{ok: 'Ок','cancel': 'Отмена'}"
                          v-model="date_to"
                          class="form-control"
                          id="date_to"
                          name="date_to">
                </datetime>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label for="timetable_ids">События</label>
                <select class="form-control autocomplete-select"
                        data-model="show"
                        data-field="title"
                        :data-selected="'[]'"
                        id="show_ids"
                        name="show_ids[]"
                        multiple>
                  <option value="">-</option>
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label for="timetable_ids">Сеансы</label>
                <select class="form-control autocomplete-select"
                        data-model="timetable"
                        data-field="date"
                        :data-selected="'[]'"
                        id="timetable_ids"
                        name="timetable_ids[]"
                        multiple>
                  <option value="">-</option>
                </select>
              </div>
            </div>
            <div class="col-12" :class="{ 'd-none': report_type === 'unsold' }">
              <div class="form-group">
                <label for="venue_ids">Площадки</label>
                <select class="form-control autocomplete-select"
                        data-model="venue"
                        data-field="title"
                        :data-selected="'[]'"
                        id="venue_ids"
                        name="venue_ids[]"
                        multiple>
                  <option value="">-</option>
                </select>
              </div>
            </div>
            <div class="col-12" :class="{ 'd-none': report_type === 'unsold' }">
              <div class="form-group">
                <label for="category_ids">Категории</label>
                <select class="form-control autocomplete-select"
                        data-model="category"
                        data-field="title"
                        :data-selected="'[]'"
                        id="category_ids"
                        name="category_ids[]"
                        multiple>
                  <option value="">-</option>
                </select>
              </div>
            </div>
            <div class="col-12" :class="{ 'd-none': report_type === 'unsold' }">
              <div class="form-group">
                <label for="organizer_ids">Организаторы</label>
                <select class="form-control autocomplete-select"
                        data-model="organizer"
                        data-field="name"
                        :data-selected="'[]'"
                        id="organizer_ids"
                        name="organizer_ids[]"
                        multiple>
                  <option value="">-</option>
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <div class="row">
                  <div class="col-auto">
                    <input type="radio" id="sales" v-model="report_type" name="report_type" value="sales">
                    <label for="sales" class="d-inline-block">Продажи</label>
                  </div>
                  <div class="col-auto">
                    <input type="radio" id="refunds" v-model="report_type" name="report_type" value="refunds">
                    <label for="refunds" class="d-inline-block">Возвраты</label>
                  </div>
                  <div class="col-auto">
                    <input type="radio" id="unsold" v-model="report_type" name="report_type" value="unsold">
                    <label for="unsold" class="d-inline-block">Непроданные</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="error" class="alert alert-danger">{{ error }}</div>
          <div class="mt-4">
            <button class="btn btn-themed btn-info" @click="formExcel">Выгрузить</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>


export default {
  mounted() {
    window.loaderIcon(false)
    setTimeout(() => {
      window.launchAutocomplete()
    },100)

  },
  methods: {
    formExcel() {
      this.error = null
      if(this.report_type === 'unsold' && !$('#timetable_ids').val().length && !$('#show_ids').val().length) {
        this.error = 'Для отчета по непроданным билетам необходимо выбрать сеанс или событие'
        return
      }
      var show_ids = this.formUrlIds('show_ids')
      var timetable_ids = this.formUrlIds('timetable_ids')
      var venue_ids = this.formUrlIds('venue_ids')
      var category_ids = this.formUrlIds('category_ids')
      var organizer_ids = this.formUrlIds('organizer_ids')
      let href = '/admin/report/sales/excel?date_from='+this.date_from+'&date_to='+this.date_to+'&report_type='+this.report_type+show_ids+timetable_ids+venue_ids+category_ids+organizer_ids
      location.href = href
    },
    formUrlIds(name) {
      let ids = $('#'+name).val()
      let ids_param = ''
      if(ids) {
        ids.forEach(item => {
          ids_param += '&'+name+'[]='+item
        })
      }
      return ids_param;
    }
  },
  watch:{
    $route (to, from) {

    },

  },
  computed: {

  },
  props: {
  },
  data: function() {
    return {
      report_type: 'sales',
      date_to: null,
      date_from: null,
      timetable_ids: [],
      // details: null,
      fade: true,
      error: null
      // timeframe: 'day'

    }
  }
}
</script>

<style scoped>

</style>
