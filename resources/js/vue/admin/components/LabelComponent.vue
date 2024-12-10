<template>
    <label :for="id" :class="classObject">
        <slot>{{ field.name }}</slot>
    </label>
</template>

<script>
    export default {
        props: {
            field: { required: true },
            id: { required: true },
            objectid: { required: true }
        },
        data: function() {
            return {
                classObject: {
                    required: (this.objectid && this.field.validation_update && this.field.validation_update.includes('required'))
                    || (this.objectid && !this.field.validation_update && this.field.validation && this.field.validation.includes('required'))
                    ||  (!this.objectid && this.field.validation && this.field.validation.includes('required'))
                }
            }
        }
    }
</script>

<style scoped>

</style>