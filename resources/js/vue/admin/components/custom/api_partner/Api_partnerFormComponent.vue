<template>
    <div v-if="object" class="col-12 mt-4">
      <div class="form-group">
        <label for="show_ids">События</label>
        <select class="form-control autocomplete-select"
                data-model="show"
                data-field="title"
                :data-selected="JSON.stringify(object.shows)"
                id="show_ids"
                name="show_ids[]"
                multiple>
          <option value="">-</option>
        </select>
      </div>
      <div class="form-group">
        <div v-if="object.token">
          <label>Токен</label>
          <div class="alert alert-info py-2">
            {{ object.token }}
          </div>
        </div>
      </div>
      <div class="form-group">
        <div v-if="object.public_key">
          <label>Публичный ключ</label>
          <div>
            <a @click="copyPublicKey" class="text-underlined pointer">Сохранить</a>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
    name: "ApiPartnerFormComponent",
    props: {
        object: {}
    },
  methods: {
      copyPublicKey() {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + this.object.public_key);
        element.setAttribute('download', 'public.pem');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
      }
  }
}
</script>

<style scoped>

</style>
