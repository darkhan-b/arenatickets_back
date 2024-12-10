<template>
  <!--  <div v-if="hasKassirRights || isOrganizerForShow" class="form-group">-->
  <div class="form-group"> 
    <label class="mb-3 bigger-text">{{ trans('pay_method') }}:</label>

    <div class="row row-narrow row-desktop-narrow">
      <div class="col-lg-4 col-md-4 col-6 mb-2" v-for="(p, pind) in availablePayMethods" :key="`av-pm-${pind}`">
        <div class="pay-method h-100 pointer" @click="handleInput(p.id)">
          <div class="row h-100 align-items-center g-0">
            <div class="col">
              <div class="pe-2">
                <img :src="`/images/${i.src}`" v-for="i in p.icons" :key="`v-${i}`" class="pay-icon" :class="i.cl" alt=""/>
                <div class="pay-method-title" v-if="p.title">{{ p.title }}</div>
              </div>
            </div>
            <div class="col-auto">
              <div class="pay-check" :class="{ active: p.id === value}">
                <img src="/images/tick.svg" alt="ok" v-if="p.id === value"/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters, mapState} from "vuex"

export default {
  name: "PayMethods",
  props: {
    value: {},
  },
  computed: {
    ...mapGetters([
      'hasKassirRights',
      'isOrganizerForShow'
    ]),
    ...mapState([
        'user'
    ]),
    availablePayMethods() {
      let arr = [
        {
          id: 'card',
          title: 'Visa/Mastercard',
          icons: [
            { src: 'pay_method_visa.png', cl: 'visa'},
            { src: 'pay_method_mc.png', cl: 'mc'},
          ]
        },
        {
          id: 'kaspi',
          icons: [{src: 'pay_method_kaspi.svg', cl: 'kaspi'}]
        },
      ]
      if(this.hasKassirRights) {
        arr = [
          {
            id: 'cash',
            title: this.trans('cashdesk'),
            icons: [{src: 'pay_method_cash.svg?2', cl: 'cash'}] }
        ]
      }
      if(this.isOrganizerForShow) {
        arr.push({
          id: 'invitation',
          title: `${this.trans('invitation')} (${this.trans('show_price')})`,
          icons: [{src: 'pay_method_invitation.svg?2', cl: 'invitation'}]
        })
        arr.push({
          id: 'invitation_hide',
          title: `${this.trans('invitation')} (${this.trans('hide_price')})`,
          icons: [{ src: 'pay_method_invitation.svg?2', cl: 'invitation'}]
        })
      }
      // if(this.user && [1,11,28].includes(this.user.id)) {
      //   arr.push({
      //       id: 'kaspi',
      //       icons: [{src: 'pay_method_kaspi.svg', cl: 'kaspi'}]
      //   })
      // }
      return arr
    }
  }, 
  methods: {
    handleInput(val) {
      this.$emit('input', val)
    }
  }
}
</script>

<style scoped lang="scss">
.pay-method {
  background: #FFFFFF;
  border: 1px solid #E5E7EA;
  border-radius: 8px;
  padding: 12px 14px;
  .pay-method-title {
    font-size:  14px;
  }
  &:hover {
    background: #f6f6f6;
  }
}
.pay-check {
  border: 1px solid #7E8389;
  border-radius: 100%;
  display: inline-flex;
  justify-items: center;
  align-items: center;
  width: 22px;
  height: 22px;
  position: relative;
  background: transparent;
  img {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translateX(-50%) translateY(-50%);
    max-width: 34px;
  }
  &.active {
    background: #4BB0FE;
    border: 1px solid #4BB0FE;
  }
}
.pay-icon {
  &.mc {
    width: 45px;
  }
  &.visa {
    width: 51px;
    margin-right: 7px;
  }
  &.kaspi {
    width: 116px;
  }
}
@media(max-width: 768px) {
  .pay-method {
    padding: 10px 12px;
    .pay-method-title {
      font-size:  12px;
    }
    img {
      max-width: 30px;
      &.kaspi {
        max-width: 90px;
      }
      &.visa {
        max-width: 40px;
      }
      &.mc {
        max-width: 35px;
      }
    }
  }
}
</style>
