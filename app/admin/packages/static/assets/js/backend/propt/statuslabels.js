define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'jquerycolorpicker'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.statuslabels/index' + location.search,
                    add_url: 'web.statuslabels/add',
                    edit_url: 'web.statuslabels/edit',
                    del_url: 'web.statuslabels/del',
                    multi_url: 'web.statuslabels/multi',
                    import_url: 'web.statuslabels/import',
                    table: 'propt_status_labels',
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
                        {
                            field: 'name', title: __('状态名称'), operate: 'LIKE', formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
                                //示例：
                                return "<span style=color:" + row.color + ">" + value + " </span> ";
                            }
                        },
                        { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'updatetime', title: __('Updatetime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'status', title: __('Status'), searchList: { "1": __('Status 1'), "2": __('Status 2'), "3": __('Status 3'), "4": __('Status 4') }, formatter: Table.api.formatter.status },
                        {
                            field: 'color', title: __('Color'), operate: false, formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
                                //示例：
                                return "<span style=color:" + value + ">" + value + " </span> ";
                            }
                        },
                        // { field: 'default_switch', title: __('Default_switch'), operate: false, searchList: { "1": __('Yes'), "0": __('No') }, table: table, formatter: Table.api.formatter.toggle },
                        { field: 'admin.nickname', title: __('创建人'), operate: false, operate: false },
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
                url: 'web.statuslabels/recyclebin' + location.search,
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
                                    url: 'web.statuslabels/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.statuslabels/destroy',
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

        add: function () {
            $('.colorpicker').colorpicker({
                // 初始化打开的时候把默认的颜色渲染上去，否则显示的弹窗选择器颜色不对
                color: function () {

                    var color = $("#c-color").val();

                    return color;
                }
            }, function (event, obj) {
                // 选择颜色后的回调
                $("#c-color").css('color', '#' + obj.hex);
                $("#c-color").val('#' + obj.hex);


            }, function (event) {
                // 选择后 关闭选择颜色窗口
                $("#c-color").css('color', 'inherit');


            });
            Controller.api.bindevent();
        },
        edit: function () {
            $("#c-color").css('color', $("#c-color").val());
            $('.colorpicker').colorpicker({
                // 初始化打开的时候把默认的颜色渲染上去，否则显示的弹窗选择器颜色不对
                color: function () {

                    var color = $("#c-color").val();

                    return color;
                }
            }, function (event, obj) {
                // 选择颜色后的回调
                $("#c-color").css('color', '#' + obj.hex);
                $("#c-color").val('#' + obj.hex);


            }, function (event) {
                // 选择后 关闭选择颜色窗口
                $("#c-color").css('color', 'inherit');


            });
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
