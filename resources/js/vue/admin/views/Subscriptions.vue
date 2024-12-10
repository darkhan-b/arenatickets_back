<template>
    <div>

        <div class="container-fluid" v-if="fade">
            <h6 class="mb-0">Список всех email строкой</h6>
            <hr>
            <span v-for="email in emails">{{ email }}, </span>
        </div>
    </div>
</template>

<script>


    export default {
        mounted() {
            this.fade = false
            this.loadData()
        },
        methods: {
            loadData() {
                axios.get('/admin/eloquent/subscription',{
                    params: {all_for_select: 1, title_for_select: 'email' }
                }).then(res => {
                    console.log(res.data)
                    this.fade = true
                    this.emails = res.data
                })
            }
        },
        watch:{
            $route (to, from) {

            },

        },
        computed: {

        },
        props: {
        },
        data: function() {
            return {
                emails: null,
                fade: false,
            }
        }
    }
</script>

<style scoped>

</style>