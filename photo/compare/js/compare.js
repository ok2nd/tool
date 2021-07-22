/*
	use jquery.ImageExpansion-multi.js
*/
function compare_image(div1, div2, img1, img2) {
	var ih_1 = document.getElementById(img1).naturalHeight;
	var ih_2 = document.getElementById(img2).naturalHeight;
	var iw_1 = document.getElementById(img1).naturalWidth;
	var iw_2 = document.getElementById(img2).naturalWidth;
	if (ih_1 > iw_1) {
		var pan1 = ih_1 / 500;
		var pan2 = ih_2 / 500;
		var drag1 = 180000 / ih_1;
		var drag2 = 180000 / ih_2;
	} else {
		var pan1 = iw_1 / 600;
		var pan2 = iw_2 / 600;
		var drag1 = 240000 / iw_1;
		var drag2 = 240000 / iw_2;
	}
	$(function() {
		$(div1).ImageExpansion({
			'pan' : pan1,	//倍率
			'drag' : drag1	//ドラッグ枠の大きさ
		});
		$(div2).ImageExpansion({
			'pan' : pan2,	//倍率
			'drag' : drag2	//ドラッグ枠の大きさ
		});
	});
}
