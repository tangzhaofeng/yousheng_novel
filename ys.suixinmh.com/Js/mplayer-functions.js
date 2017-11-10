function setEffects (player) {
	// 滑块
	player.dom.volRange.nstSlider({
		"left_grip_selector": ".mp-vol-circle",
		"value_changed_callback": function(cause, value) {
			player.dom.container.find('.mp-vol-current').width(value + '%');
			player.dom.volRange.trigger('change',[value]);
		}
	});
	player.dom.container.find('.mp-mode').click(function () {
		var dom = $(this);
		var mode = player.getPlayMode();
		dom.removeClass('mp-mode-'+mode);
		mode = mode == 3 ? 0 : mode + 1;
		player.changePlayMode(mode);
		dom.addClass('mp-mode-' + mode);
	});
}
