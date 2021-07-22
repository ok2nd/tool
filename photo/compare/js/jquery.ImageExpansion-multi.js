/*
	オリジナル：	https://studio-key.com/719.html
	改修版：	https://jsfiddle.net/Lhankor_Mhy/jw91yx3p/
*/
(function ($) {
$.fn.ImageExpansion = function (options) {
	var target = $(this);
	var settings = $.extend({
		'pan': 2,
		'drag': 100
	}, options);
	/*
	 * 初期設定
	 */
	var setImage = function (obj) {
		//画像のSRC取得
		var src = $("img", target).attr('src');
		//右の枠の大きさ ドラッグ枠の数値 * 倍率
		var hw = settings.drag * settings.pan;
		$(".pan_image", target).css({ 'width': hw, 'height': hw });
		//右の枠へ背景画像を設定
		$(".pan_image", target).css({ 'background-image': 'url(' + src + ')' });
		//左の画像の幅を倍率で割り、max-widthに指定する幅を得る
		var img_w = $("img", target).width();
		img_w = Math.floor(img_w / settings.pan);
		var img_h = $("img", target).height();
		img_h = Math.floor(img_h / settings.pan);
		//左の枠の幅を上で計算した幅と高さに設定	
		$(target).css({ 'width': img_w, 'height': img_h });
		//左の画像を上で計算した幅と高さに設定
		$("img", target).css({ 'max-width': img_w, 'height': img_h });
		//ドラッグ枠の大きさを設定
		$(".pan_mouse", target).css({ 'width': settings.drag, 'height': settings.drag });
	};
	/*
	 * ドラッグ
	 */
	$(".pan_mouse", target).draggable({
		containment: "parent"
	});
	/*
	 * ドラッグした距離で背景画像の位置を変更
	 */
	$(".pan_mouse", target).on('mousemove', function (e) {
		//image_expansion ウインド上の位置
		var div = $(target).offset();
		var ex_left = Math.floor(div.left);
		var ex_top = Math.floor(div.top);
		//pan_mouse ウインド上の位置
		var div = $('.pan_mouse', target).offset();
		var pan_left = Math.floor(div.left);
		var pan_top = Math.floor(div.top);
		//image_expansion内におけるpan_mouseの上下位置差分
		var left = (pan_left - ex_left) * settings.pan;
		var top = (pan_top - ex_top) * settings.pan;
		$(".pan_image", target).css({ 'background-position': '-' + left + 'px -' + top + 'px' });
	});
	return setImage($(this));
};
})(jQuery);
