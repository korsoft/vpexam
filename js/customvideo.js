/*!
 * H5VF
 * HTML5 Video Framework
 * http://sarasoueidan.com/h5vf
 * @author Sara Soueidan
 * @version 1.0.0
 * Copyright 2013. MIT licensed.
 */
(function ($, window, document, undefined) {
    'use strict';

    $(function () {
        var video = document.getElementById('localVideo'),
            remoteVideo = ($('#remoteVideoPatient').length==1)?document.getElementById('remoteVideoPatient'):document.getElementById('remoteVideo'),
            container = ($('#custom-videoPatient').length==1)?document.getElementById('custom-videoPatient'):document.getElementById('custom-video'),
            playbutton = document.getElementById('playpause'),
            mutebutton = document.getElementById('mute'),
            fullscreenbutton = document.getElementById('fullscreen'),
            seek = document.getElementById('seekbar'),
            volume = document.getElementById('volumebar'),
            vval = volume.value,
            bufferbar = document.getElementById('bufferbar');
    
        if(video.autoplay){
            playbutton.classList.add('icon-pause');
            playbutton.classList.remove('icon-play');
        }
        video.addEventListener('playing', function(){
            seek.classList.add('light');
        }, false);    
    
        function playpause(){
            if(video.paused){
                video.play();
                playbutton.classList.add('icon-pause');
                playbutton.classList.remove('icon-play');
                seek.classList.add('light');
            }
            else{
                video.pause();
                playbutton.classList.add('icon-play');
                playbutton.classList.remove('icon-pause');
                seek.classList.remove('light');
            }
        }
        
        playbutton.addEventListener('click', playpause, false);
        video.addEventListener('click', playpause, false);
        var isFullscreen= false;
        fullscreenbutton.addEventListener('click', function() {
            
            if(!isFullscreen){
         
                if (remoteVideo.requestFullscreen) {
                    remoteVideo.requestFullscreen();
                } 
                else if (remoteVideo.mozRequestFullScreen) {
                    container.mozRequestFullScreen(); // Firefox
                } 
                else if (remoteVideo.webkitRequestFullscreen) {
                    remoteVideo.webkitRequestFullscreen(); // Chrome and Safari
                }
                else if (remoteVideo.msRequestFullscreen) {// IE
                    container.msRequestFullscreen();                    
                }
                isFullscreen=true;
                fullscreenbutton.classList.remove('icon-fullscreen-alt');
                fullscreenbutton.classList.add('icon-fullscreen-exit-alt');
            }
            else{
                if(document.cancelFullScreen) {
                    document.cancelFullScreen();
                } 
                else if(document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } 
                else if(document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
                else if(document.msExitFullscreen) {
                    document.msExitFullscreen();
                }                
                isFullscreen=false;
                fullscreenbutton.classList.add('icon-fullscreen-alt');
                fullscreenbutton.classList.remove('icon-fullscreen-exit-alt');
            }
        }, false);

        if (document.addEventListener)
        {
            document.addEventListener('webkitfullscreenchange', exitHandler, false);
            document.addEventListener('mozfullscreenchange', exitHandler, false);
            document.addEventListener('fullscreenchange', exitHandler, false);
            document.addEventListener('MSFullscreenChange', exitHandler, false);
        }

        function exitHandler()
        {
            if (document.webkitIsFullScreen === false)
            {
                isFullscreen=false;
                fullscreenbutton.classList.add('icon-fullscreen-alt');
                fullscreenbutton.classList.remove('icon-fullscreen-exit-alt');
            }
            else if (document.mozFullScreen === false)
            {
                isFullscreen=false;
                fullscreenbutton.classList.add('icon-fullscreen-alt');
                fullscreenbutton.classList.remove('icon-fullscreen-exit-alt');
            }
            else if (document.msFullscreenElement === false)
            {
                isFullscreen=false;
                fullscreenbutton.classList.add('icon-fullscreen-alt');
                fullscreenbutton.classList.remove('icon-fullscreen-exit-alt');
            }
        }
    
        //change video time when seek changes
        seek.addEventListener('change', function(){
            var time = video.duration * (seek.value/100);
            video.currentTime = time;
        }, false);

        seek.addEventListener('mousedown', function(){
            video.pause();
        }, false);
        seek.addEventListener('mouseup', function(){
            video.play();
            //if the user plays the video without clicking play, by starting directly with specifying a point of time on the seekbar, make sure the play button becomes a pause button
            playbutton.classList.remove('icon-play');
            playbutton.classList.add('icon-pause');
        }, false);

        //change seek position as video plays
        video.addEventListener('timeupdate', function(){
            var value = (100/video.duration) * video.currentTime;
            seek.value = value;
        }, false);
        
        volume.addEventListener('change', function(){
            video.volume = this.value;
            vval = this.value;
            if(this.value === 0){
                video.muted = true;
                mutebutton.classList.add('icon-volume');
                mutebutton.classList.remove('icon-volume-2');
            }
            else if(this.value !== 0){
                video.muted = false;
                mutebutton.classList.add('icon-volume-2');
                mutebutton.classList.remove('icon-volume');
            }
        }, false);
        
        video.addEventListener('ended', function(){
            video.pause();
            video.currentTime = 0;
            playbutton.classList.add('icon-play');
            playbutton.classList.remove('icon-pause');
            seek.classList.remove('light');
        });
    });

})(jQuery, window, document);

var module = {
        options: [],
        header: [navigator.platform, navigator.userAgent, navigator.appVersion, navigator.vendor, window.opera],
        dataos: [
            { name: 'Windows Phone', value: 'Windows Phone', version: 'OS' },
            { name: 'Windows', value: 'Win', version: 'NT' },
            { name: 'iPhone', value: 'iPhone', version: 'OS' },
            { name: 'iPad', value: 'iPad', version: 'OS' },
            { name: 'Kindle', value: 'Silk', version: 'Silk' },
            { name: 'Android', value: 'Android', version: 'Android' },
            { name: 'PlayBook', value: 'PlayBook', version: 'OS' },
            { name: 'BlackBerry', value: 'BlackBerry', version: '/' },
            { name: 'Macintosh', value: 'Mac', version: 'OS X' },
            { name: 'Linux', value: 'Linux', version: 'rv' },
            { name: 'Palm', value: 'Palm', version: 'PalmOS' }
        ],
        databrowser: [
            { name: 'Chrome', value: 'Chrome', version: 'Chrome' },
            { name: 'Firefox', value: 'Firefox', version: 'Firefox' },
            { name: 'Safari', value: 'Safari', version: 'Version' },
            { name: 'Internet Explorer', value: 'MSIE', version: 'MSIE' },
            { name: 'Opera', value: 'Opera', version: 'Opera' },
            { name: 'BlackBerry', value: 'CLDC', version: 'CLDC' },
            { name: 'Mozilla', value: 'Mozilla', version: 'Mozilla' }
        ],
        init: function () {
            var agent = this.header.join(' '),
                os = this.matchItem(agent, this.dataos),
                browser = this.matchItem(agent, this.databrowser);
            
            return { os: os, browser: browser };
        },
        matchItem: function (string, data) {
            var i = 0,
                j = 0,
                html = '',
                regex,
                regexv,
                match,
                matches,
                version;
            
            for (i = 0; i < data.length; i += 1) {
                regex = new RegExp(data[i].value, 'i');
                match = regex.test(string);
                if (match) {
                    regexv = new RegExp(data[i].version + '[- /:;]([\\d._]+)', 'i');
                    matches = string.match(regexv);
                    version = '';
                    if (matches) { if (matches[1]) { matches = matches[1]; } }
                    if (matches) {
                        matches = matches.split(/[._]+/);
                        for (j = 0; j < matches.length; j += 1) {
                            if (j === 0) {
                                version += matches[j] + '.';
                            } else {
                                version += matches[j];
                            }
                        }
                    } else {
                        version = '0';
                    }
                    return {
                        name: data[i].name,
                        version: parseFloat(version)
                    };
                }
            }
            return { name: 'unknown', version: 0 };
        }
    };
    
    var e = module.init(),
        debug = '';
    
   
