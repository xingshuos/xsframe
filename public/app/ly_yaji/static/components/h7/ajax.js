define(['jquery', 'tip'], function ($) {
    let ajax = {};

    ajax.post = function (url, data, callback) {
        $.post(url, data, (json) => {
            callback && callback(json);
        }, 'json').error((err) => {
            // console.log(err);
            // console.log(typeof err.responseJSON);

            if (err.status === 401) {
                tip.msgbox.err(err.responseJSON);
                setTimeout(() => {
                    location.href = window.sysinfo.loginUrl;
                }, 1000);
            } else {
                if (typeof err.responseJSON === 'string') {
                    tip.msgbox.err(err.responseJSON);
                } else {
                    tip.msgbox.err(err.responseJSON.message);
                }
            }

        });
    };

    window.ajax = ajax;
});