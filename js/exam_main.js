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
    var src = "/images/exam/component/" + elem.dataset.time + "/" + elem.id + "/" +  patientGender + ".gif";
    $modelImg.attr("src", src);
}

function trMouseOut(elem) {
    $modelImg.attr("src", (patientGender == 'male') ? "images/male.jpg" : "images/female.jpg");
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
