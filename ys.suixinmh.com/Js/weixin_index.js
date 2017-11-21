$(function () {
    var _editArea = $('#editArea');

    //显示隐藏发送按钮
    var _editAreaInterval;
    $('#editArea').focus(function () {
        var _this = $(this), html;
        _editAreaInterval = setInterval(function () {
            html = _this.html();
            if (html.length > 0) {
                $('#web_wechat_pic').hide();
                $('#btn_send').show();
            } else {
                $('#web_wechat_pic').show();
                $('#btn_send').hide();
            }
        }, 200);
    });

    $('#editArea').blur(function () {
        clearInterval(_editAreaInterval);
    });

    //显示隐藏表情栏
    $('.web_wechat_face').click(function () {
        $('.box_ft_bd').toggleClass('hide');
        resetMessageArea();
    });

    //切换表情主题
    $('.exp_hd_item').click(function () {
        var _this = $(this), i = _this.data('i');
        $('.exp_hd_item,.exp_cont').removeClass('active');
        _this.addClass('active');
        $('.exp_cont').eq(i).addClass('active');
        resetMessageArea();
    });

    //选中表情
    $('.exp_cont a').click(function () {
        var _this = $(this);
        var html = '<img class="' + _this[0].className + '" title="' + _this.html() + '" src="Images/lyf.png">';
        _editArea.html(_editArea.html() + html);
        $('#web_wechat_pic').hide();
        $('#btn_send').show();
    });

    resetMessageArea();


    function sendMsg(str) {
    }

    function resetMessageArea() {
        $('#messageList').animate({ 'scrollTop': 999 }, 500);
    }
});