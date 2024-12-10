<template>
    <div>
        <div class="container-fluid" v-if="fade">
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0">
                        Настройки сайта
                    </h6>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
<!--                    <div v-for="l in avail_langs" class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input :value="l" type="checkbox" v-model="LANGS" :id="'langs-'+l" />-->
<!--                            <label :for="'langs-'+l">{{ trans(l) }}</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="LANG_IN_LINK" id="LANG_IN_LINK" />-->
<!--                            <label for="LANG_IN_LINK">Отображать язык в url</label>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input value="1" type="checkbox" v-model="SEND_EMAILS" id="SEND_EMAILS" />
                            <label for="SEND_EMAILS">Отправлять email'ы</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input value="1" type="checkbox" v-model="MAINTENANCE_MODE" id="MAINTENANCE_MODE" />
                            <label for="MAINTENANCE_MODE">Режим обслуживания</label>
                        </div>
                    </div>
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="USE_PROMOCODES" id="USE_PROMOCODES" />-->
<!--                            <label for="USE_PROMOCODES">Использовать промокода</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="USE_FAVS" id="USE_FAVS" />-->
<!--                            <label for="USE_FAVS">Список избранного</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="USE_COMPARISONS" id="USE_COMPARISONS" />-->
<!--                            <label for="USE_COMPARISONS">Список сравнений</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="USE_FEEDBACKS" id="USE_FEEDBACKS" />-->
<!--                            <label for="USE_FEEDBACKS">Отзывы на товары</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <div class="checkbox-wrapper">-->
<!--                            <input value="1" type="checkbox" v-model="USE_BONUSES" id="USE_BONUSES" />-->
<!--                            <label for="USE_BONUSES">Бонусы при покупке</label>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="form-group">
                        <button class="btn btn-themed btn-info" @click="saveEnv">{{ trans('save') }}</button>
                    </div>
                </div>
                <div class="col-md-6 text-md-right">

                    <div class="form-group">
                        <button class="btn btn-info btn-themed btn-outline" @click="clearCache">Очистить кэш</button>
                    </div>
<!--                    <div class="form-group">-->
<!--                        <a @click="refreshSitemap" target="_blank" class="btn btn-info btn-themed btn-outline">Обновить sitemap</a>-->
<!--                        <div class="mt-2">-->
<!--                            <a target="_blank" class="text-underlined" href="/sitemap.xml">Посмотреть sitemap</a>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <form method="post" action="/admin/dbdump" class="mt-3">-->
<!--                            <span v-html="window.csrf_field()"></span>-->
<!--                            <button type="submit" class="btn btn-themed btn-outline-info">Экспорт базы</button>-->
<!--                        </form>-->
<!--                    </div>-->
                </div>
            </div>

        </div>
    </div>
</template>

<script>


    export default {
        mounted() {
            this.fade = false
            this.loadEnv()
        },
        methods: {
            loadEnv() {
                axios.get('/admin/env').then(res => {
                    this.fade = true
                    this.LANGS = res.data.LANGS
                    this.LANG_IN_LINK = res.data.LANG_IN_LINK == 1
                    this.USE_PROMOCODES = res.data.USE_PROMOCODES == 1
                    this.USE_BONUSES = res.data.USE_BONUSES == 1
                    this.USE_COMPARISONS = res.data.USE_COMPARISONS == 1
                    this.USE_FAVS = res.data.USE_FAVS == 1
                    this.SEND_EMAILS = res.data.SEND_EMAILS == 1
                    this.USE_FEEDBACKS = res.data.USE_FEEDBACKS == 1
                    this.MAINTENANCE_MODE = res.data.MAINTENANCE_MODE == 1
                })
            },
            saveEnv() {
                axios.post('/admin/env', {
                    LANG_IN_LINK: this.LANG_IN_LINK,
                    SEND_EMAILS: this.SEND_EMAILS,
                    LANGS: this.LANGS,
                    USE_PROMOCODES: this.USE_PROMOCODES,
                    USE_COMPARISONS: this.USE_COMPARISONS,
                    USE_FAVS: this.USE_FAVS,
                    USE_BONUSES: this.USE_BONUSES,
                    USE_FEEDBACKS: this.USE_FEEDBACKS,
                    MAINTENANCE_MODE: this.MAINTENANCE_MODE,
                }).then(res => {
                    window.noty()
                    // this.loadEnv()
                })
            },
            clearCache() {
                window.loaderIcon()
                axios.post('/admin/cache/clear').then(res => {
                    window.noty()
                })
            },
            refreshSitemap() {
                window.loaderIcon()
                axios.post('/admin/sitemap')
            }
        },
        watch:{
            $route (to, from) {

            }
        },
        computed: {

        },
        props: {
        },
        data: function() {
            return {
                LANGS: [],
                LANG_IN_LINK: 0,
                SEND_EMAILS: 0,
                USE_PROMOCODES: 0,
                USE_BONUSES: 0,
                USE_FAVS: 0,
                USE_COMPARISONS: 0,
                USE_FEEDBACKS: 0,
                MAINTENANCE_MODE: 0,
                fade: false,
                avail_langs: ['ru','kz','en']
            }
        }
    }
</script>