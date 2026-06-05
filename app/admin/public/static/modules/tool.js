layui.define(['jquery','util','form','laydate','xmSelect'], function (exports) {
    var MOD_NAME = 'tool',
        form = layui.form,
        laydate = layui.laydate,
        xmSelect = layui.xmSelect,
        $ = layui.jquery;

    var Tool = function () {
        this.v = '1.1.0';
    }

    Tool.prototype.toDateString = function(time, format){
        if(time.length == 10){
            time = time + '000'
        }
        return layui.util.toDateString(time, format);
    }
    Tool.prototype.jsonToQueryString = function(params){
        var params = params || {}
        var query = []
        for (const key in params) {
            if (Object.hasOwnProperty.call(params, key)) {
                query.push(key+"="+encodeURIComponent(params[key]));
            }
        }
        return query.join('&')
    }
    Tool.prototype.getQueryString = function(name){
        var url = window.location.href
        var str=url.split('?');
        var query=str[1];
        if(!query) return ''
        var vars=query.split('&');
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == name){
                return pair[1];
            }
        }
        return '';
    }
    //同步
    Tool.prototype.ajaxGetSync = function(url,success,error){
        var success = typeof success == 'function' ? success : function(){};
        var error = typeof error == 'function' ? error : function(){};
        $.ajax({
            url : url,
            type : "get",
            dataType : 'json',
            async : false,
            success : function (result){
                if(result.code == 200){
                    success(result)
                }else if(result.code == 403){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'('+url+')</p>', time: 2000, end:function(){ } });
                }else if(result.code == 301){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'</p>', time: 2000, end:function(){ window.location.href = '/login/?direct='+encodeURIComponent(result.data.direct) } });
                }
            },
            error:function(result){
                if(result.status == 404){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">你访问的地址不存在！！！('+url+')</p>', time: 2000, end:function(){ } });
                }else{
                    error(result)
                }
            }
        });
    }
    Tool.prototype.ajaxGet = function(url,success,error){
        var success = typeof success == 'function' ? success : function(){};
        var error = typeof error == 'function' ? error : function(){};
        $.ajax({
            url : url,
            type : "get",
            dataType : 'json',
            success : function (result){
                if(result.code == 200){
                    success(result)
                }else if(result.code == 403){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'('+url+')</p>', time: 2000, end:function(){ } });
                }else if(result.code == 301){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'</p>', time: 2000, end:function(){ window.location.href = '/login/?direct='+encodeURIComponent(result.data.direct) } });
                }
            },
            error:function(result){
                if(result.status == 404){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">你访问的地址不存在！！！('+url+')</p>', time: 2000, end:function(){ } });
                }else{
                    error(result)
                }
            }
        });
    }
    Tool.prototype.ajaxPost = function(url,data,success,error){
        var data = data || {}
        var success = typeof success == 'function' ? success : function(){};
        var error = typeof error == 'function' ? error : function(){};
        $.ajax({
            url : url,
            type : "post",
            data : data,
            dataType : 'json',
            success : function (result){
                if(result.code == 200){
                    success(result)
                }else if(result.code == 403){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'('+url+')</p>', time: 2000, end:function(){ } });
                }else if(result.code == 301){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">'+result.msg+'</p>', time: 2000, end:function(){ window.location.href = '/login/?direct='+encodeURIComponent(result.data.direct) } });
                }
            },
            error:function(result){
                if(result.status == 404){
                    layer.open({ type: 1, title: false, closeBtn: 0, shadeClose: true, content: '<p style="margin:15px 30px;">你访问的地址不存在！！！('+url+')</p>', time: 2000, end:function(){ } });
                }else{
                    error(result)
                }
            }
        });
    }
    Tool.prototype.getMGameOption = function(params,option){
        var element = (option && option.element) || 'mgid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择主游戏</option>')
        tool.ajaxGetSync('/platform/getMGameOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i]['mgid']] = list[i]
                    select.append('<option value="'+list[i]['mgid']+'"> ['+list[i]['mgid']+ ']-'+list[i]['mgname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.getGameOption = function(params,option){
        var element = (option && option.element) || 'gid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择游戏</option>')
        tool.ajaxGetSync('/platform/getGameOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i]['gid']] = list[i]
                    select.append('<option value="'+list[i]['gid']+'"> ['+list[i]['gid']+ ']-'+list[i]['gname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.getChannelOption = function(params,option){
        var element = (option && option.element) || 'cid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择渠道</option>')
        tool.ajaxGetSync('/platform/getChannelOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i]['cid']] = list[i]
                    select.append('<option value="'+list[i]['cid']+'"> ['+list[i]['cid']+ ']-'+list[i]['cname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.renderAdLink = function(option){
        var element = (option && option.element) || 'linkid'
        var width = (option && option.width) || '174px'
        var pageSize = (option && option.pageSize) || 20
        var _this = this;
        xmSelect.render({
            el: '#'+element, 
            tips: '请选择推广链',
            //配置搜索
            filterable: true,
            //延迟搜索
            delay: 1000,
            //配置远程分页
            paging: true,
            pageRemote: true,
            pageSize: pageSize,
            size:'mini',
            style:{
                width:width
            },
            toolbar: {
                show: true,
                showIcon: false,
                list: [ 'ALL', 'CLEAR', 'REVERSE' ]
            },
            model: {
                label: {
                    type: 'name', //自定义与下面的对应
                    name: {
                        template(data,sels){
                            return "<span style='font-size:12px'>已选择 " + sels.length + "条推广链</span>"
                        }
                    },
                }
            },
            //数据处理
            remoteMethod: function(val, cb, show, pageIndex){
                //val: 搜索框的内容, 不开启搜索默认为空, cb: 回调函数, show: 当前下拉框是否展开, pageIndex: 当前第几页
                var params = {
                    keyword: val,
                    page: pageIndex,
                    limit: pageSize
                }
                var queryString = _this.jsonToQueryString(params)
                tool.ajaxGet('/mk/getAdLinkOption?'+queryString,function(result){
                    if(result.state){
                        var list = result.data.list;
                        var dataArr = [];
                        for(var i in list){
                            dataArr.push({value:list[i]['linkid'],name:'['+list[i]['linkid']+']'+list[i]['lname']})
                        }
                        cb(dataArr, result.data.total)
                    }else{
                        cb([], 0);
                    }
                });
            }
        })
    },
    Tool.prototype.getAdAccountOption = function(params,option){
        var element = (option && option.element) || 'accid'
        var multi = (option && option.multi) ? true : false
        var width = (option && option.width) || '174px'
        var queryString = this.jsonToQueryString(params)
        var map;
        if(multi){
            tool.ajaxGetSync('/mk/getAdAccountOption?'+queryString,function(result){
                map = {}
                if(result.state){
                    var list = result.data;
                    var dataArr = [];
                    for(var i in list){
                        dataArr.push({value:list[i][element],name:'['+list[i][element]+']'+list[i]['accname']})
                        map[list[i][element]] = list[i]
                    }
                    xmSelect.render({
                        el: "#"+element,
                        name: element,
                        tips: '请选择账户',
                        filterable: true,
                        style:{
                            width:width
                        },
                        toolbar: {
                            show: true,
                            showIcon: false,
                            list: [ 'ALL', 'CLEAR', 'REVERSE' ]
                        },
                        size:'mini',
                        height: '300px',
                        model: {
                            label: {
                                type: 'name', //自定义与下面的对应
                                name: {
                                    template(data,sels){
                                        return "<span style='font-size:12px'>已选择 " + sels.length + "个账户</span>"
                                    }
                                },
                            }
                        },
                        data: dataArr,
                    });
                }
            })
        }else{
            tool.ajaxGetSync('/mk/getAdAccountOption?'+queryString,function(result){
                $('select[name='+element+']').html('<option value="">选择投放账户</option>')
                map = {}
                if(result.state){
                    var list = result.data
                    var select = $('select[name='+element+']');
                    for(var i in list){
                        map[list[i]['accid']] = list[i]
                        select.append('<option value="'+list[i]['accid']+'"> ['+list[i]['accid']+ ']-'+list[i]['accname']+'</option>');
                    }
                    form.render('select');
                }
            })
        }
        
        return map
    }
    Tool.prototype.getPitcherOption = function(params,option){
        var element = (option && option.element) || 'pitid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择投放人员</option>')
        tool.ajaxGetSync('/mk/getPitcherOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i][element]] = list[i]
                    select.append('<option value="'+list[i][element]+'"> ['+list[i][element]+ ']-'+list[i]['pitname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.getAgentOption = function(params,option){
        var element = (option && option.element) || 'agtid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择代理</option>')
        tool.ajaxGetSync('/mk/getAgentOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i][element]] = list[i]
                    select.append('<option value="'+list[i][element]+'"> ['+list[i][element]+ ']-'+list[i]['agtname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.getCompanyOption = function(params,option){
        var element = (option && option.element) || 'cpnid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择主体</option>')
        tool.ajaxGetSync('/mk/getCompanyOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i][element]] = list[i]
                    select.append('<option value="'+list[i][element]+'"> ['+list[i][element]+ ']-'+list[i]['cpnname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.getPartnerOption = function(params,option){
        var element = (option && option.element) || 'pid'
        var queryString = this.jsonToQueryString(params)
        var map;
        $('select[name='+element+']').html('<option value="">选择联运商</option>')
        tool.ajaxGetSync('/platform/getPartnerOption?'+queryString,function(result){
            map = {}
            if(result.state){
                var list = result.data
                var select = $('select[name='+element+']');
                for(var i in list){
                    map[list[i][element]] = list[i]
                    select.append('<option value="'+list[i][element]+'"> ['+list[i][element]+ ']-'+list[i]['pname']+'</option>');
                }
                form.render('select');
            }
        })
        return map
    }
    Tool.prototype.renderBetweenDate = function(option){
        var elem = $('#'+option.elem);
        var name = option.name || 'date';
        var input = '<input type="text" placeholder="日期" class="layui-input" name="'+name+'" id="'+name+'" value="" lay-filter='+name+' autocomplete="off"/><input type="hidden" name="s'+name+'" value=""><input type="hidden" name="e'+name+'" value="">';
        //定义接收本月的第一天和最后一天
        var startDate1 = new Date(new Date().setDate(1));
        var endDate1 = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);
        //定义接收上个月的第一天和最后一天
        var startDate2 = new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1);
        var endDate2 = new Date(new Date().setDate(0));
        elem.append(input);
        laydate.render({
            elem: '#'+name,
            type: 'date',
            range: '-',
            format: 'yyyy-MM-dd',
            value: option.value,
            extrabtns: [
                {id:'today', text:'今天', range:[new Date(), new Date()]},
                {id:'yesterday', text:'昨天', range:[new Date(new Date().setDate(new Date().getDate()-1)),
                new Date(new Date().setDate(new Date().getDate()-1))]},
                {id:'lastday-7', text:'过去7天', range:[new Date(new Date().setDate(new Date().getDate()-7)),
                new Date(new Date().setDate(new Date().getDate()-1))]},
                {id:'lastday-30', text:'过去30天', range:[new Date(new Date().setDate(new Date().getDate()-30)),
                new Date(new Date().setDate(new Date().getDate()-1))]},
                {id:'thismonth', text:'本月', range:[startDate1,endDate1]},
                {id:'lastmonth', text:'上个月', range:[startDate2,endDate2]}
            ],
            done: function(val, stdate, ovdate){
                //当确认选择时间后调用这里
                var arr = val.split(' - ');
                $('input[name=s'+name+']').val(arr[0]);
                $('input[name=e'+name+']').val(arr[1]);
            }
        });
    }
    ,Tool.prototype.keepTwoDecimalFull = function(num){
        var result = parseFloat(num);
        if(isNaN(result)){
            return 0.00
        }
        result = Math.round(num*100)/100;
        var s_x = result.toString();//将数字转化为字符串
        var pos_decimal = s_x.indexOf('.');//小数点的索引值
        //当整数时，pos_decimal=-1自动补0
        if(pos_decimal<0){
            pos_decimal = s_x.length
            s_x+='.'
        }

        //当数字的长度<小数点的索引+2时，补0
        while(s_x.length<=pos_decimal+2){
            s_x += '0'
        }

        return s_x;
    }

    var tool = new Tool();

    exports(MOD_NAME, tool);
})