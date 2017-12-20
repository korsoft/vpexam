var WaitingRoom = {
    timer : null,
    init : function(physician, patient) {
        var isIE    = /(MSIE|Edge)/.test(window.navigator.userAgent),
            isChrome    = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor),
            checkin = function(patientname, patientid) {
            patientname = patientname || '';
            $.ajax({
                method   : 'POST',
                url      : '/api/waiting_room/checkin.php',
                data     : { physician : physician.id, patientname : patientname, patientid: patientid },
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
                    swal({
                      closeOnConfirm     : true,
                      confirmButtonColor : '#2b8c36',
                      confirmButtonText  : 'Ok',
                      imageUrl           : '/img/waiting_room.png',
                      text               : 'Wait for ' + physician.name + ' to call you...',
                      title              : 'Welcome ' + patient.name + '!'
                    }, function(isConfirm) {
                        checkin(patient.name, patient.id);
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
                        text: "<input id='swalpwdHashed'name='swalpwdHashed' type='hidden' /> <span class='specialspan'>Name:</span><input type='text' id='swal-name' class='swal-input' tabindex='3'> <span class='specialspan'>Lastname:</span> <input id='swal-lastname' type='text' class='swal-input' tabindex='4'> <span class='specialspan'>Date of Birth:</span> <input id='swal-birthdate' name='dob' readonly='true' type='text' class='swal-input holo' tabindex='5'> <span class='specialspan'>Email:</span> <input id='swal-email' name='email' type='email' class='swal-input' tabindex='6'> <span class='specialspan'>Password:</span> <input id='swal-password' type='password' class='swal-input' tabindex='7'>"
                     }, function(patientname) {
                            var name = document.getElementById('swal-name').value;
                            var lastname = document.getElementById('swal-lastname').value;
                            var birthdate = document.getElementById('swal-birthdate').value;
                            var email = document.getElementById('swal-email').value;
                            var password = document.getElementById('swal-password').value;
                            var salt = document.getElementById('swalpwdHashed').value;
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
                                            data        : { 'name' : name, 'lastname' : lastname, 'birthdate' : birthdate, 'email' : email, 'password' : hashedPwdElem, 'physicianid' : physician.id}
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
