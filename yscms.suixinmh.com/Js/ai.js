$(function(){
	// TAB多栏选卡
	var tab = $(".J_tab .tab-nav li");
	tab.click(function(){
		$(this).addClass('on').siblings().removeClass();
        $('.J_tab_con').eq(tab.index(this)).show().siblings().hide();
    });
	
	//全选 反选
	$(".J_checkAll").click(function(){
		var name = this.id.substring(0,this.id.length-3);
		if(this.checked){
			$("#"+name).find("input[name='"+name+"']").each(function(){this.checked=true;});
		}else{
			$("#"+name).find("input[name='"+name+"']").each(function(){this.checked=false;});
		}
		checkAll(name);
	});
	$(".J_check").click(function() {
		var name = $(this).parents().parents().parents().attr('id');
		checkAll(name);
	});
	function checkAll(name){
		var list = document.getElementsByClassName("J_checkAll");
		for(var i=0;i<list.length;i++){
		   if(list[i].id == name+"All" || list[i].id == name+"Alb"){
			   list[i].checked=($("#"+name).find("input[name='"+name+"']").length == $("#"+name).find("input[name='"+name+ "']:checked").length ? true : false);
		   }
		}
	}
	
	//开关按钮
	$("#no").click(function(){
		var parent = $(this).parents('.no-off');
		$('#off',parent).removeClass('btn-no').addClass('btn-off');
		$('#no',parent).removeClass('btn-off').addClass('btn-no');
		$('#open',parent).attr('checked', true);
		$('#shut',parent).attr('checked', false);
	});
	$("#off").click(function(){
		var parent = $(this).parents('.no-off');
		$('#no',parent).removeClass('btn-no').addClass('btn-off');
		$('#off',parent).removeClass('btn-off').addClass('btn-no');
		$('#shut',parent).attr('checked', true);
		$('#open',parent).attr('checked', false);
	});

	// 数量加减
	$ (".J_num .jian").click (function (){
		var me = $ (this), txt = me.next (":text");
		var val = parseFloat (txt.val ());
		if (!val) {
			txt.val (1);
		}else if(val < 0){
			var shu = Math.abs(val);
			/* txt.val (-(shu + 1)); */
			txt.val (1);
		}else if(val > 0){
			if(val == 1){
				txt.val (1);  
			}else{
				txt.val (val - 1);			
			}
		}
		var sum = 0;
	});
	$(".J_num .jia").click (function (){
		var me = $ (this), txt = me.prev (":text");
		var val = parseFloat (txt.val ());
		if(val >= 1){
			txt.val (val + 1);   
		}else if(val < 0){
			if(val == -1){
				txt.val ("");  
			}else{
				var shu = Math.abs(val);
				txt.val (-(shu - 1));			
			}
		}else{
			txt.val (1);			
		}
		var sum = 0;
	});
	
	$(document).ready( function(){ 
		$("#info").click(function(){
			var id = $("#link").val();
			 $.getJSON(url,{id:id},function(data){
				
				/* var description = ""; 
				for(var i in data){   
					var property=data[i];   
					description+=i+" = "+property+"\n";  
				}   
				alert(description); */
				
				$('#num_iid').val(data['item']);
				$('#title').val(data['title']);
				if(/taobao\.com/.test(id)){
                    document.getElementById('mall').value = '1';
				}else{
					document.getElementById('mall').value = '2';
				}
				$('#shop').val(data['shop']);
				$('#shopid').val(data['shopid']);
				
				$('#pict_url').val(data['img']);
				$('#value').val(data['value']);
				$('#price').val(data['price']);
				$('#ratio').val(data['ratio']);
				$('#deal').val(data['deal'])
				
			});
		});
	});
});
