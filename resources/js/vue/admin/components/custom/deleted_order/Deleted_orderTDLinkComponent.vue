<template>
  <a class="btn" @click="orderDetails">
    <i class='ti-eye grey-color' data-toggle='tooltip' title='Детали заказа'></i>
  </a>
</template>

<script>
export default {
  name: "Deleted_orderTDLinkComponent",
  props: {
    object: {
      type: Object
    }
  },
  methods: {
    orderDetails() {
      window.loaderIcon()
      axios.get('/admin/order/'+this.object.id+'/details').then(res => {
        $('#custommodalbody').html(res.data.html);
        let modal = new bootstrap.Modal(document.getElementById('custommodal'), {
          keyboard: false,
          focus: false
        })
        modal.show();
      })
    }
  }
}
</script>

<style scoped>

</style>
