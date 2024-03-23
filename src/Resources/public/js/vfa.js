function updateVisitorData() {
    var visitorAjaxurl = document.getElementById('VisitorsData').getAttribute('data-visitorsAjax');
    // console.log('url: ', visitorAjaxurl);
    try {
        fetch( visitorAjaxurl, { method: 'GET' , headers: { 'X-Requested-With': 'XMLHttpRequest', } } )
            .then(response => response.json())
            .then(data => {
                // console.log('id: ', data.visitorBasics['id']);
                // console.log('name: ', data.visitorBasics['visitors_name']);
                // console.log('PageHitCountValue: ', data.visitorsValues[0]['PageHitCountValue']);
                document.querySelectorAll("#VisitorsOnlineCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['VisitorsOnlineCountValue'];
                });
                document.querySelectorAll("#TodayVisitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['TodayVisitCountValue'];
                });
                document.querySelectorAll("#TotalVisitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['TotalVisitCountValue'];
                });
                document.querySelectorAll("#TodayHitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['TodayHitCountValue'];
                });
                document.querySelectorAll("#TotalHitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['TotalHitCountValue'];
                });
                document.querySelectorAll("#YesterdayVisitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['YesterdayVisitCountValue'];
                });
                document.querySelectorAll("#YesterdayHitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['YesterdayHitCountValue'];
                });
                document.querySelectorAll("#PageHitCount").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['PageHitCountValue'];
                });
                document.querySelectorAll("#AverageVisits").forEach(el => {
                    el.innerHTML = data.visitorsValues[0]['AverageVisitsValue'];
                });
            })
        .catch( error => console.error('error:', error) );
    } catch (r) {

        return;
    }
}
// Fügen Sie diese Funktion hinzu, um den Timer alle 10 Sekunden zu starten
function startVisitorTimer() {
    var visitorUpdate = document.getElementById('VisitorsData').getAttribute('data-visitorsUpdate');
    setInterval(updateVisitorData, visitorUpdate);
}
// Starten Sie den Timer, wenn das Dokument geladen ist
document.addEventListener('DOMContentLoaded', startVisitorTimer);