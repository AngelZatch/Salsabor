// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité (#today_possible)
function insertToday(){
    var today = new moment().format("YYYY-mm-DD");
    $("#today_possible").val(today);
}