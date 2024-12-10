<template>
  <div :class="'eloquent-page-'+model">
    <div class="container-fluid" v-if="fade">

      <div class="row align-items-center mb-3">
        <div class="col">
          <h6 class="mb-0">
            {{ config.title }}
            <!--                        <a v-if="instruction" class="pointer" @click="launchInstruction">-->
            <!--                            <span id="instruction-placeholder">-->
            <!--                                <i class="ti-help-alt"></i>-->
            <!--                            </span>-->
            <!--                        </a>-->
          </h6>
        </div>

        <div class="col-auto" v-if="config.add || config.additional_header_links">
          <a tabindex="#" v-if="config.add && canAdd" class="btn btn-info btn-themed btn-fill" @click="modelAdd">Добавить</a>
          <a tabindex="#"  @click="modelExcel" v-if="config.excel" data-toggle="tooltip" title="" class="btn btn-sm btn-secondary text-white ms-2" data-original-title="Выгрузить в excel">
            <i class="ti-save-alt"></i>
          </a>
          <template v-if="$options.components[$options.filters.capitalize(model)+'HeaderComponent']">
            <component :is="$options.filters.capitalize(model)+'HeaderComponent'"></component>
          </template>

          <a v-if="config.checks && config.delete && canDelete" tabindex="#" @click="deleteChecked" data-toggle="tooltip" title="" class="d-inline-block btn btn-themed btn-danger ms-2 c-white" data-original-title="Удалить выделенные">
            <i class="ti-trash"></i>
          </a>
        </div>

      </div>
      <hr>



      <div class="dynamic-wrap">
        <div class="modal" id="eloquent-modal" role="dialog" aria-labelledby="eloquent-modalLabel">
          <div class="modal-dialog" :class="{'modal-lg': config.modal }" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="eloquent-modalLabel">{{ modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form-component v-on:formSubmit="formSubmit"
                                v-on:mediaChange="mediaChange"
                                :langs="available_langs"
                                :media="selected_media"
                                :model="model"
                                :select_models="select_models"
                                :object="selected_object"
                                :config="config"
                                v-if="selected_object">
                </form-component>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template v-if="$options.components[$options.filters.capitalize(model)+'ListComponent']">
        <component :is="$options.filters.capitalize(model)+'ListComponent'"></component>
      </template>

      <div v-if="config.description" v-html="config.description" class="alert alert-secondary"></div>

      <div class="row align-items-center justify-content-between">
        <div class="col-auto">
          <div class="mb-3 total-rows-number">Записей: {{ total_rows | formatNumber }}</div>
        </div>
        <div class="col-auto">
          <div v-html="paginator"></div>
        </div>
      </div>

      <div class="card overflow-wrapper">

        <table class="table table-striped admin-table mb-0">
          <thead>
          <tr v-cloak>
            <th v-if="config.checks"></th>
            <th v-for="(field, field_id) in tableFields" @click="startSorting(field_id, field)" class="filter-column sorting" :data-ord="field_id">{{ field.name }}</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-cloak>
            <td v-if="config.checks">
              <input type="checkbox" v-model="checkedall" @click="checkAll($event)"/>
            </td>
            <td v-for="(field, field_id) in tableFields">
              <select v-if="field.table_show" @change="searchChanged" v-model="search[field_id]" class="search-column form-control" :data-search="field_id">
                <option value="">Все</option>
                <option v-for="(v,k) in field.table_show" :value="k">{{ v | strip_tags }}</option>
              </select>
              <select v-else-if="field.table_filter_select" @change="searchChanged" v-model="search[field_id]" class="search-column form-control" :data-search="field_id">
                <option value="">Все</option>
                <option v-for="(v,k) in select_data[field_id]" :value="k">{{ v }}</option>
              </select>
              <input v-else-if="field.tablesearch" type="text" @keyup="searchChanged" v-model="search[field_id]" class="search-column form-control" :data-search="field_id">
            </td>
            <td></td>
          </tr>

          <tr v-for="(object,ind) in data" v-cloak :class="config.trclass ? config.trclass+'-'+object[config.trclass] : ''">
            <td v-if="config.checks">
              <input type="checkbox" @click="checkId(object.id,$event,ind)" :checked="checked.includes(object.id)" :data-ind="ind" :data-id="object.id" class="item-check"/>
            </td>
            <td v-for="(field, field_id) in tableFields">

              <template v-if="$options.components[$options.filters.capitalize(model)+$options.filters.capitalize(field_id)+'TDComponent']">
                <component :is="$options.filters.capitalize(model)+$options.filters.capitalize(field_id)+'TDComponent'" :object="object" :field="field" :field_id="field_id"></component>
              </template>

              <template v-else>
                <a v-if="config.fields[field_id].table_url" target="_blank" :href="$options.filters.tableUrl(config.fields[field_id].table_url,object)">
                  <td-component :field="field" :object="object" :field_id="field_id"></td-component>
                </a>

                <span v-else><td-component :field="field" :object="object" :field_id="field_id"></td-component></span>

              </template>

            </td>
            <td class="nobr" style="font-size: 0;">
                            <span v-if="config.additional_links && config.additional_links.length > 0">
                                <a v-for="link in config.additional_links"
                                   :href="$options.filters.tableUrl(link.url,object)"
                                   target="_blank"
                                   class="btn"
                                   v-html="$options.filters.tableUrl(link.title,object)"></a>
                            </span>

              <template v-if="$options.components[$options.filters.capitalize(model)+'TDLinkComponent']">
                <component :is="$options.filters.capitalize(model)+'TDLinkComponent'" :object="object"></component>
              </template>

              <a v-if="config.edit && canUpdate" data-toggle="tooltip" title="Редактировать" :data-id ="object.id" @click="modelEdit(object.id,ind)" class="model-edit btn hover-info">
                <i class="ti-pencil"></i>
              </a>
              <a v-if="config.duplicate && canAdd" data-toggle="tooltip" title="Дублировать" :data-id ="object.id" @click="modelDuplicate(object.id,ind)" class="model-duplicate btn hover-info">
                <i class="ti-layers"></i>
              </a>
              <a v-if="config.delete && canDelete" data-toggle="tooltip" title="Удалить" :data-id="object.id" @click="modelDelete(object.id,ind)" class="model-delete btn hover-red">
                <i class="ti-trash"></i>
              </a>
            </td>
          </tr>
          </tbody>
        </table>

      </div>

      <div class="mt-4">
        <div v-html="paginator"></div>
      </div>


      <div v-if="instruction" class="modal" id="instruction-modal" role="dialog" aria-labelledby="instruction-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="instruction-modalLabel">Инструкция</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div v-html="instruction.body"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal" id="custommodal" role="dialog" aria-labelledby="custommodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="custommodalLabel">Детали</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="custommodalbody">
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script>

import EventBus from "../../../event-bus"

export default {
  mounted() {
    this.loadConfig("with_reload")
    // this.loadInstructions()
    this.loadEnv()

    let _this = this
    $(document).on('click','.page-link',function(event) {
      _this.page = $(this).data('page')
      _this.reload()
    });

    EventBus.$on('reloadEloquent', this.reload)
  },
  methods: {
    checkAll($event) {
      const check = $($event.target).prop('checked');
      this.checked = []
      if(check) {
        this.data.forEach(item => this.checked.push(item.id))
      }
    },
    checkId(id, $event, index) {
      let _this = this
      const check = $($event.target).prop('checked');
      if($event.shiftKey) {
        this.checked = []
        let smaller = index > this.lastchecked ? this.lastchecked : index
        let bigger = index > this.lastchecked ? index : this.lastchecked
        $('.item-check').each(function() {
          var ind = $(this).data('ind')
          if(ind >= smaller && ind <= bigger) {
            if(check) {
              _this.checked.push($(this).data('id'))
            }
          }
        })
      } else {
        if(check) {
          this.checked.push(id)
        } else {
          const index = this.checked.indexOf(id)
          if (index > -1) {
            this.checked.splice(index, 1)
          }
        }
      }

      this.lastchecked = index

      this.checked = Array.from(new Set(this.checked))
    },
    loadEnv() {
      axios.get('/admin/env').then(res => {
        this.available_langs = res.data.LANGS
      })
    },
    loadConfig(with_reload = '') {
      axios.get('/admin/eloquent/'+this.model+'/config').then(res => {
        this.config = res.data
        if(this.config.initial_order) {
          this.sort = this.config.initial_order[0]
          this.order = this.config.initial_order[1]
        }
        if(with_reload === "with_reload") {
          this.reload()
        }
      })
    },
    reload: function(excel = false) {
      window.loaderIcon()
      let src = {}
      for(let i in this.search) {
        src[i] = this.search[i]
      }
      axios.get('/admin/eloquent/'+this.model+window.location.search, {
        params: {
          page: this.page,
          sort: this.sort,
          order: this.order,
          search: $.param(src),
          model: this.model
        }
      }).then(res => {
        this.data = res.data.list.data
        this.select_data = res.data.select_data
        this.paginator = res.data.paginator
        this.total_rows = res.data.total
        this.permissions = res.data.permissions
        this.fade = true
        this.sortingClasses()
        // if(this.$route.query.id) {
        //   this.modelEdit(this.$route.query.id)
        // }
        this.$nextTick(() => {
          $('[data-toggle="tooltip"]').tooltip()
          if(!this.eloquentModal) this.eloquentModal = new bootstrap.Modal(document.getElementById('eloquent-modal'), {
            keyboard: false,
            focus: false
          })
        })
      })
    },
    searchChanged: function() {
      let _this = this
      this.page = 1
      clearTimeout(this.timer)
      this.timer = setTimeout(function(object) {
        _this.reload()
      }, 400, this)
    },
    modelAddEdit(url) {
      window.loaderIcon()
      this.selected_object  = null
      this.selected_media = []
      axios.get(url).then(res => {
        this.selected_object =  res.data.object
        this.selected_media =  res.data.media
        this.select_models =  res.data.select_models
        this.eloquentModal.show()
        // $('#eloquent-modal').()
      })
    },
    modelAdd() {
      window.loaderIcon()
      this.selected_index = ''
      this.selected_object_id = null
      this.selected_operation = "add"
      const url = '/admin/eloquent/'+this.model+'/create'
      this.modelAddEdit(url)
    },
    modelEdit: function(model_id, index = null) {
      window.loaderIcon()
      this.selected_index = index
      this.selected_object_id = model_id
      this.selected_operation = "edit"
      const url = '/admin/eloquent/'+this.model+'/'+model_id+'/edit'
      this.modelAddEdit(url)
    },
    modelDelete: function(model_id, index) {
      if(confirm("Вы действительно хотите удалить материал?")) {
        window.loaderIcon()
        axios.post('/admin/eloquent/'+this.model+'/'+model_id,{"_method": "delete"}).then(res => {
          this.data.splice(index, 1)
          this.total_rows--
          window.noty(this.trans('success_message'),'success')
        });
      }
    },
    modelExcel() {
      let src = {};
      for(let i in this.search) {
        src[i] = this.search[i]
      }
      location.href = "/admin/eloquent/"+this.model+window.location.search+'?page'+this.page+'&sort='+this.sort+'&order='+this.order+'&model='+this.model+'&excel=1&search='+$.param(src)+"&additional="+this.additional
    },
    deleteChecked() {
      if(confirm("Вы действительно хотите удалить материал?")) {
        axios.post('/admin/eloquent/'+this.model+'/bulk/delete',{"_method": "delete", "ids": this.checked}).then(res => {
          window.noty(this.trans('success_message'),'success')
          this.checked = []
          this.reload()
          this.checkedall = false
        })
      }
    },
    startSorting: function(sort, field) {
      if(field.tablesort) {
        this.changeOrder(sort)
        this.sort = sort
        this.reload()
      }
    },
    sortingClasses: function() {
      $('.sorting').removeClass('sorting-asc').removeClass('sorting-desc');
      $('.sorting[data-ord="'+this.sort+'"]').addClass('sorting-'+this.order);
    },
    changeOrder: function(sort) {
      if(sort !== this.sort) {
        this.order = "asc"
      } else {
        if(this.order === "desc") {
          this.order = "asc"
        } else {
          this.order = "desc"
        }
      }
    },
    mediaChange(media) {
      console.log('emitted')
      this.selected_media = media
    },
    // loadInstructions() {
    //     this.instruction = null
    //     console.log(this.$route.path)
    //     axios.get('/admin/instructions',{params: {link: this.$route.path}}).then(res => {
    //         this.instruction = res.data
    //     })
    // },
    // launchInstruction() {
    //     $('#instruction-modal').modal()
    // },
    formSubmit() {
      window.loaderIcon()
      const form = $('#eloquent-form');
      var paramObj = {};
      let formData = new FormData()
      $.each(form.serializeArray(), function(x, kv) {
        if (paramObj.hasOwnProperty(kv.name)) {
          paramObj[kv.name] = $.makeArray(paramObj[kv.name]);
          paramObj[kv.name].push(kv.value);
        } else {
          formData.append(kv.name, kv.value)
        }
      });
      form.find("input[type=file]").each(function() {
        let t = $(this)[0]
        let uploadedFiles = t.files
        for( var i = 0; i < uploadedFiles.length; i++ ) {
          formData.append(t.name+'[' + i + ']', uploadedFiles[i])
        }
      })
      let url = '/admin/eloquent/'+this.model
      if(this.selected_operation === "edit") {
        url+= '/'+this.selected_object.id
        formData.append('_method', 'put')
      }

      axios.post(url,formData,{ headers: {'Content-Type': 'multipart/form-data'}}).then(res => {
        if(this.selected_operation === "add") {
          this.data.unshift(res.data)
          this.total_rows++
        }
        if(this.selected_operation === "edit") {
          Vue.set(this.data,this.selected_index,res.data)
        }
        this.eloquentModal.hide()
        // $('#eloquent-modal').modal('hide');
        window.noty(this.trans('success_message'),'success')
      })
    },
    modelDuplicate(model_id, index) {
      window.loaderIcon()
      axios.post('/admin/eloquent/'+this.model+'/'+model_id+'/clone').then(res => {
        this.data.unshift(res.data)
        window.noty(this.trans('success_message'),'success')
      })
    }
  },
  watch:{
    $route (to, from) {
      this.config = {}
      this.model  = this.$route.params.model
      this.additional  = this.$route.params.add
      this.data = []
      this.search = []
      this.selected_object = null
      this.selected_object_id = null
      this.page = 1
      this.fade = false
      this.paginator = ""
      this.sort = "id"
      this.order = "asc"
      this.total_rows = 0
      this.eloquentModal = null
      const query = this.$route.query
      if(query) {
        this.search = query
      }
      $('.tooltip').hide();
      this.loadConfig("with_reload")
    },
  },
  computed: {
    tableFields() {
      let fields = this.config.fields
      if(fields) {
        return Object.keys(fields)
            .filter(key => fields[key].table)
            .reduce((obj, key) => {
              obj[key] = fields[key]
              return obj }, {})
      }  else {
        return {}
      }
    },
    modalTitle() {
      return this.config.title + (this.selected_operation === 'edit' ? (' - редактирование id '+this.selected_object_id) : ' - добавление')
    },
    canAdd() {
      if(!this.config.permissions || !this.config.permissions.create) {
        return true
      }
      var arrayLength = this.config.permissions.create.length
      for (var i = 0; i < arrayLength; i++) {
        if(!this.permissions.includes(this.config.permissions.create[i])) {
          return false
        }
      }
      return true

    },
    canUpdate() {
      if(!this.config.permissions || !this.config.permissions.update) {
        return true
      }
      var arrayLength = this.config.permissions.update.length
      for (var i = 0; i < arrayLength; i++) {
        if(!this.permissions.includes(this.config.permissions.update[i])) {
          return false
        }
      }
      return true
    },
    canDelete() {
      if(!this.config.permissions || !this.config.permissions.delete) {
        return true
      }
      var arrayLength = this.config.permissions.delete.length
      for (var i = 0; i < arrayLength; i++) {
        if(!this.permissions.includes(this.config.permissions.delete[i])) {
          return false
        }
      }
      return true
    }
  },
  props: {
  },
  data: function() {
    return {
      model: this.$route.params.model,
      data: [],
      fade: false,
      paginator: "",
      sort: "id",
      order: "asc",
      page: 1,
      search: [],
      selected: [],
      total_rows: '',
      select_data: null,
      config: {},
      selected_index: '',
      selected_operation: '',
      select_models: '',
      selected_object: null,
      selected_object_id: null,
      selected_media: [],
      timer: null,
      instruction: null,
      additional: this.$route.params.add,
      checked: [],
      lastchecked: 0,
      checkedall: false,
      available_langs: [],
      permissions: null,
      eloquentModal: null

    }
  }
}
</script>


<style scoped>
.alert-secondary {
  border-radius: 0;
  background: #eee;
  border-color: #ddd;
}
</style>
