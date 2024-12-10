//***** WINDOW FUNCTIONS *****//

window.Noty = require('noty');

window.noty = function(message = window.trans('success_message'), type = 'success') {
    new Noty({
        text: message,
        layout: 'bottomRight',
        progressBar: false,
        timeout: 5000,
        type: type
    }).show();
}

window.validationErrors = function(errors) {
    var string = "";
    for (var error in errors) {
        if (errors.hasOwnProperty(error)) {
            string += errors[error]+"<br>";
        }
    }
    return string;
}

window.formatNumber = function(value) {
    return numeral(value).format("0,0"); // displaying other groupings/separators is possible, look at the docs
}

window.loaderIcon = function(on = true) {
    let els = document.getElementsByClassName("vue-loader")
    if(els && els.length) {
        const el = els[0]
        if(on) {
            el.style.display = 'block';
            // $('.vue-loader').show();
        } else {
            el.style.display = 'none';
            // $('.vue-loader').hide();
        }    
    }
    
}


window.axios.interceptors.response.use(res => {
    window.loaderIcon(false)
    return res;
}, function (error) {
    window.loaderIcon(false)
    let status = error.response.status
    if(status === 422) {
        window.noty(window.validationErrors(error.response.data.errors),'alert')
    }
    if(status === 403) {
        window.noty(this.trans('not_enough_rights'),'alert')
    }
    if(status === 500) {
        window.noty(this.trans('something_wrong'),'alert')
    }
    return Promise.reject(error);
});

