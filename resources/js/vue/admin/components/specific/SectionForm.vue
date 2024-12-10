<template>
  <div>

    <div class="bg-white p-3 mt-2 border">
      <div>
        <div class="row align-items-center">
          <div class="col-3"></div>
          <div class="col-3">ru</div>
          <div class="col-3">kz</div>
          <div class="col-3">en</div>
        </div>
        <div class="row align-items-center mt-2">
          <div class="col-3">
            Название
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.title.ru"/>
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.title.kz"/>
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.title.en"/>
          </div>
        </div>
        <div class="row align-items-center mt-2">
          <div class="col-3">
            Пометка
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.note.ru"/>
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.note.kz"/>
          </div>
          <div class="col-3">
            <input class="form-control" v-model="section.note.en"/>
          </div>
        </div>
        <div class="row align-items-center mt-2">
          <div class="col-3">
            Цвет
          </div>
          <div class="col-3">
            <input class="form-control" :disabled="!!section.svg.custom" v-model="section.svg.color"/>
          </div>
          <div class="col-3">
            <div class="checkbox-wrapper">
              <input type="checkbox" :id="'for-sale-'+section.id" v-model="section.for_sale" value="1"/>
              <label :for="'for-sale-'+section.id">Для продажи</label>
            </div>
          </div>
          <div class="col-3">
            <div class="checkbox-wrapper">
              <input type="checkbox" :id="'show-title-'+section.id" v-model="section.show_title" value="1"/>
              <label :for="'show-title-'+section.id">Показывать название</label>
            </div>
          </div>
        </div>
      </div>

      <div class="row align-items-center mt-2">
        <div class="col-4">
          <div>&nbsp;</div>
          Точка названия:
        </div>
        <div class="col-3">
          <div>x</div>
          <input type="number" v-model="section.svg.text[0]" class="form-control"/>
        </div>
        <div class="col-3">
          <div>y</div>
          <input type="number" v-model="section.svg.text[1]" class="form-control"/>
        </div>
        <div class="col-2">
          <div>Угол</div>
          <input type="number" v-model="section.svg.text[2]" class="form-control"/>
        </div>
      </div>

      <div class="mt-4">
        <div v-for="(p, pind) in section.svg.points">

          <div class="row align-items-center mt-1">
            <div class="col-4">Точка {{ pind }}: ({{ cumulativeX(pind) }} {{ cumulativeY(pind) }})</div>
            <div class="col-3">
              <input type="number" :disabled="!!section.svg.custom" v-model="p[0]" class="form-control"/>
            </div>
            <div class="col-3">
              <input type="number" :disabled="!!section.svg.custom" v-model="p[1]" class="form-control"/>
            </div>
            <div class="col-2">
              <a @click="removePoint(pind)"><i class="ti-trash"></i></a>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <div >Кастомный svg код (при наличии точки и цвет игнорируются)</div>
        <textarea class="form-control" v-model="section.svg.custom" rows="6"></textarea>
      </div>

      <div class="mt-3">
        <button :disabled="!!section.svg.custom" @click="addPoint" class="btn btn-outline btn-themed btn-secondary">+ точку</button>
        <a @click="deleteSection" class="btn btn-outline btn-themed btn-danger"><i class="ti-trash"></i></a>
        <router-link class="btn btn-outline btn-themed btn-secondary ml-2" v-if="section.id" :to="'/section/'+section.id">План сектора</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    section: {
      type: Object,
      required: true
    },
  },
  methods: {
    addPoint() {
      this.section.svg.points.push([50,50])
    },
    removePoint(point_index) {
      this.$delete(this.section.svg.points, point_index)
    },
    deleteSection() {
      this.$emit('deleteSection', this.section)
    },
    cumulativeX(index) {
      return this.section.svg.points.reduce((a, v, i) => (a = a + (i <= index ? parseInt(v[0]) : 0), a), 0)
    },
    cumulativeY(index) {
      return this.section.svg.points.reduce((a, v, i) => (a = a + (i <= index ? parseInt(v[1]) : 0), a), 0)
    }
  },
  name: "SectionForm"
}
</script>

<style scoped>

</style>
