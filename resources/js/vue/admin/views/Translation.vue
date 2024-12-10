<template>
    <div>
        <div class="container-fluid" v-if="fade">

            <div class="row align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0">Переводы</h6>
                </div>

                <div class="col-auto">
                    <a tabindex="#" @click="refresh" class="btn btn-info btn-themed btn-fill">Обновить</a>
                </div>

            </div>
            <hr>

            <div class="row">
                <div class="col-lg-12">

                    <div class="grey-back toggle-menu mb-2">
                        <a v-for="lang in available_langs" @click="language = lang" :class="{ 'bg-white': language == lang }" class="btn border btn-themed toggle me-2 pointer text-uppercase">{{ lang }}</a>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" name="filecontent" v-model="data" rows="16"></textarea>
                    </div>
                    <button type="submit" @click="saveLang" class="btn btn-themed btn-info">Сохранить</button>

                </div>
            </div>

        </div>

    </div>
</template>

<script>


    export default {
        mounted() {
            // this.fade = true
            this.loadLangFile()
        },
        methods: {
            loadLangFile() {
                axios.get('/admin/language/'+this.language).then(res => {
                    this.fade = true
                    this.data = res.data.file
                    this.available_langs = res.data.langs
                })
            },
            refresh() {
                axios.post('/admin/translation/languages/refresh').then(res => {
                    window.noty(this.trans('success_message'),'success')
                })
            },
            saveLang() {
                axios.post('/admin/translation',{ lang: this.language, filecontent: this.data }).then(res => {
                    window.noty(this.trans('success_message'),'success')
                })
            }
        },
        watch:{
            $route (to, from) {

            },
            language(val) {
                this.loadLangFile()
            }

        },
        computed: {

        },
        props: {
        },
        data: function() {
            return {
                data: [],
                available_langs: ['ru','kz','en','uk','uz'],
                language: 'ru',
                fade: false,

            }
        }
    }
</script>

<style scoped>

</style>
