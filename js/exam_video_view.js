var examParts = [];
var examPartsVideo = [];
var currentIndex = parseInt(getParameterByName('idx'));
var physicianId = 0;
var patientId = 0;
var examId = 0;
var currentExamPartAbbrev = "";
var vid = null;
var jumpTableHTML = "";
var currentMousePos = { x: -1, y: -1 };
var isAnimating = false;

var fullscreenControlsLeft = screen.width - 55;

var fullscreenControlsBoundingRect = {
    top: (screen.height / 2) - 82.5,
    right: screen.width,
    bottom: (screen.height / 2) + 82.5,
    left: screen.width - 55
};

var clipboardIntervalId = -1;

var ONE_MINUTE_MILLIS = 60000;

var savedVideoHeight = -1;

$(document).on("ready", function() {
    // Align the header text in the middle of the top bar
    var $headerText = $('.headerText');
    var widthSub = $headerText.width() / 2;
    var leftCSS = "calc(50% - " + widthSub + "px)";
    $headerText.css({
        left: leftCSS
    });

    // Adjust the height of the video player if the video is in portrait orientation
    vid = videojs('examVid');
    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });
    $('.toggle-button').on('click', function() {
        slideout.toggle();
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
    var btnPlay = $("#btnPlay");
    vid.on('loadeddata', function() {
        var $videojs = $('.video-js');
        $videojs.resizable({
            handles: 's',
            minHeight: 100,
            stop: function(event, ui) {
                $.cookie('videoHeight', ui.size.height);
            }
        });
        var $img = $('<img src="img/resize_handle.png">');
        $img.appendTo($('.ui-resizable-s'));

        var videoHeight = $.cookie('videoHeight') === undefined ? ((screen.height * 0.8) + 'px') : ($.cookie('videoHeight') + 'px');

        $videojs.css({
            height: videoHeight
        });

        var $elem = $(
            '<div class="videoBoxContainer">' +
                '<div class="videoBoxHeader">Clipboard</div>' +
                '<div class="clipboardContainer">' +
                    '<textarea id="taClipboard"></textarea>' +
                '</div>' +
                '<div class="btnContainer" id="saveClipboardContainer">' +
                    '<span class="subTitle spanLastSaved">Last Saved: Not Saved</span>' +
                    '<div class="button-dark-modified" id="btnSaveClipboard">Save Clipboard</div>' +
                '</div>' +
                '<div class="insertNormalDiv">' +
                    '<div class="button-dark-modified" id="btnInsertNormal">Insert Normal</div>' +
                '</div>' +
            '</div>' +
            '<div class="vidBoxSlideHandle">' +
                '<div class="triangle triangle-right"></div>' +
            '</div>' +
            '<div class="emptyDiv"></div>' +
            '<div class="fullscreenControls">' +
                '<div class="outerBtnDiv" id="btnPrevComponent">' +
                    '<div class="button-dark-image-no-text">' +
                        '<img src="img/skip_prev.png">' +
                    '</div>' +
                '</div>' +
                '<div class="outerBtnDiv" id="btnJump">' +
                    '<div class="button-dark-image-no-text">' +
                        '<img src="img/jump.png">' +
                    '</div>' +
                '</div>' +
                '<div id="jumpDialog" title="Jump">' +
                    '<table class="jumpTable">' +
                    jumpTableHTML +
                    '</table>' +
                '</div>' +
                '<div class="outerBtnDiv" id="btnNextComponent">' +
                    '<div class="button-dark-image-no-text">' +
                        '<img src="img/skip_next.png">' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
        var boxWidth = screen.width * 0.3; // Width of the box is 30% of the screen width
        $($elem[0]).css({
            height: screen.height,
            width: boxWidth.toString() + 'px'
        });
        $($elem[1]).css({
            left: boxWidth.toString() + 'px'
        }).on('click', function() {
            var val = $('.videoBoxContainer').css('left') === "0px" ? -boxWidth : 0;
            var otherVal = $('.videoBoxContainer').css('left') === "0px" ? 0 : boxWidth;
            var widthVal = $('.videoBoxContainer').css('left') === "0px" ? '100%' : ((screen.width - boxWidth) + 'px');
            $('.videoBoxContainer').animate({
                left: val + 'px'
            });
            $('.vidBoxSlideHandle').animate({
                left: otherVal + 'px'
            }, {
                complete: function() {
                    if (otherVal === 0)
                        $('.triangle').removeClass('triangle-left').addClass('triangle-right');
                    else
                        $('.triangle').removeClass('triangle-right').addClass('triangle-left');
                }
            });
            $('.vjs-tech, .vjs-control-bar').animate({
                left: otherVal + 'px',
                width: widthVal
            });
        });
        vid.el().appendChild($elem[0]);
        vid.el().appendChild($elem[1]);
        vid.el().appendChild($elem[2]);
        vid.el().appendChild($elem[3]);
        $('#jumpDialog').dialog({
            autoOpen: false,
            width: 'auto',
            appendTo: '#examVid'
        });
        var tinymceHeight = (screen.height - 135) * 0.5;

        tinymce.init({
            selector: '#taClipboard',
            menubar: false,
            toolbar: ["undo redo | bold italic underline | fontselect | fontsizeselect"],
            fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt",
            height: tinymceHeight + 'px'
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

        // Button listeners
        $('#btnJump').on('click', function() {
            $('#jumpDialog').dialog("open");
        });

        $('#btnSaveClipboard, #btnSaveClipboardMain').on('click', function() {
            ajaxSave(patientId, examId, ($(this).attr('id') === 'btnSaveClipboard') ? 'taClipboard' : 'taClipboardMain');
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
            var url = (isVideo ? "exam_video_view.php?" : "exam_audio_view.php?") + "patientId=" + patientId + "&examId=" + examId + "&abbrev=" + abbrev + "&idx=" + idx;
            window.location.href = url;
        });

        $('#btnPrevComponent, #btnPrevComponentMain').on('click', function() {
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

        $('#btnNextComponent, #btnNextComponentMain').on('click', function() {
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

        $('.emptyDiv').on('mouseenter', function() {
            $('.fullscreenControls').animate({
                left: fullscreenControlsLeft + 'px'
            });
        }).on('mouseleave', function() {
            var $fullscreenControls = $('.fullscreenControls');
            if (!((currentMousePos.x > fullscreenControlsBoundingRect.left && currentMousePos.y < fullscreenControlsBoundingRect.right) &&
                (currentMousePos.y > fullscreenControlsBoundingRect.top && currentMousePos.y < fullscreenControlsBoundingRect.bottom))) {
                $fullscreenControls.animate({
                    left: screen.width + 'px'
                });
            }
        });
    });

    vid.on('fullscreenchange', function() {
        if (vid.isFullscreen()) {
            $('#jumpDialogMain').dialog("close");
            $('.videoBoxContainer').css({
                display: 'block'
            }).css({
                left: -$('.videoBoxContainer').height() + 'px'
            });
            $('.vidBoxSlideHandle').css({display: 'block', left: 0});
            var boxWidth = $('.videoBoxContainer').width();

            var clipboardHeight = $('.clipboardContainer').height() + 36;
            $('.btnContainer').css({
                top: clipboardHeight + 'px'
            });
            /*var controlsTitleTop = clipboardHeight + $('#saveClipboardContainer').height() + 20;
            $('#controlsTitle').css({
                top: controlsTitleTop + 'px'
            });
            var controlsTop = controlsTitleTop + $('#controlsTitle').height() + 5;
            $('.controlsContainer').css({
                top: controlsTop + 'px'
            });*/
            var insertNormalTop = clipboardHeight + $('#saveClipboardContainer').height() + 1;
            $('.insertNormalDiv').css({
                top: insertNormalTop + 'px'
            });
            $('.emptyDiv').css({
                display: 'block'
            });
            $('.fullscreenControls').css({
                display: 'block'
            });
            $(document).on('mousemove', function(event) {
                currentMousePos.x = event.pageX;
                currentMousePos.y = event.pageY;
            });

            // Synchronize the two clipboards
            tinymce.get('taClipboard').setContent(tinymce.get('taClipboardMain').getContent());
        } else {
            $(document).off('mousemove');
            $('#jumpDialog').dialog("close");
            $('.videoBoxContainer').css({
                display: 'none'
            });
            $('.vidBoxSlideHandle').css({
                display: 'none'
            });
            $('.triangle').removeClass('triangle-left').addClass('triangle-right');
            $('.vjs-tech').css({
                left: 0,
                width: '100%'
            });
            $('.vjs-control-bar').css({
                left: 0,
                width: '100%'
            });
            $('.emptyDiv').css({
                display: 'none'
            });
            $('.fullscreenControls').css({
                display: 'none'
            });
            // Synchronize the two clipboards
            tinymce.get('taClipboardMain').setContent(tinymce.get('taClipboard').getContent());
        }
    });

    // Button click listeners
    $('#btnJumpMain').on('click', function() {
        $('#jumpDialogMain').dialog("open");
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

function setPatientId(patient) {
    patientId = patient;
}

function setExamId(exam) {
    examId = exam;
}

function setJumpTableHTML(html) {
    jumpTableHTML = decodeParameter(html);
}

function plus2Space(str) {
    return str.replace('+', / /g);
}

function  formatAMPM(date) {
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
                if (selector === "btnInsertNormalMain")
                    tinymce.get('taClipboardMain').execCommand('mceInsertContent', false, text);
                else
                    tinymce.get('taClipboard').execCommand('mceInsertContent', false, text);
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

function ajaxSave(patientId, examId, selector) {
    tinymce.get(selector).setProgressState(true);
    $.ajax({
        async: true,
        beforeSend: function(jqxhr, settings) {
            jqxhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        },
        data: "patientId=" + patientId + "&examId=" + examId + "&physId=" + physicianId + "&content=" + encodeURIComponent(tinymce.get(selector).getContent()),
        error: function(jqxhr, textStatus, error) {
            $('.subTitle').text("Last Saved: Not Saved");
            tinymce.get(selector).setProgressState(false);
            alert("Error saving clipboard: " + textStatus + ": " + error);
        },
        method: 'POST',
        success: function(data, textStatus, jqxhr) {
            tinymce.get(selector).setProgressState(false);
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

/**
 * Conserve aspect ratio of the orignal region. Useful when shrinking/enlarging
 * images to fit into a certain area.
 *
 * @param {Number} srcWidth Source area width
 * @param {Number} srcHeight Source area height
 * @param {Number} maxWidth Fittable area maximum available width
 * @param {Number} maxHeight Fittable area maximum available height
 * @return {Object} { width, heigth }
 */
function calculateAspectRatioFit(srcWidth, srcHeight, maxWidth, maxHeight) {
    var ratio = Math.min(maxWidth / srcWidth, maxHeight / srcHeight);
    return { width: srcWidth * ratio, height: srcHeight * ratio };
}

function decodeParameter(str) {
    return decodeURIComponent(str.replace(/\+/g, ' '));
}