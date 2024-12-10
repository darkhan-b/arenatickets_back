<template>
  <div class="admincollapse w-100" :class="'collapse-'+tab">
    <div class="col-12">
      <label>Id события в системе Eventum</label>
      <input type="number" 
             class="form-control"
             name="meta_data[eventumId]"
             :value="object.meta_data ? object.meta_data.eventumId : ''"/>
      <div>
        <small class="text-muted">Если указан Id, то сеанс будет продаваться через систему Eventum</small>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: "TimetableMetaTabComponent",
  props: {
    object: {},
    tab: {},
  },
  data() {
    return {
    }
  },
  async mounted() {
    
  },
  methods: {
    
  },
}
</script>