(function () {
    var head = document.getElementsByTagName('head')[0];
    var host = 'https://api.topbilet.kz'
    var css = document.createElement('link');
    css.type = 'text/css';
    css.rel = 'stylesheet';
    css.href = `${host}/css/iframe.css?6`;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    css.onreadystatechange = handler;
    css.onload = handler;

    // Fire the loading
    head.appendChild(css);

    function handler() {

        document.body.addEventListener( 'click', function ( event ) {
            if( event.target.classList.contains('tb-widget')) {
                showIframe(
                    event.target.dataset.eventId,
                    event.target.dataset.timetableId,
                    event.target.dataset.hallId,
                    event.target.dataset.language,
                    event.target.dataset.cl,
                    event.target.dataset.source,
                    event.target.dataset.tkn,
                );
            }
        });

        async function showIframe(event_id, timetable_id, hall_id, language, additional_class, source, tkn) {

            var body = document.getElementsByTagName('body')[0];
            var wrap = document.getElementsByClassName('aa-iframe-wrap');
            var url_part = 'widget#';
            if(event_id) {
                url_part += '/'+event_id;
            }
            if(timetable_id) {
                url_part += '/'+timetable_id;
            }
            var get_params = '?iframe=1';
            if(hall_id && hall_id > 0) { get_params += '&hall_id='+hall_id; }
            if(language) { get_params += '&lang='+language; }
            // if(fio) { get_params += '&fio='+fio; }
            // if(phone) { get_params += '&phone='+phone; }
            // if(email) { get_params += '&email='+email; }
            if(source) { get_params += '&source='+source; }
            if(additional_class) { get_params += '&additional_class='+additional_class; }
            if(tkn) {
                let res = await fetch(host+'/widget/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ tkn })
                });
                let json = await res.json();
                tkn = json.tkn;
                if(tkn) {
                    get_params += '&tkn='+tkn;
                    url_part = url_part.replace('widget#', 'widget')
                }
            }
            if(event_id) {
                get_params += '&show_id='+event_id;
            }

            let fullUrl = `${host}/${url_part}/${get_params}`

            if(window.innerWidth <= 768 && source == 'topbilet') {
                fullUrl += '&mobile=1'
                location.href = fullUrl // making iframe in modal on mobile makes browser crash
                return
            }

            if (wrap.length > 0) {
                wrap[0].style.display = 'block';
            } else {
                body.insertAdjacentHTML('afterbegin', '<div class="aa-iframe-wrap">' +
                    '<div class="aa-iframe-container '+additional_class+'">' +
                    '<div class="aa-header"></div>' +
                    '<a id="aa-close" onclick=""><img src="'+host+'/images/close.svg"/></a>' +
                    '<div class="aa-iframe-holder">' +
                    '<div id="aa-iframe-loader"></div>' +
                    '<iframe id="widget-iframe" src="'+fullUrl+'"></iframe>' +
                    '<div class="aa-close-frame"></div>' +
                    '</div></div></div>');
                document.body.classList.add('noscroll');
            }

            var iframe = document.getElementById('widget-iframe');
            var orderMade = null;

            window.addEventListener('message', function (e) { // getting messages from iframe
                const data = e.data;
                try {
                    const decoded = JSON.parse(data);
                    switch(decoded.action) {
                        case 'addClass':
                            iframe.classList.add(decoded.data)
                            break;
                        case 'removeClass':
                            iframe.classList.remove(decoded.data)
                            break;
                        case 'close':
                            close();
                            break;
                        case 'setOrder':
                            orderMade = decoded.data;
                    }
                } catch (e) {}
            });

            iframe.onload = function(e) {
                var $loader = document.getElementById('aa-iframe-loader');
                if($loader) $loader.remove()

                // define a function that sets min-height of my-element to window.innerHeight:
                const setHeight = () => {
                    const $el = document.getElementById("widget-iframe")
                    if($el) {
                        $el.style.minHeight = window.innerHeight + "px"
                    }
                };

                // define mobile screen size:
                let deviceWidth = window.matchMedia("(max-width: 768px)");
                let isIpad = (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 0) || navigator.platform === 'iPad';

                if (deviceWidth.matches || isIpad) {
                    // set an event listener that detects when innerHeight changes:
                    window.addEventListener("resize", setHeight);
                    // call the function once to set initial height:
                    setHeight();
                }
            };

            document.getElementById('aa-close').addEventListener("click", function () {
                if(orderMade) {
                    let res = confirm('Ваш заказ будет отменен. Вы уверены?');
                    if(!res) return;
                    fetch(`${host}/api/order/${orderMade.id}/${orderMade.hash}`, {
                        method: 'DELETE',
                        headers: {
                            'X-API-KEY': 'a2dOlfPITF63PnSpXVSdlPtiZZRVGf3BaNk4dgylRE3GMmJOrBjfXFvs5tGu'
                        }
                    }).then(response => response.json()).then(res => {
                        if(res) {
                            close();
                        }
                    });
                    // iframe.contentWindow.postMessage(JSON.stringify({
                    //     action: 'cancelOrder',
                    //     data: orderMade
                    // }), '*');
                    return;
                }
                close();
            });

            const close = () => {
                var elements = document.getElementsByClassName('aa-iframe-wrap');
                while (elements.length > 0) {
                    elements[0].parentNode.removeChild(elements[0]);
                }
                document.body.classList.remove('noscroll');
            }

        }

    }
})();

