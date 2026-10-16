define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    assetsindex: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/assetsindex' + location.search,

          table: 'propt_user_assets',
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


      var assetstable = $("#assetstable");

      // 初始化表格
      assetstable.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        fixedColumns: true,
        fixedRightNumber: 1,
        columns: [
          [
            { checkbox: true },
            { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.assetsdetail },
            { field: 'image', title: __('展示图片'), operate: false, formatter: Table.api.formatter.image },
            { field: 'asset_tag', title: __('资产标签'), operate: 'LIKE', formatter: Table.api.formatter.flag },
            { field: 'serial', title: __('流水号'), operate: 'LIKE' },
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
            {
              field: 'checkout.status', title: __('申请状态'), operate: false, formatter: function (value, row, index) {
                //value：intro字段的值
                //row：当前行所有字段的数据
                //index：当前行索引
                //示例：


                if (value !== undefined) {
                  return '<span class="label label-default">已申请</span>';
                } else {
                  return '<span class="label label-default">待申请</span>';
                }

              }
            },

            { field: 'purchase_cost', title: __('采购花费'), operate: false },
            { field: 'models.name', title: __('型号'), operate: 'LIKE' },
            { field: 'locations.name', title: __('地点'), operate: false },
            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: assetstable,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: 'assetsrquest',
                  title: __('立即申请'),
                  classname: ' btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-sign-in',
                  visible: function (row) {

                    return !row.assigned_to_id & !row.checkout.id;
                  },
                  url: 'web.userrequest/assetsrquest'
                }



              ],
              formatter: Table.api.formatter.operate
            },
          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(assetstable);
    },
    assetsrquest: function () {
      Controller.api.bindevent();
    },

    assetsdetail: function () {
      Controller.api.bindevent();
    },
    licensesindex: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/licensesindex' + location.search,

          table: 'propt_user_assets',
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
            { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.licensesdetail },

            { field: 'serial', title: __('序列号'), operate: 'LIKE' },
            { field: 'expiration_date', title: __('过期时间'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            { field: 'license_name', title: __('许可人名称'), operate: false },
            { field: 'license_email', title: __('许可人邮件'), operate: false },
            { field: 'seats', title: __('总数'), operate: false },
            { field: 'free_seat_count', title: __('可申请次数'), operate: false },


            { field: 'purchase_cost', title: __('采购花费'), operate: false },
            { field: 'models.name', title: __('型号'), operate: false },
            { field: 'locations.name', title: __('地点'), operate: false },
            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: table,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: 'licensesrquest',
                  title: __('立即申请'),
                  classname: ' btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-sign-in',
                  visible: function (row) {

                    return row.free_seat_count > 0;
                  },
                  url: 'web.userrequest/licensesrquest'
                }



              ],
              formatter: Table.api.formatter.operate
            },
          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    licensesrquest: function () {
      Controller.api.bindevent();
    },

    licensesdetail: function () {
      Controller.api.bindevent();
    },
    accessorieindex: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/accessorieindex' + location.search,

          table: 'propt_user_assets',
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
            { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.accessoriedetail },
            { field: 'categories.name', title: __('分类'), operate: 'LIKE' },
            { field: 'model_number', title: __('型号'), operate: 'LIKE' },
            { field: 'location.name', title: __('位置'), operate: 'LIKE' },
            { field: 'qty', title: __('总量'), operate: false },
            { field: 'count', title: __('已借出'), operate: false },

            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
            { field: 'purchase_date', title: __('采购日期'), operate: false, addclass: 'datetimerange', autocomplete: false },

            { field: 'suppliers.name', title: __('供应商'), operate: 'LIKE' },
            { field: 'manufacturers.name', title: __('制造商'), operate: 'LIKE' },
            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: table,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: ' accessorierquest',
                  title: __('立即申请'),
                  classname: ' btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-sign-in',
                  visible: function (row) {

                    return (row.qty - row.count) > 0;
                  },
                  url: 'web.userrequest/accessorierquest'
                }



              ],
              formatter: Table.api.formatter.operate
            },
          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    accessorierquest: function () {
      Controller.api.bindevent();
    },

    accessoriedetail: function () {
      Controller.api.bindevent();
    },

    combinationindex: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/combinationindex' + location.search,

          table: 'propt_combinationindex',
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
            { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.combinationdetail },
            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },

            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: table,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: ' accessorierquest',
                  title: __('立即申请'),
                  classname: ' btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-sign-in',
                  visible: function (row) {

                    return true;
                  },
                  url: 'web.userrequest/combinationrquest'
                }



              ],
              formatter: Table.api.formatter.operate
            },
          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    combinationrquest: function () {
      Controller.api.bindevent();
    },

    combinationdetail: function () {
      Table.api.init({
        extend: {
          index_url: 'web.combinationitem/index',

          multi_url: 'web.combinationitem/multi',
          table: 'tableacombinationitem',
        },
        queryParams: function (params) { //自定义搜索条件

          var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
          var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件
          if (Fast.api.query("ids")) {

            filter.combination_id = Fast.api.query("ids");     //将透传的参数 Config.group_id，追加到搜索条件中
            op.combination_id = "=";  //group_id的操作方法的为 找到相等的


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
      var tablecombinationitem = $("#tablecombinationitem");
      tablecombinationitem.bootstrapTable({
        url: 'web.combinationitem/index',
        pk: 'id',
        sortName: 'id',

        toolbar: '#toolbarcombinationitem',
        fixedColumns: false,


        columns: [
          [

            {
              field: 'item.name', title: __('子项名称'), operate: false,
            },
            {
              field: 'item_type_text', title: __('子项类型'), operate: false,
            },

            {
              field: 'statusswitch', title: __('状态'), searchList: { "0": __('关闭'), "1": __('开启') }, class: "disabled", table: tablecombinationitem, formatter: function (value, row, params) {
                return value == 0 ? '已关闭' : '已开启';
              }
            },
            { field: 'qty', title: __('数量') },
            { field: 'admin.nickname', title: __('创建人'), operate: 'LIKE' },


            { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },

            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }


          ]
        ]
      });

      // 为表格1绑定事件
      Table.api.bindevent(tablecombinationitem);
      Controller.api.bindevent();
    },

    consumablesindex: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/consumablesindex' + location.search,

          table: 'propt_user_ consumables',
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
            { field: 'name', title: __('Name'), operate: 'LIKE', formatter: Controller.api.formatter.consumablesdetail },
            { field: 'categories.name', title: __('分类'), operate: 'LIKE' },
            { field: 'model_number', title: __('型号'), operate: 'LIKE' },
            { field: 'location.name', title: __('位置'), operate: 'LIKE' },
            { field: 'qty', title: __('总量'), operate: false },
            { field: 'count', title: __('已借出'), operate: false },

            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
            { field: 'purchase_date', title: __('采购时间'), operate: false, addclass: 'datetimerange', autocomplete: false },

            { field: 'suppliers.name', title: __('供应商'), operate: 'LIKE' },
            { field: 'manufacturers.name', title: __('制造商'), operate: 'LIKE' },
            // { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: table,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: 'consumablesrquest',
                  title: __('立即申请'),
                  classname: ' btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-sign-in',
                  visible: function (row) {

                    return (row.qty - row.count) > 0;
                  },
                  url: 'web.userrequest/consumablesrquest'
                }



              ],
              formatter: Table.api.formatter.operate
            },
          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    consumablesrquest: function () {
      Controller.api.bindevent();
    },

    accessoriedetail: function () {
      Controller.api.bindevent();
    },
    myrequest: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.userrequest/myrequest' + location.search,

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
            { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
            { field: 'updatetime', title: __('Updatetime'), operate: false, addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
            {
              field: 'canceltime', title: __('取消时间'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
            },
            {
              field: 'agreetime', title: __('同意时间'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
            },
            {
              field: 'rejecttime', title: __('拒绝时间'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime
            },
            {
              field: 'operator.nickname', title: __('操作人'), operate: false

            },
            { field: 'status', title: __('Status'), searchList: { "-2": __('已取消'), "-1": __('已拒绝'), "0": __('初始提交'), "1": __('已同意') }, formatter: Table.api.formatter.status },
            { field: 'admin.nickname', title: __('申请人'), operate: false },
            {
              field: 'operate',
              width: "150px",
              title: __('Operate'),
              table: table,
              events: Table.api.events.operate,
              buttons: [
                {
                  name: 'ajaxcancel',
                  title: __('取消'),
                  classname: 'btn btn-xs btn-danger btn-ajax',
                  icon: 'fa fa-times-circle',
                  confirm: '确认取消？',
                  url: 'web.userrequest/cancel',
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
        assetsdetail: function (value, row, index) {
          //这里手动构造URL 
          url = "propt/userrequest/assetsdetail?ref=addtabs&ids=" + row.id;

          //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
          //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

          //方式二,直接调用Table.api.formatter.addtabs
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
        },
        licensesdetail: function (value, row, index) {
          //这里手动构造URL 
          url = "propt/userrequest/licensesdetail?ref=addtabs&ids=" + row.id;

          //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
          //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

          //方式二,直接调用Table.api.formatter.addtabs
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
        },
        accessoriedetail: function (value, row, index) {
          //这里手动构造URL 
          url = "propt/userrequest/accessoriedetail?ref=addtabs&ids=" + row.id;

          //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
          //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

          //方式二,直接调用Table.api.formatter.addtabs
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
        },
        consumablesdetail: function (value, row, index) {
          //这里手动构造URL 
          url = "propt/userrequest/consumablesdetail?ref=addtabs&ids=" + row.id;

          //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
          //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

          //方式二,直接调用Table.api.formatter.addtabs
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
        },
        combinationdetail: function (value, row, index) {
          //这里手动构造URL 
          url = "propt/userrequest/combinationdetail?ref=addtabs&ids=" + row.id;

          //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
          //return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

          //方式二,直接调用Table.api.formatter.addtabs
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
        },
      },
      bindevent: function () {
        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
