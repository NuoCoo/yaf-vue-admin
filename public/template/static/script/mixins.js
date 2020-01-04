var $layout = {
    data: function () {
        return {
            layouts:{
                loading:false,
                collapse:{
                    show: false,
                },
                containerMainStyle:{
                    height:'450px',
                },
                containerMainBoxStyle:{},
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
        }
    },
    methods: {

        /*计算页面高度*/
        computingWindowHeight: function () {
            var that = this;
            that.layouts.containerMainStyle = {
                'height': (document.documentElement.clientHeight - 45) + 'px',
            };
            that.layouts.containerMainBoxStyle = {
                'minHeight': (document.documentElement.clientHeight - 112) + 'px',
            };
            console.log(that.layouts.containerMainStyle);
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
           // that.$loading({lock: false, text: '正在加载页面...',target:'#layouts-container'});
            console.log('fasdfads');
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
            sessionStorage.setItem('currentItems', JSON.stringify(menuItems));
            $("#layouts-container").load('/index/forms');
        },

        /*左侧菜单的点击事件*/
        layoutsLeftMenuEvent:function(key, keyPath, e){
            var that = this;
          //  that.$loading({lock: false, text: '正在加载页面...',target:'#layouts-container'});
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
            sessionStorage.setItem('currentItems', JSON.stringify(menuItems));
            $("#layouts-container").load('/index/forms');
        },

        /*tabs 菜单事件触发前的事件*/
        layoutsTabsMenuBeforeEvent:function(e){

        },

        /*tabs 菜单点击事件*/
        layoutsTabsMenuClickEvent:function(e){
            var that = this;
           // that.$loading({lock: false, text: '正在加载页面...',target:'#layouts-container'});
            var tabsItems = e.$attrs.item;
            sessionStorage.setItem('currentItems', JSON.stringify(tabsItems));
            $("#layouts-container").load('/index/forms');
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
                    $("#layouts-container").load('/index/forms');
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
                        $("#layouts-container").load('/index/forms');
                    }
                    break;
                case 'close-all':
                    sessionStorage.removeItem('tabsItems');
                    that.getTabsMenusColumn();
                    break;
                case 'refresh-page':
                    $("#layouts-container").load('/index/forms');
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
            var headerMenuItems = JSON.parse(sessionStorage.getItem('headerMenuItems'));
            if(headerMenuItems){
                that.layouts.header.menus = headerMenuItems;
                that.layouts.left.menus = headerMenuItems;
               return true;
            }
            $request.post('/app/index/getHeaderColumn',{}, function (res) {
                if(res.code === 200 && res.result){
                    sessionStorage.setItem('headerMenuItems', JSON.stringify(res.result));
                    sessionStorage.setItem('leftMenuItems', JSON.stringify(res.result));
                    that.layouts.header.menus = res.result;
                    that.layouts.left.menus = res.result;
                } else {
                    that.layouts.header.show = false;
                    that.layouts.left.show = false;
                    that.layouts.tabs.show = false;
                }
            });
        },

        getTabsMenusColumn:function () {
            var that = this;
            var tabsItems = JSON.parse(sessionStorage.getItem('tabsItems'));
            if(!tabsItems){
                that.layouts.tabs.menus = [{id:'0', name:'首页', url:'/', icon:'', source:'init'}];
                sessionStorage.setItem('tabsItems',  JSON.stringify(that.layouts.tabs.menus));
                sessionStorage.setItem('currentItems',  JSON.stringify({id:'0', name:'首页', url:'/', icon:'', source:'init'}));
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
                    //$("#layouts-container").load('/index/forms');
                }
            }
        }
    },

    mounted: function () {
        var that = this;
        that.getHeaderColumn();
        that.computingWindowHeight();
        that.getTabsMenusColumn();
    }

};