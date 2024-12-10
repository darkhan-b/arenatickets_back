<template>
  <div class="btn-group switcher" v-if="selectedSectionIndex === null && timetable && timetable.type === 'sections' && step < 3">
    <button class="btn btn-secondary left"
            @click="switchTo('plan')"
            :class="{ active: schemeView === 'plan'}">
      <img src="/images/tiles.svg" class="me-2 d-md-inline" :class="{ 'd-none': schemeView === 'plan' }"/>
      <img src="/images/tiles_b.svg" class="me-2 d-md-none" :class="{ 'd-inline': schemeView === 'plan', 'd-none': schemeView !== 'plan' }"/>
      {{ trans('hall_scheme') }}
    </button>
    <button class="btn btn-secondary right"
            @click="switchTo('list')"
            :class="{ active: schemeView === 'list'}">
      <img src="/images/list.svg" class="me-2 d-md-inline" :class="{ 'd-none': schemeView === 'list' }"/>
      <img src="/images/list_b.svg" class="me-2 d-md-none" :class="{ 'd-inline': schemeView === 'list', 'd-none': schemeView !== 'list' }"/>
      {{ trans('list') }}
    </button>
  </div>
</template>

<script>
import {mapState} from "vuex"

export default {
  name: "SchemeViewSwitcher",
  computed: {
    ...mapState(['schemeView','selectedSectionIndex','timetable', 'step']),
  },
  methods: {
    switchTo(val) {
      this.$store.commit('setSchemeView', val)
    }
  }
}
</script>

<style scoped lang="scss">
.switcher {
  position: fixed;
  bottom: 20px;
  margin-left: 38px;
}
.btn-group {
  font-size: 0;
  .btn {
    background: rgba(0,0,0,0.5);
    opacity: 0.7;
    font-weight: 500;
    font-size: 12px;
    color: #fff;
    border: none;
    display: inline-flex;
    align-items: center;
    &:focus {
      box-shadow: none;
    }
    &.active {
      opacity: 1;
    }
    &.left {
      border-top-left-radius: 8px;
      border-bottom-left-radius: 8px;
      border-right: 1px solid rgba(255,255,255,0.1);
    }
    &.right {
      border-top-right-radius: 8px;
      border-bottom-right-radius: 8px;
    }
  }
}
.mobile-switcher {
  display: none;
}
@media(max-width: 768px) {
  .switcher {
    margin-left: 0;
  }
  .switcher.mobile-switcher {
    height: 38px;
    display: flex;
    position: relative;
    top: 0;
    bottom: auto;
    left: 0;
  }
  .switcher.btn-group {
    .btn {
      justify-content: center;
      font-size: 13px;
      &.left {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-right: none;
      }
      &.right {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
      }
      &.active {
        background: #E4F3FF;
        color: #4BB0FE;
        fill: #4BB0FE;
      }
    }
  }
}

</style>
