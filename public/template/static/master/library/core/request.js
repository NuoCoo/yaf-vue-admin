var $request = {
    post: function (url, data, response) {
        if($Config.request.encrypt){
            this.decryptRequest(url, data, response);
        } else {
            this.formRequest(url, data, response);
        }
    },

    decryptRequest:function(url, data, response){
        var VaCsrfToken = document.querySelector("#VaCsrfToken").valueOf().defaultValue;
        var VaCurrentRoute = document.querySelector("#VaCurrentRoute").valueOf().defaultValue;
        var iv = $request.random();
        var key = $request.random(32);
        var _t = new Date().getTime();
        var index = $request.random(8);
        var encrypt = $request.encrypt(JSON.stringify({ts: _t, k:key, t:VaCsrfToken,i:index,r:VaCurrentRoute}), $Config.request.key, iv);
        var formData = new Array();
        formData[index] = $request.encrypt(JSON.stringify(data), key, $Config.request.iv);
        formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
        $request.httpRequest({
            url: url,
            data:formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'sign-V':iv,
                'sign-S':encrypt
            },
            response:function (res, status, header) {
                console.log(header);
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
                        $vue.$message({message: '网络错误', type: 'error'});
                        return false;
                    }
                    if(res.code === 302){
                        window.location = '/run/passport/login'
                    } else {
                        if(!header.sign || !header.iv){
                            $vue.$message({message: '网络错误', type: 'error'});
                            return false;
                        }
                        var sign = JSON.parse($request.decrypt(header.sign,$Config.request.key,header.iv));
                        if(!sign || !sign.key){
                            $vue.$message({message: '网络错误', type: 'error'});
                            return false;
                        }
                        if(res.result){
                            res.result = JSON.parse($request.decrypt(res.result, sign.key, $Config.request.iv));
                        }
                        response(res);
                    }
                }
            }
        })
    },

    formRequest:function(url, data, response){
        $request.httpRequest({
            url: url,
            data:data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Content-encrypt':false
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
                        $vue.$message({message: '网络错误', type: 'error'});
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
        if($Config.request.encrypt){
            var formData = new Array();
            var sign = {
                token:document.querySelector("#VaCsrfToken").valueOf().defaultValue,
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_sg'] = $request.encrypt(JSON.stringify(sign), $Config.request.key, $Config.request.iv);
            formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
            window.location.href =  url+'?'+$request.getQueryString(formData);
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:new Date().getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $Config.request.key, $Config.request.iv);
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
        if($Config.request.encrypt){
            var formData = new Array();
            var sign = {
                token:document.querySelector("#VaCsrfToken").valueOf().defaultValue,
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_sg'] = $request.encrypt(JSON.stringify(sign), $Config.request.key, $Config.request.iv);
            formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
            window.open(url+'?'+$request.getQueryString(formData));
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:new Date().getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $Config.request.key, $Config.request.iv);
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
        if($Config.request.encrypt){
            var formData = new Array();
            var sign = {
                token:document.querySelector("#VaCsrfToken").valueOf().defaultValue,
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_sg'] = $request.encrypt(JSON.stringify(sign), $Config.request.key, $Config.request.iv);
            formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
            return url+'?'+$request.getQueryString(formData);
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:new Date().getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $Config.request.key, $Config.request.iv);
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
        $vue.layouts.loading = true;
        if($Config.request.encrypt){
            var formData = new Array();
            var sign = {
                token:document.querySelector("#VaCsrfToken").valueOf().defaultValue,
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_sg'] = $request.encrypt(JSON.stringify(sign), $Config.request.key, $Config.request.iv);
            formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
            $("#layouts-container").load(url+'?'+$request.getQueryString(formData), {}, null,function(response,status,xhr){
                if(xhr.status === 404){
                    $vue.layouts.loading = false;
                    $vue.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                } else if(xhr.status === 500){
                    $vue.layouts.loading = false;
                    $vue.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                } else {

                }
                // console.log(url+'?'+$request.getQueryString(formData));
                // history.pushState(stateObject, '', url+'?'+$request.getQueryString(formData));
            });
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:new Date().getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $Config.request.key, $Config.request.iv);
            $("#layouts-container").load(url+'?'+$request.getQueryString(data), null,function(response,status,xhr){
                if(xhr.status === 404){
                    $vue.layouts.loading = false;
                    $vue.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                } else if(xhr.status === 500){
                    $vue.layouts.loading = false;
                    $vue.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                } else {
                    
                }
                
                // console.log(url+'?'+$request.getQueryString(data));
                // history.pushState(stateObject, '', url+'?'+$request.getQueryString(data));
            });
        }
    },

    /**
     * getCurrentRoute
     * 获取当前页面路由
     *
     * @param url string 请求地址
     * @param data json 请求参数
     * @param disposable bool 是否是一次性请求
     * @access public
     * @since 1.0
     * @return string
     */
    getCurrentRoute:function(){
        var currentRoute = $("#VaCurrentRoute").val();
        console.log(currentRoute);
        return ;
        if($Config.request.encrypt){
            var formData = new Array();
            var sign = {
                token:document.querySelector("#VaCsrfToken").valueOf().defaultValue,
                ts:new Date().getTime(),
                ds:disposable ? 1 : 0,
                params:data ? data :{},
            };
            formData['_sg'] = $request.encrypt(JSON.stringify(sign), $Config.request.key, $Config.request.iv);
            formData['_at'] = document.querySelector("#AccessToken").valueOf().defaultValue;
            $("#layouts-container").load(url+'?'+$request.getQueryString(formData), {}, null,function(response,status,xhr){
                // console.log(url+'?'+$request.getQueryString(formData));
                // history.pushState(stateObject, '', url+'?'+$request.getQueryString(formData));
            });
        } else {
            data._at = $request.encrypt(JSON.stringify({
                ts:new Date().getTime(),
                e:0,
                ds:disposable ? 1 : 0,
            }), $Config.request.key, $Config.request.iv);
            $("#layouts-container").load(url+'?'+$request.getQueryString(data), null,function(response,status,xhr){
                // console.log(url+'?'+$request.getQueryString(data));
                // history.pushState(stateObject, '', url+'?'+$request.getQueryString(data));
            });
        }
    },
    




    httpRequest:function(settings)  {
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
                $vue.$message({message: '网络错误', type: 'error'});
            },

            response: function(xhr, status){

            }
        }, settings);
        if(!_s.url){
            _s.url = window.location.href
        }
        // 参数验证
        if (!_s.url || !_s.type || !_s.dataType || !_s.async) {
            $vue.$message({message: '网络错误', type: 'error'});
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

};
