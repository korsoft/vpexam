var physicianId = -1,
    isIE        = /(MSIE|Edge)/.test(window.navigator.userAgent);

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

    $('.patientsTable').each(function(idx, val) {
        $(val).on('click', function() {
            location.href = ("patient_view.php?patientId=" + $(this).attr('id'));
        });
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        location.href = "https://doxy.me/sign-in";
    });

    $('#btnMenuMyPatients').on('click', function() {
        // Already here
        location.href = location.href;
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
    $.ajax({
        success: function(data, status, jqxhr) {
            if (data) {
                if (data.success)
                    $('#' + patientId)[0].remove();
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

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

