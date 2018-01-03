var _patients     = {};
var _blnIsValid   = true;
var _physician_id = 0;

// Listener que procesa los comandos del webworker
self.addEventListener('message', function(e) {
  var data    = e.data;
  _blnIsValid = true;
  switch (data.cmd) {
    case 'start':
         self.postMessage('WORKER STARTED: ' );
         self.postMessage(data.msg);
      break;
    case 'pause':
         self.postMessage('WORKER PAUSED: ' );
      break;
    case 'restart':
         self.postMessage('WORKER RESTARTED: ' );
      break;
    default:
      self.postMessage('Unknown command: ' + data.msg);
      _blnIsValid = false;
  };
  if(_blnIsValid){
     _physician_id = data.msg.physician_id;
     _patients     = {};
     getPatientsActived();
   }
}, false);

/**
 * Funcion que sirve para pedir los puntos de los empleados del customer
 *
 * @param void
 *
 * @return void
 **/
function getPatientsActived(){
    //console.info('getPatientsActived() :: worker is working');
    load(function(xhr) {	
        var result      = JSON.parse( xhr.responseText );
        var arrPatients = result.patients;
        var tmpPatients = _patients;
 
        for (var numIndex in tmpPatients){
            var item = tmpPatients[numIndex];
            item.command        = 'remove';
            _patients[numIndex] = item;
        }
 
        for (var i in arrPatients){
            var item = arrPatients[i];
            var oPatient;
            var numIndex = item.id ;
            if( _patients[ numIndex] ){
                oPatient             = _patients[ numIndex ];
                oPatient.command     = 'none';
                oPatient.id          = item.id;
                oPatient.name        = item.name;
                //oPatient.lastName    = item.lastName;
                oPatient.address     = item.address;   
                oPatient.dob         = item.dob;       
                oPatient.gender      = item.gender;    
                oPatient.mrn         = item.mrn;      
                oPatient.phone       = item.phone;  
                oPatient.uploaded    = item.uploaded;     
            }else{
                oPatient = {
                           id        : numIndex,
                           name      : item.name,
                           lastName  : item.lastName,
                           address   : item.address,
                           dob       : item.dob,
                           gender    : item.gender,
                           mrn       : item.mrn,
                           phone     : item.phone,
                           uploaded  : item.uploaded,
                           command   : 'append'
                        }; 
            }
            _patients[numIndex] = oPatient;
        }
 
        var message      = {
                               'command' : 'patients',
                               'result'  : _patients
                           }
        self.postMessage( message );
 
        var tmpPatients   =_patients;
 
        for (var numIndex in tmpPatients){
            var item = tmpPatients[numIndex];
            if( item.command == 'remove' ){
                delete _patients[numIndex];
            }
        }
    });
    setTimeout("getPatientsActived()",15000);
}

/**
 * Funcion que sirve para cargar una peticion ajax 
 *
 * @param callback funcion se se ejecutara para procesar el resultado
 *
 * @return void
 **/
function load(callback) {
	var xhr;

	if(typeof XMLHttpRequest !== 'undefined') xhr = new XMLHttpRequest();
	else {
		var versions = ["MSXML2.XmlHttp.5.0", 
			 	"MSXML2.XmlHttp.4.0",
			 	"MSXML2.XmlHttp.3.0", 
			 	"MSXML2.XmlHttp.2.0",
			 	"Microsoft.XmlHttp"]

		for(var i = 0, len = versions.length; i < len; i++) {
		try {
			xhr = new ActiveXObject(versions[i]);
			break;
		}
			catch(e){
                       self.postMessage( e );
                }
		} // end for
	}
		
	xhr.onreadystatechange = ensureReadiness;
		
	function ensureReadiness() {
		if(xhr.readyState < 4) {
			return;
		}
			
		if(xhr.status !== 200) {
			return;
		}

		// all is well	
		if(xhr.readyState === 4) {
			callback(xhr);
		}			
	}
		
	xhr.open('POST', '/api/getPatientsFromWaitingRoom.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	xhr.send('physician_id=' + _physician_id );
}
