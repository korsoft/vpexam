var physicianId = -1,
    isIE        = /(MSIE|Edge)/.test(window.navigator.userAgent);
var dataSet0 = null;
var tabla = null;
$(document).on("ready", function() {
     $('#imgToolPhys').on('click', function () {
        var intWidth = null;
        intWidth = (document.getElementById("divSidenavPhys").style.right==='-200px' || document.getElementById("divSidenavPhys").style.right==='')?"0px":"-200px";
        document.getElementById("divSidenavPhys").style.right = intWidth;
    });
    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });

    $('.toggle-button').on('click', function() {
        slideout.toggle();
    });

    $('.patientsTable').each(function(idx, val) {
        $(val).on('click', function() {
            location.href = ("patient_view.php?patientId=" + $(this).attr('id'));
        });
    });
    $('.patientsTable.wrclass').each(function(idx, val) {
        $(val).on('click', function() {
            location.href = ("patient_view.php?patientId=" + $(this).attr('id') + "&wr=1");
        });
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        location.href = "https://doxy.me/sign-in";
    });

    $('#btnMenuMyPatients').on('click', function() {
        // Already here
        location.href = '/physician_main.php';
    });

    $('#btnMenuSearch').on('click', function() {
        location.href = "search.php";
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    $('.removePatientOuter').on('click', function(event) {
        event.stopPropagation();
        removeFromMyPatients($(this).parent().parent().attr('id'));
    });
    $('.waitingRoom ul li label').on('click', function() {
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
                id   : $(this).parent().data('id'),
                name : $(this).text()
            };
            $('#calling').val(JSON.stringify(calling));
            $('#chat').click();
        }
    });
    $.ajax({
        success: function(info) {
            if (info) {
                if (info.success){
                    dataSet0 = info.data;
                    tabla = $('#example').DataTable( {
                        data: dataSet0,
                        order: [[1, 'desc']],
                        columns: [
                        {     data: 'patientId',
                             'render': function (patientId) {
                                return '<img class="patientProfilePic" src="includes/getProfileImage.php?id='+patientId+'&type=1" />';
                            },
                            targets: [0], 
                            searchable: false, 
                            sorting: false,
                            orderable: false, 
                            visible: true
                        },           
                        { data: 'firstName' },
                        { data: 'lastName' },
                        {
                            data: 'dob',
                            'render': function (dob) {
                                var date = dob.replace(/(\d{4})-(\d{2})-(\d{2})/, '$2-$3-$1');
                                return date;
                            }
                        },            
                        {
                            data: 'gender',
                            'render': function (gender) {
                                return (gender=='male' ? 'Male' : 'Female');
                            }
                        },        
                        {
                            data: 'uploaded',
                            'render': function (uploaded) {
                                return (uploaded==1 ? '<img src="/img/check.png" width="30" height="30">' : '');
                            }
                        },    
                        {
                             data: 'waitingroom',
                            'render': function ( waitingroom, type, row, meta ) {
                                var json = JSON.parse(JSON.stringify(row));
                                return (waitingroom!=null && waitingroom!='' ? '<button type="button" class="btnwr" onclick="openWRChat('+json.patientId+',\''+json.firstName+'\')">Go to Waiting room!</button>' : '');
                            }
                        },
                        {
                             data: 'register_at',
                            'render': function ( waitingroom, type, row, meta ) {
                                var json = JSON.parse(JSON.stringify(row));
                                var value = (json.register_at).substr(0,10);
                                var datereg = value.replace(/(\d{4})-(\d{2})-(\d{2})/, '$2-$3-$1');
                                return(datereg);
                            }
                        },
                        {     
                            data: 'patientId',
                             'render': function (patientId) {
                                return '<div onclick="removeFromWR('+patientId+');" class="removePatientOuter" id="'+patientId+'"><div class=\"removePatientInner\">X</div></div>';
                            },
                            targets: [0], 
                            sorting: false,
                            searchable: false, 
                            orderable: false, 
                            visible: true
                        }                              
                    ]
                    } );         
                    var table = $('#example').DataTable();
                    $('#example tbody').on('click', 'td', function () {
                       if(table.cell( this ).index().columnVisible<3)
                       {
                            var data = table.row( this ).data();
                            var json = JSON.parse(JSON.stringify(data));
                            location.href = ("patient_view.php?patientId=" + json.patientId+(json.waitingroom!=null && json.waitingroom!=''?'&wr=1':''));
                        }
                    } );                    
                    
                }else{
                    alert("There was an error getting patient list");
                }  
            } else {
                alert("There was an error getting patient list");
            }
        },
        data: 'physicianid=' +_numPhysicianId,
        dataType: 'json',
        method: 'POST',
        url: '/includes/getpatientphysicianwaitingroom.php'
    });    
});

function setPhysicianId(id) {
    physicianId = id;
}

function search(physicianId) {
    var firstName = document.getElementById("fname").value;
    var lastName = document.getElementById("lname").value;
    if (firstName == "" && lastName == "") {
        window.alert("You must enter a first name or last name before searching.");
    } else {
        var cbs = document.getElementsByClassName("genderCheckbox");
        var gender = "&gender=";
        gender += ((cbs[0].checked) ? cbs[0].value : "");
        gender += ((cbs[1].checked) ? cbs[1].value : "");
        window.location = "search_results.php?fname=" + encodeURIComponent(firstName) + "&lname=" + encodeURIComponent(lastName) + gender + "&physId=" + physicianId;
    }
}

function addToRecentList(physId, patientId) {
    $.ajax({
        success: function(data, status, jqxhr) {
            if (status === "success") {
                var json = JSON.parse(data);
                if (!json.success) {
                    alert("Error saving patient to recent patients list.");
                }
            }
        },
        method: 'GET',
        url: 'includes/addToRecentList.php?physId=' + physId + '&patientId=' + patientId
    });
}

function removeFromRecentList(idx, patientId) {
    $.ajax({
        success: function(data, status, jqxhr) {
            if (status === "success") {
                var json = JSON.parse(data);
                if (!json.success) {
                    alert("Error saving patient to recent patients list.");
                } else {
                    $('#recentlyViewedTable')[0].deleteRow(idx);
                }
            }
        },
        method: 'GET',
        url: 'includes/removeFromRecentList.php?physId=' + physicianId + '&patientId=' + patientId
    });
}

function removeFromMyPatients(patientId) {
    //var table = $('.tableContent').DataTable();
    $.ajax({
        success: function(data, status, jqxhr) {
            if (data) {
                if (data.success){
                    //$('#' + patientId)[0].remove();
                    
                    table.row('#' + patientId).remove().draw();
                }else{
                    alert("There was an error while deleting this row: " + data.error);
                }  
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

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function openWRChat( numId,strName){
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
 * Funcion que se llama para borrar a un paciente por el doctor
 *
 * @param integer numId id del paciente
 *
 * @return void
 **/
function removeFromWR( numId ){
    
swal({
  title: "Are you sure you want to delete this patient?",
  text: "You will not be able to recover this information!",
  type: "warning",
  showCancelButton: true,
  confirmButtonClass: "btn-danger",
  confirmButtonText: "Accept",
  cancelButtonText: "Cancel",
  closeOnConfirm: false,
  closeOnCancel: true
},
function(isConfirm) {
  if (isConfirm) {
    $.ajax({
        success: function(data, status, jqxhr) {
            if (data) {
                if (data.success)
                {
                    var intAx = null;
                    $.each(dataSet0, function(key, value) {
                        var objDataSet = JSON.parse(JSON.stringify(value));
                        if(objDataSet.patientId==numId)
                        {
                            if(typeof dataSet0[key] === 'undefined') {
                                console.log('Error : element no exist.'); 
                            }
                            else 
                                intAx=key;
                        }
                    });         

                    dataSet0.splice(intAx, 1); 
                    swal("Deleted!", "This information was deleted successfully!", "success");                    
                    var myDataTable = $('#example').DataTable();
                    myDataTable.clear().draw();
                    myDataTable.rows.add(dataSet0); // Add new data
                    myDataTable.columns.adjust().draw(); // Redraw the DataTable 
                }
                else
                    alert("There was an error while deleting this row: " + data.error);
            } else {
                alert("There was an error while deleting this row");
            }
        },
        data: 'physId=' + physicianId + '&patientId=' + numId,
        dataType: 'json',
        method: 'POST',
        url: 'includes/removeFromMyPatients.php'
    });    
  }
});    
    


    WaitingRoom.physician.removePatientFromWR(physicianId, numId );
}