<template>
  <div class="zoom-block">
    <a @click="zoomOut" class="mb-2"><zoom-in-svg/></a>
    <a @click="zoomIn"><zoom-out-svg/></a>
  </div>
</template>

<script>
import ZoomInSvg from '../../../../../public/images/zoom-in-2.svg'
import ZoomOutSvg from '../../../../../public/images/zoom-out-2.svg'

export default {
  name: "ZoomBlock",
  props: {
    maxScale: {
      type: Number,
      default: 3
    },
    minScale: {
      type: Number,
      default: 0.2
    },
    step: {
      type: Number,
      default: 0.2
    }
  },
  components: {
    ZoomInSvg,
    ZoomOutSvg,
  },
  data() {
    return {
    }
  },
  methods: {
    zoomIn() {
      this.$parent.scale = Math.max(this.minScale, (this.$parent.scale - this.step))
    },
    zoomOut() {
      this.$parent.scale = Math.min(this.maxScale, (this.$parent.scale + this.step))
    }
  }
}
</script>

<style scoped>
  .zoom-block a {
    touch-action: manipulation;
  }
</style>
