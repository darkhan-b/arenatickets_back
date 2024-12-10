<template>
  <div data-parent="#eloquent-form"
       :class="['admincollapse',
                 collapseClass,
                 field.tab && config.tabs && ((Array.isArray(field.tab) && field.tab[0] === config.tabs[0]) || (field.tab === config.tabs[0])) ? 'show' : '',
                 ]">

    <template v-if="$options.components[$options.filters.capitalize(model)+$options.filters.capitalize(id)+'InputComponent']">
      <component :object="object" :field="field" :id="id" :is="$options.filters.capitalize(model)+$options.filters.capitalize(id)+'InputComponent'"></component>
    </template>

    <template v-else>
      <div class="form-group">
        <label-component :id="id" :objectid="object.id" :field="field" :key="'label'+id" v-if="!['file','checkbox'].includes(field.type)"></label-component>

        <template v-if="field.type  == 'textarea'">
          <template v-if="field.languages">
            <div class="mb-2 admincollapse"
                 :class="['collapse-'+lang, {show: lang === config.tabs[0]}]"
                 v-for="lang in field.languages">
              <label>{{ trans(lang) }}</label>
              <div v-if="field.quill">
                <quill-editor :class="field.classes"
                              v-model="object[id][lang]"
                              :options="quillOptions"/>
                <input type="hidden" :name="id+'['+lang+']'" :id="id+'['+lang+']'" :value="object[id][lang]"/>
              </div>
              <textarea v-else :rows="field.rows ? field.rows : '5'" class="form-control" :class="field.classes" :id="id+'['+lang+']'" :name="id+'['+lang+']'" v-model="object[id][lang]">{{ object[id][lang] }}</textarea>
            </div>
          </template>
          <template v-else>
            <div v-if="field.quill">
              <quill-editor :class="field.classes" v-model="object[id]" :options="quillOptions"/>
              <input type="hidden" :name="id" :id="id" :value="object[id]"/>
            </div>
            <textarea v-else :rows="field.rows ? field.rows : '5'" class="form-control" :class="field.classes" :id="id" :name="id" v-model="object[id]">{{ object[id] }}</textarea>
          </template>
        </template>

        <template v-if="field.type  == 'checkbox'">
          <div class="checkbox-wrapper">
            <input type="hidden" :name="id" value="0"/>
            <input value="1" class="me-1" :class="field.classes" :type="field.type" :id="id" :name="id" :checked="object[id] == 1"/>
            <label-component :id="id" :objectid="object.id" :field="field" :key="'label'+id"></label-component>
          </div>
        </template>

        <template v-if="field.type  == 'file'">
          <div v-if="(field.subtype && field.subtype == 'image')">
            <div v-for="(m, mind) in media" class="d-inline-block position-relative media-image-wrapper mb-2" :data-id="m.id">
              <template v-if="!field.collection || field.collection === m.collection_name">
                <video v-if="m.mime_type.startsWith('video')" :src="m.url" style="max-width: 100px;"></video>
                <img v-else :src="m.url" :data-ind="mind" class="img-fluid mb-2" width="100" alt=""/>
                <i :data-id="m.id" @click="mediaDelete(m.id)" class="media-delete-button ti-trash position-absolute" ></i>
                <i v-if="media.length > 1" :data-id="m.id" @click="mediaMove(m.id)" class="media-delete-button media-move-button ti-arrow-right position-absolute" ></i>
              </template>
            </div>
          </div>
          <div class="file-form-wrapper">
            <input @change="fileChange(id,$event)" class="form-control" :class="field.classes" :type="field.type" :id="id" :name="id" :placeholder="field.name" :multiple="field.multiple"/>
            <label-component :id="id" :objectid="object.id" :field="field" :key="'label'+id" :class="{'files_selected': selected_files[id] > 0 }">{{ selected_files[id] > 0 ? 'Файлов выбрано: '+selected_files[id] : field.name }}</label-component>
            <a v-if="!field.subtype && object[id]" target="_blank" :href="'/admin/file/'+id+'/'+object[id]">{{ field.name }}</a>
          </div>
        </template>

        <template v-if="field.type  == 'select'">
          <select class="form-control"
                  :data-model="field.select_data.type == 'model' ? field.select_data.autocomplete_model : ''"
                  :data-selected="Array.isArray(object[id]) ? JSON.stringify(object[id]) : JSON.stringify([{id: (object[id] && object[id].id) ? object[id].id : object[id]}])"
                  :data-field="field.select_data.title"
                  :class="field.classes+(field.autocomplete ? ' autocomplete-select' : '')"
                  :id="id"
                  :name="field.multiple ? id+'[]' : id" :multiple="field.multiple">
            <template v-if="field.select_data.type == 'model'">

              <option value="">-</option>
              <option v-for="d in select_models[id]" :value="d.id" :selected="(Array.isArray(object[id]) ? object[id].map(x=>x.id) : [parseInt(object[id])]).includes(d.id)">
                [{{ d.id }}]
                <template v-if="Array.isArray(field.select_data.title)">
                  <template v-for="t in field.select_data.title">
                    {{ d[t] }}
                  </template>
                </template>
                <template v-else-if="field.select_data.language">
                  {{ d[field.select_data.title][field.select_data.language] }}
                </template>
                <template v-else>
                  {{ d[field.select_data.subtitle ? field.select_data.subtitle : field.select_data.title] }}
                </template>
              </option>
            </template>
            <template v-if="field.select_data.type == 'list'">
              <option value="">-</option>
              <option v-for="d in field.select_data.data" :value="d.value" :selected="(Array.isArray(object[id]) ? object[id] : [object[id]]).includes(d.value)">
                [{{ d.value }}] {{ d.title }}
              </option>
            </template>
          </select>

        </template>

        <template v-if="['text','password','email','number'].includes(field.type)">
          <template v-if="field.languages">
            <div class="mt-2 mb-2 admincollapse"
                 :class="['collapse-'+lang, {show: lang === config.tabs[0]}]"
                 v-for="lang in field.languages">
              <label>{{ trans(lang) }}</label>
              <input :value="object[id]"
                     v-model="object[id][lang]"
                     class="form-control"
                     :class="field.classes"
                     :type="field.type"
                     :id="id+'['+lang+']'"
                     :name="id+'['+lang+']'"/>
            </div>
          </template>
          <template v-else>
            <input :value="field.type == 'password' ? '' : object[id]" v-model="object[id]" class="form-control" :class="field.classes" :type="field.type" :id="id" :name="id"/>
          </template>
        </template>

        <template v-if="['date','datetime'].includes(field.type)">
          <div class="position-relative">
            <datetime :type="field.type"
                      :format="field.type == 'datetime' ? 'yyyy-MM-dd HH:mm' : 'yyyy-MM-dd'"
                      zone="Asia/Almaty"
                      :value-zone="'Asia/Almaty'"
                      :hour-step="1"
                      :minute-step="5"
                      :auto="true"
                      :phrases="{ok: 'Ок','cancel': 'Отмена'}"
                      v-model="date_rep"
                      class="form-control"
                      :class="field.classes"
                      :id="id"
                      :name="id"
                      :input-id="id">
            </datetime>
            <span @click="date_rep = null" style="position: absolute; right: 10px;top: 50%;transform: translateY(-50%);z-index: 2; cursor: pointer; display: inline-block; padding: 5px;opacity: 0.5">x</span>
          </div>
        </template>

        <template v-if="field.type  == 'color'">
          <div class="row no-gutters align-items-end">
            <div class="col">
              <input type="text" v-model="object[id]"  class="form-control" :id="id" :name="id"/>
            </div>
            <div class="col-auto ps-2">
              <input :value="object[id]" style="width: 50px;" v-model="object[id]" class="form-control" :class="field.classes" :type="field.type"/>
            </div>
            <div class="col ps-2">
              <a tabindex="#" @click="object[id] = null" class="text-underlined text-muted mt-1 d-inline-block pointer"><small>Сбросить цвет</small></a>
            </div>
          </div>
        </template>

        <div v-if="field.formnote">
          <small class="text-muted">{{ field.formnote }}</small>
        </div>

      </div>
    </template>

  </div>

</template>

<script>
import moment from 'moment'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'
import { quillEditor } from 'vue-quill-editor'

export default {
  components: {
    quillEditor
  },
  mounted() {
    let _this = this
    $(function() {
      _this.selected_files = {}
      // $('.form-library-datepicker').datetimepicker({format: 'yyyy-mm-dd', autoclose: true, language: 'ru'})
      // $('.form-library-datetimepicker').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true, language: 'ru'})
      $('.select2-candidate:not(.autocomplete-select)').select2()
      $('.ckeditorcandidate').ckeditor()
      // $(".modal, .cke_dialog_background_cover").removeAttr("tabindex").attr('tabindex', '');
      window.launchAutocomplete()
    })
  },
  methods: {
    mediaMove(id) {
      axios.post('/admin/eloquent/'+this.model+'/'+this.object.id+'/media/move', {"_method": "patch", id: id}).then(response => {
        this.$emit('mediaChange',response.data)
      })
    },
    mediaDelete(id) {
      if(confirm("Вы действительно хотите удалить файл?")) {
        let wrapper = $('.media-image-wrapper[data-id="' + id + '"]');
        wrapper.hide();
        axios.post('/admin/media/delete', {"_method": "delete", id: id}).then(function (response) {
          wrapper.remove();
        }).catch(function (error) {
          wrapper.show();
          alert(error);
        });
      }
    },
    fileChange(id, event) {
      Vue.set(this.selected_files, id, event.target.files.length)
    },
    editorJSReady(id, lang) {

    },
    changeEditorJS(id, lang) {
      let refid = 'editorjs-'+lang
      let editor = this.$refs[refid][0]
      this.selected_editor_id = id+'['+lang+']'
      editor.save(id)
    },
    saveEditorJS(data, id) {
      $('input[name="'+this.selected_editor_id+'"]').val(JSON.stringify(data))
    },
    dateToTime(date) {
      return '2020-02-22T12:20:00.001Z'
    }
  },
  computed: {
    collapseClass() {
      if(this.field.tab && this.config.tabs) {
        if(Array.isArray(this.field.tab)) {
          let str = ''
          this.field.tab.forEach(item => {
            str += ' collapse-'+item
          })
          return str
        } else {
          return 'collapse-'+this.field.tab
        }
      }
      return 'collapse-general show '
    }
  },
  watch: {
    object(val, old) {
      if(val.id !== old.id) {
        this.selected_files = {}
      }
      setTimeout(function() {
        $('.select2-candidate').select2()
      },20)
    }
  },
  props: {
    object: { required: true },
    field: { required: true },
    id: { required: true },
    config: { required: true },
    media: { required: true },
    model: { required: true },
    langs: {},
    select_models: { required: true },
  },
  data: function() {
    return {
      selected_files: {},
      selected_editor_id: null,
      date_rep: ['date','datetime'].includes(this.field.type) ? moment(this.object[this.id]).toISOString() : null,
      quillOptions: {
        tagName: "div",
        placeholder: '...',
        modules: {
          toolbar: {
            container:
                [
                  'bold',
                  'italic',
                  'underline',
                  'strike',
                  { 'list': 'ordered'},
                  { 'list': 'bullet' },
                  { 'indent': '-1'},
                  { 'indent': '+1' },
                  { 'color': [] },
                  { 'background': [] },
                  { 'align': [] },
                  'clean',
                  'image',
                  'showHtml'
                ],
            handlers: {
              showHtml: () => {
                if (quill.txtArea.style.display === "") {
                  const html = quill.txtArea.value;
                  if (html === '<p><br/></p>') {
                    quill.html = null;
                  } else {
                    quill.html = html.replace(new RegExp('<p><br/>', 'g'), '<p>')
                  }
                  quill.pasteHTML(html);
                }
                quill.txtArea.style.display =
                    quill.txtArea.style.display === "none" ? "" : "none";
              }
            }
          },
          // clipboard: {
          //     matchers: [
          //         [ Node.ELEMENT_NODE, function(node, delta) {
          //             var plaintext = node.innerText
          //             var Delta = Quill.import('delta')
          //             return new Delta().insert(plaintext)
          //             return delta.compose(new Delta().retain(delta.length()))
          //         }]
          //     ]
          // },
          imageUpload: {
            url: '/admin/quill/image', // server url. If the url is empty then the base64 returns
            method: 'POST', // change query method, default 'POST'
            name: 'image', // custom form name
            headers: {
              'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
            },
            callbackOK: (serverResponse, next) => {
              next(serverResponse);
            },
            callbackKO: serverError => {
              console.log(serverError)
              alert("Ошибка при загрузке файла");
            }
          },
        }
      }
    }
  }
}
</script>

<style scoped>
.media-move-button {
  right: 22px;
  border: 1px solid grey;
  color: grey;
  background: #fff;
}
</style>
