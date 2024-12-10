import { TheMask } from 'vue-the-mask'

if(document.getElementById("vue-vacancy")) {

    new Vue({
        el: '#vue-vacancy',
        components: { TheMask },
        data() {
            return {
                name: '',
                phone: '',
                vacancy_id: null,
                file: null,
                lang: $('html')[0].lang,
            }
        },
        methods: {
            submitForm() {
                let formData = new FormData();
                formData.append('name', this.name)
                formData.append('phone', this.phone)
                if(this.file) {
                    formData.append('file', this.file)
                }
                if(this.vacancy_id) {
                    formData.append('vacancy_id', this.vacancy_id)
                }
                axios.post(`/${this.lang}/vacancy`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data"
                    }
                }).then(res => {
                    window.noty(res.data.message, res.data.success ? 'success' : 'error')
                    if(res.data.success) {
                        this.name = ''
                        this.phone = ''
                        this.vacancy_id = null
                        this.file = null
                    }
                });
            },
            processFile(event) {
                this.file = event.target.files[0]
            },
            resumeClicked(e) {
                this.vacancy_id = e.target.getAttribute("data-id")
                let element = document.getElementById("vacancy-form")
                element.scrollIntoView()   
            }
        },
        mounted() {
        }
    });
}
