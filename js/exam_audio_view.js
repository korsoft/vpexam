var examParts = [];
var examPartsVideo = [];
var currentIndex = parseInt(getParameterByName('idx'));
var physicianId = -1;
var examId = -1;
var patientId = -1;
var wavesurfer = null;
var currentExamPartAbbrev = "";
var intervalHandle = null;
var audioTimeElem = null;
var savedAudioTime = -1;
var savedAudioHeight = -1;
var audioURL = "";

var clipboardIntervalId = -1;

var ONE_MINUTE_MILLIS = 60000;

$(document).on("ready", function() {
    // Align the header text in the middle of the top bar
    var $headerText = $('.headerText');
    var widthSub = $headerText.width() / 2;
    var leftCSS = "calc(50% - " + widthSub + "px)";
    $headerText.css({
        left: leftCSS
    });

    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });

    tinymce.init({
        selector: '#taClipboardMain',
        menubar: false,
        toolbar: ["undo redo | bold italic underline | fontselect | fontsizeselect"],
        fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt",
        height: '350px',
        width: '99.5%',
        setup: function(editor) {
            editor.on('init', function(e) {
                ajaxLoad(patientId, examId);
            });
        }
    });

    $('.toggle-button').on('click', function() {
        slideout.toggle();
    });
    $('#waveform').resizable({
        handles: 's',
        stop: function(event, ui) {
            $.cookie('audioHeight', ui.size.height);
            // Save the current position so it can be restored
            var savedTime = wavesurfer.getCurrentTime();
            wavesurfer.destroy();
            loadAudio({
                loop: true,
                scale: 1000,
                scroll: false,
                height: ui.size.height,
                savedTime: savedTime
            });
        }
    });
    var $img = $('<img src="img/resize_handle.png">');
    $img.appendTo($('.ui-resizable-s'));

    $('#btnPlayPause').on('click', function() {
        if (wavesurfer.backend.isPaused()) {
            $(this).children().attr('src', 'img/pause.png');
            intervalHandle = setInterval(function() {
                var currentTimeS = Math.floor(wavesurfer.backend.getCurrentTime());
                var totalTimeS = Math.floor(wavesurfer.backend.getDuration());
                // Minutes and seconds
                var minsCurrent = ~~(currentTimeS / 60);
                var minsTotal = ~~(totalTimeS / 60);
                var secsCurrent = currentTimeS % 60;
                var secsTotal = totalTimeS % 60;

                // Output like "1:01" or "4:03:59" or "123:03:59"
                var timeCurrent = "";
                var timeTotal = "";

                timeCurrent += "" + minsCurrent + ":" + (secsCurrent < 10 ? "0" : "");
                timeCurrent += "" + secsCurrent;
                timeTotal += "" + minsTotal + ":" + (secsTotal < 10 ? "0" : "");
                timeTotal += "" + secsTotal;

                $('#audioTime').text(timeCurrent + " / " + timeTotal);
                wavesurfer.play();
            }, 250);
        } else {
            $(this).children().attr('src', 'img/play.png');
            clearInterval(intervalHandle);
            wavesurfer.pause();
        }
    });

    $('#volumeSlider').bind("slider:changed", function(event, data) {
        if (data.ratio <= 0.05)
            $('.volumeImgDiv').css('background-position', '0 0');
        else if (data.ratio <= 0.25)
            $('.volumeImgDiv').css('background-position', '0 -25px');
        else if (data.ratio <= 0.75)
            $('.volumeImgDiv').css('background-position', '0 -50px');
        else
            $('.volumeImgDiv').css('background-position', '0 -75px');
        wavesurfer.setVolume(data.ratio);
    });

    $('#btnZoomIn').on('click', function() {
        var minPxPerSec = wavesurfer.backend.params.minPxPerSec;
        if (minPxPerSec == undefined)
            minPxPerSec = 50;

        if (minPxPerSec + 200 > 4500)
            minPxPerSec = 4500;
        else
            minPxPerSec += 200;

        wavesurfer.zoom(minPxPerSec);
    });

    $('#btnZoomOut').on('click', function() {
        var minPxPerSec = wavesurfer.backend.params.minPxPerSec;
        if (minPxPerSec == undefined)
            minPxPerSec = 50;

        if (minPxPerSec - 200 < 50)
            minPxPerSec = 50;
        else
            minPxPerSec -= 200;

        wavesurfer.zoom(minPxPerSec);
    });

    $('#btnZoomFit').on('click', function() {
        wavesurfer.zoom(50);
    });

    $('#btnRw').on('click', function() {
        wavesurfer.skipBackward(1);
    });

    $('#btnFf').on('click', function() {
        wavesurfer.skipForward(1);
    });

    // Button listeners
    $('#btnJumpMain').on('click', function() {
        $('#jumpDialogMain').dialog("open");
    });

    $('#btnSaveClipboardMain').on('click', function() {
        ajaxSave(patientId, examId);
    });

    $('#jumpDialogMain').dialog({
        autoOpen: false,
        width: 'auto'
    });

    $('#cantMoveDialog').dialog({
        autoOpen: false,
        buttons: [
            {
                text: "Ok",
                click: function() {
                    $(this).dialog("close");
                }
            }
        ],
        modal: true
    });

    $('.jumpTableTr').on('click', function() {
        var abbrev = $(this).attr('id');
        var idx = 0;
        for (var i = 0; i < examParts.length; i++) {
            if (examParts[i] === abbrev) {
                idx = i;
                break;
            }
        }
        var isVideo = examPartsVideo[idx];
        var url = (isVideo ? "exam_video_view.php?" : "exam_audio_view.php?") + "patientId=" + patientId + "&examId=" + examId + "&title=" + title + "&abbrev=" + abbrev + "&idx=" + idx;
        window.location.href = url;
    });

    $('#btnPrevComponentMain').on('click', function() {
        if (currentIndex === 0) {
            var $cantMoveDialog = $('#cantMoveDialog');
            $cantMoveDialog.empty();
            var $text = $('<p>You cannot move back any farther. You are already at the first exam component.</p>');
            $text.appendTo($cantMoveDialog);
            $cantMoveDialog.dialog("option", "title", "Can't Move Back").dialog("open")
        } else {
            var idx = currentIndex - 1;
            var abbrev = examParts[idx];
            var isVideo = examPartsVideo[idx];
            var url = (isVideo ? "exam_video_view.php?" : "exam_audio_view.php?") + "patientId=" + patientId + "&examId=" + examId + "&abbrev=" + abbrev + "&idx=" + idx;
            window.location.href = url;
        }
    });

    $('#btnNextComponentMain').on('click', function() {
        if (currentIndex === (examParts.length - 1)) {
            var $cantMoveDialog = $('#cantMoveDialog');
            $cantMoveDialog.empty();
            var $text = $('<p>You cannot move forward any farther. You are already at the last exam component.</p>');
            $text.appendTo($cantMoveDialog);
            $cantMoveDialog.dialog("option", "title", "Can't Move Forward").dialog("open")
        } else {
            var idx = currentIndex + 1;
            var abbrev = examParts[idx];
            var isVideo = examPartsVideo[idx];
            var url = (isVideo ? "exam_video_view.php?" : "exam_audio_view.php?") + "patientId=" + patientId + "&examId=" + examId + "&abbrev=" + abbrev + "&idx=" + idx;
            window.location.href = url;
        }
    });

    $('#btnInsertNormal, #btnInsertNormalMain').on('click', function() {
        getNormal($(this).attr('id'));
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
        location.href = "settings.php";
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    loadAudio({
        height: $.cookie('audioHeight') === undefined ? 256 : parseInt($.cookie('audioHeight')),
        loop: true,
        scale: 50,
        scroll: false
    });
});

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function setExamParts(parts) {
    examParts = JSON.parse(decodeURIComponent(parts));
}

function setExamPartsVideo(parts) {
    examPartsVideo = JSON.parse(decodeURIComponent(parts));
}

function setPhysician(phys) {
    physicianId = phys;
}

function setExamId(id) {
    examId = id;
}

function setPatientId(id) {
    patientId = id;
}

function setAudioURL(url) {
    audioURL = decodeURIComponent(url);
}

function loadAudio(opts) {
    // Create Wavesurfer instance
    wavesurfer = Object.create(WaveSurfer);

    wavesurfer.on('ready', function() {
        // Set the time of the audio file
        var savedTime = opts.savedTime || 0;
        var currentTimeS = Math.floor(savedTime);
        var totalTimeS = Math.floor(wavesurfer.backend.getDuration());
        // Minutes and seconds
        var minsCurrent = ~~(currentTimeS / 60);
        var minsTotal = ~~(totalTimeS / 60);
        var secsCurrent = currentTimeS % 60;
        var secsTotal = totalTimeS % 60;

        // Output like "1:01" or "4:03:59" or "123:03:59"
        var timeCurrent = "";
        var timeTotal = "";

        timeCurrent += "" + minsCurrent + ":" + (secsCurrent < 10 ? "0" : "");
        timeCurrent += "" + secsCurrent;
        timeTotal += "" + minsTotal + ":" + (secsTotal < 10 ? "0" : "");
        timeTotal += "" + secsTotal;

        if (opts.savedTime != undefined)
            wavesurfer.seekTo(opts.savedTime / wavesurfer.getDuration());

        $('#audioTime').text(timeCurrent + " / " + timeTotal);

        // Set the volume slider to the current volume.
        $('#volumeSlider').simpleSlider("setRatio", wavesurfer.backend.getVolume());

        if (wavesurfer.backend.getVolume() <= 0.05)
            $('.volumeImgDiv').css('background-position', '0 0');
        else if (wavesurfer.backend.getVolume() <= 0.25)
            $('.volumeImgDiv').css('background-position', '0 -25px');
        else if (wavesurfer.backend.getVolume() <= 0.75)
            $('.volumeImgDiv').css('background-position', '0 -50px');
        else
            $('.volumeImgDiv').css('background-position', '0 -75px');

        $('#btnPlayPause').click();
    });

    wavesurfer.on('seek', function(progress) {
        var currentTimeS = Math.floor(wavesurfer.backend.getCurrentTime());
        var totalTimeS = Math.floor(wavesurfer.backend.getDuration());
        // Minutes and seconds
        var minsCurrent = ~~(currentTimeS / 60);
        var minsTotal = ~~(totalTimeS / 60);
        var secsCurrent = currentTimeS % 60;
        var secsTotal = totalTimeS % 60;

        // Output like "1:01" or "4:03:59" or "123:03:59"
        var timeCurrent = "";
        var timeTotal = "";

        timeCurrent += "" + minsCurrent + ":" + (secsCurrent < 10 ? "0" : "");
        timeCurrent += "" + secsCurrent;
        timeTotal += "" + minsTotal + ":" + (secsTotal < 10 ? "0" : "");
        timeTotal += "" + secsTotal;

        $('#audioTime').text(timeCurrent + " / " + timeTotal);
    });

    // Init & load audio file
    var options = {
        container: $('#waveform')[0],
        waveColor: 'violet',
        progressColor: 'purple',
        loaderColor: 'purple',
        cursorColor: 'navy',
        loop: opts.loop || false,
        minPxPerSec: opts.scale || 1000,
        scrollParent: opts.scroll || false,
        height: opts.height || 256
    };

    wavesurfer.init(options);

    // Load the audio from URL
    wavesurfer.load(audioURL);
}

function plus2Space(str) {
    return str.replace('+', / /g);
}

function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;	// The hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}

function getNormal(selector) {
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

function setCurrentExamPartAbbrev(abbrev) {
    currentExamPartAbbrev = abbrev;
}