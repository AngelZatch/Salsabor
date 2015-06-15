$(function(){
    if(Cookies.get('side-menu-collapse') == 'true'){
        $("#small-menu").toggle();
        $("#large-menu").toggle();
        $(".main").toggleClass("large");
    }
});

function toggleSideMenu(){
    $("#large-menu").toggle();
    $("#small-menu").toggle();
    $(".main").toggleClass("large");
    if(Cookies.get('side-menu-collapse') == 'true'){
        Cookies.set('side-menu-collapse', 'false');
    } else {
        Cookies.set('side-menu-collapse', 'true');
    }
}

function toggleListePlanning(){
    $("#display-liste").toggle();
    $("#display-planning").toggle();
}

function toggleRecurringOptions(){
    $("#recurring-options").toggle('600');
}

function toggleWeekHours(){
    $("#week-hours").toggle('600');
}

$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});