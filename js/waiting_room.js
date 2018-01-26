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
                        allowEscapeKey     : false,
                        showSpinner: true,
                        //imageUrl           : '/img/waiting_room.png',
                        showCancelButton   : false,
                        text               : 'Please check in below to let ' + physician.name + ' know you are here:',
                        title              : 'Welcome!',
                        html               : true,
                        text: "<input id='swalpwdHashed'name='swalpwdHashed' type='hidden' />" +
                                "<div id='app1' class='app'>" +
                                "<a href='#' id='start-camera' class='visible'>Touch here to start the app.</a>"+
                                "<video id='camera-stream'></video>"+
                                "<div id='contentsnap'><img id='snap'></div>"+
                                "<p id='error-message'></p>"+
                                "<div class='controls'>"+
                                "<a href='#' id='delete-photo' title='Delete Photo' class='disabled'><i class='material-icons'>delete</i></a>"+
                                "<a href='#' id='take-photo' title='Take Photo'><i class='material-icons'>camera_alt</i></a>"+
                                "<a href='#' id='download-photo' download='selfie.png' title='Save Photo' class='disabled'><i class='material-icons'>file_download</i></a>"+  
                                "</div>"+
                                "<canvas></canvas>"+
                          "</div>"+
                          "<span class='specialspan mandatory'>Photo:</span><input type='file' id='selectPhoto' value=''>"+
                          "<span class='specialspan mandatory'>First Name:</span><input type='text' id='swal-name' class='swal-input' tabindex='3' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false'> "+
                          "<span class='specialspan mandatory'>Last Name:</span><input id='swal-lastname' type='text' class='swal-input' tabindex='4' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false'> "+
                          "<span class='specialspan mandatory'>Gender:</span><div class='radioscss'><input type='radio' class='swal-input swal-gender' name='swal-gender' value='M'> Male  <input type='radio' class='swal-input swal-gender' name='swal-gender' value='F'> Female </div> "+
                          "<span class='specialspan mandatory'>Date of Birth:</span><div class='smes'><input id='swal-birthmonth' name='dobm' type='text' class='swal-input sm holo' placeholder='MM' maxlength='2' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false' value=''>"+
                            "<input id='swal-birthday' name='dobd' type='text' class='swal-input sm holo' placeholder='DD' maxlength='2' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false' value=''>"+
                            "<input id='swal-birthyear' name='doby' type='text' class='swal-input sm holo' placeholder='YYYY' maxlength='4' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false' value=''> </div>"+
                          "<span class='specialspan'>Phone:</span><input type='number' id='swal-phone' class='swal-input' tabindex='3' maxlength='10' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false'> "+
                          " <span class='specialspan'>Email:</span><input id='swal-email' name='email' type='email' class='swal-input' tabindex='6' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false'> "+
                          "<span class='specialspan'>Password:</span><input id='swal-password' type='password' class='swal-input' tabindex='7'>"
                     }, function(patientname) {
                            var name = document.getElementById('swal-name').value;
                            var lastname = document.getElementById('swal-lastname').value;
                            //var gender = document.getElementById('swal-gender').checked;
                            //var gender = $('swal-gender').checked.val();
                            var gender = $(".swal-gender:checked").val();
                            var birthmonth = document.getElementById('swal-birthmonth').value;
                            var birthday = document.getElementById('swal-birthday').value;
                            var birthyear = document.getElementById('swal-birthyear').value;
                            var phone = document.getElementById('swal-phone').value;
                            var email = document.getElementById('swal-email').value;
                            var password = document.getElementById('swal-password').value;
                            var salt = document.getElementById('swalpwdHashed').value;
                            var profilePic = document.getElementById('snap').src;
                            var regexes = {
                                email: /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i,
                                address: "",
                                zip: "",
                                phone: "^[0-9]*$",
                                numbers:"^[0-9]",
                                month:"(0[1-9]|1[012])",
                                day:"(0[1-9]|[12]\d|3[01])",
                                year:"[0-9]",
                                birthdate:"^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$"
                            };

                            if(birthday.length==1 && (birthday>0 && birthday<10)){
                                birthday='0'+birthday;
                                $('#swal-birthday').val(birthday);
                                console.log(birthday);
                            }
                            if(birthmonth.length==1 && (birthmonth>0 && birthmonth<10)){
                                birthmonth='0'+birthmonth;
                                $('#swal-birthmonth').val(birthmonth);
                                console.log(birthmonth);
                            }
                            console.log('gender'+gender)
                            //console.log( gender.length ) ;
                            console.log($('#swal-phone').val());
                            var birthdate = birthyear+'-'+birthmonth+'-'+birthday;
                            if(''===profilePic){
                                swal.showInputError('You need to take a profile picture!');
                                return;
                            }else if('' === name || name.trim().length == 0){
                                swal.showInputError('You need to write your first name!');
                                return;
                            }else if ('' === lastname || lastname.trim().length == 0){
                                swal.showInputError('You need to write your last name!');
                                return;
                            }else if ('' === gender || typeof gender === 'undefined'){
                                swal.showInputError('You need to select your gender!');
                                return;
                            }else if ('' === birthmonth || birthmonth >12) {
                                swal.showInputError('You need to write a valid birth month!');
                                return;
                            }else if ('' === birthday || birthday > 31) {
                                swal.showInputError('You need to write a valid birth day!');
                                return;
                            }else if ('' === birthyear || birthyear < 1900 || birthyear > (new Date()).getFullYear()) {
                                swal.showInputError('You need to write a valid birth year!');
                                return;
                            }else if (!isValidDate(birthdate)){
                                console.log(birthdate);
                                swal.showInputError('You need to write a valid birthdate!');
                                return;
                            }else if ('' != ($('#swal-phone').val()) && (phone.length) < 10) {
                                swal.showInputError('You need to write a valid phone!');
                                return;
                            }else if ('' != email) {
                                if(!new RegExp(regexes.email).test(email)){
                                    swal.showInputError('You need to write a valid email!');
                                    return;
                                }else if ('' === password ) {
                                    swal.showInputError('You need to write a valid password!');
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
                                            //Mandar a waiting room
                                            checkin(results.result.firstName, results.result.patientId);
                                            //swal.showInputError('Error: '+results.result.errorMsg);
                                            //return;
                                        } else {
                                            var $pwd = $('#swal-password');
                                            if ($($pwd).val() !== "") {
                                                var hashedPwdElem=''; //= $('<input id="pwdHashed" name="pwdHashed" type="hidden" />');
                                                hashedPwdElem = hex_sha512($pwd.val());
                                                $pwd.val("");
                                            }

                                            //Mandar llamar API para crear el usuario (preregistro) physician.id
                                            console.log('name '+name+' lastname '+ lastname+' Phone: '+phone);
                                            var username = name+lastname+birthmonth+birthday+birthyear+gender;
                                            $.ajax({
                                                method      : 'POST',
                                                url         : '/api/savePatientWaitingRoom.php',
                                                data        : { 'name' : name, 'lastname' : lastname, 'birthdate' : birthdate, 'username' : username ,'gender' : gender, 'email' : email, 'password' : hashedPwdElem, 'physicianid' : physician.id, 'photo' : profilePic, 'phone' : phone}
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
                            }else{
                                //Guardar usuario sin correo y contraseña
                                //Mandar llamar API para crear el usuario (preregistro) physician.id
                                console.log('name '+name+' lastname '+ lastname+' Phone: '+phone);
                                var username = name+lastname+birthmonth+birthday+birthyear+gender;
                                console.log(username);
                                $.ajax({
                                    method      : 'POST',
                                    url         : '/api/savePatientWaitingRoom.php',
                                    data        : { 'name' : name, 'lastname' : lastname, 'birthdate' : birthdate, 'username' : username ,'gender' : gender, 'physicianid' : physician.id, 'photo' : profilePic, 'phone' : phone}
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
                        //Funciones para tomar una foto de perfil...
                        //En keyup checar qe no tenga letras
                        $('#swal-birthmonth').on('keyup', function(event) {
                            var input = this,
                                kc    = event.which || event.keyCode,
                                removeLast = function() {
                                    return input.value.slice(0, -1)
                                }
                            if( !kc || kc == 229 ) {
                                kc = input.value.substr(input.selectionStart - 1 || 0, 1).charCodeAt(0)
                            }
                            console.log('kc { ', kc, ' }')
                            console.log('input.value { ', input.value, ' }')
                            console.log('input.value.length { ', input.value.length, ' }')
                            if (13 == kc || 8 == kc) {
                                event.preventDefault()
                                return false
                            }
                            //Solo numeros
                            if(48 > kc || 57 < kc) {
                                console.log('No es numero')
                                input.value = removeLast()
                            }
                            //Necesita empezar con 0 o 1
                            if(1 == input.value.length && 48 != kc && 49 != kc){
                                console.log('Necesita empezar con 0 o 1')
                                input.value = removeLast()
                            }
                            //Si el valor en el input es mayor que 12
                            else if(12 < input.value) {
                                console.log('Mes invalido')
                                input.value = removeLast()
                            }
                            if(2 == input.value.length && 13 > input.value){
                                console.log('valor correcto');
                                $(this).next('input:text').focus();
                            }
                        });



                        $('#swal-birthday').on('keyup', function(event) {
                            var input = this,
                                kc    = event.which || event.keyCode,
                                removeLast = function() {
                                    return input.value.slice(0, -1)
                                }
                            if( !kc || kc == 229 ) {
                                kc = input.value.substr(input.selectionStart - 1 || 0, 1).charCodeAt(0)
                            }
                            console.log('kc { ', kc, ' }')
                            console.log('input.value { ', input.value, ' }')
                            console.log('input.value.length { ', input.value.length, ' }')
                            if (13 == kc || 8 == kc) {
                                event.preventDefault()
                                return false
                            }
                            //Solo numeros
                            if(48 > kc || 57 < kc) {
                                console.log('No es numero')
                                input.value = removeLast()
                            }
                            //Necesita empezar con 0, 1, 2 , 3
                            if(1 == input.value.length && 48 != kc && 49 != kc && 50 != kc && 51 != kc){
                                console.log('Necesita empezar con 0 ,1,2 o 3')
                                input.value = removeLast()
                            }
                            //Si el valor en el input es mayor que 31
                            else if(31 < input.value) {
                                console.log('Dia invalido')
                                input.value = removeLast()
                            }
                            if(2 == input.value.length && 32 > input.value){
                                console.log('valor correcto');
                                $(this).next('input:text').focus();
                            }
                            
                        });
                        $('#swal-birthyear').on('keyup', function(event) {
                            $(this).val($(this).val().replace(/[^\d].+/, ""));
                            if ((event.which < 48 || event.which > 57)) {
                                event.preventDefault();
                            }
                            if($(this).val().length==4){
                                console.log('anio '+$(this).val().length);
                                event.preventDefault();
                            }
                            if($(this).val().length>4){
                                $(this).val($(this).val().substring(0,4));
                            }
                        });

                        $('#swal-phone').unbind('keyup change input paste').bind('keyup change input paste',function(e){
                            $(this).val($(this).val().replace(/[^\d].+/, ""));
                            if ((e.which < 48 || e.which > 57)) {
                                e.preventDefault();
                            }
                            var $this = $(this);
                            var val = $this.val();
                            var valLength = val.length;
                            var maxCount = $this.attr('maxlength');
                            if(valLength>maxCount){
                                $this.val($this.val().substring(0,maxCount));
                            }
                        });
                        /*$('#swal-phone').inputmask({
                            autoUnmask: true,
                            mask: "(999) 999-9999",
                            greedy: false,
                            removeMaskOnSubmit: true
                        });*/
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
                                  navigator.getMedia({video: { width: 250, height: 250 },audio: false},
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
                                        if (err.name === 'TrackStartError') {
                                            console.log('error');
                                            displayErrorMessage(" " );
                                        }else{
                                            displayErrorMessage("There was an error with accessing the camera stream: " + err.name, err);
                                        }
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
                          //showVideo();
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
                            console.log('algo preview');
                            console.log(document.querySelector('input[type=file]').files[0].type);
                            if(!(regex.test(document.querySelector('input[type=file]').files[0].type))) {
                                //alert('The image format is not supported');
                                swal.showInputError('The image format is not supported');
                            }else{
                                hideUI();
                                image.setAttribute('src', snap);
                                //image.classList.add("visible");
                                
                                //document.querySelector('#camera-stream').classList.add('visible');
                                image.setAttribute('style', 'height:136px; width:132px;left:22px;');
                                //image.setAttribute('style', 'transform: rotate(90deg); height:176px; width:132px;left:22px;'); //top: -22px;position:relative;     max-height:150px;
                                var preview = document.querySelector('#snap');
                                var file    = document.querySelector('input[type=file]').files[0];
                                var reader  = new FileReader();
                                
                                /*document.getElementById('selectPhoto').onchange = function (e) {
                                    loadImage(
                                        e.target.files[0],
                                        function (img) {
                                            //document.getElementById("snap").remove();
                                             document.getElementById("contentsnap").appendChild(img);
                                        },
                                        {maxWidth: 176,
                                        orientation: true} // Options
                                    );
                                };*/

                                /*$('#selectPhoto').on('change',function(e){
                                    console.log(e.target.files[0]);
                                    loadImage(
                                        e.target.files[0],
                                        function (img) {
                                            //console.log(img);
                                            //document.getElementById("snap").remove();
                                            $('#contentsnap canvas').remove();
                                             document.getElementById("contentsnap").appendChild(img);
                                        },
                                        {maxWidth: 176,
                                        orientation: true} // Options
                                    );
                                });*/
                                reader.onloadend = function () {
                                    //Se muestra imagen en img
                                    preview.src = reader.result;
                                    console.log('lalalal');
                                    
                                    loadImage(
                                        $('#selectPhoto')[0].files[0],
                                        function (img) {
                                            $('#contentsnap canvas').remove();
                                             document.getElementById("contentsnap").appendChild(img);
                                        },
                                        {maxWidth: 176,
                                        orientation: true} // Options
                                    );
                                }

                                if (file) {
                                    reader.readAsDataURL(file);
                                } else {
                                    preview.src = "";
                                }
                            }
                        }
                        function testPassword(password) {
                            var upperRegex = /[A-Z]/,
                                lowerRegex = /[a-z]/,
                                numberRegex = /[0-9]/,
                                specialRegex = /[^A-Za-z0-9]/,
                                minLength = 8;

                            var lengthGood = (password.length >= minLength),
                                haveUpper = upperRegex.test(password),
                                haveLower = lowerRegex.test(password),
                                haveNum = numberRegex.test(password),
                                haveSpecial = specialRegex.test(password);

                            if (lengthGood)
                                $('#length').removeClass('invalid').addClass('valid');
                            else
                                $('#length').removeClass('valid').addClass('invalid');

                            if (haveUpper)
                                $('#capital').removeClass('invalid').addClass('valid');
                            else
                                $('#capital').removeClass('valid').addClass('invalid');

                            if (haveLower)
                                $('#letter').removeClass('invalid').addClass('valid');
                            else
                                $('#letter').removeClass('valid').addClass('invalid');

                            if (haveNum)
                                $('#number').removeClass('invalid').addClass('valid');
                            else
                                $('#number').removeClass('valid').addClass('invalid');

                            if (haveSpecial)
                                $('#special').removeClass('invalid').addClass('valid');
                            else
                                $('#special').removeClass('valid').addClass('invalid');

                            return (lengthGood && haveUpper && haveLower && haveNum && haveSpecial);
                        }
                        $("#selectPhoto").change(function () {
                            previewFile(this);
                        });
                        $('#swal-password').on('focus', function() {
                            console.log('focus');
                            //$('#pwdInfo').fadeIn('slow');
                            if ($(this).data('ui-tooltip'))
                                $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
                        }).on('keyup', function() {
                            //testPassword($(this).val());
                        }).on('blur', function() {
                            console.log('blur');
                            //$('#pwdInfo').fadeOut('slow');
                        });
                        function isValidDate(dateString) {
                          var regEx = /^\d{4}-\d{2}-\d{2}$/;
                          if(!dateString.match(regEx)) return false;  // Invalid format
                          var d = new Date(dateString);
                          if(!d.getTime() && d.getTime() !== 0) return false; // Invalid date
                          return d.toISOString().slice(0,10) === dateString;
                        }
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
