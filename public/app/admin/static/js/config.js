let version = +new Date();
let myConfig = {
    baseUrl: '/app/admin/static/',
    path: '/app/admin/static/',
    paths: {
        'jquery': 'components/jquery/jquery-1.11.1.min',
        'jquery.form': 'components/jquery/jquery.form',
        'jquery.gcjs': 'components/jquery/jquery.gcjs',
        'jquery.validate': 'validate.min',
        'jquery.nestable': 'components/jquery/nestable/jquery.nestable',
        'jquery.qrcode': 'components/jquery/jquery.qrcode.min',

        'bootstrap': 'components/bootstrap/bootstrap.min',
        'bootstrap.suggest': 'components/bootstrap/bootstrap-suggest.min',
        "filestyle": "components/bootstrap/bootstrap-filestyle.min",
        "bootstrap.switch": "components/switch/bootstrap-switch.min",

        'bootbox': 'components/bootbox/bootbox.min',
        'sweet': 'components/sweetalert/sweetalert.min',
        'select2': 'components/select2/select2.min',
        'jquery.confirm': 'components/jquery/confirm/jquery-confirm',
        "jquery.jplayer": "components/jplayer/jquery.jplayer.min",
        'jquery.contextMenu': 'components/jquery/contextMenu/jquery.contextMenu',
        'switchery': 'components/switchery/switchery',
        'echarts': 'components/echarts/echarts-all',
        'echarts.min': 'components/echarts/echarts.min',
        'toast': 'components/jquery/toastr.min',
        'clipboard': 'components/clipboard/clipboard.min',
        'tpl': 'components/tmodjs/tmodjs',
        'datetimepicker': 'components/datetimepicker/jquery.datetimepicker',
        'daterangepicker': 'components/daterangepicker/daterangepicker',
        'clockpicker': "components/clockpicker/clockpicker.min",
        'moment': 'components/daterangepicker/moment',
        'tooltipbox': 'components/tooltipbox/tooltipbox',
        'tip': 'components/tip/tip',
        'district': "components/district/district",

        'ueditor': 'components/ueditor/ueditor.all.min',

        // angular
        "angular": "components/angular/angular.min",
        "angular.sanitize": "components/angular/angular-sanitize.min",
        "angular.hotkeys": "components/angular/angular.hotkeys",

        // we7
        "loadjs": "components/load/loadjs",
        "loadcss": "components/load/loadcss.min",
        'fontawesome': "components/fontawesome/fontawesome",
        'emoji': "components/emoji/emoji",
        'colorpicker': "components/colorpicker/spectrum",
        'swiper': 'components/swiper/swiper.min',

        "jquery.ui": "components/jquery/jquery-ui-1.10.3.min",

        'webuploader': 'components/webuploader/webuploader.min',
        // "fileUploader": "components/fileuploader/fileuploader",
        "fileUploader": "components/fileuploader/fileuploader_new.min",

        'biz': "js/web/biz",
        'form': "js/web/form",
        'funbar': "js/web/funbar",
        'init': "js/web/init",
        'table': "js/web/table",
        'util': "js/web/util",
    },
    shim: {
        ueditor: {
            deps: ["/app/admin/static/components/ueditor/third-party/zeroclipboard/ZeroClipboard.min.js", "/app/admin/static/components/ueditor/ueditor.config.js"],
            exports: "UE",
            init: function (ZeroClipboard) {
                //导出到全局变量，供ueditor使用
                window.ZeroClipboard = ZeroClipboard;
            }
        },
        biz: {
            exports: "js/web/biz"
        },
        form: {
            exports: "js/web/form"
        },
        funbar: {
            exports: "js/web/funbar"
        },
        init: {
            exports: "js/web/init"
        },
        table: {
            exports: "js/web/table"
        },
        util: {
            exports: "js/web/util"
        },
        tip: {
            deps: ["loadcss!components/tip/tip.css", "loadcss!components/jquery/confirm/jquery-confirm.css"]
        },
        'jquery-confirm': {
            deps: ["loadcss!components/jquery/confirm/jquery-confirm.css"]
        },
        daterangepicker: {
            deps: ["moment", "loadcss!components/daterangepicker/daterangepicker.css"]
        },
        datetimepicker: {
            deps: ["loadcss!components/datetimepicker/jquery.datetimepicker.css"]
        },
        switchery: {
            deps: ["loadcss!components/switchery/switchery.css"]
        },
        colorpicker: {
            deps: ["loadcss!components/colorpicker/spectrum.css"]
        },
        "jquery.ui": {
            exports: "$"
        },
        "jquery.caret": {
            exports: "$"
        },
        "jquery.nestable": {
            exports: "$"
        },
        bootstrap: {
            exports: "$"
        },
        "bootstrap.switch": {
            deps: ["loadcss!components/switch/bootstrap-switch.min.css"],
            exports: "$"
        },
        clockpicker: {
            exports: "$",
            deps: ["loadcss!components/clockpicker/clockpicker.min.css"]
        },
        district: {
            exports: "$"
        },
        emoji: {
            deps: ["loadcss!components/emoji/emotions.css"]
        },
        fontawesome: {
            deps: ["loadcss!components/fontawesome/style.css"]
        },
        swiper: {
            deps: ["loadcss!components/swiper/swiper.min.css"]
        },
        webuploader: {
            deps: ['loadcss!components/webuploader/webuploader.css', 'loadcss!components/webuploader/style.css']
        },
        select2: {
            deps: ['loadcss!components/select2/select2.css', 'loadcss!components/select2/select2-bootstrap.css']
        },

        angular: {
            exports: "angular",
            deps: ["jquery"]
        },
        "angular.sanitize": {
            exports: "angular",
            deps: ["angular"]
        },
        "angular.hotkeys": {
            exports: "angular",
            deps: ["angular"]
        },
    },
};

require.config(myConfig);