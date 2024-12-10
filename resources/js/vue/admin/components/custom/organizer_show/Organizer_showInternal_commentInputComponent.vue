<template>
  <div class="col-12 mb-3 text-muted" v-if="object.internal_comment">
    {{ object.internal_comment }}
  </div>
</template>

<script>

export default {
  name: "Organizer_showInternal_commentInputComponent",
  props: {
    object: {}
  },
}
</script>