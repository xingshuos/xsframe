require.config({
    baseUrl: '/app/store/static/',
    paths: {
        'jquery': 'components/jquery/jquery-1.11.1.min',
        'jquery.qrcode': 'components/jquery/jquery.qrcode.min',
        'jquery.gcjs': 'components/jquery/jquery.gcjs',
        'jquery.confirm': 'components/jquery/confirm/jquery-confirm',

        'bootstrap': 'components/bootstrap/bootstrap.min',

        "loadjs": "components/load/loadjs",
        "loadcss": "components/load/loadcss.min",

        "swiper": "components/swiper/swiper.min",
        "videojs": "components/videojs/video.min",

        'tip': 'components/tip/tip',
        'sweetalert': 'components/sweetalert2/sweetalert2.min',

        'vue': 'components/vue/vue.min',
        'axios': 'components/vue/axios.min',
        'h7.axios': 'components/vue/h7.axios',
    },
    // exports 解决命名冲突问题
    // deps 依赖顺序
    shim: {
        "bootstrap": {
            exports: "$",
            deps: [
                // "loadcss!components/bootstrap/bootstrap.min.css",
            ]
        },
        "h7.axios": {
            deps: ["axios"],
        },
        "jquery.gcjs": {
            exports: "$",
            deps: ['jquery']
        },
        "jquery.qrcode": {
            exports: "$"
        },
        "jquery.confirm": {
            deps: [
                "loadcss!components/jquery/confirm/jquery-confirm.css",
            ]
        },
        swiper: {
            deps: [
                "loadcss!components/swiper/swiper.min.css",
            ]
        },
        common: {
            deps: [
                "loadcss!fonts/font.css",
                // "loadcss!//at.alicdn.com/t/font_2651925_zabmgy37k78.css",
            ]
        },
        videojs: {
            deps: [
                "loadcss!components/videojs/video.min.css",
                "loadcss!components/videojs/video.css",
            ]
        },
        tip: {
            deps: ["loadcss!components/tip/tip.css", "loadcss!components/jquery/confirm/jquery-confirm.css"]
        },
        "sweetalert": {
            deps: [
                "loadcss!components/sweetalert2/sweetalert2.css",
            ]
        },
    }
});
