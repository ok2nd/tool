// Thanks to oishii_mahou (chiebukuro.yahoo.co.jp)
// <div id="gallery">の横幅を中の画像の数に合わせて調整し、余分の余白が出来ないようにする。
function initGallery() {
	gallery = document.getElementById("gallery");
	imgItem = gallery.getElementsByTagName("span");
	gallery.innerHTML = gallery.innerHTML.replace( /\r?\n|\r/g, "" );	// 改行コードを除去
	adjustGallery();
}
function adjustGallery() {
	var imgStyle = imgItem[0].currentStyle || document.defaultView.getComputedStyle(imgItem[0], "");
	var imgMargin = parseInt(imgStyle.marginLeft, 10) + parseInt(imgStyle.marginRight, 10);
	var imgWidth = imgItem[0].offsetWidth + 5;
	var winWidth = document.body.offsetWidth || document.documentElement.offsetWidth;	// ウィンドウ幅
	var imgNum = Math.min(Math.floor(winWidth/imgWidth), imgItem.length);	// 横に並んでいる画像の数
	gallery.style.width = imgNum * imgWidth + (imgNum - 1) * imgMargin + "px";
}
window.addEventListener( "load", initGallery, false );
window.addEventListener( "resize", adjustGallery, false );
