'use strict';

window.bsEmitter.on('mounted', ({ el }) => setTimeout(() => {
    $(el).find('.row').first().before(
        `<div class="form-group has-feedback">
            <input id="invitation-code" type="text" class="form-control" placeholder="邀请码">
            <span class="glyphicon glyphicon-inbox form-control-feedback"></span>
        </div>`
    );
}, 100));

// 插入邀请码的值
window.bsEmitter.on('beforeFetch', request => {
    request.data.invitationCode = $('#invitation-code').val();
});
