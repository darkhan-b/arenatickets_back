<template>
    <div>
        <div class="container-fluid" v-if="fade">
            <div class="row">
                <div class="col-md-7">
                    <div v-if="!config">
                        <h2>Конфиг файлы</h2>
                        <hr>
                        <div class="row">

                            <div class="col-md-7">
                                <div class="pointer" v-for="l in list">
                                    <a class="py-1 d-block" tabindex="#" @click="loadConfig(l)">{{ l }}</a>
                                </div>
                            </div>
                            <div class="col-md-5">

                            </div>
                        </div>
                    </div>

                    <div v-if="config">
                        <h2>{{ file }}</h2>
                        <hr>
                        <div class="border-bottom">
                            <div v-for="s in standard_fields" class="mb-2">
                                <config-standard-field-component @ch="config[s.id] = $event" :s="s" :model="config[s.id]"></config-standard-field-component>
                            </div>
                        </div>

                        <div v-for="(field,id) in config.fields" class="mt-3 pb-3 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <a class="d-block pointer" data-toggle="collapse" :data-target="'.collapse-'+id">
                                        <input type="text" class="form-control" :value="id" @change="changedId(id, $event)" />
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a class="formatbutton" @click="moveUpDown(id,'down')"><i class="ti ti-arrow-down"></i></a>
                                    <a class="formatbutton" @click="moveUpDown(id,'up')"><i class="ti ti-arrow-up ms-2"></i></a>
                                    <a class="formatbutton" @click="copyField(id)"><i class="ti ti-clipboard ms-2"></i></a>
                                    <a class="formatbutton" @click="deleteField(id)"><i class="ti ti-trash ms-2"></i></a>
                                </div>
                            </div>
                            <div  :class="'collapse collapse-'+id">
                                <div class="mt-3">
                                    <div v-for="s in standard_field_fields" class="mb-2">
                                        <config-standard-field-component @ch="field[s.id] = $event" :s="s" :skey="'field-'+id+'sid'+s.id" :model="field[s.id]"></config-standard-field-component>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-7">
                                <input type="text" class="form-control" placeholder="Новое поле" v-model="newfieldname"/>
                            </div>
                            <div class="col-md-5">
                                <button @click="addNewField" class="btn btn-themed btn-primary">Добавить</button>
                            </div>
                        </div>

                        <div class="floating-buttons">
                            <div>
                                <button @click="back" class="btn btn-outline-secondary btn-themed me-2">Назад</button>
                                <button @click="save" class="btn btn-info btn-themed">Сохранить</button>
                            </div>
                        </div>
                        <div class="floating-bottom-buttons">
                            <div class="mt-5 text-right">
                                <button class="btn btn-themed btn-outline-secondary me-2" @click="sql">SQL</button>
                                <button class="btn btn-themed btn-outline-secondary me-2" @click="createClass">Класс</button>
                                <button class="btn btn-themed btn-danger" @click="deleteFile">Удалить</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div  v-if="!config">
            <div class="fixed-top-menu">
                <div class="border-bottom pb-3">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <input type="text" placeholder="Название файла" class="form-control" v-model="newfilename"/>
                        </div>
                        <div class="col-4">
                            <select class="form-control" v-model="copy_from">
                                <option value="">Скопировать с</option>
                                <option v-for="l in list" :value="l">{{ l }}</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <a class="btn btn-themed btn-info" @click="addNewFile">Добавить</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal" id="sql-modal" role="dialog" aria-labelledby="sql-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sql-modalLabel">Запрос</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><svg class="svg-close"><use xlink:href="#close_svg"></use></svg></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" rows="15" v-model="sqlq"></textarea>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn btn-themed btn-outline-secondary ms-2" @click="addSQLDelete">Удаление</button>
                                    <button class="btn btn-themed btn-outline-secondary ms-2" @click="sql">Создание</button>
                                </div>
                                <div class="col-6 text-right">
                                    <button class="btn btn-themed btn-success" @click="runsql(false)">Провести</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>


    export default {
        mounted() {
            this.fade = false
            this.loadList()
        },
        methods: {
            loadList() {
                axios.get('/admin/eloquent/json_configs/get').then(res => {
                    this.fade = true
                    this.list = res.data
                })
            },
            loadConfig(file) {
                window.loaderIcon()
                this.file = file
                axios.get('/admin/eloquent/json_configs/get/'+file).then(res => {
                    this.config = res.data
                }).catch(err => {
                    console.log(err)
                })
            },
            changedId(id, $event) {
                const newkey = $event.target.value
                Vue.set(this.config.fields,newkey,this.config.fields[id])
                Vue.delete(this.config.fields, id)
            },
            save() {
                axios.post('/admin/eloquent/json_configs/save/'+this.file, {config: this.config}).then(res => {
                    window.noty()
                })
            },
            deleteFile() {
                if(confirm('Вы уверены, что хотите удалить этот файл?')) {
                    axios.post('/admin/eloquent/json_configs/delete/'+this.file).then(res => {
                        window.noty()
                        this.config = null
                        this.file = null
                        this.loadList()
                    })
                }
            },
            back() {
                this.config = null
                this.file = null
            },
            moveUpDown(id,type) {
                const index = this.ids.indexOf(id)
                let newindex = type == 'down' ? this.ids[(index+1)] : id
                let oldid = type == 'down' ? id : this.ids[(index-1)]
                this.config.fields = this.moveObjectElement(oldid, newindex, this.config.fields)
            },
            deleteField(id) {
                Vue.delete(this.config.fields,id)
            },
            copyField(id) {
                Vue.set(this.config.fields,id+"_1",this.config.fields[id])
            },
            moveObjectElement(currentKey, afterKey, obj) {
                var result = {};
                var val = obj[currentKey];
                delete obj[currentKey];
                var next = -1;
                var i = 0;
                if(typeof afterKey == 'undefined' || afterKey == null) afterKey = '';
                $.each(obj, function(k, v) {
                    if((afterKey == '' && i == 0) || next == 1) {
                        result[currentKey] = val;
                        next = 0;
                    }
                    if(k == afterKey) { next = 1; }
                    result[k] = v;
                    ++i;
                });
                if(next == 1) {
                    result[currentKey] = val;
                }
                if(next !== -1) return result; else return obj;
            },
            addNewField() {
                if(!this.config.fields) {
                    this.config.fields = {}
                }
                Vue.set(this.config.fields,this.newfieldname,{})
                this.newfieldname = ''
            },
            addNewFile() {
                axios.post('/admin/eloquent/json_configs/new',{name: this.newfilename, copy: this.copy_from}).then(res => {
                    this.list = res.data
                    this.newfilename = ''
                    window.noty()
                })
            },
            sql() {
                const table = this.config.table ? this.config.table : this.file.replace('.json','')+'s'
                this.sqlq = 'CREATE TABLE IF NOT EXISTS '+table+' ('
                this.sqlq += '\n id int(11) AUTO_INCREMENT'
                let k
                for(k in this.config.fields) {
                    if (this.config.fields.hasOwnProperty(k) && k != 'id') {
                        const f = this.config.fields[k]
                        if(['text','textarea','select','checkbox'].includes(f.type)) {
                            this.sqlq += ', \n '+k
                            if(f.type == 'text') {
                                this.sqlq += ' varchar(255) NULL DEFAULT NULL'
                            }
                            if(f.type == 'textarea') {
                                this.sqlq += ' TEXT NULL DEFAULT NULL'
                            }
                            if(f.type == 'select') {
                                this.sqlq += ' int(11) NULL DEFAULT NULL'
                            }
                            if(f.type == 'checkbox') {
                                this.sqlq += ' BOOLEAN DEFAULT false NOT NULL'
                            }
                        }
                    }
                }
                this.sqlq += ',\n created_at TIMESTAMP NULL DEFAULT NULL,\n updated_at TIMESTAMP NULL DEFAULT NULL'
                this.sqlq += ', PRIMARY KEY (id))'
                $('#sql-modal').modal()

            },
            addSQLDelete() {
                const table = this.config.table ? this.config.table : this.file.replace('.json','')+'s'
                this.sqlq = 'DROP table '+ table
            },
            runsql() {
                axios.post('/admin/eloquent/json_configs/runsql',{sqlq: btoa(this.sqlq)}).then(res => {
                    window.noty()
                    // $('#sql-modal').modal('hide')
                }).catch(err => {
                    console.log(err)
                })
            },
            createClass() {
                axios.post('/admin/eloquent/json_configs/class',{
                    file: this.file.replace('.json',''),
                    path: this.config.path,
                    table: this.config.table
                }).then(res => {
                    window.noty()
                })
            }

        },
        watch:{
            $route (to, from) {

            }
        },
        computed: {
            ids() {
                let arr = []
                let k
                for(k in this.config.fields) {
                    if(this.config.fields.hasOwnProperty(k)) {
                        arr.push(k)
                    }
                }
                return arr
            }
        },
        props: {
        },
        data: function() {
            return {
                config: null,
                list: [],
                fade: false,
                newfilename: '',
                copy_from: '',
                newfieldname: '',
                file: null,
                sqlq: '',
                standard_fields: [
                    {id: "path", type: "text", name: "Путь к классу" },
                    {id: "table", type: "text", name: "Таблица" },
                    {id: "title", type: "text", name: "Название" },
                    {id: "with", type: "array", name: "Подгружать", childrentype: "text" },
                    {id: "initial_order", type: "array", name: "Начальная сортировка", childrentype: "text" },
                    {id: "tabs", type: "array", name: "Табы", childrentype: "text", newelement: "" },
                    {id: "add", type: "checkbox", name: "Добавление" },
                    {id: "edit", type: "checkbox", name: "Редактирование"},
                    {id: "duplicate", type: "checkbox", name: "Дублирование"},
                    {id: "delete", type: "checkbox", name: "Удаление"},
                    {id: "excel", type: "checkbox", name: "Excel"},
                    {id: "checks", type: "checkbox", name: "Чекбоксы"},
                    {id: "description", type: "textarea", name: "Доп пометка"},
                    {id: "modal", type: "text", name: "Класс модального окна"},
                    {id: "additional_links", type: "array", name: "Доп линки в ряду таблицы", childrentype: "object", structure: [
                            {id: "url", type: "text", name: "URL"},
                            {id: "title", type: "text", name: "Название (html)"}
                        ], newelement: {}
                    },
                    {id: "permissions", type: "object", name: "Необходимые права", structure: [
                            {id: "view", type: "array", name: "Просмотр", childrentype: "text"},
                            {id: "create", type: "array", name: "Создание", childrentype: "text"},
                            {id: "update", type: "array", name: "Редактирование", childrentype: "text"},
                            {id: "delete", type: "array", name: "Удаление", childrentype: "text"},
                        ]}
                ],
                standard_field_fields: [
                    {id: "name", type: "text", name: "Название"},
                    {id: "type", type: "select", name: "Тип", options: ["text","textarea","select","checkbox","file"]},
                    {id: "subtype", type: "text", name: "Подтип (для файлов)"},
                    {id: "select_data", type: "object", name: "Опции для select", structure: [
                            {id: "type", type: "select", name: "Тип", options: ["model","list"]},
                            {id: "model", type: "text", name: "Путь к классу модели"},
                            {id: "autocomplete_model", type: "text", name: "Модель autocomplete"},
                            {id: "title", type: "text", name: "Поле названия"},
                            {id: "data", type: "array", name: "Опции (если не привязано к модели)", childrentype: "object", structure: [
                                    {id: "value", type: "text", name: "Значение"},
                                    {id: "title", type: "text", name: "Название"}
                                ], newelement: {}
                            }
                        ], newelement: [{}]
                    },
                    {id: "form", type: "checkbox", name: "Показывать в форме"},
                    {id: "table", type: "checkbox", name: "Показывать в таблице"},
                    {id: "tablesort", type: "checkbox", name: "Сортировать в таблице"},
                    {id: "tablesearch", type: "checkbox", name: "Поиск в таблице"},
                    {id: "likesearch", type: "checkbox", name: "Поиск LIKE в таблице"},
                    {id: "attributes", type: "array", name: "Аттрибуты (отображение связанных моделей)", childrentype: "object", structure: [
                            {id: "id", type: "text", name: "Id"},
                            {id: "value", type: "array", name: "Названия", childrentype: "text"}
                        ], newelement: {}
                    },
                    {id: "classes", type: "text", name: "Класс инпута"},
                    {id: "wrapclasses", type: "text", name: "Класс упаковки"},
                    {id: "tab", type: "text", name: "Таб"},
                    {id: "validation", type: "text", name: "Валидация"},
                    {id: "validation_update", type: "text", name: "Валидация при редактировании"},
                    {id: "formnote", type: "text", name: "Подсказка"},
                    {id: "table_url", type: "text", name: "Url в таблице"},
                    {id: "multiple", type: "checkbox", name: "Несколько значений (для select)"},
                    {id: "autocomplete", type: "checkbox", name: "Autocomplete (для select)"},
                    {id: "skipsearch", type: "checkbox", name: "Пропускать стандартный поиск"},
                    {id: "table_filter_select", type: "checkbox", name: "Поиск по таблице из выпадающего списка"},
                    {id: "format_number", type: "checkbox", name: "Форматирование номера"},
                    {id: "format_date", type: "checkbox", name: "Форматирование даты"},
                    {id: "table_color", type: "checkbox", name: "Цвет в таблице"},
                    {id: "table_image", type: "checkbox", name: "Показывать в таблице картинкой"},
                    {id: "table_field", type: "text", name: "Другое поле для показа в таблице"},
                    {id: "table_show", type: "object", name: "Показ в таблице", structure: "keyvalue"},
                ]
            }
        }
    }
</script>

<style scoped>
    .floating-buttons {
        position: fixed;
        right: 25px;
        top: 25px;
    }
    .floating-bottom-buttons {
        position: fixed;
        right: 25px;
        bottom: 25px;
    }
    .formatbutton {
        cursor: pointer;
    }
    .formatbutton:hover {
        opacity: 0.5;
    }
    .fixed-top-menu {
        position: fixed;
        top: 25px;
        right: 25px;
        max-width: 450px;
    }

</style>

<style>
    label {
        margin-bottom: 0;
    }
    .svg-close {
        width: 13px;
        height: 13px;
    }
</style>
