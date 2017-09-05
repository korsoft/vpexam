var examParts = [];
var allExamPartsAbbrevs = [];
var allExamPartsNames = [];
var examPartsIndices = [];
var examPartsVideo = [];
var currentIndex = getParameterByName('idx');
var physicianId = 0;
var patientId = 0;
var examId = 0;
var currentExamPartAbbrev = "";
var vid = null;

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

    $('#copyDialog').dialog({
        autoOpen: false,
        resizable: false,
        width: 550
    });

    tinymce.init({
        selector: '#taClipboardMain',
        menubar: false,
        toolbar: ["undo redo | bold italic underline | fontselect | fontsizeselect"],
        fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt",
        height: (window.innerHeight - 330) + 'px',
        setup: function (editor) {
            editor.on('init', function (e) {
                ajaxLoad(patientId, examId);
            });
        }
    });

    $('#btnInsertNormalMain').on('click', function() {
        getNormal();
    });

    $('#btnSaveClipboardMain').on('click', function() {
        ajaxSave(patientId, examId);
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
        var patientId = getParameterByName('patientId');
        var examId = getParameterByName('examId');
        location.href = ("exam_main.php?patientId=" + patientId + "&examId=" + examId);
    });

    $('#btnMenuClipboard').on('click', function() {
        var patientId = getParameterByName('patientId');
        var examId = getParameterByName('examId');
        location.href = ("clipboard.php?patientId=" + patientId + "&examId=" + examId);
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

    $('#btnCopyPlainText').on('click', function() {
        $('#copyDialog').dialog("open");
        $('#copyText').val(tinymce.get('taClipboardMain').getContent({format: 'text'})).select();
    });

    $('#btnCopyHTML').on('click', function() {
        $('#copyDialog').dialog("open");
        $('#copyText').val(tinymce.get('taClipboardMain').getContent()).select();
    });

    $('#btnCopyDialogOk').on('click', function() {
        $('#copyDialog').dialog("close");
    });
});

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"), results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
function getNormal() {
    $.ajax({
        url: "api/getPhysicianNormal.php?physId=" + physicianId,
        success: function(data) {
            var rspObj = JSON.parse(data);
            if (rspObj === null) {
                alert("Error loading 'Normal' text: Returned JSON failed to parse.");
            } else if (!rspObj.success) {
                alert("Error loading 'Normal' text: " + rspObj.errorMsg);
            } else {
                var text = "<br />" + rspObj.text + "<br />";
                tinymce.get('taClipboardMain').execCommand('mceInsertContent', false, text);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            alert("Error loading 'Normal' text: " + errorThrown);
        },
        method: 'GET'
    });
}

function ajaxLoad(patientId, examId) {
    tinymce.get('taClipboardMain').setProgressState(true);
    $.ajax({
        async: true,
        error: function(jqxhr, textStatus, error) {
            tinymce.get('taClipboardMain').setProgressState(false);
            alert("Error loading clipboard: " + textStatus + ": " + error);
        },
        method: 'GET',
        success: function(data, textStatus, jqxhr) {
            tinymce.get('taClipboardMain').setProgressState(false);
            var rsp = JSON.parse(data);
            if (!rsp.success)
                alert(rsp.errorMsg);
            else
                tinymce.get('taClipboardMain').setContent(rsp.text);
        },
        url: "api/getClipboardContent.php?patientId=" + patientId + "&examId=" + examId + "&physId=" + physicianId
    });
}

function ajaxSave(patientId, examId) {
    tinymce.get('taClipboardMain').setProgressState(true);
    $.ajax({
        async: true,
        beforeSend: function(jqxhr, settings) {
            jqxhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        },
        data: "patientId=" + patientId + "&examId=" + examId + "&physId=" + physicianId + "&content=" + encodeURIComponent(tinymce.get('taClipboardMain').getContent()),
        error: function(jqxhr, textStatus, error) {
            $('.subTitle').text("Last Saved: Not Saved");
            tinymce.get('taClipboardMain').setProgressState(false);
            alert("Error saving clipboard: " + textStatus + ": " + error);
        },
        method: 'POST',
        success: function(data, textStatus, jqxhr) {
            tinymce.get('taClipboardMain').setProgressState(false);
            var rsp = JSON.parse(data);
            if (!rsp.success) {
                clearInterval(clipboardIntervalId);
                alert(rsp.errorMsg);
            } else {
                clearInterval(clipboardIntervalId);
                // Set the interval to update the clipboard last saved time
                clipboardIntervalId = setInterval(function() {
                    var lastSaved = Math.round(Date.now() / 1000) - rsp.lastModified;
                    if (lastSaved < 1) {
                        $('.subTitle').text("Last Saved: Just Now");
                    } else if (lastSaved === 1) {
                        $('.subTitle').text("Last Saved: 1 second ago");
                    } else if (lastSaved > 1 && lastSaved < 60) {
                        lastSaved = Math.ceil(lastSaved);
                        $('.subTitle').text("Last Saved: " + lastSaved + " seconds ago");
                    } else if (lastSaved === 60) {
                        $('.subTitle').text("Last Saved: 1 minute ago");
                    } else if (lastSaved > 60 && lastSaved < 3600) {
                        lastSaved = Math.ceil(lastSaved / 60);
                        $('.subTitle').text("Last Saved: " + lastSaved + " minutes ago");
                    } else if (lastSaved === 3600) {
                        $('.subTitle').text("Last Saved: 1 hour ago");
                    } else if (lastSaved > 3600 && lastSaved < 86400) {
                        lastSaved = Math.ceil(lastSaved / 60 / 60);
                        $('.subTitle').text("Last Saved: " + lastSaved + " hours ago");
                    } else if (lastSaved === 86400) {
                        $('.subTitle').text("Last Saved: 1 day ago");
                    } else if (lastSaved > 86400 && lastSaved < 432000) {
                        lastSaved = Math.ceil(lastSaved / 24 / 60 / 60);
                        $('.subTitle').text("Last Saved: " + lastSaved + " days ago");
                    } else if (lastSaved > 432000) {
                        $('.subTitle').text("Last Saved: over 5 days ago");
                    }
                }, ONE_MINUTE_MILLIS);
                var lastSaved = Math.round(Date.now() / 1000) - rsp.lastModified;
                if (lastSaved < 1) {
                    $('.subTitle').text("Last Saved: Just Now");
                } else if (lastSaved === 1) {
                    $('.subTitle').text("Last Saved: 1 second ago");
                } else if (lastSaved > 1 && lastSaved < 60) {
                    lastSaved = Math.ceil(lastSaved);
                    $('.subTitle').text("Last Saved: " + lastSaved + " seconds ago");
                } else if (lastSaved === 60) {
                    $('.subTitle').text("Last Saved: 1 minute ago");
                } else if (lastSaved > 60 && lastSaved < 3600) {
                    lastSaved = Math.ceil(lastSaved / 60);
                    $('.subTitle').text("Last Saved: " + lastSaved + " minutes ago");
                } else if (lastSaved === 3600) {
                    $('.subTitle').text("Last Saved: 1 hour ago");
                } else if (lastSaved > 3600 && lastSaved < 86400) {
                    lastSaved = Math.ceil(lastSaved / 60 / 60);
                    $('.subTitle').text("Last Saved: " + lastSaved + " hours ago");
                } else if (lastSaved === 86400) {
                    $('.subTitle').text("Last Saved: 1 day ago");
                } else if (lastSaved > 86400 && lastSaved < 432000) {
                    lastSaved = Math.ceil(lastSaved / 24 / 60 / 60);
                    $('.subTitle').text("Last Saved: " + lastSaved + " days ago");
                } else if (lastSaved > 432000) {
                    $('.subTitle').text("Last Saved: over 5 days ago");
                }
            }
        },
        url: "api/saveClipboardContent.php"
    });
}

function setExamParts(parts) {
    examParts = JSON.parse(decodeURIComponent(parts));
}

function setAllExamPartsAbbrevs(parts) {
    allExamPartsAbbrevs = JSON.parse(decodeURIComponent(parts));
}

function setAllExamPartsNames(parts) {
    allExamPartsNames = JSON.parse(decodeURIComponent(parts));
}

function setExamPartsIndices(parts) {
    examPartsIndices = JSON.parse(decodeURIComponent(parts));
}

function setPhysician(phys) {
    physicianId = phys;
}

function setPatientId(patient) {
    patientId = patient;
}

function setExamId(exam) {
    examId = exam;
}

function setJumpTableHTML(html) {
    jumpTableHTML = decodeParameter(html);
}

function setCurrentExamPartAbbrev(abbrev) {
    currentExamPartAbbrev = abbrev;
}

function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // The hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}