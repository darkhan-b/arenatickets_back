if(document.getElementById("vue-people")) {
    new Vue({
        el: '#vue-people',
        data() {
            return {
                letterSelected: [],
                letters: [],
                lang: $('html')[0].lang,
                group: $('#vue-people').data('group'),
            }
        },
        methods: {
            getLetters() {
                axios.get(`/${this.lang}/people/letters/${this.group}/get`).then(res => {
                    this.letters = res.data;
                });
            },
            letterPressed(letter) {
                this.letterSelected = [letter];
                this.load()
            },
            reset() {
                this.letterSelected = []
                this.load()
            },
            load() {
                if(!this.letterSelected.length) return
                axios.get(`/${this.lang}/people/letter/${this.group}/${this.letterSelected[0]}`).then(res => {
                    $('#people-dynamic').html(res.data);
                    AOS.init();
                })
            }
        },
        mounted() {
            this.getLetters()
        }
    });
}
