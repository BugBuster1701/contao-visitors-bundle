document.addEventListener('DOMContentLoaded', function () {
    var iVCID = document.getElementById('VisitorsData').getAttribute('data-visitorsCategory');
    var sRoute = document.getElementById('VisitorsData').getAttribute('data-visitorsRouteScreenCount');
    var iWidth  = window.innerWidth  || (window.document.documentElement.clientWidth  || window.document.body.clientWidth);
    var iHeight = window.innerHeight || (window.document.documentElement.clientHeight || window.document.body.clientHeight);
    var sWidth  = screen.width;
    var sHeight = screen.height;
    var visitorurl = sRoute+'?vcid='+iVCID+'&scrw='+sWidth+'&scrh='+sHeight+'&scriw='+iWidth+'&scrih='+iHeight+'';
    try {
        console.log(visitorurl);
        fetch( visitorurl, { method: 'GET' , headers: { 'X-Requested-With': 'XMLHttpRequest', } } )
        .catch( error => console.error('error:', error) );
    } catch (r) {
        return;
    }

});
