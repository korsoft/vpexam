var currentFile;
var currentFileWRI;
var physicianId = -1;
var examComponentId = 0;
var $uploadProgressLbl;
var $uploadProgressLblWRI;
var $uploadProgress;
var $uploadProgressWRI;
var objComponents;
var $inputHomePhone, $inputWorkPhone, $inputCellPhone, $homePhoneSpan, $workPhoneSpan, $cellPhoneSpan;
var fileLimitSize = 700000;
var fSExt = new Array('Bytes', 'KB', 'MB', 'GB');

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

    $('.cropBoxContainerWRI > img').cropper({
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

    $uploadProgressLblWRI = $('.progress-labelWRI');

    $uploadProgressWRI = $('#uploadProgressWRI').progressbar({
        value: false,
        change: function() {
            $uploadProgressLblWRI.text($uploadProgressWRI.progressbar('value') + '%');
            var lblLeft = Math.floor($uploadProgressWRI.width() / 2) - Math.floor($uploadProgressLblWRI.width() / 2);
            $uploadProgressLblWRI.css({
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

    $('#fileChooserWRI, .dropContainerWRI').fileReaderJS({
        accept: 'image/*',
        dragClass: 'dropContainerHoverWRI',
        readAsMap: {
            'image/*': 'DataURL'
        },
        on: {
            loadend: function(e, file) {
                currentFileWRI = file;
                $('.outerDropContainerWRI').hide(300);
                $('.cropBoxOuterContainerWRI').show(300);
                $('.cropBoxContainerWRI > img').cropper('replace', e.target.result);
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

    $('.dropContainerWRI').on('click', function() {
        $('#fileChooserWRI').click();
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

    $('#btnNewImageWRI').on('click', function() {
        $('.cropBoxOuterContainerWRI').hide(300);
        $('.outerDropContainerWRI').show(300);
    });

    $('#btnZoomIn').on('click', function() {
        $('.cropBoxContainer > img').cropper('zoom', 0.1);
    });

    $('#btnZoomInWRI').on('click', function() {
        $('.cropBoxContainerWRI > img').cropper('zoom', 0.1);
    });    

    $('#btnZoomOut').on('click', function() {
        $('.cropBoxContainer > img').cropper('zoom', -0.1);
    });

    $('#btnZoomOutWRI').on('click', function() {
        $('.cropBoxContainerWRI > img').cropper('zoom', -0.1);
    });    

    $('#btnRotateCCW').on('click', function() {
        $('.cropBoxContainer > img').cropper('rotate', -90);
    });

    $('#btnRotateCCWWRI').on('click', function() {
        $('.cropBoxContainerWRI > img').cropper('rotate', -90);
    });    

    $('#btnRotateCW').on('click', function() {
        $('.cropBoxContainer > img').cropper('rotate', 90);
    });

    $('#btnRotateCWWRI').on('click', function() {
        $('.cropBoxContainerWRI > img').cropper('rotate', 90);
    });    

 $('#inputWRUrl').keypress(function (e) {
        //Allow only letters, numbers and _ -
        var regex = new RegExp(/^[a-zA-Z\b\_\-\d]+$/);
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);     
        if(e.keyCode == 37 || e.keyCode == 38 || e.keyCode == 39 || e.keyCode == 40) { // allow arrows keys
            return;
        }else if (regex.test(str)) {
            return true;
        }
        else {
            e.preventDefault();
            return false;
        }
    });

    $('#btnCrop').on('click', function() {
        var data = $('.cropBoxContainer > img').cropper("getData");
        ajaxUpload(physicianId, currentFile, data);
    });

    $('#btnCropWRI').on('click', function() {
        var data = $('.cropBoxContainerWRI > img').cropper("getData");
        ajaxUploadWRI(physicianId, currentFileWRI, data);
    });    

    $('#btnSaveNormal').on('click', function() {
        ajaxSaveNormal();
    });

    $('#btnResultOk').on('click', function() {
        $('.changeProfilePicDialog').dialog("close");
    });

    $('#btnResultOkWRI').on('click', function() {
        $('.changeProfilePicDialogWRI').dialog("close");
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

    $('.changeProfilePicDialogWRI').dialog({
        autoOpen: false,
        close: function(event, ui) {
            $('.cropBoxOuterContainerWRI').hide();
            $('.loadingContainerWRI').hide();
            $('.errorContainerWRI').hide();
            $('.resultContainerWRI').hide();
            $('.outerDropContainerWRI').show();
        },
        dialogClass: 'cppDlg',
        resizable: false,
        width: 'auto'
    });    

    $('input[type="range"]').addClass(browser);

    $('#btnChangeProfilePic').on('click', function() {
        $('.changeProfilePicDialog').dialog('open');
    });

    $('#btnChangeProfileWRI').on('click', function() {
        $('.changeProfilePicDialogWRI').dialog('open');
    });    

    $('#btnEditPracticeAddr').on('click', function() {
        $('#practiceAddressDiv').hide(300);
        $('#btnEditPracticeAddr').hide(300);
        $('#practiceAddressDivEdit').show(300);
    });
    
    $('#btnCreateExamComponents').on('click', function() {
        $('#success_msgEC').hide();
        resetExamComponents();
        $('#createExamComponentsDlg').show(300);
        $('#btnSetExamComponents').hide(300);
        $('#btnCreateExamComponents').hide(300);          
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

    $('#btnCancelCreateComponent').on('click', function() {
        $('#createExamComponentsDlg').hide(300);
        $('#btnSetExamComponents').show(300);
        $('#btnCreateExamComponents').show(300);  
        $('#success_msgEC').hide();
        resetExamComponents();       
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
    
    $('#dlgConfirmDeleteExCom').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width: 375,
        buttons: {
            "Delete": function() {
                $(this).dialog("close");
                deleteExamComponent($("#dlgConfirmDeleteExCom").data('idExComp'));
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
        closeOnEscape: true,
        dialogClass: 'cppDlg',
        modal: true,
        resizable: true,
        width: '480px',
        maxHeight: 600,
        position: { my: "center top", at: "center top" }
    });
   

    $('#btnSetExamComponents').on('click', function() {
        objComponents = null;
        $('#setExamComponentsDlg').dialog('open');
    });


    $('#inputWRUrl').focus(function() {
        $('#success_msgWR').hide();
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

    $('#btnWRUrl').on('click', function() {
        var regex = new RegExp(/^[a-zA-Z\b\_\-0-9]+$/);
        var regexAlpha = new RegExp(/^[a-zA-Z]/);
        var regexDoubleUn = new RegExp(/^(?!.*?__).*$/);
        var regexDoubleHyphen = new RegExp(/^(?!.*?--).*$/); 
        var regexDoubleUH = new RegExp(/^(?!.*?-_).*$/);
        var regexDoubleHU = new RegExp(/^(?!.*?_-).*$/);
        if ($('#inputWRUrl').val() === "") {
            alert("You must enter a valid URL");
        } 
        else if(!regex.test($('#inputWRUrl').val()))
        {
            alert("Please input alphanumeric characters only");
        }
        else if(!regexAlpha.test($('#inputWRUrl').val()))
        {
            alert("Url must start with letters");
        }    
        else if(100<$('#inputWRUrl').val().length)
        {
            alert("Url must be less than 100 characters.");
        } 
        else if(!regexDoubleUn.test($('#inputWRUrl').val()))
        {
            alert("Double special characters not allowed.");
        } 
        else if(!regexDoubleHyphen.test($('#inputWRUrl').val()))
        {
            alert("Double special characters not allowed.");
        }  
        else if(!regexDoubleUH.test($('#inputWRUrl').val()))
        {
            alert("Double special characters not allowed.");
        } 
        else if(!regexDoubleHU.test($('#inputWRUrl').val()))
        {
            alert("Double special characters not allowed.");
        }         
        else {
            updateWRUrl($('#inputWRUrl').val())
        }
    });    

    $('#btnSaveCreateComponent').on('click', function() {
        var regex = new RegExp(/^[a-zA-Z \b\_\.\:\-0-9]+$/);
        var regexabbrev = new RegExp(/^[a-z0-9\/\-\_]+$/);
        var blEmptyfield = false;
        var blSpeciaChar = false;
        var blSpeciaChar2 = false;        
        $('#inputComponentDesc, #inputComponentTitle').each(function() {
            if ($(this).val() == '') {
              blEmptyfield = true;
              $(this).parent().effect('shake', {times: 3}, 50);
            }
            else if(!regex.test($(this).val()))
            {
                blSpeciaChar=true;
                $(this).parent().effect('shake', {times: 3}, 50);
            }
        });
        $('#inputComponentAbbrev').each(function() {
            if ($(this).val() == '') {
              blEmptyfield = true;
              $(this).parent().effect('shake', {times: 3}, 50);
            }
            else if(!regexabbrev.test($(this).val()))
            {
                blSpeciaChar2=true;
                $(this).parent().effect('shake', {times: 3}, 50);
            }
        }); 
      if(blEmptyfield)
          alert('Please fill all required fields [Title,Abbrev,Desription]');
      else if(blSpeciaChar)
          alert("Please input alphanumeric characters only");  
      else if(blSpeciaChar2)
          alert("Please input only lowercase characters, numbers, - and _ ");  
      else if(2048<$('#inputComponentTitle').val().length)
      {
          alert("Exam component title must be less than 2048 characters.");
      } 
      else if(50<$('#inputComponentAbbrev').val().length)
      {
          alert("Exam component abbrev must be less than 50 characters.");
      }
      else if(4096<$('#inputComponentDesc').val().length)
      {
          alert("Exam component description must be less than 4096 characters.");
      }      
      else if( ($.trim($("#imgMaleModel").attr("src")) == "") && ($.trim($("#vidMaleModel source").attr("src")) == ""))
      {
          alert("Please select 3D male model image or video.");
      } 
      else if(($.trim($("#imgFemaleModel").attr("src")) == "") && ($.trim($("#vidFemaleModel source").attr("src")) == ""))
      {
          alert("Please select 3D female model image or video.");
      }       
      else
        saveExamComponent();
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
    
    $('#btnChangeImgMale').on('click', function() {
        $('#fileToUploadMale').trigger('click');
    });    
    
    $('#btnChangeImgFemale').on('click', function() {
        $('#fileToUploadFemale').trigger('click');
    });
    
    $('#btnChangeAudio').on('click', function() {
        $('#fileToUploadAudio').trigger('click');
    });    
        
    // AVI, FLV, WMV, MOV, MP4   
    $('#fileToUploadMale').on('change',function() {
        var preview = document.querySelector('#imgMaleModel');
        var fileM    = document.querySelector('#fileToUploadMale').files[0];
        var regex = new RegExp("(.*?)\.(png|jpeg|jpg|gif|avi|flv|wmv|mov|mp4|x-flv|x-ms-wmv|quicktime)$");
       
        if(!(regex.test(document.querySelector('#fileToUploadMale').files[0].type))) {
            alert('3D-model male image format is not supported');
        }
	    else if(0 >= document.querySelector('#fileToUploadMale').files[0].size ||  fileLimitSize < document.querySelector('#fileToUploadMale').files[0].size) {

            fSize = document.querySelector('#fileToUploadMale').files[0].size; 
            i=0;while(fSize>900){fSize/=1024;i++;}
            alert('3D-model male image size exceeds 700 Kb limit file size. Current file size = '+((Math.round(fSize*100)/100)+' '+fSExt[i]))+'.';
            $("#fileToUploadMale").val(null);
	    }
        else
        {
            var reader  = new FileReader();
            reader.addEventListener("load", function () {
                $('#vidMaleModel source').remove();
                $('#imgMaleModel').attr("src", '');
                //console.log(reader.result);

                var mimeType = reader.result.split(",")[0].split(":")[1].split(";")[0];
                console.log(mimeType);
                if(mimeType == 'video/quicktime' || mimeType == 'video/x-flv' || mimeType == 'video/mp4' || mimeType == 'video/x-ms-wmv' || mimeType == 'video/avi'){
                    var video = document.getElementById('vidMaleModel');

                    var source = document.createElement('source');
                    source.setAttribute('src', reader.result);
                    video.appendChild(source);
                    video.load();
                    console.log('vidMaleModel physician_settings.js');
                    $("#imgMaleModel").hide();
                    $("#vidMaleModel").show();
                }else{
                    preview.src = reader.result;
                    console.log('vidMaleModel physician_settings.js else');
                    $("#vidMaleModel").hide();
                    $("#imgMaleModel").show();
                }
            }, false);

            if (fileM) {
              reader.readAsDataURL(fileM);
            }
        }
    }); 
    
    $('#fileToUploadFemale').on('change',function() {
        var previewF = document.querySelector('#imgFemaleModel');
        var file    = document.querySelector('#fileToUploadFemale').files[0];
        var regex = new RegExp("(.*?)\.(png|jpeg|jpg|gif|avi|flv|wmv|mov|mp4|x-flv|x-ms-wmv|quicktime)$");
       
        if(!(regex.test(document.querySelector('#fileToUploadFemale').files[0].type))) {
            alert('3D-model female image format is not supported');
        }
	else if(0 >= document.querySelector('#fileToUploadFemale').files[0].size ||  fileLimitSize < document.querySelector('#fileToUploadFemale').files[0].size) {

            fSize = document.querySelector('#fileToUploadFemale').files[0].size; 
            i=0;while(fSize>900){fSize/=1024;i++;}
            alert('3D-model female image size exceeds 700 Kb limit file size. Current file size = '+((Math.round(fSize*100)/100)+' '+fSExt[i]))+'.';
            $("#fileToUploadFemale").val(null);
	}
        else
        {
            $("#imgFemaleModel").show();
            var readerF  = new FileReader();
            readerF.addEventListener("load", function () {
                $('#vidFemaleModel source').remove();
                $('#imgFemaleModel').attr("src", '');
                
                var mimeType = readerF.result.split(",")[0].split(":")[1].split(";")[0];
                console.log(mimeType);
                if(mimeType == 'video/quicktime' || mimeType == 'video/x-flv' || mimeType == 'video/mp4' || mimeType == 'video/x-ms-wmv' || mimeType == 'video/avi'){
                    var videoF = document.getElementById('vidFemaleModel');
                    var sourceF = document.createElement('source');
                    sourceF.setAttribute('src', readerF.result);
                    videoF.appendChild(sourceF);
                    videoF.load();
                    $("#imgFemaleModel").hide();
                    $("#vidFemaleModel").show();
                }else{
                    previewF.src = readerF.result;
                    $("#vidFemaleModel").hide();
                    $("#imgFemaleModel").show();
                }
            }, false);

            if (file) {
              readerF.readAsDataURL(file);
            }
        }
    }); 
    
    $('#fileToUploadAudio').on('change',function() {
        var preview = document.querySelector('#sndAudio');
        var file    = document.querySelector('#fileToUploadAudio').files[0];
        var regex = new RegExp("(.*?)\.(mp3|wav|mpeg)$");
       
        if(!(regex.test(document.querySelector('#fileToUploadAudio').files[0].type))) {
            alert('Audio instructions file extension is not supported');
        }
	else if(0 >= document.querySelector('#fileToUploadAudio').files[0].size ||  fileLimitSize < document.querySelector('#fileToUploadAudio').files[0].size) {

            fSize = document.querySelector('#fileToUploadAudio').files[0].size; 
            i=0;while(fSize>900){fSize/=1024;i++;}
            alert('Audio instructions size exceeds 700 Kb limit file size. Current file size = '+((Math.round(fSize*100)/100)+' '+fSExt[i]))+'.';
            $("#fileToUploadAudio").val(null);
	}
        else
        {          
            $('#sndAudio').show(300);
            var reader  = new FileReader();
            reader.addEventListener("load", function () {
              preview.src = reader.result;
            }, false);

            if (file) {
              reader.readAsDataURL(file);
            }
        }
    }); 
    
    checkHaveBAAOrNotNeeded();
    
});

function uploadComponentFile(idComponent,typeFile){
    var data = null;
    if(typeFile==='M')
        data = new FormData(document.querySelector('#myFormMale'));
    else if(typeFile==='F')
        data = new FormData(document.querySelector('#myFormFemale'));
    else if(typeFile==='A')
        data = new FormData(document.querySelector('#myFormAudio'));
    data.append('idComponent', idComponent);
    data.append('typeFile', typeFile);
    $.ajax({
        url: '/includes/upload_exam_component_image_audio.php',
        type: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function(result) {
            var rsp = JSON.parse(result);
        }
    });
}
    function saveComponents() {
        var selected = $('.cbExamComponent:checkbox:checked').map(function() {
            return this.id;
        }).get();
        //obtener orden return ( ($(this).index()+1) != this.id ? ([id:($(this).index()+1), sort:this.id]) : null);
        var searchCriteria = [];
        var ordered = $('#sortable li').map(function() {
            console.info('***************ordered*************');
            //var returnar = ($(this).index()+1) != this.id ? ({'id':($(this).index()+1), 'sort':this.id}) : null;
            return this.id;
        }).get();
        saveExamComponents(selected.join(),ordered.join());
    }

function resetExamComponents()
{
    console.log('resetear componentes');
    $('#inputComponentTitle').val('');
    $('#cmdComponentType').val('a');
    $('#inputComponentAbbrev').val('');
    $('#inputComponentDesc').val('');
    $('input[name=rdComponentPublic]').attr('disabled',false).val([1]);
    $("#fileToUploadMale").val(null);
    $("#fileToUploadFemale").val(null);
    $("#fileToUploadAudio").val(null);
    $("#imgMaleModel").hide();
    $("#imgFemaleModel").hide();
    $('#sndAudio').hide();
    $("#imgMaleModel").attr("src", '');
    $("#imgFemaleModel").attr("src", '');
    $('#contentvidFemaleModel video').remove();
    $('#contentvidFemaleModel').html('<video id="vidFemaleModel" width="200" height="170" controls poster="/images/player_poster.jpg" preload="metadata"></video>');
    $('#vidFemaleModel').hide();
    $('#contentvidMaleModel video').remove();
    $('#contentvidMaleModel').html('<video id="vidMaleModel" width="200" height="170" controls poster="/images/player_poster.jpg" preload="metadata"></video>');
    $('#vidMaleModel').hide();
    examComponentId=0;     
}

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

function updateWRUrl(UrlUsername) {

    var target  = document.getElementById('spin'),
        spinner = new Spinner(opts).spin(target);
    $(target).data('spinner', spinner);
    $.ajax({
        async: true,
        data: 'physId=' + physicianId + '&UrlUsername=' + UrlUsername,
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
            $('#success_msgWR').show();
        },
        url: "api/updateUrlUsername.php"
    });
}

function saveExamComponent() {
    var title = $('#inputComponentTitle').val();
    var type = $('#cmdComponentType').val();
    var abbrev = $('#inputComponentAbbrev').val();
    var description = $('#inputComponentDesc').val();
    var public = $('input[name=rdComponentPublic]:checked').val();

    var target  = document.getElementById('spin'),
        spinner = new Spinner(opts).spin(target);
    $(target).data('spinner', spinner);
    $.ajax({
        async: true,
        data: 'author_physician=' + physicianId + '&title=' + title
        + '&type=' + type
        + '&abbrev=' + abbrev
        + '&description=' + description
        + '&public=' + public
        + '&id=' + examComponentId,
        error: function(xhr, textStatus, errorThrown) {
            $('#maxTimeLoadingOverlay').spin(false);
            alert("Error processing request: " + textStatus + ": " + errorThrown);
        },
        method: 'POST',
        success: function(data) {
            var result = JSON.parse(data);
            if (!result.success){
                alert("Error processing request: " + result.errorMsg);
            }
            else
            {
                examComponentId=result.id;
                $('#success_msgEC').show();
                if($.trim($("#imgMaleModel").attr("src")) != "" || $("#vidMaleModel source").attr("src") != "")
                    uploadComponentFile(examComponentId,'M');
                if($.trim($("#imgFemaleModel").attr("src")) != "" || $("#vidFemaleModel source").attr("src") != "")
                    uploadComponentFile(examComponentId,'F');
                if($.trim($("#sndAudio").attr("src")) != "")
                    uploadComponentFile(examComponentId,'A');
                resetExamComponents();
            }
            $('#spin').data('spinner').stop();
        },
        url: "api/saveExamComponent.php"
    });
}

function fetchExamComponents() {
    $.ajax({
        async: true,
        error: function(xhr, textStatus, errorThrown) {
            alert("Error fetching exam components: " + textStatus + ": " + errorThrown);
        },
        method: 'GET',
        success: function(result) {
          //console.log(result);
          $( "#setExamComponentsDlg").empty();

            if (!result.success) {
                alert("Error fetching exam components: " + result.errorMsg);
            } else {
              objComponents = null;
              objComponents = result.examComponents;
            var strHeader = '<p>You may use the checkboxes below to select which exam components you would like your patients to submit. The '
                            +'exam components you select here will be automatically selected in the VPExam app. Make sure to <strong>save</strong>'
                            + 'your selections using the button at the bottom of this dialog.</p> <br /><ul id=\'sortable\' class=\'Component\'> ';
                    
            var strFooter = '</ul> <div style="margin-top: 10px; text-align: right;">'
                            + '<div class="button-dark-smaller" id="btnSaveComponents" onclick="saveComponents();">Save</div></div>';
                                                            
            var trHTML = '';
            var valoor = 1;
        $.each(objComponents, function (abbrev, element) {
            trHTML += '<li id="'+element.id+'" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span> <div class="box1">';
            trHTML += '<input class="cbExamComponent" type="checkbox" id="' + element.abbrev + '"' + (element.selected===1 ? ' checked' : '') + '>';
            trHTML += '<label for="' + element.abbrev + '">' + element.title + '</label>';
            trHTML += '<span class="sortablenumber">'+(valoor++)+'</span>';
            trHTML += '</div>';
            trHTML += '<div class="box2">' + (1===element.author_physician ? '<img src=\'../images/pencil.jpg\'  onMouseOver="this.style.cursor=\'pointer\'" onClick="displayExamComponent(\''+abbrev+'\');"><img src=\'../images/trash.png\'  onMouseOver="this.style.cursor=\'pointer\'" onClick="deleteExamComponentDialog('+element.id+',\''+element.title+'\');">' : '&nbsp;') + '</div>';
            trHTML += '<div class="box3"><img src=\'../images/' + (element.type === "v" ? "video_icon.png" : "audio_icon.png") + '\' ></div>';
            trHTML += '</li>';
        });
        var strjs='<script>$( function() {'+
                        '$( "#sortable" ).sortable({'+
                            'placeholder: "ui-state-highlight",'+
                            'helper: "clone",'+
                            'sort: function(e, ui) {'+
                                '$(ui.placeholder).html(Number($("#sortable > li:visible").index(ui.placeholder)) + 1);'+
                            '},'+
                            'update: function(event, ui) {'+
                                'var $lis = $(this).children("li");'+
                                '$lis.each(function() {'+
                                    //'var $li = $(this);'+
                                    'var newVal = $(this).index() + 1;'+
                                    '$(this).children(".sortablenumber").html(newVal);'+
                                    //'$(this).children(\'#item_display_order\').val(newVal);'+ 
                                '});'+
                                '}'+
                        '});'+
                        '$( "#sortable" ).disableSelection();'+
                        '} );'+
                    '</script>';                                                       
                                                            
        $( "#setExamComponentsDlg").append( strHeader+trHTML +strFooter + strjs);}
        },
        url: "api/getAllExamComponents.php?physId=" + physicianId
    });
}

function displayExamComponent(element){
    var objComponentDesc = objComponents[element];
    var rdComponentPublicValue = [];
    rdComponentPublicValue.push(objComponentDesc.public);
    examComponentId=objComponentDesc.id;
    $('#inputComponentTitle').val(objComponentDesc.title);
    $('#cmdComponentType').val(objComponentDesc.type);
    $('#inputComponentAbbrev').val(objComponentDesc.abbrev);
    $('#inputComponentDesc').val(objComponentDesc.desc);
    $('input[name=rdComponentPublic]').attr('disabled',true).val(rdComponentPublicValue);
    $('#setExamComponentsDlg').dialog('close');
    $('#createExamComponentsDlg').show(300);
    $('#btnSetExamComponents').hide(300);
    $('#btnCreateExamComponents').hide(300);   
    $('#imgMaleModel').show(300); 
    $('#vidMaleModel').show(300); 
    $('#imgFemaleModel').show(300); 
    $('#vidFemaleModel').show(300); 
    $('#sndAudio').show(300); 

    var srcMale = "/images/exam/component/" + ((new Date()).getTime()) + "/" + examComponentId + "/male";

    var geturl;
    geturl = $.ajax({
        type: "GET",
        url: srcMale,
        success: function () {
            var mimeMale = geturl.getResponseHeader("Content-Type");
            console.log(srcMale);
            console.log(mimeMale);
            //$('#imgMaleModel').attr("src", srcMale);
            if(mimeMale == 'video/mp4'){
                var videoMale = document.getElementById('vidMaleModel');
                var sourceMale = document.createElement('source');
                sourceMale.setAttribute('src', srcMale);
                videoMale.appendChild(sourceMale);
                videoMale.load();
                $('#imgMaleModel').hide();
                $('#vidMaleModel').show();
            }else{
                $('#imgMaleModel').attr("src", srcMale);
                $('#vidMaleModel').hide();
                $('#imgMaleModel').show();
            }
        }
    }).fail(function() { 
        console.log('error');
        $('#imgMaleModel').hide();
        $('#vidMaleModel').hide();
    });
    
    var srcFemale = "/images/exam/component/" + ((new Date()).getTime())+ "/" + examComponentId + "/female";
    
    
     var geturlFemale;
    geturlFemale = $.ajax({
        type: "GET",
        url: srcFemale,
        success: function () {
            var mimeMale = geturlFemale.getResponseHeader("Content-Type");
            console.log(srcFemale);
            console.log(mimeMale);
            //$('#imgFemaleModel').attr("src", srcFemale);
            if(mimeMale == 'video/mp4'){
                var video = document.getElementById('vidFemaleModel');
                var source = document.createElement('source');
                source.setAttribute('src', srcFemale);
                video.appendChild(source);
                video.load();
                $('#imgFemaleModel').hide();
                $('#vidFemaleModel').show();
            }else{
                $('#imgFemaleModel').attr("src", srcFemale);
                $('#vidFemaleModel').hide();
                $('#imgFemaleModel').show();
            }
        }
    }).fail(function() { 
        console.log('error female');
        $('#imgFemaleModel').hide();
        $('#vidFemaleModel').hide();
    });

    var src = "/images/exam/component/" + ((new Date()).getTime()) + "/" + examComponentId + "/audio";
    $('#sndAudio').attr("src", src);     
    
}

function saveExamComponents(jsonStr, otroJson) {
    console.info('************** primerJson *************');
    console.info(jsonStr);
    console.info('************** otroJson *************');
    console.info(otroJson);
     /*
     $.each(otroJson, function(i, item) {
        alert('id= '+item.id +' sort= '+item.sort);
      });*/
    $.ajax({
        async: true,
        data: "physId=" + physicianId + "&ecString=" + encodeURIComponent(jsonStr)+"&exorderString="+encodeURIComponent(otroJson),
        error: function(xhr, textStatus, errorThrown) {
            alert("Error saving exam components: " + textStatus + ": " + errorThrown);
        },
        method: 'POST',
        success: function(result) {
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

                renderPDF("/includes/getBAAPDF.php?physId=" + physicianId, document.getElementById("pdfContainer"));
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
                $('#cropResult').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=2&time='+(new Date()).getTime());
                $('#profilePic').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=2&time='+(new Date()).getTime());
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

function ajaxUploadWRI(physId, file, cropData) {
    var cropDataStr = JSON.stringify(cropData);
    var data = new FormData();

    data.append('originalImage', file);
    data.append('cropData', cropDataStr);
    data.append('physicianId', physId.toString());
    data.append('backgroundImg', true);    
    for(let i of data){
    console.log(i)
    }
    $.ajax({
        url: 'util/crop.php',
        type: 'POST',
        data: data,
        processData: false,
        contentType: false,

        beforeSend: function(jqXHR, settings) {
            $('.cropBoxOuterContainerWRI').hide(300);
            $('.loadingContainerWRI').show(300);
        },

        success: function(data) {
            var rsp = JSON.parse(data);
            if (rsp.success) {
                $uploadProgressLblWRI.text("Upload Complete");
                var lblLeft = Math.floor($uploadProgressWRI.width() / 2) - Math.floor($uploadProgressLblWRI.width() / 2);
                $uploadProgressLblWRI.css({
                    left: lblLeft + 'px'
                });
                $('.resultContainerWRI').show(300);
                $('#cropResultWRI').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=3&time='+(new Date()).getTime());
                $('#BackgroundImgWR').attr('src', 'includes/getProfileImage.php?id=' + physId + '&type=3&time='+(new Date()).getTime());
            } else {
                $('#errorCodeWRI').text(rsp.error);
                $('.errorContainerWRI').show(300);
            }
        },

        error: function(jqXHR, textStatus, error) {
            $('#errorCodeWRI').text(textStatus + ": " + error);
            $('.errorContainerWRI').show(300);
        },

        progress: function(e) {
            if (e.lengthComputable) {
                var percentComplete = Math.round((e.loaded / e.total) * 100);
                $uploadProgressWRI.progressbar("value", percentComplete);
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

function deleteExamComponentDialog(idExamComponent,titleExamComponent) {
    $('#lblExamComponent').show();
    $('#lblExamComponent').text(titleExamComponent);
    $('#setExamComponentsDlg').dialog('close');
    $('#dlgConfirmDeleteExCom').data('idExComp', idExamComponent).dialog('open');
}
       
function deleteExamComponent(idExamComponent) {

    $.ajax({
        async: true,
        method: 'POST',
        data: 'idExCom='+idExamComponent,
        success: function(result) {
            var rspObj = JSON.parse(result);
            if (rspObj.success) {
                swal(
                    'Deleted!',
                    'Your exam component has been deleted.',
                    'success'
                  )
            }
        },
        url: "includes/removeExamComponent.php"
    });   
}

$('#btnEmailNot').on('click', function() {
        var target  = document.getElementById('spin'),
            spinner = new Spinner(opts).spin(target);
        $(target).data('spinner', spinner);
        var emailnotification = $('input[name=emailnotification]:checked').val(); 
        if(emailnotification != ''){
            $.ajax({
                async: true,
                data: 'physId=' + physicianId + '&emailnotification=' + emailnotification,
                error: function(xhr, textStatus, errorThrown) {
                    alert("Error processing request: " + textStatus + ": " + errorThrown);
                },
                method: 'POST',
                success: function(data) {
                    var result = JSON.parse(data);
                    if (!result.success) {

                        alert("Error changing settings: " + result.errorMsg);
                    } else {
                        $('#spin').data('spinner').stop();
                        $('#success_msgEN').show();
                        //alert("Settings changed successfully!");
                    }
                },
                url: "api/updatePhysicianEmailNotification.php"
            });
        }else{
            alert("Error changing password: " + result.errorMsg);
        }
    });