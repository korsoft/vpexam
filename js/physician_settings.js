var currentFile;
var physicianId = -1;
var $uploadProgressLbl;
var $uploadProgress;
var $inputHomePhone, $inputWorkPhone, $inputCellPhone, $homePhoneSpan, $workPhoneSpan, $cellPhoneSpan;

// From https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/keys
if (!Object.keys) {
    Object.keys = (function() {
        'use strict';
        var hasOwnProperty = Object.prototype.hasOwnProperty,
            hasDontEnumBug = !({ toString: null }).propertyIsEnumerable('toString'),
            dontEnums = [
                'toString',
                'toLocaleString',
                'valueOf',
                'hasOwnProperty',
                'isPrototypeOf',
                'propertyIsEnumerable',
                'constructor'
            ],
            dontEnumsLength = dontEnums.length;

        return function(obj) {
            if (typeof obj !== 'object' && (typeof obj !== 'function' || obj === null))
                throw new TypeError('Object.keys called on non-object');

            var result = [], prop, i;

            for (prop in obj) {
                if (hasOwnProperty.call(obj, prop))
                    result.push(prop);
            }

            if (hasDontEnumBug) {
                for (i = 0; i < dontEnumsLength; i++) {
                    if (hasOwnProperty.call(obj, dontEnums[i]))
                        result.push(dontEnums[i]);
                }
            }
            return result;
        };
    }());
}

$(document).on('ready', function() {
    navigator.sayswho = (function(){
        var ua= navigator.userAgent, tem,
            M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
        if(/trident/i.test(M[1])){
            tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
            return 'IE '+(tem[1] || '');
        }
        if(M[1] === 'Chrome'){
            tem= ua.match(/\bOPR\/(\d+)/)
            if(tem!= null) return 'Opera '+tem[1];
        }
        M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
        if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
        return M[0];
    })();

    $(".tab-container").easytabs({
        animationSpeed: 'fast'
    });

    var browser = navigator.sayswho.toLowerCase();

    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });
    $('.toggle-button').on('click', function() {
        slideout.toggle();
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
        // Already here
        location.href = location.href;
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    $('.waitingRoom ul li label').on('click', function() {
        var calling = {
            id   : $(this).parent().data('id'),
            name : $(this).text()
        };
        $('#calling').val(JSON.stringify(calling));
        $('#chat').click();
    });

    $('#btnGenerateBAA').on('click', function() {
        if (!validateBAAForm())
            alert("There are some incomplete fields. Please complete the fields marked in red before generating the BAA.");
        else
            generateBAA();
    });

    $("#inputBAAName, #inputBAANPI, #inputBAACoveredEntity, #selBAAState, #selBAABusinessType, #inputBAAOtherBusinessType, #inputBAAOrgType, #inputBAAAddr, #inputBAATitle").on('focus', function() {
        $(this).removeClass('incomplete');
    });

    $('#selBAABusinessType').change(function() {
        if ($(this).val() === "O")
            $('#trBAAOtherBusinessType').fadeIn('300');
        else
            $('#trBAAOtherBusinessType').fadeOut('300');
    });

    $('.cropBoxContainer > img').cropper({
        aspectRatio: 1
    });

    $uploadProgressLbl = $('.progress-label');

    $uploadProgress = $('#uploadProgress').progressbar({
        value: false,
        change: function() {
            $uploadProgressLbl.text($uploadProgress.progressbar('value') + '%');
            var lblLeft = Math.floor($uploadProgress.width() / 2) - Math.floor($uploadProgressLbl.width() / 2);
            $uploadProgressLbl.css({
                left: lblLeft + 'px'
            });
        }
    });

    $('#fileChooser, .dropContainer').fileReaderJS({
        accept: 'image/*',
        dragClass: 'dropContainerHover',
        readAsMap: {
            'image/*': 'DataURL'
        },
        on: {
            loadend: function(e, file) {
                currentFile = file;
                $('.outerDropContainer').hide(300);
                $('.cropBoxOuterContainer').show(300);
                $('.cropBoxContainer > img').cropper('replace', e.target.result);
            },
            skip: function(e, file) {
                alert("The file you chose could not be loaded because it is not a valid image file.");
    }
        }
    });

    tinymce.init({
        selector: '#taNormalText',
        menubar: false,
        toolbar: ["undo redo | bold italic underline | fontselect | fontsizeselect"],
        fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt",
        height: '350px',
        width: '100%',
        setup: function(editor) {
            editor.on('init', function(e) {
                ajaxLoadNormal();
            });
        }
    });

    $('.dropContainer').on('click', function() {
        $('#fileChooser').click();
    }).on('dragenter', function() {
        $(this).empty().append('<span class="dropContainerText">Release Mouse To Upload Image</span>');
    }).on('dragleave', function() {
        $(this).empty().append('<span class="dropContainerText">Click Here To Choose An Image</span><br /><span class="dropContainerText">OR</span><br /><span class="dropContainerText">Drag And Drop An Image Here</span>');
    }).on('dragover', function() {
        $(this).empty().append('<span class="dropContainerText">Release Mouse To Upload Image</span>');
    }).on('drop', function() {
        $(this).empty().append('<span class="dropContainerText">Click Here To Choose An Image</span><br /><span class="dropContainerText">OR</span><br /><span class="dropContainerText">Drag And Drop An Image Here</span>');
    });

    $('#btnNewImage').on('click', function() {
        $('.cropBoxOuterContainer').hide(300);
        $('.outerDropContainer').show(300);
    });

    $('#btnZoomIn').on('click', function() {
        $('.cropBoxContainer > img').cropper('zoom', 0.1);
    });

    $('#btnZoomOut').on('click', function() {
        $('.cropBoxContainer > img').cropper('zoom', -0.1);
    });

    $('#btnRotateCCW').on('click', function() {
        $('.cropBoxContainer > img').cropper('rotate', -90);
    });

    $('#btnRotateCW').on('click', function() {
        $('.cropBoxContainer > img').cropper('rotate', 90);
    });

    $('#btnCrop').on('click', function() {
        var data = $('.cropBoxContainer > img').cropper("getData");
        ajaxUpload(physicianId, currentFile, data);
    });

    $('#btnSaveNormal').on('click', function() {
        ajaxSaveNormal();
    });

    $('#btnResultOk').on('click', function() {
        $('.changeProfilePicDialog').dialog("close");
    });

    $('.changeProfilePicDialog').dialog({
        autoOpen: false,
        close: function(event, ui) {
            $('.cropBoxOuterContainer').hide();
            $('.loadingContainer').hide();
            $('.errorContainer').hide();
            $('.resultContainer').hide();
            $('.outerDropContainer').show();
        },
        dialogClass: 'cppDlg',
        resizable: false,
        width: 'auto'
    });

    $('input[type="range"]').addClass(browser);

    $('#btnChangeProfilePic').on('click', function() {
        $('.changeProfilePicDialog').dialog('open');
    });

    $('#btnEditPracticeAddr').on('click', function() {
        $('#practiceAddressDiv').hide(300);
        $('#btnEditPracticeAddr').hide(300);
        $('#practiceAddressDivEdit').show(300);
    });
    $('#btnSavePracticeAddr').on('click', function() {
        var name = $('#inputPracticeName').val();
        var addr = $('#inputPracticeAddr').val();
        var city = $('#inputPracticeCity').val();
        var state = $('#selPracticeState').val();
        var zip = $('#inputPracticeZip').val();

        if (name === "")
            $('#practiceName').hide();
        else
            $('#practiceName').text(name).show();

        $('#practiceAddr').text(addr);

        var cityStateZip = city + ', ' + state + '  ' + zip;
        $('#practiceCityStateZip').text(cityStateZip);

        ajaxUpdatePracticeAddr(name, addr, city, state, zip);
    });
    $('#btnCancelPracticeAddr').on('click', function() {
        $('#practiceAddressDivEdit').hide(300);
        $('#practiceAddressDiv').show(300);
        $('#btnEditPracticeAddr').show(300);
    });

    $('#dlgConfirmDeleteBAA').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width: 375,
        buttons: {
            "Delete": function() {
                $(this).dialog("close");
                deleteBAA();
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        }
    });

    $('#btnDeleteBAA').on('click', function() {
        $('#dlgConfirmDeleteBAA').dialog("open");
    });

    $('#btnDownloadBAA').on('click', function() {
        window.location = "includes/getBAAPDF.php?physId=" + physicianId;
    });

    $('#setExamComponentsDlg').dialog({
        autoOpen: false,
        close: function(event, ui) {
            $('#screenDimmer').css({display: 'none'});
            // Re-enable page scrolling on main page
            $('body').css({overflow: ''});
        },
        open: function(event, ui) {
            // Disable page scrolling on main page
            $('body').css({overflow: 'hidden'});
            // Load default exam components
            fetchExamComponents();
        },
        closeOnEscape: false,
        dialogClass: 'cppDlg',
        modal: true,
        resizable: false,
        width: '480px'
    });

    $('#btnSetExamComponents').on('click', function() {
        $('#setExamComponentsDlg').dialog('open');
    });

    $('#btnSaveComponents').on('click', function() {
        var selected = $('.cbExamComponent:checkbox:checked').map(function() {
            return this.id;
        }).get();
        var jsonStr = JSON.stringify(selected);
        saveExamComponents(jsonStr);
    });

    $('#inputMaxTime').focus(function() {
        $('#success_msg').hide();
    })

    $('#cbMaxTimeUnlimited').on('change', function() {
        $('#success_msg').hide();
        if (this.checked)
            $('#inputMaxTime').prop('disabled', true);
        else
            $('#inputMaxTime').prop('disabled', false);
    });

    $('#inputMaxTime').numeric({
        decimal: false,
        negative: false
    });

    if ($('#cbMaxTimeUnlimited').prop('checked'))
        $('#inputMaxTime').prop('disabled', true);

    $('#btnSaveMaxTime').on('click', function() {
        if (!($('#cbMaxTimeUnlimited').prop('checked')) && ($('#inputMaxTime').val() === "" || $('#inputMaxTime').val() === "0")) {
            alert("You must enter a value greater than 0 or check the \"unlimited\" box");
        } else {
            if ($('#cbMaxTimeUnlimited').prop('checked'))
                updateMaxStethRecordTime(-1);
            else
                updateMaxStethRecordTime($('#inputMaxTime').val())
        }
    });

    $('#inputNewPassword').qtip({
        content: {
            text: $('#qtipContent')
        },
        hide: {
            event: 'blur'
        },
        show: {
            event: 'focus'
        },
        style: 'qtip-tipsy'
    }).focus(function() {
        $(this).triggerHandler('showQtip');
    }).keyup(function() {
        var text = $(this).val();

        if (text.length > 0)
            $('#imgConfirmPwd').attr('src', 'img/red_x.png').css('display', 'block');
        else
            $('#imgConfirmPwd').css('display', 'none');

        if (text.length >= 8)
            $('#qtImg1').attr('src', 'img/green_check.png');
        else
            $('#qtImg1').attr('src', 'img/red_x.png');

        if (/^(?=.*[A-Z])/.test(text))
            $('#qtImg2').attr('src', 'img/green_check.png');
        else
            $('#qtImg2').attr('src', 'img/red_x.png');

        if (/^(?=.*[a-z])/.test(text))
            $('#qtImg3').attr('src', 'img/green_check.png');
        else
            $('#qtImg3').attr('src', 'img/red_x.png');

        if (/^(?=.*[0-9])/.test(text))
            $('#qtImg4').attr('src', 'img/green_check.png');
        else
            $('#qtImg4').attr('src', 'img/red_x.png');
    });

    $('#inputConfirmNewPassword').keyup(function() {
        if ($('#inputNewPassword').val() == $(this).val())
            $('#imgConfirmPwd').attr('src', 'img/green_check.png');
        else
            $('#imgConfirmPwd').attr('src', 'img/red_x.png');
    });

    $('#btnChangePassword').on('click', function() {
        var value = $('#inputConfirmNewPassword').val();
        if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/.test(value) || !(value.length >= 8)) {
            alert("Password does not meet specified criteria. Please check criteria.");
        } else {
            // Change password
            var oldPwd = hex_sha512($('#inputCurrentPassword').val());
            var newPwd = hex_sha512($('#inputConfirmNewPassword').val());

            $.ajax({
                async: true,
                data: 'u=physician&' + 'userId=' + physicianId + '&pwdHashedOld=' + oldPwd + '&pwdHashedNew=' + newPwd,
                error: function(xhr, textStatus, errorThrown) {
                    alert("Error processing request: " + textStatus + ": " + errorThrown);
                },
                method: 'POST',
                success: function(data) {
                    var result = JSON.parse(data);
                    if (!result.success) {
                        $('#inputCurrentPassword').val("");
                        $('#inputNewPassword').val("");
                        $('#inputConfirmNewPassword').val("");
                        $('#qtImg1, #qtImg2, #qtImg3, #qtImg4').attr('src', 'img/red_x.png');
                        $('#imgConfirmPwd').css('display', 'none');

                        alert("Error changing password: " + result.errorMsg);
                    } else {
                        $('#inputCurrentPassword').val("");
                        $('#inputNewPassword').val("");
                        $('#inputConfirmNewPassword').val("");
                        $('#qtImg1, #qtImg2, #qtImg3, #qtImg4').attr('src', 'img/red_x.png');
                        $('#imgConfirmPwd').css('display', 'none');
                        
                        alert("Password changed successfully!");
                    }
                },
                url: "api/changePassword.php"
            });
        }
    });

    checkHaveBAAOrNotNeeded();
});

function setPhysicianId(id) {
    physicianId = id;
}

function updateMaxStethRecordTime(time) {
    var target  = document.getElementById('spin'),
        spinner = new Spinner(opts).spin(target);
    $(target).data('spinner', spinner);

    $.ajax({
        async: true,
        data: 'physId=' + physicianId + '&time=' + time,
        error: function(xhr, textStatus, errorThrown) {
            $('#maxTimeLoadingOverlay').spin(false);
            alert("Error processing request: " + textStatus + ": " + errorThrown);
        },
        method: 'POST',
        success: function(data) {
            var result = JSON.parse(data);
            if (!result.success)
                alert("Error processing request: " + result.errorMsg);
            $('#spin').data('spinner').stop();
            $('#success_msg').show();
        },
        url: "api/updateMaxStethRecordTime.php"
    });
}

function fetchExamComponents() {
    $.ajax({
        async: true,
        error: function(xhr, textStatus, errorThrown) {
            alert("Error fetching exam components: " + textStatus + ": " + errorThrown);
        },
        method: 'GET',
        success: function(data) {
            var result = JSON.parse(data);
            if (!result.success) {
                alert("Error fetching exam components: " + result.errorMsg);
            } else {
                var components = result.examComponents;
                $.each(components, function(index, val) {
                    $('#' + val).prop('checked', true);
                });
            }
        },
        url: "api/getDefaultExamComponents.php?physId=" + physicianId
    });
}

function saveExamComponents(jsonStr) {
    $.ajax({
        async: true,
        data: "physId=" + physicianId + "&ecString=" + encodeURIComponent(jsonStr),
        error: function(xhr, textStatus, errorThrown) {
            alert("Error saving exam components: " + textStatus + ": " + errorThrown);
        },
        method: 'POST',
        success: function(data) {
            var result = JSON.parse(data);
            if (!result.success)
                alert("Error saving exam components: " + result.errorMsg);
            $('#setExamComponentsDlg').dialog('close');
        },
        url: "api/saveDefaultExamComponents.php"
    });
}

function deleteBAA() {
    $.ajax({
        async: true,
        method: 'GET',
        success: function(data) {
            var rspObj = JSON.parse(data);
            if (rspObj.success) {
                $('#pdfContainer').empty();
                $('#baaPDFContainer').hide();
                $('#baaInfoContainer').show();
            }
        },
        url: "includes/deleteBAA.php?physId=" + physicianId
    });
}

function checkHaveBAAOrNotNeeded() {
    $.ajax({
        async: true,
        method: 'GET',
        success: function(data) {
            var jsObj = JSON.parse(data);
            if (jsObj.needBAA && jsObj.haveBAA) {
                $('#cbNoBAA').prop('checked', false);
                $('#baaInfoContainer').css({
                    display: 'none'
                });

                $('#baaPDFContainer').css({
                    display: 'block'
                });

                renderPDF("https://vpexam.com/includes/getBAAPDF.php?physId=" + physicianId, document.getElementById("pdfContainer"));
            } else if (jsObj.needBAA && !jsObj.haveBAA) {
                $('#cbNoBAA').prop('checked', false);
            } else if (!jsObj.needBAA) {
                $('#cbNoBAA').prop('checked', true);
                $('#baaSettingsTable').fadeOut(300);
            }
            $('#cbNoBAA').change(function() {
                if ($(this).is(':checked')) {
                    updateBAANeeded(false);
                    $('#baaSettingsTable').fadeOut(300);
                } else {
                    updateBAANeeded(true);
                    $('#baaSettingsTable').fadeIn(300);
                }
            });
        },
        url: "includes/haveBAA.php?physId=" + physicianId
    });
}

function updateBAANeeded(needed) {
    $.ajax({
        async: true,
        method: 'GET',
        success: function(data) {
            var rspObj = JSON.parse(data);
            if (!rspObj.success)
                alert(rspObj.errorMsg);
        },
        url: "includes/updateNeedBAA.php?physId=" + physicianId + "&needBAA=" + needed.toString()
    });
}

function ajaxUpdatePracticeAddr(name, addr, city, state, zip) {
    $.ajax({
        async: true,
        beforeSend: function(jqxhr, settings) {
            jqxhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        },
        data: "id=" + physicianId + "&name=" + encodeURIComponent(name) + "&addr=" + encodeURIComponent(addr) + "&city=" + encodeURIComponent(city) + "&state=" + encodeURIComponent(state) + "&zip=" + encodeURIComponent(zip),
        error: function(xhr, textStatus, errorThrown) {
            alert("Error updating practice address: " + textStatus + ": " + error);
        },
        method: 'POST',
        success: function(data) {
            var rsp = JSON.parse(data);
            if (!rsp.success) {
                alert("Error updating practice address: " + rsp.error);
            } else {
                $('#practiceAddressDivEdit').hide(300);
                $('#practiceAddressDiv').show(300);
                $('#btnEditPracticeAddr').show(300);
            }
        },
        url: "api/updatePracticeAddress.php"
    });
}

function ajaxUpload(physId, file, cropData) {
    var cropDataStr = JSON.stringify(cropData);
    var data = new FormData();

    data.append('originalImage', file);
    data.append('cropData', cropDataStr);
    data.append('physicianId', physId.toString());

    $.ajax({
        url: 'util/crop.php',
        type: 'POST',
        data: data,
        processData: false,
        contentType: false,

        beforeSend: function(jqXHR, settings) {
            $('.cropBoxOuterContainer').hide(300);
            $('.loadingContainer').show(300);
        },

        success: function(data) {
            var rsp = JSON.parse(data);
            if (rsp.success) {
                $uploadProgressLbl.text("Upload Complete");
                var lblLeft = Math.floor($uploadProgress.width() / 2) - Math.floor($uploadProgressLbl.width() / 2);
                $uploadProgressLbl.css({
                    left: lblLeft + 'px'
                });
                $('.resultContainer').show(300);
                $('#cropResult').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=2');
                $('#profilePic').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=2');
            } else {
                $('#errorCode').text(rsp.error);
                $('.errorContainer').show(300);
            }
        },

        error: function(jqXHR, textStatus, error) {
            $('#errorCode').text(textStatus + ": " + error);
            $('.errorContainer').show(300);
        },

        progress: function(e) {
            if (e.lengthComputable) {
                var percentComplete = Math.round((e.loaded / e.total) * 100);
                $uploadProgress.progressbar("value", percentComplete);
            }
        }
    });
}

function ajaxLoadNormal() {
    tinymce.get('taNormalText').setProgressState(true);
    $.ajax({
        url: "api/getPhysicianNormal.php?physId=" + physicianId,
        success: function(data) {
            tinymce.get('taNormalText').setProgressState(false);
            var rspObj = JSON.parse(data);
            if (rspObj === null)
                alert("Error loading 'Normal' text: Returned JSON failed to parse.");
            else if (!rspObj.success)
                alert("Error loading 'Normal' text: " + rspObj.errorMsg);
            else
                tinymce.get('taNormalText').setContent(rspObj.text);
        },
        error: function(xhr, textStatus, errorThrown) {
            tinymce.get('taNormalText').setProgressState(false);
            alert("Error loading 'Normal' text: " + errorThrown);
        },
        method: 'GET'
    });
}

function ajaxSaveNormal() {
    tinymce.get('taNormalText').setProgressState(true);
    $.ajax({
        async: true,
        beforeSend: function(jqxhr, settings) {
            jqxhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        },
        data: "physId=" + physicianId + "&content=" + encodeURIComponent(tinymce.get('taNormalText').getContent()),
        error: function(xhr, textStatus, errorThrown) {
            tinymce.get('taNormalText').setProgressState(false);
            alert("Error saving clipboard: " + textStatus + ": " + error);
        },
        method: 'POST',
        success: function(data) {
            tinymce.get('taNormalText').setProgressState(false);
            var rsp = JSON.parse(data);
            if (!rsp.success) {
                alert(rsp.errorMsg);
            } else {
                $('#normalLastSaved').text("My Normal Exam saved at " + formatAMPM(new Date()));
            }
        },
        url: "api/savePhysicianNormal.php"
    });
}

function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours %= 12;
    hours = hours ? hours : 12; // The hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}

function removePhonePunctuation(phoneNum) {
    return phoneNum.replace(/[() x-]/g, "");
}

function validateBAAForm() {
    var incompleteElements = [];
    var otherBusinessType = false;
    if ($('#inputBAAName').val() === "")
        incompleteElements.push('#inputBAAName');
    if ($('#inputBAANPI').val() === "")
        incompleteElements.push('#inputBAANPI');
    if ($('#inputBAACoveredEntity').val() === "")
        incompleteElements.push('#inputBAACoveredEntity');
    if ($('#selBAAState').val() === "SEL")
        incompleteElements.push('#selBAAState');
    if ($('#selBAABusinessType').val() === "SEL")
        incompleteElements.push('#selBAABusinessType');
    else if ($('#selBAABusinessType').val() === "O")
        otherBusinessType = true;
    if (otherBusinessType && $('#inputBAAOtherBusinessType').val() === "")
        incompleteElements.push('#inputBAAOtherBusinessType');
    if ($('#inputBAAOrgType').val() === "")
        incompleteElements.push('#inputBAAOrgType');
    if ($('#inputBAAAddr').val() === "")
        incompleteElements.push('#inputBAAAddr');
    if ($('#inputBAATitle').val() === "")
        incompleteElements.push('#inputBAATitle');

    if (incompleteElements.length > 0) {
        for (var i = 0; i < incompleteElements.length; i++)
            $(incompleteElements[i]).addClass('incomplete');
    }

    return !(incompleteElements.length > 0);
}

function generateBAA() {
    $('#baaOverlay').css({
        'display': 'block',
        'height': $('#baaSettingsTable').height() + 'px',
        'width': $('#baaSettingsTable').width() + 'px'
    }).spin({
        lines: 10,
        length: 12,
        width: 8,
        radius: 12
    }, '#555');

    var currentDate = new Date();

    var params = {
        'physId': physicianId,
        'coveredEntity': encodeURIComponent($('#inputBAACoveredEntity').val()),
        'dateTop': encodeURIComponent(nth(currentDate.getDate()) + " day of " + monthName(currentDate.getMonth()) + ", " + currentDate.getFullYear()),
        'state': encodeURIComponent(articlePlusState($('#selBAAState').val())),
        'businessType': encodeURIComponent(($('#selBAABusinessType').val() === "O") ? $('#inputBAAOtherBusinessType').val() : $('#selBAABusinessType').val()),
        'addr': encodeURIComponent($('#inputBAAAddr').val()),
        'name': encodeURIComponent($('#inputBAAName').val()),
        'orgType': encodeURIComponent($('#inputBAAOrgType').val()),
        'posTitle': encodeURIComponent($('#inputBAATitle').val()),
        'npi': encodeURIComponent($('#inputBAANPI').val()),
        'dateBottom': encodeURIComponent(dateString(currentDate))
    };

    var queryStr = createQueryString(params);

    // Load the BAA template HTML
    $.ajax({
        async: true,
        beforeSend: function(jqxhr, settings) {
            jqxhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        },
        data: queryStr,
        error: function(xhr, textStatus, errorThrown) {
            $('#baaOverlay').css({
                'display': 'none'
            }).spin(false);
            alert("There was an error while trying to create BAA: " + textStatus);
        },
        method: 'POST',
        success: function(data) {
            $('#baaOverlay').css({
                'display': 'none'
            }).spin(false);

            var dataObj = JSON.parse(data);
            if (!dataObj.success) {
                alert("There was an error while trying to generate the BAA: " + dataObj.errorMsg);
                return;
            }

            $('#baaInfoContainer').css({
                display: 'none'
            });

            $('#baaPDFContainer').css({
                display: 'block'
            });

            renderPDF("https://vpexam.com/includes/getBAAPDF.php?physId=" + physicianId, document.getElementById("pdfContainer"));
        },
        url: 'api/generateBAA.php',
        type: 'POST'
    });
}

function renderPDF(url, canvasContainer, options) {
    var options = options || { scale: 1 };

    function renderPage(page) {
        var desiredWidth = 600;
        var viewport = page.getViewport(options.scale);
        var scale = desiredWidth / viewport.width;
        var scaledViewport = page.getViewport(scale);
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        canvas.height = viewport.height;
        canvas.width = viewport.width;

        canvasContainer.appendChild(canvas);

        page.render(renderContext);
    }

    function renderPages(pdfDoc) {
        for (var num = 1; num <= pdfDoc.numPages; num++)
            pdfDoc.getPage(num).then(renderPage);
    }

    PDFJS.disableWorker = true;
    PDFJS.getDocument(url).then(renderPages);
}

/**
 * Creates a query string from an object containing
 * names and values
 * @param params
 */
function createQueryString(params) {
    var numProperties = Object.keys(params).length;
    var counter = 0;

    var queryStr = '';

    for (var key in params) {
        if (counter == numProperties - 1)
            queryStr += (key + '=' + params[key]);
        else
            queryStr += (key + '=' + params[key] + '&');
        counter++;
    }

    return queryStr;
}

function nth(day) {
    if (day > 3 && day < 21)
        return day + 'th';
    switch (day % 10) {
        case 1:
            return day + 'st';
        case 2:
            return day + 'nd';
        case 3:
            return day + 'rd';
        default:
            return day + 'th';
    }
}

function monthName(month) {
    switch (month) {
        case 0:
            return "January";
        case 1:
            return "February";
        case 2:
            return "March";
        case 3:
            return "April";
        case 4:
            return "May";
        case 5:
            return "June";
        case 6:
            return "July";
        case 7:
            return "August";
        case 8:
            return "September";
        case 9:
            return "October";
        case 10:
            return "November";
        case 11:
            return "December";
        default:
            return "Unknown";
    }
}

function articlePlusState(state) {
    var firstLetter = state.substring(0, 1).toLowerCase();
    switch (firstLetter) {
        case 'a':
        case 'e':
        case 'i':
        case 'o':
            return 'an ' + state;
        default:
            return 'a ' + state;
    }
}

function dateString(date) {
    var dtStr = '';
    switch (date.getDay()) {
        case 0:
            dtStr += 'Sun ';
            break;
        case 1:
            dtStr += 'Mon ';
            break;
        case 2:
            dtStr += 'Tues ';
            break;
        case 3:
            dtStr += 'Wed ';
            break;
        case 4:
            dtStr += 'Thurs ';
            break;
        case 5:
            dtStr += 'Fri ';
            break;
        case 6:
            dtStr += 'Sat ';
            break;
    }

    switch (date.getMonth()) {
        case 0:
            dtStr += 'Jan ';
            break;
        case 1:
            dtStr += 'Feb ';
            break;
        case 2:
            dtStr += 'Mar ';
            break;
        case 3:
            dtStr += 'Apr ';
            break;
        case 4:
            dtStr += 'May ';
            break;
        case 5:
            dtStr += 'Jun ';
            break;
        case 6:
            dtStr += 'Jul ';
            break;
        case 7:
            dtStr += 'Aug ';
            break;
        case 8:
            dtStr += 'Sep ';
            break;
        case 9:
            dtStr += 'Nov ';
            break;
        case 10:
            dtStr += 'Dec ';
            break;
        case 11:
            dtStr += 'Jan ';
            break;
    }

    if (date.getDate() < 10)
        dtStr += ('0' + date.getDate() + ' ');
    else
        dtStr += (date.getDate() + ' ');

    dtStr += date.getFullYear();

    return dtStr;
}

var opts = {
  lines: 17 // The number of lines to draw
, length: 28 // The length of each line
, width: 14 // The line thickness
, radius: 42 // The radius of the inner circle
, scale: 1 // Scales overall size of the spinner
, corners: 1 // Corner roundness (0..1)
, color: '#000' // #rgb or #rrggbb or array of colors
, opacity: 0.25 // Opacity of the lines
, rotate: 0 // The rotation offset
, direction: 1 // 1: clockwise, -1: counterclockwise
, speed: 1 // Rounds per second
, trail: 60 // Afterglow percentage
, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
, zIndex: 2e9 // The z-index (defaults to 2000000000)
, className: 'spinner' // The CSS class to assign to the spinner
, top: '50%' // Top position relative to parent
, left: '50%' // Left position relative to parent
, shadow: false // Whether to render a shadow
, hwaccel: false // Whether to use hardware acceleration
, position: 'absolute' // Element positioning
};
