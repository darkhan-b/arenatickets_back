<template>
  <div>
    <div v-for="(o, oind) in object[field_id]" :key="`key-${oind}`">
      [{{ o.id }}] {{ o.date }} - {{ o.show.title.ru }}
    </div>
  </div>
</template>

<script>
export default {
  name: "CarouselTimetablesTDComponent",
  props: {
    object: {
      type: Object
    },
    field_id: {}
  },
}
</script>