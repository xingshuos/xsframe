<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

use xsframe\util\StringUtil;

if (!function_exists('tpl_selector')) {
    function tpl_selector($name, $options = [])
    {
        $options['multi']       = intval($options['multi']);
        $options['buttontext']  = isset($options['buttontext']) ? $options['buttontext'] : '请选择';
        $options['items']       = isset($options['items']) && $options['items'] ? $options['items'] : [];
        $options['readonly']    = isset($options['readonly']) ? $options['readonly'] : true;
        $options['callback']    = isset($options['callback']) ? $options['callback'] : '';
        $options['key']         = isset($options['key']) ? $options['key'] : 'id';
        $options['text']        = isset($options['text']) ? $options['text'] : 'title';
        $options['thumb']       = isset($options['thumb']) ? $options['thumb'] : 'thumb';
        $options['preview']     = isset($options['preview']) ? $options['preview'] : true;
        $options['type']        = isset($options['type']) ? $options['type'] : 'image';
        $options['input']       = isset($options['input']) ? $options['input'] : true;
        $options['required']    = isset($options['required']) ? $options['required'] : false;
        $options['nokeywords']  = isset($options['nokeywords']) ? $options['nokeywords'] : 0;
        $options['placeholder'] = isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词';
        $options['autosearch']  = isset($options['autosearch']) ? $options['autosearch'] : 0;

        if (empty($options['items'])) {
            $options['items'] = [];
        } else {
            if (!is_array2($options['items'])) {
                $options['items'] = [$options['items']];
            }
        }

        $options['name'] = $name;
        $titles          = '';

        foreach ($options['items'] as $item) {
            if ($item[$options['valuetext']]) {
                $titles .= $item[$options['valuetext']];
            } else {
                $titles .= $item[$options['text']];
            }

            if (1 < count($options['items'])) {
                $titles .= '; ';
            }
        }

        $options['value'] = isset($options['value']) ? $options['value'] : $titles;
        $readonly         = $options['readonly'] ? 'readonly' : '';
        $required         = $options['required'] ? ' data-rule-required="true"' : '';
        $callback         = !empty($options['callback']) ? ', ' . $options['callback'] : '';
        $id               = $options['multi'] ? $name . '[]' : $name;
        $html             = '<div id=\'' . $name . '_selector\' class=\'selector\'
                     data-type="' . $options['type'] . '"
                     data-key="' . $options['key'] . '"
                     data-text="' . $options['text'] . '"
                     data-thumb="' . $options['thumb'] . '"
                     data-multi="' . $options['multi'] . '"
                     data-callback="' . $options['callback'] . '"
                     data-url="' . $options['url'] . '"
                     data-nokeywords="' . $options['nokeywords'] . '"
                  data-autosearch="' . $options['autosearch'] . '"

                 >';
        if ($options['text'] == 'nickname' && $options['value'] != '') {
            $optionsValue = &$options['value'];
            $optionsValue = preg_replace('#[\'|"]#', '', $options['value']);
            unset($optionsValue);
        }

        if ($options['input']) {
            $html .= '<div class=\'input-group\'>' . ('<input type=\'text\' id=\'' . $name . '_text\' name=\'' . $name . '_text\'  value=\'' . $options['value'] . '\' class=\'form-control text\'  ' . $readonly . '  ' . $required . '/>') . '<div class=\'input-group-btn\'>';
        }

        $html .= '<button class=\'btn btn-primary\' type=\'button\' onclick=\'biz.selector.select(' . json_encode($options, JSON_HEX_APOS) . (');\'>' . $options['buttontext'] . '</button>');

        if ($options['input']) {
            $html .= '</div>';
            $html .= '</div>';
        }

        $show = $options['preview'] ? '' : ' style=\'display:none\'';

        if ($options['type'] == 'image') {
            $html .= '<div class=\'input-group multi-img-details container\' ' . $show . '>';
        } else {
            $html .= '<div class=\'input-group multi-audio-details container\' ' . $show . '>';
        }

        foreach ($options['items'] as $item) {
            if ($options['type'] == 'image') {
                $html .= '<div class=\'multi-item\' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' data-name=\'' . $name . '\'>
                                      <img class=\'img-responsive img-thumbnail\' src=\'' . tomedia($item[$options['thumb']]) . ('\' onerror=\'this.src="/app/admin/static/images/nopic.png"\' style=\'width:100px;height:100px;\'>
                                      <div class=\'img-nickname\'>' . $item[$options['text']] . '</div>
                                     <input type=\'hidden\' value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'>
                                     <em onclick=\'biz.selector.remove(this,"' . $name . '")\'  class=\'close\'>×</em>
                            <div style=\'clear:both;\'></div>
                         </div>');
            } else {
                $html .= '<div class=\'multi-audio-item \' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' >
                       <div class=\'input-group\'>
                       <input type=\'text\' class=\'form-control img-textname\' readonly=\'\' value=\'' . $item[$options['text']] . '\'>
                       <input type=\'hidden\'  value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'>
                       <div class=\'input-group-btn\'><button class=\'btn btn-default\' onclick=\'biz.selector.remove(this,"' . $name . '")\' type=\'button\'><i class=\'fa fa-remove\'></i></button>
                       </div></div></div>';
            }
        }

        $html .= '</div></div>';
        return $html;
    }
}

if (!function_exists('webUrl')) {
    function webUrl($url = null, $params = [], $full = true, $suffix = true)
    {
        if (!StringUtil::strexists($url, 'web.') && app('http')->getName() != 'admin') {
            $url = "web." . $url;
        }

        if (empty($params['page']) && StringUtil::strexists($url, '/edit') && !empty($_GET['page']) ) {
            $params['page'] = $_GET['page'];
        }

        if (!empty($_GET['i'])) {
            $params['i'] = $_GET['i'];
        }

        $url = url($url, array_filter($params), $suffix, $full);

        // 负载均衡下域名协议会被强制转成http协议，所以这里需要转成https的方式 start
        if (StringUtil::strexists($_SERVER['HTTP_REFERER'], 'https')) {
            $url = str_replace("http:", "https:", $url);
        }
        // end
        return str_replace(".html.html", ".html", is_object($url) ? strval($url) : $url);
    }
}

if (!function_exists('pagination')) {
    function pagination($total, $pageIndex, $pageSize = 16)
    {
        $pageNum = 1;
        if ($pageSize < $total) {
            $pageNum = intval($total / $pageSize) + (($total % $pageSize) > 0 ? 1 : 0);
        }

        $pager            = [];
        $pager['page']    = $pageIndex;
        $pager['pageNum'] = $pageNum;
        $pager['total']   = $total;

        return $pager;
    }
}

// 后台生成分页列表
if (!function_exists('pagination2')) {
    function pagination2($total, $pageIndex, $pageSize = 15, $url = '', $params = [])
    {
        $context = ['isajax' => false, 'before' => 2, 'after' => 2, 'ajaxcallback' => '', 'callbackfuncname' => ''];
        $context = array_merge($context, $params);

        $scriptName = getScriptName();
        $pdata      = ['tcount' => 0, 'tpage' => 0, 'cindex' => 0, 'findex' => 0, 'pindex' => 0, 'nindex' => 0, 'lindex' => 0, 'options' => ''];

        if (empty($context['before'])) {
            $context['before'] = 2;
        }
        if (empty($context['after'])) {
            $context['after'] = 3;
        }

        if ($context['ajaxcallback']) {
            $context['isajax'] = true;
        }

        if ($context['callbackfuncname']) {
            $callbackfunc = $context['callbackfuncname'];
        }

        $html = '<div><ul class="pagination pagination-centered"><li><span class="nobg">共' . $total . '条记录</span></li></ul>';

        if (!empty($total)) {
            $pdata['tcount'] = $total;
            $pdata['tpage']  = empty($pageSize) || $pageSize < 0 ? 1 : ceil($total / $pageSize);

            if ($pdata['tpage'] <= 1) {
                return '';
            }

            if (1 < $pdata['tpage']) {
                $html            .= '<ul class="pagination pagination-centered">';
                $cindex          = $pageIndex;
                $cindex          = min($cindex, $pdata['tpage']);
                $cindex          = max($cindex, 1);
                $pdata['cindex'] = $cindex;
                $pdata['findex'] = 1;
                $pdata['pindex'] = 1 < $cindex ? $cindex - 1 : 1;
                $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
                $pdata['lindex'] = $pdata['tpage'];
                if ($context['isajax']) {
                    if (empty($url)) {
                        $url = $scriptName . '?' . http_build_query($_GET);
                    }
                    $pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['findex'] . '\', this);"' : '');
                    $pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['pindex'] . '\', this);"' : '');
                    $pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['nindex'] . '\', this);"' : '');
                    $pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['lindex'] . '\', this);"' : '');
                } else {
                    if ($url) {
                        $pdata['jump'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
                        $pdata['faa']  = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
                        $pdata['paa']  = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
                        $pdata['naa']  = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
                        $pdata['laa']  = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
                    } else {
                        $jump_get         = $_GET;
                        $jump_get['page'] = '';
                        $pdata['jump']    = 'href="' . ($scriptName ?? '') . '?' . http_build_query($jump_get) . $pdata['cindex'] . '" data-href="' . ($scriptName ?? '') . '?' . http_build_query($jump_get) . '"';
                        $_GET['page']     = $pdata['findex'];
                        $pdata['faa']     = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page']     = $pdata['pindex'];
                        $pdata['paa']     = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page']     = $pdata['nindex'];
                        $pdata['naa']     = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page']     = $pdata['lindex'];
                        $pdata['laa']     = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                    }
                }

                if (1 < $pdata['cindex']) {
                    $html .= '<li><a ' . ($pdata['faa'] ?? '') . ' class="pager-nav">首页' . $scriptName . '</a></li>';
                    $html .= '<li><a ' . ($pdata['paa'] ?? '') . ' class="pager-nav">&laquo;上一页</a></li>';
                }

                if (!$context['before'] && $context['before'] != 0) {
                    $context['before'] = 5;
                }

                if (!$context['after'] && $context['after'] != 0) {
                    $context['after'] = 4;
                }

                if ($context['after'] != 0 && $context['before'] != 0) {
                    $range          = [];
                    $range['start'] = max(1, $pdata['cindex'] - $context['before']);
                    $range['end']   = min($pdata['tpage'], $pdata['cindex'] + $context['after']);

                    if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
                        $range['end']   = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
                        $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
                    }

                    $i = $range['start'];

                    while ($i <= $range['end']) {
                        if ($context['isajax']) {
                            $aa = 'href="javascript:;" page="' . $i . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $i . '\', this);"' : '');
                        } else {
                            if ($url) {
                                $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                            } else {
                                $_GET['page'] = $i;
                                $aa           = 'href="?' . http_build_query($_GET) . '"';
                            }
                        }

                        if (!empty($context['isajax'])) {
                            $html .= ($i == $pdata['cindex'] ? '<li class="active">' : '<li>') . "<a {$aa}>" . $i . '</a></li>';
                            ++$i;
                        } else {
                            $html .= $i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : '<li><a ' . $aa . '>' . $i . '</a></li>';
                            ++$i;
                        }
                    }
                }

                if ($pdata['cindex'] < $pdata['tpage']) {
                    $html .= '<li><a ' . ($pdata['naa'] ?? '') . ' class="pager-nav">下一页&raquo;</a></li>';
                    $html .= '<li><a ' . ($pdata['laa'] ?? '') . ' class="pager-nav">尾页</a></li>';
                }

                $html .= '</ul>';

                if (5 < $pdata['tpage']) {
                    $html .= '<ul class="pagination pagination-centered">';
                    $html .= '<li><span class=\'input\' style=\'margin-right: 0;\'><input value=\'' . $pdata['cindex'] . '\' type=\'tel\'/></span></li>';
                    $html .= '<li><a ' . $pdata['jump'] . ' class="pager-nav pager-nav-jump">跳转</a></li>';
                    $html .= '</ul>';
                    $html .= '<script>$(function() {$(".pagination .input input").bind("input propertychange", function() {var val=$(this).val(),elm=$(this).closest("ul").find(".pager-nav-jump"),href=elm.data("href");elm.attr("href", href+val)}).on("keydown", function(e) {if (e.keyCode == "13") {var val=$(this).val(),elm=$(this).closest("ul").find(".pager-nav-jump"),href=elm.data("href"); location.href=href+val;}});})</script>';
                }
            }
        }

        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('getScriptName')) {
    function getScriptName()
    {
        $script_name = basename($_SERVER['SCRIPT_FILENAME']);
        if (basename($_SERVER['SCRIPT_NAME']) === $script_name) {
            $script_name = $_SERVER['SCRIPT_NAME'];
        } else {
            if (basename($_SERVER['PHP_SELF']) === $script_name) {
                $script_name = $_SERVER['PHP_SELF'];
            } else {
                if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name) {
                    $script_name = $_SERVER['ORIG_SCRIPT_NAME'];
                } else {
                    if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $script_name)) !== false) {
                        $script_name = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $script_name;
                    } else {
                        if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                            $script_name = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                        } else {
                            $script_name = 'unknown';
                        }
                    }
                }
            }
        }
        return str_replace("/index.php", "", $script_name);
    }
}

/**
 * 单图上传
 * @param string $name 名称
 * @param string $value
 * @param array $options
 * @return string
 */
function tpl_form_field_image(string $name, $value = '', $options = [])
{
    $default = $options['default'] ?: '/app/admin/static/images/nopic.png';

    $val = $default;
    if (!empty($value)) {
        $val = tomedia($value);
    }
    if (empty($options['tabs'])) {
        $options['tabs'] = ['upload' => 'active', 'browser' => '', 'crawler' => ''];
    }
    if (!empty($options['global'])) {
        $options['global'] = true;
    } else {
        $options['global'] = false;
    }
    if (empty($options['class_extra'])) {
        $options['class_extra'] = '';
    }
    if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
        if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
            exit('图片上传目录错误,只能指定最多两级目录,如: "picture","picture/1"');
        }
    }

    $options['direct'] = true;
    $options['multi']  = false;

    if (isset($options['thumb'])) {
        $options['thumb'] = !empty($options['thumb']);
    }

    $s = '';
    if (!defined('TPL_INIT_IMAGE')) {
        $s = '
		<script type="text/javascript">
			function showImageDialog(elm, opts, options) {
				require(["util"], function(util){
					let btn = $(elm);
					let ipt = btn.parent().prev();
					let val = ipt.val();
					let img = ipt.parent().next().children();

					util.image(val, function(url){
					    console.log("url",url)
						if(url.url){
							if(img.length > 0){
								img.get(0).src = url.url;
							}
							ipt.val(url.fileurl);
							ipt.attr("filename",url.filename);
							ipt.attr("url",url.url);
						}
						if(url.media_id){
							if(img.length > 0){
								img.get(0).src = "";
							}
							ipt.val(url.media_id);
						}
					}, opts, options);
				});
			}
			function deleteImage(elm){
				require(["jquery"], function($){
                    $(elm).prev().attr("src", "/app/admin/static/images/nopic.png");
					$(elm).parent().prev().find("input").val("");
				});
			}
		</script>';
        define('TPL_INIT_IMAGE', true);
    }

    $s .= '
<div class="input-group ' . $options['class_extra'] . '">
	<input type="text" name="' . $name . '" value="' . $value . '"' . ($options['extras']['text'] ? $options['extras']['text'] : '') . ' class="form-control" autocomplete="off">
	<span class="input-group-btn">
		<button class="btn btn-primary" type="button" onclick="showImageDialog(this, \'' . base64_encode(iserializer($options)) . '\', ' . str_replace('"', '\'', json_encode($options)) . ');">选择图片</button>
	</span>
';

    if (!empty($options['isShowRandomlogoBtn'])) {
        $qqLen = $options['qq_length'] ?? 9;
        $s     .= '
            <span class="input-group-btn">
                <div class="btn btn-default" onclick="getQQAavatar(this)">自动获取头像</div>
            </span>
            <script>
                function getQQAavatar(This){
                    let qq = Math.random().toString().slice(-' . $qqLen . ')
                    let logo = "//q1.qlogo.cn/g?b=qq&nk="+qq+"&s=100"
                    
                    require(["jquery"], function($){
                        $(This).parent().parent().children("input[name=' . $name . ']").val(logo)
                        $(This).parent().parent().parent().children().find("img").attr("src",logo)
                    });
                }
            </script>
        ';
    }

    $s .= '</div>';

    if (!empty($options['tabs']['browser']) || !empty($options['tabs']['upload'])) {
        $s .=
            '<div class="input-group ' . $options['class_extra'] . '" style="margin-top:.5em;">
				<img src="' . $val . '" onerror="this.src=\'' . $default . '\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail" ' . ($options['extras']['image'] ? $options['extras']['image'] : '') . ' width="150" />
				<em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="deleteImage(this)">×</em>
			</div>';
    }
    return $s;
}

/**
 * 多图上传
 * @param string $name
 * @param array $value
 * @param array $options
 * @return string
 */
if (!function_exists('tpl_form_field_multi_image')) {
    function tpl_form_field_multi_image($name, $value = [], $options = [])
    {
        $options['multiple']      = true;
        $options['direct']        = false;
        $options['fileSizeLimit'] = 10 * 1024;
        if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
            if (!preg_match('/^\\w+([\\/]\\w+)?$/i', $options['dest_dir'])) {
                exit('图片上传目录错误,只能指定最多两级目录,如: "we7_store","we7_store/d1"');
            }
        }

        $s = '';

        if (!defined('TPL_INIT_MULTI_IMAGE')) {
            $s = '
<script type="text/javascript">
	function uploadMultiImage(elm) {
		moveImages();
		let name = $(elm).next().val();
		util.image( "", function(urls){
			$.each(urls, function(idx, url){
				$(elm).parent().parent().next().append(\'<div class="multi-item"><img onerror="this.src=\\\'/app/admin/static/images/nopic.png\\\'; this.title=\\\'图片未找到.\\\'" src="\'+url.url+\'" class="img-responsive img-thumbnail"><input type="hidden" name="\'+name+\'[]" value="\'+url.fileurl+\'"><em class="close" title="删除这张图片" onclick="deleteMultiImage(this)">×</em></div>\');
			});
		}, ' . json_encode($options) . ');
	}

	function moveImages(){
		var isShow = false;
        setTimeout(function(){
            $(".dropdown-toggle").click(function(){
                isShow = !isShow;
                var _this = $(this);

                if(isShow){
                    $(this).parent().addClass("open").find(".dropdown-menu").show();
                    $(this).parent().find(".dropdown-menu").hover(
                        function(){$(this).show();$(this).parent().addClass("open")},
                        function(){$(this).hide();$(this).parent().removeClass("open");}
                    );
                }else{
                    $(this).parent().removeClass("open").find(".dropdown-menu").hide();
                }
            });
        },500);
	}

	function deleteMultiImage(elm){
		require(["jquery"], function($){
			$(elm).parent().remove();
		});
	}

</script>';
            define('TPL_INIT_MULTI_IMAGE', true);
        }

        $s .= '<div class="input-group">
	<input type="text" class="form-control" readonly="readonly" value="" placeholder="批量上传图片" autocomplete="off">
	<span class="input-group-btn">
		<button class="btn btn-primary" type="button" onclick="uploadMultiImage(this);">选择图片</button>
		<input type="hidden" value="' . $name . '" />
	</span>
</div>
<div class="input-group multi-img-details">';
        if (is_array($value) && 0 < count($value)) {
            foreach ($value as $row) {
                $s .= '
<div class="multi-item">
	<img src="' . tomedia($row) . '" onerror="this.src=\'/app/admin/static/images/nopic.png\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail">
	<input type="hidden" name="' . $name . '[]" value="' . $row . '" >
	<em class="close" title="删除这张图片" onclick="deleteMultiImage(this)">×</em>
</div>';
            }
        }

        $s .= '</div>';
        return $s;
    }
}

/**
 * 标签输入
 * @param string $name 名称
 * @param string $value 1,2,3
 * @param array $options
 * @return string
 */
function tpl_form_field_tags_input($name, $value = '', $options = [])
{
    $autocompleteUrl = $options['autocomplete_url'] ?? "";
    $height          = $options['height'] ?? "42px";
    $total           = $options['total'] ?? 5;

    $html = '
    
    <input name="' . $name . '" id="' . $name . '" value="' . $value . '" style="width: auto; min-height: ' . $height . '; height: ' . $height . ';" />
    <script type="text/javascript">
    
        require(["jquery.tagsinput"], function () {
            $("#' . $name . '").tagsInput({
                width: "auto",
                defaultText: "输入后回车确认",
                minInputWidth: 110,
                height: "' . $height . '",
                placeholderColor: "#999",
                onChange: function(e) {
                    let input = $(this).siblings(".tagsinput");
                    let maxLen = "' . $total . '"; // e.g.
                    if (input.children("span.tag").length >= maxLen) {
                        input.children("div").hide();
                    } else {
                        input.children("div").show();
                    }
                },
                onKeyDown: function(e) {
                },
                autocomplete_url:"' . $autocompleteUrl . '",
            });
        })
    
    </script>';

    return $html;
}

/**
 * 单日历
 * @param $name
 * @param string $value
 * @param bool $withtime
 * @param bool $disabled
 * @return string
 */
function tpl_form_field_date($name, $value = '', $withtime = false, $disabled = false)
{
    $s        = '';
    $withtime = empty($withtime) ? false : true;
    if (!empty($value)) {
        $value = strexists($value, '-') ? strtotime($value) : $value;
    } else {
        $value = time();
    }
    $value = ($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value));
    $s     .= '<input type="text" name="' . $name . '"  value="' . $value . '" placeholder="请选择日期时间" ' . ($disabled ? 'disabled' : '') . ' class="datetimepicker form-control" style="padding-left:12px;" />';
    $s     .= '
		<script type="text/javascript">
			require(["datetimepicker"], function(){
					var option = {
						lang : "zh",
						step : 5,
						scrollInput:false, // 鼠标滚动切换日期
						timepicker : ' . (!empty($withtime) ? "true" : "false") . ',
						closeOnDateSelect : true,
						format : "Y-m-d' . (!empty($withtime) ? ' H:i"' : '"') . '
					};
				$(".datetimepicker[name = \'' . $name . '\']").datetimepicker(option);
			});
		</script>';
    return $s;
}

/**
 * 获取昵称
 * @return void
 */
function tpl_form_field_nickname(string $name, $value = ''){
    $s = '
		<script type="text/javascript">
            function getNickName(This){
                let names = ["聆厛埖雨", "陌路离殇℡", "ろ回眸", "女孩般的幸福", "盛夏ヽ剩下", "巴赫的爱情是我的憧憬", "慢慢的聆听", "azure°（蔚蓝的）", "早已▲沧海桑田", "小嘴欠吻", "你若不离·我定不弃", "爱苦但亦甜", "增增感情ぬ谈谈爱", "酒窝暖男℃", "代价是折磨╳", "吥懂?;?珍惜", "嘘！安静点", "旧人成梦", "不被信任的解释都是剩余的", "从未被记起⌒", "叽里呱啦▽", "夜凉如水々", "迷路的男人", "゛指尖的阳光丶", "ら樱雪之城ペ", "负②代", "眼眸，透出一丝温柔。", "俯瞰天空。", "怪我眼瞎べ", "回忆里斑驳的画面", "〃幸福发芽-γ*", "空白的记忆", "现实太过现实", "北极以北。", "κiζs呆呆尐糖", "失去了呼吸的声音", "没资本就别装纯", "街角幸福", "蝼蚁", "↘▂_倥絔", "人忘七年", "対你眞綪", "夜半丶唱情歌", "只求一份安定", "看那一叶春风", "心如死灰゛", "记得以往", "緦唸λ蓇", "吾皇万岁_万万岁", "ゞ渲染ら流星ㄟ的颜色╰︶", "勿念心安。", "瞳孔印温柔", "聆听ゝ╮锦云漫卷的柔暖", "花開丶若相惜", "┏有你我就幸福┛", "#念念不忘丶", "Ｓòrγy︶ㄣ", "__没有背景丶仅有背影", "对半感情", "相依°-相随", "渣中王", "天青色等烟雨", "海消失后鱼死了”", "那時°年少", "三寸日光¤", "美丽的邂逅。", "街捔の一个人", "逐流水袖染尘缘", "像太阳一样温暖人心", "我静静的看着人来人往", "梦醒时分°", "誓言再羙丶抵可是流言似水", "卑微旳嗳", "简单一句我想你了゛言语中", "誮舞⊕霓裳", "柔光的暖阳◎", "钻石女王心！", "╰つ卸不掉的素装ゝ", "╬字路口", "缺氧患人！", "——__斯文敗類", "╘等迩宛在水中央", "淡然一笑", "笑声蕴藏我们的记忆", "吥↘恠侑噯↘", "吾皇万岁_万万岁", "这年头寂寞", "纣王偏宠妲己妖", "时光静好莫念", "一半╮眼线", "帶著面具過日子~", "阳光的耀眼永追随向日葵", "漫步巴黎", "ミ記憶de承渃", "恨旳有滋有味╮", "゜念小炜", "阳光下的忧郁很迷离み", "浅心蓝染△", "怎奈那以往", "旧日巴黎﹏", "洞房不败~", "懒懒的喵喵", "悉数沉淀", "╮想念沵残留旳香水味。", "②号当铺，典当灵魂ぴ", "一干为尽", "等你的季节", "ご沿海哋带づ", "不痛不癢≈", "浮夸了年华丶", "今非昔比’", "失去方向。", "细心翼翼", "相知相惜", "疯狂的年代，无知的我们╮", "一曲離殇", "浅夏﹌初凉", "默吟，尓の诗", "你与氧气共存亡i", "不爱我？滚！", "左岸流年つ", "墜落↓", "淡抹煙熏妝丶", "┈┾现实如此真。", "一生何求の", "以往的回忆完美到让我流泪", "一個人的浪漫。", "达不到的那段奕宏夢", "沉醉在伤感的旋律里", "彼岸花﹏落败", "櫻婲の涙", "不恨了也是一种爱", "我爱你丶不需要理由", "多谢你光临我的梦", "维也纳旋转的音律", "季末如歌", "巴黎的街头我独自徘徊", "緦唸λ蓇", "沐浴在灰色细雨里的巴黎。", "〆恋旧——", "~短暫旳吿別", "回忆的沙漏", "じòぴé離莪冭遙逺", "ㄨ米兰T台下的紫色柠檬草", "长欢尽", "隔壁王学长;", "心中有曲自然嗨", "·☆蝶舞飛揚☆·", "紫冰幽梦", "秋季忆往如故", "——__花季末了", "灵魂深处有个他", "在你的世界走不出去#", "丅①秒の瞬間り", "旧梦虐心ё", "纯真ブ已不复存在", "玫瑰香旳誘惑", "黑魅惑", "残花为谁悲丶", "以往少年薄荷凉", "焰火灿烂时", "釹王控。", "小三温柔了也是低贱ぢ", "夜唯美", "爱情傀儡°", "指着心脏说胃疼i", "冰河丶时代", "晴天般的微笑", "密码深情", "初见。", "空城旧颜°", "我会一向等你", "暗巷点灯", "昔日餘光。", "〆残阳々", "当你的眼睛眯着笑", "樱花树下那纯美一笑", "╭指間de噯╮", "。浅橙色的微光弥漫", "放肆丶小侽人", "一辈子到底有多久", "莮亾↘哋洺牸ㄟ", "怎样自在怎样活", "深爱是场谋杀。", "小嘴欠抽", "花開終贁落", "俄的世界错乱了┃。", "妖艳", "老衲法号乱来", "下一站゛幸福", "三无产品", "暇裝bu愛迩媞壞銀", "不，完美。", "泡沫", "——落盡殘殇", "心似洋葱", "故事，还未完", "听灵魂诉说，", "风的味道╮", "薄暮凉年∞", "淫而不Se丶", "放肆ら弥漫", "倾听花开的声音。", "已沾不起高傲", "轻轻的┒想念", "那抹清澈的阳光╮", "香烟", "墨尔本╮情", "〆仿佛与我相隔多年", "ー紙巟哖", "灬一抹丶苍白", "飞舞的头发", "不落╭若殇舞", "Ⅻ踩著〔淚水〕說愛Э", "机场“相遇”", "虚张声势丶", "夏末未了°", "逆光·", "劳资丿平底鞋走天下", "黯繎落泪〃", "℡淺笑如夏゛", "㊣兒⑧經", "生命因你而灿烂。", "眼眸里的阳光", "◤戏子°", "丶猫猫er", "此號已封", "浅浅嫣然笑", "欢迎勾引", "独守空城", "鍵盤敲出的愛情", "感觉出了错", "请在乎我１秒", "峩陪沵╰╮海枯石烂°", "渴死的鱼", "爱情有保质期", "你用单手遮住了我的眼°", "ら用生命回忆从前", "╰淺唱幸福", "艰难爱情", "So丶各自安好", "日久见人心", "十指连心", "石头队长再见了", "绣一颗最温柔的心", "命里自知ゝ゛", "じ習慣囿沵", "旧角落里的旧画面^~`", "妲己再美终是妃", "尐嘴゛親親", "似花非花つ", "累", "请ń陪wǒ走", "季末诉说寂寞的期盼", "盛开在阳光里的女子∝", "指尖星光谱写黑白乐章", "麦芽糖糖ぴ", "闹够了就滚", "繁华落幕゛", "-深情不及久伴", "﹎℡默默的爱", "爾ф氵曼埗", "嘴角扬起的那一抹い微笑", "童心未泯", "や眼角⒈絲淚丶", "∝逢床作戏", "听说叹息桥下的拥抱会永恒", "旧时光她是个美人", "花花花小伙", "哆啦ā夢り", "日暮斜阳", "浅笑ヽ微凉", "回头丶yi无路可走", "゛浮殇若梦╮", "默默的离开。", "姓大名爷", "背对背拥抱", "轻描淡写的别离。", "以心换心", "。婞褔ｖīｐ", "有没有那么一首歌，", "婚姻终结者", "深巷的猫", "犯二到底", "夏天的味道", "絕版丫頭", "预言又止的痛", "别念不该念的人", "烟染╰素人颜", "梦巴黎", "筱┓熙┓", "人帅！真无奈", "°一米阳光", "覆水难收ζ", "不可离去╮", "安之若素", "玩人必被人玩", "转角记忆朦胧的那盏灯", "╭⌒无言以对", "寶貝ミ吥乖﹎", "萌爹爹", "他的命是我的命-", "单身女王i", "神经兮兮°", "暮夏那年开", "覆水难收╰", "後會無期", "会唱歌的小包子", "戀上孤独", "沙棘沙漠", "游走在苏荷迷域的小镇っ", "挚爱°尐宝", "夹缝的瑰丽", "一身傲骨怎能服输", "梦一样的人生", "自欺欺人", "恋爱的笔诠释青春。", "微信网名", "梅花三烙▲", "时光凉透初时模样。", "零下１℃", "那么爱你WHY", "妖言惑衆", "在哪跌倒こ就在哪躺下", "那片微醉阳光", "啃樱桃小丸子", "类似爱情", "ら゛浅安时光", "近在咫尺的爱恋", "爱没有所谓亏欠", "且听风铃", "爲迩封鈊", "絕蝂de愛♂", "迷乱浮生﹡", "无名指的伤", "心云间凝听", "哥，淫领时尚", "人不风流枉骚年*", "彼此爱人i", "時光在唱歌", "陌离女王", "淡抹丶悲伤", "人必须要靠自我", "じ浮浮沉沉☆", "米修米修er", "哭花了素颜", "回忆如此多娇", "有一抹阳光倾泻在你眼眸", "一切皆有可能", "难免心酸°", "终成陌路″", "麽心麽肺", "念沵心安丶", "｀┗从未爱过谈何分离┓", "乱的很有节奏ゆ", "回忆那份伤", "沵要的，涐給罘起。", "吃素的蚊子", "﹎卌除恛憶ㄨ", "太阳与月亮的交接", "奥利奥╮", "體溫㎝╮", "。私心劣肺", "面具下╮那暧昧不清旳尔", "爱你太久i", "花花世界总是那么虚伪﹌", "安然失笑ゝ", "正二紧", "花落な莫离い", "彼岸花﹏等待", "青春路上我们一齐笑ミ", "婲丷残泪﹌", "安熙诺丶柒°", "ˊ命鍾鉒顁。", "安暖斑驳的阳光", "涐のloveシ伱", "┅涟漪水波在泛滥┅", "等我的另一半。", "最温暖的墙", "走过你的时光", "时光如画，划过你明媚的眼", "铅笔描绘旳思念╮动人心弦", "繼續等待", "孤单海岸线", "錯失的必然", "﹏诉丶那鍛綪", "仰望埃菲尔塔的云", "约好的以后。", "迷路的男人", "墳場做戲﹏", "※雨芐姒後", "我们晒着阳光望着遥远", "爱与恨", "ー半憂傷", "偷得浮生", "陌落ミ繁華﹏", "虚伪了的真心", "一个人演戏°", "空城旧梦", "冷暖自知ら", "放手也是一种罪过‖", "流年染指经不起的伤", "傻蛋也有爱情他腐朽年華＊", "╬茡潞釦", "孤獨患者", "没心没肺°", "夜风月随", "╮稍纵即逝", "▲光脚丫奔跑", "怪癖尐姐", "开心的▲笨小孩", "不要迷恋爷，爷会让你中邪", "女厕老大*&amp;", "再努力也换不回你的温柔ㄟ", "*丶海阔天空", "瞎闹腾i", "烂命一条√", "人小鬼大", "▓米熙小夏", "旧人不归", "俄相信精彩能够隽永", "心隨你動。", "步非+烟花", "と闭眼冥想东京铁塔的记忆°", "看我不爽就滚i", "爱丽丝的旋律", "如果我坦白说≈", "招摇过市小b", "定格°牵手那一刻‖", "伪装坚强", "冷月葬魂", "执念，爱", "▼遗忘那段似水年华", "爱情就是难题", "啃樱桃小丸子", "寂寞，好了", "有妳很瞞促", "无休无止っ", "天意弄人", "艳司令", "何必丶认真", "无人区玫瑰", "夏殤¤落樱", "℡嬡仩沵芣", "素描つ那片天", "浮浮沉沉﹋", "我想请你次辣条!", "謹色安年*", "稚_小_葵", "看电视总期望反派赢。", "紫陌≈紅塵", "感受浅蓝的淡然", "◆◇黑白颠倒", "寂寞的花开", "生命在聆听", "╰素顔小姐", "╯玛莎拉蒂╰", "﹏玻璃一般的透明。", "笑看向日葵", "开心的笨小孩", "怀念·最初", "————倾城之夏", "难过’", "墨染孤舟", "那逝去的流年红楼梦散", "香烟迷醉人心", "摩天輪的仰望", "蝶恋花╮", "梦幻谱写的丶旋律", "地大物勃&gt;&gt;勃大精深", "最爱还是你i", "何時才能學會Ｓay「Ｎｏ」", "给她ヽ夏天般的幸福ゞ", "雨后的温暖", "淡白色╰一素锦流年", "_倾月轩萱_", "掌心温差", "碎一地阳光。", "-你们是我最耀眼的星盟。", "住进时光里", "流年獨殤", "solo-", "一世妖娆", "Aurevoir", "#空城旧梦", "越疯癫的女人心越脆弱", "花骨朵er", "美の别致╮", "浅蓝铯啲嗳", "智商╮偏d。", "ヾ︷浅色年华", "〆那一抹阳光多灿烂。", "黄昏中等待夜幕的降临", "人情薄如纸", "沧海一声笑゛", "哭花了素颜", "那年夏天我们依然在微笑", "厮守╭┈這份情", "琉璃般若花゛", "◆残留德花瓣", "傾尽一生丶等伱つ", "世界的另一半", "Summer·不離不棄", "°捏碎你的虛偽Δ！！", "人心比海深比冰冷ぃ", "情话最终变成了童话", "浮生若梦ァ", "深秋无痕。", "半梦半醒半浮生", "你永远都不知道我有多爱你", "笑靥っ如誮", "爱情ヽ消失在茫茫的人海", "枪蹦狗友", "雨dē印記", "记忆里那片海", "冷暖自知", "淡淡の清香", "六月离歌", "り午夜↘清醒依旧", "最终一刻才明白", "「似水流年」°", "掠过风尘的薄纱洗净铅华", "笑叹。红尘", "一無所冇", "言己", "稚嫩的笑容", "﹂生﹂世﹂双人ら", "冰封の記憶", "くつ沵彵媽嬡過莪庅", "半夏时光", "∞一杯红酒配电影", "回忆未来", "穿越古代", "落在淡水的月光", "嬡過メ財浍慬", "冷落了♂自我·", "你不爱我但我爱你", "罌粟Ω妖嬈", "屎性ハ改°", "从未消失的孤独", "呦你的绿帽子哪买的∝°", "昂贵的背影", "旧人不覆", "长短不一粗细不一样", "半醉〞巴黎づ", "记得遗忘", "不见〞不念", "可喜可乐", "挽贤", "低头〆抚摸你的眼泪", "夏日清菏＆", "︶▔清晨的一缕阳光", "流苏▼暮凉", "爱我所爱", "温柔在手心", "夏至ゝ未至", "自古深情必自毙i", "忧思难忘", "落叶牵绊着思念︶﹋", "旧事重提。", "那岸的向日葵依然灿烂ζ", "以往旳不舍此刻旳不屑", "顺萁咱然丶", "光阴荏苒了谁的思恋", "你会腻我何必", "你是我的幸运儿", "的愛情", "万能男神经i", "ぃ绣滊泡泡℃", "别在我面前犯贱", "丄错车", "伱個禽獸丶", "灿勋zzang", "莫名的青春", "深渊的那支花", "你的愚忠", "心╰→比柠檬酸", "我是升级后的路人乙°", "黑夜的沈寂", "指尖流动的风摇曳中", "若即若离", "听说你是个茬子;", "ヾ亂世浮華つ", "素锦流年", "穿过眼瞳的那些明媚阳光ゝ", "ˉ夨落旳尐孩。", "恋人爱成路人", "默默的付出", "メ稀饭你的笑", "姐的拽你不懂", "骄傲到自负。", "∨盛夏未央", "安颜如夏︶浅Se年华﹌", "悲痛抬头微笑|", "细唇印温柔", "岠蓠浐泩羙", "若爱的牵强", "还有你温暖的眼光", "浮光", "爸气外漏！", "残魂〞", "手心仍有一丝余温", "不要迷恋爸，爸只是个神话", "半夢__半醒", "别无所求", "断肠人", "释怀╮", "少年的泪不及海湛蓝", "Queenie女帝", "颓废式╭流年", "本人已屎", "前后都是你", "愛如空氣", "一季花开ˇ", "只想要你陪", "私定终生ら", "疯格", "一笑婆娑醉颜陀゜", "就再多一秒的爱", "ㄜ~离隹仺白了青春", "脾气酸独与你温柔", "你的笑阳光明媚。", "爱你心口难开", "指尖的气息", "黑的不是社会，是心。", "淺眸丶流年", "人生本就如梦", "無盡透明的思念", "酌酒一杯赐你饮下", "妞╮笑个", "非祢⒏嫁ヾ", "海綿bāo寶り", "花香洇染", "柠栀", "无可置疑◆", "她们似懂非懂", "给我五厘米的高度", "一转身便是一生", "★゛蹲街角ヽ只为等待伱", "乂日光倾城¤", "那年夏天", "┕嬞鍀硪ｄｅ愛", "习惯", "你的眼眸闪烁着未来﹌", "Dissappear。", "旋转的摩天轮，巴黎的印象", "——_浅梦未央", "做伱的﹁半", "xn丶惘然___", "这辈子赖定你了", "哇！原来你也是人", "寄生于回忆中的光", "⊿發糞塗牆△", "虚伪了的真心", "看见岳父只能叫叔叔i", "夏殤¤落樱", "行尸走肉", "為你鐘情", "魔鬼先森", "▲格子涂过的夏天◇╮", "抬起头╰眼泪就不会往下流", "藍色灬飛揚", "记忆浮现。", "∞◆暯小萱◆", "燈光下的淒涼", "╮巴黎铁塔下，仰望幸福", "独美i", "凌晨的末班车", "╰华灯初上旧人可安°", "ヅ白衣飘飘＊", "阳光下那一抹微笑ゝ゜", "灬时空转角盛夏落幕╮", "■□後知後覺", "半糖主義", "‖残殇℡", "雪花ミ飞舞", "毁了悔了", "久而旧之～", "若你能共我唱首歌┛", "蹲街守寂寞", "浅夏﹌初凉", "無處葬心", "那傷。眞美", "ɡrl。女孩", "美美的校霸花", "々爱被冰凝固ゝ", "永恆的承諾卟屬於我", "等待繁华能开满天际°", "三好先森", "◇◆◇·熙", "旧情未了", "我一贱你就笑i", "我還想他√", "や不堪回首", "爱情自以为是", "亲爱的别走。√", "じ★憮鈊嗳你", "午后柠檬树下的阳光。", "消逝在黑夜里旳那抹烟火╮", "窗帘卷起我的发", "重返岁", "别在我面前犯贱", "梦里訴說著對你的思念", "喂请带莪回家", "煙消雲散只為成全*", "转身丶寂寞", "细数那段旧事", "我姓黄我心慌！", "-得不到的永远在骚动", "查无此人゛", "分开也不必须分手", "薄荷小镇的遗忘时光", "续写つ未来", "人心可畏", "不期然而然▽", "心力憔悴〤", "夕颜若沐°Somnus", "时间已摆平所有犯的错", "泪是回忆的代言人", "腼腆１笑", "莫气少年穷", "沵算what°", "默默的承受", "余温散尽ぺ", "独奏ヽ情歌", "命運不堪浮華", "忘了他╮", "华年乱了谁的浮生ˉ", "浮生若梦花香依旧╮", "维也纳的海风づ永不失约", "淰１抹→微笑", "记忆承载将来", "人生如果初见时。", "颓废囧妳", "街角回忆欢乐与忧伤", "莫再执迷不悟。", "普罗旺斯的花海℡永不湮灭", "〆夏未回憶▁▁", "女王(Queen)ゆ性", "屁颠屁颠--&gt;", "夕陽西下", "﹏繁花°似景", "微笑恍若陽光燦烂", "钕人如花ゝ花似梦。", "阿骚：澎湃么*", "没刘海照样拽i", "-Vie", "花容月貌", "尐貓咪咪", "知足是福", "人去楼空", "倾城一笑，抵我半壁江山", "此号已封", "雪花拥抱阳光", "深爱是场谋杀！", "一紙荒年", "煙花易涼。", "下个站口等迩", "用心聆听嵌入灵魂的音符り", "下一站，去哪里→", "粉红。顽皮豹", "浮华落满肩头", "墨羽尘曦", "谁会心疼", "男人調情是天性", "簡單灬愛", "街角·陌路△", "萌主﹫", "℡懒懒DE猪", "散场の电影院", "︶ㄣ沉浮于世的微尘╮", "杯中酒，鸳鸯情。", "空巷旧梦", "女人玩的是命", "我的右手╰没了温度", "喜欢你是我有病i", "C丶F灬梦之队", "┆靜侯メ輪徊", "想゛留你在下一個街角ん", "徒留一场笑谈一场心伤", "慢热型男", "♂蘰踄繧鍴￡", "∝陌上花歌″", "曾苦笑說‘愛你。", "追逐白月光。", "在哪跌倒こ就在哪躺下", "我们的故事已成事故", "右岸亦怜度年华", "聆听寂寞", "◆◇喧哗丶扰乱了浮尘", "夏末聆听本属于我们的恋", "战争与玫瑰·DEspt", "仰望星空想着的人是你", "化思念为星。", "甯缺勿濫丶", "染指流年笑看世间事", "﹡巴黎铁塔", "多一度旳想念", "有钱就是任性", "我们的爱", "琉璃瓶的回忆", "沉浮宁楹的年少。", "等候下个花季", "漫长の人生", "默念丶那份爱", "笑叹★尘世美", "╯念抹浅笑", "一生承诺", "那爱情错的很透明≈", "〃把牢底坐穿╯", "憧憬巴黎夜的安好", "为你付出一切", "喧嚣１切，静止。-", "私定终生ら", "╰听海哭了", "-我在地狱仰望天堂", "阳光下的少年。", "现实▍是那样残酷", "若即若离丿", "亿昔瞳里唯一的", "愛上╮寂寞", "别离·碎碎念", "══做个低调の孩纸◥", "無関痛痒", "月光丶散漫的印在身上", "﹏﹏那年一路向北", "ら普罗旺斯的薰衣草未开つ", "暖寄归人", "缘しve相知", "闭眼之前對迩説_硪愛迩", "嘚瑟的小情绪ぃ", "蔚蓝的天空〃没有我的翅膀", "Darling", "刪蒢ゝ鐹呿", "——__丧心病狂", "听这一季忧伤的雨声╮", "﹏半盏流年丷", "ぐ日光曲", "青春散场", "眺望远方╮你离开的方向", "勾引你爸做你妈。", "或许", "爲愛癡狂", "凉城以北り", "十年温如初", "杞人忧天", "哟耍脾气", "跳进海里躲雨╭ァ", "回忆丶涐们的点点滴滴", "执笔画浮尘。", "你说没对象你媳妇造么", "半夏锦年，笑靥如花", "失去并非不是一种收获", "ゆ散落在回忆里的时光", "孤单*无名指", "这样就好╮", "╰流年已逝╮", "縴伱手①起赱", "唇边回味奶茶浓香*", "三无先森", "Sorry丶我不是警察", "你是我流年里散乱的体温丶", "涐们的幸福像流星丶", "思念成瘾", "後知後覺", "浅浅dē伤", "倾听冷暖丿", "姐抽的是寂寞", "命中之劫°", "◆帅气范儿つ", "じ十指相扣☆", "穿精又带淫゛_", "最迷人的危险", "╰╮強顔歡笑", "半醉人间一念间的天与地", "過客。", "眼成海却未蓝", "未了情", "半世倾城半世殇", "莫泆莫忘", "石头剪子布", "就是任性", "冷酷‰杀神", "莫名的曖昧", "如雪般明澈的双眸", "只为你生！", "▍生人勿扰", "我心透心凉", "蹲墙角丶画圏", "乜許悲傷", "一辈子都当女超人", "盛世如你じ", "不會┑再哭勒", "厌世症i", "立刻有对象", "紫蝶之纞", "相见不如怀念〆", "正在刷新", "时间是自称包治百病的庸医", "花开了吗", "︶ㄣ如果云知道", "め愛的仅有ù┌你", "很有粪量的人", "〆聆听你呼吸的旋律ㄟ", "︶ε╰叽叽歪歪", "如果不是因为爱", "为梦喧闹只为你", "間間單單ヾ", "比花还妖艳的笑容", "莣跽葃兲", "哼唱小情歌", "沦陷芭比伦女人", "揍性！", "吧唧吧唧", "沵好呐年旧曙光", "两个人的回忆", "飘渺的姿态", "丶多谢你给的幸福", "你好同路人", "有妳，很幸福", "一生一世守HU你", "浅陌凉汐﹋", "海绵没宝宝~!", "夜店情殇", "不在乎谁对谁错ン", "巴黎街头那淡淡旳微笑╮", "你听心口在说谎°", "兎孒菈菈", "拜你所赐", "淺憶微涼∞", "丶演绎悲伤", "布拉格广场ˋ旋转忧伤", "分开走", "青春的爱恋", "情比紙薄", "浮殇年華", "----影子", "往事讽刺笑到肚疼", "-沒有以後嗎。", "悲伤中的那一缕阳光つ", "丶七炫灬", "半世陌影", "果冻布丁℃", "左岸云烟", "缺我也没差", "错过一路的风景", "此生不换的执着", "丿super丶潮流灬", "女人无情便是王", "旧城俨然回眸笑ゝ", "夏末°微傷", "愛笑旳眼睛ゅ", "屌丝一号ペ", "有个人，得不到也忘不掉", "何必再忆", "黒色ン誘惑灬", "蜡笔小旧", "誮開一夏", "故作堅強", "赐毒酒一杯给那贱人", "絆夏嶶涼゛", "我脑残我乐意", "众人皆醉我独醒", "——_流氓先森〃", "默默的远观", "亲爱的白小兔", "▓黑色礼服゛", "あ為谁痴狂ゼ", "°別敷衍涐", "紅塵殇雪", "感染了你我旳回忆", "姬〆小溅", "流连东京街角的煦暖", "二無止境", "神经兮兮°", "现实太假ゝ", "一念执著", "不再眠心悲凉つ", "紫色的彩虹", "初夏。浅笑", "◆乱世梦红颜", "●芣へ慬っ爱", "晴天。小曦", "旧时光的记忆", "低吟·那微笑", "以往飞蛾扑火", "背光，世界是斑斓的霞￠", "糕富帅#", "焚心劫", "彼岸花开半纪的清晨一抹°", "胡撸娃i", "嘟嘟⊕糖果", "真爱永存", "爱我毁她你好吊i", "_夏沫丶嘴角的幸福", "彩虹", "若能与你长相厮守ょ", "张望的时光", "路上的风景，只能边走边忘", "豆芽菜", "安瑾然", "三生缘", "美男环绕", "我恋小黄人", "各自寻欢", "长街与风", "江山策", "油炸小飞象", "初吻暖心", "月歌辞", "以你为中心", "南馆潇湘", "滚烫思念", "久自知", "片羽惊鸿", "永不为奴", "小巷姑娘", "辞宴酒", "迪士尼王子", "满眼醉意", "画眉如黛", "清晨热吻", "闲游西湖", "世事浮沉", "樟木板", "诺妆离", "赋流云", "梦冥光", "洛倾颜", "苍暮颜", "安陌夕", "少女甜心", "极致疯狂", "天使之吻", "捻墨于埃", "顾我安稳", "很甜小尾巴", "晨雪度", "口腹之欲", "孤山细雨", "文艺名字", "与子偕臧", "古道瘦马", "落雪倾城", "初夏细雨", "修仙成佛", "欢喜道长", "肃然起敬", "浅月流歌", "共枕一梦", "细雪长风", "折了樱桃", "迷失的灵魂", "乱楼兰", "容颜决", "心动盲点", "眉黛依画", "不知者无罪", "七秒梦", "温和脾气", "故人何以", "沉默控", "甜蜜惩罚", "来我长街", "星星打烊", "银笺别梦", "情忘终", "故人老街", "乱与心", "关关雎鸠", "揪心爱人", "深情是死罪", "熬夜公主", "心倾颜", "微人与归", "人在天涯", "声优先生", "洛井然", "海棠湾恋人", "清酒暖风", "接吻日记", "莫名的曖昧", "爱笑姑娘", "潇湘夜雨", "清歌隐", "笑若扶风", "安未末", "冷月飘霜", "烟寒若雨", "寂寂浮川", "娇眉策", "今天很乖哦", "欲望爱人", "南鸢北筏", "感恩老爸", "灵魂失控", "星星泡饭", "负佳期", "揉乳", "闲云清烟", "辣酒蚀鲠", "烟雨梦兮", "各有归舟", "荒词旧笺", "恰上心头", "渡星河", "迟冷熙", "温柔恶鬼", "半夏荷花", "我醉欲眠", "陌花浮", "极致宠爱", "枯藤昏鸦", "安末言", "无人相候", "苍山林", "阡陌红尘", "花雨黯", "少年如花儿", "热血无赖", "月醉颜", "久别辞", "夏雨潇潇", "薄荷微风", "花泽香菜", "黛画生花", "喝醋耍酒疯", "古风云", "喜欢我吗", "缠心绊情", "玉笙寒", "初见倾心", "溺星光", "热恋烟火", "凝残月", "一曲墨白", "灵魂有火", "挽长情", "旧人不知", "载星而归", "甜橘汽水", "欲望城市", "夏末蓝海", "极致诱惑", "热搜红人", "淡雅荷花", "似余若离", "火山爆米花", "花美兮", "人间贩爱", "小姐姐真撩人", "月色醉人", "京州几今秋", "恩爱恋人", "长明灯", "一叶兰舟", "陌上倾城", "心动代码", "野味仙儿", "短巷与雨", "醉落夕风", "执我之手", "折扇白衣", "余生长醉", "甜熊恋", "坞中客", "仙界商贩", "画卿晚", "满满元气", "伴君幽独", "灵幻先生", "跟着光", "三千痴妄", "小舟碧水", "优雅姑娘", "几妆痕", "满桃绿", "怨恨遍野", "感恩父亲", "沉醉其中", "清笑歌", "黑暗灵魂", "甜啾", "断秋风", "楚碧瑶", "香草味", "仰天望月", "卷眼朦胧", "姑娘我陪你", "敷衍录", "乐意陪你", "南街浊酒", "雕花影", "君醉沙场", "交换软糖", "青丘狐", "久别鸿", "白龍吟", "尘暮夕", "月夜虫鸣", "辣妹", "上官海棠", "锦瑟花", "小胖子", "软风", "花花", "花笺碎", "深情独白", "江南江北", "莫晨筱", "共赴一世", "初出茅庐", "情话喂风", "菊花仙子", "筱晨缘", "长街听风", "倾紫淑", "青丝依琯", "巷口酒肆", "冬瓜茶饮", "晚安亲吻", "争霸天涯", "枫无痕", "笑饮孤鸿", "翠竹扫窗", "山间明月", "深夜鱼棠", "不负朝暮", "梦的出口", "吻上瘾", "仙姬", "决雌雄", "心宽体胖", "落叶劫", "谈春色", "露绮兰", "狂热恋人", "失心线", "盐焗小星球", "珠残影", "北悸安凉", "问心无愧", "网络恋爱", "吸睛红人", "橘予梦迟", "粉色桃花", "风云变幻", "执墨笔", "守护小月亮", "锦绣金札", "清欢百味", "长发嫩妹子", "兮洛词", "北海凉夏", "梦在深巷", "我欲封天", "过度幻想", "素骨白筝", "温其如玉", "清风归客", "山河夜舟", "背靠暖阳", "旧人序", "旧城凉", "允我心安", "南栀北辰", "调教爱奴", "饮尽风生", "南笙一梦", "渡余生", "只要平凡", "灵丹妙药", "旧容颜", "株连内心", "恋学长", "百日醉", "春末残花", "媚娘浅笑", "南独酌酒", "姹紫嫣红", "共清欢", "青弦墨韵", "遥遥星摆", "绻影浮沉", "尽情享用", "惊鸿照", "人性的丑陋", "顾北凉城", "长歌当哭", "白茶清欢", "甜网恋", "抱着小熊长大", "歌年华", "冷残影", "糙汉", "风过长街", "沦陷温柔", "紫樱夏日", "北仑色", "煉獄人间", "一箭流光", "梦幻花园", "问我亦无愧", "醉春风", "顾敖方", "染指徒留", "平凡女人", "梦幻少年派", "一竿风月", "倾国妃", "恩怨尽", "关于我们", "剑道", "欠你的幸福", "长宫柳", "各有渡口", "桂花酿", "相思劫", "碧水天", "浪推晚风", "几孤风月", "雨中妖", "青丝绕手", "夜风央", "清酒独酌", "冷熙瑶", "梦春秋", "追忆昔年", "谪仙人", "醉魂幽扬","笔在指尖","两袖清风","一支笔杆","苍天不负","水墨淡雅","清水芙蓉","清雅别致","爱笑的玫瑰","独树一帜","独木成林","高雅","信息时代","遥遥相对","不甘示弱","爱在五角大楼","美国去shi","风也残烛","随风而逝","难以割舍","美丽蝴蝶","风咋"];
                let index = getIndex(0,names.length);
                let name = names[index];
                $(This).prev().val(name);
            }
            function getIndex(min,max){
                return Math.floor(Math.random()*(max-min)) + min;
            }
		</script>
    ';

    $s .= '
        <div class="input-group">
            <input type="text" class="form-control" name="'.$name.'" value="'.$value.'">
            <a class="input-group-addon" onclick="getNickName(this)" href="javascript:;">自动获取昵称</a>
        </div>
    ';
    return $s;
}

function tpl_ueditor($id, $value = '', $options = [])
{
    $s                             = '';
    $options['height']             = empty($options['height']) ? 200 : $options['height'];
    $options['allow_upload_video'] = isset($options['allow_upload_video']) ? $options['allow_upload_video'] : true;

    $s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$id}\" type=\"text/plain\" style=\"height:{$options['height']}px;\">{$value}</textarea>" : '';

    $id               = $id ? $id : "";
    $height           = $options['height'];
    $audioLimit       = 30 * 1024; // 音频大小
    $imageLimit       = 20 * 1024; // 图片大小
    $destDir          = $options['dest_dir'] ? $options['dest_dir'] : '';
    $allowUploadVideo = $options['allow_upload_video'] ? true : false;

    $s .= "
	<script type=\"text/javascript\">
		require(['util'], function(util){
			util.editor('{$id}', {
                height : '{$height}', 
                dest_dir : '{$destDir}',
                image_limit : '{$imageLimit}',
                allow_upload_video : '{$allowUploadVideo}',
                audio_limit : '{$audioLimit}',
                callback : ''
			});
		});
	</script>";
    return $s;
}

function tpl_tinymce($name, $value = '', $options = [])
{
    $s                             = '';
    $options['height']             = empty($options['height']) ? 200 : $options['height'];
    $options['allow_upload_video'] = isset($options['allow_upload_video']) ? $options['allow_upload_video'] : true;

    $id = str_replace("[", "_", $name);
    $id = str_replace("]", "", $id);

    $s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$name}\" type=\"text/plain\" style=\"height:{$options['height']}px;\">{$value}</textarea>" : '';

    $s .= "
	<script type=\"text/javascript\">
		require(['util'], function(util){
			util.tinymce($(\"#{$id}\")[0]);
		});
	</script>";
    return $s;
}

function tpl_form_field_color($name, $value = '')
{
    $s = '';
    if (!defined('TPL_INIT_COLOR')) {
        $s = '
		<script type="text/javascript">
			$(function(){
				$(".colorpicker").each(function(){
					var elm = this;
					util.colorpicker(elm, function(color){
						$(elm).parent().prev().prev().val(color.toHexString());
						$(elm).parent().prev().css("background-color", color.toHexString());
					});
				});
				$(".colorclean").click(function(){
					$(this).parent().prev().prev().val("");
					$(this).parent().prev().css("background-color", "#FFF");
				});
			});
		</script>';
        define('TPL_INIT_COLOR', true);
    }
    $s .= '
		<div class="row row-fix">
			<div class="col-xs-8 col-sm-8" style="padding-right:0;">
				<div class="input-group">
					<input class="form-control" type="text" name="' . $name . '" placeholder="请选择颜色" value="' . $value . '">
					<span class="input-group-addon" style="width:35px;border-left:none;background-color:' . $value . '"></span>
					<span class="input-group-btn">
						<button class="btn btn-default colorpicker" type="button">选择颜色 <i class="fa fa-caret-down"></i></button>
						<button class="btn btn-default colorclean" type="button"><span><i class="fa fa-remove"></i></span></button>
					</span>
				</div>
			</div>
		</div>
		';
    return $s;
}


function tpl_form_field_location_category($name, $values = [], $del = false)
{
    $html = '';
    if (!defined('TPL_INIT_LOCATION_CATEGORY')) {
        $html .= '
		<script type="text/javascript">
			require(["location"], function(loc){
				$(".tpl-location-container").each(function(){

					var elms = {};
					elms.cate = $(this).find(".tpl-cate")[0];
					elms.sub = $(this).find(".tpl-sub")[0];
					elms.clas = $(this).find(".tpl-clas")[0];
					var vals = {};
					vals.cate = $(elms.cate).attr("data-value");
					vals.sub = $(elms.sub).attr("data-value");
					vals.clas = $(elms.clas).attr("data-value");
					loc.render(elms, vals, {withTitle: true});
				});
			});
		</script>';
        define('TPL_INIT_LOCATION_CATEGORY', true);
    }
    if (empty($values) || !is_array($values)) {
        $values = ['cate' => '', 'sub' => '', 'clas' => ''];
    }
    if (empty($values['cate'])) {
        $values['cate'] = '';
    }
    if (empty($values['sub'])) {
        $values['sub'] = '';
    }
    if (empty($values['clas'])) {
        $values['clas'] = '';
    }
    $html .= '
		<div class="row row-fix tpl-location-container">
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[cate]" data-value="' . $values['cate'] . '" class="form-control tpl-cate">
				</select>
			</div>
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[sub]" data-value="' . $values['sub'] . '" class="form-control tpl-sub">
				</select>
			</div>
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[clas]" data-value="' . $values['clas'] . '" class="form-control tpl-clas">
				</select>
			</div>';
    if ($del) {
        $html .= '
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3" style="padding-top:5px">
				<a title="删除" onclick="$(this).parents(\'.tpl-location-container\').remove();return false;"><i class="fa fa-times-circle"></i></a>
			</div>
		</div>';
    } else {
        $html .= '</div>';
    }

    return $html;
}

/**
 * 获取日期区间
 * @param unknown $name
 * @param unknown $value
 * @param string $time
 * @return string
 */
function tpl_form_field_daterange($name, $value = [], $time = false)
{
    $s = '';

    // $value['starttime'] = "2022-08-08";

    if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
        $s = '
<script type="text/javascript">
	require(["daterangepicker"], function(){
		$(function(){
			$(".daterange.daterange-date").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD"
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());
					$(elm).prev().prev().val(start.toDateStr());
					$(elm).prev().val(end.toDateStr());
				});
			});
		});
	});
</script>
';
        define('TPL_INIT_DATERANGE_DATE', true);
    }

    if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
        $s = '
<script type="text/javascript">
	require(["daterangepicker"], function(){
		$(function(){
			$(".daterange.daterange-time").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD HH:mm",
					timePicker: true,
					timePicker12Hour : false,
					timePickerIncrement: 1,
					minuteStep: 1
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());
					$(elm).prev().prev().val(start.toDateTimeStr());
					$(elm).prev().val(end.toDateTimeStr());
				});
			});
		});
	});
</script>
';
        define('TPL_INIT_DATERANGE_TIME', true);
    }

    if ($value['starttime'] !== false && $value['start'] !== false) {
        if ($value['start']) {
            $value['starttime'] = empty($time) ? date('Y-m-d', strtotime($value['start'])) : date('Y-m-d H:i', strtotime($value['start']));
        }
        $value['starttime'] = empty($value['starttime']) ? (empty($time) ? date('Y-m-d') : date('Y-m-d H:i')) : $value['starttime'];
    } else {
        $value['starttime'] = '请选择';
    }

    if ($value['endtime'] !== false && $value['end'] !== false) {
        if ($value['end']) {
            $value['endtime'] = empty($time) ? date('Y-m-d', strtotime($value['end'])) : date('Y-m-d H:i', strtotime($value['end']));
        }
        $value['endtime'] = empty($value['endtime']) ? $value['starttime'] : $value['endtime'];
    } else {
        $value['endtime'] = '请选择';
    }

    $s .= '
	<input name="' . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . '" />
	<input name="' . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . '" />

	<button class="btn btn-default daterange ' . (!empty($time) ? 'daterange-time' : 'daterange-date') . '" type="button"><span class="date-title">' . $value['starttime'] . ' 至 ' . $value['endtime'] . '</span> <i class="fa fa-calendar"></i></button>
	';
    return $s;
}

function tpl_form_field_district($name, $values = [])
{
    $html = '';
    if (!defined('TPL_INIT_DISTRICT')) {
        $html .= '
		<script type="text/javascript">
			require(["district"], function(dis){
				$(".tpl-district-container").each(function(){
					var elms = {};
					elms.province = $(this).find(".tpl-province")[0];
					elms.city = $(this).find(".tpl-city")[0];
					elms.district = $(this).find(".tpl-district")[0];
					var vals = {};
					vals.province = $(elms.province).attr("data-value");
					vals.city = $(elms.city).attr("data-value");
					vals.district = $(elms.district).attr("data-value");
					dis.render(elms, vals, {withTitle: true});
				});
			});
		</script>';
        define('TPL_INIT_DISTRICT', true);
    }
    if (empty($values) || !is_array($values)) {
        $values = ['province' => '', 'city' => '', 'district' => ''];
    }
    if (empty($values['province'])) {
        $values['province'] = '';
    }
    if (empty($values['city'])) {
        $values['city'] = '';
    }
    if (empty($values['district'])) {
        $values['district'] = '';
    }
    $html .= '
		<div class="row row-fix tpl-district-container">
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[province]" data-value="' . $values['province'] . '" class="form-control tpl-province">
				</select>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[city]" data-value="' . $values['city'] . '" class="form-control tpl-city">
				</select>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[district]" data-value="' . $values['district'] . '" class="form-control tpl-district">
				</select>
			</div>
		</div>';
    return $html;
}

function tpl_form_field_clock($name, $value = '')
{
    $s = '';
    if (!defined('TPL_INIT_CLOCK_TIME')) {
        $s .= '
		<script type="text/javascript">
			require(["clockpicker"], function($){
				$(function(){
					$(".clockpicker").clockpicker({
						autoclose: true
					});
				});
			});
		</script>
		';
        define('TPL_INIT_CLOCK_TIME', 1);
    }
    $time = date('H:i');
    if (!empty($value)) {
        if (!strexists($value, ':')) {
            $time = date('H:i', $value);
        } else {
            $time = $value;
        }
    }
    $s .= '	<div class="input-group clockpicker">
				<span class="input-group-addon"><i class="icon icon-time"></i></span>
				<input type="text" name="' . $name . '" value="' . $time . '" class="form-control">
			</div>';
    return $s;
}

function tpl_form_field_calendar($name, $values = [])
{
    $html = '';
    if (!defined('TPL_INIT_CALENDAR')) {
        $html .= '
		<script type="text/javascript">
			function handlerCalendar(elm) {
				require(["moment"], function(moment){
					var tpl = $(elm).parent().parent();
					var year = tpl.find("select.tpl-year").val();
					var month = tpl.find("select.tpl-month").val();
					var day = tpl.find("select.tpl-day");
					day[0].options.length = 1;
					if(year && month) {
						var date = moment(year + "-" + month, "YYYY-M");
						var days = date.daysInMonth();
						for(var i = 1; i <= days; i++) {
							var opt = new Option(i, i);
							day[0].options.add(opt);
						}
						if(day.attr("data-value")!=""){
							day.val(day.attr("data-value"));
						} else {
							day[0].options[0].selected = "selected";
						}
					}
				});
			}
			require([""], function(){
				$(".tpl-calendar").each(function(){
					handlerCalendar($(this).find("select.tpl-year")[0]);
				});
			});
		</script>';
        define('TPL_INIT_CALENDAR', true);
    }

    if (empty($values) || !is_array($values)) {
        $values = [0, 0, 0];
    }
    $values['year']  = intval($values['year']);
    $values['month'] = intval($values['month']);
    $values['day']   = intval($values['day']);

    if (empty($values['year'])) {
        $values['year'] = '1980';
    }
    $year = [date('Y'), '1914'];
    $html .= '<div class="row row-fix tpl-calendar">
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[year]" onchange="handlerCalendar(this)" class="form-control tpl-year">
				<option value="">年</option>';
    for ($i = $year[1]; $i <= $year[0]; $i++) {
        $html .= '<option value="' . $i . '"' . ($i == $values['year'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
    }
    $html .= '	</select>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[month]" onchange="handlerCalendar(this)" class="form-control tpl-month">
				<option value="">月</option>';
    for ($i = 1; $i <= 12; $i++) {
        $html .= '<option value="' . $i . '"' . ($i == $values['month'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
    }
    $html .= '	</select>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[day]" data-value="' . $values['day'] . '" class="form-control tpl-day">
				<option value="0">日</option>
			</select>
		</div>
	</div>';
    return $html;
}

if (!function_exists('tpl_form_field_video2')) {
    function tpl_form_field_video2($name, $value = '', $options = [])
    {
        $options['btntext'] = !empty($options['btntext']) ? $options['btntext'] : '选择视频';

        if ($options['disabled']) {
            $options['readonly'] = true;
        }

        $html = '';
        $html .= '<div class="input-group"';

        if ($options['disabled']) {
            $html .= ' style="width: 100%;"';
        }

        $idName = str_replace("]", "", str_replace("[", "-", $name));

        $html .= '><input class="form-control" id="select-video-' . $idName . '" name="' . $name . '" value="' . $value . '" placeholder="' . $options['placeholder'] . '"';

        if ($options['readonly']) {
            $html .= ' readonly="readonly"';
        }

        $html .= '/>';

        if (!$options['disabled']) {
            $html .= '<span class="input-group-addon btn btn-primary" data-toggle="selectVideo" data-input="#select-video-' . $idName . '" data-network="' . $options['network'] . '">' . $options['btntext'] . '</span>';
        }

        $html .= '</div>';
        $html .= '<div class="input-group"><div class="multi-item" style="display: block" title="预览视频" data-toggle="previewVideo" data-input="#select-video-' . $idName . '"><div class="img-responsive img-thumbnail img-video" style="width: 100px; height: 100px; position: relative; text-align: center; cursor: pointer;" src=""><i class="icon icon-play-circle" style="font-size: 60px; line-height: 90px; color: #999;"></i></div>';

        if (!$options['disabled']) {
            $html .= '<em class="close" title="移除视频" data-toggle="previewVideoDel" data-element="#select-video-' . $idName . '">×</em>';
        }

        $html .= '</div></div>';
        return $html;
    }
}

if (!function_exists('tpl_form_field_video')) {
    function tpl_form_field_video($name, $value = '', $options = [])
    {
        if (!is_array($options)) {
            $options = [];
        }
        if (!is_array($options)) {
            $options = [];
        }
        $options['direct']        = true;
        $options['multi']         = false;
        $options['type']          = 'video';
        $options['fileSizeLimit'] = 2048 * 1024;
        $s                        = '';
        if (!defined('TPL_INIT_VIDEO')) {
            $s = '
<script type="text/javascript">
	function showVideoDialog(elm, options) {
		require(["util"], function(util){
			var btn = $(elm);
			var ipt = btn.parent().prev();
			var val = ipt.val();
			util.audio(val, function(url){
				if(url && url.attachment && url.url){
					btn.prev().show();
					ipt.val(url.attachment);
					ipt.attr("filename",url.filename);
					ipt.attr("url",url.url);
				}
				if(url && url.media_id){
					ipt.val(url.media_id);
				}
			}, ' . json_encode($options) . ');
		});
	}

</script>';
            echo $s;
            define('TPL_INIT_VIDEO', true);
        }

        $s .= '
	<div class="input-group">
		<input type="text" value="' . $value . '" name="' . $name . '" class="form-control" autocomplete="off" ' . ($options['extras']['text'] ? $options['extras']['text'] : '') . '>
		<span class="input-group-btn">
			<button class="btn btn-default" type="button" onclick="showVideoDialog(this,' . str_replace('"', '\'', json_encode($options)) . ');">选择媒体文件</button>
		</span>
	</div>';
        return $s;
    }
}

function tpl_form_field_audio($name, $value = '', $options = [])
{
    if (!is_array($options)) {
        $options = [];
    }
    $options['direct']        = true;
    $options['multiple']      = false;
    $options['fileSizeLimit'] = 1024 * 1024;

    $s = '';
    if (!defined('TPL_INIT_AUDIO')) {
        $s = '
<script type="text/javascript">
	function showAudioDialog(elm, base64options, options) {
		require(["util"], function(util){
			var btn = $(elm);
			var ipt = btn.parent().prev();
			var val = ipt.val();
			
			util.audio(val, function(url){
			    
			    console.log("url",url);
			    
				if(url && url.fileurl && url.url){
					btn.prev().show();
					ipt.val(url.fileurl);
					ipt.attr("filename",url.filename);
					ipt.attr("url",url.url);
					setAudioPlayer();
				}
				if(url && url.media_id){
					ipt.val(url.media_id);
				}
			}, "" , ' . json_encode($options) . ');
		});
	}

	function setAudioPlayer(){
		require(["jquery.jplayer"], function(){
			$(function(){
				$(".audio-player").each(function(){
					$(this).prev().find("button").eq(0).click(function(){
						var src = $(this).parent().prev().val();
						if($(this).find("i").hasClass("fa-stop")) {
							$(this).parent().parent().next().jPlayer("stop");
						} else {
							if(src) {
								$(this).parent().parent().next().jPlayer("setMedia", {mp3: util.tomedia(src)}).jPlayer("play");
							}
						}
					});
				});

				$(".audio-player").jPlayer({
					playing: function() {
						$(this).prev().find("i").removeClass("fa-play").addClass("fa-stop");
					},
					pause: function (event) {
						$(this).prev().find("i").removeClass("fa-stop").addClass("fa-play");
					},
					swfPath: "/app/admin/static/components/jplayer",
					supplied: "mp3"
				});
				$(".audio-player-media").each(function(){
					$(this).next().find(".audio-player-play").css("display", $(this).val() == "" ? "none" : "");
				});
			});
		});
	}
	setAudioPlayer();
</script>';
        echo $s;
        define('TPL_INIT_AUDIO', true);
    }
    $s .= '
	<div class="input-group">
		<input type="text" value="' . $value . '" name="' . $name . '" class="form-control audio-player-media" autocomplete="off" ' . ($options['extras']['text'] ? $options['extras']['text'] : '') . '>
		<span class="input-group-btn">
			<button class="btn btn-default audio-player-play" type="button" style="display:none;"><i class="fa fa-play"></i></button>
			<button class="btn btn-default" type="button" onclick="showAudioDialog(this, \'' . base64_encode(iserializer($options)) . '\',' . str_replace('"', '\'', json_encode($options)) . ');">选择媒体文件</button>
		</span>
	</div>
	<div class="input-group audio-player"></div>';
    return $s;
}


function tpl_form_field_multi_audio($name, $value = [], $options = [])
{
    $s                        = '';
    $options['direct']        = false;
    $options['multiple']      = true;
    $options['fileSizeLimit'] = intval($GLOBALS['_W']['setting']['upload']['audio']['limit']) * 1024;

    if (!defined('TPL_INIT_MULTI_AUDIO')) {
        $s .= '
<script type="text/javascript">
	function showMultiAudioDialog(elm, name) {
		require(["util"], function(util){
			var btn = $(elm);
			var ipt = btn.parent().prev();
			var val = ipt.val();

			util.audio(val, function(urls){
				$.each(urls, function(idx, url){
					var obj = $(\'<div class="multi-audio-item" style="height: 40px; position:relative; float: left; margin-right: 18px;"><div class="multi-audio-player"></div><div class="input-group"><input type="text" class="form-control" readonly value="\' + url.fileurl + \'" /><div class="input-group-btn"><button class="btn btn-default" type="button"><i class="fa fa-play"></i></button><button class="btn btn-default" onclick="deleteMultiAudio(this)" type="button"><i class="fa fa-remove"></i></button></div></div><input type="hidden" name="\'+name+\'[]" value="\'+url.fileurl+\'"></div>\');
					$(elm).parent().parent().next().append(obj);
					setMultiAudioPlayer(obj);
				});
			}, ' . json_encode($options) . ');
		});
	}
	function deleteMultiAudio(elm){
		$(elm).parent().parent().parent().remove();
	}
	function setMultiAudioPlayer(elm){
		require(["jquery.jplayer"], function(){
			$(".multi-audio-player",$(elm)).next().find("button").eq(0).click(function(){
				var src = $(this).parent().prev().val();
				if($(this).find("i").hasClass("fa-stop")) {
					$(this).parent().parent().prev().jPlayer("stop");
				} else {
					if(src) {
						$(this).parent().parent().prev().jPlayer("setMedia", {mp3: util.tomedia(src)}).jPlayer("play");
					}
				}
			});
			$(".multi-audio-player",$(elm)).jPlayer({
				playing: function() {
					$(this).next().find("i").eq(0).removeClass("fa-play").addClass("fa-stop");
				},
				pause: function (event) {
					$(this).next().find("i").eq(0).removeClass("fa-stop").addClass("fa-play");
				},
				swfPath: "/app/admin/static/components/jplayer",
				supplied: "mp3"
			});
		});
	}
</script>';
        define('TPL_INIT_MULTI_AUDIO', true);
    }

    $s .= '
<div class="input-group">
	<input type="text" class="form-control" readonly="readonly" value="" placeholder="批量上传音乐" autocomplete="off">
	<span class="input-group-btn">
		<button class="btn btn-default" type="button" onclick="showMultiAudioDialog(this,\'' . $name . '\');">选择音乐</button>
	</span>
</div>
<div class="input-group multi-audio-details clear-fix" style="margin-top:.5em;">';
    if (!empty($value) && !is_array($value)) {
        $value = [$value];
    }
    if (is_array($value) && count($value) > 0) {
        $n = 0;
        foreach ($value as $row) {
            $m = random(8);
            $s .= '
	<div class="multi-audio-item multi-audio-item-' . $n . '-' . $m . '" style="height: 40px; position:relative; float: left; margin-right: 18px;">
		<div class="multi-audio-player"></div>
		<div class="input-group">
			<input type="text" class="form-control" value="' . $row . '" readonly/>
			<div class="input-group-btn">
				<button class="btn btn-default" type="button"><i class="fa fa-play"></i></button>
				<button class="btn btn-default" onclick="deleteMultiAudio(this)" type="button"><i class="fa fa-remove"></i></button>
			</div>
		</div>
		<input type="hidden" name="' . $name . '[]" value="' . $row . '">
	</div>
	<script language="javascript">setMultiAudioPlayer($(".multi-audio-item-' . $n . '-' . $m . '"));</script>';
            $n++;
        }
    }
    $s .= '
</div>';

    return $s;
}

if (!function_exists('tpl_form_field_position')) {
    function tpl_form_field_position($field, $value = [], $locationType = "GCJ-02")
    {
        // BD-09 是百度地图的坐标系，需要百度坐标的类型改为 BD-09
        // GCJ-02 是腾讯与高德地图的坐标系，需要高德坐标的类型改为 GCJ-02

        $s = '';

        if (!defined('TPL_INIT_COORDINATE')) {
            $s .= '<script type="text/javascript">
                    function showCoordinate(elm) {
                        
                            var val = {};
                            val.lng = parseFloat($(elm).parent().prev().prev().find(":text").val());
                            val.lat = parseFloat($(elm).parent().prev().find(":text").val());
                            
                            '.($locationType == 'BD-09' ? "val = biz.BdMapToTxMap(val.lat,val.lng);" : "").'
                            
                            biz.map(val, function(r){
                                var address_label = $("#address_label");
                                if (address_label.length>0)
                                {
                                    address_label.val(r.label);
                                }
                                '.($locationType == 'BD-09' ? "r = biz.TxMapToBdMap(r.lat,r.lng);" : "").'
                                $(elm).parent().prev().prev().find(":text").val(r.lng);
                                $(elm).parent().prev().find(":text").val(r.lat);
                            },"' . '/admin/util/map.html' . '");
    }
    
                </script>';
            define('TPL_INIT_COORDINATE', true);
        }

        $s .= '
            <div class="row row-fix">
                <div class="col-xs-4 col-sm-4">
                    <input type="text" name="' . $field . '[lng]" value="' . $value['lng'] . '" placeholder="地理经度"  class="form-control" />
                </div>
                <div class="col-xs-4 col-sm-4">
                    <input type="text" name="' . $field . '[lat]" value="' . $value['lat'] . '" placeholder="地理纬度"  class="form-control" />
                </div>
                <div class="col-xs-4 col-sm-4">
                    <button onclick="showCoordinate(this);" class="btn btn-default" type="button">选择坐标</button>
                </div>
            </div>';
        return $s;
    }
}