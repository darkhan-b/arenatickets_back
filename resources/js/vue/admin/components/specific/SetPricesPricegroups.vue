<template>
  <div>
    <div class="row mb-3">
      <div class="col-md-3">
        Ценовые группы:
      </div>
      <div class="col-md-2">
        Кол-во
      </div>
      <div class="col-md-2">
        Стоимость
      </div>
    </div>

    <div v-for="(p, pind) in pricegroups" class="mb-3">
      <div class="row">
        <div class="col-md-3 col-12 mb-md-0 mb-2">
          <input type="text" class="form-control" v-model="p.title.ru" placeholder="Название"/>
        </div>
        <div class="col-md-2 col-6 mb-md-0 mb-2">
          <input type="number" class="form-control" v-model="p.amount" placeholder="Кол-во билетов"/>
        </div>
        <div class="col-md-2 col-6 mb-md-0 mb-2">
          <input type="number" class="form-control" v-model="p.price" placeholder="Цена"/>
        </div>
        
        <div class="col-md col-12">
          <a class="btn btn-info btn-themed btn-fill" @click="savePricegroup(p, false)">Сохранить</a>
          <div class="dropdown d-inline-block align-middle ml-3">
            <a role="button"
               class="btn btn-info btn-themed btn-fill dropdown-toggle"
               id="dropdownMenuButton"
               @click="showPIndex === pind ? showPIndex = null : showPIndex = pind"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">
              Пригласительные ({{ p.amount }})
            </a>
            <div class="dropdown-menu" :class="{ show: showPIndex === pind}" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item pointer" @click="savePricegroup(p, true, true)">Прятать цену</a>
              <a class="dropdown-item pointer" @click="savePricegroup(p, true, false)">Оставить цену</a>
            </div>
          </div>
<!--          <a class="btn btn-info btn-themed btn-fill" @click="savePricegroup(p, true)">Пригласительные ({{ p.amount }})</a>-->
          <a tabindex="#" class="btn btn-themed btn-outline btn-danger ml-3" @click="deletePricegroup(p, pind)"><i class="ti-trash"></i></a>
        </div>
      </div>
    </div>
    <div class="text-left">
      <a class="btn btn-themed btn-outline btn-secondary" @click="addPricegroup">Добавить</a>
    </div>

    <div class="mt-4">
      <label>Комментарий для пригласительных</label>
      <textarea v-model="comment" class="form-control" rows="2"></textarea>
    </div>
  </div>

</template>

<script>
export default {
  name: "SetPricesPricegroups",
  props: {
    ticketsCount: {
      type: Object
    },
    timetable: {
      type: Object
    },
  },
  data() {
    return {
      pricegroups: [],
      showPIndex: null,
      comment: ''
    }
  },
  methods: {
    addPricegroup() {
      this.pricegroups.push({
        title: {
          ru: '',
          kz: ''
        },
        price: '',
        amount: '',
      })
    },
    getPricegroups() {
      axios.get('/admin/timetable/'+this.timetable.id+'/pricegroups').then(res => {
        this.pricegroups = res.data
      })
    },
    savePricegroup(pricegroup, invitationOrder = false, hidePrice = false) {
      if(invitationOrder) {
        let res = confirm(`Будет создано ${pricegroup.amount} билетов, которые будут оформлены как пригласительные, продолжить?`)
        if(!res) return
      }
      window.loaderIcon()
      axios.post('/admin/timetable/'+this.timetable.id+'/pricegroups', { 
        ...pricegroup, 
        invitationOrder, 
        hidePrice, 
        comment: this.comment 
      }).then(res => {
        if(res && invitationOrder) {
          window.location = res.data.ticketsLink
          return
        }
        this.comment = ''
        this.pricegroups = res.data
        window.noty()
      })
    },
    deletePricegroup(pricegroup, index) {
      if(pricegroup.id) {
        window.loaderIcon()
        axios.delete('/admin/timetable/'+this.timetable.id+'/pricegroups/'+pricegroup.id).then(res => {
          this.pricegroups = res.data
          window.noty()
        })
      }
      if(!pricegroup.id) {
        this.$delete(this.pricegroups, index)
        // this.pricegroups.splice()
      }
    }
  },
  mounted() {
    this.getPricegroups()
  },
}
</script>