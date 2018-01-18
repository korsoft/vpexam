var BANDWITDH = (function(){
  var imageAddr;
  var size;
  var startTime, endTime;
  var downloadSize;
  var download = new Image();

  return {
      init: function(callback, address, size){
          imageAddr = address !== undefined ? address : "https://vpexam.com/images/cat.JPG" + "?n=" + Math.random(); 
          downloadSize = size !== undefined ? size : 5616998; 
          startTime = (new Date()).getTime();
          download.src = imageAddr;
          download.onerror = function() {
              if($('#divBW').length==1 && typeof(WaitBW)=='object' && typeof(WaitBW.hide)=='function'){
                  swal ( "Internet connection test failed" ,  "An error occurred, please, try again!" ,  "error" )
                  WaitBW.hide();
              }
          }
          download.onload = function() {
            endTime = (new Date()).getTime();
            var duration = (endTime - startTime) / 1000;
            var bitsLoaded = downloadSize * 8;
            var speedBps = (bitsLoaded / duration).toFixed(2);
            var speedKbps = (speedBps / 1024).toFixed(2);
            var speedMbps = (speedKbps / 1024).toFixed(2);
            callback(speedMbps);
         }
      },
    } 
})();