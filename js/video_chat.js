'use strict';
/** browser dependent definition are aligned to one and the same standard name **/
navigator.getUserMedia       = navigator.webkitGetUserMedia       || navigator.mozGetUserMedia || navigator.getUserMedia;
window.RTCPeerConnection     = window.webkitRTCPeerConnection     || window.RTCPeerConnection;
window.RTCIceCandidate       = window.webkitRTCIceCandidate       || window.RTCIceCandidate;
window.RTCSessionDescription = window.webkitRTCSessionDescription || window.RTCSessionDescription;
window.SpeechRecognition     = window.webkitSpeechRecognition     || window.SpeechRecognition;

var isChrome    = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor),
    isFirefox   = !!navigator.mozGetUserMedia,
    isIE        = /(MSIE|Edge)/.test(window.navigator.userAgent),
    isWin       = -1 != window.navigator.appVersion.indexOf('Win'),
    constraints = window.constraints = {
      audio: true,
      video: true
    };
var blPlayed        = false;
var netBandwidth    = null;
var muteMicbutton   = document.getElementById('mute');
var VideoChat = {
  audio     : null,
  callback  : false,
  reader    : null, 
  recorder  : null,
  reqrecord : false,
  service   : {
    pc    : null,
    wsc   : null,
    start : function() {
      if(0 < VideoChat.video.local.user.id) {
        //Establecer conexion con el servidor socket
        //this.wsc = new WebSocket('https:' == location.protocol?'wss://vpexam.com:8443':'ws://vpexam.com:9090');
        this.wsc = new WebSocket('https:' == location.protocol?'wss://vpexam.com/signal':'ws://vpexam.com:9090');
        //this.wsc.binaryType = 'arraybuffer';
        this.wsc.onopen    = function () { 
          console.log('Connecting to the signaling server');
          VideoChat.send({ id : VideoChat.video.local.user.id, name : VideoChat.video.local.user.name, type: 'login' });
        };
        this.wsc.onmessage = function(msg) {
          var data = JSON.parse(msg.data);
          switch(data.type) {
            //when server response after login on it
            case 'login':
              if(data.success) {
                console.log('Connected to the signaling server');
              }
              else {
                VideoChat.alert({title : 'Another user is logged with this account', type : 'warning'});
              }
            break; 
            //when somebody wants to call us 
            case 'offer':
              if(false === data.success) {
                var title = VideoChat.video.remote.user.name,
                    text  = 'Is not logged into the site';
                if('busy' === data.status) {
                  text = 'Is on a call right now';
                }
                VideoChat.alert({title : title, text : text, type : 'warning'});
              }
              else {
                VideoChat.status    = 'is_incoming_call';
                VideoChat.reqrecord = data.record;
                if(0 == VideoChat.video.remote.user.id) {
                  VideoChat.video.remote.user = data.user;
                }
                VideoChat.service.pc.setRemoteDescription(new RTCSessionDescription(data.offer));
                VideoChat.ringing();
              }
            break;
            //when we got an answer from a remote user
            case 'answer':
              VideoChat.service.pc.setRemoteDescription(new RTCSessionDescription(data.answer));
              VideoChat.reqrecord = data.record;
              if(VideoChat.reqrecord) {
                VideoChat.record.init();
              }
              VideoChat.answer(false);
            break; 
            //when a remote peer sends an ice candidate to us 
            //when we got an ice candidate from a remote user 
            case 'candidate': 
              VideoChat.service.pc.addIceCandidate(new RTCIceCandidate(data.candidate)); 
            break; 
            case 'leave':
              VideoChat.leave(true);
            default: 
            break; 
          }
        }
      }
      this.wsc.onerror = function (error) { 
          VideoChat.alert({title : 'You cannot connect to the video server', type : 'error'});
          console.log('Error from socket server, error { ', error, ' }.'); 
      };
      // setup stream listening 
      this.pc = new RTCPeerConnection(
        {
          'iceServers': [
            { 'urls'       : (isFirefox?'stun:stun.services.mozilla.com':'stun:stun.l.google.com:19302') },
            { 'urls'       : 'turn:vpexam.com',
              'credential' : 'Vp3X4m',
              'username'   : 'vp_user'
            }
          ]
        },
        { optional: [{ RtpDataChannels: true }, { DtlsSrtpKeyAgreement: true }] }
      );
      //when a remote user adds stream to the peer connection, we display it
      this.pc.ontrack  = function(data) {
        VideoChat.video.remote.stream = data.streams[0];
        VideoChat.video.remote.dom[0].srcObject = VideoChat.video.remote.stream;
      };
        BANDWITDH.init(function(bandwitdh){
            console.log('calculate bandwitdh');
            if(bandwitdh>=5)
                $("#imgbandwidth").attr("src","images/bw_green.png");
            else if(bandwitdh>1 && bandwitdh<5)
                $("#imgbandwidth").attr("src","images/bw_yellow.png");
            else if(bandwitdh>0)
                $("#imgbandwidth").attr("src","images/bw_red.png");
            else
                $("#imgbandwidth").attr("src","images/bw_black.png");
            console.log('bandwitdh is ' + bandwitdh + ' [Mbps]');
            netBandwidth=bandwitdh;
        });      
      // Setup ice handling 
      this.pc.onicecandidate = function (event) {
        if(event.candidate) { 
          VideoChat.send({
            id        : VideoChat.video.remote.user.id,
            type      : 'candidate', 
            candidate : event.candidate 
          });
        }
      };
    }
  },
  status    : 'initialized', //initialized, connected, is_incoming_call, finalized
  video     : {
    local  : {
      dom    : null,
      stream : null,
      user   : {
        id     : 0,
        name   : '',
        status : 'available'
      }
    },
    remote : {
      dom    : null,
      stream : null,
      user   : {
        id     : 0, 
        name   : '',
        status : 'available'
      }
    }
  },
  alert     : function(cnf, callback) {
    callback = callback || function() {
      VideoChat.leave();
    };
    swal(cnf, callback);
  },
  answer    : function(sendanswer) {
    if(is_patient && 'function' === typeof WaitingRoom.stop) {
      WaitingRoom.stop();
    }
    VideoChat.status = 'connected';
    //Se detiene el audio de "llamando"
    VideoChat.audio.pause();
    //Se cambia el titulo del fancybox
    VideoChat.title('Talking with ' + VideoChat.video.remote.user.name);
    VideoChat.bind(false, true);
    VideoChat.video.remote.dom.removeClass('hide');
    VideoChat.video.local.dom
      .css({position: 'absolute', width: 'auto'})
      .animate({
        bottom : '20px',
        height : '100px',
        right  : '20px'
      }, 500);
    if(sendanswer) {
      VideoChat.record.ask(function(wantrecord) {
        wantrecord = wantrecord || false;
        //Create an answer
        VideoChat.service.pc.createAnswer(
          //Success creation answer
          function(answer) {
            //Send the answer
            VideoChat.send({ 
              type   : 'answer', 
              answer : answer,
              from   : VideoChat.video.local.user.id,
              to     : VideoChat.video.remote.user.id,
              record : wantrecord
            });
            VideoChat.service.pc.setLocalDescription(answer);
          },
          //Error creation answer
          function(error) {
            console.log('Error when creating an answer, error { ', error, ' }.'); 
          }
        );
      });
    } 
  },
  bind      : function(answer, decline) {
    var answerBtn  = $('#answerButton'),
        declineBtn = $('#declineButton');
    if(answer) {
      answerBtn
        .removeClass('disabled')
        .unbind('click')
        .bind('click', function() { VideoChat.offer(); });
    }
    else {
      answerBtn
        .addClass('disabled')
        .unbind('click');
    }
    if(decline) { 
      declineBtn
        .removeClass('disabled')
        .unbind('click')
        .bind('click', function() { $.fancybox.close(true); });
    }
    else { 
      declineBtn
        .addClass('disabled')
        .unbind('click');
    }
  },
  init      : function(_caller) {
    if(undefined !== _caller && 0 < _caller.id && 0 == VideoChat.video.local.user.id) {
      VideoChat.video.local.user = _caller;
    }
    VideoChat.status           = 'initialized';
    VideoChat.video.local.dom  = $('#localVideo');
    VideoChat.video.remote.dom = $('#remoteVideo');
    VideoChat.audio            = new Audio('/sounds/ringing_sound.mp3');
    VideoChat.audio.volume     = 1;
    //Se define loop para el audio de "llamando"
    VideoChat.audio.addEventListener('ended', function() {
      this.currentTime = 0;
      this.play();
    }, false);
    VideoChat.service.start();
  },
  leave     : function(left) {
    left = left || false;
    if('finalized' != VideoChat.status) {
      if(true !== left && undefined !== WaitingRoom && is_patient) {
        WaitingRoom.patient.leave(VideoChat.video.remote.user.id, VideoChat.video.local.user.id);
      }
      VideoChat.send({ id : VideoChat.video.local.user.id, type : 'leave' });
      if(true === left) {
        VideoChat.title(VideoChat.video.remote.user.name + ' left the call.');
      }
      //Si es paciente y se inicio el grabado de la llamada
      if(is_patient && null != VideoChat.recorder) {
        //La detenemos
        VideoChat.recorder.stop();
      }
      //If the webcam is on
      if(null !== VideoChat.video.local.stream) {
        //Stop it
        VideoChat.video.local.stream.getVideoTracks()[0].stop();
        VideoChat.video.local.stream     = null;
        VideoChat.video.local.dom[0].src = '';
        VideoChat.video.local.dom.css({
          bottom : 0,
          height : '100%',
          position: 'relative', 
          right  : 0,
          width: 'auto'
        });
        VideoChat.video.local.user.status = 'available';
      }
      //If the webcam is on
      if(null !== VideoChat.video.remote.stream) {
        //Stop it
        VideoChat.video.remote.stream     = null;
        VideoChat.video.remote.dom[0].src = '';
        VideoChat.video.remote.dom.addClass('hide');
        VideoChat.video.remote.user       = {
          id     : 0,
          name   : '',
          status : 'available'
        };
      }
      VideoChat.service.pc.close();
      VideoChat.service.pc.onicecandidate = null;
      VideoChat.service.pc.ontrack        = null;
      VideoChat.status                    = 'finalized';
      VideoChat.callback                  = false;
      //Se detiene el audio de "llamando"
      VideoChat.audio.pause();
      VideoChat.service.start();
    }
  },
  offer     : function() {
    VideoChat.record.ask(function(wantrecord) {
      wantrecord = wantrecord || false;
      VideoChat.video.local.user.status = 'busy';
      //Create an offer
      VideoChat.service.pc.createOffer(
        //Success creation offer
        function(offer) { 
          VideoChat.audio.play();
          VideoChat.title('Calling to ' + VideoChat.video.remote.user.name);
          VideoChat.bind(false, true);
          //Send an offer
          VideoChat.send({
            type   : 'offer',
            offer  : offer,
            from   : VideoChat.video.local.user,
            to     : VideoChat.video.remote.user.id,
            record : wantrecord
          }); 
          VideoChat.service.pc.setLocalDescription(offer);
        },
        //Error creation offer
        function (error) { 
          console.log('Error when creating an offer, error { ', error, ' }.'); 
        }
      );
    });
  },
  ready     : function(_calling) {
    var container = $('.fancybox-inner');
    if(undefined !== _calling && 0 < _calling.id) {
      VideoChat.video.remote.user = _calling;
    }
    if('finalized' == VideoChat.status) {
      VideoChat.status = 'initialized';
    }
    Permissions.show();
    Loading.show(container);
    // check browser WebRTC availability
    navigator.mediaDevices.getUserMedia(constraints)
    .then(function(stream) {
      Permissions.hide();
      Loading.hide(container);
      Error.hide(container);
      $('#videochat').removeClass('hide');
      VideoChat.video.local.stream = stream;
      window.stream = stream;
      //displaying local video stream on the page
      var video = VideoChat.video.local.dom[0]
      VideoChat.service.pc.addStream(stream); 
      video.srcObject = stream;
      video.onloadedmetadata = function(e) {
        video.play();
      };
      //VideoChat.video.local.dom[0].src = window.URL.createObjectURL(VideoChat.video.local.stream);
      if(VideoChat.reqrecord) {
        VideoChat.record.init();
      }
      if('function' == typeof VideoChat.callback) {
        VideoChat.callback();
      }
      else {
        VideoChat.bind(true, true);  
      }
    })
    .catch(function(err) {
      Permissions.hide();
      Loading.hide(container);
      Error.show(container);
      console.log('Error when trying get user media, error { ', err, ' }.'); 
    });
  },
  record    : {
    init : function() {
      var bitsPerSecond = 512 * 8 * 1024,
          options       = { mimeType : 'video/webm', bitsPerSecond : bitsPerSecond };
      try {
        VideoChat.recorder = new MediaRecorder(VideoChat.video.local.stream, options);
        VideoChat.reader   = new FileReader();
      }
      catch (e0) {
        console.log('Unable to create MediaRecorder with options Object: ', e0);
        try {
          options = { mimeType : 'video/webm,codecs=vp9', bitsPerSecond : bitsPerSecond };
          VideoChat.recorder = new MediaRecorder(window.stream, options);
        } catch (e1) {
          console.log('Unable to create MediaRecorder with options Object: ', e1);
          try {
            options = 'video/vp8'; // Chrome 47
            VideoChat.recorder = new MediaRecorder(window.stream, options);
          } catch (e2) {
            VideoChat.alert({title : 'MediaRecorder is not supported by this browser', text : 'Try Firefox 29 or later, or Chrome 47 or later, with Enable experimental Web Platform features enabled from chrome://flags.', type : 'warning'});
            console.error('Exception while creating MediaRecorder:', e2);
            return;
          }
        }
      }
      VideoChat.recorder.onstop = function(event) {
        console.log('Recorder stopped');
        VideoChat.send({ id : VideoChat.video.local.user.id, otherid : VideoChat.video.remote.user.id, type : 'record' });
      };
      VideoChat.recorder.ondataavailable = function(event) {
        if (event.data && event.data.size > 0) {
          VideoChat.reader.readAsArrayBuffer(event.data);
          VideoChat.reader.onloadend = function(event) {
            if(null != VideoChat.reader) {
              VideoChat.service.wsc.send(VideoChat.reader.result);
            }
          };
        }
      };
      console.log('Recorder started');
      VideoChat.recorder.start(10);
    },
    ask : function(callback) {
      /*
      if(!is_patient) {
        if (confirm('Do you want to record the patient conversation?') == true) {
            callback(true);
        }
        else {
            callback();
        }
      }
      else {
      */
        callback();
      //}
    }
  },
  ringing   : function() {
    VideoChat.audio.play();
    VideoChat.alert({
      cancelButtonText   : 'Decline',
      closeOnConfirm     : true,
      confirmButtonColor : '#2b8c36',
      confirmButtonText  : 'Answer',
      imageUrl           : '/img/ringing.gif',
      showCancelButton   : true, 
      text               : 'Is calling you...',
      title              : VideoChat.video.remote.user.name
    }, function(isConfirm) {
      if (isConfirm) {
        $('#chat').click();
        VideoChat.callback = function() { VideoChat.answer(true); };
      }
      else {
        VideoChat.leave();
      }
    });
  },
  send      : function(msg) { this.service.wsc.send(JSON.stringify(msg)); },
  title     : function(text) { $('.fancybox-title > span').text(text); },
  muteMic   : function(){
    console.info('MuteMic');
    //mediaStream.getAudioTracks()[0].enabled = true; // or false to mute it.
    //VideoChat.video.local.stream.getVideoTracks()[0].stop();
    //mediaStream.getVideoTracks()[0].enabled = !(mediaStream.getVideoTracks()[0].enabled);
    if(VideoChat.video.local.stream.getAudioTracks()[0].enabled){
        muteMicbutton.classList.add('icon-volume');
        muteMicbutton.classList.remove('icon-volume-2');
        
        console.info('video_chat.js entra video muted true volumen = ');
    }
    else{
        console.info('video_chat.js entra video muted false else');
        muteMicbutton.classList.add('icon-volume-2');
        muteMicbutton.classList.remove('icon-volume');
    }
    VideoChat.video.local.stream.getAudioTracks()[0].enabled = !(VideoChat.video.local.stream.getAudioTracks()[0].enabled);
  }
};

var Loading = {
  show : function(container) {
    if(0 == container.find('#loading').length) {
      container.prepend('<div id="loading"><img src="/img/loading.gif" alt="Loading..." /></div>')
    }
  },
  hide : function(container) {
    if(0 < container.find('#loading').length) {
      container.find('#loading').remove()
    }
  }
};

var Error = {
  show  : function(container) {
    if(0 == container.find('#error').length) {
      container.prepend('<div id="error" onClick="VideoChat.ready();">To use VPExam Video call you need to allow the camera and microphone permissions.</div>')
               .find('#error').slideDown('fast');
    }
  },
  hide  : function(container) {
    if(0 < container.find('#error').length) {
      container.find('#error').remove()
    }
  }
};

var Permissions = {
  show : function() {
    $('body').append('<div id="modal"></div><div id="message" ' + (isChrome?'class="forChrome"':'') + '><a href="javascript:void(0);" onClick="Permissions.hide();">X</a><img src="/img/arrow.png" alt="|" /><h1>click "Allow" above to turn on your webcam.</h1></div>');
    $('#modal').fadeIn('300');
    $('#message').fadeIn('500');
  },
  hide : function() {
    $('#modal').remove();
    $('#message').remove();
  }
}
var blPlayed=false;
var SoundTest = {
  show : function() {
      $('#modalSoundTest').slideUp('fast',function(){
    $('#modalSoundTest').removeClass('hide').slideDown('fast');
  }); 
  $('#messageSoundTest').slideUp('fast',function(){
    $('#messageSoundTest').removeClass('hide').slideDown('fast');
  }); 
    
  },
  hide : function() {
      $('#modalSoundTest').slideUp('fast',function(){
    $('#modalSoundTest').addClass('hide').slideDown(0);
  }); 
  $('#messageSoundTest').slideUp('fast',function(){
    $('#messageSoundTest').addClass('hide').slideDown(0);
  }); 
  }
}
function fncShowBandwDlg()
{
    if(netBandwidth>=5)
        swal ( "Bandwidth Test" ,  "You have a high internet connection!" ,  "success" )
    else if(netBandwidth>1 && netBandwidth<5)
        swal ( "Bandwidth Test" ,  "You have a regular internet connection!" ,  "warning" )
    else if(netBandwidth>0)
        swal ( "Bandwidth Test" ,  "You have a low internet connection!" ,  "error" )
    else
        swal ( "Bandwidth Test" ,  "No internet connection detected!" ,  "error" )
}
function fncChangeImg(blValue)
{
    $("#imgSound").attr("src","images/audio_red.png");
    if(blValue && blPlayed)
    {
        $("#imgSound").attr("src","images/audio_green.png");
    }
    else if(!blPlayed){
        swal ( "Sound Test" ,  "Please, press play button!" ,  "error" )
    }
    SoundTest.hide();   
    blPlayed=false;
}
$(document).ready(function() {
    $('#lnSound').on('click', function () {
        SoundTest.show();
    });    
  //if(/^\/(patient_main|patient_view)\.php(.*)$/g.test(window.location.pathname)) {
  if(/^\/(patient_view)\.php(.*)$/g.test(window.location.pathname)) {
    $('#chat').removeClass('hide');
  }
 
   document.getElementById('audSoundTest').addEventListener('play', function(){ blPlayed=true; });
 
 
  if('' != $('#caller').val()) {
    var caller           = JSON.parse($('#caller').val()),
        storeStreamError = function(obj) {
          var devices;
          if(obj.id) {
            var audioTracks = obj.getAudioTracks();
            var videoTracks = obj.getVideoTracks();
            
            if(audioTracks[0].muted)
                $("#imgMic").attr("src","images/mic_red.png");
            else if(!audioTracks[0].muted)
                $("#imgMic").attr("src","images/mic_green.png");
            else
                $("#imgMic").attr("src","images/mic_black.png");

            if(videoTracks[0].enabled)
                $("#imgCamera").attr("src","images/camera_green.png");
            else if(!videoTracks[0].enabled)
                $("#imgCamera").attr("src","images/camera_red.png");
            else
                $("#imgCamera").attr("src","images/camera_black.png");             

            devices = {
              audio: {
                id         : audioTracks[0].id, 
                kind       : audioTracks[0].kind, 
                label      : audioTracks[0].label, 
                muted      : audioTracks[0].muted,
                enabled    : audioTracks[0].enabled,
                readyState : audioTracks[0].readyState
              },
              video: {
                id         : videoTracks[0].id, 
                kind       : videoTracks[0].kind, 
                label      : videoTracks[0].label, 
                muted      : videoTracks[0].muted,
                enabled    : videoTracks[0].enabled,
                readyState : videoTracks[0].readyState
              }
            }
            $.ajax({
              url    : '/includes/storeStreamError.php',
              method : 'POST',
              data   : {
                id      : caller.id,
                msg     : (obj.message && '' != obj.message?obj.message:(obj.name && '' != obj.name?obj.name:obj.id)),
                devices : JSON.stringify(devices)
              }
            });
          }
          else { 
                $("#imgCamera").attr("src","images/camera_red.png");
                $("#imgMic").attr("src","images/mic_red.png");
            navigator.mediaDevices.enumerateDevices()
            .then(function(devices) {
              devices = devices.map(function(device) {
                var mapped = {}
                mapped[device.kind] = device.label
                return mapped
              })
              $.ajax({
                url    : '/includes/storeStreamError.php',
                method : 'POST',
                data   : {
                  id      : caller.id,
                  msg     : (obj.message && '' != obj.message?obj.message:(obj.name && '' != obj.name?obj.name:obj.id)),
                  devices : JSON.stringify(devices)
                }
              });
            })
            .catch(function(err) {
                console.log(err.name + ": " + err.message);
            });
          }
        };
    VideoChat.init(caller);
    // check browser WebRTC availability
    navigator.mediaDevices
    .getUserMedia(constraints)
    .then(storeStreamError)
    .catch(storeStreamError);
  }

  if(isIE) {
    $('#chat').attr('href', 'javascript:void(0);')
              .click(function() {
                swal({
                  title: 'Unsupported browser',
                  text: 'Your browser does not support <span style="color: #3051a6;">VPExam Video call</span>. Please use Google Chrome or Firefox',
                  html: true,
                  type: 'warning'
                });
              });
  }
  else {
    $('#chat').fancybox({
      aspectRatio: true,
      autoSize   : false,
      closeClick : false, // prevents closing when clicking INSIDE fancybox 
      fitToView  : false, 
      padding    : 0,
      title      : 'Video chat',
      //height     : (screen.height/2) + 'px',
      scrolling  : false,
      helpers    : { 
        overlay : { closeClick: false } // prevents closing when clicking OUTSIDE fancybox 
      },
      afterShow  : function() {
        VideoChat.ready(JSON.parse($('#calling').val()));
      },
      afterClose : VideoChat.leave
    });
  }
  
  muteMicbutton.addEventListener('click', function(){
    VideoChat.muteMic();
  });
});

//TODO: Stop rining for caller when user called decline

