var WaitingRoom = {
    timer : null,
    init : function(physician, patient) {
        var isIE    = /(MSIE|Edge)/.test(window.navigator.userAgent),
            checkin = function(patientname) {
            patientname = patientname || '';
            $.ajax({
                method   : 'POST',
                url      : '/api/waiting_room/checkin.php',
                data     : { physician : physician.id, patientname : patientname },
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
                    var chronometer = $('<div id="chronometer"><img src="/img/waiting_room.png" alt="" /><span></span></div>');
                    WaitingRoom.timer = new Timer();
                    WaitingRoom.timer.start();
                    WaitingRoom.timer.addEventListener('secondsUpdated', function (e) {
                        chronometer.find('span').text(WaitingRoom.timer.getTimeValues().toString());
                    });
                    $('body').append(chronometer);
                    chronometer.animate({
                        height : '35px',
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
                        checkin();
                    });
                }
                else {
                    swal({
                        animation          : 'slide-from-top',
                        closeOnConfirm     : false,
                        confirmButtonColor : '#2b8c36',
                        confirmButtonText  : 'Check In',
                        imageUrl           : '/img/waiting_room.png',
                        inputPlaceholder   : 'Enter your name here',
                        showCancelButton   : false,
                        text               : 'Please check in below to let ' + physician.name + ' know you are here:',
                        title              : 'Welcome!',
                        type               : 'input'
                    }, function(patientname) {
                        if ('' === patientname) {
                            swal.showInputError('You need to write your name!');
                            return;
                        }
                        checkin(patientname);
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
