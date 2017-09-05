$(document).on("ready", function() {
    $('#btnChangeImg').on('click', function() {
    	var bar = $('#progress .progress-bar');
    	if(bar.hasClass('progress-bar-danger')) {
    		bar.removeClass('progress-bar-danger');
    	}
    	if(0 < bar.width()) {
    		bar.css('width', 0);
    	}
        $('#fileToUpload').trigger('click');
    });
    $('#btnMenuLogout').on('click', function() {
        location.href = "logout.php";
    });
});

var mesages = {
	1 : 'Need to be logged to change the image',
	2 : 'Please add an image.',
	3 : 'Please add a png or jpeg image.',
	4 : 'Please add an images less than 500 KB.'
};

/*jslint unparam: true */
/*global window, $ */
$(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = '/includes/upload_image.php';
    $('#fileToUpload').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
        	if(!data.result.success) {
        		$('#progress .progress-bar').removeClass('progress-bar-succcess').addClass('progress-bar-danger');
        		swal({
                    title: data.result.error.msg,
                    text: mesages[data.result.error.id],
                    type: 'error'
                });
        	}
        	else {
    			$('#profileImg').attr('src', '/profile/1/' + $('#id').val()+'?' + (new Date()).getTime());
    		}
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css('width', progress + '%');
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});

var validate = function() {

}
