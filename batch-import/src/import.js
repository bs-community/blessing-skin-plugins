/*
* @Author: printempw
* @Date:   2017-01-01 16:19:00
* @Last Modified by:   printempw
* @Last Modified time: 2017-01-13 21:46:40
*/

'use strict';

var progress = 0;

function check_dir() {
    $.ajax({
        url: '?action=check-dir',
        type: 'POST',
        data: {
            dir: $('#dir').val(),
            gbk: $('#gbk').prop('checked')
        },
        beforeSend: function() {
            $('#next').html('<i class="fa fa-spinner fa-spin"></i> 检查目录权限中').prop('disabled', 'disabled');
        },
        success: function(json) {
            if (json.errno == 0) {
                window.location = "?step=2";
            } else {
                $('#next').html('下一步').prop('disabled', '');
                toastr.warning(json.msg);
            }
        },
        error: function(json) {
            $('#next').html('下一步').prop('disabled', '');
            showAjaxError(json);
        }
    });
}

$('#start-import').click(function(event) {
    $.ajax({
        url: '?action=prepare-import',
        type: 'POST',
        dataType: 'json',
    })
    .done(function(json) {
        console.log("prepare importing, temporary directory: "+json.tmp_dir);
        console.log("start importing");

        $.ajax({
            url: '?action=start-import',
            type: 'POST',
            dataType: 'json',
            data: {
                'type': $('#type').val(),
                'uploader': $('#uploader').val()
            }
        })
        .done(function(json) {
            progress = 100;
            console.log("importe completed");
        })
        .fail(showAjaxError);

        var intervalID = window.setInterval(function() {
            if (progress == 100) {
                alert('导入完成！');
                window.location = "../skinlib";
            } else {
                $.ajax({
                    url: '?action=get-progress',
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(json) {
                    progress = json.progress;

                    $('#imported-progress').html(json.progress);
                    $('.progress-bar').css('width', json.progress+'%').attr('aria-valuenow', json.progress);

                    console.log("Progress: "+json.progress);
                })
                .fail(function(json) {
                    showAjaxError(json);
                    clearInterval(intervalID);
                });
            }

        }, 300);
    })
    .fail(showAjaxError);

});
