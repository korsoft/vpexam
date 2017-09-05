$(document).on("ready", function() {
    $('#btnLogin').click(function() {
        if (validateForm())
            attemptLogin();
        else
            showError();
    });
});

function showError() {
    var $loginBox = $('.loginBox');
    $('.errorBox').show();
    $($loginBox).css({
        top: 'calc(50% - 115.5px)'
    });
    $($loginBox).effect('shake');
}

function attemptLogin() {
    var username = $('#inputUsername').val();
    var hashedPwd = hex_sha512($('#inputPwd').val());

    $.ajax({
        async: true,
        data: 'username=' + encodeURIComponent(username) + '&pwd=' + hashedPwd,
        dataType: 'json',
        error: function(jqxhr, textStatus, error) {
            alert("There was an error processing the login request: " + error + ".\nIf this error persists, please contact the system administrator.");
        },
        method: 'POST',
        success: function(data, textStaus, jqxhr) {
            if (!data.authed)
                showError();
        },
        url: 'includes/adminAuth.php'
    });
}

function validateForm() {
    return !($('#inputUsername').val() == "" || $('#inputPwd').val() == "");
}