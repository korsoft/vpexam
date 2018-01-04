var WaitingRoom = {
    timer : null,
    init : function(physician, patient) {
        var isIE    = /(MSIE|Edge)/.test(window.navigator.userAgent),
            isChrome    = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor),
            checkin = function(patientname, patientid, app) {
            patientname = patientname || '';
            $.ajax({
                method   : 'POST',
                url      : '/api/waiting_room/checkin.php',
                data     : { physician : physician.id, patientname : patientname, patientid: patientid, app:app },
                dataType : 'json'
            })
            .done(function(response) {
                if('string' == typeof response) {
                    response = JSON.parse(response);
                }
                if (false == response.success) {
                    console.log(response.errorMsg);
                }
                else {
                    //Cerramos el sweetalert
                    swal.close();
                    //Agregamos el cronometro para mostrar el tiempo de espera del paciente hasta que lo antiendan
                    var chronometer = $('<div id="chronometer"></div>');
                    WaitingRoom.timer = new Timer();
                    WaitingRoom.timer.start();
                    /*WaitingRoom.timer.addEventListener('secondsUpdated', function (e) {
                        chronometer.find('span').text(WaitingRoom.timer.getTimeValues().toString());
                    });*/
                    $('body').append(chronometer);
                    chronometer.animate({
                        height : '75px',
                        opacity  : 1
                    }, 500);
                   VideoChat.init(response.data);
                }
            })
            .fail(function(err) {
                console.log('WaitingRoom :: init :: Error :', err);
            });
        };
        try {
            if(isIE) {
                swal({
                    title: 'Unsupported browser',
                    text: 'Your browser does not support <span style="color: #3051a6;">VPExam Video call</span>. Please use Google Chrome or Firefox',
                    html: true,
                    type: 'warning'
                });
            }
            else {
                if(0 < patient.id) {
                    console.log('patient');
                    console.log(patient);
                    swal({
                      closeOnConfirm     : true,
                      confirmButtonColor : '#2b8c36',
                      confirmButtonText  : 'Ok',
                      imageUrl           : '/img/waiting_room.png',
                      text               : 'Wait for ' + physician.name + ' to call you...',
                      title              : 'Welcome ' + patient.name + '!'
                    }, function(isConfirm) {
                        checkin(patient.name, patient.id, patient.app);
                    });
                }
                else {                  
                    swal({
                        animation          : 'slide-from-top',
                        closeOnConfirm     : false,
                        confirmButtonColor : '#2b8c36',
                        confirmButtonText  : 'Check In',
                        imageUrl           : '/img/waiting_room.png',
                        showCancelButton   : false,
                        text               : 'Please check in below to let ' + physician.name + ' know you are here:',
                        title              : 'Welcome!',
                        html               : true,
                        text: "<input id='swalpwdHashed'name='swalpwdHashed' type='hidden' />" +
                              "<div class='app'>" +
                              "<a href='#' id='start-camera' class='visible'>Touch here to start the app.</a>"+
                              "<video id='camera-stream'></video>"+
                              "<img id='snap'>"+
                              "<p id='error-message'></p>"+
                              "<div class='controls'>"+
                              "<a href='#' id='delete-photo' title='Delete Photo' class='disabled'><i class='material-icons'>delete</i></a>"+
                              "<a href='#' id='take-photo' title='Take Photo'><i class='material-icons'>camera_alt</i></a>"+
                              "<a href='#' id='download-photo' download='selfie.png' title='Save Photo' class='disabled'><i class='material-icons'>file_download</i></a>"+  
                            "</div>"+
                            "<canvas></canvas>"+
                          "</div>"+
                          "<span class='specialspan'>Photo:</span><input type='file' id='selectPhoto'>"+
                          "<span class='specialspan'>Name:</span><input type='text' id='swal-name' class='swal-input' tabindex='3'> <span class='specialspan'>Lastname:</span> <input id='swal-lastname' type='text' class='swal-input' tabindex='4'> <span class='specialspan'>Date of Birth:</span> <input id='swal-birthdate' name='dob' readonly='true' type='text' class='swal-input holo' tabindex='5'> <span class='specialspan'>Email:</span> <input id='swal-email' name='email' type='email' class='swal-input' tabindex='6'> <span class='specialspan'>Password:</span> <input id='swal-password' type='password' class='swal-input' tabindex='7'>"
                     }, function(patientname) {
                            var name = document.getElementById('swal-name').value;
                            var lastname = document.getElementById('swal-lastname').value;
                            var birthdate = document.getElementById('swal-birthdate').value;
                            var email = document.getElementById('swal-email').value;
                            var password = document.getElementById('swal-password').value;
                            var salt = document.getElementById('swalpwdHashed').value;
                            var profilePic = document.getElementById('snap').src;
                            var regexes = {
                                email: /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i,
                                address: "",
                                zip: ""
                            };
                            //alert(document.getElementById('swal-input1').value);
                            if('' === name){
                                swal.showInputError('You need to write your name!');
                                return;
                            }else if ('' === lastname){
                                swal.showInputError('You need to write your lastname!');
                                return;
                            }else if ('' === birthdate) {
                                swal.showInputError('You need to write your birthdate!');
                                return;
                            }else if ('' === email) {
                                swal.showInputError('You need to write your email!');
                                return;
                            } else if (!new RegExp(regexes.email).test(email)) {
                                swal.showInputError('You need to write a valid email!');
                                return;
                            }else if ('' === password) {
                                swal.showInputError('You need to write your password!');
                                return;
                            
                            }else{
                                //Mandar llamar api para checar si el email existe si es false el email existe si es true sigue todo bien {"success":false,"result":{"errorMsg":"","intTotal":2}}
                                $.get("api/getEmailPatient.php", { email: email })
                                .done(function(data) {
                                    var results = $.parseJSON(data);
                                    if (results == null) {
                                        swal.showInputError('Error');
                                        //return;
                                    } else if (!results.success) {
                                        swal.showInputError('Error: '+results.result.errorMsg);
                                        //return;
                                    } else {
                                        //hashForm();
                                        var $pwd = $('#swal-password');
                                        if ($($pwd).val() !== "") {
                                            var hashedPwdElem=''; //= $('<input id="pwdHashed" name="pwdHashed" type="hidden" />');
                                            hashedPwdElem = hex_sha512($pwd.val());
                                            $pwd.val("");
                                            //$('#pwdConfirmInput').val("");
                                            //$('form').append($hashedPwdElem);
                                        }
                                        //Mandar llamar API para crear el usuario (preregistro) physician.id
                                        console.log('name '+name+' lastname '+ lastname);
                                        $.ajax({
                                            method      : 'POST',
                                            url         : '/api/savePatientWaitingRoom.php',
                                            data        : { 'name' : name, 'lastname' : lastname, 'birthdate' : birthdate, 'email' : email, 'password' : hashedPwdElem, 'physicianid' : physician.id, 'photo' : profilePic}
                                        })
                                        .done(function(response) {
                                            response = JSON.parse(response);
                                            console.log('WaitingRoomJS :: patient :: init :: Error :');
                                            console.log(response.success);
                                            console.log(response.errorMsg);
                                            console.log(response.patient_id);
                                            if(response.success){
                                                checkin(name, response.patient_id);
                                            }else{
                                                swal.showInputError('Error: '+response.errorMsg);
                                            }
                                        })
                                        .fail(function(err) {
                                            console.log('WaitingRoom :: patient :: init :: Error :', err);
                                        });
                                        //Enviar al paciente al waiting room
                                        //checkin(name);
                                    }
                                });
                            }
                        });
                        //Funciones para tomar una foto de perfil...

                        var video = document.querySelector('#camera-stream'),
                        image = document.querySelector('#snap'),
                        start_camera = document.querySelector('#start-camera'),
                        controls = document.querySelector('.controls'),
                        take_photo_btn = document.querySelector('#take-photo'),
                        delete_photo_btn = document.querySelector('#delete-photo'),
                        download_photo_btn = document.querySelector('#download-photo'),
                        error_message = document.querySelector('#error-message');


                            // The getUserMedia interface is used for handling camera input.
                            // Some browsers need a prefix so here we're covering all the options
                            navigator.getMedia = ( navigator.getUserMedia ||
                                          navigator.webkitGetUserMedia ||
                                          navigator.mozGetUserMedia ||
                                          navigator.msGetUserMedia);
                            
                    
                            if(!navigator.getMedia){
                              displayErrorMessage("Your browser doesn't have support for the navigator.getUserMedia interface.");
                            }
                            else{
                                
                                  // Request the camera. video-capture
                                  navigator.getMedia({video: { width: 250, height: 250 },audio: true},
                                    // Success Callback
                                    function(stream){
                                      // Create an object URL for the video stream and
                                      // set it as src of our HTLM video element.
                                      video.src = window.URL.createObjectURL(stream);
                                      // Play the video element to start the stream.
                                      video.play();
                                      video.onplay = function() {
                                        showVideo();
                                      };
                                        var audioTracks = stream.getAudioTracks();
                                        var videoTracks = stream.getVideoTracks();
                                        if($('#imgMic').length==1)
                                        {
                                            if(audioTracks[0].muted)
                                                $("#imgMic").removeClass().addClass('error');
                                            else if(!audioTracks[0].muted)
                                                $("#imgMic").removeClass().addClass('success');
                                            else
                                                $("#imgMic").removeClass().addClass('normal');
                                        }
                                        
                                        if($('#imgCamera').length==1)
                                        {
                                            if(videoTracks[0].enabled)
                                                $("#imgCamera").removeClass().addClass('success');
                                            else if(!videoTracks[0].enabled)
                                                $("#imgCamera").removeClass().addClass('error');
                                            else
                                                $("#imgCamera").removeClass().addClass('normal'); 
                                        }
                                    },
                                    // Error Callback
                                    function(err){
                                      displayErrorMessage("There was an error with accessing the camera stream: " + err.name, err);
                                      if($('#imgCamera').length==1)
                                        $("#imgCamera").removeClass().addClass('error');
                                      if($('#imgMic').length==1)
                                        $("#imgMic").removeClass().addClass('error');
                                    }
                                  );
                            } 
                       
                        // Mobile browsers cannot play video without user input,
                        // so here we're using a button to start it manually.
                        start_camera.addEventListener("click", function(e){
                          e.preventDefault();
                          // Start video playback manually.
                          video.play();
                          showVideo();
                        });
                        take_photo_btn.addEventListener("click", function(e){
                          e.preventDefault();
                          //deshabilitar boton de seleccionar imagen
                          $('#selectPhoto').attr("disabled", true);
                          var snap = takeSnapshot();
                          // Show image. 
                          image.setAttribute('src', snap);
                          image.classList.add("visible");
                          // Enable delete and save buttons
                          delete_photo_btn.classList.remove("disabled");
                          download_photo_btn.classList.remove("disabled");
                          // Set the href attribute of the download button to the snap url.
                          download_photo_btn.href = snap;
                          // Pause video playback of stream.
                          video.pause();
                        });

                        delete_photo_btn.addEventListener("click", function(e){
                          e.preventDefault();
                          $('#selectPhoto').attr("disabled", false);
                          // Hide image.
                          image.setAttribute('src', "");
                          image.classList.remove("visible");
                          // Disable delete and save buttons
                          delete_photo_btn.classList.add("disabled");
                          download_photo_btn.classList.add("disabled");
                          // Resume playback of stream.
                          video.play();
                        });
                        function showVideo(){
                          // Display the video stream and the controls.
                          hideUI();
                          video.classList.add("visible");
                          controls.classList.add("visible");
                        }

                        function takeSnapshot(){
                          // Here we're using a trick that involves a hidden canvas element.  
                          var hidden_canvas = document.querySelector('canvas'),
                              context = hidden_canvas.getContext('2d');

                          var width = video.videoWidth,
                              height = video.videoHeight;

                          if (width && height) {
                            // Setup a canvas with the same dimensions as the video.
                            hidden_canvas.width = width;
                            hidden_canvas.height = height;
                            // Make a copy of the current frame in the video on the canvas.
                            context.drawImage(video, 0, 0, width, height);
                            // Turn the canvas image into a dataURL that can be used as a src for our photo.
                            return hidden_canvas.toDataURL('image/png');
                          }
                        }
                        function displayErrorMessage(error_msg, error){
                          error = error || "";
                          if(error){
                            console.log(error);
                          }

                          error_message.innerText = error_msg;

                          hideUI();
                          error_message.classList.add("visible");
                        }
                        function hideUI(){
                          // Helper function for clearing the app UI.
                          controls.classList.remove("visible");
                          start_camera.classList.remove("visible");
                          video.classList.remove("visible");
                          snap.classList.remove("visible");
                          error_message.classList.remove("visible");
                        }
                        function previewFile() {
                            var regex = new RegExp("(.*?)\.(png|jpeg|jpg|gif)$");
                            if(!(regex.test(document.querySelector('input[type=file]').files[0].type))) {
                                //alert('The image format is not supported');
                                swal.showInputError('The image format is not supported');
                            }else{
                                hideUI();
                                image.setAttribute('src', snap);
                                image.classList.add("visible");
                                image.setAttribute('style', 'position:relative; max-height:150px;');
                                var preview = document.querySelector('#snap');
                                var file    = document.querySelector('input[type=file]').files[0];
                                var reader  = new FileReader();
                                reader.onloadend = function () {
                                    preview.src = reader.result;
                                }
                                if (file) {
                                    reader.readAsDataURL(file);
                                } else {
                                    preview.src = "";
                                }
                            }
                        }
                        $("#selectPhoto").change(function () {
                            previewFile(this);
                        });
                        /*Fin */

                        //prueba para poner el datepicker
                        $('#swal-birthdate').datepicker({
                            changeMonth: true,
                            changeYear: true,
                            maxDate: 0,
                            yearRange: "-120:+0"
                        }).on('focus', function() {
                            if ($(this).data('ui-tooltip')) {
                                $(this).css({
                                    "border": ""
                                }).tooltip('destroy').attr("title", "");

                            } 
                        });
                    }
            }
        }
        catch(e) {
            swal.showInputError(e);
        }
    },
    patient : {
        init : function(patientid) {
            $.ajax({
                method      : 'POST',
                url         : '/api/waiting_room/iswaiting.php',
                contentType : 'application/json',
                data        : { patientid : patientid},
                dataType    : 'json'
            })
            .done(function(response) {
                response = JSON.parse(response);
                if(response.success) {
                    var chronometer = $('<div id="chronometer"><img src="/img/waiting_room.png" alt="" /><span></span></div>');
                    WaitingRoom.timer.start();
                    WaitingRoom.timer.addEventListener('secondsUpdated', function (e) {
                        chronometer.find('span').text(timer.getTimeValues().toString());
                    });
                    swal({
                      closeOnConfirm     : true,
                      confirmButtonColor : '#2b8c36',
                      confirmButtonText  : 'Ok',
                      imageUrl           : '/img/waiting_room.png',
                      text               : 'Wait for ' + response.data + ' to call you...',
                      title              : 'Waiting room'
                    }, function(isConfirm) {
                        $('body').append(chronometer);
                        chronometer.animate({
                            height : '35px',
                            opacity  : 1
                        }, 500);
                    });
                }
            })
            .fail(function(err) {
                console.log('WaitingRoom :: patient :: init :: Error :', err);
            });
        },
        leave : function(physicianid, patientid, callback) {
            callback = callback || false;
            $.ajax({
                method   : 'POST',
                url      : '/api/waiting_room/leave.php',
                data     : { patientid : patientid, physicianid : physicianid},
                dataType : 'json'
            })
            .done(function(response) {
                if('function' == typeof callback) {
                    callback(patientid);
                }
                else {
                    $('#msg > h1').html('Thanks for using VPExam Video call!');
                    $('#msg > h3').html('We hope you\'ll get well soon.');
                }
            })
            .fail(function(err) {
                console.log('WaitingRoom :: leave :: Error :', err);
            });
        },
    },
    physician : {
        removePatientFromWR : function(physicianid, patientid) {
            WaitingRoom.patient.leave(physicianid, patientid, function(patientid) {
                var ul = $('.waitingRoom ul'),
                    li = ul.find('#menu-li-patient' + patientid);
                if(0 < li.length) {
                    li.remove();
                    if(0 == ul.find('li').length) {
                        $('.waitingRoom').addClass('hide');
                    }
                }
            });
        }
    },
    stop : function() {
        if(!$('#chronometer > span').hasClass('Stop')) {
            WaitingRoom.timer.stop();
            var time = $('#chronometer > span').text();
            $('#chronometer > span').addClass('Stop').text('You were attended in ' + time);
        }
    }
};
$(document).ready(function() {
    if(0 < $('.waitingRoom').length) {
        $('.waitingRoom ul li .removeFromWR').bind('click', function() {
            WaitingRoom.physician.removePatientFromWR($('.waitingRoom').data('id'), $(this).parent().data('id'));
        });
    }
});
