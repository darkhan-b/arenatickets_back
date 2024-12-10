<template>
    <div class="admincollapse w-100" :class="'collapse-'+tab">
        <div class="col-12" v-if="loaded">
            <div v-for="(line,lind) in lines" :key="`line-${lind}`">
                <div class="row align-items-center">
                    <div class="col-12 font-weight-bold">
                        {{ line.title.ru }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 font-weight-bold">месячно</div>
                    <div class="col" v-for="l in langs" :key="`line-m-${lind}-${l}`">
                        <div>{{ l }}</div>
                        <input type="text"
                               :name="`lines[${line.id}][content_monthly][${l}]`"
                               v-model="tmpTariffLines[line.id].content_monthly[l]"
                               class="form-control"/>
                    </div>
                </div>
                <div class="row mt-2 mb-4">
                    <div class="col-12 font-weight-bold">единовременно</div>
                    <div class="col" v-for="l in langs" :key="`line-o-${lind}-${l}`">
                        <div>{{ l }}</div>
                        <input type="text"
                               :name="`lines[${line.id}][content_one][${l}]`"
                               v-model="tmpTariffLines[line.id].content_one[l]"
                               class="form-control"/>
                    </div>
                </div>
                <hr/>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TariffLinesTabComponent",
    mounted() {
        axios.get('/admin/tariffs/lines').then(res => {
            this.lines = res.data
            this.lines.forEach(line => {
                let tline = null
                if(this.object.tariff_lines && this.object.tariff_lines.length) {
                    tline = this.object.tariff_lines.find(x => x.pivot.tariff_line_id == line.id)
                }
                if(tline) {
                    this.$set(this.tmpTariffLines, line.id, {
                        content_monthly: tline.pivot.content_monthly ? JSON.parse(tline.pivot.content_monthly) : JSON.parse(JSON.stringify(this.defaultContent)),
                        content_one: tline.pivot.content_one ? JSON.parse(tline.pivot.content_one) : JSON.parse(JSON.stringify(this.defaultContent)),
                        tariff_line_id: line.id
                    })
                } else {
                    this.$set(this.tmpTariffLines, line.id, {
                        content_monthly: JSON.parse(JSON.stringify(this.defaultContent)),
                        content_one: JSON.parse(JSON.stringify(this.defaultContent)),
                        tariff_line_id: line.id
                    })
                }
            })
            this.loaded = true
        })
    },
    methods: {

    },
    props: {
        object: {},
        tab: {},
    },
    data: function() {
        return {
            lines: [],
            loaded: false,
            tmpTariffLines: {},
            langs: ["ru","kz","en","uk","uz"],
            defaultContent: {
                "ru": '',
                "kz": '',
                "en": '',
                "uk": '',
                "uz": ''
            }
        }
    }
}
</script>

<style scoped>

</style>
