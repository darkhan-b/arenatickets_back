<template>
    <form enctype="multipart/form-data" id="eloquent-form">
        <div class="row">

            <div class="col-12 border-bottom pb-3 mb-2" v-if="config.tabs">
                <a class="btn border text-capitalize btn-themed toggle ms-2"
                   v-if="!(['kz','en','ru'].includes(tab) && !langs.includes(tab))" v-for="(tab,tind) in config.tabs" data-toggle="admincollapse"
                   :data-target="'.collapse-'+tab" role="button"
                   :class="{active: tind === 0}">{{ trans(tab) }}</a>
            </div>

            <template v-for="tab in config.tabs">
                <template v-if="$options.components[$options.filters.capitalize(model)+$options.filters.capitalize(tab)+'TabComponent']">
                    <component :object="object" :tab="tab" :is="$options.filters.capitalize(model)+$options.filters.capitalize(tab)+'TabComponent'"></component>
                </template>
            </template>

            <div v-for="(field,id) in config.fields"
                 v-if="field.form"
                 :class="[field.wrapclasses ? field.wrapclasses : 'col-12']">
                <form-field-component :field="field"
                                      :id="id"
                                      :object="object"
                                      :media="media"
                                      :model="model"
                                      :select_models="select_models"
                                      v-on:mediaChange="mediaChange"
                                      :config="config"></form-field-component>
            </div>

            <div class="collapse collapse-metas"  data-parent="#eloquent-form">
                <metas-form-component v-if="config.metas" :object="object" :config="config"></metas-form-component>
            </div>

            <template v-if="$options.components[$options.filters.capitalize(model)+'FormComponent']">
                <component :object="object"
                           :select_models="select_models"
                           :is="$options.filters.capitalize(model)+'FormComponent'">
                </component>
            </template>



            <div class="col-12 text-right">
                <button @click="formSubmit" class=" mt-4 mb-3 btn btn-info btn-themed">Сохранить</button>
            </div>

        </div>
    </form>
</template>

<script>

    export default {
        methods: {
            formSubmit(e) {
                e.preventDefault()
                this.$emit('formSubmit')
            },
            mediaChange(data) {
                this.$emit('mediaChange',data)
            },
        },
        props: {
            object: { required: true },
            config: { required: true },
            media: { required: true },
            model: { required: true },
            langs: {},
            select_models: { required: true },
        },
    }
</script>
