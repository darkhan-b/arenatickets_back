<template>
  <div>
<!--    <h1>This is test page</h1>-->
<!--    <div class="py-4">Test num: {{ num }}</div>-->
<!--    <div class="mb-4">-->
<!--    <button v-prevent-double-tap-zoom @click="clickMe">v directive</button>-->
<!--    </div>-->
<!--    <button class="css" @click="clickMe">css style</button>-->
  </div>
</template>

<script>
export default {
  name: "TestPage",
  data() {
    return {
      num: 1
    }
  },
  methods: {
    clickMe() {
      this.num = Math.random()
    }
  }
}
</script>

<style scoped>
  button.css {
    touch-action: manipulation;
  }
</style>