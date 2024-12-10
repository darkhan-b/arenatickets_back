<template>
    <div v-if="object && object.additional" class="col-12 mt-4" style="white-space: pre-wrap">
        <h5 class="mb-0">Данные анкеты:</h5>
        <div>{{ object.additionalString }}</div>
        <div><a target="_blank" style="text-decoration: underline" :href="object.resume_path">Резюме</a></div>
    </div>
</template>

<script>
export default {
    name: "ApplicationFormComponent",
    props: {
        object: {}
    },
}
</script>

<style scoped>

</style>
