<template>
    <div>
        <div v-for="event in events" class="text-center">
            <div class="mt-3 mb-2">
                {{ transName(event.title) }}
            </div>
            <div v-for="t in event.timetables">
                <router-link :to="`/${event.id}/${t.id}`" class="timetable-btn">
                    {{ t.date | formatDate }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        created() {
            this.loadEvents()
            this.$store.commit('setStep', 1)
        },
        methods: {
            loadEvents() {
                window.loaderIcon()
                axios.get('/api/events').then(res => {
                    this.events = res.data.events.data
                })
            },
        },
        data() {
            return {
                events: []
            }
        },
        metaInfo() {
            return {
                title: "Расписание",
                meta: []
            }
        }
    }
</script>

<style scoped>

</style>
