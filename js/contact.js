$(document).on("ready", function() {
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
});