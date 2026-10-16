define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.licenseseats/index' + location.search,
                    add_url: 'web.licenseseats/add',
                    edit_url: 'web.licenseseats/edit',
                    del_url: 'web.licenseseats/del',
                    multi_url: 'web.licenseseats/multi',
                    import_url: 'web.licenseseats/import',
                    table: 'propt_license_seats',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {
                            field: 'id', title: __('ID'), operate: false, formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
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
                                return value + "  " + (row.assigned_type == 2 ? '<span class="label label-default">资产</span>' : '<span class="label label-default">用户</span>');
                            }
                        },

                        { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },

                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
                url: 'web.licenseseats/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
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
                                    url: 'web.licenseseats/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.licenseseats/destroy',
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
        checkin: function () {
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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
