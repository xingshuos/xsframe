define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {

      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'web.my/index' + location.search,

          table: 'propt_user_myindex',
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
        commonSearch: false,
        search: false,
        fixedRightNumber: 1,
        columns: [
          [
            { checkbox: true },
            { field: 'name', title: __('Name'), operate: false, formatter: Controller.api.formatter.detail },
            { field: 'num', title: __('型号/序列号'), operate: false },
            { field: 'type_text', title: __('资源类型'), operate: false },

          ]
        ]
      });


      // 为表格绑定事件
      Table.api.bindevent(table);
    },














    api: {
      formatter: {
        detail: function (value, row, index) {
          if (row.type == "assets") {
            url = "propt/userrequest/assetsdetail?ref=addtabs&ids=" + row.id;
          } else if (row.type == "license") {
            url = "propt/userrequest/licensesdetail?ref=addtabs&ids=" + row.id;
          } else if (row.type == "accessories") {
            url = "propt/userrequest/accessoriedetail?ref=addtabs&ids=" + row.id;
          } else if (row.type == "consumables") {
            url = "propt/userrequest/consumablesdetail?ref=addtabs&ids=" + row.id;
          }
          this.url = url;
          return Table.api.formatter.addtabs.call(this, value, row, index);
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
