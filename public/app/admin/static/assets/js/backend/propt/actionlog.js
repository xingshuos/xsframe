define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.actionlog/index' + location.search,
                    add_url: 'web.actionlog/add',
                    edit_url: 'web.actionlog/edit',
                    del_url: 'web.actionlog/del',
                    multi_url: 'web.actionlog/multi',
                    import_url: 'web.actionlog/import',
                    table: 'propt_action_logs',
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
                        {checkbox: true},
                        {field: 'action_title', title: __('Action_title'), operate: 'LIKE'},
                        {field: 'target_id', title: __('Target_id')},
                        {field: 'target_type', title: __('Target_type'), searchList: {"1":__('Target_type 1'),"2":__('Target_type 2'),"3":__('Target_type 3'),"4":__('Target_type 4'),"5":__('Target_type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
