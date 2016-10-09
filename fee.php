<include file="include:head" />
<title>报名费</title>
<script type="text/javascript">
    var fee_list_dg,search_key;
    $(function($) {
        fee_list_dg = $("#fee_list_dg").datagrid({
            method:"post",
            border:false,
            idField:'id',
            fit:true,
            queryParams:{act:'bindcardlist',state:0,rows:20},
            //sortName:'registerTime',
            //sortOrder:'asc',
            striped:true,
            rownumbers:true,
            pagination:true,
            singleSelect:true,
            pageSize:20,
            pageList:[10,20,30,40],
            toolbar:'#toolbar',
            onDblClickRow:showFun,
            onRowContextMenu: function(e, rowIndex, rowData){
                e.preventDefault();
                if(rowIndex === -1 || rowData.createtime=='暂无数据') return;
                $(this).datagrid('selectRow',rowIndex);
                $('#onRowContextMenu').menu('show', {
                    left:e.pageX,
                    top:e.pageY
                });
            },
            fitColumns:true,
            columns:[[
                {field:'createtime',title:'入账时间',width:100,sortable:false},
                {field:'bankcard',title:'支付宝交易号|流水号',width:100},
                {field:'bankname',title:'商户订单号',width:120},
                {field:'applyamount',title:'账务类型',width:50,/*formatter:function(value,row,index){
                    if(value<1 && value!=null){
                        value='0'+String(value);
                    }
                    if(value){
                        return parseFloat(value).toFixed(2);
                    }
                    return value;
                }*/},
                {field:'bankcardstate',title:'收支金额(元)',width:80,formatter: function (value,row,index) {
                    value==1?value='已认证':value='未认证';
                    return value;
                }},
                {field:'cardname',title:'账户余额(元)',width:120},
                {field:'checkstate',title:'操作',width:120,formatter:function(value,row,index){
                        switch (value){
                            case '1':value='审核成功';break;
                            case '2':value='审核失败';break;
                            default:value='待审核';
                        }
                        return value;
                    }
                }
            ]],
            onLoadSuccess : function(data){
                $(this).datagrid("unselectAll");
            },
            //onBeforeLoad:function(param){console.log(param)},
            loadFilter: function(data){
                !data.total?data.total=0:0;
                return data;
            },
            loader:function(data,success,error){
                $.ajax({
                    url: '/admin/post', data:data,
                    success: function (reply) {
                        if (!reply.rows) {
                            success({"rows":[{"createtime":'暂无数据'}]});
                            $("#datagrid-row-r1-2-0").parents('table').css('width','100%');
                            $("#datagrid-row-r1-2-0 td[field='createtime']").css('colspan','6').children().css('text-align','center').css('width','100%');
                            $("#datagrid-row-r1-2-0 td[field='bankname']").css('display','none');
                            $("#datagrid-row-r1-2-0 td[field='bankcard']").css('display','none');
                            $("#datagrid-row-r1-2-0 td[field='applyamount']").css('display','none');
                            $("#datagrid-row-r1-2-0 td[field='bankcardstate']").css('display','none');
                            $("#datagrid-row-r1-2-0 td[field='cardname']").css('display','none');
                            $("#datagrid-row-r1-2-0 td[field='checkstate']").css('display','none');
                        }else{
                            success(reply);
                        }
                    }
                })
            }
        });
        search_key = $("#search_key").searchbox({
            searcher:function(value,name){
                var presentState=$('#for_mma').find('.l-btn-text').html();
                switch(presentState){
                    case '审核成功':presentState=1;break;
                    case '待审核':presentState=0;break;
                    case '审核失败':presentState=2;break;
                    default:presentState=-1;
                }
                fee_list_dg.datagrid('load',{act: 'bindcardlist', rows:10, state:presentState, keywords: value})
            },
            prompt:'关键字搜索'
        });
        checkState=function (obj){
            var state=$(obj).find('.menu-text').html();
            $('#for_mma').find('.l-btn-text').html(state);
            switch (state){
                case '审核失败':state=2;break;
                case '待审核':state=0;break;
                case '审核成功':state=1;break;
                default:state=-1;
            }
            var keywords=$('#toolbar table td .textbox .textbox-value').val();
            if(keywords){
                $("#fee_list_dg").datagrid('load',
                    {act:'',state:state,keywords:keywords}      //请求act
                )
            }else{
                $("#fee_list_dg").datagrid('reload',
                    {act:'',state:state}           //请求act
                )
            }
        };
        $('#search_org').combobox({
            loader: myloader,
            mode: 'remote',
            valueField:'id',
            textField:'text',
            value:'全部账务类型'                 //默认值
        });

        $('#fin_type').textbox({                                       //订单名称
            icons:[{iconCls:'icon-search',handler: function(e){
                var thing=$(e.data.target).textbox('getValue');
                alert(thing);
                console.log(thing.charCodeAt());
            }}],
            prompt:'订单名称'
        });
        $('#timeSearchF').datetimebox({
            editable:false,
            showSeconds: false
        });
        $('#timeSearchB').datetimebox({
            editable:false,
            showSeconds: false
        });
        $('#todaySpan').bind('click',function(){
            var now=new Date();
            var hour=now.getHours();
            var min=now.getMinutes();
            var date=now.toLocaleDateString()/*.replace(/\//g,'-')*/;
            $('#timeSearchB').datetimebox('setValue',date+' 23:59');
            $('#timeSearchF').datetimebox('setValue',date+' 00:00')
        });
        $('#yesterdaySpan').bind('click',function(){
            var dateT=GetDateStr(0);
            var dateY=GetDateStr(-1);
            $('#timeSearchF').datetimebox('setValue',dateY+' 00:00');
            $('#timeSearchB').datetimebox('setValue',dateY+' 23:59')
        });
        $('#weekSpan').bind('click',function(){
            var now=new Date();
            var hour=now.getHours();
            var min=now.getMinutes();
            var dateT=GetDateStr(0);
            var dateW=GetDateStr(-7);
            $('#timeSearchF').datetimebox('setValue',dateW+' 00:00');
            $('#timeSearchB').datetimebox('setValue',dateT+' '+23+':'+59)
        });
        $('#monthSpan').bind('click',function(){
            var now=new Date();
            var hour=now.getHours();
            var min=now.getMinutes();
            var dateT=GetDateStr(0);
            var dateM=GetDateStr(-30);
            $('#timeSearchF').datetimebox('setValue',dateM+' 00:00');
            $('#timeSearchB').datetimebox('setValue',dateT+' '+23+':'+59)
        });
    });
    function showFun(rowIndex, rowData)  {
        if(rowData.createtime=='暂无数据'){return}
        /*var dialog = parent.sy.modalDialog({
         title : '登录用户信息',
         url : '/admin/Finance/bankCardBindInfo.php?id='+rowData.id,
         height:400,
         buttons : [
         {text : '关闭',handler : function() {dialog.dialog('destroy');}}
         ]
         });*/
        if(parent.mainTabs.tabs('exists','详细信息查看')){
            var panel = parent.mainTabs.tabs('getTab','详细信息查看').panel('panel');
            var frame = panel.find('iframe');
            frame[0].src='/admin/Finance/fee.php?id='+rowData.id;
            parent.mainTabs.tabs('select','详细信息查看');
        }else{
            parent.mainTabs.tabs('add',{
                title:'报名费查看',
                content:'<iframe src="/admin/Finance/fee.php?id=' + rowData.id+'" allowTransparency="true" scrolling="yes" style="border: 0; width: 100%; height: 100%;" frameBorder="0"></iframe>',
                closable:true
            })
        }
    }
    var editFun = function() {
        if(sy.dg_getRowData(fee_list_dg)==null){
            parent.$.messager.alert('警告','没有选择需要修改的数据');
        }
        else{
            var rowData = sy.dg_getRowData(fee_list_dg);
            var index = fee_list_dg.datagrid('getRowIndex',rowData);
            var dialog = parent.sy.modalDialog({
                title : '修改信息',
                url : '../Finance/fee.php?id='+rowData.id,
                height:456,
                buttons : [{
                    text : '编辑',
                    handler : function() {
                        dialog.find('iframe').get(0).contentWindow.submitForm(dialog, fee_list_dg, parent.$);
                    }
                }]
            });
            /*if(parent.mainTabs.tabs('exists','修改')){
             var panel = parent.mainTabs.tabs('getTab','修改').panel('panel');
             var frame = panel.find('iframe');
             frame[0].src='/admin/Finance/fee.php?id='+rowData.id;
             parent.mainTabs.tabs('select','修改');
             }else{
             parent.mainTabs.tabs('add',{
             title:'修改',
             fit:true,
             content:'<iframe src="/admin/Finance/fee.php?id=' + rowData.id+'" allowTransparency="true" scrolling="yes" style="border: 0; width: 100%; height: 100%;" frameBorder="0"></iframe>',
             closable:true
             })
             }*/
        }
    };
    function showMessage(){
        var rowData = sy.dg_getRowData(fee_list_dg);
        if(rowData==null){
            parent.$.messager.alert('警告','没有选择需要的数据');
        }else{
            /*var dialog = parent.sy.modalDialog({
             title : '消息信息',
             url : '/admin/Finance/feeInfo.php?id=' + rowData.id,
             height:400,
             buttons : [{text:'关闭',handler:function(){dialog.dialog('destroy');}}]
             });*/
            if(parent.mainTabs.tabs('exists','费用信息')){
                var panel = parent.mainTabs.tabs('getTab','费用信息').panel('panel');
                var frame = panel.find('iframe');
                frame[0].src='/admin/Finance/fee.php?id='+rowData.id;
                parent.mainTabs.tabs('select','费用信息');
            }else{
                parent.mainTabs.tabs('add',{
                    title:'费用信息',
                    content:'<iframe src="/admin/Finance/fee.php?id=' + rowData.id+'" allowTransparency="true" scrolling="yes" style="border: 0; width: 100%; height: 100%;" frameBorder="0"></iframe>',
                    closable:true
                })
            }
        }
    }
    function myloader(param,success,error){
        var q = param.q || '';
        if (q.length <= 2){return false}
        $.ajax({
            url: '',               //json地址
            dataType: 'json',
            data: {
                q: q
            },
            success: function(data){
                var items = $.map(data, function(item,index){
                    return {
                        id: index,
                        name: item
                    };
                });
                success(items);
            },
            error: function(){
                error.apply(this, arguments);
            }
        });
    }
    function GetDateStr(AddDayCount) {
        var dd = new Date();
        dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
        var y = dd.getFullYear();
        var m = dd.getMonth()+1;//获取当前月份的日期
        var d = dd.getDate();
        return y+"-"+m+"-"+d;
    }
</script>
<style>
    .state-accept{
        background: url('/__PUBLIC__/syExt/style/images/state-accept.png') no-repeat center center;
    }
    .state-all{
        background: url('/__PUBLIC__/syExt/style/images/state-all.png') no-repeat center center;
    }
    .state-fail{
        background: url('/__PUBLIC__/syExt/style/images/state-fail.png') no-repeat center center;
    }
    .state-wait{
        background: url('/__PUBLIC__/syExt/style/images/state-wait.png') no-repeat center center;
    }
</style>
</head>
<body>
<div id="toolbar" class="hide">
    <table>
        <tr>
            <td><input name="keywords" style="width:200px;" id="search_key" data-options="panelHeight:'auto'"/></td>
            <td><a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'ext-icon-zoom_out',plain:true" onClick="search_key.searchbox('setValue','');fee_list_dg.datagrid('load',{act:'',rows:'10',state:0});$('#for_mma').find('.l-btn-text').html('待审核')">重置搜索</a></td>
            <td>
<!--                <a id="for_mma" href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'ext-icon-newspaper',menu:'#mma',plain:true" >待审核</a>-->
<!--                <div id="mma">-->
<!--                    <div data-options="iconCls:'state-fail'" onclick="checkState(this)">审核失败</div>-->
<!--                    <div data-options="iconCls:'state-wait'" onclick="checkState(this)">待审核</div>-->
<!--                    <div data-options="iconCls:'state-accept'" onclick="checkState(this)">审核成功</div>-->
<!--                    <div data-options="iconCls:'state-all'" onclick="checkState(this)">全部</div>-->
<!--                </div>-->
            </td>
        </tr>
        <tr>
            <td><input name="org_key" style="width:200px;" id="search_org" data-options="panelHeight:'auto'" /></td>
            <td colspan="3"><input id="timeSearchF" style="width: 130px" type="text"> - <input id="timeSearchB" style="width: 130px" type="text"></td>
            <td colspan="2">
                <a id="todaySpan" href="#" class="easyui-linkbutton" data-options="">今天</a>
                <a id="yesterdaySpan" href="#" class="easyui-linkbutton" data-options="">昨天</a>
                <a id="weekSpan" href="#" class="easyui-linkbutton" data-options="">最近7天</a>
                <a id="monthSpan" href="#" class="easyui-linkbutton" data-options="">最近30天</a>
            </td>
        </tr>
        <tr>
            <td>
<!--                订单名称<input id="fin_type" type="text" class="easyui-textbox" data-options="valueField:'id',textField:'text',url:''">-->
                <input id="fin_type" placeholder="订单名称" type="text" style="width: 200px" class="easyui-textbox" >
            </td>
        </tr>
    </table>
</div>
<div id="onRowContextMenu" class="easyui-menu hide">
    <div data-options="iconCls:'icon-edit'" onClick="editFun()">修改</div>
    <div class="menu-sep"></div>
    <div data-options="iconCls:'icon-redo'" onClick="showMessage()">详细信息</div>
</div>
<table id="fee_list_dg" style="width: 100%;"></table>
</body>
</html>
