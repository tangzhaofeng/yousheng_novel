$(function() {
	function t(t) {
		var e = 0;
		return $(t).each(function() {
			e += $(this).outerWidth(!0)
		}), e
	}
	function e(e) {
		var a = t($(e).prevAll()),
			i = t($(e).nextAll()),
			n = t($(".tab").children().not(".J_Tabs")),
			s = $(".tab").outerWidth(!0) - n,
			r = 0;
		if ($(".page-con").outerWidth() < s) r = 0;
		else if (i <= s - $(e).outerWidth(!0) - $(e).next().outerWidth(!0)) {
			if (s - $(e).next().outerWidth(!0) > i) {
				r = a;
				for (var o = e; r - $(o).outerWidth() > $(".page-con").outerWidth() - s;) r -= $(o).prev().outerWidth(), o = $(o).prev()
			}
		} else a > s - $(e).outerWidth(!0) - $(e).prev().outerWidth(!0) && (r = a - $(e).prev().outerWidth(!0));
		$(".page-con").animate({
			marginLeft: 0 - r + "px"
		}, "fast")
	}
	function a() {
		var e = Math.abs(parseInt($(".page-con").css("margin-left"))),
			a = t($(".tab").children().not(".J_Tabs")),
			i = $(".tab").outerWidth(!0) - a,
			n = 0;
		if ($(".page-con").width() < i) return !1;
		for (var s = $(".J_Tab:first"), r = 0; r + $(s).outerWidth(!0) <= e;) r += $(s).outerWidth(!0), s = $(s).next();
		if (r = 0, t($(s).prevAll()) > i) {
			for (; r + $(s).outerWidth(!0) < i && s.length > 0;) r += $(s).outerWidth(!0), s = $(s).prev();
			n = t($(s).prevAll())
		}
		$(".page-con").animate({
			marginLeft: 0 - n + "px"
		}, "fast")
	}
	function i() {
		var e = Math.abs(parseInt($(".page-con").css("margin-left"))),
			a = t($(".tab").children().not(".J_Tabs")),
			i = $(".tab").outerWidth(!0) - a,
			n = 0;
		if ($(".page-con").width() < i) return !1;
		for (var s = $(".J_Tab:first"), r = 0; r + $(s).outerWidth(!0) <= e;) r += $(s).outerWidth(!0), s = $(s).next();
		for (r = 0; r + $(s).outerWidth(!0) < i && s.length > 0;) r += $(s).outerWidth(!0), s = $(s).next();
		n = t($(s).prevAll()), n > 0 && $(".page-con").animate({
			marginLeft: 0 - n + "px"
		}, "fast")
	}
	function n() {
		var t = $(this).attr("href"),
			a = $(this).data("index"),
			i = $.trim($(this).text()),
			n = !0;
		if (void 0 == t || 0 == $.trim(t).length) return !1;
		if ($(".J_Tab").each(function() {
			return $(this).data("id") == t ? ($(this).hasClass("no") || ($(this).addClass("no").siblings(".J_Tab").removeClass("no"), e(this), $(".J_main .J_iframe").each(function() {
				return $(this).data("id") == t ? ($(this).show().siblings(".J_iframe").hide(), !1) : void 0
			})), n = !1, !1) : void 0
		}), n) {
			var s = '<a href="javascript:;" class="no J_Tab" data-id="' + t + '">' + i + ' <i class="icon ai-close"></i></a>';
			$(".J_Tab").removeClass("no");
			var r = '<iframe class="J_iframe" name="iframe' + a + '" width="100%" height="100%" src="' + t + '" data-id="' + t + '" frameborder="0" seamless></iframe>';
			$(".J_main").find("iframe.J_iframe").hide().parents(".J_main").append(r);
			$(".J_Tabs .page-con").append(s), e($(".J_Tab.no"))
		}
		return !1
	}
	function s() {
		var t = $(this).parents(".J_Tab").data("id"),
			a = $(this).parents(".J_Tab").width();
		if ($(this).parents(".J_Tab").hasClass("no")) {
			if ($(this).parents(".J_Tab").next(".J_Tab").size()) {
				var i = $(this).parents(".J_Tab").next(".J_Tab:eq(0)").data("id");
				$(this).parents(".J_Tab").next(".J_Tab:eq(0)").addClass("no"), $(".J_main .J_iframe").each(function() {
					return $(this).data("id") == i ? ($(this).show().siblings(".J_iframe").hide(), !1) : void 0
				});
				var n = parseInt($(".page-con").css("margin-left"));
				0 > n && $(".page-con").animate({
					marginLeft: n + a + "px"
				}, "fast"), $(this).parents(".J_Tab").remove(), $(".J_main .J_iframe").each(function() {
					return $(this).data("id") == t ? ($(this).remove(), !1) : void 0
				})
			}
			if ($(this).parents(".J_Tab").prev(".J_Tab").size()) {
				var i = $(this).parents(".J_Tab").prev(".J_Tab:last").data("id");
				$(this).parents(".J_Tab").prev(".J_Tab:last").addClass("no"), $(".J_main .J_iframe").each(function() {
					return $(this).data("id") == i ? ($(this).show().siblings(".J_iframe").hide(), !1) : void 0
				}), $(this).parents(".J_Tab").remove(), $(".J_main .J_iframe").each(function() {
					return $(this).data("id") == t ? ($(this).remove(), !1) : void 0
				})
			}
		} else $(this).parents(".J_Tab").remove(), $(".J_main .J_iframe").each(function() {
			return $(this).data("id") == t ? ($(this).remove(), !1) : void 0
		}), e($(".J_Tab.no"));
		return !1
	}
	function r() {
		$(".page-con").children("[data-id]").not(":first").not(".no").each(function() {
			$('.J_iframe[data-id="' + $(this).data("id") + '"]').remove(), $(this).remove()
		}), $(".page-con").css("margin-left", "0")
	}
	function d() {
		if (!$(this).hasClass("no")) {
			var t = $(this).data("id");
			$(".J_main .J_iframe").each(function() {
				return $(this).data("id") == t ? ($(this).show().siblings(".J_iframe").hide(), !1) : void 0
			}), $(this).addClass("no").siblings(".J_Tab").removeClass("no"), e(this)
		}
	}
	function c() {
		var t = $('.J_iframe[data-id="' + $(this).data("id") + '"]'),
			e = t.attr("src");
		t.attr("src", e)
	}
	
	function x() {
		var i = $('.J_Tabs .no').data("id"),
			t = $('.J_iframe[data-id="' + i + '"]'),
			e = t.attr("name");
		frames[e].location.reload();
	}
	
	function l() {
		$(".page-con").children("[data-id]").not(":first").each(function() {
			$('.J_iframe[data-id="' + $(this).data("id") + '"]').remove(), $(this).remove()
		}), $(".page-con").children("[data-id]:first").each(function() {
			$('.J_iframe[data-id="' + $(this).data("id") + '"]').show(), $(this).addClass("no")
		}), $(".page-con").css("margin-left", "0")
	}
	
	function o() {
		var t = $(this).data("id"),
			q = $('.nav-con ul').index($("#menu" + t));
		$(this).parents(".menu-con li").addClass("no").siblings().removeClass();
		$('.nav-con ul').eq(q).show().siblings().hide();
		return !1
	}
	
	$(".J_Item").each(function(t) {
		$(this).attr("data-index") || $(this).attr("data-index", t)
	}),
	
    $(".J_menu").on("click", o),
	$(".J_Item").on("click", n), 
	$(".J_Tabs").on("click", ".J_Tab i", s),            //单击关闭  【标签】
	$(".J_Tabs").on("click", ".J_Tab", d),              //单击切换  【标签】
	$(".J_Tabs").on("dblclick", ".J_Tab", c),           //双击刷新  【标签】
	$(".J_New").on("click", x),                            //单击刷新  【子页面】
	$(".J_Left").on("click", a),                             //向右移动  【标签栏】
	$(".J_Right").on("click", i),                            //向右移动  【标签栏】
	$(".J_Other").on("click", r),                       //单击关闭  【其他标签】
	$(".J_All").on("click", l);                         //单击关闭  【全部标签】
});