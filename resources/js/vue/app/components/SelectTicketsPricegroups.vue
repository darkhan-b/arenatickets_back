<template>
    <div class="pricegroups-wrapper mx-auto mt-5">
        <div v-for="p in Object.keys(pricegroups)">
            <select-pricegroup-tickets-amount :pricegroup="pricegroups[p]"/>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        name: "SelectTicketsPricegroups",
        mounted() {
            this.$store.commit('setStep', 2)
        },
        computed: {
            ...mapState(['pricegroups'])
        },
    }
</script>

<style scoped lang="scss">
    .pricegroups-wrapper {
        //max-width: 400px;
    }
</style>
