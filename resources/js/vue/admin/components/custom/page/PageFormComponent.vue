<template>
    <div class="col-12 d-none">
        <div v-for="l in langs" :class="'admincollapse collapse-'+l">
            <h3 class="mt-4" style="font-size: 19px;">Блоки</h3>
            <div v-for="(b, bind) in blocks" class="border-top pt-3 mb-3">
                <input type="hidden" :name="`blocks[${bind}][id]`" v-model="b.id"/>
                <div class="row align-items-center">
                    <div class="col-3">
                        <b>Блок {{ (bind + 1) }}</b>
                    </div>
                    <div class="col text-right">
                        <label>Код</label>
                    </div>
                    <div class="col-4">
                        <input class="form-control" type="text" :name="`blocks[${bind}][code]`" v-model="b.code"/>
                    </div>
                    <div class="col text-right">
                        <label>Порядок</label>
                    </div>
                    <div class="col-2">
                        <input class="form-control" type="text" :name="`blocks[${bind}][sort_order]`" v-model="b.sort_order"/>
                    </div>
                    <div class="col-auto">
                        <a class="pointer" @click="del(bind)"><i class="ti-trash"></i></a>
                    </div>
                    <div class="col-12 mt-3">
                        <label>Текст</label>
                        <input type="hidden" :name="`blocks[${bind}][content][${l}]`" v-model="b.content[l]"/>
                        <textarea class="form-control ckeditorcandidate" type="text" :name="`blocks[${bind}][content][${l}]`" v-model="b.content[l]"></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <a class="btn btn-themed btn-info btn-outline" @click="addBlock">Добавить блок</a>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "PageFormComponent",
    data() {
        return {
            langs: ['ru','kz','en'],
            blocks: [],
        }
    },
    props: {
        object: {}
    },
    methods: {
        addBlock() {
            this.blocks.push({
                'id': null,
                'page_id': this.object.id,
                'code': '',
                'block_type': 'text',
                'content': {'ru': '', 'en': '', 'kz': ''},
                'sort_order': (this.blocks.length + 1)
            })
            this.launchCKEdit()
        },
        fetchBlocks() {
            axios.get('/admin/page/'+this.object.id+'/blocks').then(res => {
                this.blocks = res.data
                console.log(res.data)
                this.launchCKEdit()
            })
        },
        del(index) {
            this.blocks.splice(index, 1)
        },
        launchCKEdit() {
            this.$nextTick(() => {
                $('.ckeditorcandidate').ckeditor()
            })

        }
    },
    mounted() {
        this.fetchBlocks()
    }
}
</script>

<style scoped>

</style>
