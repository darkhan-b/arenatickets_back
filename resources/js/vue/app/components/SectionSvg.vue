<template>
  <g class="section" :class="{ selected: selected }">
    <g v-if="section.svg.custom" v-html="section.svg.custom"></g>
    <path v-else
          stroke="black"
          :stroke-width="selected ? '4' : '0'"
          :fill="section.svg.color"
          :d="'M'+section.svg.points.join(' l')+'z'"></path>
    <text text-anchor="middle"
          v-if="section.show_title"
          dominant-baseline="central"
          :transform="`rotate(${section.svg.text[2] ? section.svg.text[2] : 0}, ${section.svg.text[0]}, ${section.svg.text[1]})`"
          :style="`fill: ${section.title_color ? section.title_color : '#000'}`"
          :x="section.svg.text[0]"
          :y="section.svg.text[1]">
      {{ section.title.ru }}
    </text>
  </g>
</template>

<script>
export default {
  name: "SectionSvg",
  props: {
    section: {
      type: Object,
      required: true
    },
    selected: {
      type: Boolean,
      default: false,
    }
  },
  methods: {
  },
}
</script>

<style scoped>

</style>
