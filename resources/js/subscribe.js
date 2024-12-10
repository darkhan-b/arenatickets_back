if(document.getElementById("subscribe-block")) {
    new Vue({
        el: '#subscribe-block',
        data() {
            return {
                email: ''
            }
        },
        methods: {
            subscribe() {
                axios.post('/subscribe', { email: this.email }).then(res => {
                    window.noty(res.data.message, res.data.success ? 'success' : 'error')
                    if(res.data.success) {
                        this.email = ''
                    }
                })
            }
        }
    });
}
