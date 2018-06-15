'use strict';

$('.col-md-4 .box-primary .box-header').append(
    '<div class="box-tools pull-right" style="position: initial; cursor: pointer;">'+
        '<span id="report-texture" class="label label-warning">'+
            '<i class="fa fa-flag" aria-hidden="true"></i> ' + trans('reportTexture.reportThis')+
        '</span>'+
    '</div>'
);

function reportTexture(tid) {
    var reason = $('#report-form input').val();

    $.ajax({
        type: 'POST',
        url: url('skinlib/report'),
        dataType: 'json',
        data: { tid: tid, reason: reason },
        beforeSend: function() {
            $('.modal-footer button').html('<i class="fa fa-spinner fa-spin"></i>' + trans('reportTexture.submitting')).prop('disabled', 'disabled');
        },
        success: function (result) {
            $('.modal').modal('hide')

            if (result.errno == 0) {
                swal({ type: 'success', html: result.msg });
            } else {
                swal({ type: 'warning', html: result.msg });
                $('.modal-footer button').html('OK').prop('disabled', '');
            }
        },
        error: showAjaxError
    });
}

$('body').on('click', '#report-texture', function () {
    var tid = location.pathname.match(/skinlib\/show\/(\d*)/)[1];

    if (! tid) {
        return alert(trans('reportTexture.invalidTID'));
    }

    $('.modal').each(function () {
        if ($(this).css('display') == 'none') $(this).remove();
    });

    var dom =
    '<div class="form-group" id="report-form">'+
        '<label for="tid">' + trans('reportTexture.reportReason') +'</label>'+
        '<input id="tid" class="form-control" type="text" placeholder="' + trans('reportTexture.reportReasonExample') + '">'+
    '</div>';

    showModal(dom,  trans('reportTexture.TID') +':'+ tid, 'default', {
        callback: 'reportTexture(' + tid + ')'
    });
});

var report = {
    ban: function (id) {
        $.ajax({
            type: 'POST',
            url: url('admin/reports'),
            dataType: 'json',
            data: { id: id, operation: 'ban' },
            success: function (result) {
                if (result.errno == 0) {
                    $('#report-'+id+' #status').text(trans('reportTexture.status.resolved'));
                    toastr.success(result.msg);
                } else {
                    toastr.warning(result.msg);
                }
            },
            error: showAjaxError
        });
    },
    delete: function (id) {
        $.ajax({
            type: 'POST',
            url: url('admin/reports'),
            dataType: 'json',
            data: { id: id, operation: 'delete' },
            success: function (result) {
                if (result.errno == 0) {
                    $('#report-'+id+' #status').text(trans('reportTexture.status.resolved'));
                    toastr.success(result.msg);
                } else {
                    toastr.warning(result.msg);
                }
            },
            error: showAjaxError
        });
    },
    reject: function (id) {
        $.ajax({
            type: 'POST',
            url: url('admin/reports'),
            dataType: 'json',
            data: { id: id, operation: 'reject' },
            success: function (result) {
                if (result.errno == 0) {
                    $('#report-'+id+' #status').text(trans('reportTexture.status.rejected'));
                    toastr.success(result.msg);
                } else {
                    toastr.warning(result.msg);
                }
            },
            error: showAjaxError
        });
    }
}
