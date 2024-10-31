define(["js/web/biz"], function (biz) {
    model = {};
    model.url = "";
    model.callback = '';

    model.init = function (url, callback, ids = []) {
        model.url = url;
        model.ele = $("#ajaxModal");
        model.$search = model.ele.find(".search");

        model.getpage(1);
        model.callback = callback;

        model.listen();

        $(document).on("click", ".pager-nav", function () {
            let page = Number($(this).attr("page"));
            model.jumpnow(page);
        });
    };

    model.listen = function() {
        $(document).keypress(function(e) {
            if (e.which === 13 && model.ele !== undefined) {
                model.jumpnow(1);
                return false
            }
        });
    };

    model.jumpnow = function(page) {
        model.keyword = model.$search.val();
        model.getpage(page, model.keyword)
    };

    model.getpage = function (page, keywords) {
        if (!page > 0) {
            page = 1
        }
        if (keywords === undefined) {
            model.$search.val("")
        }
        let condition = model.ele.find("where").text();
        console.log(model.url)
        $.ajax({
            url: model.url,
            type: "post",
            data: {
                data: {},
                page: page,
                keywords: keywords,
                condition: condition,
            },
            success: function (htm) {
                model.ele.find(".content").empty().html(htm);
            },
        });
    };
    return model;
});