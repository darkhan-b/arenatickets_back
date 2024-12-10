<template>
  <div>
    <span v-if="object.refunded_at" class='badge bg-success'>Одобрена</span>
    <span v-else>-</span>
  </div> 
</template>

<script>
export default {
  name: "Refund_applicationTDLinkComponent",
  props: {
    object: {
      type: Object
    },
    field_id: {}
  },
  methods: {
  }
}
</script>

<style scoped>

</style>