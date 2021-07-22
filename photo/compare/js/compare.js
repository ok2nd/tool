/*
	use jquery.ImageExpansion-multi.js
*/
function image_expand(div, img) {
	var ih = document.getElementById(img).naturalHeight;
	var iw = document.getElementById(img).naturalWidth;
	if (ih > iw) {
		var pan = ih / 500;
		var drag = 180000 / ih;
	} else {
		var pan = iw / 600;
		var drag = 240000 / iw;
	}
	$(function() {
		$(div).ImageExpansion({
			'pan' : pan,	//倍率
			'drag' : drag	//ドラッグ枠の大きさ
		});
	});
}
