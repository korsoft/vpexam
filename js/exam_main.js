var patientGender = "";
var physicianId = -1;
var $modelImg;

$(document).on("ready", function() {
    var slideout = new Slideout({
        'panel': $('#panel')[0],
        'menu': $('#menu')[0],
        'padding': 256,
        'tolerance': 70
    });

    $('.toggle-button').on('click', function() {
        slideout.toggle();
    });

    $modelImg = $('#modelImg');

    $('.trExam').each(function(idx, val) {
        $(val).on('click', function() {
            location.href = $(this).data('link');
        });
    });

    $('#dialogConfirmDelete').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: {
            "Delete Exam": function() {
                removeExam();
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        width: 350
    });

    $('#btnMenuPatientOverview').on('click', function() {
        var patientId = getParameterByName('patientId');
        location.href = ("patient_view.php?patientId=" + patientId);
    });

    $('.remove_document').on('click', function(event) {
        event.preventDefault();
        removeDocument($(this).data('id'));
    });
    //Prueba editar
    $('.edit_document').click(function(){
        var trid ='#id_'+$(this).data('id');
        $(trid +' .edit_document').hide();
        $(trid +' td.examPartName2').each(function(){
            var content = $(this).html();
            $(this).html('<input type="text" value="'+ content+' ">');
        });
        $(trid +' .save').show();
        $(trid +' .cancel').show();
        $('.info').fadeIn('fast');
    });

    $('.save').click(function(){
        var id = $(this).data('id');
        var trid ='#id_'+ $(this).data('id');
        $(trid +' td .save').hide();
        $(trid +' td .cancel').hide();
        $(trid +' td input').each(function(){
            var content = $(this).val();//.replace(/\n/g,"<br>");
            $('#idspan_'+id).html(content);
            $(this).html(content);
            $(this).contents().unwrap();
            editDocument(id, content);
        }); 

        $('.edit_document').show(); 
    });
    $('.cancel').click(function(){
        var trid ='#id_'+$(this).data('id');
        var id = $(this).data('id');
        var originalValue = $('#idspan_'+id).html();
        console.log('#idspan_'+id +' = '+originalValue);
        $(trid +' td .save').hide();
        $(trid +' td .cancel').hide();
        $(trid +' td input').each(function(){
            var content = $(this).val();//.replace(/\n/g,"<br>");
            $(this).html(originalValue);
            $(this).contents().unwrap();
            console.log(content);
        }); 
        $('.edit_document').show(); 
    });
//fin prueba editar
    $('#btnMenuPatientDem').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuHistory').on('click', function() {
        var patientId = getParameterByName('patientId');
        var examId = getParameterByName('examId');
        location.href = ("history.php?patientId=" + patientId + "&examId=" + examId);
    });

    $('#btnMenuExam').on('click', function() {
        location.href = location.href;
    });

    $('#btnMenuClipboard').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        location.href = "https://doxy.me/sign-in";
    });

    $('#btnMenuMyPatients').on('click', function() {
        location.href = "physician_main.php";
    });

    $('#btnMenuSearch').on('click', function() {
        // TODO: Implement this
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });

    $('#btnRemoveExam').on('click', function() {
        $('#dialogConfirmDelete').dialog("open");
    });
});

function setPatientGender(gender) {
    patientGender = gender;
}

function setPhysicianId(id) {
    physicianId = id;
}

function trMouseOver(elem) {
    var src = "/images/exam/component/" + elem.dataset.time + "/" + elem.id + "/" +  patientGender;
    console.log(src);
    $modelImg.attr("src", src);
}

function trMouseOut(elem) {
    $modelImg.attr("src", (patientGender == 'male') ? "images/male.jpg" : "images/female.jpg");
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function removeExam() {
    $.ajax({
        async: true,
        error: function(jqxhr, textStatus, error) {
            swal.showInputError("There was an error while trying to delete the exam: " + textStatus + ": " + error);
        },
        method: 'GET',
        success: function(data, textStatus, jqxhr) {
            var rspObj = JSON.parse(data);
            if (rspObj.success) {
                var patientId = getParameterByName('patientId');
                $('#dialogConfirmDelete').dialog("close");
                swal({
                  title: "Exam deleted!",
                  text: "",
                  type: "success",
                  showCancelButton: false,
                  confirmButtonText: "Continue to patient overview",
                  closeOnConfirm: false
                },
                function(){
                  location.href = ("patient_view.php?patientId=" + patientId);
                });
            } else {
                swal.showInputError("Error while deleting the exam: " + rspObj.errorMsg);
            }
        },
        url: "api/removeExam.php?physId=" + physicianId + "&patientId=" + getParameterByName("patientId") + "&examId=" + getParameterByName("examId")
    });
}

function removeDocument(id) {
    swal({
      closeOnConfirm     : true,
      confirmButtonColor : '#2b8c36',
      confirmButtonText  : 'Ok',
      type               : 'warning',
      text               : 'You won\'t be able to revert this!',
      title              : 'Are you sure? ',
      showCancelButton   : true,
      cancelButtonText  : 'Cancel',
      showCloseButton: true
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                async: true,
                error: function(jqxhr, textStatus, error) {
                    alert("There was an error while trying to delete the document: " + textStatus + ": " + error);
                },
                method: 'GET',
                success: function(data, textStatus, jqxhr) {
                    var rspObj = JSON.parse(data);
                    if (rspObj.success) {
                        var examId = getParameterByName('examId');
                        var patientId = getParameterByName('patientId');
                        location.href = ("exam_main.php?patientId="+patientId+"&examId=" + examId);
                    } else {
                        alert("Error while deleting the document: " + rspObj.errorMsg);
                    }
                },
                url: "api/removeDocument.php?documentId=" + id
            });
        }
    });
}

function editDocument(id, name) {
    $.ajax({
        async: true,
        error: function(jqxhr, textStatus, error) {
            alert("There was an error while trying to save the document: " + textStatus + ": " + error);
        },
        method: 'GET',
        success: function(data, textStatus, jqxhr) {
            var rspObj = JSON.parse(data);
            if (rspObj.success) {
                swal(
                    'Success!',
                    'Your exam document has been edited.',
                    'success'
                );
            } else {
                alert("Error while deleting the document: " + rspObj.errorMsg);
            }
        },
        url: "api/updateDocument.php?documentId=" + id + "&nameDocument=" + name
    });
}
