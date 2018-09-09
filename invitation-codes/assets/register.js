'use strict';

blessing.event.on('mounted', ({ el }) => {
    $(el).find('.row').first().before(
        `<div class="form-group has-feedback">
            <input id="invitation-code" type="text" class="form-control" placeholder="邀请码">
            <span class="glyphicon glyphicon-inbox form-control-feedback"></span>
        </div>`
    );
});

// 插入邀请码的值
blessing.event.on('beforeFetch', request => {
    request.data.invitationCode = $('#invitation-code').val();
});
