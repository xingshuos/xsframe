define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.licenses/index' + location.search,
                    add_url: 'web.licenses/add',
                    edit_url: 'web.licenses/edit',
                    del_url: 'web.licenses/del',
                    multi_url: 'web.licenses/multi',
                    import_url: 'web.licenses/import',
                    table: 'propt_licenses',
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
                        { field: 'expiration_date', title: __('Expiration_date'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        { field: 'license_name', title: __('许可人名称'), operate: 'LIKE' },
                        { field: 'license_email', title: __('许可人邮箱'), operate: 'LIKE' },
                        { field: 'seats', title: __('总数'), operate: false },
                        { field: 'free_seat_count', title: __('可使用次数'), operate: false },
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
                url: 'web.licenses/recyclebin' + location.search,
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
                                    url: 'web.licenses/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.licenses/destroy',
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

            licenseseats: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.licenseseats/index',

                        table: 'tablealicenseseats',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.license_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.license_id = "=";  //group_id的操作方法的为 找到相等的


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
                var tablelicenseseats = $("#tablelicenseseats");
                tablelicenseseats.bootstrapTable({
                    url: 'web.licenseseats/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarlicenseseats',
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [
                            {
                                field: 'id', title: __('ID'), operate: false, formatter: function (value, row, index) {

                                    //示例：
                                    return '席位' + row.id;
                                }
                            },
                            {
                                field: 'assigned_to.name', title: __('分配给'), operate: false, formatter: function (value, row, index) {
                                    //value：intro字段的值
                                    //row：当前行所有字段的数据
                                    //index：当前行索引
                                    //示例：
                                    if (row.assigned_type) {
                                        return value + "  " + (row.assigned_type == 2 ? '<span class="label label-default">资产</span>' : '<span class="label label-default">用户</span>');
                                    } else {
                                        return '<span class="label label-success">未分配</span>';
                                    }

                                }
                            },

                            { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },

                            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                            {
                                field: 'operate',
                                width: "150px",
                                title: __('Operate'),
                                table: tablelicenseseats,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'licenseseatscheckin',
                                        title: __('归还'),
                                        classname: ' btn btn-xs btn-success btn-dialog',
                                        icon: 'fa fa-sign-in',
                                        visible: function (row) {
                                            return row.assigned_to_id
                                        },
                                        url: 'web.licenseseats/checkin'
                                    },
                                    {
                                        name: 'licenseseatscheckout',
                                        title: __('分配'),
                                        classname: ' btn btn-xs btn-warning btn-dialog',
                                        icon: 'fa fa-sign-out',
                                        visible: function (row) {
                                            return !row.assigned_to_id
                                        },
                                        url: 'web.licenseseats/checkout'
                                    },

                                ],
                                formatter: Table.api.formatter.operate
                            },
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablelicenseseats);
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
                            filter.target_type = 5; //将透传的参数 Config.group_id，追加到搜索条件中
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
        copy: function () {
            $("#c-status_id").data("eSelect", function () {
                //后续操作

                Fast.api.ajax({ url: 'web.statuslabels/item', data: { ids: $('#c-status_id').val() } }, (data, ret) => {
                    if (data.status == 1) {
                        $('#display_assigned_type').css('display', 'block');

                    } else {
                        $('#display_assigned_type').css('display', 'none');
                    }
                });

            });
            $(document).on("change", "#c-assigned_type", function () {

                var val = $("#c-assigned_type").val()
                $(".assigned_type").css('display', 'none');
                console.log($(".assigned_type"));
                if (val == 1) {
                    $('#user_assigned_type').css('display', 'block');
                } else if (val == 2) {
                    $('#assets_assigned_type').css('display', 'block');
                } else if (val == 3) {
                    $('#location_assigned_type').css('display', 'block');
                }
                //变更后的回调事件
            });
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
        }, detail: function () {
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
                    Fast.api.ajax({ url: 'web.licenses/del', data: { ids: id } }, (data, ret) => {
                        window.parent.location.href = 'index?ref=addtabs';
                    });
                });
            });
            $(document).on("click", ".btn-dialog-comfrime[data-id]", function () {
                var id = $(this).data('id');


                layer.confirm('确定归还所有？', {
                    btn: ['确定', '取消']
                }, function (index) {
                    Layer.close(index);
                    Fast.api.ajax({ url: 'web.licenses/checkinall', data: { ids: id } }, (data, ret) => {
                        window.location.reload();
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
        checkout: function () {

            $(document).on("change", "#c-assigned_type", function () {


                // 遍历所有元素并移除name属性


                var val = $("#c-assigned_type").val()
                $(".assigned_type").css('display', 'none');

                console.log($(".assigned_type"));
                if (val == 1) {
                    $('#user_assigned_type').css('display', 'block');
                } else if (val == 2) {
                    $('#assets_assigned_type').css('display', 'block');
                } else {
                    $('#location_assigned_type').css('display', 'block');
                }
                //变更后的回调事件
            });
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(ret);//这里是重点
            }, function (data, ret) {

            });
        },
        api: {
            formatter: {
                detail: function (value, row, index) {
                    //这里手动构造URL 
                    url = "propt/licenses/detail?ref=addtabs&ids=" + row.id;

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
