var patientId = -1;

$(document).ready(function(){
    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });

    $('.toggle-button').on('click', function() {
        slideout.toggle();
    });

    $('.trExam').each(function(idx, obj) {
        $(obj).on('click', function() {
            location.href = "exam_main.php?patientId=" + patientId + "&examId=" + $(this).attr('id');
        });
    });

    $('#btnMenuPatientOverview').on('click', function() {
        // Already here
        location.href = location.href;
    });

    $('#btnMenuPatientDem').on('click', function() {
        // TODO: Implement
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        location.href = "https://doxy.me/sign-in";
    });

    $('#btnMenuMyPatients').on('click', function() {
        location.href = "physician_main.php";
    });

    $('#btnMenuSearch').on('click', function() {
        location.href = "search.php";
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    $('.examDate').each(function(index, val) {
        $(this).html(moment.utc($(this).html(), 'MM/DD/YYYY hh:mm A').tz(moment.tz.guess()).format('MM/DD/YYYY h:mm A'));
    });
});

function setPatientId(id) {
    patientId = id;
}