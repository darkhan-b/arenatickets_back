import Datetime from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
import { Settings } from 'luxon'
Settings.defaultLocale = 'ru'
Vue.use(Datetime)

import Quill from 'quill'
import { ImageUpload } from 'quill-image-upload'
Quill.register('modules/imageUpload', ImageUpload)
// import VueDragSelector from "vue-drag-selector";
// Vue.use(VueDragSelector);

$(document).on('click','[data-toggle="admincollapse"]',function() {
    $('[data-toggle="admincollapse"]').removeClass('active');
    $(this).addClass('active');
    var target = $(this).data('target');
    $('.admincollapse').removeClass('show');
    $('.admincollapse'+target).addClass('show');
});

window.launchAutocomplete = function() {
    let elements = $(document).find('.autocomplete-select:not(.select2-hidden-accessible)');
    elements.each(function(i,sdd) {
        var el = $(sdd);
        el.select2({
            placeholder: "-",
            minimumInputLength: 0,
            allowClear: true,
            ajax: {
                url: '/admin/eloquent/'+el.data('model')+'/autocomplete/'+el.data('field'),
                dataType: 'json',
                method: 'get',
                delay: 250,
                data: function (params) {
                    return {
                        // _token: $('meta[name=csrf-token]').attr('content'),
                        search: params.term
                    }
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    });

    elements.each(function(i,sdd) {
        var el = $(sdd);
        let selected = el.attr('data-selected');
        selected = JSON.parse(selected).map(x=>x.id)
        if(selected.length > 0) {
            // var newOption = new Option('-', '3', false, false);
            // el.append(newOption).trigger('change');
            axios.get('/admin/eloquent/'+el.data('model')+'/autocomplete/'+el.data('field'),{params: {ids: selected}}).then(res => {
                let data = res.data
                for (var d in data) {
                    if (data.hasOwnProperty(d)) {
                        var opt = data[d]
                        var newOption = new Option(opt.text, opt.id, true, true);
                        el.append(newOption).trigger('change');
                    }
                }
            });
        }
    });
}
