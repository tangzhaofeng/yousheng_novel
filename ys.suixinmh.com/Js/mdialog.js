/*  
 * @弹出提示层 ( 加载动画(load), 提示动画(tip), 成功(success), 错误(error), )  
 * @method  tipBox  
 * @description 默认配置参数   
 * @time    2014-12-19   
 * @param {Number} width -宽度  
 * @param {Number} height -高度         
 * @param {String} str -默认文字  
 * @param {Object} windowDom -载入窗口 默认当前窗口  
 * @param {Number} setTime -定时消失(毫秒) 默认为0 不消失  
 * @param {Boolean} hasMask -是否显示遮罩  
 * @param {Boolean} hasMaskWhite -显示白色遮罩   
 * @param {Boolean} clickDomCancel -点击空白取消  
 * @param {String} lodUrl -请求地址 (只在开启定时消失时才生效)  
 * @param {Function} callBack -回调函数 (只在开启定时消失时才生效)  
 * @param {Function} hasBtn -显示按钮  
 * @param {String} type -动画类型 (加载,成功,失败,提示)  
 * @example   
 * new TipBox();   
 * new TipBox({type:'load',setTime:1000,callBack:function(){ alert(..) }});   
*/
var thisTipBox;
function TipBox(cfg){  
    this.config = {   
        width          : 200,      
        height         : 120,                 
        str            : '正在处理',       
        windowDom      : window,   
        setTime        : 0,     
        hasMask        : true,    
        hasMaskWhite   : false,
        clickDomCancel : false,    
        lodUrl         : null,
		lodData        :{},
        callBack       : null, 
        hasBtn         : false, 
        type           : 'success'  
    }  
    $.extend(this.config,cfg);    
    //存在就retrun  
    if(TipBox.prototype.boundingBox) return;  
    //初始化  
    this.render(this.config.type);    
    return this;   
};  
//外层box  
TipBox.prototype.boundingBox = null;  
//渲染  
TipBox.prototype.render = function(tipType,container){    
    this.renderUI(tipType);   
    //绑定事件  
    this.bindUI();   
    //初始化UI  
    this.syncUI();   
    $(container || this.config.windowDom.document.body).append(TipBox.prototype.boundingBox);     
};  
  
//渲染UI  
TipBox.prototype.renderUI = function(tipType){   
    TipBox.prototype.boundingBox = $("<div id='animationTipBox'></div>");         
    tipType == 'load'    && this.loadRenderUI();  
    tipType == 'success' && this.successRenderUI();   
    tipType == 'error'   && this.errorRenderUI();  
    tipType == 'tip'     && this.tipRenderUI();  
    TipBox.prototype.boundingBox.appendTo(this.config.windowDom.document.body);  
                  
    //是否显示遮罩  
    if(this.config.hasMask){  
        this.config.hasMaskWhite ? this._mask = $("<div class='mask_white'></div>") : this._mask = $("<div class='mask'></div>");  
        this._mask.appendTo(this.config.windowDom.document.body);  
    }     
    // 是否显示按钮
    if(this.config.hasBtn){
        this.config.height = 206;
        $('#animationTipBox').css("margin-top","103px");
        switch(this.config.type){
            case 'success':$(".success").after("<button class='okoButton'>ok</button>");
                break;
            case 'error':$(".lose").after("<button class='okoButton redOkoButton'>ok</button>");
                break;
            case 'tip':$(".tip").after("<button class='okoButton'>ok</button>");
                break;
            default: break;
        }
        $('button.okoButton').on('click',function(){_this.close();});
    }
	thisTipBox = this;
	if(tipType == 'load' && typeof this.config.callBack === "function"){
		// post请求
		this.config.setTime && setTimeout( function(){ _this.close(); }, _this.config.setTime);
		xhr.open("post",this.config.lodUrl,true);
		// 不支持FormData的浏览器的处理 
		if(typeof FormData == "undefined") {
			xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}else{
			xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
		}
		xhr.send(postDataFormat(this.config.lodData));
	}else{
		//定时消失  
		!this.config.setTime && typeof this.config.callBack === "function" && (this.config.setTime = 1);      
		this.config.setTime && setTimeout( function(){ thisTipBox.close(); }, thisTipBox.config.setTime);
	}
};  
  
TipBox.prototype.bindUI = function(){  
    _this = this;             
    //点击空白立即取消  
    this.config.clickDomCancel && this._mask && this._mask.click(function(){_this.close();});                        
};  
TipBox.prototype.syncUI = function(){             
    TipBox.prototype.boundingBox.css({  
        width       : this.config.width+'px',  
        height      : this.config.height+'px',  
        marginLeft  : "-"+(this.config.width/2)+'px',  
        marginTop   : "-"+(this.config.height/2)+'px'  
    });   
};  
  
//提示效果UI  
TipBox.prototype.tipRenderUI = function(){  
    var tip = "<div class='tip'>";  
        tip +="     <div class='icon'>i</div>";  
        tip +="     <div class='dec_txt'>"+this.config.str+"</div>";  
        tip += "</div>";  
    TipBox.prototype.boundingBox.append(tip);  
};  
  
//成功效果UI  
TipBox.prototype.successRenderUI = function(){  
    var suc = "<div class='success'>";  
        suc +=" <div class='icon'>";  
        suc +=      "<div class='line_short'></div>";  
        suc +=      "<div class='line_long'></div>  ";        
        suc +=  "</div>";  
        suc +=" <div class='dec_txt'>"+this.config.str+"</div>";  
        suc += "</div>";  
    TipBox.prototype.boundingBox.append(suc);  
};  
  
//错误效果UI  
TipBox.prototype.errorRenderUI = function(){  
    var err  = "<div class='lose'>";  
        err +=  "   <div class='icon'>";  
        err +=  "       <div class='icon_box'>";  
        err +=  "           <div class='line_left'></div>";  
        err +=  "           <div class='line_right'></div>";  
        err +=  "       </div>";  
        err +=  "   </div>";  
        err +=  "<div class='dec_txt'>"+this.config.str+"</div>";  
        err +=  "</div>";  
    TipBox.prototype.boundingBox.append(err);  
};  
  
//加载动画load UI  
TipBox.prototype.loadRenderUI = function(){  
    var load = "<div class='load'>";  
        load += "<div class='icon_box'>";  
    for(var i = 1; i < 4; i++ ){  
        load += "<div class='cirBox"+i+"'>";  
        load +=     "<div class='cir1'></div>";  
        load +=     "<div class='cir2'></div>";  
        load +=     "<div class='cir3'></div>";  
        load +=     "<div class='cir4'></div>";  
        load += "</div>";  
    }  
    load += "</div>";  
    load += "</div>";  
    load += "<div class='dec_txt'>"+this.config.str+"</div>";  
    TipBox.prototype.boundingBox.append(load);  
};  
  
//关闭  
TipBox.prototype.close = function(){      
    TipBox.prototype.destroy();  
    this.destroy();  
    //this.config.setTime && typeof this.config.callBack === "function" && this.config.callBack();                  
};  
  
//销毁  
TipBox.prototype.destroy = function(){  
    this._mask && this._mask.remove();  
    TipBox.prototype.boundingBox && TipBox.prototype.boundingBox.remove();   
    TipBox.prototype.boundingBox = null;  
};
/*
 * 统一XHR接口
 */
function createXHR() {
    // IE7+,Firefox, Opera, Chrome ,Safari
    if(typeof XMLHttpRequest != "undefined") {
        return new XMLHttpRequest();
    }
    // IE6-
    else if(typeof ActiveXObject != "undefined"){
        if(typeof arguments.callee.activeXString != "string") {
            var versions = ["MSXML2.XMLHttp.6.0", "MSXML2.XMLHttp.3.0", "MSXMLHttp"],
            i, len;
            for(i = 0, len = versions.length; i < len; i++) {
                try{
                    new ActiveXObject(versions[i]);
                    arguments.callee.activeXString = versions[i];
                    break;
                }catch(ex) {
                    alert("请升级浏览器版本");
                }
            }
        }
        return arguments.callee.activeXString;        
    }else {
        throw new Error("XHR对象不可用");
    }
}

var xhr = createXHR();
// 定义xhr对象的请求响应事件
xhr.onreadystatechange = function() {
    switch(xhr.readyState) {
        case 0 :
            //alert("请求未初始化");
            break; 
        case 1 :
            //alert("请求启动，尚未发送");
            break;
        case 2 :
            //alert("请求发送，尚未得到响应");
            break;
        case 3 : 
            //alert("请求开始响应，收到部分数据");
            break;
        case 4 :
			thisTipBox.close();
            if((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304) {
				thisTipBox.config.callBack(xhr.responseText);
            }else {
				thisTipBox.config.callBack(xhr.status + " " + xhr.statusText);
            }
            break;
    }
};
 
// get请求
// get请求添加查询参数
function urlParam(url, name, value) {
    url += (url.indexOf('?') == -1 ) ? '?' : '&' ; 
    url += encodeURIComponent(name) + "=" + encodeURIComponent(value);
    return url;
}
/*
//get请求
url = urlParam("example.php","name","ccb");
url = urlParam(url,"pass","123");
xhr.open("get", url ,true);
xhr.send(null);*/

// post请求
// 格式化post 传递的数据
function postDataFormat(obj,_this){
    if(typeof obj != "object" ) {
        alert("输入的参数必须是对象");
        return;
    }
    // 支持有FormData的浏览器（Firefox 4+ , Safari 5+, Chrome和Android 3+版的Webkit）
    if(typeof FormData == "function") {
        var data = new FormData();
        for(var attr in obj) {
            data.append(attr,obj[attr]);
        }
        return data;
    }else {
        // 不支持FormData的浏览器的处理 
        var arr = new Array();
        var i = 0;
        for(var attr in obj) {
            arr[i] = encodeURIComponent(attr) + "=" + encodeURIComponent(obj[attr]);
            i++;
        }
        return arr.join("&");
    }
}

