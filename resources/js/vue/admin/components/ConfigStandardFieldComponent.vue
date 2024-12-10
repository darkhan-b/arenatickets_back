<template>

    <div>
        <template v-if="s.type  == 'checkbox'">
            <div class="row align-items-center">
                <div class="col-9 offset-3">
                    <div class="checkbox-wrapper">
                        <input value="1" class="me-1" :type="s.type" :id="s.id+skey" v-model="newm" :name="s.id"/>
                        <label-component :id="s.id+skey" :objectid="'1'" :field="s" :key="'label'+s.id"></label-component>
                    </div>
                </div>
            </div>
        </template>

        <template v-if="['text','password','email','textarea'].includes(s.type)">
            <div class="row align-items-center">
                <div class="col-3">
                    <label-component :id="s.id" :objectid="'1'" :field="s" :key="'label'+s.id"></label-component>
                </div>
                <div class="col-9">
                    <textarea v-if="s.type === 'textarea'" v-model="newm" class="form-control" :type="s.type" :id="s.id" :name="s.id" rows="1"></textarea>
                    <input v-else v-model="newm" class="form-control" :type="s.type" :id="s.id" :name="s.id"/>
                </div>
            </div>
        </template>



        <template v-if="s.type == 'array'">
            <div class="row align-items-center">
                <div class="col-3">
                    <label-component :id="s.id" :objectid="'1'" :field="s" :key="'label'+s.id"></label-component>
                </div>
                <div class="col-9">
                    <div class="mb-2" v-for="(n,nind) in newm">
                        <config-standard-field-component @ch="newm[nind] = $event" :s="{type: s.childrentype, id: (s.id+'-'+nind), structure: s.structure}" :model="newm[nind]"></config-standard-field-component>
                    </div>
                </div>
            </div>
            <div class=" mt-1 row no-gutters justify-content-end">
                <div class="col-auto">
                    <button class="btn-r btn rounded-circle btn-outline-info" @click="newm.push(s.newelement)">+</button>
                </div>
                <div class="col-auto ps-2">
                    <button class="btn-r btn rounded-circle btn-outline-danger" @click="newm.splice((newm.length - 1), 1)">-</button>
                </div>
            </div>
        </template>


        <template v-if="s.type == 'object'">
            <div class="row align-items-center">
                <div class="col-3">
                    <label-component :id="s.id" :objectid="'1'" :field="s" :key="'label'+s.id"></label-component>
                </div>

                <div class="col-9" v-if="newm">
                    <div v-if="s.structure == 'keyvalue'">
                        <div class="mb-2" v-for="(v,k) in newm">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <label-component :id="s.id+'-'+k" :objectid="'1'" :field="{name: k}" :key="'label'+s.id+'-'+k"></label-component>
                                </div>
                                <div class="col-8">
                                    <input v-model="newm[k]" class="form-control" type="text" :id="s.id+'-'+k" :name="s.id+'-'+k"/>
                                </div>
                                <div class="col-1"><a @click="deleteKey(k)"><i class="ti ti-trash"></i></a></div>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-9">
                                <input v-model="newkey" class="form-control" type="text" placeholder="Новый ключ"/>
                            </div>
                            <div class="col-3 text-right">
                                <button @click="addKey" class="btn-r btn rounded-circle btn-outline-info">+</button>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="mb-2" v-for="(o,oind) in s.structure">
                            <config-standard-field-component @ch="newm[o.id] = $event" :s="o" :model="newm[o.id]"></config-standard-field-component>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-if="s.type  == 'select'">
            <div class="row align-items-center">
                <div class="col-3">
                    <label-component :id="s.id" :objectid="'1'" :field="s" :key="'label'+s.id"></label-component>
                </div>
                <div class="col-9">
                    <select v-model="newm" class="form-control" :id="s.id" :name="s.id">
                        <option v-for="d in s.options" :value="d">
                            [{{ d }}] {{ d }}
                        </option>
                    </select>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        mounted() {
            if(this.s.type == 'object' && !this.newm) {
                // console.log('empty')
                this.newm = {}
            }
            if(this.s.type == 'array' && !this.newm) {
                // console.log('empty')
                this.newm = []
            }
        },
        methods: {
            deleteKey(key) {
                Vue.delete(this.newm, key)
            },
            addKey() {
                Vue.set(this.newm, this.newkey, '')
                this.newkey = ''
            }
        },
        watch: {
            newm: {
                handler: function (val, oldVal) {
                    this.$emit('ch', val)
                },
                deep: true
            }
        },
        props: {
            s: { required: true },
            model: { required: true },
            skey: { },
        },
        data: function() {
            return {
                newm: this.model,
                newkey: '',
            }
        }
    }
</script>

<style scoped>
    .btn-r {
        padding: 0;
        padding-bottom: 3px;
        width: 28px;
        height: 28px;
        line-height: 24px;
        font-size: 19px;
        text-align: center;
        display: inline-block;
    }
</style>
