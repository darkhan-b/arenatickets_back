<template>
    <div class="col-md-12">
        <div class="form-group">
            <label for="tags">Тэги</label>
            <input type="text" class="form-control" name="tags" :value="object.tags.map(x => x.title).join(',')" id="tags"/>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.loadAllTags()
            console.log(this.object.tags.map(x => x.title).join(','))
        },
        methods: {
            initateSelectize() {

            },
            loadAllTags() {
                axios.get('/admin/tags',{ params: { type: this.tagtype } }).then(res => {
                    let opts = []
                    let d = res.data
                    var i = 0
                    for (i = 0; i < d.length; ++i) {
                        opts.push({ tag: d[i] })
                    }

                    $('#tags').selectize({
                        delimiter: ',',
                        valueField: 'tag',
                        labelField: 'tag',
                        searchField: 'tag',
                        options: opts,
                        create: true
                    })
                })
            },
            getCurrentObjectTags() {

            }
        },
        props: {
            object: { required: true },
            tagtype: { required: true },
        },
        data: function() {
            return {

            }
        }
    }
</script>

<style scoped>

</style>