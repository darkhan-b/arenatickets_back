<template>
  <span>
    <a class="btn" @click="duplicateScheme">
      <i class='ti-layers grey-color' data-toggle='tooltip' title='Дублировать рассадку'></i>
    </a>
  </span>

</template>

<script>

import EventBus from "../../../../../event-bus"

export default {
  name: "Refund_applicationTDLinkComponent",
  props: {
    object: {
      type: Object
    }
  },
  methods: {
    duplicateScheme() {
      let title = prompt("Введите название новой рассадки");
      if (title == null || title == "") {
        return
      }
      window.loaderIcon()
      axios.post('/admin/schemes/'+this.object.id+'/duplicate', { title }).then(res => {
        if(res.data) {
          window.noty()
          EventBus.$emit('reloadEloquent')
        } else {
          this.generalError()
        }
      }).catch((e) => {
        if(e.response.status == 404) {
          window.noty('Заявка не найдена','alert')
        } else {
          this.generalError()
        }
      })
    },
    generalError() {
      window.noty('Что-то пошло не так, рассадка не была дублирована','alert')
    },
  }
}
</script>