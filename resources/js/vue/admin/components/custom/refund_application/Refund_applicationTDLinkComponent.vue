<template>
  <span>
    <a class="btn" @click="approveRefundApp" v-if="!object.refunded_at">
      <i class='ti-check-box grey-color' data-toggle='tooltip' title='Одобрить заявку'></i>
    </a>
    <a data-toggle="tooltip" title="Удалить" @click="deleteModel" class="model-delete btn hover-red" v-if="!object.refunded_at">
      <i class="ti-trash"></i>
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
    approveRefundApp() {
      let conf = confirm('Вы действительно хотите одобрить заявку на возврат? Обратите внимание, что возврат денег надо совершать отдельно.')
      if(!conf) return;
      window.loaderIcon()
      axios.post('/admin/refund/application/'+this.object.id+'/approve').then(res => {
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
      window.noty('Что-то пошло не так, заявка не была одобрена','alert')
    },
    deleteModel() {
      if(confirm("Вы действительно хотите удалить материал?")) {
        window.loaderIcon()
        axios.post('/admin/eloquent/refund_application/'+this.object.id,{"_method": "delete"}).then(res => {
          window.noty(this.trans('success_message'),'success')
          EventBus.$emit('reloadEloquent')
        });
      }
    }
  }
}
</script>

<style scoped>

</style>