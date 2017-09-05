var physicianId = -1;
var $searchResultsDiv;

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

    $('#searchInput').on('keyup', function(event) {
        if ($(this).val() === "") {
            $searchResultsDiv.empty();
        } else {
            var arr = $(this).val().split(' ');
            arr = $.grep(arr, function(n) {
                return n;
            });
            ajaxSearch(JSON.stringify(arr));
        }
    });

    $('#btnMenuConnectDoxy').on('click', function() {
        // TODO: Implement
    });

    $('#btnMenuMyPatients').on('click', function() {
        location.href = "physician_main.php";
    });

    $('#btnMenuSearch').on('click', function() {
        location.href = location.href;
    });

    $('#btnMenuSettings').on('click', function() {
        location.href = "physician_settings.php";
    });

    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });
    $('.waitingRoom ul li label').on('click', function() {
        var calling = {
            id   : $(this).parent().data('id'),
            name : $(this).text()
        };
        $('#calling').val(JSON.stringify(calling));
        $('#chat').click();
    });

    $searchResultsDiv = $('.searchResultsDiv');
});

function ajaxSearch(tokens) {
    $.ajax({
        async: true,
        error: function(jqxhr, textStatus, error) {
            alert("There was an error while performing the search query: " + textStatus + ": " + error);
        },
        method: 'GET',
        success: function(data, textStatus, jqxhr) {
            var resultObj = JSON.parse(data);
            if (resultObj.success) {
                var numResults = resultObj.results.length;
                if (numResults === 0) {
                    $searchResultsDiv.empty();
                    $searchResultsDiv.append($('<span class="noPatientsFound">No patients found</span>'));
                } else {
                    $searchResultsDiv.empty();

                    var $table = $(
                        '<table class="resultsTable" data-sortable>' +
                        '   <thead>' +
                        '       <tr>' +
                        '           <th class="shortColumn" style="padding: 0 0 0 10px;">LAST NAME</th>' +
                        '               <th class="shortColumn">FIRST NAME</th>' +
                        '               <th class="shortColumn">MIDDLE NAME</th>' +
                        '               <th class="shortColumn" data-sortable="false">DOB</th>' +
                        '               <th class="longColumn" data-sortable="false">CONTACT INFO</th>' +
                        '       </tr>' +
                        '   </thead>' +
                        '   <tbody class="resultsTableBody">' +
                        '   </tbody>' +
                        '</table>'
                    );
                    $searchResultsDiv.append($table);
                    var $tableBody = $('.resultsTableBody');

                    for (var i = 0; i < numResults; i++) {
                        var dt = resultObj.results[i].patientInfo.dob;
                        var month, day, year;
                        month = dt.substr(5, 2);
                        day = dt.substr(8, 2);
                        year = dt.substr(0, 4);
                        var addr = resultObj.results[i].patientInfo.address + ', ' + resultObj.results[i].patientInfo.city + ', ' + resultObj.results[i].patientInfo.state + ' ' + resultObj.results[i].patientInfo.zip;

                        var $tr = $(
                            '   <tr class="hoverableRow patientsTable" id="' + resultObj.results[i].patientId + '">' +
                            '       <td class="shortColumn" style="width: 20%;">' +
                            '           <img class="patientProfilePic" src="includes/getProfileImage.php?id=' + resultObj.results[i].patientId + '&type=1">' +
                            '           <div class="nameMRNDiv">' +
                            '               <div style="margin: 20px 0 0 0;">' +
                            '                   <div>' + resultObj.results[i].patientInfo.lastName + '</div>' +
                            '               </div>' +
                            '           </div>' +
                            '       </td>' +
                            '       <td class="shortColumn">' + resultObj.results[i].patientInfo.firstName + '</td>' +
                            '       <td class="shortColumn">' + (resultObj.results[i].patientInfo.middleName === "" ? "-" : resultObj.results[i].patientInfo.middleName) + '</td>' +
                            '       <td class="shortColumn">' +
                            '           <div class="nameMRNDiv">' +
                            '               <div style="margin: 20px 0 0 0;">' +
                            '                   <div>' + month + '/' + day + '/' + year + '</div>' +
                            '                   <div>' + (resultObj.results[i].patientInfo.gender === "male" ? "Male" : "Female") + '</div>' +
                            '               </div>' +
                            '           </div>' +
                            '       </td>' +
                            '       <td class="longColumn">' +
                            '           <div class="nameMRNDiv">' +
                            '               <div style="margin: 20px 0 0 0;">' +
                            '                   <div>' + addr + '</div>' +
                            '                   <div>' + getFormattedPhone(resultObj.results[i].patientInfo.phone) + '</div>' +
                            '               </div>' +
                            '           </div>' +
                            '       </td>' +
                            '   </tr>'
                        );
                        $tableBody.append($tr);

                        $($tr).on('click', function() {
                            location.href = ("patient_view.php?patientId=" + $(this).attr('id'));
                        });
                        /*$('.resultsTableBody tr').each(function(idx, val) {
                            $(val).on('click', function() {
                                location.href = ("patient_view.php?patientId=" + $(this).attr('id'));
                            });
                        });*/
                    }
                    Sortable.init();
                    //$('.resultsTable').paging({limit: 20});
                }
            } else {
                alert("There was an error while performing the search query: " + resultObj.errorMsg);
            }
        },
        url: "api/searchPatients.php?keywords=" + encodeURIComponent(tokens) + "&physId=" + physicianId
    });
}

/**
 * This is a copy of the PHP function with the same name
 * @param phone string
 * @returns string formatted phone number
 */
function getFormattedPhone(phone) {
    if (typeof(phone) !== "string")
        return null;

    var phoneLen = phone.length;

    // This case should really never happen, but just in case
    // 7 digits are treated as a phone number w/o an area code
    // Ex. 000-0000
    if (phoneLen === 7) {
        var prefix = phone.substr(0, 3);
        var lineNum = phone.substr(3);
        return (prefix + '-' + lineNum);
    } else if (phoneLen === 10) {
        // Standard 10-digit phone number, with area code
        // Ex. (000) 000-0000
        var areaCode = phone.substr(0, 3);
        var prefix = phone.substr(3, 3);
        var lineNum = phone.substr(6);
        return ('(' + areaCode + ') ' + prefix + '-' + lineNum);
    } else if (length > 10) {
        // The first 10 digits are treated as the standard
        // 10-digit phone number while the remaining digits
        // are treated as the extension (up to 5 digits)
        var areaCode = phone.substr(0, 3);
        var prefix = phone.substr(3, 3);
        var lineNum = phone.substr(6, 4);
        var ext = phone.substr(10);
        return ('(' + areaCode + ') ' + prefix + '-' + lineNum + ' x' + ext);
    }
    return null;
}

function setPhysicianId(id) {
    physicianId = id;
}
