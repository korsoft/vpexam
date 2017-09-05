var npiInfo = {
    npi: "",
    fname: "",
    lname: "",
    addr: "",
    city: "",
    state: "",
    zip: ""
};

$(document).on('ready', function() {
    var progressbar = $('#meter'),
        progressbarText = $('.progress-label');

    $('#btnRegister').on('click', function() {
        var validation = validate();
        if (!validation.validated && validation.errors.length > 0) {
            for (var i in validation.errors)
                $(validation.errors[i].elem).addClass('incomplete');
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

    $('#dobInput').datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        yearRange: "-120:+0"
    }).on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });

    $('#fnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $('#mnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $('#lnameInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $('#emailInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $("input[type='radio'][name='gender']").each(function(obj) {
        $(this).on('focus', function() {
            if ($(this).data('ui-tooltip'))
                $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
        });
    });
    $('#addrInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $('#cityInput').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });
    $('#selState').on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });

    $('#phoneInput').inputmask({
        autoUnmask: true,
        mask: "(999) 999-9999[\\ x99999]",
        greedy: false,
        removeMaskOnSubmit: true
    }).on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });

    $('#zipInput').numeric({
        decimal: false,
        negative: false
    }).on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });

    $('#selHospital').change(function() {
        if ($(this).val() == "1") {
            if (!$('#trHospOther').is(':visible')) {
                $('#trHospOther').fadeIn({
                    duration: 200
                });
            }
        } else {
            if ($('#trHospOther').is(':visible')) {
                $('#trHospOther').fadeOut({
                    duration: 200
                });
            }
        }
    }).on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    });

    $('#npiInput').on('focus', function() {
        if ($(this).data('ui-tooltip')) {
            $(this).css({
                "border": ""
            }).tooltip('destroy').attr("title", "");
        }
    }).numeric();

    $('#pwdInput').on('focus', function() {
        $('#pwdInfo').fadeIn({
            duration: 200
        });
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
    }).on('keyup', function() {
        testPassword($(this).val());
    }).on('blur', function() {
        $('#pwdInfo').fadeOut({
            duration: 200
        });
    });

    $('#pwdConfirmInput').on('keyup', function() {
        var pwdInput = $('#pwdInput').val();
        if (pwdInput !== "" && ($(this).val() === pwdInput)) {
            $('#pwdConfirmMatch').attr('src', 'img/green_check.png');
        } else {
            $('#pwdConfirmMatch').attr('src', 'img/red_x.png');
        }
    }).on('focus', function() {
        if ($(this).data('ui-tooltip'))
            $(this).removeClass('incomplete').tooltip('destroy').attr("title", "");
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
    var hosp = $('#selHospital').val();
    var hospOther = $('#hospitalInput').val();
    var npi = $('#npiInput').val();
    var pwd = $('#pwdInput').val();
    var pwdConfirm = $('#pwdConfirmInput').val();

    if (fname === "") {
        returnObj.errors.push({
            msg: "You must enter your first name.",
            elem: "#fnameInput"
        });
    }

    if (lname === "") {
        returnObj.errors.push({
            msg: "You must enter your last name.",
            elem: "#lnameInput"
        });
    }

    if (email === "") {
        returnObj.errors.push({
            msg: "You must enter an email address.",
            elem: "#emailInput"
        });
    } else if (!new RegExp(regexes.email).test(email)) {
        returnObj.errors.push({
            msg: "You must enter a valid email address.",
            elem: "#emailInput"
        });
    }

    if (gender === undefined) {
        returnObj.errors.push({
            msg: "You must select a gender.",
            elem: "#genderInput"
        });
    }

    if (hosp === "-1") {
        returnObj.errors.push({
            msg: "You must select a hospital from the list, or enter one.",
            elem: '#selHospital'
        });
    } else if (hosp === "1") {
        if (hospOther === "") {
            returnObj.errors.push({
                msg: "You must enter a hospital name.",
                elem: '#hospitalInput'
            });
        }
    }

    if (npi === "") {
        returnObj.errors.push({
            msg: "You must enter your NPI number.",
            elem: '#npiInput'
        });
    } else if (npi.length < 10) {
        returnObj.errors.push({
            msg: "Your NPI number must be 10 digits long.",
            elem: '#npiInput'
        });
    }

    if (pwd === "") {
        returnObj.errors.push({
            msg: "You must enter a password.",
            elem: '#pwdInput'
        });
    } else if (!testPassword(pwd)) {
        returnObj.errors.push({
            msg: "Password does not meet minimum strength requirements.",
            elem: '#pwdInput'
        });
    }

    if (pwdConfirm === "") {
        returnObj.errors.push({
            msg: "You must confirm your password.",
            elem: '#pwdConfirmInput'
        });
    } else if (pwd !== pwdConfirm) {
        returnObj.errors.push({
            msg: "Password confirmation must match password.",
            elem: '#pwdConfirmInput'
        });
    }

    returnObj.validated = (returnObj.length === 0);

    return returnObj;
}

function hashForm() {
    var $pwd = $('#pwdInput');
    var $hashedPwdElem = $('<input id="pwdHashed" name="pwdHashed" type="hidden" />');
    $hashedPwdElem.val(hex_sha512($pwd.val()));
    $pwd.val("");
    $('#pwdConfirmInput').val("");
    $('form').append($hashedPwdElem);
}

function testPassword(password) {
    var upperRegex = /[A-Z]/,
        lowerRegex = /[a-z]/,
        numberRegex = /[0-9]/,
        specialRegex = /[^A-Za-z0-9]/,
        minLength = 8;

    var lengthGood = (password.length >= minLength),
        haveUpper = upperRegex.test(password),
        haveLower = lowerRegex.test(password),
        haveNum = numberRegex.test(password),
        haveSpecial = specialRegex.test(password);

    if (lengthGood)
        $('#length').removeClass('invalid').addClass('valid');
    else
        $('#length').removeClass('valid').addClass('invalid');

    if (haveUpper)
        $('#capital').removeClass('invalid').addClass('valid');
    else
        $('#capital').removeClass('valid').addClass('invalid');

    if (haveLower)
        $('#letter').removeClass('invalid').addClass('valid');
    else
        $('#letter').removeClass('valid').addClass('invalid');

    if (haveNum)
        $('#number').removeClass('invalid').addClass('valid');
    else
        $('#number').removeClass('valid').addClass('invalid');

    if (haveSpecial)
        $('#special').removeClass('invalid').addClass('valid');
    else
        $('#special').removeClass('valid').addClass('invalid');

    return (lengthGood && haveUpper && haveLower && haveNum && haveSpecial);
}