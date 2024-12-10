<template>
  <div class="position-relative">
    <zoom-block v-if="schemeView === 'plan'"/>
    <div class="venue-wrapper" v-if="schemeView === 'plan'" ref="venueWrapper">
      <svg class="w-100 h-100 position-absolute venue-svg"
           ref="venue"
           :viewBox="'0 0 600 600'"
           :style="`transform: scale(${scale}); left: ${leftCalced}px`">
        <template v-for="(s, sind) in scheme.sections">
          <SectionSvg @click.native="selectSector(sind, s)"
                      :section="s"
                      :class="{ 'blocked': sectorIsBlocked(s) && s.for_sale, 'not_for_sale': !s.for_sale }"
                      :data-toggle="view == 'user' && s.for_sale ? 'tooltip' : ''"
                      :title="`${numberOfTickets(s.id) > 0 ? trans('tickets_of')+': '+numberOfTickets(s.id) : trans('no_tickets')}`"
                      :selected="selectedSectionIndex === sind"
                      :key="'path-'+sind"/>
        </template>
      </svg>
    </div>
    <div v-if="schemeView === 'list'">
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
  name: 'VenueOld',
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
    }
  },
  data: function() {
    return {
      timetable: null,
      fade: false,
      scale: 1,
      leftCalced: 0,
      maxW: 0,
    }
  },
  computed: {
    ...mapState(['pricegroups','schemeView'])
  },
  mounted() {
    $('[data-toggle="tooltip"]').tooltip()
    if(this.scheme.sections && this.scheme.sections.length == 1) {
      this.selectSector(0, this.scheme.sections[0])
    }
    this.maxW = this.$refs.venue.clientWidth
  },
  methods: {
    selectSector(index, section) {
      if(this.sectorIsBlocked(section)) {
        return
      }
      $('.tooltip').hide()
      // this.$store.commit('setSelectedSectionIndex', (index == this.selectedSectionIndex) ? null : index)
      this.$parent.selectedSectionIndex = (index == this.selectedSectionIndex) ? null : index
    },
    sectorIsBlocked(section) {
      if(this.view === 'admin') {
        return false
      }
      // return !(this.pricegroups && this.pricegroups[section.id] && this.pricegroups[section.id].cnt > 0)
      return this.numberOfTickets(section.id) < 1
    },
    numberOfTickets(sectorId) {
      if(this.pricegroups && this.pricegroups[sectorId]) return this.pricegroups[sectorId].cnt
      return 0
    }
  },
  watch:{
    scale(val, prevval) {
      let $el = this.$refs.venueWrapper
      console.log(this.maxW)
      if(val > 1 || val > prevval) {
        setTimeout(() => {
          this.leftCalced = (this.maxW * (val - 1)) / 2;
          this.$nextTick(() => {
            $el.scrollLeft = $el.scrollLeft + ((this.maxW * ((val - prevval))) / 2);
          })
        }, 150)
      }

      // this.$nextTick(() => {
      //   $el.scrollLeft = ($el.scrollWidth - $el.clientWidth) / 2 ;
      // })
      // this.leftCalced = ($el.scrollWidth - $el.clientWidth) / 2;
      // console.log($el.scrollWidth)
      // console.log($el.clientWidth)
      // console.log(($el.scrollWidth - $el.clientWidth) / 2)
      // $el.scrollLeft = ($el.scrollWidth - $el.clientWidth) / 2 ;
    }
  },
}
</script>

<style scoped lang="scss">
.section-row {
  padding: 14px 0;
  border-bottom: 1px solid #D7DADD;
  .section-title {
    font-weight: 500;
    font-size: 18px;
  }
}
.venue-svg {
  transform-origin: top center;
}
@media(max-width: 768px) {
  .section-row {
    .section-title {
      font-size: 15px;
    }
  }
}
</style>
