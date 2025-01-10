
define(['bootstrap', 'webuploader', 'util', 'filestyle','jquery.ui'], function ($, WebUploader, util) {
    /*
        'options' : {
            type     : '',     设置上传类型 image/audio
            multi    : false,  返回结果为 object | array
            direct   : false,  是否单击即选中文件
            dest_dir : '',     重置上传目录,
            global   : true,   定位到 global 目录
            tabs : {
                'upload'      : 'active', 上传文件
                'crawler'     : '',       提取网络文件
                'browser'     : '',       附件浏览
                'remoteImage' : '',       上传到微信
                'remoteAudio' : '',       上传到微信
            }
        },
     */

    fileUploader = {

        'supports': ['upload', 'crawler', 'browser', 'remoteAudio', 'remoteImage'],

        'defaultoptions': {
            debug: false,
            global: false,
            callback: null, // 回调方法
            type: 'image', // 上传组件类型
            direct: false, // 效果, 是否选择即返回, 单图可用.
            multi: false, // 返回结果是 object 还是 Array
            dest_dir: '', // 自定义上传目录
            tabs: { // 选项卡, remote
                'upload': 'active',
                'browser': '',
                'crawler': ''
            }
        },

        'options': {}, // 当前配置项

        'show': function (callback, options) {
            this.init(callback, options);
        },

        'reset': function () {
            if (this.modalobj != null) {
                this.images = [];
                for (i in this.options.tabs) {
                    eval("$this.reset_" + i + "();");
                }
            }
        },

        'hide': function () {
            if (this.modalobj != null) {
                this.reset();
                this.modalobj.modal('hide');
            }
        },

        'uploader': {},
        'modalobj': null,
        'images': [],
        'uploadImages': [],

        /*上次控件的状态,tabname,active*/
        'historyOptions': '',
        'test': function (msg) {
            $this = this;
            if (!$this.options.debug) {
                return;
            }
            console.log('fileupload->debug-------start------')
            console.log('【选项卡】: ' + msg);
            console.log('【options】: ');
            console.log($this.options);
            console.log('【result】: ');
            if ($this.options.multi) {
                console.log($this.images);
            } else {
                console.log($this.images[0]);
            }
            console.log('【callback】: ');
            console.dir($this.options.callback);
            console.log('fileupload->debug-------end------')
        },

        'init': function (callback, options) {

            $this = this;

            this.options = $.extend({}, this.defaultoptions, options);

            console.log('options',this.options)

            this.options.callback = callback;

            if (options.tabs) {

                this.options.tabs = {};

                if (typeof (options.tabs.remote) != 'undefined') {
                    if (this.options.type == 'image') {
                        options.tabs['remoteImage'] = options.tabs.remote;
                    } else {
                        options.tabs['remoteAudio'] = options.tabs.remote;
                    }
                    delete options.tabs.remote;
                }

                for (i in options.tabs) {
                    if ($.inArray(i, $this.supports) > -1) {
                        $this.options.tabs[i] = options.tabs[i];
                    }
                }
            }
            if (this.options.global) {
                this.options.global = 'global';
            } else {
                this.options.global = '';
            }

            document.cookie = "__fileupload_type=" + escape(this.options.type);
            document.cookie = "__fileupload_dest_dir=" + escape(this.options.dest_dir);
            document.cookie = "__fileupload_global=" + escape(this.options.global);

            if ($('#modal-fileUploader').length == 0) {
                $(document.body).append('<div id="modal-fileUploader" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                this.modalobj = $('#modal-fileUploader');
                this.modalobj.append(
                    '<div class="modal-dialog" style="width: 960px;">\n' +
                    '    <div class="modal-content">\n' +
                    '         <div class="modal-header" style="padding: 15px 20px;">' +
                    '              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                    '              <ul class="nav nav-pills" role="tablist">' +
                    '              </ul>' +
                    '         </div>' +
                    '         <div class="modal-body tab-content"></div>\n' +
                    '    </div>\n' +
                    '</div>\n'
                );
            } else {
                this.modalobj = $('#modal-fileUploader');
            }

            let currentOptions = '';
            for (i in this.options.tabs) {
                eval("this.init_" + i + "();");
                currentOptions += i + $this.options.tabs[i];
            }

            if (!this.historyOptions || this.historyOptions != currentOptions) {

                $this.modalobj.find('.nav-pills').find('li').removeClass('active').hide();
                $this.modalobj.find('.tab-pane').removeClass('active');

                for (i in $this.options.tabs) {
                    $this.modalobj.find('.nav-pills').find('a[aria-controls="' + i + '"]').parent().show();
                    if ($this.options.tabs[i]) {
                        $this.modalobj.find('.nav-pills').find('a[aria-controls="' + i + '"]').parent().addClass('active');
                        $this.modalobj.find('#' + i).addClass('active');
                    }
                }

                if ($this.options.path) {
                    this.browser(this.options.path);
                } else {
                    this.browser(this.options.type + 's');
                }
            }

            $($this.modalobj.find('.nav-pills').find('li.active').find('a').attr('href')).addClass('active');

            this.reset();

            this.historyOptions = currentOptions;

            this.modalobj.modal({'keyboard': false});
            this.modalobj.modal('show');

            let eduizindex = $('#edui1').css('z-index');
            this.modalobj.css('z-index', parseInt(eduizindex) + 100);
        },

        'init_crawler': function () {
            $this = this;

            if (this.modalobj.find('#crawler').length == 0) {
                this.modalobj.find('.nav-pills').append('<li role="presentation"><a aria-controls="crawler" role="tab" data-toggle="tab" href="#crawler">提取网络文件</a></li>');
                this.modalobj.find('.modal-body').append(this.template().crawler);
            }

            this.modalobj.find('#btnFetch').off('click');
            this.modalobj.find('#btnFetch').click(function () {
                let url = $('#crawlerUrl').val();
                if (url.length == 0) {
                    alert('请输入网络文件地址.');
                }
                if (url.length > 0) {
                    $.post('/admin/file/fetch', {'uniacid': window.sysinfo.uniacid, 'module': window.sysinfo.module, 'url': url}, function (data) {
                        let result = $.parseJSON(data);
                        if (result.message) {
                            alert(result.message);
                        } else {
                            $this.images = [];
                            $this.images.push(result);
                            if ($this.options.direct == true) {
                                $this.modalobj.find('.crawler').find('button.btn-primary').click();
                            }
                            if ($this.options.type == 'image') {
                                $this.modalobj.find('.crawler').find('.crawler-img-sizeinfo').text(result.width + 'x' + result.height);
                            } else {
                                $this.modalobj.find('.crawler').find('.crawler-img-sizeinfo').text(result.size);
                            }
                            $this.modalobj.find('.crawler').find('.crawler-img').css("background-image", "url(" + result.url + ")");
                        }
                    });
                }
            });

            this.modalobj.find('#crawler').find('button.btn-primary').off('click');
            this.modalobj.find('#crawler').find('button.btn-primary').on('click', function () {
                if ($this.images.length > 0) {
                    if ($.isFunction($this.options.callback)) {
                        if ($this.options.multi) {
                            $this.options.callback($this.images);
                        } else {
                            $this.options.callback($this.images[0]);
                        }
                        $this.hide();
                    }
                } else {
                    alert('未选择任何文件.');
                }
            });
        },

        'reset_crawler': function () {
            $('#crawlerUrl').val('');
            this.modalobj.find('#crawler').find('.crawler-img-sizeinfo').text('');
            this.modalobj.find('#crawler').find('.crawler-img').css("background-image", "url('../static/images/nopic.png')");
        },

        'init_remoteImage': function () {

            $this = this;

            if (this.modalobj.find('#remoteImage').length == 0) {
                this.modalobj.find('.nav-pills').append('<li role="presentation"><a aria-controls="remoteImage" role="tab" data-toggle="tab" href="#remoteImage">上传到微信</a></li>');
                this.modalobj.find('.modal-body').append(this.template().remoteImage);
            }

            this.modalobj.find('#remoteImage').find(':file[name="file"]').filestyle({buttonText: '选择文件'});
            this.modalobj.find('#remoteImage').find('button.btn-primary').off('click');
            this.modalobj.find('#remoteImage').find('button.btn-primary').on('click', function () {

                util.loading();

                $('#fileUploader_remote_image_form').submit();
                let interval = setInterval(function () {
                    let content = $('#fileUploader_remote_image_target').get(0).contentWindow.document.body.innerText;
                    if (content != '') {
                        clearInterval(interval);
                        let result = $.parseJSON(content);
                        if (result.message) {
                            alert(result.message);
                        } else if (result.media_id) {
                            $this.test('remote-image');
                            if ($.isFunction($this.options.callback)) {
                                if ($this.options.multi) {
                                    $this.options.callback([result]);
                                } else {
                                    $this.options.callback(result);
                                }

                                util.loaded();

                                $this.hide();
                            }
                        } else {
                            console.log('上传文件错误信息:');
                            console.log(result);
                        }
                    }
                }, 500);
            });
        },

        'reset_remoteImage': function () {

        },

        'init_remoteAudio': function () {
            $this = this;
            if (this.modalobj.find('#remoteAudio').length == 0) {
                this.modalobj.find('.nav-pills').append('<li role="presentation"><a aria-controls="remoteAudio" role="tab" data-toggle="tab" href="#remoteAudio">上传到微信</a></li>');
                this.modalobj.find('.modal-body').append(this.template().remoteAudio);
                this.modalobj.find('#remoteAudio').find(':file[name="file"]').filestyle({buttonText: '选择文件'});
            }
            this.modalobj.find('#remoteAudio').find('button.btn-primary').off('click');
            this.modalobj.find('#remoteAudio').find('button.btn-primary').on('click', function () {

                util.loading();

                $('#fileUploader_remote_audio_form').submit();
                let interval = setInterval(function () {
                    let $target = $('#fileUploader_remote_audio_target').get(0).contentWindow.document.body.innerText;
                    if ($target != '') {
                        clearInterval(interval);
                        let result = $.parseJSON($target);
                        if (result.message) {
                            alert(result.message);
                        } else if (result.media_id) {
                            $this.test('remote-audio');
                            if ($.isFunction($this.options.callback)) {
                                if ($this.options.multi) {
                                    $this.options.callback([result]);
                                } else {
                                    $this.options.callback(result);
                                }

                                util.loaded();

                                $this.hide();
                            }
                        } else {
                            console.log(result);
                        }
                    }
                }, 500);
            });
        },

        'reset_remoteAudio': function () {

        },

        'init_browser': function () {
            $this = this;

            if (this.modalobj.find('#browser').length == 0) {
                this.modalobj.find('.nav-pills').append('<li role="presentation"><a aria-controls="browser" role="tab" data-toggle="tab" href="#browser">浏览附件</a></li>');
                this.modalobj.find('.modal-body').append(this.template().browser);
                this.browser(this.type + 's');
            }

            this.modalobj.find('#browser').find('button.btn-primary').off('click');
            this.modalobj.find('#browser').find('button.btn-primary').on('click', function () {
                if ($this.images.length > 0) {
                    $this.test('browser');
                    if ($.isFunction($this.options.callback)) {
                        if ($this.options.multi) {
                            $this.options.callback($this.images);
                        } else {
                            $this.options.callback($this.images[0]);
                        }
                        $this.hide();
                    }
                } else {
                    alert('未选择任何文件.');
                }
            });
        },

        'reset_browser': function () {
            this.modalobj.find('#browser').find('.img-item-selected').removeClass('img-item-selected');
            this.modalobj.find('#browser').find('.browser-info').text('');
        },

        'browserfiles': {},

        'browser': function (path) {
            $this = this;

            if (path === 'undefineds' || path === undefined) {
                return false
            }

            let $browser = $this.modalobj.find('#browser');

            $browser.find('.browser').html('<i class="icon icon-spinner icon-pulse"></i>');

            //初始化数据
            $.getJSON('/admin/file/browser.html', {'uniacid': window.sysinfo.uniacid, 'module': window.sysinfo.module, 'type': $this.options.type, 'path': path}, function (data) {
                if (data['message']) {
                    alert(data.message);
                } else {

                    $this.browserfiles = {};

                    let crumbs = data.list;
                    let html = '';
                    for (i in crumbs) {
                        if ($this.options.type === 'image') {
                            // html += '<li><a herf="javascript:;" class="browser-item" attachment="' + crumbs[i].fileurl + '">' + crumbs[i].filename + '</a></li>';
                            html += '<div herf="javascript:;" class="browser-item" attachment="' + crumbs[i].fileurl + '" url="' + crumbs[i].url + '" style="background-image: url(' + crumbs[i].url + ');">'
                                + '<div class="browser-item-name">' + crumbs[i].filename + '</div>'
                                + '</div>';
                        } else if ($this.options.type === 'video') {
                            // html += '<li><a herf="javascript:;" class="browser-item" attachment="' + crumbs[i].fileurl + '">' + crumbs[i].filename + '</a></li>';
                            html += '<div herf="javascript:;" class="browser-item" attachment="' + crumbs[i].fileurl + '" url="' + crumbs[i].url + '" style="background-image: url(' + crumbs[i].url + ');">'
                                + '<div class="browser-item-icon"><i class="icon icon-play-circle"></i></div>'
                                + '<div class="browser-item-name">' + crumbs[i].filename + '</div>'
                                + '</div>';
                        }
                    }
                    $browser.find('.breadcrumb').empty();
                    // $browser.find('.breadcrumb').append(html);
                    $browser.find('.breadcrumb').find('li').eq(0).find('a').html('<i class="icon icon-home">&nbsp;</i>');

                    html = '';
                    // let parent = data.parent;
                    // if (parent != null) {
                    //     html =
                    //         '<div title="上一级" attachment="' + parent.attachment + '" title="' + parent.filename + '" class="thumbnail browser-item">' +
                    //         ' <i class="icon icon-mail-reply" style="font-size:45px; padding:10px;"></i>' +
                    //         '</div>';
                    // }

                    let deleteHtml = '';
                    if (data.canDelete === true) { // 是否有删除权限
                        deleteHtml = '<div class="btnClose"><a href="javascript:;"><i class="icon icon-trash"></i></a></div>';
                    }

                    let files = data.list;
                    for (i in files) {
                        let file = files[i];

                        if (file.is_dir) {
                            html +=
                                '<div title="' + file.filename + '" attachment="' + file.fileurl + '" class="thumbnail browser-item">' +
                                '  <i class="icon icon-folder"></i>' +
                                '  <span class="text-center">' + file.filename + '</span>' +
                                '</div>';
                        } else {
                            file['id'] = i;
                            $this.browserfiles['file_' + i] = file;

                            if ($this.options.type === 'image') {
                                html +=
                                    '<div class="img-item" title="' + file.filename + '" attachid="' + file.id + '" attachment="' + file.fileurl + '">' +
                                    deleteHtml +
                                    '   <div class="img-container" style="background-image: url(\'' + file.url + '\');">' +
                                    '        <div class="img-meta">' + file.filename + '</div>' +
                                    '        <div class="select-status"><span></span></div>' +
                                    '   </div>' +
                                    '</div>';
                            } else {
                                // html +=
                                //     '<div class="img-item" title="' + file.filename + '" attachid="' + file.id + '" attachment="' + file.fileurl + '">' +
                                //     '   <div class="btnClose"><a href="javascript:;"><i class="icon icon-times"></i></a></div>' +
                                //     '   <div class="img-container" style="background-image: url(\'./resource/images/media.jpg\');">' +
                                //     '        <div class="img-meta">' + file.size + '</div>' +
                                //     '        <div class="select-status"><span></span></div>' +
                                //     '   </div>' +
                                //     '</div>';

                                html += '<div herf="javascript:;" class="img-item browser-item" title="' + file.fileurl + '" attachid="' + file.id + '" attachment="' + file.fileurl + '" url="' + file.url + '">'
                                    + deleteHtml
                                    + '<div class="browser-item-icon"><i class="icon icon-play-circle"></i></div>'
                                    + '<div class="browser-item-name">' + crumbs[i].filename + '</div>'
                                    + '</div>';

                            }
                        }
                    }
                    $browser.find('.file-browser').empty();
                    $browser.find('.file-browser').append(html);

                    // 绑定事件
                    $browser.find('.browser-item').off('click');
                    $browser.find('.browser-item').on('click', function () {
                        // console.log('browser-item',$(this).attr('attachment'))
                        // $this.browser($(this).attr('attachment'));

                        let imgObj = {
                            filename: $(this).attr('attachment'),
                            url: $(this).attr('url'),
                        };

                        if (!$this.options.multi) {
                            $this.images = imgObj;
                        } else {
                            $this.images.push(imgObj);
                        }

                        $this.options.callback($this.images);
                        $this.hide();
                    });

                    $browser.find('.btnClose').off('click');
                    $browser.find('.btnClose').on('click', function (event) {
                        let $this = this;
                        if (confirm("确定要删除文件吗？")) {
                            let attachment = $(this).parent().attr('attachment');
                            $.post('/admin/file/delete.html', {'uniacid': window.sysinfo.uniacid, 'module': window.sysinfo.module, 'file': attachment}, function (data) {
                                if( data.message.errno == 1 ){
                                    alert(data.message.message);
                                }else{
                                    $($this).parent().remove();
                                }
                            },'json');
                        }
                        event.stopPropagation();
                    });

                    $browser.find('.img-item').off('click');
                    $browser.find('.img-item').on('click', function () {
                        $(this).toggleClass('img-item-selected');

                        // 修改后按照先后顺讯存储
                        let attachid = $(this).attr('attachid');
                        let attachment = $(this).attr('attachment');

                        if ($(this).hasClass('img-item-selected')) {
                            $this.images.push($this.browserfiles['file_' +attachid]);
                        } else {
                            $this.images = $this.images.filter(function(item) {
                                return item.fileurl !== attachment;
                            });
                        }

                        // 未修改前不按照顺序
                        // $this.images = [];
                        // $.each($('.img-item-selected'), function (idx, ele) {
                        //     $this.images.push($this.browserfiles['file_' + $(ele).attr('attachid')]);
                        // });

                        $browser.find('.browser-info').text('已选中 ' + $this.images.length + ' 个文件.');

                        if (($this.options.direct || !$this.options.multi) && $(this).hasClass('img-item-selected')) {
                            $browser.find('button.btn-primary').click();
                        }
                    });

                }
            });
        },

        'init_upload': function () {

            $this = this;

            if (this.modalobj.find('.nav-pills').html().indexOf('上传文件') == -1) {
                this.modalobj.find('.nav-pills').append('<li role="presentation"><a aria-controls="upload" role="tab" data-toggle="tab" href="#upload" >上传文件</a></li>');
            }

            this.modalobj.find('#upload').remove();

            if (this.modalobj.find('#upload').length == 0) {

                this.modalobj.find('.modal-body').append(this.template().upload);

                let $wrap = $('#uploader'),

                    // 图片容器
                    $queue = $('<ul class="filelist"></ul>').appendTo($wrap.find('.queueList')),
                    // 状态栏，包括进度和控制按钮
                    $statusBar = $wrap.find('.statusBar'),
                    // 文件总体选择信息。
                    $info = $statusBar.find('.info'),
                    // 上传按钮
                    $upload = $wrap.find('.uploadBtn'),
                    // 没选择文件之前的内容。
                    $placeHolder = $wrap.find('.placeholder'),
                    $progress = $statusBar.find('.progress').hide(),
                    // 添加的文件数量
                    fileCount = 0,
                    // 添加的文件总大小
                    fileSize = 0,
                    // 优化retina, 在retina下这个值是2
                    ratio = window.devicePixelRatio || 1,
                    // 缩略图大小
                    thumbnailWidth = 110 * ratio,
                    thumbnailHeight = 110 * ratio,
                    // 可能有pedding, ready, uploading, confirm, done.
                    state = 'pedding',
                    // 所有文件的进度信息，key为file id
                    percentages = {},
                    supportTransition = (function () {
                        let s = document.createElement('p').style,
                            r = 'transition' in s ||
                                'WebkitTransition' in s ||
                                'MozTransition' in s ||
                                'msTransition' in s ||
                                'OTransition' in s;
                        s = null;
                        return r;
                    })(),

                    uploader;

                    // 更改排列顺序
                    $('.filelist').sortable({scroll: 'false'});

                let options = {
                    //auto: !$this.options.multi,
                    pick: {
                        id: '#filePicker',
                        label: '点击选择文件',
                        multiple: $this.options.multi
                    },
                    dnd: '#dndArea', // 拖拽区域
                    paste: '#uploader', // 粘贴上传
                    // swf文件路径
                    swf: './public/app/admin/static/components/webuploader/Uploader.swf',
                    // 文件接收服务端。
                    server: '/admin/file/upload.html',
                    chunked: false,
                    compress: false,
                    formData: {
                        uniacid: window.sysinfo.uniacid,
                        module: window.sysinfo.module,
                        uploadtype: escape(this.options.type),
                        dest_dir: escape(this.options.dest_dir),
                        global: escape(this.options.global),
                        thumb: escape(this.options.thumb),
                        width: escape(this.options.width)
                    },
                    duplicate: true,
                    fileNumLimit: $this.options.multi ? 30 : 1,
                    fileSizeLimit: 200 * 1024 * 1024, // 200 M大小
                    fileSingleSizeLimit: 100 * 1024 * 1024, // 100 M大小
                };

                // 实例化
                uploader = WebUploader.create(options);

                if ($this.options.multi) {
                    // 添加“添加文件”的按钮，
                    uploader.addButton({
                        id: '#filePicker2',
                        label: '继续添加',
                        multiple: $this.options.multi
                    });
                }

                $this.uploader = uploader;

                // 成功上传
                accept = 0;

                $this.reset_upload = function () {

                    fileCount = 0;
                    fileSize = 0;
                    accept = 0;

                    $.each($this.uploader.getFiles(), function (index, file) {
                        removeFile(file);
                    });
                    updateTotalProgress();

                    $this.uploader.reset();
                    $this.uploader.refresh();

                    $('#dndArea').removeClass('element-invisible');
                    $('#uploader').find('.filelist').empty();
                    if ($this.options.multi) {
                        $('#filePicker2').removeClass('element-invisible');
                        $('#filePicker2').next().removeClass('disabled');
                        $('#filePicker2').find('.webuploader-pick').next().css({'top': '0px', 'left': '0px', 'width': '100px', 'height': '32px'});
                    }
                    $('#filePicker').find('.webuploader-pick').next().css({'left': '20%', 'top': '28px', 'width': '60%', 'height': '60px'});
                    let bar = $('#uploader').find('.statusBar');
                    bar.find('.info').empty();
                    bar.find('.accept').empty();
                    bar.show();
                };

                // 当有文件添加进来时执行，负责view的创建
                function addFile(file) {

                    console.log('addFile',file)

                    let $li = $('<li id="' + file.id + '">' +
                        '<p class="title">' + file.name + '</p>' +
                        '<p class="imgWrap"></p>' +
                        '<p class="progress"><span></span></p>' +
                        '</li>'),
                        $btns = $('<div class="file-panel">' +
                            '<span class="cancel">删除</span></div>').appendTo($li),
                        $prgress = $li.find('p.progress span'),
                        $wrap = $li.find('p.imgWrap'),
                        $info = $('<p class="error"></p>'),

                        showError = function (code) {
                            switch (code) {
                                case 'exceed_size':
                                    text = '文件大小超出';
                                    break;
                                case 'interrupt':
                                    text = '上传暂停';
                                    break;
                                default:
                                    text = '上传失败，请重试';
                                    break;
                            }
                            $info.text(text).appendTo($li);
                        };
                    if (file.getStatus() === 'invalid') {
                        showError(file.statusText);
                    } else {
                        // @todo lazyload
                        $wrap.text('预览中');
                        uploader.makeThumb(file, function (error, src) {
                            if (error) {
                                $wrap.text('不能预览');
                                return;
                            }
                            let img = $('<img src="' + src + '">');
                            $wrap.empty().append(img);
                        }, thumbnailWidth, thumbnailHeight);
                        percentages[file.id] = [file.size, 0];
                        file.rotation = 0;
                    }
                    file.on('statuschange', function (cur, prev) {
                        if (prev === 'progress') {
                            $prgress.hide().width(0);
                        } else if (prev === 'queued') {
                            $li.off('mouseenter mouseleave');
                            $btns.remove();
                        }
                        // 成功
                        if (cur === 'error' || cur === 'invalid') {
                            showError(file.statusText);
                            percentages[file.id][1] = 1;
                        } else if (cur === 'interrupt') {
                            showError('interrupt');
                        } else if (cur === 'queued') {
                            percentages[file.id][1] = 0;
                        } else if (cur === 'progress') {
                            $info.remove();
                            $prgress.css('display', 'block');
                        } else if (cur === 'complete') {
                            //$li.append('<span class="success"></span>');
                        }
                        $li.removeClass('state-' + prev).addClass('state-' + cur);
                    });

                    $li.on('mouseenter', function () {
                        $btns.stop().animate({height: 30});
                    });

                    $li.on('mouseleave', function () {
                        $btns.stop().animate({height: 0});
                    });

                    $btns.on('click', 'span', function () {
                        let index = $(this).index(),
                            deg;
                        switch (index) {
                            case 0:
                                uploader.removeFile(file);
                                return;
                            case 1:
                                file.rotation += 90;
                                break;
                            case 2:
                                file.rotation -= 90;
                                break;
                        }
                        if (supportTransition) {
                            deg = 'rotate(' + file.rotation + 'deg)';
                            $wrap.css({
                                '-webkit-transform': deg,
                                '-mos-transform': deg,
                                '-o-transform': deg,
                                'transform': deg
                            });
                        } else {
                            $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                        }
                    });
                    $li.appendTo($queue);
                }

                // 负责view的销毁
                function removeFile(file) {
                    let $li = $('#' + file.id);
                    delete percentages[file.id];
                    updateTotalProgress();
                    $li.off().find('.file-panel').off().end().remove();
                }

                function updateTotalProgress() {
                    let loaded = 0,
                        total = 0,
                        spans = $progress.children(),
                        percent;

                    $.each(percentages, function (k, v) {
                        total += v[0];
                        loaded += v[0] * v[1];
                    });

                    percent = total ? loaded / total : 0;

                    spans.eq(0).text(Math.round(percent * 100) + '%');
                    spans.eq(1).css('width', Math.round(percent * 100) + '%');
                    updateStatus();
                }

                function updateStatus() {
                    let text = '', stats;

                    if (state === 'ready') {
                        text = '选中' + fileCount + '个文件，共' + WebUploader.formatSize(fileSize) + '。';
                    } else if (state === 'confirm') {
                        stats = uploader.getStats();
                        if (stats.uploadFailNum) {
                            text = '已上传' + stats.successNum + '个文件,' + stats.uploadFailNum + '个文件上传失败，<a class="retry" href="#">重新上传</a>失败文件或<a class="ignore" href="#">忽略</a>'
                        }
                    } else {
                        stats = uploader.getStats();
                        text = '共' + fileCount + '个（' + WebUploader.formatSize(fileSize) + '），已上传' + stats.successNum + '个';

                        if (stats.uploadFailNum) {
                            text += '，失败' + stats.uploadFailNum + '个';
                        }
                    }

                    $info.html(text);
                }

                function setState(val) {
                    let file, stats;
                    if (val === state) {
                        return;
                    }
                    $upload.removeClass('state-' + state);
                    $upload.addClass('state-' + val);
                    state = val;
                    switch (state) {
                        case 'pedding':
                            $placeHolder.removeClass('element-invisible');
                            $queue.hide();
                            $statusBar.addClass('element-invisible');
                            uploader.refresh();
                            break;
                        case 'ready':
                            $placeHolder.addClass('element-invisible');
                            $('#filePicker2').removeClass('element-invisible');
                            $queue.show();
                            $statusBar.removeClass('element-invisible');
                            uploader.refresh();
                            break;
                        case 'uploading':
                            $('#filePicker2').addClass('element-invisible');
                            $progress.show();
                            $upload.text('暂停上传');
                            break;
                        case 'paused':
                            $progress.show();
                            $upload.text('继续上传');
                            break;
                        case 'confirm':
                            $progress.hide();
                            $upload.text('确认使用').addClass('disabled');
                            stats = uploader.getStats();
                            if (stats.successNum && !stats.uploadFailNum) {
                                setState('finish');
                                return;
                            }
                            break;
                        case 'finish':
                            $('#filePicker2').removeClass('element-invisible');
                            $upload.text('确认使用').removeClass('disabled');
                            stats = uploader.getStats();
                            if (stats.successNum) {
                                // alert('上传成功');
                            } else {
                                // 没有成功的文件，重设
                                state = 'done';
                                location.reload();
                            }
                            break;
                    }
                    updateStatus();
                }

                uploader.onUploadProgress = function (file, percentage) {
                    let $li = $('#' + file.id),
                        $percent = $li.find('.progress span');
                    $percent.css('width', percentage * 100 + '%');
                    percentages[file.id][1] = percentage;
                    fileid = file.id;
                    updateTotalProgress();
                };

                uploader.onFileQueued = function (file) {
                    fileCount++;
                    fileSize += file.size;

                    if (fileCount === 1) {
                        $placeHolder.addClass('element-invisible');
                        $statusBar.show();
                    }

                    console.log('onFileQueued',file)

                    addFile(file);
                    setState('ready');
                    updateTotalProgress();
                };

                // 使用 uploadBeforeSend 钩子在每次上传前设置顺序
                uploader.on('uploadBeforeSend', function(block, data) {
                    console.log('uploadBeforeSend',block)
                    $this.uploadImages.push(block.blob.name);
                    // 使用当前块的序号来从 imageFiles 数组中获取正确的文件对象
                    // let file = imageFiles[block.file.id];
                    // 将正确的文件对象重新赋值到 block.file
                    // block.file = file;
                    // 如果有必要，可以修改 data 对象来包含额外的参数
                });

                uploader.onFileDequeued = function (file) {
                    fileCount--;
                    fileSize -= file.size;

                    if (!fileCount) {
                        setState('pedding');
                    }

                    removeFile(file);
                    updateTotalProgress();

                    $('#filePicker2').removeClass('element-invisible');
                    $('#filePicker2').next().removeClass('disabled');
                };

                uploader.on('all', function (type) {
                    let stats;
                    switch (type) {
                        case 'uploadFinished':
                            setState('confirm');
                            break;
                        case 'startUpload':
                            setState('uploading');
                            break;
                        case 'stopUpload':
                            setState('paused');
                            break;
                        case 'beforeFileQueued':
                            break;
                        case 'fileQueued':
                            break;
                    }
                });

                uploader.on('uploadSuccess', function (file, result) {
                    if (result == 'Access Denied') {
                        console.log(result);
                    }
                    if (result.message) {
                        alert(result.message);
                    }
                    if (!result.message) {
                        accept++;
                        $this.images.push(result);
                        if (result.width) {
                            $('#' + file.id).append('<span class="success" style="line-height: 50px;">' + result.width + 'x' + result.height + '</span>');
                        } else if (result.size) {
                            $('#' + file.id).append('<span class="success" style="line-height: 50px;">' + result.size + '</span>');
                        }
                        $('.accept').text('成功上传 ' + accept + ' 个文件');

                        if (!$this.options.multi) {
                            $this.modalobj.find('#upload').find('.btn.btn-primary').click();
                        }
                    }
                });

                uploader.on('uploadFinished', function () {
                    // console.log("uploader.on uploadFinished uploadImages",$this.uploadImages);

                    // 获取dom元素的排列顺序
                    let newUploadImages = [];
                    $.each($('.filelist li'), function (idx, ele) {
                        newUploadImages.push($($(ele).children()[0]).text())
                    });
                    // console.log("uploader.on newUploadImages",newUploadImages);

                    // 手动更改图片位置
                    // $this.uploadImages = ['3.jpeg', '2.png', '1.png'];
                    $this.images = $this.sortObjectArrayByNameArray(newUploadImages, $this.images);

                    // console.log("uploader.on uploadFinished updated images",$this.images);

                    if ($this.images.length > 0) {
                        $this.modalobj.find('#upload').find('.btn.btn-primary').click();
                    }
                });

                uploader.onError = function (code) {
                    console.log("uploader.on onError")
                    if (code == 'Q_EXCEED_SIZE_LIMIT') {
                        alert('错误信息: 文件大于 100M 无法上传.');
                        return
                    }
                    if (code == 'F_DUPLICATE') {
                        alert('错误信息: 不能重复上传文件.');
                        return
                    }
                    alert('Eroor: ' + code);
                };

                $upload.on('click', function () {
                    console.log("uploader.on click")
                    if ($(this).hasClass('disabled')) {
                        return false;
                    }
                    if (state === 'ready') {
                        uploader.upload();
                    } else if (state === 'paused') {
                        uploader.upload();
                    } else if (state === 'uploading') {
                        uploader.stop();
                    }
                });

                $info.on('click', '.retry', function () {
                    uploader.retry();
                });

                $info.on('click', '.ignore', function () {
                    // alert('todo');
                });

                $upload.addClass('state-' + state);
                updateTotalProgress();
            }

            this.modalobj.find('#upload').find('button.reset').off('click');
            this.modalobj.find('#upload').find('button.reset').on('click', function () {
                $this.reset_upload();
            });
            this.modalobj.find('#upload').find('button.btn-primary').off('click');
            this.modalobj.find('#upload').find('button.btn-primary').on('click', function () {
                $this.test('upload');
                if ($this.images.length > 0) {
                    if ($.isFunction($this.options.callback)) {
                        if ($this.options.multi) {
                            $this.options.callback($this.images);
                        } else {
                            $this.options.callback($this.images[0]);
                        }
                        $this.hide();
                    }
                } else {
                    alert('未选择任何文件.');
                }
            });
        },

        'sortObjectArrayByNameArray' : function(nameArray, objectArray){
            // 创建一个映射，将文件名映射到它们的索引位置
            const nameToIndexMap = new Map();
            nameArray.forEach((name, index) => {
                nameToIndexMap.set(name, index);
            });
            // 根据映射中的索引对对象数组进行排序
            objectArray.sort((a, b) => {
                return nameToIndexMap.get(a.name) - nameToIndexMap.get(b.name);
            });
            return objectArray;
        },

        'reset_upload': function () {

        },

        'template': function () {

            let template = {};

            template['upload'] =
                '<div role="tabpanel" class="tab-pane upload" id="upload">' +
                '   <div id="uploader" class="uploader">' +
                '        <div class="queueList">' +
                '             <div id="dndArea" class="placeholder">' +
                '                  <div id="filePicker"></div>' +
                '                  <p>或将文件拖到这里，单次最多可选 ' + ($this.options.multi ? 30 : 1) + '个文件</p>' +
                '             </div>' +
                '        </div>' +
                '        <div class="statusBar" style="line-height: 30px; margin-bottom: -15px;">' +
                '             <div class="progress">' +
                '                  <span class="text">0%</span>' +
                '                  <span class="percentage"></span>' +
                '             </div>' +
                '             <div class="info"></div>' +
                '             <div class="accept"></div>' +
                '             <div class="btns">' +
                ($this.options.multi ? '                    <div id="filePicker2" class="btn btn-primary" style="margin-top: 4px; color: white;"></div>' : '') +
                '                  <div class="uploadBtn btn btn-primary" style="margin-top: 4px;">确认使用</div>' +
                '                  <div class="modal-button-upload" style="float: right; margin-left: 5px; margin-right: -20px; display: none;">' +
                //'                     <button type="button" class="btn btn-default reset">清空</button>'+
                '                       <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                '                       <button type="button" class="btn btn-primary">确认</button>' +
                '                  </div>' +
                '             </div>' +
                '        </div>' +
                '   </div>' +
                '</div>';

            template['crawler'] =
                '<div role="tabpanel" class="tab-pane crawler" id="crawler">' +
                '   <div style="margin-top: 10px;">' +
                '        <form>' +
                '             <div class="form-group">' +
                '                  <div class="input-group">' +
                '                       <input type="url" class="form-control" id="crawlerUrl" placeholder="请输入网络文件地址">' +
                '                       <input type="hidden" value="" >' +
                '                       <span class="input-group-btn">' +
                '                            <button class="btn btn-default" type="button" id="btnFetch">提取</button>' +
                '                       </span>' +
                '                  </div>' +
                '                  <div class="crawler-img" style="background-image:url(\'../images/nopic.png\')">' +
                '                       <span class="crawler-img-sizeinfo"></span>' +
                '                  </div>' +
                '             </div>' +
                '        </form>' +
                '   </div>' +
                '   <div class="modal-footer" style="padding: 30px 0 0;">' +
                '        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                '        <button type="button" class="btn btn-primary">确认</button>' +
                '   </div>' +
                '</div>';
            ;

            template['browser'] =
                '<div role="tabpanel" class="tab-pane browser" id="browser" style="margin-top: 0;">' +
                '   <div class="breadcrumb" style="display: none;padding: 8px; background: #FFFFFF; margin-top: -10px; margin-bottom: 0px;">' +
                '   </div>' +
                '   <div class="clearfix file-browser">' +
                '   </div>' +
                '   <div class="modal-footer" style="background: #fff;display:flex;justify-content: center;align-items: center;">' +
                '        <div style="float: left;">' +
                '             <span class="browser-info"><span>' +
                '        </div>' +
                '        <div style="float: right;">' +
                '             <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                '             <button type="button" class="btn btn-primary" style="margin-left:15px;">确认</button>' +
                '        </div>' +
                '   </div>' +
                '</div>';

            template['remoteImage'] =
                '<div role="tabpanel" class="tab-pane remoteImage" id="remoteImage">' +
                '   <div class="row">' +
                '        <iframe width="0" height="0" id="fileUploader_remote_image_target" name="fileUploader_remote_image_target" style="display:none;"></iframe>' +
                '        <form class="form-horizontal" name="fileUploader_remote_image_form" id="fileUploader_remote_image_form" action="./index.php?c=utility&a=file&do=upload&type=image" enctype="multipart/form-data" method="post" target="fileUploader_remote_image_target">' +
                '             <div class="form-group">' +
                '                  <label class="col-xs-12 col-sm-2 control-label">上传图片</label>' +
                '                  <div class="col-sm-10">' +
                '                       <input type="file" name="file">' +
                '                  </div>' +
                '             </div>' +
                '             <div class="form-group">' +
                '                  <label class="col-xs-12 col-sm-2 control-label">资源类型</label>' +
                '                  <div class="col-sm-10">' +
                '                       <label class="radio-inline">' +
                '                            <input type="radio" name="mediatype" value="image" checked="checked">图片(小于1M)' +
                '                       </label>' +
                '                       <label class="radio-inline">' +
                '                            <input type="radio" name="mediatype" value="thumb">缩略图(小于64K)' +
                '                       </label>' +
                '                  </div>' +
                '             </div>' +
                '             <div class="form-group">' +
                '                  <div class="col-sm-10 col-sm-offset-2">' +
                '                       <div class="alert alert-warning" role="alert">注 : 上传图片类型仅限 JPG</div>' +
                '                  </div>' +
                '             </div>' +
                '        </form>' +
                '   </div>' +
                '   <div class="modal-footer" style="background: #fff;">' +
                '        <div style="float: left;">' +
                '             <span class="browser-info"><span>' +
                '        </div>' +
                '        <div style="float: right;">' +
                '             <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                '             <button type="button" class="btn btn-primary">确认</button>' +
                '        </div>' +
                '   </div>' +
                '</div>';

            template['remoteAudio'] =
                '<div role="tabpanel" class="tab-pane remoteAudio" id="remoteAudio">' +
                '   <div class="row">' +
                '        <iframe width="0" height="0" id="fileUploader_remote_audio_target" name="fileUploader_remote_audio_target" style="display:none;"></iframe>' +
                '        <form class="form-horizontal" id="fileUploader_remote_audio_form" name="fileUploader_remote_audio_form" action="./index.php?c=utility&a=file&do=upload&type=audio" enctype="multipart/form-data" method="post" target="fileUploader_remote_audio_target">' +
                '             <div class="form-group">' +
                '                  <label class="col-xs-12 col-sm-2 control-label">上传文件</label>' +
                '                  <div class="col-sm-10">' +
                '                       <input type="file" name="file">' +
                '                       <input type="hidden" name="mediatype" value="video">' +
                '                  </div>' +
                '             </div>' +
                '             <div class="form-group">' +
                '                  <div class="col-sm-10 col-sm-offset-2">' +
                '                       <div class="alert alert-warning" role="alert">注 : 上传媒体类型仅限 mp3/amr (小于 2M)、 mp4(小于 10M).</div>' +
                '                  </div>' +
                '             </div>' +
                '        </form>' +
                '   </div>' +
                '   <div class="modal-footer" style="background: #fff;">' +
                '        <div style="float: left;">' +
                '             <span class="browser-info"><span>' +
                '        </div>' +
                '        <div style="float: right;">' +
                '             <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                '             <button type="button" class="btn btn-primary">确认</button>' +
                '        </div>' +
                '   </div>' +
                '</div>';

            return template;
        }
    };

    return fileUploader;
});