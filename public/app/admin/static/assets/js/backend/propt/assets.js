define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $(".btn-add").data("area", ["90%", "90%"]);
            $(".btn-edit").data("area", ["90%", "90%"]);
            $(".btn-editone").data("area", ["90%", "90%"]);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'web.assets/index' + location.search,
                    add_url: 'web.assets/add',
                    edit_url: 'web.assets/edit',
                    del_url: 'web.assets/del',
                    multi_url: 'web.assets/multi',
                    import_url: 'web.assets/import',
                    table: 'propt_assets',
                },
                queryParams: function (params) { //自定义搜索条件

                    var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                    var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                    if (Fast.api.query("model_id")) {
                        filter.model_id = Fast.api.query("model_id");     //将透传的参数 Config.group_id，追加到搜索条件中
                        op.model_id = "=";  //group_id的操作方法的为 找到相等的

                    }
                    params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                    params.op = JSON.stringify(op);
                    //如果希望忽略搜索栏搜索条件,可使用
                    //params.filter = JSON.stringify({url: 'login'});
                    //params.op = JSON.stringify({url: 'like'});
                    return params;

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

                        { field: 'image', title: __('展示图片'), operate: false, formatter: Table.api.formatter.image },
                        { field: 'asset_tag', title: __('Asset_tag'), operate: 'LIKE', formatter: Table.api.formatter.flag },
                        { field: 'serial', title: __('Serial'), operate: 'LIKE' },
                        { field: 'categories.name', title: __('类别'), operate: false, formatter: Table.api.formatter.normal },
                        {
                            field: 'assetstatus.name', title: __('状态'), operate: false, formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
                                //示例：
                                return "<span style=color:" + row.assetstatus.color + ";>" + value + "</span>  " + (row.assigned_to_id ? '<span class="label label-default">已分配</span>' : '');
                            }
                        },
                        { field: 'assigned_to.name', title: __('借出至'), operate: false, formatter: Table.api.formatter.normal },
                        { field: 'purchase_cost', title: __('Purchase_cost'), operate: 'BETWEEN' },
                        { field: 'serial', title: __('当前价值'), operate: false, formatter: Table.api.formatter.normal },
                        { field: 'models.name', title: __('型号'), operate: 'LIKE' },
                        { field: 'location.name', title: __('Locations.name'), operate: false },
                        // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        {
                            field: 'operate',
                            width: "150px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'assigned',
                                    title: __('归还'),
                                    classname: ' btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-sign-in',
                                    visible: function (row) {

                                        return row.assigned_to_id;
                                    },
                                    url: 'web.assets/assigned'
                                },
                                {
                                    name: 'assigned',
                                    title: __('借出'),
                                    classname: ' btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-sign-out',
                                    visible: function (row) {
                                        return row.assetstatus.status == 1 && !row.assigned_to_id
                                    },
                                    url: 'web.assets/assigned'
                                }


                            ],
                            formatter: Table.api.formatter.operate
                        },
                    ]
                ]
            });
            table.on('post-body.bs.table', function () {
                // 修改添加窗口的大小

                // 修改编辑窗口的大小
                $(".btn-editone").data("area", ["90%", "90%"]);
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
                url: 'web.assets/recyclebin' + location.search,
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
                                    url: 'web.assets/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'web.assets/destroy',
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
            detail: function () {
            },
            licenses: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.licenseseats/index',

                        table: 'tablelicenses',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {
                            filter.assigned_type = '2';
                            filter.assigned_to_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.assigned_to_id = "=";  //group_id的操作方法的为 找到相等的
                            op.assigned_type = "=";  //group_id的操作方法的为 找到相等的

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
                var tablelicenses = $("#tablelicenses");
                tablelicenses.bootstrapTable({
                    url: 'web.licenseseats/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarlicenses',
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [

                            { field: 'licenses.name', title: __('名称'), operate: false },
                            { field: 'licenses.serial', title: __('产品序列号'), operate: false },
                            { field: 'licenses.expiration_date', title: __('到期日期'), operate: false, addclass: 'datetimerange', autocomplete: false },
                            {
                                field: 'operate',
                                width: "150px",
                                title: __('Operate'),
                                table: tablelicenses,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'licenseschcekin',
                                        title: __('归还'),
                                        classname: ' btn btn-xs btn-warning btn-dialog',
                                        icon: 'fa fa-sign-in',

                                        url: 'web.licenseseats/checkin'
                                    }



                                ],
                                formatter: Table.api.formatter.operate
                            },

                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablelicenses);
            },
            components: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.componentsseat/index',

                        table: 'tablecomponents',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {
                            filter.asset_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.asset_id = "=";  //group_id的操作方法的为 找到相等的
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
                var tablecomponents = $("#tablecomponents");
                tablecomponents.bootstrapTable({
                    url: 'web.componentsasset/index',
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbarcomponents',
                    commonSearch: false,
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [
                            { field: 'components.name', title: __('名称'), operate: false },
                            { field: 'assigned_qty', title: __('数量'), operate: false },
                            { field: 'components.purchase_cost', title: __('采购花费'), operate: false },
                            { field: 'components.serial', title: __('产品序列号'), operate: false },
                            { field: 'licenses.purchase_date', title: __('采购日期'), operate: false, addclass: 'datetimerange', autocomplete: false },
                            {
                                field: 'operate',
                                width: "150px",
                                title: __('Operate'),
                                table: tablecomponents,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'componentschcekin',
                                        title: __('归还'),
                                        classname: ' btn btn-xs btn-warning btn-dialog',
                                        icon: 'fa fa-sign-in',
                                        url: 'web.componentsasset/checkin'
                                    }



                                ],
                                formatter: Table.api.formatter.operate
                            },

                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablecomponents);
            },
            assets: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.assets/index',

                        table: 'tableassets',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {
                            filter.assigned_type = '2';
                            filter.assigned_to_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.assigned_to_id = "=";  //group_id的操作方法的为 找到相等的
                            op.assigned_type = "=";  //group_id的操作方法的为 找到相等的

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
                var tableassets = $("#tableassets");
                tableassets.bootstrapTable({
                    url: 'web.assets/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarassets',
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'name', title: __('Name'), operate: 'LIKE',  },
                            { field: 'image', title: __('展示图片'), operate: false, formatter: Table.api.formatter.image },
                            { field: 'asset_tag', title: __('Asset_tag'), operate: 'LIKE', formatter: Table.api.formatter.flag },
                            { field: 'serial', title: __('Serial'), operate: 'LIKE' },
                            { field: 'categories.name', title: __('类别'), operate: false, formatter: Table.api.formatter.normal },
                            {
                                field: 'assetstatus.name', title: __('状态'), operate: false, formatter: function (value, row, index) {
                                    //value：intro字段的值
                                    //row：当前行所有字段的数据
                                    //index：当前行索引
                                    //示例：
                                    return value + "  " + (row.assigned_to_id ? '<span class="label label-default">已分配</span>' : '');
                                }
                            },
                            { field: 'assigned_to.name', title: __('借出至'), operate: false, formatter: Table.api.formatter.normal },
                            { field: 'purchase_cost', title: __('Purchase_cost'), operate: 'BETWEEN' },
                            { field: 'serial', title: __('当前价值'), operate: false, formatter: Table.api.formatter.normal },
                            { field: 'models.name', title: __('型号'), operate: 'LIKE' },
                            { field: 'location.name', title: __('Locations.name'), operate: false },
                            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                            {
                                field: 'operate',
                                width: "150px",
                                title: __('Operate'),
                                table: tableassets,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'assigned',
                                        title: __('归还'),
                                        classname: ' btn btn-xs btn-warning btn-dialog',
                                        icon: 'fa fa-sign-in',
                                        visible: function (row) {

                                            return row.assigned_to_id;
                                        },
                                        url: 'web.assets/assigned'
                                    },
                                    {
                                        name: 'assigned',
                                        title: __('借出'),
                                        classname: ' btn btn-xs btn-danger btn-dialog',
                                        icon: 'fa fa-sign-out',
                                        visible: function (row) {
                                            return row.assetstatus.status == 1 && !row.assigned_to_id
                                        },
                                        url: 'web.assets/assigned'
                                    }


                                ],
                                formatter: Table.api.formatter.operate
                            },
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tableassets);
            },
            maintenances: function () {
                Table.api.init({
                    extend: {
                        index_url: 'web.asset_maintenances/index',
                        del_url: 'web.asset_maintenances/del',
                        table: 'tablemaintenances',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {

                            filter.asset_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
                            op.asset_id = "=";  //group_id的操作方法的为 找到相等的


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
                var tablemaintenances = $("#tablemaintenances");
                tablemaintenances.bootstrapTable({
                    url: 'web.asset_maintenances/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarmaintenances',
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
                            { field: 'location.name', title: __('位置'), operate: false },
                            //1=维护,2=维修,3=升级,4=PAT测试,5=校准,6=软件支持,7=硬件支持,8=配置更改
                            { field: 'asset_maintenance_type', title: __('维修类型'), searchList: { "1": __('维护'), "2": __('维修 2'), "3": __('升级'), "4": __('PAT测试'), "5": __('校准'), "6": __('软件支持'), "7": __('硬件支持'), "8": __('配置更改') }, formatter: Table.api.formatter.normal },
                            { field: 'title', title: __('Title'), operate: 'LIKE' },
                            { field: 'start_date', title: __('开始日期'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            { field: 'completion_date', title: __('完成日期'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            { field: 'warranty_data', title: __('保修'), searchList: { "1": __('是'), "0": __('否') }, formatter: Table.api.formatter.normal },
                            { field: 'cost', title: __('花费'), operate: 'BETWEEN' },
                            { field: 'admin.nickname', title: __('创建人'), operate: 'BETWEEN' },
                            { field: 'operate', title: __('Operate'), table: tablemaintenances, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablemaintenances);
            },
            file: function () {
                Table.api.init({
                    extend: {
                        index_url: 'general/attachment/index',
                        add_url: 'general/attachment/add',
                        del_url: 'general/attachment/del',

                        table: 'tablefile',
                    },
                    queryParams: function (params) { //自定义搜索条件

                        var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                        var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
                        if (Fast.api.query("ids")) {
                            filter.extparam = 'propt_file_assetid_' + Fast.api.query("ids");

                            op.extparam = "=";  //group_id的操作方法的为 找到相等的


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
                var tablefile = $("#tablefile");
                tablefile.bootstrapTable({
                    url: 'general/attachment/index',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    toolbar: '#toolbarfile',
                    fixedColumns: true,

                    fixedRightNumber: 1,
                    columns: [
                        [
                            { field: 'state', checkbox: true },
                            { field: 'id', title: __('Id') },
                            { field: 'category', title: __('文件类别'), operate: 'in', formatter: Table.api.formatter.label, searchList: Config.categoryList },
                            { field: 'admin_id', title: __('上传用户'), visible: false, addClass: "selectpage", extend: "data-source='auth/admin/index' data-field='nickname'" },
                            { field: 'user_id', title: __('User_id'), visible: false, addClass: "selectpage", extend: "data-source='user/user/index' data-field='nickname'" },
                            { field: 'preview', title: __('预览'), formatter: Controller.api.formatter.thumb, operate: false },
                            { field: 'url', title: __('Url'), formatter: Controller.api.formatter.url, visible: false },
                            { field: 'filename', title: __('文件名'), sortable: true, formatter: Controller.api.formatter.filename, operate: 'like' },
                            {
                                field: 'filesize', title: __('文件大小'), operate: 'BETWEEN', sortable: true, formatter: function (value, row, index) {
                                    var size = parseFloat(value);
                                    var i = Math.floor(Math.log(size) / Math.log(1024));
                                    return (size / Math.pow(1024, i)).toFixed(i < 2 ? 0 : 2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
                                }
                            },
                            { field: 'imagewidth', title: __('宽度'), sortable: true },
                            { field: 'imageheight', title: __('高度'), sortable: true },
                            { field: 'imagetype', title: __('图片类型'), sortable: true, formatter: Table.api.formatter.search, operate: 'like' },
                            { field: 'storage', title: __('存储引擎'), formatter: Table.api.formatter.search, operate: 'like' },
                            { field: 'mimetype', title: __('Mime类型'), formatter: Controller.api.formatter.mimetype },
                            {
                                field: 'createtime',
                                title: __('创建日期'),
                                formatter: Table.api.formatter.datetime,
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                sortable: true,
                                width: 150
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: tablefile,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(tablefile);
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
                            filter.target_type = 2; //将透传的参数 Config.group_id，追加到搜索条件中
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
                    Fast.api.ajax({ url: 'web.assets/del', data: { ids: id } }, (data, ret) => {
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

        add: function () {
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
            Controller.api.bindevent();
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

        },
        addfile: function () {

            //上传完成后刷新父窗口
            $(".faupload").data("upload-complete", function (files) {
                setTimeout(function () {
                    window.parent.$(".btn-refresh").trigger("click");
                }, 100);
            });
            // 获取上传类别
            $("#faupload-third,#faupload-third-chunking").data("category", function (file) {
                return $("#category-third").val();
            });
            // 获取上传类别
            $("#faupload-local,#faupload-local-chunking").data("category", function (file) {
                return $("#category-local").val();
            });
            Controller.api.bindevent();
        },
        assigned: function () {
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
                var elements = $(".c-assigned_type");

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
        audit: function () {
            Form.api.bindevent($("form[role=form]"), function (data, ret) {

                Fast.api.close(ret);//这里是重点 
            }, function (data, ret) {

            });

        },
        api: {
            formatter: {
                thumb: function (value, row, index) {
                    var html = '';
                    if (row.file_type.indexOf("img") > -1) {
                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + row.fullurl + '" alt="" style="max-height:60px;max-width:120px"></a>';
                    } else {
                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + Fast.api.fixurl("ajax/icon") + "?suffix=" + row.file_type + '" alt="" style="max-height:90px;max-width:120px"></a>';
                    }
                    return '<div style="width:120px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + html + '</div>';
                },
                detail: function (value, row, index) {
                    //这里手动构造URL 
                    url = "propt/assets/detail?ref=addtabs&ids=" + row.id;

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
