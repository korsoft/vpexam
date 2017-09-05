$(document).on("ready", function() {
    var selectedMenuButton = 'home';

    var mSwiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        direction: 'horizontal',
        nextButton: '.swiper-button-next.swiper-button-white',
        prevButton: '.swiper-button-prev.swiper-button-white',
        paginationClickable: true,
        spaceBetween: 30,
        centeredSlides: true,
        autoplay: 2500,
        autoplayDisableOnInteraction: false
    });

    $('#googlePlayLink').on('click', function() {
        alert("The app is coming soon!");
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

    $('#btnRegister').on('click', function() {
        window.location = "register_patient.php";
    });

    $('#btnRequestTrial').on('click', function() {
        window.location = "register_physician.php";
    });
});

function hashForm() {
    var $pwd = $('#password');
    var $hashedPwdElem = $('<input id="pwdHashed" name="p" type="hidden" />');
    $hashedPwdElem.val(hex_sha512($pwd.val()));
    $pwd.val("");
    $('#loginForm').append($hashedPwdElem);
}
