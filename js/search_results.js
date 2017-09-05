$(document).on("ready", function() {
    var fname = getParameterByName("fname");
    var lname = getParameterByName("lname");
    var gender = getParameterByName("gender");
    if (fname !== null && lname !== null && gender !== null) {
        var firstName = document.getElementById("fname");
        var lastName = document.getElementById("lname");
        var cbs = document.getElementsByClassName("genderCheckbox");
        cbs[0].checked = (gender.indexOf("m") > -1)
        cbs[1].checked = (gender.indexOf("f") > -1)
        firstName.value = fname;
        lastName.value = lname
        var physId = getParameterByName("physId");
        search(physId);
    }
});

function search(physId) {
    var firstName = document.getElementById("fname").value;
    var lastName = document.getElementById("lname").value;
    if (firstName == "" && lastName == "") {
        window.alert("You must enter a first name or last name before searching.");
    } else {
        var cbs = document.getElementsByClassName("genderCheckbox");
        var gender = "&gender=";
        gender += ((cbs[0].checked) ? cbs[0].value : "");
        gender += ((cbs[1].checked) ? cbs[1].value : "");
        var query = "fname=" + encodeURIComponent(firstName) + "&lname=" + encodeURIComponent(lastName) + gender + "&physId=" + physId;
        ajaxSearch(query, physId);
    }
}

function ajaxSearch(query, physId) {
    // Do the ajax call here
    //TODO: Set some sort of progress indicator	// Show progress
    if (window.XMLHttpRequest) {
        // Code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {
        // Code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4) {
            //TODO: Disable (hide) progress indicator
            if (xmlhttp.status == 200) {
                // We received a response code of 200 indicating everything's just peachy
                // The response from this API call is JSON so we can easily convert it
                // to a JavaScript object.
                var result = JSON.parse(xmlhttp.responseText);

                if (result != null) {
                    var tab = document.getElementById("tblSearchResults");
                    var tableHtml = "";
                    var l = result.results.length;
                    if (l > 0) {
                        tableHtml = "\t<thead>\n\t\t<tr>\n\t\t\t<th class=\"headerRow\">Name</th>\n\t\t\t<th>MRN</th>\n\t\t\t<th>Gender</th>\n\t\t\t<th>DOB</th>\n\t\t\t<th>Phone</th>\n\t\t\t<th>Address</th>\n\t\t</tr>\n\t</thead>\n\t<tbody>";
                        for (var i = 0; i < l; i++) {
                            tableHtml += ("\n\t\t<tr class=\"listRow\" onclick=\"onTableRowClick(" + result.results[i].patientId + ")\">");
                            for (var j = 0; j < 6; j++) {
                                switch (j) {
                                    case 0:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += (result.results[i].lastName + ", " + result.results[i].firstName);
                                        tableHtml += "</td>";
                                        break;
                                    case 1:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += result.results[i].mrn;
                                        tableHtml += "</td>";
                                        break;
                                    case 2:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += (result.results[i].gender == "male" ? "M" : "F");
                                        tableHtml += "</td>";
                                        break;
                                    case 3:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += result.results[i].dob;
                                        tableHtml += "</td>";
                                        break;
                                    case 4:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += result.results[i].phone;
                                        tableHtml += "</td>";
                                        break;
                                    case 5:
                                        tableHtml += "\n\t\t\t<td>";
                                        tableHtml += (result.results[i].address + ", " + result.results[i].city + ", " + result.results[i].state + "  " + result.results[i].zip);
                                        tableHtml += "</td>";
                                        break;
                                }
                            }
                            tableHtml += "\n\t\t</tr>";
                        }
                        tableHtml += "\n\t</tbody>";
                    } else {
                        tableHtml = "\t<thead>\n\t\t<tr>\n\t\t\t<th><span style=\"font-weight: bold\">No results for the specified search query.</span></th>\n\t\t</tr>\n\t</thead>";
                    }
                    tab.innerHTML = tableHtml;
                }
            } else {
                alert("Error!");
            }
        }
    }
    var url = "api/search.php?" + query;
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}

function onTableRowClick(id) {
    window.location = "patient_view.php?patientId=" + id;
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}