define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.checkoutreq/index' + location.search,



                    table: 'propt_checkout_requests',
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
                        { field: 'item.name', title: "申请名称", operate: false },
                        { field: 'requestable_type_text', title: "申请类型", operate: false },
                        { field: 'qty', title: __('申请数量'), operate: false },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'canceltime', title: __('Canceltime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'agreetime', title: __('Agreetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'rejecttime', title: __('Rejecttime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operator.nickname', title: __('操作人'), operate: 'LIKE',
                        },
                        { field: 'status', title: __('Status'), searchList: { "-2": __('已取消'), "-1": __('已拒绝'), "0": __('初始提交'), "1": __('已同意') }, formatter: Table.api.formatter.status },
                        { field: 'admin.nickname', title: __('申请人'), operate: 'LIKE' },
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'ajaxagree',
                                    title: __('同意'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check-circle-o',
                                    confirm: '同意会将该资源直接借给申请者，确认吗？',
                                    url: 'web.checkoutreq/agree',
                                    visible: function (row) {
                                        return row.status === '0'
                                    },
                                    success: function (data, ret) {

                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                        $("#toolbar .btn-refresh").click();
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'ajaxcancel',
                                    title: __('取消'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-times-circle',
                                    confirm: '确认取消？',
                                    url: 'web.checkoutreq/cancel',
                                    visible: function (row) {
                                        return row.status === '0'
                                    },
                                    success: function (data, ret) {

                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                        $("#toolbar .btn-refresh").click();
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'ajaxcancel',
                                    title: __('拒绝'),
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    icon: 'fa fa-undo',
                                    confirm: '确认拒绝？',
                                    url: 'web.checkoutreq/reject',
                                    visible: function (row) {
                                        return row.status === '0'
                                    },
                                    success: function (data, ret) {

                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                        $("#toolbar .btn-refresh").click();
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },


                            ],
                            formatter: Table.api.formatter.operate
                        },
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
                url: 'web.checkoutreq/recyclebin' + location.search,
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
                                    url: 'web.checkoutreq/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.checkoutreq/destroy',
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
