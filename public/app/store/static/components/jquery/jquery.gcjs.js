jQuery.extend({
    isEmail: function (str) {
        return /^(?:\w+\.?)*\w+@(?:\w+\.)+\w+$/.test($.trim(str));
    },
    isIDCard: function (obj) {
        let aCity = {
            11: "北京",
            12: "天津",
            13: "河北",
            14: "山西",
            15: "内蒙古",
            21: "辽宁",
            22: "吉林",
            23: "黑龙 江",
            31: "上海",
            32: "江苏",
            33: "浙江",
            34: "安徽",
            35: "福建",
            36: "江西",
            37: "山东",
            41: "河南",
            42: "湖 北",
            43: "湖南",
            44: "广东",
            45: "广西",
            46: "海南",
            50: "重庆",
            51: "四川",
            52: "贵州",
            53: "云南",
            54: "西 藏",
            61: "陕西",
            62: "甘肃",
            63: "青海",
            64: "宁夏",
            65: "新疆",
            71: "台湾",
            81: "香港",
            82: "澳门",
            91: "国 外"
        };
        let iSum = 0;
        //let info = "";
        let strIDno = obj;
        let idCardLength = strIDno.length;
        if (!/^\d{17}(\d|x)$/i.test(strIDno) && !/^\d{15}$/i.test(strIDno))
            return false; //非法身份证号

        if (aCity[parseInt(strIDno.substr(0, 2))] === null)
            return false;// 非法地区

        // 15位身份证转换为18位
        if (idCardLength === 15) {
            sBirthday = "19" + strIDno.substr(6, 2) + "-" + Number(strIDno.substr(8, 2)) + "-" + Number(strIDno.substr(10, 2));
            let d = new Date(sBirthday.replace(/-/g, "/"));
            let dd = d.getFullYear().toString() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
            if (sBirthday !== dd)
                return false; //非法生日
            strIDno = strIDno.substring(0, 6) + "19" + strIDno.substring(6, 15);
            strIDno = strIDno + GetVerifyBit(strIDno);
        }

        // 判断是否大于2078年，小于1900年
        let year = strIDno.substring(6, 10);
        if (year < 1900 || year > 2078)
            return false;//非法生日

        //18位身份证处理

        //在后面的运算中x相当于数字10,所以转换成a
        strIDno = strIDno.replace(/x$/i, "a");

        sBirthday = strIDno.substr(6, 4) + "-" + Number(strIDno.substr(10, 2)) + "-" + Number(strIDno.substr(12, 2));
        let d = new Date(sBirthday.replace(/-/g, "/"));
        if (sBirthday !== (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate()))
            return false; //非法生日
        // 身份证编码规范验证
        for (let i = 17; i >= 0; i--)
            iSum += (Math.pow(2, i) % 11) * parseInt(strIDno.charAt(17 - i), 11);
        if (iSum % 11 !== 1)
            return false;// 非法身份证号

        // 判断是否屏蔽身份证
        let words = [];
        words = ["11111119111111111", "12121219121212121"];

        for (let k = 0; k < words.length; k++) {
            if (strIDno.indexOf(words[k]) !== -1) {
                return false;
            }
        }

        return true;
    },
    isUrl: function (str) {
        return /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/.test($.trim(str));
    },
    isInt: function (str) {
        return /^[-\+]?\d+$/.test($.trim(str));
    },
    isUserID: function (str) {
        return /^\s*[A-Za-z0-9_-]{6,20}\s*$/.test($.trim(str));
    },
    isMobile: function (str) {
        if (str !== undefined) {
            str = str.replace(/\D/g, ""); /* 替换手机号中的空格*/
        }
        return $.trim(str) !== '' && /^(0|86|17951)?(13[0-9]|15[012356789]|16[6]|19[89]]|17[01345678]|18[0-9]|14[579])[0-9]{8}$/.test(str);
    },
    isChinese: function (str) {
        return $.trim(str) !== '' & !/[^\u4e00-\u9fa5]/.test($.trim(str));
    },
    isEnglish: function (str) {
        return $.trim(str) !== '' & !/[^a-zA-Z]/.test($.trim(str));
    },
    isPassword: function (str) {
        return /^[^\u4e00-\u9fa5\s]{6,20}$/.test($.trim(str));
    },
    isFloat: function (str) {
        return /^(\+|-)?\d+($|\.\d+$)/.test($.trim(str));
    },
    isNumber: function (str) {
        return !$.isEmpty(str) && !isNaN(str);
    },
    isIP: function (str) {
        if (/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/.test($.trim(str))) {
            if (RegExp.$1 < 256 && RegExp.$2 < 256 && RegExp.$3 < 256 && RegExp.$4 < 256)
                return true;
        }
        return false;
    },
    isDate: function (str) {
        let r = $.trim(str).split("-");
        if (r === null)
            return false;
        let d = new Date(r[0], r[1] - 1, r[2]);
        return (d.getFullYear() === r[0] && (d.getMonth() + 1) === r[1] && d.getDate() === r[2]);
    },
    isEmpty: function (str) {
        return $.trim(str) === '' || str === undefined || str === null || str === 0
    },
});
(function ($) {
    $.fn.isInt = function () {
        return $.isInt($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isIDCard = function () {
        return $.isIDCard($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isEnglish = function () {
        return $.isEnglish($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isEmail = function () {
        return $.isEmail($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isDate = function () {
        return $.isDate($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isIP = function () {
        return $.isIP($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isChinese = function () {
        return $.isChinese($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isEmpty = function () {
        return $.isEmpty($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isUrl = function () {
        return $.isUrl($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isFloat = function () {
        return $.isFloat($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isNumber = function () {
        return $.isNumber($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isUserID = function () {
        return $.isUserID($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isPassword = function () {
        return $.isPassword($(this).val());
    }
})(jQuery);
(function ($) {
    $.fn.isMobile = function () {
        return $.isMobile($(this).val());
    }
})(jQuery);