require('./bootstrap');
require('./translations');
require('./filters');
require('./window');

require('./subscribe');
require('./people');
require('./vacancy');

$(function() {
    $('#menu-toggler, #close-top-menu, #topmenu-shadow').click(function() {
        $('#topmenu').toggleClass('active'); 
        $('#topmenu-shadow').toggleClass('active'); 
    });

    AOS.init();
})
