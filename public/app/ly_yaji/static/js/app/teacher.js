require(['jquery.lazyload'], function () {
    $("img").lazyload({effect: "fadeIn", threshold: 180, failure_limit: 50});
});

require(['jquery', 'swiper'], function ($, Swiper) {
    new Swiper('.swiper', {
        autoplay: true, // 自动播放
        speed: 1000, // 滑动速度
        grabCursor: true, // 鼠标小手
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        on: {
            init: function () {

            },
            slideChangeTransitionEnd: function () {
                let activeIndex = this.activeIndex;
                let url = $(this.slides[activeIndex]).children()[0].getAttribute('src');
                $(".bk").css('background-image', "url(" + url + ")");
            }
        }
    });
});