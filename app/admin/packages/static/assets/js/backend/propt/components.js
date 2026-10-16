define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.components/index' + location.search,
                    add_url: 'web.components/add',
                    edit_url: 'web.components/edit',
                    del_url: 'web.components/del',
                    multi_url: 'web.components/multi',
                    import_url: 'web.components/import',
                    table: 'propt_components',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        { checkbox: true },
                        { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.detail },
                        { field: 'requestableswitch', title: __('用户可请求'), searchList: { "0": __('关'), "1": __('开') }, table: table, formatter: Table.api.formatter.toggle },

                        { field: 'serial', title: __('Serial'), operate: 'LIKE' },
                        { field: 'categories.name', title: __('分类名称'), operate: 'LIKE' },

                        { field: 'qty', title: __('Qty'), operate: false },
                        { field: 'count', title: __('已借出'), operate: false },
                        { field: 'min_amt', title: __('最小数量'), operate: false },
                        { field: 'location.name', title: __('位置'), operate: 'LIKE' },

                        { field: 'purchase_date', title: __('Purchase_date'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },


                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'web.components/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'name', title: __('Name'), align: 'left' },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'web.components/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.components/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        table: {
            info: function () {
            },

            componentsassets: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.componentsasset/index',

                        table: 'tableacomponentsassets',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.component_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.component_id = "=";  //group_id的操作方法的为 找到相等的


                        }
                        params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                        params.op = JSON.stringify(op);
                        //如果希望忽略搜索栏搜索条件,可使用
                        //params.filter = JSON.stringify({url: 'login'});
                        //params.op = JSON.stringify({url: 'like'});
                        return params;

                    },
                });
                // 表格1
                var tablecomponentsassets = $("#tablecomponentsassets");
                tablecomponentsassets.bootstrapTable({
                    url: 'web.componentsasset/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarcomponentsassets',
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'components.name', title: __('组件名'), operate: false },
                            { field: 'assets.name', title: __('资产名'), operate: false },
                            { field: 'assigned_qty', title: __('数量'), operate: false },
                            { field: 'notes', title: __('备注'), operate: false },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                            {
                                field: 'operate',
                                width: "150px",
                                title: __('Operate'),
                                table: tablecomponentsassets,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'componentsassetscheckin',
                                        title: __('归还'),
                                        classname: ' btn btn-xs btn-warning btn-dialog',
                                        icon: 'fa fa-sign-in',

                                        url: 'web.componentsasset/checkin'
                                    },

                                ],
                                formatter: Table.api.formatter.operate
                            },
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablecomponentsassets);
            },

            history: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.actionlog/index',

                        table: 'tablehistory',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {}; //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.target_id = Fast.api.query("ids"); //将透传的参数 Config.group_id，追加到搜索条件中
                            op.target_id = "="; //group_id的操作方法的为 找到相等的
                            filter.target_type = 4; //将透传的参数 Config.group_id，追加到搜索条件中
                            op.target_type = "="; //group_id的操作方法的为 找到相等的

                        }
                        params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                        params.op = JSON.stringify(op);
                        //如果希望忽略搜索栏搜索条件,可使用
                        //params.filter = JSON.stringify({url: 'login'});
                        //params.op = JSON.stringify({url: 'like'});
                        return params;

                    },
                });
                // 表格1
                var tablehistory = $("#tablehistory");
                tablehistory.bootstrapTable({
                    url: 'web.actionlog/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarhistory',

                    columns: [
                        [
                            { field: 'action_title', title: __('操作标题'), operate: false },
                            { field: 'content', title: __('操作内容'), operate: false },
                            { field: 'createtime', title: __('操作时间'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            { field: 'admin.nickname', title: __('操作人'), operate: false },
                            { field: 'url', title: __('文件'), formatter: Table.api.formatter.image, operate: false },
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablehistory);
            },
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(ret);//这里是重点
            }, function (data, ret) {

            });
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(ret);//这里是重点
            }, function (data, ret) {

            });
        },
        copy: function () {

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(ret);//这里是重点
            }, function (data, ret) {

            });
        }, checkout: function () {


            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(ret);//这里是重点
            }, function (data, ret) {

            });
        },
        detail: function () {

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.length > 0) {

                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $(document).on("click", ".close-diable[data-id]", function () {
                var id = $(this).data('id');


                layer.confirm('确定删除？', {
                    btn: ['确定', '取消']
                }, function (index) {
                    Layer.close(index);
                    Fast.api.ajax({ url: 'web.components/del', data: { ids: id } }, (data, ret) => {
                        window.parent.location.href = 'index?ref=addtabs';
                    });
                });
            });

            $(document).on("click", ".btn-dialog-self[data-id]", function () {
                var type = $(this).data('type');
                Fast.api.open($(this).data('url'), __($(this).data('title')), {
                    area: ['70%', '80%'],
                    callback: function (value) {
                        console.log(value);
                        if (type == 'list') {
                            window.parent.location.href = 'detail?ref=addtabs&ids=' + value.data.id;
                        } else {
                            window.location.reload();
                        }



                    }
                });
            });

            Controller.api.bindevent();
        },
        api: {
            formatter: {
                detail: function (value, row, index) {
                    //这里手动构造URL 
                    url = "propt/components/detail?ref=addtabs&ids=" + row.id;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

                    //方式二,直接调用Table.api.formatter.addtabs
                    this.url = url;
                    return Table.api.formatter.addtabs.call(this, value, row, index);
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
