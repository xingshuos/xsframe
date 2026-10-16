/**
 * lodop-print.js
 * ---------------------------------------------------------------------------
 * 与 python_work/douyin (C-Lodop 打印客户端 index.html) 完全一致的 LODOP 打印/预览逻辑。
 * 本文件为「框架无关」实现：直接读取设计器画布 #postera 中的 .drag 元素（与 index.js
 * 的 getDynamicData 读取方式一致），用与 index.js lodopPrintCreate 完全相同的参数
 * 构建 LODOP 打印任务，从而保证「预览窗口 / 实际打印」的布局、样式、输出效果两边完全一致。
 *
 * 唯一差异：index.html 的纸张尺寸 / 打印机 / 打印方向 / 字体 由用户在界面选择（存入设置）；
 * 本文件在 post.html（模板编辑页）中统一使用「系统默认值」——即由调用方传入 paperWidth /
 * paperHeight（取自模板自身的宽高输入框），其余（方向、打印机、字体）使用与 index.js 一致的默认值。
 * ---------------------------------------------------------------------------
 */
(function (global) {
    'use strict';

    var LODOP;                       // LODOP 实例（全局）
    var AMPLIFY_FACTOR = 320;        // 设计器画布放大系数，与 index.js 完全一致
    var DEFAULT_FONT = 'Microsoft YaHei';

    /* ============================ 默认颜色 / 默认文本 ============================ */

    function getDefaultColor(type) {
        var defaultColors = {
            'ordersn': '#ff0000', 'paytime': '#ff0000', 'product_name': '#ff0000',
            'nickname': '#ff0000', 'realname': '#ff0000', 'mobile': '#ff0000',
            'address': '#ff0000', 'price': '#ff0000', 'total': '#ff0000',
            'remark': '#ff0000', 'option': '#ff0000', 'amount': '#ff0000',
            'status': '#ff0000', 'is_refund': '#ff0000', 'serial_number': '#ff0000',
            'buyer_total': '#ff0000', 'tvanchor_name': '#ff0000', 'user_identifier': '#ff0000',
            'neworold': '#ff0000', 'filed_2': '#ff0000', 'buy_total': '#ff0000',
            'is_black': '#ff0000', 'buyer_sn': '#ff0000'
        };
        return defaultColors[type] || '#000000';
    }

    function getDefaultText(type) {
        var defaultTexts = {
            'text': '文字内容',
            'ordersn': '[A:4974027494100602097]',
            'paytime': '[B:2025/07/06 18:30:30]',
            'product_name': '[C:预售确认款盲盒手办拍卖]',
            'nickname': '[D:mynickname]',
            'realname': '[E:realname]',
            'mobile': '[F:15888888888]',
            'address': '[G:浙江省杭州市西湖区]',
            'price': '[H:9.9]',
            'total': '[I:1]',
            'remark': '[J:买家留言]',
            'option': '[K:5号位]',
            'amount': '[L:9.9]',
            'status': '[M:待发货]',
            'is_refund': '[退]',
            'serial_number': '[O:1]',
            'buyer_total': '[P:1]',
            'tvanchor_name': '[Q:禾擎快打单]',
            'user_identifier': '[R:1/5]',
            'neworold': '[S:店铺新客]',
            'filed_2': '[来源:抖音]',
            'buy_total': '[T:新人首单]',
            'is_black': '[黑]',
            'buyer_sn': '[V:95645]'
        };
        return defaultTexts[type] || '文本内容';
    }

    /* ============================ 表格相关默认值 ============================ */

    var TABLE_FIELD_OPTIONS = [
        {label: '商品名称', value: 'product_name'},
        {label: '规格', value: 'option'},
        {label: '数量', value: 'total'},
        {label: '单价', value: 'price'},
        {label: '金额', value: 'amount'},
        {label: '订单号', value: 'ordersn'},
        {label: '备注', value: 'remark'},
        {label: '状态', value: 'status'}
    ];

    function getDefaultPrintTableConfig() {
        return {
            headerHeight: 22,
            rowHeight: 34,
            fontSize: 12,
            borderColor: '#333333',
            headerBg: '#f5f7fa',
            columns: [
                {title: '商品', field: 'product_name', width: 130},
                {title: '规格', field: 'option', width: 90},
                {title: '数量', field: 'total', width: 48},
                {title: '金额', field: 'amount', width: 62}
            ]
        };
    }

    function getTableFieldLabel(field) {
        for (var i = 0; i < TABLE_FIELD_OPTIONS.length; i++) {
            if (TABLE_FIELD_OPTIONS[i].value === field) return TABLE_FIELD_OPTIONS[i].label;
        }
        return '';
    }

    /* ============================ 通用工具函数 ============================ */

    function safeCssColor(color, fallback) {
        if (!color) return fallback;
        var value = String(color).trim();
        if (/^#[0-9a-fA-F]{3,8}$/.test(value) || /^rgba?\([0-9\s,%.]+\)$/.test(value)) {
            return value;
        }
        return fallback;
    }

    function escapeHtml(value) {
        if (value === undefined || value === null) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatCentAmount(value) {
        if (value === undefined || value === null || value === '') return '';
        var num = Number(value);
        if (isNaN(num)) return value;
        return (num / 100).toFixed(2);
    }

    function extractSkuText(row) {
        if (!row) return '';
        if (Array.isArray(row.properties)) {
            for (var i = 0; i < row.properties.length; i++) {
                var prop = row.properties[i];
                if (prop && prop.key === 'sku_spec' && prop.text) return prop.text;
            }
        }
        if (Array.isArray(row.sku_spec)) {
            return row.sku_spec.map(function (spec) {
                return (spec.name || '') + (spec.value ? ':' + spec.value : '');
            }).join(' ');
        }
        return '';
    }

    // 统一把 px/mm 等字号单位换算为 pt，保证屏幕预览与 LODOP 打印视觉一致
    function normalizePrintFontSize(sizeValue, fallbackPx) {
        if (!sizeValue) return (parseFloat(fallbackPx || 12) * 0.75) + 'pt';
        var str = String(sizeValue).trim();
        var num = parseFloat(str);
        if (isNaN(num)) return (parseFloat(fallbackPx || 12) * 0.75) + 'pt';
        if (str.endsWith('pt')) return num + 'pt';
        if (str.endsWith('mm')) return (num * 2.83465) + 'pt';
        return (num * 0.75) + 'pt';
    }

    function normalizeCanvasFontSize(sizeValue) {
        var num = parseFloat(String(sizeValue == null ? '15' : sizeValue));
        if (isNaN(num) || num <= 0) return '15px';
        return num + 'px';
    }

    function parseTableConfig(value) {
        if (!value) return getDefaultPrintTableConfig();
        try {
            return normalizePrintTableConfig(JSON.parse(decodeURIComponent(value)));
        } catch (error) {
            try {
                return normalizePrintTableConfig(JSON.parse(value));
            } catch (e) {
                return getDefaultPrintTableConfig();
            }
        }
    }

    function getTableConfigFromElement(obj) {
        if (!obj || !obj.length) return getDefaultPrintTableConfig();
        return parseTableConfig(obj.attr('table'));
    }

    function normalizePrintTableConfig(config) {
        var defaults = getDefaultPrintTableConfig();
        var raw = (config && typeof config === 'object') ? config : {};
        var sourceColumns = (Array.isArray(raw.columns) && raw.columns.length > 0) ? raw.columns : defaults.columns;
        var columns = sourceColumns.map(function (column, index) {
            var fallback = defaults.columns[index] || defaults.columns[0];
            var field = column.field || fallback.field || 'product_name';
            return {
                title: column.title || getTableFieldLabel(field) || fallback.title || ('列' + (index + 1)),
                field: field,
                width: Math.max(28, parseInt(column.width || fallback.width || 60))
            };
        });

        return {
            headerHeight: Math.max(14, parseInt(raw.headerHeight || defaults.headerHeight)),
            rowHeight: Math.max(18, parseInt(raw.rowHeight || defaults.rowHeight)),
            fontSize: Math.max(8, parseInt(raw.fontSize || defaults.fontSize)),
            borderColor: safeCssColor(raw.borderColor, defaults.borderColor),
            headerBg: safeCssColor(raw.headerBg, defaults.headerBg),
            columns: columns
        };
    }

    function getPrintTableRows(orderItem) {
        if (!orderItem) {
            return [{
                product_name: '预售确认款盲盒手办拍卖',
                option: '5号位',
                total: '1',
                price: '9.90',
                amount: '9.90',
                remark: '买家留言',
                status: '待发货'
            }];
        }

        var possibleLists = [
            orderItem.product_items, orderItem.product_item, orderItem.goods,
            orderItem.products, orderItem.items, orderItem.order_items
        ];

        for (var i = 0; i < possibleLists.length; i++) {
            if (Array.isArray(possibleLists[i]) && possibleLists[i].length > 0) {
                return possibleLists[i].map(function (item) {
                    var merged = {};
                    for (var k in orderItem) merged[k] = orderItem[k];
                    for (var j in item) merged[j] = item[j];
                    return merged;
                });
            }
        }
        return [orderItem];
    }

    function getPrintTableFieldText(row, field, orderItem) {
        var source = row || {};
        var fallback = orderItem || {};
        var value = '';

        switch (field) {
            case 'product_name':
                value = source.product_name || source.name || source.title || fallback.product_name || '';
                break;
            case 'option':
                value = source.option || source.sku_name || source.sku || extractSkuText(source) || fallback.option || '';
                break;
            case 'total':
                value = source.total || source.combo_num || source.number || source.quantity || source.count || fallback.total || '';
                break;
            case 'price':
                value = source.price || formatCentAmount(source.price_amount) || formatCentAmount(source.total_price) || fallback.price || '';
                break;
            case 'amount':
                value = source.amount || formatCentAmount(source.pay_amount) || formatCentAmount(source.total_amount) || fallback.amount || fallback.price || '';
                break;
            default:
                value = (source[field] !== undefined && source[field] !== null) ? source[field] : (fallback[field] || '');
        }
        return value;
    }

    /* ============================ 表格 HTML 构建（与 index.js buildPrintTableHtml 一致） ============================ */

    function buildPrintTableHtml(element, orderItem, printUnit, designerMode, contentScale) {
        var tableConfig = normalizePrintTableConfig(element.table || element);
        var columns = tableConfig.columns;

        var MIN_COLUMN_WIDTH = {
            product_name: 80, option: 50, total: 30, amount: 40, price: 40, remark: 50, status: 50
        };
        var effectiveColumns = columns.map(function (column) {
            var min = MIN_COLUMN_WIDTH[column.field] || 40;
            return {
                field: column.field,
                title: column.title,
                effectiveWidth: Math.max(min, parseInt(column.width) || min)
            };
        });
        var totalWidth = effectiveColumns.reduce(function (sum, column) {
            return sum + column.effectiveWidth;
        }, 0) || 1;

        var fontFamily = DEFAULT_FONT;
        var fontStack = "'" + fontFamily + "','Microsoft YaHei','SimHei','SimSun','Arial Unicode MS',sans-serif";
        var pxToCss = function (val) {
            return Math.max(1, Math.round(parseFloat(val || 0) * contentScale)) + 'px';
        };
        var fontSize = printUnit && printUnit.fontSize
            ? normalizePrintFontSize(printUnit.fontSize, tableConfig.fontSize)
            : pxToCss(tableConfig.fontSize);
        var headerHeight = printUnit && printUnit.headerHeight
            ? normalizePrintFontSize(printUnit.headerHeight, tableConfig.headerHeight)
            : pxToCss(tableConfig.headerHeight);
        var rowHeight = printUnit && printUnit.rowHeight
            ? normalizePrintFontSize(printUnit.rowHeight, tableConfig.rowHeight)
            : pxToCss(tableConfig.rowHeight);
        var borderColor = safeCssColor(tableConfig.borderColor, '#333333');
        var headerBg = safeCssColor(tableConfig.headerBg, '#f5f7fa');
        var rows = getPrintTableRows(orderItem);

        var elemHeightPx = parseInt(element.height, 10) || 0;
        var headerPx = parseInt(tableConfig.headerHeight, 10) || 24;
        var rowPx = parseInt(tableConfig.rowHeight, 10) || 24;
        var maxRows = Infinity;
        var truncated = false;
        if (elemHeightPx > 0) {
            var availPx = elemHeightPx - headerPx - 2;
            maxRows = Math.max(1, Math.floor(availPx / rowPx));
            truncated = rows.length > maxRows;
        }
        var dataMax = truncated ? Math.max(0, maxRows - 1) : maxRows;
        var displayRows = truncated ? rows.slice(0, dataMax) : rows;

        var cellBaseStyle = [
            'position:relative', 'box-sizing:border-box',
            'border:1px solid ' + borderColor, 'padding:0',
            'overflow:hidden', 'vertical-align:middle'
        ].join(';');

        var textBaseStyle = [
            'box-sizing:border-box', 'width:100%', 'padding:2px 4px',
            'overflow:hidden', 'word-break:normal', 'word-wrap:break-word',
            'overflow-wrap:break-word', 'white-space:normal',
            'letter-spacing:0', 'word-spacing:0', 'line-height:1.25'
        ].join(';');

        var columnsHtml = effectiveColumns.map(function (column) {
            var percent = (column.effectiveWidth / totalWidth * 100).toFixed(6);
            return '<col style="width:' + percent + '%;">';
        }).join('');

        var headerCells = effectiveColumns.map(function (column, index) {
            var resizeHandle = (designerMode && index < effectiveColumns.length - 1)
                ? '<span class="table-col-resize" data-col-index="' + index + '"></span>' : '';
            return '<th class="print-table-cell print-table-head-cell" data-col-index="' + index + '" style="' + cellBaseStyle + ';height:auto;min-height:' + headerHeight + ';font-weight:bold;text-align:center;background:' + headerBg + ';"><div style="display:flex;align-items:center;justify-content:center;' + textBaseStyle + ';min-height:' + headerHeight + ';max-height:' + headerHeight + ';overflow:hidden;height:auto;font-family:' + fontStack + ';font-size:' + fontSize + ';line-height:1.2;">' + escapeHtml(column.title) + resizeHandle + '</div></th>';
        }).join('');

        var bodyRows = displayRows.map(function (row) {
            var cells = effectiveColumns.map(function (column, index) {
                var text = getPrintTableFieldText(row, column.field, orderItem);
                return '<td class="print-table-cell" data-col-index="' + index + '" style="' + cellBaseStyle + ';height:auto;min-height:' + rowHeight + ';"><div style="' + textBaseStyle + ';min-height:' + rowHeight + ';max-height:' + rowHeight + ';overflow:hidden;height:auto;font-family:' + fontStack + ';font-size:' + fontSize + ';">' + escapeHtml(text) + '</div></td>';
            }).join('');
            return '<tr class="print-table-row" style="height:auto;min-height:' + rowHeight + ';overflow:hidden;">' + cells + '</tr>';
        }).join('');

        var noteRow = truncated
            ? '<tr class="print-table-row print-table-note-row" style="height:auto;min-height:' + rowHeight + ';overflow:hidden;"><td colspan="' + effectiveColumns.length + '" style="' + cellBaseStyle + ';height:auto;min-height:' + rowHeight + ';"><div style="display:flex;align-items:center;justify-content:center;' + textBaseStyle + ';min-height:' + rowHeight + ';max-height:' + rowHeight + ';overflow:hidden;font-family:' + fontStack + ';font-size:' + fontSize + ';color:#909399;text-align:center;">↓ 还有 ' + (rows.length - dataMax) + ' 项商品未列出（为保证单页打印已省略）</div></td></tr>'
            : '';

        return '<div class="print-table-wrap" style="width:100%;height:auto;box-sizing:border-box;overflow:hidden;font-family:' + fontStack + ';font-size:' + fontSize + ';color:#303133;background:transparent;letter-spacing:0;word-spacing:0;-webkit-print-color-adjust:exact;print-color-adjust:exact;"><table class="print-table" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;table-layout:fixed;border-collapse:collapse;border-spacing:0;box-sizing:border-box;letter-spacing:0;word-spacing:0;"><colgroup>' + columnsHtml + '</colgroup><thead><tr class="print-table-header" style="height:auto;min-height:' + headerHeight + ';overflow:hidden;">' + headerCells + '</tr></thead><tbody class="print-table-body">' + bodyRows + noteRow + '</tbody></table></div>';
    }

    /* ============================ 像素 -> 毫米 ============================ */

    function calcPx2Mm(size, widthRatio) {
        var numStr = String(size);
        if (numStr.slice(-2) === 'px') numStr = numStr.slice(0, -2);
        var realSize = parseFloat(numStr) / widthRatio;
        realSize = Math.ceil(realSize.toFixed(2) * 100) / 100; // 向上取舍并保留2位小数
        return realSize + 'mm';
    }

    /* ============================ 获取打印文本内容（预览时 orderItem 为空） ============================ */

    function getPrintText(orderItem, textType, defaultText) {
        var text = '';
        if (orderItem) {
            switch (textType) {
                case 'text': text = defaultText; break;
                case 'barcode': text = orderItem['ordersn'] || defaultText; break;
                case 'qr': text = orderItem['ordersn'] || defaultText; break;
                case 'is_refund': text = orderItem['is_refund'] === '退' ? '退' : ''; break;
                case 'is_black': text = parseInt(orderItem['is_black']) === 1 ? '黑' : ''; break;
                case 'buy_total': text = (parseInt(orderItem['buy_total']) === 1 && orderItem['neworold'] === '店铺新客') ? '新人首单' : ''; break;
                default: text = orderItem[textType] || '';
            }
        } else {
            if (textType === 'barcode') {
                text = '1234567890';
            } else if (textType === 'qr') {
                text = '1234567890';
            } else {
                text = defaultText;
            }
        }
        return text;
    }

    /* ============================ 读取画布元素（与 index.js getDynamicData 一致） ============================ */

    function getDynamicData(container) {
        var data = [];
        if (typeof $ === 'undefined') return data;
        var $container = $(container);
        $container.find('.drag').each(function () {
            var obj = $(this);
            var type = obj.attr('type');
            var left = obj.css('left');
            var top = obj.css('top');
            var d = {
                left: left, top: top,
                type: type,
                width: obj.css('width'),
                height: obj.css('height'),
                weight: obj.attr('weight') || '',
                align: obj.attr('align') || 'left'
            };

            var TEXT_TYPES = ['text', 'ordersn', 'paytime', 'product_name', 'nickname', 'realname',
                'mobile', 'address', 'price', 'total', 'remark', 'option', 'amount', 'status',
                'is_refund', 'serial_number', 'buyer_total', 'tvanchor_name', 'user_identifier',
                'neworold', 'buy_total', 'is_black', 'buyer_sn', 'filed_2'];

            if (TEXT_TYPES.indexOf(type) !== -1) {
                d.size = obj.attr('size') || '15';
                d.color = obj.attr('color') || getDefaultColor(type) || '#000';
                d.weight = obj.attr('weight') || '';
                d.align = obj.attr('align') || 'left';
                d.text = obj.attr('text');
                if (!d.text || d.text === 'undefined' || d.text === undefined || d.text === null) {
                    d.text = getDefaultText(type);
                }
            } else if (type === 'qr') {
                d.size = obj.attr('size');
            } else if (type === 'img' || type === 'thumb') {
                d.src = obj.attr('src');
            } else if (type === 'table') {
                var tableConfig = getTableConfigFromElement(obj);
                d.table = tableConfig;
                d.size = tableConfig.fontSize + 'px';
            }
            data.push(d);
        });
        return data;
    }

    /* ============================ 构建 LODOP 任务（与 index.js lodopPrintCreate 一致） ============================ */

    function getLodopInstance() {
        if (!LODOP && typeof getLodop === 'function') {
            LODOP = getLodop();
        }
        return LODOP;
    }

    function buildLodopJob(opts) {
        opts = opts || {};
        var paperWidth = parseFloat(opts.paperWidth) || 40;
        var paperHeight = parseFloat(opts.paperHeight) || 30;
        // 系统默认方向：0 = 方向不定（由打印机缺省决定），与 index.js settings.print_orientation 默认值一致
        var orientation = (opts.orientation != null) ? parseInt(opts.orientation, 10) : 0;
        var fontFamily = opts.fontFamily || '';
        var title = opts.title || '打印预览';
        var container = opts.container || '#postera';

        var data = getDynamicData(container) || [];

        LODOP = getLodopInstance();
        if (!LODOP) {
            throw new Error('未获取到 LODOP 实例，请确认已安装并启动 Lodop 打印控件。');
        }
        LODOP.PRINT_INIT(title);

        var finalPaperWidth = parseInt((paperWidth * 10).toFixed(0));
        var finalPaperHeight = parseInt((paperHeight * 10).toFixed(0));
        var paperSizeName = paperWidth + 'X' + paperHeight;

        // 设置纸张尺寸 - 使用实际的毫米尺寸
        LODOP.SET_PRINT_PAGESIZE(orientation, finalPaperWidth, finalPaperHeight, paperSizeName);

        // 关键设置：禁止自动分页；允许宽高扩展以避免内容被截断
        LODOP.SET_PRINT_MODE("PRINT_NOCOLLATE", 1);      // 禁止自动分页
        LODOP.SET_PRINT_MODE("FULL_WIDTH_FOR_OVERFLOW", 1);  // 允许宽度扩展，防止文字横向被裁
        LODOP.SET_PRINT_MODE("FULL_HEIGHT_FOR_OVERFLOW", 0); // 0=超出元素高度的内容直接裁剪，不再生成第二页
        LODOP.SET_PRINT_MODE("AUTO_CLOSE_PREWINDOW", 1);    // 打印后自动关闭预览

        // 打印质量设置：关闭“HTML转图像”，使用LODOP原生文本渲染以保持字间距与浏览器预览一致
        LODOP.SET_PRINT_MODE("PRINT_DEFINITION", 600); // 高分辨率打印
        LODOP.SET_PRINT_MODE("ANTIALIASING_MODE", 2); // 高级抗锯齿
        LODOP.SET_PRINT_MODE("PRINT_HTM_AS_IMAGE", 0); // 0=原生文本渲染，避免图像缩放导致字间距/列宽变化
        LODOP.SET_PRINT_STYLE("FontSmoothing", 2);    // 字体平滑处理

        // 设置打印机：优先使用用户在界面手动选择的打印机（printerIndex 或 printerName），
        // 未选择时回退到索引 0（系统默认打印机），不再由页面/后端强制指定默认打印机。
        var printerProvided = (opts.printerIndex != null && opts.printerIndex !== '');
        var printer = printerProvided
            ? parseInt(opts.printerIndex, 10)
            : (opts.printerName != null && opts.printerName !== '' ? opts.printerName : 0);
        LODOP.SET_PRINTER_INDEX(printer);

        // 设计器画布使用 amplifyFactor/paperWidth 的独立 px 比例；LODOP HTML 引擎固定按 96dpi 解析 px。
        // contentScale 把“设计器 px”换算为“LODOP 渲染 px”，保证字体、行高、表格等内部尺寸与画布预览严格等比例。
        var widthRatio = parseFloat((AMPLIFY_FACTOR / paperWidth).toFixed(3));
        var contentScale = parseFloat(((paperWidth / AMPLIFY_FACTOR) * (96 / 25.4)).toFixed(4));

        LODOP.SET_PRINT_STYLEA(0, "PreventPageBreak", true); // 禁止分页

        for (var i = 0; i < data.length; i++) {
            var element = data[i];
            var elementTop = calcPx2Mm(element.top, widthRatio);
            var elementLeft = calcPx2Mm(element.left, widthRatio);
            var elementWidth = calcPx2Mm(element.width, widthRatio);
            var elementHeight = calcPx2Mm(element.height, widthRatio);
            var elementText = getPrintText(null, element.type, element.text);

            if (element.type === 'table') {
                var tableConfig = normalizePrintTableConfig(element.table || element);
                var tableHtml = buildPrintTableHtml(element, null, null, false, contentScale);
                LODOP.ADD_PRINT_HTM(elementTop, elementLeft, elementWidth, elementHeight, tableHtml);
                LODOP.SET_PRINT_STYLEA(0, "PreventPageBreak", true);
            } else if (element.type === 'qr') {
                var qrDataUrl = '';
                try {
                    var tempDiv = document.createElement('div');
                    document.body.appendChild(tempDiv);
                    new QRCode(tempDiv, {
                        text: elementText,
                        width: 150,
                        height: 150,
                        correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 0
                    });
                    var qrCanvas = tempDiv.querySelector('canvas');
                    if (qrCanvas) {
                        qrDataUrl = qrCanvas.toDataURL('image/png');
                    }
                    document.body.removeChild(tempDiv);
                } catch (e) {
                    console.error('QR code generation failed:', e);
                }
                if (qrDataUrl) {
                    var qrHtml = '<img src="' + qrDataUrl + '" style="width:' + elementWidth + ';height:' + elementHeight + ';object-fit:contain;" />';
                    LODOP.ADD_PRINT_HTM(elementTop, elementLeft, elementWidth, elementHeight, qrHtml);
                }
            } else if (element.type === 'barcode') {
                LODOP.ADD_PRINT_BARCODE(elementTop, elementLeft, elementWidth, elementHeight, "128Auto", elementText);
                LODOP.SET_PRINT_STYLEA(0, "ShowBarText", 0);
            } else if (element.type === 'img' || element.type === 'thumb') {
                var imgSrc = element.src || '';
                if (imgSrc && !/^(data:|https?:|file:|blob:)/i.test(imgSrc)) {
                    try { imgSrc = new URL(imgSrc, location.href).href; } catch (e) { imgSrc = ''; }
                }
                if (imgSrc) {
                    LODOP.ADD_PRINT_IMAGE(elementTop, elementLeft, elementWidth, elementHeight, imgSrc);
                }
            } else {
                var elementSize = (parseFloat(element.size || '12') * contentScale) + 'px';
                var fontStack = fontFamily
                    ? "'" + fontFamily + "','Microsoft YaHei','SimHei','SimSun','Arial Unicode MS',sans-serif"
                    : "'SimSun','Microsoft YaHei','SimHei','Arial Unicode MS',sans-serif";
                var html = '<div style="width:' + elementWidth + ';overflow:hidden;box-sizing:border-box;font-family:' + fontStack + ';font-size:' + elementSize + ';font-weight:' + element.weight + ';color:' + element.color + ';text-align:' + element.align + ';line-height:1.25;word-break:normal;word-wrap:break-word;white-space:normal;-webkit-print-color-adjust: exact;print-color-adjust: exact;letter-spacing: 0;word-spacing: 0;">' + elementText + '</div>';
                LODOP.ADD_PRINT_HTM(elementTop, elementLeft, elementWidth, elementHeight, html);
            }
        }
    }

    /* ============================ 打印机枚举（供界面手动选择） ============================ */

    // 获取当前已安装的打印机列表：[{index:0, name:'xxx'}, ...]
    function getPrinters() {
        LODOP = getLodopInstance();
        if (!LODOP || typeof LODOP.GET_PRINTER_COUNT !== 'function') return [];
        var count = LODOP.GET_PRINTER_COUNT() || 0;
        var list = [];
        for (var i = 0; i < count; i++) {
            var name = '';
            try {
                name = LODOP.GET_PRINTER_NAME(i) || ('打印机 ' + (i + 1));
            } catch (e) {
                name = '打印机 ' + (i + 1);
            }
            list.push({index: i, name: name});
        }
        return list;
    }

    // 获取系统默认打印机名称（LODOP.GET_PRINTER_NAME(-1) 返回默认打印机）
    function getDefaultPrinterName() {
        LODOP = getLodopInstance();
        if (!LODOP || typeof LODOP.GET_PRINTER_NAME !== 'function') return '';
        try {
            return LODOP.GET_PRINTER_NAME(-1) || '';
        } catch (e) {
            return '';
        }
    }

    /* ============================ 对外接口 ============================ */

    function preview(opts) {
        try {
            buildLodopJob(opts);
            LODOP.PREVIEW();
        } catch (e) {
            var msg = (e && e.message) ? e.message : e;
            if (typeof layer !== 'undefined') {
                layer.alert('打印预览失败：' + msg + '\n请确认已安装并启动 Lodop 打印控件（C-Lodop）。');
            } else {
                alert('打印预览失败：' + msg + '\n请确认已安装并启动 Lodop 打印控件（C-Lodop）。');
            }
            console.error(e);
        }
    }

    function print(opts) {
        try {
            buildLodopJob(opts);
            LODOP.PRINT();
        } catch (e) {
            var msg = (e && e.message) ? e.message : e;
            if (typeof layer !== 'undefined') {
                layer.alert('打印失败：' + msg);
            } else {
                alert('打印失败：' + msg);
            }
            console.error(e);
        }
    }

    global.HqLodopPrint = {
        preview: preview,
        print: print,
        build: buildLodopJob,
        getPrinters: getPrinters,
        getDefaultPrinterName: getDefaultPrinterName
    };

})(window);
