define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.suppliers/index' + location.search,
                    add_url: 'web.suppliers/add',
                    edit_url: 'web.suppliers/edit',
                    del_url: 'web.suppliers/del',
                    multi_url: 'web.suppliers/multi',
                    import_url: 'web.suppliers/import',
                    table: 'propt_suppliers',
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
                        { field: 'address2', title: __('Address2'), operate: 'LIKE' },
                        { field: 'city', title: __('City'), operate: 'LIKE' },
                        { field: 'phone', title: __('Phone'), operate: 'LIKE' },
                        { field: 'fax', title: __('Fax'), operate: 'LIKE' },
                        { field: 'email', title: __('Email'), operate: 'LIKE' },
                        { field: 'contact', title: __('Contact'), operate: 'LIKE' },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'url', title: __('Url'), operate: 'LIKE', formatter: Table.api.formatter.url },
                        { field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image },
                        { field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE' },
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
                url: 'web.suppliers/recyclebin' + location.search,
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
                                    url: 'web.suppliers/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.suppliers/destroy',
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
        detail: function () {
            // 初始化表格参数配置
            Table.api.init();

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


            Controller.api.bindevent();
        },
        table: {
            assets: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.assets/index' + location.search,

                        table: 'tableassets',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.supplier_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.supplier_id = "=";  //group_id的操作方法的为 找到相等的


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
                var table1 = $("#tableassets");
                table1.bootstrapTable({

                    url: 'web.assets/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarassets',
                    sortName: 'id',
                    search: true,
                    columns: [
                        [


                            { field: 'name', title: __('名称'), operate: 'LIKE' },
                            { field: 'asset_tag', title: __('标签'), operate: 'LIKE', formatter: Table.api.formatter.flag },
                            { field: 'serial', title: __('序列号'), operate: 'LIKE' },
                            { field: 'purchase_cost', title: __('采购花费'), operate: 'BETWEEN' },
                            { field: 'models.name', title: __('型号'), operate: 'LIKE' },
                            { field: 'locations.name', title: __('位置'), operate: 'LIKE' },
                            { field: 'operate', title: __('Operate'), table: table1, events: Table.api.events.operate, formatter: Table.api.formatter.operate }

                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            licenses: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.licenses/index' + location.search,

                        table: 'tablelicenses',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.supplier_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.supplier_id = "=";  //group_id的操作方法的为 找到相等的


                        }
                        params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                        params.op = JSON.stringify(op);
                        //如果希望忽略搜索栏搜索条件,可使用
                        //params.filter = JSON.stringify({url: 'login'});
                        //params.op = JSON.stringify({url: 'like'});
                        return params;

                    },
                });
                // 表格2
                var table2 = $("#tablelicenses");
                table2.bootstrapTable({
                    url: 'web.licenses/index' + location.search,
                    pk: 'id',
                    toolbar: '#toolbarlicenses',
                    sortName: 'id',
                    commonSearch: false,
                    search: true,
                    columns: [
                        [

                            { field: 'name', title: __('名称'), operate: 'LIKE' },
                            { field: 'seats', title: __('席位') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            { field: 'termination_date', title: __('授权截至日期'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },

                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            },
            accessories: function () {
                // 表格2
                Table.api.init({
                    extend: {
                        index_url: 'web.accessories/index' + location.search,

                        table: 'tableaccessories',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.supplier_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.supplier_id = "=";  //group_id的操作方法的为 找到相等的


                        }
                        params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                        params.op = JSON.stringify(op);
                        //如果希望忽略搜索栏搜索条件,可使用
                        //params.filter = JSON.stringify({url: 'login'});
                        //params.op = JSON.stringify({url: 'like'});
                        return params;

                    },
                });
                var table3 = $("#tableaccessories");
                table3.bootstrapTable({
                    url: 'web.accessorie/index' + location.search,
                    pk: "id",
                    toolbar: '#toolbaraccessories',
                    sortName: 'id',
                    commonSearch: false,
                    search: true,
                    columns: [
                        [
                            { field: 'name', title: __('Name'), operate: 'LIKE' },
                            { field: 'qty', title: __('数量') },
                            { field: 'purchase_date', title: __('采购日期'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            { field: 'categories.name', title: __('分类'), operate: 'LIKE' },
                            { field: 'manufacturers.name', title: __('制造商'), operate: 'LIKE' },

                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table3);
            },
            consumables: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.consumables/index' + location.search,

                        table: 'tableconsumables',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.supplier_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.supplier_id = "=";  //group_id的操作方法的为 找到相等的


                        }
                        params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                        params.op = JSON.stringify(op);
                        //如果希望忽略搜索栏搜索条件,可使用
                        //params.filter = JSON.stringify({url: 'login'});
                        //params.op = JSON.stringify({url: 'like'});
                        return params;

                    },
                });
                // 表格2
                var table4 = $("#tableconsumables");
                table4.bootstrapTable({
                    url: 'web.consumables/index' + location.search,
                    commonSearch: false,
                    toolbar: '#toolbarconsumables',
                    sortName: 'id',
                    search: true,
                    columns: [
                        [
                            { field: 'name', title: __('Name'), operate: 'LIKE' },
                            { field: 'qty', title: __('数量') },

                            { field: 'purchase_date', title: __('采购日期'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            { field: 'purchase_cost', title: __('采购花费'), operate: 'BETWEEN' },
                            { field: 'categories.name', title: __('分类'), operate: 'LIKE' },
                            { field: 'manufacturers.name', title: __('制造商'), operate: 'LIKE' },

                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table4);
            }
        },
        api: {
            formatter: {
                detail: function (value, row, index) {
                    //这里手动构造URL 
                    url = "propt/suppliers/detail?ref=addtabs&ids=" + row.id;

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
