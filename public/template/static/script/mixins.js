var $layout = {
    data: function () {
        return {
            layouts:{
                // 全局加载事件
                loading:false,
                // 
                collapse:{
                    show: false,
                },
				style:{
					containerMain:{},
					containerMainContent:{},
					dialogFixed:{},
					dialogAuto:{},
				},
                header:{
                    active: '0',
                    menus: [],
                    show: true,
                    style:{},
                },
                
                left:{
                    active: '0',
                    menus: [],
                    show: true,
                    style:{},
                },

                tabs:{
                    active: '0',
                    menus: [],
                    show: true,
                    style:{},
                },
            },
            components:{
				dialogFixed:{dialog:false},
                dialogAuto:{dialog:false},
                import:{
                    dialog:false, status:false,callback:'',
                    result:{},
                    progress:{status:false, percentage:0},
                    options:{accept:'txt,sxd',size:'10'},
                    action:{url:'',callback:''}
                },
			}
        }
    },
    methods: {
        /*计算页面高度*/
        computingWindowHeight: function () {
            var that = this;
			var clientHeight = document.documentElement.clientHeight;
            that.layouts.style = {
				containerMain:{height:(clientHeight - 45)+ 'px'},
				containerMainContent:{minHeight:(clientHeight - 112)+ 'px'},
				dialogFixed:{height:(clientHeight - 115)+ 'px'},
				dialogAuto:{height:parseInt((clientHeight * 0.81) - 150)+ 'px'}
            };
        },

        /*导航关闭 或者 打开 事件*/
        layoutsCollapseEvent:function(){
            var that = this;
            that.layouts.collapse.show = !that.layouts.collapse.show;
            if(that.layouts.left.tabs){

            }
        },

        /*头部菜单的点击事件*/
        layoutsHeaderMenuEvent:function (key, keyPath, e) {
            var that = this;
            var layoutsLoading = that.$loading({lock: false, text: '正在加载页面...', target:'#layouts-container'});
            var menuItems = e.$attrs.item;
            menuItems.source = 'header';
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            var mark = false;
            tabsItems.forEach(function (val, key) {
                if(val.id === menuItems.id){
                    mark = true;
                }
            });
            /*如果不存在tabs中 推送一条新标签*/
            if(mark === false){
                tabsItems.push(menuItems);
                sessionStorage.setItem('tabsItems', JSON.stringify(tabsItems));
            }
            sessionStorage.setItem('headerMenuAction', JSON.stringify(menuItems));

            // 存储新的当前数据
            sessionStorage.setItem('currentItems', JSON.stringify(menuItems));
            if(menuItems.url){
                $("#layouts-container").load(menuItems.url, function(response,status,xhr){
                    console.log(xhr);
                    if(xhr.status === 404){
                        layoutsLoading.close();
                        that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                    } else if(xhr.status === 500){
                        layoutsLoading.close();
                        that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                    } else {
    
                    }
                });
            } else {
                layoutsLoading.close();
                that.getLeftColumn();
            }
        },

        /*左侧菜单的点击事件*/
        layoutsLeftMenuEvent:function(key, keyPath, e){
            var that = this;
            var layoutsLoading = that.$loading({lock: false, text: '正在加载页面...',target:'#layouts-container'});
            var menuItems = e.$attrs.item;
            menuItems.source = 'left';
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            var mark = false;
            tabsItems.forEach(function (val, key) {
                if(val.id === menuItems.id){
                    mark = true;
                }
            });
            /*如果不存在tabs中 推送一条新标签*/
            if(mark === false){
                tabsItems.push(menuItems);
                sessionStorage.setItem('tabsItems', JSON.stringify(tabsItems));
            }
            sessionStorage.setItem('leftMenuAction', JSON.stringify(menuItems));
            sessionStorage.setItem('currentItems', JSON.stringify(menuItems));
            if(menuItems.url){
                $("#layouts-container").load(menuItems.url, function(response,status,xhr){
                    if(xhr.status === 404){
                        layoutsLoading.close();
                        that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                    } else if(xhr.status === 500){
                        layoutsLoading.close();
                        that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                    } else {
    
                    }
                });
            } else {
                layoutsLoading.close();
            } 
        },

        /*tabs 菜单事件触发前的事件*/
        layoutsTabsMenuBeforeEvent:function(e){

        },

        /*tabs 菜单点击事件 */
        layoutsTabsMenuClickEvent:function(e){
            var that = this;
            var layoutsLoading = that.$loading({lock: false, text: '正在加载页面...',target:'#layouts-container'});
            var tabsItems = e.$attrs.item;
            if(tabsItems.source == "header"){
                sessionStorage.setItem('headerMenuAction', JSON.stringify(tabsItems));
            }
            if(tabsItems.source == "left"){
                sessionStorage.setItem('leftMenuAction', JSON.stringify(tabsItems));
            }
            sessionStorage.setItem('currentItems', JSON.stringify(tabsItems));
            $("#layouts-container").load(tabsItems.url, function(response,status,xhr){
                console.log(xhr);
                if(xhr.status === 404){
                    layoutsLoading.close();
                    that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                } else if(xhr.status === 500){
                    layoutsLoading.close();
                    that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                } else {

                }
            });
        },

        /*tabs 菜单关闭事件*/
        layoutsTabsMenuCloseEvent:function(closeIndex){
            var that = this;
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            var tabsItemsMap = JSON.parse(sessionStorage.getItem('tabsItems'));
            var currentItems = JSON.parse(sessionStorage.getItem('currentItems'));
            var closeKey = 0;
            tabsItems.forEach(function (val, key) {
                if(val.id === closeIndex){
                    closeKey = key;
                }
            });
            tabsItems.splice(closeKey, 1);
            sessionStorage.setItem('tabsItems', JSON.stringify(tabsItems));
            that.layouts.tabs.menus = tabsItems;
            if(currentItems.id === tabsItemsMap[closeKey].id){
                if(tabsItemsMap[closeKey - 1]){
                    sessionStorage.setItem('currentItems', JSON.stringify(tabsItemsMap[closeKey-1]));
                    $("#layouts-container").load(tabsItemsMap[closeKey-1].url, function(response,status,xhr){
                        if(xhr.status === 404){
                            layoutsLoading.close();
                            that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                        } else if(xhr.status === 500){
                            layoutsLoading.close();
                            that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                        } else {
        
                        }
                    });
                }
            }
        },

        layoutsTabsDropdownCommand:function(command){
            var that = this;
            var currentItems = JSON.parse(sessionStorage.getItem('currentItems'));
            var tabsItemsMap = JSON.parse(sessionStorage.getItem('tabsItems'));
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            switch (command) {
                case 'close-current':
                    var closeKey = 0;
                    tabsItems.forEach(function (val, key) {
                        if(val.id === currentItems.id){
                            closeKey = key;
                        }
                    });
                    if(closeKey === 0){
                        return true;
                    }
                    tabsItems.splice(closeKey, 1);
                    sessionStorage.setItem('tabsItems', JSON.stringify(tabsItems));
                    that.layouts.tabs.menus = tabsItems;
                    if(tabsItemsMap[closeKey - 1]){
                        sessionStorage.setItem('currentItems', JSON.stringify(tabsItemsMap[closeKey-1]));
                        $("#layouts-container").load(tabsItemsMap[closeKey-1].url, function(response,status,xhr){
                            if(xhr.status === 404){
                                that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                            } else if(xhr.status === 500){
                                that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                            } else {
            
                            }
                        });
                    }
                    break;
                case 'close-all':
                    sessionStorage.removeItem('tabsItems');
                    that.getTabsMenusColumn();
                    break;
                case 'refresh-page':
                    $("#layouts-container").load(currentItems.url, function(response,status,xhr){
                        if(xhr.status === 404){
                            that.$message({message: '网络请求失败，请稍后再试！', type: 'warning',duration:2000});
                        } else if(xhr.status === 500){
                            that.$message({message: '服务器响应错误，请稍后再试！', type: 'warning',duration:2000});
                        } else {
        
                        }
                    });
                    break;
            }
        },

        /*
         *获取头部菜单数据
         *@method getHeaderColumn
         *@for $layout
         *@param {}
         *@return m
        */
        getHeaderColumn:function () {
            var that = this;
            var currentItems = JSON.parse(sessionStorage.getItem('currentItems'));
            var headerMenuItems = JSON.parse(sessionStorage.getItem('headerMenuItems'));
            var headerMenuAction = JSON.parse(sessionStorage.getItem('headerMenuAction'));
            if(headerMenuItems){
                that.layouts.header.menus = headerMenuItems;
                if(headerMenuAction){
                    that.layouts.header.active = headerMenuAction.id;
                }
                return true;
            }
            $request.post('/wmore/framework/getHeaderColumn',{}, function (res) {
                if(res.code === 200 && res.result){
                    sessionStorage.setItem('headerMenuItems', JSON.stringify(res.result));
                    that.layouts.header.menus = res.result;
                    if(headerMenuAction){
                        that.layouts.header.active = headerMenuAction.id;
                    }
                } else {
                    that.layouts.header.show = false;
                    that.layouts.left.show = false;
                    that.layouts.tabs.show = false;
                }
            });
          
           
        },

        /*
         *获取右侧菜单数据
         *@method getLeftColumn
         *@for $layout
         *@param {}
         *@return m
        */
        getLeftColumn:function () {
            var that = this;
            var currentItems = JSON.parse(sessionStorage.getItem('currentItems'));
            if(!currentItems){
                that.layouts.left.show = false;
                return true;
            }
            if(!currentItems.column_id){
                return true;
            }
            
            var leftColumnItems = JSON.parse(sessionStorage.getItem('leftColumn_'+currentItems.column_id));
            if(leftColumnItems){
                that.layouts.left.show = true;
                that.layouts.left.menus = leftColumnItems;
                return true;
            }
            $request.post('/wmore/framework/getLeftColumn', currentItems, function (res) {
                if(res.code === 200 && res.result){
                    sessionStorage.setItem('leftColumn_'+currentItems.column_id, JSON.stringify(res.result));
                    that.layouts.left.show = true;
                    that.layouts.left.menus = res.result; 
                } else {
                    that.layouts.left.show = false;
                }
            });
        },

        getTabsMenusColumn:function () {
            var that = this;
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            if(!tabsItems){
                that.layouts.tabs.menus = [{id:'0', name:'首页', url:'/', icon:'', source:'init'}];
                sessionStorage.setItem('tabsItems', JSON.stringify(that.layouts.tabs.menus));
                sessionStorage.setItem('currentItems', JSON.stringify({id:'0', name:'首页', url:'/', icon:'', source:'init'}));
                that.layouts.header.active = '0';
                that.layouts.left.active = '0';
                that.layouts.tabs.active = '0';
            } else {
                var currentItems =  JSON.parse(sessionStorage.getItem('currentItems'));
                that.layouts.tabs.menus = tabsItems;
                if(!currentItems){
                    that.layouts.header.active = '0';
                    that.layouts.left.active = '0';
                    that.layouts.tabs.active = '0';
                } else {
                    that.layouts.header.active = currentItems.id;
                    that.layouts.left.active = currentItems.id;
                    that.layouts.tabs.active = currentItems.id;
                }
            }
        },

        tablesCellClick:function(row, column, cell, event){
            event.stopPropagation();
            if($(cell).find('.cell').hasClass('Va-table-move')){
                return  false;
            } else {
                $('.cell').removeClass('Va-table-move');
                $('.Va-table-move-close').remove();
                if(!row[column.property]){
                    return false;
                }
                $(cell).find('.cell').append('<i class="el-icon-close Va-table-move-close" onclick="removeTableMove(this)"></i>');
                $(cell).find('.cell').addClass('Va-table-move');
            }
        },




        formBeforeOpen:function(){
            return true;
        },
        formAfterOpen:function(){
            return true;
        },
    },

    mounted: function () {
        var that = this;
        that.computingWindowHeight();
        that.getTabsMenusColumn();
        that.getHeaderColumn();
        that.getLeftColumn();
    }

};

function removeTableMove(obj){
    $(obj).parent().removeClass('Va-table-move');
    $(obj).remove();
}