window.$apps = {
    host: 'http://admin.nuocoo.com/',
    version: '1.0.0',
    request:{
        key:'5B9ADC14C705F1B041DDC2D9B16A2D94',
        iv:'33092152342590AD',
        encrypt:false,
    },
};


window.$utils = {
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
        var targetDay_milliseconds = today.getTime() + 1000 * 60 * 60 * 24 * dayNum;
        today.setTime(targetDay_milliseconds); //注意，这行是关键代码
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
    },

    random: function (length){
        length = length ? length : 16;
        var result='';
        for(var i=0;i<length;i++){
            result+=Math.floor(Math.random()*16).toString(16);
        }
        return result.toUpperCase();
    },
    encrypt:function(str, KEY, IV) {
        let key = CryptoJS.enc.Utf8.parse(KEY);
        let iv = CryptoJS.enc.Utf8.parse(IV);
        let encrypted = CryptoJS.AES.encrypt(str, key, {
            iv: iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        });
        return encrypted.toString();
    },

    decrypt(str, KEY, IV) {
        let key = CryptoJS.enc.Utf8.parse(KEY);
        let iv = CryptoJS.enc.Utf8.parse(IV);
        let decrypted = CryptoJS.AES.decrypt(str, key, {
            iv: iv,
            padding: CryptoJS.pad.Pkcs7
        });
        return decrypted.toString(CryptoJS.enc.Utf8);
    },
};


window.$request = {
    post: function (url, data, response) {
        if($apps.request.encrypt){
            this.decryptRequest(url, data, response);
        } else {
            this.formRequest(url, data, response);
        }
    },

    /**
     * get
     * get请求
     *
     * @param url string 请求地址
     * @param data json 请求参数
     * @param disposable bool 是否是一次性请求
     * @access public
     * @since 1.0
     * @return void
     */
    get: function (url, data, disposable) {
        if($apps.request.encrypt){
            var formData = new Array();
            var sign = {
                token:$request.getRequestToken(),
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_s'] = $request.encrypt(JSON.stringify(sign), $apps.request.key, $apps.request.iv);
            window.location.href = url+'?'+$request.getQueryString(formData);
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:(new Date()).getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $apps.request.key, $apps.request.iv);
            window.location.href =  url+'?'+$request.getQueryString(data);
        }
    },

    /**
     * open
     * 新窗口打开链接
     *
     * @param url string 请求地址
     * @param data json 请求参数
     * @param disposable bool 是否是一次性请求
     * @access public
     * @since 1.0
     * @return void
     */
    open: function (url, data, disposable) {
        if($apps.request.encrypt){
            var formData = new Array();
            var sign = {
                token:$request.getRequestToken(),
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_s'] = $request.encrypt(JSON.stringify(sign), $apps.request.key, $apps.request.iv);
            window.open(url+'?'+$request.getQueryString(formData));
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:(new Date()).getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $apps.request.key, $apps.request.iv);
            window.open(url+'?'+$request.getQueryString(data));
        }
    },

    /**
     * href
     * 获取请求链接
     *
     * @param url string 请求地址
     * @param data json 请求参数
     * @param disposable bool 是否是一次性请求
     * @access public
     * @since 1.0
     * @return string
     */
    href: function (url, data, disposable) {
        if($apps.request.encrypt){
            var formData = new Array();
            var sign = {
                token:$request.getRequestToken(),
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_s'] = $request.encrypt(JSON.stringify(sign), $apps.request.key, $apps.request.iv);
           return  url+'?'+$request.getQueryString(formData);
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:(new Date()).getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $apps.request.key, $apps.request.iv);
            return url+'?'+$request.getQueryString(data);
        }
    },

    /**
     * load
     * load 加载页面
     *
     * @param url string 请求地址
     * @param data json 请求参数
     * @param disposable bool 是否是一次性请求
     * @access public
     * @since 1.0
     * @return string
     */
    load:function(url, data, disposable){
        $NuoCoo.layouts.loading = true;
        if($apps.request.encrypt){
            var formData = new Array();
            var sign = {
                token:$request.getRequestToken(),
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_s'] = $request.encrypt(JSON.stringify(sign), $apps.request.key, $apps.request.iv);
            $("#layouts-container").load(url+'?'+$request.getQueryString(formData), {}, null,function(response,status,xhr){
                if(xhr.status === 404){
                    $NuoCoo.layouts.loading = false;
                    $NuoCoo.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:1600});
                } else if(xhr.status === 500){
                    $NuoCoo.layouts.loading = false;
                    $NuoCoo.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:1600});
                } else {

                }
            });
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:(new Date()).getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $apps.request.key, $apps.request.iv);
            $("#layouts-container").load(url+'?'+$request.getQueryString(data), null, function(response,status,xhr){
                if(xhr.status === 404){
                    $NuoCoo.layouts.loading = false;
                    $NuoCoo.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:1600});
                } else if(xhr.status === 500){
                    $NuoCoo.layouts.loading = false;
                    $NuoCoo.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:1600});
                } else {

                }
            });
        }
    },

    decryptRequest:function(url, data, response){
        var iv = $utils.random();
        var key = $utils.random(32);
        var _t = (new Date()).getTime();
        var field = $utils.random(8);
        var encrypt = $utils.encrypt(JSON.stringify({
            ts: _t, k:key,t:'345245243',u:url,f:field
        }), $apps.request.key, iv);
        var formData = new Array();
        formData[field] = $utils.encrypt(JSON.stringify(data), key, $utils.request.iv);

        $request._httpRequest({
            url: url,
            data:formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Request-V':iv,
                'Request-S':encrypt,
                'Request-E':true
            },
            response:function (res, status, header) {
                /*错误码 400*/
                if(status > 399 && status < 500){
                    // window.location = '/404?msg=页面未找到';
                    return false;
                }
                /*错误码 500*/
                else if(status >= 500 ){
                    //window.location = '/404?msg=页面未找到';
                    return false;
                }
                else {
                    if(!res){
                        $NuoCoo.$message({message: '网络错误', type: 'error'});
                        return false;
                    }
                    if(res.code === 302){
                        window.location = '/run/passport/login'
                    } else {
                        if(!header.sign || !header.iv){
                            $NuoCoo.$message({message: '网络错误', type: 'error'});
                            return false;
                        }
                        var sign = JSON.parse($utils.decrypt(header.sign, $apps.request.key, header.iv));
                        if(!sign || !sign.key){
                            $NuoCoo.$message({message: '网络错误', type: 'error'});
                            return false;
                        }
                        if(res.result){
                            res.result = JSON.parse($utils.decrypt(res.result, sign.key, $apps.request.iv));
                        }
                        response(res);
                    }
                }
            }
        })
    },

    formRequest:function(url, data, response){
        var iv = $utils.random();
        var _t = (new Date()).getTime();
        var encrypt = $utils.encrypt(JSON.stringify({
            ts: _t, k:iv,t:'345245243',u:url,f:'forms'
        }), $apps.request.key, iv);
        $request._httpRequest({
            url: url,
            data:data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Request-V':iv,
                'Request-S':encrypt,
                'Request-E':false
            },
            response:function (res, status, header) {
                /*错误码 400*/
                if(status > 399 && status < 500){
                    // window.location = '/404?msg=页面未找到';
                    return false;
                }
                /*错误码 500*/
                else if(status >= 500 ){
                    //window.location = '/404?msg=页面未找到';
                    return false;
                }
                else {
                    if(!res){
                        $NuoCoo.$message({message: '网络错误', type: 'error'});
                        return false;
                    }
                    if(res.code === 302){
                        window.location = '/run/passport/login'
                    } else {
                        response(res);
                    }
                }
            }
        })
    },

    _httpRequest:function(settings) {
        var _s = Object.assign({
            url: window.location.href,
            type: 'POST',
            dataType: 'json',
            async: true,
            data: null,
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            timeout: 0,
            encrypt:true,
            beforeSend: function(xhr){

            },

            success: function(result, status, xhr){

            },
            error: function(xhr, status, error) {
                $NuoCoo.$message({message: '网络错误', type: 'error'});
            },

            response: function(xhr, status){

            }
        }, settings);
        if(!_s.url){
            _s.url = window.location.href
        }
        // 参数验证
        if (!_s.url || !_s.type || !_s.dataType || !_s.async) {
            $NuoCoo.$message({message: '网络错误', type: 'error'});
            return;
        }
        // 创建XMLHttpRequest请求对象
        var xhr = new XMLHttpRequest();
        // 请求开始回调函数
        xhr.addEventListener('loadstart', function (e) {
            _s.beforeSend(xhr);
        });
        // 请求成功回调函数
        xhr.addEventListener('load', function (e) {
            var status = xhr.status;
            if ((status >= 200 && status < 300) || status === 304) {
                var result;
                if (xhr.responseType === 'text') {
                    result = xhr.responseText;
                } else if (xhr.responseType === 'document') {
                    result = xhr.responseXML;
                } else {
                    result = xhr.response;
                }
                _s.success(result, status, xhr);
            } else {
                _s.error(xhr, status, e);
            }
        });
        // 请求结束
        xhr.addEventListener('loadend', function (e)  {
            var result;
            if (xhr.responseType === 'text') {
                result = xhr.responseText;
            } else if (xhr.responseType === 'document') {
                result = xhr.responseXML;
            } else {
                result = xhr.response;
            }
            if(_s.encrypt){
                var iv = xhr.getResponseHeader('iv');
                var sign = xhr.getResponseHeader('sign');
                var timestamp = xhr.getResponseHeader('timestamp');
                _s.response(result, xhr.status, {iv:iv,timestamp:timestamp,sign:sign});
            } else {
                _s.response(result, xhr.status);
            }
        });
        // 请求出错
        xhr.addEventListener('error', function (e)  {
            _s.error(xhr, xhr.status, e);
        });
        // 请求超时
        xhr.addEventListener('timeout', function (e)  {
            _s.error(xhr, 408, e);
        });
        var useUrlParam = false;
        var sType = _s.type.toUpperCase();
        // 如果是"简单"请求,则把data参数组装在url上
        if (sType === 'GET' || sType === 'DELETE') {
            useUrlParam = true;
            _s.url += $request.getUrlParam(_s.url, _s.data);
        }
        xhr.open(_s.type, _s.url, _s.async);
        xhr.responseType = _s.dataType;
        var headers_keys = Object.keys(_s.headers);
        // 设置请求头
        headers_keys.forEach(function(value,index,array){
            xhr.setRequestHeader(value, _s.headers[value]);
        });
        if (_s.async && _s.timeout) {
            xhr.timeout = _s.timeout;
        }
        xhr.send(useUrlParam ? null : $request.getQueryData(_s.data));
    },

    getRequestToken:function(){
        return 'token';
    },

    getUrlParam: function(url, data) {
        if (!data) {
            return '';
        }
        var paramsStr = data instanceof Object ? $request.getQueryString(data) : data;
        return (url.indexOf('?') !== -1) ? paramsStr : '?' + paramsStr;
    },

    getQueryData: function(data) {
        if (!data) {
            return null;
        }
        if (typeof data === 'string') {
            return data;
        }
        if (data instanceof FormData) {
            return data;
        }
        return $request.getQueryString(data);
    },

    getQueryString: function(data){
        var paramsArr = [];
        if (data instanceof Object) {
            Object.keys(data).forEach(function (key) {
                var val = data[key];
                paramsArr.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
            });
        }
        return paramsArr.join('&');
    },
};