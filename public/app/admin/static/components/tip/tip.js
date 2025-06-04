const tipFun = () => {
    let tip = {};

    tip.lang = {
        "success": "操作成功",
        "error": "操作失败",
        "exception": "网络异常",
        "processing": "处理中..."
    };
    $('#tip-msgbox').remove();

    $("body", top.window.document).append('<div id="tip-msgbox" class="msgbox"></div>');
    window.msgbox = $("#tip-msgbox", top.window.document);

    tip.loading = function (flag = true){
        if( flag ){
            tip.hideLoading();
            $("body").append('<div id="tip-loading">\n' +
                '        <div class="page-loading-inner" style="z-index:9999999;">\n' +
                '            <div class="sk-three-bounce">\n' +
                '                <div class="sk-child sk-bounce1"></div>\n' +
                '                <div class="sk-child sk-bounce2"></div>\n' +
                '                <div class="sk-child sk-bounce3"></div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>');
        }else{
            tip.hideLoading();
        }
    }

    tip.hideLoading = function (){
        $("#tip-loading").remove();
    }

    tip.confirm = function (msg, callback, cancel_callback) {
        msg = msg.replace(/&lt;/g, "<");
        msg = msg.replace(/&gt;/g, ">");

        require(['jquery.confirm'], function () {
            $.confirm({
                title: '提示',
                content: msg,
                confirmButtonClass: 'btn-tip-confirm',
                cancelButtonClass: 'btn-tip-cancel',
                confirmButton: '确 定',
                cancelButton: '取 消',
                animation: 'top',
                confirm: function () {
                    if (callback && typeof (callback) == 'function') {
                        callback()
                    }
                },
                cancel: function () {
                    if (cancel_callback && typeof (cancel_callback) == 'function') {
                        cancel_callback()
                    }
                }
            })
        })

    };

    tip.prompt = function (msg, options, password) {
        let callback = null;
        let maxlength = null;
        let required = false;
        let input_type = password ? 'password' : 'text';
        if (typeof options == 'function') {
            callback = options
        } else if (typeof options == 'object') {
            maxlength = options.maxlength || null;
            callback = options.callback && typeof options.callback == 'function' ? options.callback : null;
            required = options.required || false
        }
        let inputid = 'prompt_' + (+new Date());
        let max = maxlength ? " maxlength='" + maxlength + "' " : '';
        require(['jquery.confirm'], function () {
            $.alert({
                title: '提示',
                content: "<p>" + msg + "</p><p style='padding-top: 15px;'><input style='height: 40px;line-height: 40px;padding: 0 15px;' id='" + inputid + "' type='" + input_type + "' class='form-control' name='confirm' placeholder='" + msg + "' " + max + " /></p>",
                confirmButtonClass: 'btn-primary',
                confirmButton: '确 定',
                closeIcon: true,
                animation: 'top',
                keyboardEnabled: true,
                onOpen: function () {
                    setTimeout(function () {
                        $('#' + inputid).focus()
                    }, 100)
                },
                confirm: function () {
                    let value = $('#' + inputid).val();
                    if ($.trim(value) == '' && required) {
                        $('#' + inputid).focus();
                        return false
                    }
                    if (callback && typeof (callback) == 'function') {
                        callback(value)
                    }
                }
            })
        })
    };

    tip.promptlive = function (msg, options, password) {
        let callback = null;
        let maxlength = null;
        let required = false;
        let input_type = password ? 'password' : 'text';
        if (typeof options == 'function') {
            callback = options
        } else if (typeof options == 'object') {
            maxlength = options.maxlength || null;
            callback = options.callback && typeof options.callback == 'function' ? options.callback : null;
            required = options.required || false
        }
        let inputid = 'prompt_' + (+new Date());
        let max = maxlength ? " maxlength='" + maxlength + "' " : '';
        require(['jquery.confirm'], function () {
            $.alert({
                title: '提示',
                content: "<p>" + msg + "</p><p><input id='" + inputid + "' type='" + input_type + "' class='form-control' name='confirm' placeholder='' " + max + " /></p>",
                confirmButtonClass: 'btn-primary',
                confirmButton: '确 定',
                closeIcon: true,
                animation: 'top',
                keyboardEnabled: true,
                onOpen: function () {
                    setTimeout(function () {
                        $('#' + inputid).focus()
                    }, 100)
                },
                confirm: function () {
                    let value = $('#' + inputid).val();
                    if ($.trim(value) == '' && required) {
                        $('#' + inputid).focus();
                        return false
                    }
                    if (callback && typeof (callback) == 'function') {
                        callback(value);
                        return false
                    }
                }
            })
        })
    };

    tip.alert = function (msg, callback) {
        msg = msg.replace(/&lt;/g, "<");
        msg = msg.replace(/&gt;/g, ">");
        require(['jquery.confirm'], function () {
            $.alert({
                title: '提示',
                content: msg,
                confirmButtonClass: 'btn-primary',
                confirmButton: '确 定',
                animation: 'top',
                confirm: function () {
                    if (callback && typeof (callback) == 'function') {
                        callback()
                    }
                }
            })
        })
    };

    tip.success = function (msg, delay = 2000) {
        let messageObj = $(`<div role="alert" class="tip-message tip-message--success" style="z-index: 2001;"><i class="iconfont icon-success">&#xe616;</i><p class="tip-message__content">${msg}</p></div>`);
        $('body').append(messageObj);

        messageObj.animate({}, () => {
            messageObj.css({
                'top': '20px',
                'opacity': '1',
            });
        });

        setTimeout(() => {
            messageObj.animate({}, () => {
                messageObj.css({
                    'top': '-32px',
                    'opacity': '0',
                    'transform': 'translateX(0%)',
                });
            });
        }, delay)
    };

    tip.error = function (msg, delay = 2000) {
        let messageObj = $(`<div role="alert" class="tip-message tip-message--error" style="z-index: 2001;"><i class="iconfont icon-error">&#xe616;</i><p class="tip-message__content">${msg}</p></div>`);
        $('body').append(messageObj);

        messageObj.animate({}, () => {
            messageObj.css({
                'top': '20px',
                'opacity': '1',
            });
        });

        setTimeout(() => {
            messageObj.animate({}, () => {
                messageObj.css({
                    'top': '-32px',
                    'opacity': '0',
                    'transform': 'translateX(0%)',
                });
            });
        }, delay)
    };

    let Notify = function (element, options) {
        this.$element = $(element);
        this.options = $.extend({}, $.fn.notify.defaults, options);
        let cls = this.options.type ? "msg-" + this.options.type : "msg-success";
        let $note = '<span class="msg ' + cls + '">' + this.options.message + '</span>';
        this.$element.html($note);
        return this
    };

    Notify.prototype.show = function () {
        this.$element.addClass('in');
        this.$element.append(this.$note);
        let autoClose = this.options.autoClose || true;
        if (autoClose) {
            let self = this;
            setTimeout(function () {
                self.close()
            }, this.options.delay || 2000)
        }
    };

    Notify.prototype.close = function () {
        let self = this;
        self.$element.removeClass('in');
        if (self.options.onClose) {
            self.options.onClose(self)
        }
    };

    $.fn.notify = function (options) {
        return new Notify(this, options)
    };

    $.fn.notify.defaults = {
        type: "success",
        delay: 3000,
        message: ''
    };

    tip.msgbox = {
        show: function (options) {
            if (options.url) {
                options.url = options.url.replace(/&amp;/ig, "&");
                options.onClose = function () {
                    location.href = options.url
                }
            }
            if (options.message && options.message.length > 17) {
                tip.alert(options.message, function () {
                    if (options.url) {
                        location.href = options.url
                    }
                });
                return
            }
            notify = window.msgbox.notify(options), notify.show()
        },
        suc: function (msg, url, onClose, onClosed) {
            tip.msgbox.show({
                delay: 1500,
                type: "success",
                message: msg,
                url: url,
                onClose: onClose,
                onClosed: onClosed
            })
        },
        err: function (msg, url, onClose, onClosed) {
            tip.msgbox.show({
                delay: 2000,
                type: "error",
                message: msg,
                url: url,
                onClose: onClose,
                onClosed: onClosed
            })
        }
    };

    tip.msg = tip.msgbox;

    tip.impower = function (msg, callback, cancel_callback) {
        msg = msg.replace(/&lt;/g, "<");
        msg = msg.replace(/&gt;/g, ">");
        require(['jquery.confirm'], function () {
            $.confirm({
                title: '  ',
                content: msg,
                confirmButtonClass: 'btn-default',
                cancelButtonClass: 'btn-primary',
                confirmButton: '重新上传',
                cancelButton: '审核完成',
                animation: 'top',
                closeIcon: true,
                confirm: function () {
                    if (callback && typeof (callback) == 'function') {
                        callback()
                    }
                },
                cancel: function () {
                    if (cancel_callback && typeof (cancel_callback) == 'function') {
                        cancel_callback()
                    }
                }
            })
        })
    };

    window.tip = tip
}

if( typeof define === "function"){
    define(['jquery'], function ($) {
        tipFun()
    });
}else{
    tipFun()
}