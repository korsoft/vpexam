$(document).on("ready", function() {
//$(document).ready(function(){
    var selectedMenuButton = 'home';

    /*var mSwiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        direction: 'horizontal',
        nextButton: '.swiper-button-next.swiper-button-white',
        prevButton: '.swiper-button-prev.swiper-button-white',
        paginationClickable: true,
        spaceBetween: 30,
        centeredSlides: true,
        autoplay: 2500,
        autoplayDisableOnInteraction: false
    });

    $('#googlePlayLink').on('click', function() {
        alert("The app is coming soon!");
    });*/
    $('#btnLogin').on('click', function() {
        hashForm();
        $('#loginForm').submit();
    });

    $('#btnTopLogin').on('click', function() {
        $('#sidebarLoginPhysician').toggle('slide', {
            complete: function() {
                if (!$('#sidebarLoginPhysician').is(':visible')) {
                    $('#btnTopLogin').css({
                        'background-color': '',
                        'cursor': ''
                    });
                }
            },
            direction: 'up',
            duration: 250,
            easing: 'linear'
        });
    }).on('mouseenter', function() {
        $('#btnTopLogin').css({
            'background-color': '#67c9e0',
            'cursor': 'pointer'
        });
    }).on('mouseleave', function() {
        if (!$('#sidebarLoginPhysician').is(':visible')) {
            $('#btnTopLogin').css({
                'background-color': '',
                'cursor': ''
            });
        }
    });

    $('#btnTopContact').on('mouseenter', function() {
        $('#btnTopContact').css({
            'background-color': '#67c9e0',
            'cursor': 'pointer'
        });
    }).on('mouseleave', function() {
        $('#btnTopContact').css({
            'background-color': '',
            'cursor': ''
        });
    });

    $('#btnRegister').on('click', function() {
        window.location = "register_patient.php";
    });

    $('#btnRequestTrial').on('click', function() {
        window.location = "register_physician.php";
    });
   
});

function hashForm() {
    var $pwd = $('#password');
    var $hashedPwdElem = $('<input id="pwdHashed" name="p" type="hidden" />');
    $hashedPwdElem.val(hex_sha512($pwd.val()));
    $pwd.val("");
    $('#loginForm').append($hashedPwdElem);
}

var player, iframe, player2, iframe2;
var $$ = document.querySelector.bind(document);

// init player
function onYouTubeIframeAPIReady() {
  player = new YT.Player('player', {
    height: '200',
    width: '300',
    videoId: 'CK6_Gcn5JCE',
    origin: 'https://dev.vpexam.com/',
    playerVars : {
        'rel':0,
        'loop':1,
        'controls':0,
        'showinfo':0,
        'playlist':'CK6_Gcn5JCE',
        'modestbranding':1
    },
    events: {
      'onReady': onPlayerReady
    }
  });
  player2 = new YT.Player('player2', {
    height: '200',
    width: '300',
    videoId: 'PNkMrz056X0',
    origin: 'https://dev.vpexam.com/',
    playerVars : {
        'rel':0,
        'autoplay': 1,
        'loop':1,
        'controls':0,
        'showinfo':0,
        'playlist':'PNkMrz056X0',
        'modestbranding':1
    },
    events: {
      'onReady': onPlayerReady2
    }
  });
  //player2.loadVideoById("PNkMrz056X0", 5, "large")
}

// when ready, wait for clicks
function onPlayerReady(event) {
  var player = event.target;
  iframe = $$('#player');
  /*var player2 = event.target;
  iframe2 = $$('#player2');
  player2.playVideo();*/
  setupListener(); 
}
function onPlayerReady2(event) {
    var player2 = event.target;
    iframe2 = $$('#player2');
    player2.setVolume(100);
    player2.playVideo();
}

function setupListener (){
$$('.watchVideo').addEventListener('click', playFullscreen);
}

function playFullscreen (){
    $('#superplayer').css("display","block")
  player.playVideo();//won't work on mobile https://www.youtube.com/watch?v=CK6_Gcn5JCE
  
  /*var requestFullScreen = iframe.requestFullScreen || iframe.mozRequestFullScreen || iframe.webkitRequestFullScreen;
  if (requestFullScreen) {
    requestFullScreen.bind(iframe)();
  }*/
}
$('#cerrarYB').click(function(){
    player.stopVideo();
    $('#superplayer').css("display","none")
});
console.log('keyup');
$(document).keyup(function(e) {
    console.log(e.keyCode);
    if (e.keyCode == 27) {
        player.stopVideo();
        $('#superplayer').css("display","none")
    }
});

