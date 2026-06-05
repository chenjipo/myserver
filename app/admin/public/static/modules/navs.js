;layui.define(["element","jquery"],function(exports){
    var element = layui.element,
        $ = layui.$,
        Navs = function(){};
    //生成左侧菜单

    Navs.prototype.navBar = function(topIndex){
        var data;
        layui.menuData.forEach(e => {
            if(Number(e.id) == Number(topIndex)){
                data = e.children
            }
        });
        if(data == undefined) return ulHtml;
        var ulHtml = '';
        let isSpread = false
        for (var i=0;i<data.length;i++){
            if(!isSpread && (data[i].spread || data[i].spread == undefined )){
                ulHtml += '<li class="layui-nav-item layui-nav-itemed">';
                isSpread = true
            }else{
                ulHtml += '<li class="layui-nav-item">';
            }
            ulHtml += '<a href="javascript:;" lay-direction="2">';
            ulHtml += '<i class="layui-icon '+data[i].icon+'"></i>&nbsp;';
            ulHtml += '<cite>'+data[i].display_name+'</cite>';
            ulHtml += '</a>';
            if(data[i].children != undefined && data[i].children.length > 0){
                ulHtml += '<dl class="layui-nav-child">';
                var child = data[i].children;

                for(var j=0;j<child.length;j++){
                    ulHtml+= '<dd data-name="">';
                    ulHtml+= '<a lay-href="'+child[j]['route']+'"><i class="layui-icon '+child[j].icon+'"></i>&nbsp;'+child[j].display_name+'</a>';
                    ulHtml+= '</dd>';
                }
                ulHtml += "</dl>";
            }
            ulHtml += '</li>';
        }


        // for(var i=0;i<data.length;i++){
        //     if(data[i].spread || data[i].spread == undefined){
        //         ulHtml += '<li class="layui-nav-item layui-nav-itemed">';
        //     }else{
        //         ulHtml += '<li class="layui-nav-item">';
        //     }
        //     if(data[i].children != undefined && data[i].children.length > 0){
        //         ulHtml += '<a>';
        //         if(data[i].icon != undefined && data[i].icon != ''){
        //             if(data[i].icon.indexOf("icon-") != -1){
        //                 ulHtml += '<i class="seraph '+data[i].icon+'" data-icon="'+data[i].icon+'"></i>';
        //             }else{
        //                 ulHtml += '<i class="layui-icon" data-icon="'+data[i].icon+'">'+data[i].icon+'</i>';
        //             }
        //         }
        //         ulHtml += '<cite>'+data[i].display_name+'</cite>';
        //         ulHtml += '<span class="layui-nav-more"></span>';
        //         ulHtml += '</a>';
        //         ulHtml += '<dl class="layui-nav-child">';
        //         for(var j=0;j<data[i].children.length;j++){
        //             if(data[i].children[j].target == "_blank"){
        //                 ulHtml += '<dd><a data-url="'+data[i].children[j].route+'" target="'+data[i].children[j].target+'">';
        //             }else{
        //                 ulHtml += '<dd><a data-url="'+data[i].children[j].route+'">';
        //             }
        //             if(data[i].children[j].icon != undefined && data[i].children[j].icon != ''){
        //                 if(data[i].children[j].icon.indexOf("icon-") != -1){
        //                     ulHtml += '<i class="seraph '+data[i].children[j].icon+'" data-icon="'+data[i].children[j].icon+'"></i>';
        //                 }else{
        //                     ulHtml += '<i class="layui-icon" data-icon="'+data[i].children[j].icon+'">'+data[i].children[j].icon+'</i>';
        //                 }
        //             }
        //             ulHtml += '<cite>'+data[i].children[j].display_name+'</cite></a></dd>';
        //         }
        //         ulHtml += "</dl>";
        //     }else{
        //         if(data[i].target == "_blank"){
        //             ulHtml += '<a data-url="'+data[i].route+'" target="'+data[i].target+'">';
        //         }else{
        //             ulHtml += '<a data-url="'+data[i].route+'">';
        //         }
        //         if(data[i].icon != undefined && data[i].icon != ''){
        //             if(data[i].icon.indexOf("icon-") != -1){
        //                 ulHtml += '<i class="seraph '+data[i].icon+'" data-icon="'+data[i].icon+'"></i>';
        //             }else{
        //                 ulHtml += '<i class="layui-icon" data-icon="'+data[i].icon+'">'+data[i].icon+'</i>';
        //             }
        //         }
        //         ulHtml += '<cite>'+data[i].display_name+'</cite></a>';
        //     }
        //     ulHtml += '</li>';
        // }
        return ulHtml;
    }
    //获取二级菜单数据
    Navs.prototype.render = function(topIndex) {
        //显示左侧菜单
        var _this = this;
        $("#LAY-system-side-menu").html(_this.navBar(topIndex));
        element.init();  //初始化页面元素
    }

    var bodyNavs = new Navs();
    exports("navs",bodyNavs);
})
