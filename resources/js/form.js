import Vue from 'vue'
import Vuelidate from 'vuelidate'
Vue.use(Vuelidate)
import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
Vue.use(VueSweetalert2)
import TextareaAutosize from 'vue-textarea-autosize'
Vue.use(TextareaAutosize)

Vue.prototype.appendFormdata = (FormData, data, name) => {
    name = name || '';
    if (typeof data === 'object') {
        for (var index in data) {
            if (Object.prototype.hasOwnProperty.call(data, index)) {
                if (name == '') {
                    Vue.prototype.appendFormdata(FormData, data[index], index)
                } else {
                    Vue.prototype.appendFormdata(FormData, data[index], name + '[' + index + ']')
                }
            }
        }
    } else {
        FormData.append(name, data)
    }
    return FormData
}

import { required, minLength, email, numeric, minValue, maxValue } from 'vuelidate/lib/validators'

$(function() {
    window.launchForms();
});

// $(function() {
//     if($('#vue-apply-form').length > 0) {
//         new Vue({
//             el: "#vue-apply-form",
//             data() {
//                 return {
//                     form: {},
//                     loading: false,
//                     resume: null,
//                 }
//             },
//             validations() {
//                 let rules = {
//                     form: {
//                         surname: {
//                             required
//                         },
//                         name: {
//                             required,
//                             minLength: minLength(2)
//                         },
//                         birth : {
//                             required,
//                             minLength: minLength(10)
//                         },
//                         email: {
//                             required,
//                             email
//                         },
//                         country: {
//                             required,
//                             minLength: minLength(2)
//                         },
//                         city: {
//                             required,
//                             minLength: minLength(2)
//                         },
//                         languagesSpeak: {
//                             required,
//                             minLength: minLength(2)
//                         },
//                         languages: {
//                             required,
//                             minLength: minLength(2)
//                         },
//                         position: {
//                             required,
//                         },
//                         age: {
//                             required,
//                             numeric,
//                             minValue: minValue(16),
//                             maxValue: maxValue(120),
//                         },
//                         edu: {
//                             required,
//                         },
//                         university: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         previousjob: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         recommendations: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         achievements: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         sum: {
//                             required,
//                         },
//                         sumWant: {
//                             required,
//                         },
//                         programs: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         criteria: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         break: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         whyCRM: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         family: {
//                             required,
//                         },
//                         you: {
//                             required,
//                         },
//                         goals: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         spend: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         when: {
//                             required,
//                         },
//                         forWhat: {
//                             minLength: minLength(1),
//                             $each: {
//                                 required
//                             }
//                         },
//                         app: {
//
//                         },
//                         agree: {
//                             checked: value => value === true
//                         }
//                     }
//                 }
//                 return rules
//             },
//             mounted() {
//                 this.nullify()
//             },
//             methods: {
//                 submitForm() {
//                     this.loading = true
//                     let formData = new FormData()
//                     if(this.resume && typeof this.resume === 'object') {
//                         formData.append('resume', this.resume)
//                     }
//                     formData = this.appendFormdata(formData, this.form)
//                     formData.append('_token', document.head.querySelector('meta[name="csrf-token"]').content)
//                     console.log(formData)
//                     let _this = this
//                     $.ajax({
//                         url: '/apply',
//                         data: formData,
//                         processData: false,
//                         contentType: false,
//                         type: 'post',
//                         success: function(response) {
//                             _this.loading = false
//                             _this.$swal(response)
//                             _this.nullify()
//                         },
//                         error: function(error) {
//                             // console.log(Object.values(error.responseJSON.errors).join(', '))
//                             _this.loading = false
//                             _this.$swal(Object.values(error.responseJSON.errors).join(', '))
//                         }
//                     })
//                 },
//                 addField(fieldname) {
//                     this.form[fieldname].push('')
//                 },
//                 removeField(fieldname, index) {
//                     this.form[fieldname].splice(index, 1)
//                 },
//                 processFile(event) {
//                     let file = event.target.files[0]
//                     if(file.size > (1 * 1024 * 1024)) {
//                         this.$swal("Размер файла слишком велик", '', 'error')
//                         return
//                     }
//                     this.resume = file
//                 },
//                 nullify() {
//                     this.form = {
//                         surname: '',
//                         name: '',
//                         birth: '',
//                         email: '',
//                         country: '',
//                         city: '',
//                         languagesSpeak: '',
//                         languages: '',
//                         position: null,
//                         age: '',
//                         edu: null,
//                         university: [''],
//                         previousjob: [''],
//                         recommendations: [''],
//                         achievements: [''],
//                         sum: null,
//                         sumWant: '',
//                         programs: [''],
//                         criteria: [''],
//                         break: [''],
//                         whyCRM: [''],
//                         family: null,
//                         you: null,
//                         goals: [''],
//                         spend: [''],
//                         when: null,
//                         forWhat: [''],
//                         app: '',
//                         agree: false,
//                     }
//                     this.resume = null
//                 }
//             }
//         })
//     }
// })

window.launchForms = () => {
    let $sel = $('.vue-form[v-cloak]')
    if($sel.length > 0) {
        $.each($sel, function() {
            let formtype = $(this).data('type')
            let url = $(this).data('url')
            let country = $(this).data('country')
            new Vue({
                el: '#'+$(this).attr('id'),
                data() {
                    return {
                        form: {},
                        loading: false,
                    }
                },
                validations() {
                    let rules = { form: {} }
                    rules.form.name = {
                        required,
                        minLength: minLength(4)
                    }
                    rules.form.email = {
                        required,
                        email
                    }
                    rules.form.phone = {
                        required,
                        minLength: minLength(10)
                    }
                    if(!['presentation','tariffs'].includes(formtype)) {
                        rules.form.message = {
                            required,
                            minLength: minLength(4)
                        }
                    }
                    return rules
                },
                mounted() {
                    this.nullify()
                },
                methods: {
                    submitForm() {
                        this.form.addcomment = $('#addcomment').val()
                        this.loading = true
                        let canonical = document.querySelector("link[rel='canonical']").href
                        $.post('/form/submit', {
                            _token: document.head.querySelector('meta[name="csrf-token"]').content,
                            formtype: $('#bittype').val() ? $('#bittype').val() : formtype,
                            url: url,
                            form: this.form
                        }).done((response) => {
                            this.loading = false
                            this.$swal(response)
                            this.nullify()
                            if(formtype == 'tariffs') {
                                setTimeout(() => {
                                    window.popupClose()
                                }, 3000)
                            }
                        }).fail((error) => {
                            this.loading = false
                            console.log(error)
                            this.$swal('Что-то пошло не так')
                        })
                    },
                    nullify() {
                        this.form = {
                            name: '',
                            phone: '',
                            email: '',
                            message: '',
                            country: country
                        }
                    }
                }
            })
        })
    }
}
