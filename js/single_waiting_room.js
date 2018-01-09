var oWorker;
var blnLoadWorker   = false;
var _patients       = {};
var tempResultOfFoo = {};

/**
 * Funcion que carga la instancia del worker para poderla usar
 *
 * @param void
 *
 * @return void
 **/
$( window ).on( "load", function() {
    $('#imgToolPhys').on('click', function () {
        var intWidth = null;
        intWidth = (document.getElementById("divSidenavPhys").style.right==='-200px' || document.getElementById("divSidenavPhys").style.right==='')?"0px":"-200px";
        document.getElementById("divSidenavPhys").style.right = intWidth;
    });
    if(typeof(Worker) !== "undefined") {
        if(typeof(oWorker) == "undefined") {
            oWorker = new Worker('/js/single_waiting_room_worker.js');
        }
        oWorker.onmessage = function(event) {
            // console.info( event.data) ;
            if( event.data.command == 'patients' ){
                var patients = event.data.result;
                // console.info(patients);
                $.map(patients, function( item, numIndex) {
                    if( item.command == 'remove' ){
                        removePatients( numIndex );
                    }else if(item.command == 'append'){
                        appendPatient( numIndex, item );
                    }
                });
            }
        };
        blnLoadWorker = true;
        setTimeout('tempResultOfFoo.startWorker();', 2000);
    } else {
        console.info( "Sorry! No Web Worker support.") ;
    }
});


/**
 * Funcion que remueve un paciente por medio de su id
 *
 * @param String strKey id del paciente
 *
 * @return void
 **/
function removePatients( strKey ){
    //Eliminar funcion de videollamada
    $('#tr_menu-li-patient' + strKey + ' .chat_open').prop('onclick',null).off('click');
    //Cambiar funcion de la "X" después de dar clic en eliminar de waiting room
    $('.classRemoveWR_'+strKey).attr('onclick','removeFromMyPatients('+strKey+')');
    //Ocultar icono de videollamada
    $('#tr_menu-li-patient' + strKey + ' .chat_open > div').css('display','none');
}

function removeFromMyPatients(patientId){
    console.log('removeFromMyPatients()');
    $.ajax({
        success: function(data, status, jqxhr) {
            if (data) {
                if (data.success)
                    $('#tr_menu-li-patient' + patientId)[0].remove();
                else
                    alert("There was an error while deleting this row: " + data.error);
            } else {
                alert("There was an error while deleting this row");
            }
        },
        data: 'physId=' + physicianId + '&patientId=' + patientId,
        dataType: 'json',
        method: 'POST',
        url: 'includes/removeFromMyPatients.php'
    });
}
/**
 * Funcion que agrega un paciente
 *
 * @param String strKey       id del paciente
 * @param Object oJsonPatient objeto json con la informacion del paciente
 *
 * @return void
 **/
function appendPatient( strKey, oJsonPatient ){
    _patients[ oJsonPatient.id ] = {
                             pat_id : oJsonPatient.id,
                             pat_name: oJsonPatient.name
                          }; 
    var strName    = (oJsonPatient.name).replace( /\"/g, "\\\"");
    var imguploaded='';
    if(oJsonPatient.uploaded ==1){
        imguploaded ="<img src='/img/green_check.png' width='30' height='30'>";
    }
    var strElement = "<tr class=\"hoverableRow patientsTable\" id='tr_menu-li-patient" + oJsonPatient.id + "'>" +
                     "<td class=\"shortColumn patientsTable\" id='" + oJsonPatient.id + "'  data-id='" + oJsonPatient.id + "' data-name='" + strName + "'>" +
                     "<a href=\"\/patient_view.php?patientId="+ oJsonPatient.id +"\" target=\"_self\"><img class=\"patientProfilePic\" src=\"/includes/getProfileImage.php?id=" + oJsonPatient.id + "&type=1\"></a>" +
                     "<div class=\"nameMRNDiv\">" +
                     "<div style=\"margin: 20px 0 0 0;\"> " +
                     "<div><a href=\"\/patient_view.php?patientId="+ oJsonPatient.id +"\" target=\"_self\">" + oJsonPatient.name + "</a></div>" +
                     "</div>" +
                     "</div>" +
                     "</td>" +
                     "<td class=\"shortColumn\" data-id='" + oJsonPatient.id + "' data-name='" + strName + "'>" +
                     "    <div class=\"nameMRNDiv\">" +
                     "        <div style=\"margin: 20px 0 0 0;\">" +
                     "            <div><a href=\"\/patient_view.php?patientId="+ oJsonPatient.id +"\" target=\"_self\">" + oJsonPatient.lastName + "</a></div> " +
                     "        </div>" +
                     "    </div> " +
                     "</td>" +
                     "<td class=\"shortColumn\" data-id='" + oJsonPatient.id + "' data-name='" + strName + "'>" +
                     "    <div class=\"nameMRNDiv\">" +
                     "        <div style=\"margin: 20px 0 0 0;\">" +
                     "            <div>" + oJsonPatient.dob + "</div> " +
                     "            <div>" + oJsonPatient.gender + "</div>" +
                     "        </div>" +
                     "    </div> " +
                     "</td>" +
                     "<td class=\"longColumn\" data-id='" + oJsonPatient.id + "' data-name='" + strName + "'> "+
                     "    <div class=\"nameMRNDiv\">"+
                     "        <div style=\"margin: 20px 0 0 0;\">"+
                     "            <div>" + oJsonPatient.address + "</div>" +
                     "            <div>" + oJsonPatient.phone + "</div>" +
                     "        </div>" +
                     "    </div>" +
                     "</td>" +
                     "<td class=\"shortColumn chat_open\" onclick='openWRChat( this );' data-id='" + oJsonPatient.id + "' data-name='" + strName + "'>" +
                     "    <div class=\"nameMRNDiv\">" +
                     "        <div style=\"margin: 6px 0 0 0;\">" +
                     "            <div>" + imguploaded + "</div> " +
                     "        </div>" +
                     "    </div> " +
                     "</td>" +
                     "<td class=\"longColumn chat_open\" onclick='openWRChat( this );' data-id='" + oJsonPatient.id + "' data-name='" + strName + "'>" +
                     "    <div class=\"nameMRNDiv\">" +
                     "        <div style=\"margin: 6px 0 0 0;\">" +
                     "            <div><img src='/img/video-camera4.png'></div> " +
                     "        </div>" +
                     "    </div> " +
                     "</td>" +
                     "<td class=\"classRemoveWR classRemoveWR_" + oJsonPatient.id + "\" onclick='removeFromWR(" + oJsonPatient.id + ");'>" +
                     "    <div id=\"" + oJsonPatient.id + "\" class=\"removePatientOuter\">" +
                     "        <div class=\"removePatientInner\">X</div>"+
                     "    </div>"+
                     " </td>" +
                     "</tr>" ;

    $('div.PatientsWaiting table tbody').prepend(strElement);

    var numCount = $('div.PatientsWaiting table tbody').children('tr').length;
    if(numCount > 0 ){
        $('div.PatientsWaitingEmpty').addClass('Hide');
        $('div.PatientsWaiting').removeClass('Hide');
    }else{
        $('div.PatientsWaitingEmpty').removeClass('Hide');
        $('div.PatientsWaiting').addClass('Hide');
    }
}


/**
 * Funcion que se llama para borrar a un paciente por el doctor
 *
 * @param integer numId id del paciente
 *
 * @return void
 **/
function removeFromWR( numId ){
    removePatients( numId );
    WaitingRoom.physician.removePatientFromWR(_numPhysicianId, numId );
}

/**
 * Funcion que se llama para borrar a un paciente por el doctor
 *
 * @param integer numId id del paciente
 *
 * @return void
 **/
function openWRChat( oElement ){
    var strName =  oElement.getAttribute('data-name');
    var numId   =  oElement.getAttribute('data-id');

    // console.info( strName );
    // console.info( numId );
    if(isIE) {
        swal({
            title: 'Unsupported browser',
            text: 'Your browser does not support <span style="color: #3051a6;">VPExam Video call</span>. Please use Google Chrome or Firefox',
            html: true,
            type: 'warning'
        });
    }
    else {
        var calling = {
            id   : numId,
            name : strName 
        };
        $('#calling').val(JSON.stringify(calling));
        $('#chat').click();
    }
}

/**
 * Funcion que inicializa el webworker
 *
 * @param void
 *
 * @return void
 **/
tempResultOfFoo.startWorker = function() {
    if( blnLoadWorker){
        // console.info( 'startWorker() Inicializa el webworker' );
        var oMessage = {
                          physician_id : _numPhysicianId,
                       }
        oWorker.postMessage({'cmd': 'start', 'msg': oMessage});
        $('div.PatientsWaitingLoading').addClass('Hide');
        $('div.PatientsWaitingEmpty').removeClass('Hide');

    }else{
        setTimeout('tempResultOfFoo.startWorker();', 2000);
    }
};
