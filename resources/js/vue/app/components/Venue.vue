<template>
  <div class="position-relative">
    <zoom-block v-if="schemeView === 'plan'"/>
    <div class="venue-wrapper"
         :class="`view-${view} ${panEnabled ? '' : 'pan-disabled'}`"
         v-if="schemeView === 'plan'"
         ref="venueWrapper">
<!--      <panZoom @init="panZoomInit" :options="{ -->
<!--        zoomDoubleClickSpeed: 1, -->
<!--        beforeMouseDown: panBeforeMouseDown, -->
<!--        minZoom: panEnabled ? 0.2 : 1,-->
<!--        bounds: panEnabled,-->
<!--        boundsPadding: 0.2 -->
<!--      }">-->
        <svg class="venue-svg"
             ref="venue"
             id="venueSvg"
             :style="panEnabled ? '' : `transform: scale(${scale}); width: ${maxWScaled}px`"
             :class="{ transitionTransform: zooming, 'position-absolute': !panEnabled, 'position-relative': panEnabled, 'w-100': view === 'admin' }"
             @touchstart="touchStart"
             @mousedown="touchStart"
             @touchend="touchEnd"
             @mouseup="touchEnd"
             :viewBox="`0 0 ${scheme.width} ${scheme.height}`">
          <template v-for="(s, sind) in scheme.sections">
            <SectionSvg @click.native="selectSector(sind, s)"
                        :section="s"
                        :sid="s.id"
                        :class="{ 
                          'blocked': sectorIsBlocked(s) && s.for_sale, 
                          'not_for_sale': !s.for_sale,
                          'closed': sectionIsClosed(s.id)
                        }"
                        :data-toggle="view == 'user' && s.for_sale ? 'tooltip' : ''"
                        :title="`${numberOfTickets(s.id) > 0 ? trans('tickets_of')+': '+numberOfTickets(s.id) : trans('no_tickets')}`"
                        :selected="selectedSectionIndex === sind"
                        :key="'path-'+sind"/>
          </template>
        </svg>
<!--      </panZoom>-->
    </div>
    <div v-if="schemeView === 'list'" class="pb-5">
      <template v-for="(s, sind) in scheme.sections" v-if="s.for_sale">
        <div :key="'sect-'+sind" class="section-row">
          <div class="row align-items-center">
            <div class="col">
              <div class="section-title">{{ transName(s.title) }}</div>
              <div class="text-muted">{{ trans('places_left') }}: {{ numberOfTickets(s.id) }}</div>
            </div>
            <div class="col-auto">

            </div>
            <div class="col-auto">
              <button class="btn btn-themed"
                      :disabled="sectorIsBlocked(s)"
                      @click="selectSector(sind, s)">
                {{ trans('choose_seats') }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import SectionSvg from '../../app/components/SectionSvg'
import ZoomBlock from "../../app/components/ZoomBlock"
import { mapState } from 'vuex'

export default {
  name: "Venue",
  components: {
    ZoomBlock,
    SectionSvg
  },
  props: {
    scheme: {
      type: Object,
      required: true
    },
    selectedSectionIndex: {
      type: Number,
      default: null
    },
    view: {
      type: String,
      default: "user"
    },
    panEnabled: {
      type: Boolean,
      default: true
    },
    closedSections: {
      type: Array,
      default: () => []
    }
  },
  data: function() {
    return {
      timetable: null,
      fade: false,
      scale: 1,
      panZoom: null,
      panning: false,
      zooming: false,
      panPause: 150,
    }
  },
  computed: {
    ...mapState(['pricegroups','schemeView']),
    maxWScaled() {
      if(!this.scheme || !this.scheme.width) return 0
      return Math.max(1, this.scale) * this.scheme.width
      // return this.scale * this.scheme.width
    }
  },
  mounted() {
    $('[data-toggle="tooltip"]').tooltip()
    if(this.scheme.sections && this.scheme.sections.length == 1) {
      this.selectSector(0, this.scheme.sections[0])
    }
    if(this.schemeView === 'plan' && this.view !== 'admin') {
      let w = this.$refs.venueWrapper.clientWidth
      let h = this.$refs.venueWrapper.clientHeight
      this.scale = Math.min(w / this.scheme.width, h / this.scheme.height) 
    }
  },
  methods: {
    selectSector(index, section) {
      if(this.panning) return
      if(this.sectorIsBlocked(section)) {
        return
      }
      if(this.view === 'user' && !section.for_sale) {
        return
      }
      $('.tooltip').hide()
      this.$parent.selectedSectionIndex = (index == this.selectedSectionIndex) ? null : index
    },
    sectorIsBlocked(section) {
      if(this.view === 'admin') return false
      return this.numberOfTickets(section.id) < 1
    },
    numberOfTickets(sectorId) {
      if(this.pricegroups && this.pricegroups[sectorId]) return this.pricegroups[sectorId].cnt
      return 0
    },
    touchStart() {
      setTimeout(() => {
        this.panning = true
      }, this.panPause)
    },
    touchEnd() {
      setTimeout(() => {
        this.panning = false
      }, this.panPause)
    },
    panBeforeMouseDown() {
      return !this.panEnabled
    },
    panZoomInit(panZoom) {
      this.panZoom = panZoom
      if(!this.panEnabled) {
        this.panZoom.pause()
      }
    },
    sectionIsClosed(sectionId) {
      if(this.view !== 'admin') return false
      return !!this.closedSections.find(x => x.section_id === sectionId)
    }
  },
  watch:{
    scale(val, prevval) {
      if(this.panZoom && this.panEnabled) {
        let container = document.querySelector('#venueSvg');
        let rect = container.getBoundingClientRect();
        let cx = rect.x + rect.width / 2;
        let cy = rect.y + rect.height / 2;
        this.panZoom.smoothZoomAbs(cx, cy, val);
        // let zoomBy = 1 + (val - prevval);
        // this.panZoom.smoothZoom(cx, cy, zoomBy);
      }
    },
  },
}
</script>

<style scoped lang="scss">
.venue-wrapper.view-admin {
  background: transparent;
  border: 1px solid #eee;
  svg {
    background: white;
  }
}
.section-row {
  padding: 14px 0;
  border-bottom: 1px solid #D7DADD;
  .section-title {
    font-weight: 500;
    font-size: 18px;
  }
}
.venue-svg {
  //transform-origin: top center;
  transform-origin: 0 0;
}
@media(max-width: 768px) {
  .section-row {
    .section-title {
      font-size: 15px;
    }
  }
}
</style>
