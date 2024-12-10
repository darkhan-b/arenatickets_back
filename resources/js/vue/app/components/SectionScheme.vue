<template>
  <div class="position-relative">
    <zoom-block :min-scale="0.4" :max-scale="2"/>
    <div class="scheme-price-bubbles" v-if="prices.length > 0">
      <div class="scheme-price-bubble" v-for="(p, pind) in prices">
        <span class="price-color" :class="`color-${pind}`"></span>
        <span>{{ p | formatNumber }} ₸</span>
      </div>
    </div>

    <div class="venue-wrapper"
         :class="`view-${view} ${panEnabled ? 'overflow-hidden' : 'pan-disabled'}`"
         ref="venueWrapper"
         id="venueWrapper">
<!--      <panZoom @init="panZoomInit" -->
<!--               @zoom="panZoomStart"-->
<!--               :options="{ -->
<!--                  zoomDoubleClickSpeed: 1, -->
<!--                  beforeMouseDown: panBeforeMouseDown, -->
<!--                  minZoom: panEnabled ? 0.4 : 1,-->
<!--                  maxZoom: panEnabled ? 2 : 1,-->
<!--                  bounds: panEnabled,-->
<!--                  boundsPadding: 0.5-->
<!--              }">-->
        <div class="seats-wrapper position-relative"
             :style="`width:${maxWScaled}px; height: ${maxH}px; ${panEnabled ? 'transition:none;' : 'transform: scale('+scale+')'}`"
             ref="vw"
             id="seats-wrapper"
             @touchstart="handleMouseDown"
             @mousedown="handleMouseDown"
             @touchend="touchEnd"
             @mouseup="touchEnd">
          <div class="drag-area-box" :style="selectAreaStyle"></div>
          <div v-for="(s, sind) in seats"
               :class="[{
                    hasTicket: s.ticket && s.ticket.price,
                    selected: selectedSeats.includes(s.id),
                    dragHovered: selectedIndexesByDragging.includes(sind),
                    sold: s.ticket && s.ticket.sold,
                    soldAsInvitation: s.ticket && s.ticket.soldAsInvitation,
                    blocked: s.ticket && s.ticket.blocked,
                 }, s.ticket ? 'color-'+prices.indexOf(s.ticket.price) : '']"
               @click="seatPressed(s)"
               ref="seat"
               :id="'seat-'+sind"
               class="seat"
               :key="'seat-'+sind"
               :style="`left: ${s.x}px; top: ${s.y}px;`"
          >{{ s.seat }}
          </div>
          <div class="seat-row"
               @click="rowPressed(r)"
               v-for="r in Object.keys(rows)"
               :key="'row-'+r"
               :style="`top: ${rows[r].y}px; left: ${rows[r].x}px`">
            {{ rows[r].title }}
          </div>
        </div>
<!--      </panZoom>-->
    </div>
<!--    <div v-if="mouseSelectable" class="mt-2">-->
<!--      <div class="d-inline-block radio-wrapper">-->
<!--        <label for="move">Движение схемы</label>-->
<!--        <input type="radio" class="align-middle" v-model="panEnabled" :value="true" id="move" name="sectionType"/>-->
<!--      </div>-->
<!--      <div class="d-inline-block ps-4 radio-wrapper">-->
<!--        <label for="select">Выделение билетов</label>-->
<!--        <input type="radio" class="align-middle" v-model="panEnabled" :value="false" id="select" name="sectionType"/>-->
<!--      </div>-->
<!--    </div>-->
  </div>
</template>

<script>

import ZoomBlock from "../../app/components/ZoomBlock"
const POINT_MODEL = ['offsetX', 'offsetY', 'clientX', 'clientY'];

const throttle = (method, delay = 30) => {
  let timer = null;
  return function() {
    if (timer) return;
    const args = arguments;
    timer = setTimeout(() => {
      method.apply(this, args);
      timer = null;
    }, delay);
  };
}

export default {
  name: "SectionScheme",
  components: { ZoomBlock },
  props: {
    mouseSelectable: {
      type: Boolean,
      default: false,
    },
    view: {
      type: String,
      default: 'user'
    },
    seats: {
      type: Array,
      required: true
    },
    selectedSeats: {
      type: Array,
      default: []
    },
    prices: {
      type: Array,
      default() {
        return []
      },
    },
    sectionId: {
      type: Number,
      default: null,
    }
  },
  data() {
    return {
      rows: {},
      dragging: false,
      handleMouseMoveThrottled: () => {
      },
      point: {
        offsetX: 0,
        offsetY: 0
      },
      startPoint: {
        offsetX: 0,
        offsetY: 0
      },
      endPoint: {
        offsetX: 0,
        offsetY: 0
      },
      selfPoint: {
        clientX: 0,
        clientY: 0
      },
      scale: 1,
      maxW: 0,
      maxH: 0,
      childItems: [],
      childrenDOMPoints: [],
      selectedIndexesByDragging: [],
      browserPoint: {
      },
      panZoom: null,
      panEnabled: false,
      panning: false,
      zooming: false,
      zoomTimer: null,
      zoomPause: 200,
      panPause: 150,
    }
  },
  computed: {
    selectAreaStyle() {
      const startClientX = this.startPoint.clientX - this.selfPoint.clientX;
      const endClientX = this.endPoint.clientX - this.selfPoint.clientX;
      const startClientY = this.startPoint.clientY - this.selfPoint.clientY;
      const endClientY = this.endPoint.clientY - this.selfPoint.clientY;
      const { left, top, width, height } = {
        left: Math.min(startClientX, endClientX),
        top: Math.min(startClientY, endClientY),
        width: Math.abs(startClientX - endClientX),
        height: Math.abs(startClientY - endClientY)
      };
      // console.log(`width = ${width}px, height = ${height}px, left = ${left}px, top = ${top}px`);
      return {
        left: `${left}px`,
        top: `${top}px`,
        width: `${width}px`,
        height: `${height}px`,
        zIndex: 444,
        // backgroundColor: 'rgba(51, 51, 51, 0.5)',
        backgroundColor: 'rgba(51, 51, 51, 0)',
        position: 'absolute'
      };
    },
    maxWScaled() {
      if(this.scale < 1) return this.maxW
      return this.maxW * this.scale
    }
  },
  watch: {
    seats(val) {
      this.checkRows()
    },
    scale(val, prevval) {
      if(this.panZoom && !this.zooming && this.panEnabled) {
        // console.log('new scale')
        // console.log(val)
        let container = document.querySelector('#seats-wrapper');
        let rect = container.getBoundingClientRect();
        // console.log(rect)
        let cx = rect.x + rect.width / 2;
        let cy = rect.y + rect.height / 2;
        this.panZoom.smoothZoomAbs(cx, cy, val);
        // let zoomBy = val > prevval ? (1 + (val - prevval)) : (1 / (1 + (prevval - val)));
        // this.panZoom.smoothZoom(cx, cy, zoomBy);

      }
    },
    dragging(val) {
      if (val) {
        this.collectDOMPoints();
        this.initBrowserPoint();
      }
    }
  },
  methods: {
    seatPressed(s) {
      // if(this.panning) return
      this.$emit('seatPressed', s)
    },
    rowPressed(r) {
      // if(this.panning) return
      this.$emit('rowPressed', r)
    },
    checkRows() {
      this.rows = {}
      this.maxW = 0
      this.seats.forEach(item => {
        this.maxW = Math.max(this.maxW, (item.x + 50))
        this.maxH = Math.max(this.maxH, (item.y + 100))
        if(!this.rows.hasOwnProperty(item.row)) {
          this.$set(this.rows, item.row, { y: this.getRowY(item), x: this.getRowX(item), title: this.getRowName(item) })
        }
      })
      let rows_array = (Object.keys(this.rows))
      this.$emit('maxRow',(rows_array.length > 0 ? Math.max(...rows_array) : 0))
    },
    getRowName(rowItem) {
      let title = rowItem.row
      if(isNaN(title)) return title
      return `${this.trans('row')} ${title}`
    },
    getRowX(rowItem) {
      return 0
    },
    getRowY(rowItem) {
      return rowItem.y
    },
    handleMouseDown(e) {
      // if(this.panEnabled) {
      //   setTimeout(() => {
      //     this.panning = true
      //   }, this.panPause)
      // }
      if(!this.mouseSelectable || this.panEnabled) {
        return
      }
      this.selectedIndexesByDragging = []
      this.$nextTick(() => {
        this.resetPoint(e);
        this.updatePointData(this.point, e);
        window.addEventListener('mouseup', this.handleMouseUp);
        window.addEventListener('mousemove', this.handleMouseMoveThrottled);
      });
    },
    touchEnd() {
      // setTimeout(() => {
      //   this.panning = false
      // }, this.panPause)
    },
    handleMouseUp(e) {
      this.dragging = false;
      this.updatePointData(this.point, e);
      this.resetPoint(e);
      window.removeEventListener('mouseup', this.handleMouseUp);
      window.removeEventListener('mousemove', this.handleMouseMoveThrottled);
      this.selectedIndexesByDragging.forEach(index => {
        this.$emit('seatPressed',this.seats[index])
      })
      this.selectedIndexesByDragging = []
    },
    handleMouseMove(e) {
      if (!this.dragging) {
        this.dragging = true;
      }
      this.updatePointData(this.endPoint, e);
      this.updatePointData(this.point, e);
      this.$nextTick(() => {
        this.childrenDOMPoints.forEach(child => {
          const isSelected = this.checkIfChildInSelectArea(child.point);
          if(isSelected) {
            this.selectedIndexesByDragging.push(parseInt(child.instance.id.replace('seat-','')))
            this.selectedIndexesByDragging = [...new Set(this.selectedIndexesByDragging)]
          }
        });
      });
    },
    updatePointData(pointObj, pointData) {
      POINT_MODEL.forEach(key => {
        this.$set(pointObj, key, pointData[key]);
      });
    },
    resetPoint(e) {
      this.updatePointData(this.startPoint, e);
      this.updatePointData(this.endPoint, e);
    },
    initBrowserPoint() {
      this.browserPoint = {
        clientWidth: document.body.clientWidth,
        clientHeight: document.body.clientHeight,
        scrollWidth: document.body.scrollWidth,
        scrollHeight: document.body.scrollHeight
      };
    },
    checkIfChildInSelectArea(childItem) {
      const startClientX = this.startPoint.clientX;
      const startClientY = this.startPoint.clientY;
      const endClientX = this.endPoint.clientX;
      const endClientY = this.endPoint.clientY;
      const selectPoint = {
        left: Math.min(startClientX, endClientX),
        top: Math.min(startClientY, endClientY),
        width: Math.abs(startClientX - endClientX),
        height: Math.abs(startClientY - endClientY)
      };
      return selectPoint.left <= childItem.left + childItem.width
          && selectPoint.left + selectPoint.width >= childItem.left
          && selectPoint.top <= childItem.top + childItem.height
          && selectPoint.top + selectPoint.height >= childItem.top;
    },
    collectDOMPoints() {
      let element = this.$refs.vw
      const DOMRect = element.getBoundingClientRect();
      this.selfPoint = {
        clientX: DOMRect.left || DOMRect.x,
        clientY: DOMRect.top || DOMRect.y,
      };
      this.childrenDOMPoints = this.childItems.map(child => ({
        point: child.getBoundingClientRect(),
        instance: child
      }));
    },
    scaleForWholeWidth() {
      let $el = this.$refs.venueWrapper
      let fullW = $el.scrollWidth;
      let visibleW = $el.clientWidth;
      if(fullW > visibleW && this.panZoom) {
        this.scale = 1 - (Math.ceil(((fullW - visibleW) / fullW) * 5) / 5) // rounding to nearest 0.2
      }
    },
    panBeforeMouseDown() {
      return !this.panEnabled
    },
    panZoomInit(panZoom) {
      this.panZoom = panZoom
    },
    panZoomStart() {
      if(this.zoomTimer) {
        clearTimeout(this.zoomTimer)
      }
      this.zooming = true
      // console.log('setting pan zom scale to ')
      // console.log(this.panZoom.getTransform().scale)
      this.scale = this.panZoom.getTransform().scale
      this.zoomTimer = setTimeout(() => {
        this.zooming = false
      }, this.zoomPause)
    },
  },
  mounted() {
    this.checkRows()
    this.handleMouseMoveThrottled = throttle(this.handleMouseMove);
    this.childItems = this.$refs.seat
    // this.scaleForWholeWidth()
  },
  created() {
  },
}
</script>

<style scoped>
/*.seats-wrapper {*/
/*  transform-origin: top center;*/
/*}*/
</style>
