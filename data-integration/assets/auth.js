'use strict';

$('#register-button-2').click(function() {

    var email    = $('#email').val();
    var password = $('#password').val();
    var player_name = $('#player_name').val();
    var captcha  = $('#captcha').val();

    // check valid email address
    if (email == "") {
        showMsg(trans('auth.emptyEmail'));
        $('#email').focus();
    } else if (!/\S+@\S+\.\S+/.test(email)) {
        showMsg(trans('auth.invalidEmail'), 'warning');
    } else if (password == "") {
        showMsg(trans('auth.emptyPassword'));
        $('#password').focus();
    } else if (password.length < 8 || password.length > 32) {
        showMsg(trans('auth.invalidPassword'), 'warning');
        $('#password').focus();
    } else if ($('#confirm-pwd').val() == "") {
        showMsg(trans('auth.emptyConfirmPwd'));
        $('#confirm-pwd').focus();
    } else if (password != $('#confirm-pwd').val()) {
        showMsg(trans('auth.invalidConfirmPwd'), 'warning');
        $('#confirm-pwd').focus();
    } else if (player_name == "") {
        showMsg('用户名不能为空');
        $('#player_name').focus();
    } else if (captcha == "") {
        showMsg(trans('auth.emptyCaptcha'));
        $('#captcha').focus();
    } else {

        $.ajax({
            type: "POST",
            url: "./register",
            dataType: "json",
            data: { 'email': email, 'password': password, 'player_name': player_name, 'captcha': captcha },
            beforeSend: function() {
                $('#register-button').html('<i class="fa fa-spinner fa-spin"></i> '+trans('auth.registering')).prop('disabled', 'disabled');
            },
            success: function(json) {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });
                    window.setTimeout('window.location = "../user"', 1000);
                } else {
                    showMsg(json.msg, 'warning');
                    freshCaptcha();
                    $('#register-button').html(trans('auth.register')).prop('disabled', '');
                }
            },
            error: function(json) {
                showAjaxError(json);
                $('#register-button').html(trans('auth.register')).prop('disabled', '');
            }
        });
    }
    return false;

});
