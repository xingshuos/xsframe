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
        $options['multi'] = intval($options['multi']);
        $options['buttontext'] = isset($options['buttontext']) ? $options['buttontext'] : '请选择';
        $options['items'] = isset($options['items']) && $options['items'] ? $options['items'] : [];
        $options['readonly'] = isset($options['readonly']) ? $options['readonly'] : true;
        $options['callback'] = isset($options['callback']) ? $options['callback'] : '';
        $options['key'] = isset($options['key']) ? $options['key'] : 'id';
        $options['text'] = isset($options['text']) ? $options['text'] : 'title';
        $options['thumb'] = isset($options['thumb']) ? $options['thumb'] : 'thumb';
        $options['preview'] = isset($options['preview']) ? $options['preview'] : true;
        $options['type'] = isset($options['type']) ? $options['type'] : 'image';
        $options['input'] = isset($options['input']) ? $options['input'] : true;
        $options['required'] = isset($options['required']) ? $options['required'] : false;
        $options['nokeywords'] = isset($options['nokeywords']) ? $options['nokeywords'] : 0;
        $options['placeholder'] = isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词';
        $options['autosearch'] = isset($options['autosearch']) ? $options['autosearch'] : 0;

        if (empty($options['items'])) {
            $options['items'] = [];
        } else {
            if (!is_array2($options['items'])) {
                $options['items'] = [$options['items']];
            }
        }

        $options['name'] = $name;
        $titles = '';

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
        $readonly = $options['readonly'] ? 'readonly' : '';
        $required = $options['required'] ? ' data-rule-required="true"' : '';
        $callback = !empty($options['callback']) ? ', ' . $options['callback'] : '';
        $id = $options['multi'] ? $name . '[]' : $name;
        $html = '<div id=\'' . $name . '_selector\' class=\'selector\'
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

        $pager = [];
        $pager['page'] = $pageIndex;
        $pager['pageNum'] = $pageNum;
        $pager['total'] = $total;

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
        $pdata = ['tcount' => 0, 'tpage' => 0, 'cindex' => 0, 'findex' => 0, 'pindex' => 0, 'nindex' => 0, 'lindex' => 0, 'options' => ''];

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
            $pdata['tpage'] = empty($pageSize) || $pageSize < 0 ? 1 : ceil($total / $pageSize);

            if ($pdata['tpage'] <= 1) {
                return '';
            }

            if (1 < $pdata['tpage']) {
                $html .= '<ul class="pagination pagination-centered">';
                $cindex = $pageIndex;
                $cindex = min($cindex, $pdata['tpage']);
                $cindex = max($cindex, 1);
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
                        $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
                        $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
                        $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
                        $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
                    } else {
                        $jump_get = $_GET;
                        $jump_get['page'] = '';
                        $pdata['jump'] = 'href="' . ($scriptName ?? '') . '?' . http_build_query($jump_get) . $pdata['cindex'] . '" data-href="' . ($scriptName ?? '') . '?' . http_build_query($jump_get) . '"';
                        $_GET['page'] = $pdata['findex'];
                        $pdata['faa'] = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page'] = $pdata['pindex'];
                        $pdata['paa'] = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page'] = $pdata['nindex'];
                        $pdata['naa'] = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
                        $_GET['page'] = $pdata['lindex'];
                        $pdata['laa'] = 'href="' . ($scriptName ?? '') . '?' . http_build_query($_GET) . '"';
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
                    $range = [];
                    $range['start'] = max(1, $pdata['cindex'] - $context['before']);
                    $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);

                    if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
                        $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
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
                                $aa = 'href="?' . http_build_query($_GET) . '"';
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
 * @param string $default
 * @param array $options
 * @return string
 */
function tpl_form_field_image($name, $value = '', $default = '', $options = [])
{
    if (empty($default)) {
        $default = '/app/admin/static/images/nopic.png';
    }
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
    $options['multi'] = false;

    if (isset($options['thumb'])) {
        $options['thumb'] = !empty($options['thumb']);
    }

    $s = '';
    if (!defined('TPL_INIT_IMAGE')) {
        $s = '
		<script type="text/javascript">
			function showImageDialog(elm, opts, options) {
				require(["util"], function(util){
					var btn = $(elm);
					var ipt = btn.parent().prev();
					var val = ipt.val();
					var img = ipt.parent().next().children();

					util.image(val, function(url){
					    // console.log("url",url)
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
        $s .= '
            <span class="input-group-btn">
                <div class="btn btn-default" onclick="getQQAavatar(this)">自动获取头像</div>
            </span>
            <script>
                function getQQAavatar(This){
                    let qq = Math.random().toString().slice(-' . $qqLen . ')
                    let logo = "http://q1.qlogo.cn/g?b=qq&nk="+qq+"&s=100"
                    
                    require(["jquery"], function($){
                        $(This).parent().parent().children("input[name=' . $name . ']").val(logo)
                        $(This).parent().parent().parent().children().eq(2).children("img").attr("src",logo)
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
        $options['multiple'] = true;
        $options['direct'] = false;
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
    $height = $options['height'] ?? "42px";
    $total = $options['total'] ?? 5;

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
    $s = '';
    $withtime = empty($withtime) ? false : true;
    if (!empty($value)) {
        $value = strexists($value, '-') ? strtotime($value) : $value;
    } else {
        $value = time();
    }
    $value = ($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value));
    $s .= '<input type="text" name="' . $name . '"  value="' . $value . '" placeholder="请选择日期时间" ' . ($disabled ? 'disabled' : '') . ' class="datetimepicker form-control" style="padding-left:12px;" />';
    $s .= '
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

function tpl_ueditor($id, $value = '', $options = [])
{
    $s = '';
    $options['height'] = empty($options['height']) ? 200 : $options['height'];
    $options['allow_upload_video'] = isset($options['allow_upload_video']) ? $options['allow_upload_video'] : true;

    $s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$id}\" type=\"text/plain\" style=\"height:{$options['height']}px;\">{$value}</textarea>" : '';

    $id = $id ? $id : "";
    $height = $options['height'];
    $audioLimit = 30 * 1024; // 音频大小
    $imageLimit = 20 * 1024; // 图片大小
    $destDir = $options['dest_dir'] ? $options['dest_dir'] : '';
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
    $s = '';
    $options['height'] = empty($options['height']) ? 200 : $options['height'];
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
    $values['year'] = intval($values['year']);
    $values['month'] = intval($values['month']);
    $values['day'] = intval($values['day']);

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
        $options['direct'] = true;
        $options['multi'] = false;
        $options['type'] = 'video';
        $options['fileSizeLimit'] = 2048 * 1024;
        $s = '';
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
    $options['direct'] = true;
    $options['multiple'] = false;
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
    $s = '';
    $options['direct'] = false;
    $options['multiple'] = true;
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
    function tpl_form_field_position($field, $value = [])
    {
        $s = '';

        if (!defined('TPL_INIT_COORDINATE')) {
            $s .= '<script type="text/javascript">
                    function showCoordinate(elm) {
                        
                            var val = {};
                            val.lng = parseFloat($(elm).parent().prev().prev().find(":text").val());
                            val.lat = parseFloat($(elm).parent().prev().find(":text").val());
                            val = biz.BdMapToTxMap(val.lat,val.lng);
                            biz.map(val, function(r){
                                var address_label = $("#address_label");
                                if (address_label.length>0)
                                {
                                    address_label.val(r.label);
                                }
                                r = biz.TxMapToBdMap(r.lat,r.lng);
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