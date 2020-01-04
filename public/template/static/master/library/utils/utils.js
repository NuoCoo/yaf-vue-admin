var $utils = {
    formatDate:function(fmt, time) {
        time = time ? time*1000 : (new Date()).getTime();
        var date =  new Date(parseInt(time));
        var o = {
            "M+" : date.getMonth()+1,
            "d+" : date.getDate(),
            "h+" : date.getHours(),
            "m+" : date.getMinutes(),
            "s+" : date.getSeconds(),
            "q+" : Math.floor((date.getMonth()+3)/3),
            "S"  : date.getMilliseconds()
        };
        if(/(y+)/.test(fmt))
            fmt=fmt.replace(RegExp.$1, (date.getFullYear()+"").substr(4 - RegExp.$1.length));
        for(var k in o)
            if(new RegExp("("+ k +")").test(fmt))
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length===1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        return fmt;
    },
    randomString:function(len) {
     　　len = len || 32;
     　　var $chars = '2345mnprs678abcdefhij5243kmnpr356stwx8yz234mnprs5678';
     　　var maxPos = $chars.length;
     　　var pwd = '';
     　　for (var i = 0; i < len; i++) {
         　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
         　　}
     　　return pwd;
     },

    createUUID:function () {
        return 'xxxx-xxxx-xxxx-xxxx-xxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    },

    /**
     * 获取指定天数之前的日期。
     * @param {Number} dayNum 指定day天之前的日期。
     */
    getRecentDay:function(dayNum) {
        var today = new Date();
        var targetday_milliseconds = today.getTime() + 1000 * 60 * 60 * 24 * dayNum;
        today.setTime(targetday_milliseconds); //注意，这行是关键代码
        return this.formatDate(today);
    },

    getTodayDate:function(){
        var day = new Date();
        day.setTime(day.getTime());
        return day.getFullYear()+"-" + (day.getMonth()+1) + "-" + day.getDate();
    },
    /***
     * 获得本周起止时间
     */
    getCurrentWeek:function() {
        //起止日期数组
        var startStop = new Array();
        //获取当前时间
        var currentDate = new Date();
        //返回date是一周中的某一天
        var week = currentDate.getDay();
        //返回date是一个月中的某一天
        var month = currentDate.getDate();

        //一天的毫秒数
        var millisecond = 1000 * 60 * 60 * 24;
        //减去的天数
        var minusDay = week != 0 ? week - 1 : 6;
        //alert(minusDay);
        //本周 周一
        var monday = new Date(currentDate.getTime() - minusDay * millisecond);
        //本周 周日
        var sunday = new Date(monday.getTime() + 6 * millisecond);
        //添加本周时间
        startStop.push(monday); //本周起始时间
        //添加本周最后一天时间
        startStop.push(sunday); //本周终止时间
        //返回
        return startStop;
    },

    /**
     * 获取本月起止日期。
     */
    getCurrentMonth:function() {
        //起止日期数组
        var startStop = new Array();
        //获取当前时间
        var currentDate = new Date();
        //获得当前月份0-11
        var currentMonth = currentDate.getMonth();
        //获得当前年份4位年
        var currentYear = currentDate.getFullYear();
        //求出本月第一天
        var firstDay = new Date(currentYear, currentMonth, 1);

        //当为12月的时候年份需要加1
        //月份需要更新为0 也就是下一年的第一个月
        if (currentMonth == 11) {
            currentYear++;
            currentMonth = 0; //就为
        } else {
            //否则只是月份增加,以便求的下一月的第一天
            currentMonth++;
        }

        //一天的毫秒数
        var millisecond = 1000 * 60 * 60 * 24;
        //下月的第一天
        var nextMonthDayOne = new Date(currentYear, currentMonth, 1);
        //求出上月的最后一天
        var lastDay = new Date(nextMonthDayOne.getTime() - millisecond);

        //添加至数组中返回
        startStop.push(firstDay);
        startStop.push(lastDay);
        //返回
        return startStop;
    },

    /**
     * 获取本年起止日期。
     */
    getCurrentYear:function() {

        //起止日期数组
        var startStop = new Array();

        //获取当前时间
        var currentDate = new Date();

        //获得当前年份4位年
        var currentYear = currentDate.getFullYear();

        //本年第一天
        var currentYearFirstDate = new Date(currentYear, 0, 1);

        //本年最后一天
        var currentYearLastDate = new Date(currentYear, 11, 31);

        //添加至数组
        startStop.push(currentYearFirstDate);

        startStop.push(currentYearLastDate);

        //返回
        return startStop;
    }
};

var $library = {
    editor:{},
    vaEditor:function (element, options) {
        var that = this;
        KindEditor.options.filterMode = false;
        KindEditor.ready(function(K) {
            var VaCsrfToken = document.querySelector("#VaCsrfToken").valueOf().defaultValue;
            var AccessToken = document.querySelector("#AccessToken").valueOf().defaultValue;
            var _t = new Date().getTime();
            var uploadParams =  $request.encrypt(JSON.stringify({
                ts:_t,
                token:VaCsrfToken,
                params:{},
            }), $request._k, $request._v);
            that.editor = K.create(element, {
                themeType : 'simple',
                cssPath : '/statics/library/kindeditor/plugins/code/prettify.css',
                uploadJson : '/run/material/kindEditor',
                fileManagerJson : '/statics/library/kindeditor/php/file_manager_json.php',
                allowImageRemote:true,
                autoHeightMode:true,
                allowImageUpload:false,
                allowFileManager:false,
                fillDescAfterUploadImage:true,
                extraFileUploadParams:{_sg:uploadParams, _at: AccessToken},
                minHeight:options.minHeight ? options.minHeight : 150,
                minWidth:options.width ? options.width : '100%',
                items:[
                    'source', 'undo', 'redo',  'preview', 'print', 'template', 'code', 'cut', 'copy',
                    'plainpaste', 'wordpaste',  'justifyleft', 'justifycenter', 'justifyright',
                    'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                    'superscript', 'clearhtml', 'quickformat', 'selectall', 'fullscreen',
                    'formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold',
                    'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat','multiimage',
                   'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                    'anchor', 'link', 'unlink'
                ],

                afterCreate : function() {
                    var self = this;
                },

                afterChange:function () {

                },

                afterUpload:function (res) {
                    console.log(res );
                }
            });
           /* KindEditor.plugin('hello', function(K) {
                var editor = this, name = 'hello';
                editor.clickToolbar(name, function() {
                   $vue.imageDialogOpen('vaEditorPictureOption');
                });
            });
            KindEditor.lang({
                hello : '你好'
            });*/
            prettyPrint();
            if(((typeof(options.callback)) === 'function')){
                var callback = options.callback;
                callback(that.editor);
            }
        });
    },
};

$(function () {
    $(".layouts-header-items").click(function (obj) {
        /*var that = this;
            console.log(e.$attrs.path);
            sessionStorage.setItem('headerMenuActive', that.encryptParams(key));
            sessionStorage.setItem('headerMenuPath', that.encryptParams(e.$attrs.path));
            sessionStorage.setItem('leftMenuOpenIds', that.encryptParams(e.$attrs.parents));
            sessionStorage.setItem('leftMenu', that.encryptParams(e.$attrs.path));

            $("#layouts-container").load($request.href(e.$attrs.path, {}), null,function(){
                that.layouts.loading = false;
            });*/
        
    })
})

