<template>
  <div class="col-12">
    <div class="checkbox-wrapper mb-2" v-for="permission in permissions">
      <input type="checkbox" :id="'p-'+permission.id"  :value="permission.id" name="permission[]" :checked="object.permissions && (object.permissions.map(x=>x.id)).includes(permission.id)"/>
      <label :for="'p-'+permission.id">{{ permission.name }}</label>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    this.loadPermissions()
    console.log(this.object)
  },
  methods: {
    loadPermissions() {
      axios.get('/admin/permissions').then(res => {
        this.permissions = res.data
      })
    }
  },
  props: {
    object: {}
  },
  data: function() {
    return {
      permissions: []
    }
  }
}
</script>

<style scoped>

</style>
