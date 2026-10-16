define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.asset_maintenances/index' + location.search,
                    add_url: 'web.asset_maintenances/add',
                    edit_url: 'web.asset_maintenances/edit',
                    del_url: 'web.asset_maintenances/del',
                    multi_url: 'web.asset_maintenances/multi',
                    import_url: 'web.asset_maintenances/import',
                    table: 'propt_asset_maintenances',
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
                        { field: 'assets.name', title: __('资产名称') },
                        { field: 'assets.asset_tag', title: __('资产标签') },
                        { field: 'assets.serial', title: __('序列号') },
                        {
                            field: 'assetstatus.name', title: __('状态'), operate: false, formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
                                //示例：
                                return value + "  " + (row.assigned_to_id ? '<span class="label label-default">已分配</span>' : '');
                            }
                        },
                        { field: 'locations.name', title: __('位置'), operate: false },
                        { field: 'asset_maintenance_type', title: __('Asset_maintenance_type'), searchList: { "1": __('Asset_maintenance_type 1'), "2": __('Asset_maintenance_type 2'), "3": __('Asset_maintenance_type 3'), "4": __('Asset_maintenance_type 4'), "5": __('Asset_maintenance_type 5'), "6": __('Asset_maintenance_type 6'), "7": __('Asset_maintenance_type 7'), "8": __('Asset_maintenance_type 8') }, formatter: Table.api.formatter.normal },
                        { field: 'title', title: __('维修名称'), operate: 'LIKE' },
                        { field: 'start_date', title: __('Start_date'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        { field: 'completion_date', title: __('Completion_date'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        { field: 'warranty_data', title: __('Warranty_data'), searchList: { "1": __('Warranty_data 1'), "0": __('Warranty_data 0') }, formatter: Table.api.formatter.normal },
                        { field: 'cost', title: __('Cost'), operate: 'BETWEEN' },
                        { field: 'admin.nickname', title: __('创建人'), operate: 'LIKE' },
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
                url: 'web.asset_maintenances/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'title', title: __('Title'), align: 'left' },
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
                                    url: 'web.asset_maintenances/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.asset_maintenances/destroy',
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

            if (Fast.api.query("ids")) {
                $('#c-asset_id').val(Fast.api.query("ids"));
            }

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
