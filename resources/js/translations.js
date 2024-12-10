//***** TRANSLATION *****//

Vue.prototype.trans = (string, params = {}) => {
    return window.trans(string, params)
}


window.trans = function(string, params = {}) {
    // let str = _.get(window.i18n, string);
    let str = lodashGet(window.i18n, string);
    if(!str) {
        return string
    }
    for (let key in params) {
        if (params.hasOwnProperty(key)) {
            str = str.replace(':'+key,params[key])
        }
    }
    return str;
}

function lodashGet(obj, path, def) {
    var fullPath = path
        .replace(/\[/g, '.')
        .replace(/]/g, '')
        .split('.')
        .filter(Boolean);

    return fullPath.every(everyFunc) ? obj : def;

    function everyFunc(step) {
        return !(step && (obj = obj[step]) === undefined);
    }
}


Vue.prototype.transName = (object) => {
    let lang = window.lang
    if(typeof object === 'object') {
        return object[lang] ? object[lang] : (object['ru'] ? object['ru'] : '')
    }
    return object;
};
