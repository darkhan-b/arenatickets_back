<template>
  <span v-if="object.organizer_add_status == 'new'">
  <a class="btn" @click="approve">
    <i class='ti-check grey-color' data-toggle='tooltip' title='Одобрить'></i>
  </a>
    <a class="btn" @click="reject">
    <i class='ti-na grey-color' data-toggle='tooltip' title='Отклонить'></i>
  </a>
    </span>
</template>

<script>
import EventBus from "../../../../../event-bus"

export default {
  name: "Organizer_showTDLinkComponent",
  props: {
    object: {
      type: Object
    }
  },
  methods: {
    approve() {
      this.changeStatus('approved')
    },
    reject() {
      this.changeStatus('rejected')
    },
    changeStatus(status) {
      window.loaderIcon()
      axios.post('/admin/organizer/show/'+this.object.id+'/'+status).then(res => {
        console.log(res)
        window.noty(this.trans('success_message'),'success')
        EventBus.$emit('reloadEloquent')
      })
    }
  }
}
</script>