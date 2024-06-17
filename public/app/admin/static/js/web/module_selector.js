define(["js/web/biz"], function(biz) {
    model = {};
    model.multi = false;
    model.callback = '';
    model.ele = {};
    model.listenPool = [1];
    model.selectedPool = {};
    model.merchid = 0;
    model.no_merchid = 0;
    model.url = function(routes) {
        // console.log('url',url);
        return "../" + routes;
    };
    model.post_url = model.url('util/moduleSelector', model.merchid);
    model.open = function(callback, type, merchid, multi, api_url, selected_ids, no_merchid, platform) {
        model.merchid = merchid;
        model.no_merchid = no_merchid;
        model.post_url = model.url('util/moduleSelector', merchid);
        model.platform = platform;


        // let condition = model.ele.find("where").text();
        console.log('model post_url',model.post_url);
        console.log('model api_url',api_url);


        let elename = 'moduleSelector';
        if (api_url && api_url.length > 0) {
            model.post_url = api_url
        }

        if (multi) {
            model.multi = true
        } else {
            model.multi = false
        }

        console.log('model.open',typeof(model.callback));
        
        model.callback = callback;
        if (typeof(model.callback) === 'string') {
            let url = model.url('util/moduleSelectorJs');
            model.name = elename;

            $('#goods-selector-modal').remove();
            $('body').append('<div id="goods-selector-modal"></div>');

            $.ajax({
                url: url,
                async: false,
                success: function(htm) {
                    $('#goods-selector-modal').empty().html(htm);
                    return false;
                }
            });

            console.log('model.open',"#goods_selector_" + model.name);

            model.ele = $("#goods_selector_" + model.name);
            model.mask = model.ele.find(".modal");
            model.close1 = model.ele.find(".modal").find(".close");
            model.modal = model.ele.find(".modal").find(".modal-dialog");
            model.close2 = model.ele.find(".modal").find(".modal-footer").find(".btn");
            model.s2id_autogen1 = model.ele.find("#s2id_autogen1");
            model.select2drop = model.ele.find("#select2-drop");
            model.select2result = model.ele.find(".select2-result");
            model.select2chosen = model.ele.find("#select2-chosen-2");
            model.$search = model.ele.find(".search");
            model.$goodsgroup = 0;
            model.getpage(1);
            model.mask.css("display", "block");
            setTimeout("model.mask.addClass('in')");
            let json = model.ele.find("textarea[name=" + model.name + "]").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            model.listen();
            if (selected_ids && selected_ids.length > 0) {
                $.each(selected_ids, function(i, v) {
                    model.selectedPool[v] = {
                        id: v
                    };
                    model.selectStatus()
                })
            }
        }
    };
    
    model.init = function() {
        let textareas = $(".goods-selector-textarea");
        $.each(textareas, function(i, v) {
            let obj = $(v);
            let json = obj.html();
            model.name = obj.attr("name");
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                obj.html("")
            }
            model.put_selected_to_list()
        });
        let gs = $('.goods-selector');
        $.each(gs, function(index, v) {
            let op_switch = $(this).data('switch');
            let hrefs = $(v).find('.goods-selector-op');
            $.each(hrefs, function(index, hrefobj) {
                let href = $(hrefobj).attr('href');
                href += ('&nooption=' + (op_switch === '0' ? '1' : '0'));
                $(hrefobj).attr('href', href)
            })
        });
        $(document).on("click", ".goods-selector-cancel", function() {
            model.name = $(this).parent().parent().parent().data("name");
            let id = $(this).data("id");
            model.del(id);
            model.ele = $("#goods_selector_" + model.name);
            let json = model.ele.find("textarea[name=" + model.name + "]").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            delete model.selectedPool[id];
            model.saveSelected();
            model.name = model.ele = undefined
        });
        $(document).on("click", ".goods-selector-op", function() {
            model.name = $(this).parent().parent().parent().data("name");
            let name = model.name;
            model.ele = $("#goods_selector_" + model.name);
            model.option_switch = $(model.ele).attr("data-switch");
            let href = $(this).attr('href');
            href = href.replace('nooption=undefined', 'nooption=' + model.option_switch === 0 ? 1 : 0);
            href += '&nooption=' + (model.option_switch === 0 ? '1' : '0');
            $(this).attr('href', href);
            let goodsid = $(this).data("id");
            let thismodal = $("#goods-selector-opmodal-" + goodsid);
            let json = $("#goods_selector_" + name).find(".goods-selector-textarea").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            let goods = model.selectedPool[goodsid]
        });
        $(document).on("click", ".goods-selector-op-option", function() {
            model.ele = $("#goods_selector_" + model.name);
            let goodsid = $(this).data("id");
            let checked = $("#goods-selector-opmodal-" + goodsid).find(".option-item:checked");
            let options = {};
            $.each(checked, function(i, v) {
                let thisobj = $(v);
                let input = thisobj.parent().parent().find("input").not(".option-item");
                let column = {};
                $.each(input, function(j, k) {
                    column[$(k).attr("name")] = $(k).val()
                });
                let obj;
                obj = {
                    id: thisobj.val(),
                    marketprice: thisobj.data("price"),
                    title: thisobj.parent().parent().find("td:nth-child(1)").text(),
                    stock: thisobj.parent().parent().find("td:nth-child(3)").text(),
                    column: column
                };
                options[thisobj.val()] = obj
            });
            let json = model.ele.find("textarea[name=" + model.name + "]").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            model.selectedPool[goodsid]["options"] = options;
            model.saveSelected();
            model.name = undefined
        });
        $(document).on("click", ".goods-selector-op-goods", function() {
            model.ele = $("#goods_selector_" + model.name);
            let goodsid = $(this).data("id");
            let json = model.ele.find("textarea[name=" + model.name + "]").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            let column = {};
            let input = $("#goods-selector-opmodal-" + goodsid).find("input");
            $.each(input, function(j, k) {
                column[$(k).attr("name")] = $(k).val()
            });
            model.selectedPool[goodsid]["column"] = column;
            model.saveSelected()
        });
        model.p = $(".goods-selector-open");
        model.p.click(function() {
            let url = $(this).data('url');
            let type = $(this).data('type');
            if (type === 'creditshop') {
                model.post_url += '&creditshop=1'
            } else if (type === 'group') {
                model.post_url += '&group=1'
            }
            let elename = $(this).attr("data-name");
            model.callback = $(this).data('callback');
            if (model.callback) {
                let url = model.url('util/moduleSelectorJs', merchid);
                if (model.loaded) {} else {
                    $('body').append('<div id="goods-selector-modal"></div>');
                    $.ajax({
                        url: url,
                        async: false,
                        success: function(htm) {
                            $('#goods-selector-modal').empty().html(htm);
                            model.loaded = 1;
                            return false;
                        }
                    })
                }
            }
            model.name = elename;
            model.ele = $("#goods_selector_" + model.name);
            model.mask = model.ele.find(".modal");
            model.close1 = model.ele.find(".modal").find(".close");
            model.modal = model.ele.find(".modal").find(".modal-dialog");
            model.close2 = model.ele.find(".modal").find(".modal-footer").find(".btn");
            model.s2id_autogen1 = model.ele.find("#s2id_autogen1");
            model.select2drop = model.ele.find("#select2-drop");
            model.select2result = model.ele.find(".select2-result");
            model.select2chosen = model.ele.find("#select2-chosen-2");
            model.$search = model.ele.find(".search");
            model.$goodsgroup = 0;
            model.getpage(1);
            model.mask.css("display", "block");
            setTimeout("model.mask.addClass('in')");
            let json = model.ele.find("textarea[name=" + model.name + "]").html();
            if (model.isJSON(json)) {
                model.selectedPool = JSON.parse(json)
            } else {
                model.selectedPool = {};
                model.ele.find("textarea[name=" + model.name + "]").html("")
            }
            if (model.listenPool.indexOf(model.name) < 0) {
                model.listen()
            }
        })
    };
    model.listen = function() {
        $(document).keypress(function(e) {
            if (e.which === 13 && model.ele !== undefined) {
                model.jumpnow(1);
                return false
            }
        });
        model.listenPool.push(model.name);
        $(model.modal).on("click", ".pager-nav", function() {
            let num = Number($(this).attr("page"));
            model.jumpnow(num)
        });
        $(model.modal).on("change", ".page-raduis", function() {
            let num = Number($(this).val());
            $(this).parent().next("li").find("a").attr("page", num)
        });
        $(model.modal).on("click", ".selectit", function() {
            let goods = {};
            goods = $(this).data("json");
            if (model.callback) {
                goods.act = 1;
                eval(model.callback + "(goods)");
                if (!model.multi) {
                    $('.modal.in').find('.close').trigger('click')
                } else {
                    $(this).removeClass("selectit").removeClass("label-primary").addClass("cancelit").addClass("label-danger").text("取消")
                }
            }
            model.selectedPool[goods.id] = goods;
            $(this).removeClass("selectit").removeClass("label-primary").addClass("cancelit").addClass("label-danger").text("取消");
        });
        $(model.modal).on("click", ".cancelit", function() {
            let goods = {};
            goods = $(this).data("json");
            delete model.selectedPool[goods.id];
            if (model.multi) {
                goods.act = 0;
                eval(model.callback + "(goods)")
            }
            $(this).removeClass("cancelit").removeClass("label-danger").addClass("selectit").addClass("label-primary").text("选择");
            model.del(goods.id)
        });
        $(model.mask).click(function() {
            model.mask.removeClass("in");
            setTimeout("model.mask.css('display','none');", 150);
            model.s2id_autogen1.css("border", "1px solid #efefef");
            model.select2drop.hide();
            model.saveSelected()
        });
        $(model.modal).click(function(event) {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.select2drop.hide();
            model.mask.css("display", "block");
            model.s2id_autogen1.css("border", "1px solid #efefef")
        });
        $(model.close1).click(function(event) {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.mask.removeClass("in");
            setTimeout("model.mask.css('display','none');", 150);
            model.saveSelected()
        });
        $(model.close2).click(function(event) {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.mask.removeClass("in");
            setTimeout("model.mask.css('display','none');", 150);
            model.saveSelected()
        });
        $(model.s2id_autogen1).click(function() {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.select2drop.show();
            $(this).css("border", "1px solid #44abf7 ");
            $(this).css("border-bottom", "0")
        });
        $(model.select2drop).click(function() {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.select2drop.show()
        });
        model.ele.find('.fenlei').find('select').change(function() {
            let e = window.event || event;
            if (e.stopPropagation) {
                e.stopPropagation()
            } else {
                e.cancelBubble = true
            }
            model.select2drop.hide();
            model.goodsgroup = $(this).val();
            model.select2chosen.html($(this).find("div").html());
            model.s2id_autogen1.css("border", "1px solid #efefef")
        });
        $(model.select2result).hover(function() {
            $(this).addClass("select2-highlighted")
        }, function() {
            $(this).removeClass("select2-highlighted")
        })
    };
    model.put_selected_to_list = function() {
        $.each(model.selectedPool, function(i, v) {
            model.put(v.id)
        });
        model.selectedPool = {};
        model.name = undefined
    };
    model.put = function(id) {
        model.option_switch = $(model.ele).data("switch");
        let url = "./index.php?c=site&a=entry&m=ewei_shopv2&do=web&r=util.goods_selector.op&id=" + id + "&column=" + encodeURI($("#goods-selected-list-" + model.name).attr("data-column"));
        if (model.option_switch === 0) {
            url += "&nooption=1"
        }
        let set_color = "btn-danger";
        let htm = '<tr id="goods-selected-goods' + id + '">                <td><img src="' + model.selectedPool[id].thumb + '" style="width: 40px;height: 40px;border: solid #ccc 1px"></td>                <td><p class="title">' + model.selectedPool[id].title + '</p>                <p class="text text-danger">¥' + model.selectedPool[id].marketprice + "</p></td>                <td>" + '</td>                <td><a data-toggle="ajaxModal" href="' + url + '" class="btn ' + set_color + ' btn-sm goods-selector-op" data-id="' + model.selectedPool[id].id + '">商品设置</a>                <a class="btn btn-default btn-sm goods-selector-cancel" data-id="' + id + '">取消</a></td>                </tr>';
        $("#goods-selected-list-" + model.name).append(htm)
    };
    model.del = function(id) {
        $("#goods-selected-list-" + model.name).find("#goods-selected-goods" + id).remove()
    };
    model.selectStatus = function() {
        let selectBtn = model.ele.find(".selectit");
        $.each(selectBtn, function(i, v) {
            let obj = $(v);
            let thisid = obj.data("id");
            if (model.selectedPool[thisid] !== undefined) {
                obj.removeClass("selectit").removeClass("label-primary").addClass("cancelit").addClass("label-danger").text("取消")
            }
        })
    };
    model.isJSON = function(str) {
        if (typeof str === "string") {
            try {
                let obj = JSON.parse(str);
                if (str.indexOf("{") > -1) {
                    return true
                } else {
                    return false
                }
            } catch (e) {
                console.log("隐藏域数据格式不能解析，已清空，请重新选择：" + e);
                return false
            }
        }
        return false
    };
    model.saveSelected = function() {
        model.ele.find("textarea[name=" + model.name + "]").html(JSON.stringify(model.selectedPool));
        model.selectedPool = {}
    };
    model.jumpnow = function(page) {
        model.keyword = model.$search.val();
        model.getpage(page, model.keyword, model.goodsgroup)
    };
    model.getpage = function(page, keywords, goodsgroup) {
        if (!page > 0) {
            page = 1
        }
        if (keywords === undefined) {
            model.$search.val("")
        }
        let condition = model.ele.find("where").text();
        $.ajax({
            url: model.post_url,
            type: "post",
            data: {
                data: {},
                page: page,
                keywords: keywords,
                goodsgroup: goodsgroup,
                condition: condition,
                no_merchid: model.no_merchid,
                platform: model.platform,
            },
            success: function(htm) {
                model.ele.find(".content").empty().html(htm);
                model.selectStatus()
            },
        })
    };
    return model
});