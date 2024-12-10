<template>
    <div class="col-12">
        <div class="row">
            <div class="col-12" v-for="meta in dict">
                <div class="form-group">
                    <label-component :id="meta" :field="{ name: meta }" :key="'metalabel'+meta"></label-component>
                    <!--            <input :value="object[meta]" v-model="object[meta]" class="form-control" type="text" :id="meta" :name="meta"/>-->
                    <input :value="object['']" class="form-control" type="text" :id="meta" :name="meta"/>
                </div>
            </div>

        </div>
    </div>
</template>

<script>


    export default {
        mounted() {
            let _this = this
            if(this.config.langs) {
                this.config.langs.forEach(function (lang, lindex) {
                    _this.metatypes.forEach(function (metatype, mindex) {
                        _this.dict.push('meta_'+metatype+'_'+lang)
                    })
                })
            } else {
                this.metatypes.forEach(function (metatype, mindex) {
                    _this.dict.push('meta_'+metatype+'_'+lang)
                })
            }
            console.log(_this.dict)
        },
        props: {
            object: { required: true },
            config: { required: true },
        },
        data: function() {
            return {
                dict: [],
                metatypes: ['title','keywords','description']
            }
        }
    }
</script>