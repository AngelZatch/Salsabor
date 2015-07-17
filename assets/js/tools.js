// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité 
$("*[date-today='true']").click(function(){
    var today = new moment().format("YYYY-MM-DD");
    $(this).parent().prev().val(today);
});