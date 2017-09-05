var patientGender = "";
var physicianId = -1;
var $modelImg;

$(document).on("ready", function() {
    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });

    $('.toggle-button').on('click', function() {
        slideout.toggle();
    });

    $modelImg = $('#modelImg');

    $('.trExam').each(function(idx, val) {
        $(val).on('click', function() {
            location.href = $(this).data('link');
        });
    });

    $('#dialogConfirmDelete').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: {
            "Delete Exam": function() {
                removeExam();
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        width: 350
    });

    $('#btnMenuPatientOverview').on('click', function() {
        var patientId = getParameterByName('patientId');
        location.href = ("patient_view.php?patientId=" + patientId);
    });

    $('#btnMenuPatientDem').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuHistory').on('click', function() {
        var patientId = getParameterByName('patientId');
        var examId = getParameterByName('examId');
        location.href = ("history.php?patientId=" + patientId + "&examId=" + examId);
    });

    $('#btnMenuExam').on('click', function() {
        location.href = location.href;
    });

    $('#btnMenuClipboard').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        location.href = "https://doxy.me/sign-in";
    });

    $('#btnMenuMyPatients').on('click', function() {
        location.href = "physician_main.php";
    });

    $('#btnMenuSearch').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    $('#btnRemoveExam').on('click', function() {
        $('#dialogConfirmDelete').dialog("open");
    });
});

function setPatientGender(gender) {
    patientGender = gender;
}

function setPhysicianId(id) {
    physicianId = id;
}

function trMouseOver(elem) {
    var src = "";
    switch (elem.id) {
        case 'htt':
            src = (patientGender == 'male') ? "images/Step1Man.gif" : "images/generalexamwoman.gif";
            break;
        case 'mm':
            src = (patientGender == 'male') ? "images/mouth.gif" : "images/mouth_w.gif";
            break;
        case 'aas':
            src = (patientGender == 'male') ? "images/rightaortic.gif" : "images/rightaortic_w.gif";
            break;
        case 'aps':
            src = (patientGender == 'male') ? "images/leftpulmonic.gif" : "images/leftpulmonic_w.gif";
            break;
        case 'ats':
            src = (patientGender == 'male') ? "images/tricuspidleft.gif" : "images/tricuspid_w.gif";
            break;
        case 'ams':
            src = (patientGender == 'male') ? "images/mitralsite.gif" : "images/mitralsite_w.gif";
            break;
        case 'ala':
            src = (patientGender == 'male') ? "images/leftabdomen.gif" : "images/leftabdomen_w.gif";
            break;
        case 'ara':
            src = (patientGender == 'male') ? "images/rightabdomen.gif" : "images/rightabdomen_w.gif";
            break;
        case 'alm':
            src = (patientGender == 'male') ? "images/leftpulmiddleback.gif" : "images/leftpulmiddleback_w.gif";
            break;
        case 'arm':
            src = (patientGender == 'male') ? "images/rightpulmiddleback.gif" : "images/rightpulmiddleback_w.gif";
            break;
        case 'all':
            src = (patientGender == 'male') ? "images/leftpulbaseback.gif" : "images/leftpulbaseback_w.gif";
            break;
        case 'arl':
            src = (patientGender == 'male') ? "images/rightpulbaseback.gif" : "images/rightpulbaseback_w.gif";
            break;
        case 'rjva':
            src = (patientGender == 'male') ? "images/rightantjug.gif" : "images/rightantjug_w.gif";
            break;
        case 'rjvl':
            src = (patientGender == 'male') ? "images/rightlatjug.gif" : "images/rightlatjug_w.gif";
            break;
        case 'ljva':
            src = (patientGender == 'male') ? "images/leftantjug.gif" : "images/leftantjug_w.gif";
            break;
        case 'ljvl':
            src = (patientGender == 'male') ? "images/leftlatjug.gif" : "images/leftlatjug_w.gif";
            break;
        case 'rhr':
            src = (patientGender == 'male') ? "images/rightpulapexback.gif" : "images/rightpulapexback_w.gif";
            break;
        case 'lhr':
            src = (patientGender == 'male') ? "images/leftpulapexback.gif" : "images/leftpulapexback_w.gif";
            break;
        case 'rleek':
            src = (patientGender == 'male') ? "images/rightknee.gif" : "images/rightknee_w.gif";
            break;
        case 'rleea':
            src = (patientGender == 'male') ? "images/rightshin.gif" : "images/rightshin_w.gif";
            break;
        case 'lleek':
            src = (patientGender == 'male') ? "images/leftknee.gif" : "images/leftknee_w.gif";
            break;
        case 'lleea':
            src = (patientGender == 'male') ? "images/leftshin.gif" : "images/leftshin_w.gif";
            break;
        case 'mv1':
        case 'mv2':
            src = (patientGender == 'male') ? "images/mv1_2_m.gif" : "images/mv1_2_w.gif";
            break;
    }

    $modelImg.attr("src", src);
}

function trMouseOut(elem) {
    $modelImg.attr("src", (patientGender == 'male') ? "images/male.jpg" : "female.jpg");
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function removeExam() {
    $.ajax({
        async: true,
        error: function(jqxhr, textStatus, error) {
            alert("There was an error while trying to delete the exam: " + textStatus + ": " + error);
        },
        method: 'GET',
        success: function(data, textStatus, jqxhr) {
            var rspObj = JSON.parse(data);
            if (rspObj.success) {
                var patientId = getParameterByName('patientId');
                location.href = ("patient_view.php?patientId=" + patientId);
            } else {
                alert("Error while deleting the exam: " + rspObj.errorMsg);
            }
        },
        url: "api/removeExam.php?physId=" + physicianId + "&patientId=" + getParameterByName("patientId") + "&examId=" + getParameterByName("examId")
    });
}