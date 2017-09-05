$(document).on('ready', function() {
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

    $('#btnLogin').on('click', function() {
        hashForm();
        $('#loginForm').submit();
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

function hashForm() {
    var $pwd = $('#password');
    var $hashedPwdElem = $('<input id="pwdHashed" name="p" type="hidden" />');
    $hashedPwdElem.val(hex_sha512($pwd.val()));
    $pwd.val("");
    $('#loginForm').append($hashedPwdElem);
}