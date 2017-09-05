$(document).on('ready', function() {
    var progressbar = $('#meter'),
        progressbarText = $('.progress-label');

    $('#btnRegister').on('click', function() {
        var validation = validate();
        if (!validation.validated && validation.errors.length > 0) {
            for (var i in validation.errors) {
                $(validation.errors[i].elem).addClass('incomplete');
            }
            $("[title]").tooltip({
                position: {
                    my: "left top",
                    at: "right+5 top-5"
                }
            });
        } else {
            // Form validation was successful, proceed to submit the form
            hashForm();
            $('form').submit();
        }
    });

    progressbar.progressbar({
        max: 100,
        value: 0
    });

    var progressbarContainer = $('.ui-progressbar-value');

    $('.question').tooltip({
        position: {
            my: "bottom-30"
        }
    });

    $('#physicianInput').keyup(function() {
        var keywords = JSON.stringify($(this).val().split(' '));
        if ($(this).val().length > 0) {
            $.get("api/searchPhysAutocomplete.php", { keywords: keywords })
            .done(function(data) {
                $('#results').html('');
                $('#physicianIdInput').val("");
                var results = $.parseJSON(data);
                if (results == null) {
                    alert("Error");
                } else if (!results.success) {
                    alert("Error: " + results.error);
                } else {
                    for (var val in results.results) {
                        var $elem = $('<div class="item">' + results.results[val].fname + ' ' + results.results[val].lname + '</div>');
                        $elem.data('phys-id', results.results[val].id);
                        $('#results').append($elem);
                    }

                    $('.item').click(function() {
                        var text = $(this).html();
                        $('#physicianInput').val(text);
                        $('#physicianIdInput').val($(this).data('phys-id'));
                    });
                }
            });
        } else {
            $('#results').html('');
        }
    }).blur(function() {
        $('#results').fadeOut(500);
    }).focus(function() {
        $('#results').show();
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });

    $('#dobInput').datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        yearRange: "-120:+0"
    }).on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });

    $('#fnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $('#mnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $('#lnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $('#emailInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $("input[type='radio'][name='gender']").each(function(obj) {
        $(this).on('focus', function() {
            if ($('#genderInput').data('ui-tooltip')) {
                $('#genderInput').css({
                    "border": ""
                }).tooltip('destroy').attr("title", "");
            }
        });
    });
    $('#addrInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $('#cityInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
    $('#selState').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });

    $('#phoneInput').inputmask({
        autoUnmask: true,
        mask: "(999) 999-9999[\\ x99999]",
        greedy: false,
        removeMaskOnSubmit: true
    }).on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });

    $('#zipInput').numeric({
        decimal: false,
        negative: false
    }).on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });

    $('#pwdInput').on('focus', function() {
        $('#pwdConfirm').fadeIn(200);
    }).on('blur', function() {
        if ($(this).val() === "") {
            $('#pwdConfirm').fadeOut(200);
            $('#pwdConfirmInput').val("");
            $('#pwdConfirmMatch').attr('src', 'img/red_x.png');
        }
    });

    $('#pwdConfirmInput').on('keyup', function() {
        if ($(this).val() === $('#pwdInput').val()) {
            $('#pwdConfirmMatch').attr('src', 'img/green_check.png');
        } else {
            $('#pwdConfirmMatch').attr('src', 'img/red_x.png');
        }
    });

    $('#btnTopHome').on('mouseenter', function() {
        $('#btnTopHome').css({
            'background-color': '#67c9e0',
            'cursor': 'pointer'
        });
    }).on('mouseleave', function() {
        $('#btnTopHome').css({
            'background-color': '',
            'cursor': ''
        });
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

    $('#selInsPatientRelationship').change(function() {
        if ($(this).val() === "other") {
            $('#insPatientRelationshipOtherInput').val("");
            $('#insurancePatientRelationshipOtherRow').fadeIn({
                duration: 250
            });
        } else {
            $('#insurancePatientRelationshipOtherRow').fadeOut({
                duration: 250
            });
        }
    });

    $('#insIssueDateInput').datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        yearRange: "-20:+0"
    })
});

/*function gCaptchaCallback(response) {
    $('iframe').each(function(obj) {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    });
}*/

function isValidDate(dt) {
    var objDate,
        seconds,
        day,
        month,
        year,
        returnObj = {
            valid: false,
            errors: []

        };
    // Date length should be 10 characters (no more no less)
    if (dt.length !== 10) {
        returnObj.valid = false;
        returnObj.errors.push("Date should be of format 'mm/dd/yyyy'.");
        return returnObj;
    }
    // third and sixth character should be '/'
    if (dt.substring(2, 3) !== '/' || dt.substring(5, 6) !== '/') {
        returnObj.valid = false;
        returnObj.errors.push("Date should be of format 'mm/dd/yyyy'.");
        return returnObj;
    }
    // extract month, day and year from the ExpiryDate (expected format is mm/dd/yyyy)
    // subtraction will cast variables to integer implicitly (needed
    // for !== comparing)
    month = dt.substring(0, 2) - 1; // because months in JS start from 0
    day = dt.substring(3, 5) - 0;
    year = dt.substring(6, 10) - 0;
    // Test year range
    if (year < 1000 || year > 3000) {
        returnObj.valid = false;
        returnObj.errors.push("Year must be between 1000 and 3000.");
        return returnObj;
    }
    // Convert date to milliseconds
    mSeconds = (new Date(year, month, day)).getTime();
    // Initialize Date() object from calculated milliseconds
    objDate = new Date();
    objDate.setTime(mSeconds);
    // Compare input date and parts from Date() object
    // if difference exists then date isn't valid
    if (objDate.getFullYear() !== year ||
        objDate.getMonth() !== month ||
        objDate.getDate() !== day) {
        returnObj.valid = false;
        returnObj.errors.push("Date is invalid.");
        return returnObj;
    }

    returnObj.valid = true;
    returnObj.errors.length = 0;
    return returnObj;
}

function validate() {
    var regexes = {
        email: /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i,
        address: "",
        zip: ""
    };

    var returnObj = {
        validated: false,
        errors: []
    };

    var fname = $('#fnameInput').val();
    var mname = $('#mnameInput').val();
    var lname = $('#lnameInput').val();
    var email = $('#emailInput').val();
    var dob = $('#dobInput').val();
    var gender = $("input[type='radio'][name='gender']:checked").val();
    var phone = $('#phoneInput').val();
    var address = $('#addrInput').val();
    var city = $('#cityInput').val();
    var state = $('#selState').val();
    var zip = $('#zipInput').val();
    var physician = $('#physicianInput').val();
    var physId = $('#physicianIdInput').val();
    var pwd = $('#pwdInput').val();
    var pwdConfirm = $('#pwdConfirmInput').val();

    if (fname === "") {
        returnObj.errors.push({
            msg: "You must enter your first name.",
            elem: "#fnameInput"
        });
        returnObj.validated = false;
    }

    /*if (mname === "") {
        returnObj.errors.push({
            msg: "You must enter your middle name.",
            elem: "#mnameInput"
        });
        returnObj.validated = false;
    }*/

    if (lname === "") {
        returnObj.errors.push({
            msg: "You must enter your last name.",
            elem: "#lnameInput"
        });
        returnObj.validated = false;
    }

    /*if (email === "") {
        returnObj.errors.push({
            msg: "You must enter an email address.",
            elem: "#emailInput"
        });
        returnObj.validated = false;
    } else if (!new RegExp(regexes.email).test(email)) {
        returnObj.errors.push({
            msg: "You must enter a valid email address.",
            elem: "#emailInput"
        });
        returnObj.validated = false;
    }*/

    if (dob === "") {
        returnObj.errors.push({
            msg: "You must enter a birthdate.",
            elem: "#dobInput"
        });
        returnObj.validated = false;
    } else {
        var dtValidation = isValidDate(dob);
        if (!dtValidation.valid) {
            returnObj.errors.push({
                msg: "Invalid date.",
                elem: "#dobInput"
            });
            returnObj.validated = false;
        }
    }

    if (gender === undefined) {
        returnObj.errors.push({
            msg: "You must select a gender.",
            elem: "#genderInput"
        });
        returnObj.validated = false;
    }

    /*if (phone === "") {
        returnObj.errors.push({
            msg: "You must enter a phone number.",
            elem: "#phoneInput"
        });
        returnObj.validated = false;
    } else if (!(phone.length >= 10)) {
        returnObj.errors.push({
            msg: "Phone number is too short.",
            elem: "#phoneInput"
        });
        returnObj.validated = false;
    }

    if (address === "") {
        returnObj.errors.push({
            msg: "You must enter an address.",
            elem: "#addrInput"
        });
        returnObj.validated = false;
    }

    if (city === "") {
        returnObj.errors.push({
            msg: "You must enter a city.",
            elem: "#cityInput"
        });
        returnObj.validated = false;
    }

    if (state === "SEL") {
        returnObj.errors.push({
            msg: "You must select a state.",
            elem: "#selState"
        });
        returnObj.validated = false;
    }

    if (!(zip.length === 5)) {
        returnObj.errors.push({
            msg: "Invalid zip code.",
            elem: "#zipInput"
        });
        returnObj.validated = false;
    }

    if (physician === "" || physId === "") {
        returnObj.errors.push({
            msg: "You must select a valid physician.",
            elem: "#physicianInput"
        });
    }

    if (pwd !== "") {
        if (pwdConfirm === "") {
            returnObj.errors.push({
                msg: "Password confirmation must match password",
                elem: '#pwdConfirmInput'
            });
        }
    }*/

    /*if (grecaptcha.getResponse() === "") {
        returnObj.errors.push({
            msg: "Please verify that you are human.",
            elem: "iframe"
        });
    }*/

    if (returnObj.errors.length === 0)
        returnObj.validated = true;

    return returnObj;
}

function hashForm() {
    var $pwd = $('#pwdInput');
    if ($($pwd).val() !== "") {
        var $hashedPwdElem = $('<input id="pwdHashed" name="pwdHashed" type="hidden" />');
        $hashedPwdElem.val(hex_sha512($pwd.val()));
        $pwd.val("");
        $('#pwdConfirmInput').val("");
        $('form').append($hashedPwdElem);
    }
}

var RANK = {
    TOO_SHORT: 0,
    WEAK: 1,
    MEDIUM: 2,
    STRONG: 3,
    VERY_STRONG: 4
};

function rankPassword(password) {
    var upper = /[A-Z]/,
        lower = /[a-z]/,
        number = /[0-9]/,
        special = /[^A-Za-z0-9]/,
        minLength = 8,
        score = 0;

    if (password.length < minLength) {
        return RANK.TOO_SHORT;
    }

    // Increment the score for each of these conditions
    if (upper.test(password))
        score++;
    if (lower.test(password))
        score++;
    if (number.test(password))
        score++;
    if (special.test(password))
        score++;

    // Penalize if there aren't at least three char types
    if (score < 3)
        score--;

    if (password.length > minLength) {
        // Increment the score for every 2 chars longer than the minimum
        score += Math.floor((password.length - minLength) / 2);
    }

    // Return a ranking based on the calculated score
    if (score < 3)
        return RANK.WEAK;
    if (score < 4)
        return RANK.MEDIUM;
    if (score < 5)
        return RANK.STRONG
    return RANK.VERY_STRONG;
}