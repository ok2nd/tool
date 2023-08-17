/*
***********************************************************************
	ok2nd.js
***********************************************************************
	Version		2.02
	Copyright	2013,2014 ok.2nd <m.ok.2nd@gmail.com>
	HomePage	http://ok2nd.web.fc2.com/
			MyHome Portal （個人向けWebポータル）
	Blog		http://ok2nd.blog87.fc2.com/
			中級プログラマの自宅でPHP ブログ
*/
// @@@	Stringオブジェクトにメソッド追加 @@@@@@@@@@@@@@@@@@@@
String.prototype.trim = function() {
	return this.replace(/^[\s　]+|[\s　]+$/g, '');
}
String.prototype.rnt_strip = function() {	// \r \n \t 除去
	return this.replace(/\r|\n|\t/g, "");
}
String.prototype.tagstrip = function() {	// HTMLタグ除去
	return this.replace(/<\/?[^>]+>/gi, "");
}
String.prototype.dirname = function() {		// 親ディレクトリのパスを返す
	return this.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
}
String.prototype.basename = function() {	// パスの最後にある名前(ファイル名)の部分を返す
	return this.replace(/\\/g,'/').replace( /.*\//, '' );
}
String.prototype.zen2han = function() {		// 全角英数字を半角英数字に変換
	var i, charCode, charArray = [];
	for (i = this.length-1; 0 <= i; i--) {
		charCode = charArray[i] = this.charCodeAt(i);
		switch(true) {
			case (charCode <= 0xff5e && 0xff01 <= charCode):
				charArray[i] -= 0xfee0;
				break;
			case (charCode == 0x3000):
				charArray[i] = 0x0020;
				break;
		//	case (charCode == 0x30FC):	// ー
		//		charArray[i] = 0x002D;
		//		break;
		}
	}
	return String.fromCharCode.apply(null, charArray);
}
function num_format(num, col) {		// 数値を3桁区切りのカンマ表示＆小数点以下桁数指定
	var ix;
	if (col) {
		var multiple = 1;
		for (ix = 1; ix <= col; ix++) {
			multiple *= 10;
		}
		num = Math.round(num * multiple) / multiple;
	} else {
		num = Math.round(num);
	}
	var str = new String(num).replace(/,/g/"");
	while(str != (str = str.replace(/^(-?\d+)(\d{3})/,"$1,$2")));
	return str;
}
function str_width(str) {		// 文字列の文字幅(漢字は2)を求める
	var r = 0;
	for (var i = 0; i < str.length; i++) {
		var c = str.charCodeAt(i);
		// Shift_JIS: 0x0 ～ 0x80, 0xa0 , 0xa1 ～ 0xdf , 0xfd ～ 0xff
		// Unicode : 0x0 ～ 0x80, 0xf8f0, 0xff61 ～ 0xff9f, 0xf8f1 ～ 0xf8f3
		if ((c >= 0x0 && c < 0x81) || (c == 0xf8f0) || (c >= 0xff61 && c < 0xffa0) || (c >= 0xf8f1 && c < 0xf8f4)) {
			r += 1;
		} else {
			r += 2;
		}
	}
	return r;
}
function dirname(path) {		// 親ディレクトリ(上位ディレクトリ)のパスを返す
	return path.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
}
function basename(path) {		// パスの最後にある名前(ファイル名)の部分を返す
	return path.replace(/\\/g,'/').replace( /.*\//, '' );
}
function zen2han(str) {			// 全角英数字を半角英数字に変換
	var i, charCode, charArray = [];
	for (i = str.length-1; 0 <= i; i--) {
		charCode = charArray[i] = str.charCodeAt(i);
		switch(true) {
			case (charCode <= 0xff5e && 0xff01 <= charCode):
				charArray[i] -= 0xfee0;
				break;
			case (charCode == 0x3000):
				charArray[i] = 0x0020;
				break;
		//	case (charCode == 0x30FC):		// ー
		//		charArray[i] = 0x002D;
		//		break;
		}
	}
	return String.fromCharCode.apply(null, charArray);
}
// @@@	Filter Change Reload ( URL Parameter ) @@@@@@@@@@@@@@@@@@@@
function chgFilterVal(name, val, opt) {
	if (opt != '') opt = "&"+opt;
	location.href = location.href.split('?')[0]+"?"+name+"="+val+opt;
}
// <input type="button" value="リセット" onClick="chgFilterReset('cat', '<?= query_string_strip('cat') ?>')">
function chgFilterReset(name, opt) {
	chgFilterVal(name, "__reset__", opt);
}
// <select onChange="chgFilterSelect(this, 'cat', '<?= query_string_strip('cat') ?>')">
// <select onChange="chgFilterSelect(this, 'cat', '<?= query_string_strip_multi('cat,cat2') ?>')">
function chgFilterSelect(me, name, opt) {
	var val = me.options[me.selectedIndex].value;
	chgFilterVal(name, val, opt);
}
// <input type="radio" onClick="chgFilterRadio(this, 'cat', '<?= query_string_strip('cat') ?>')">
function chgFilterRadio(me, name, opt) {
	var val = me.value;
	chgFilterVal(name, val, opt);
}
// <input type="checkbox" onClick="chgFilterCheckOnOff(this, 'cat', '<?= query_string_strip('cat') ?>')">
function chgFilterCheckOnOff(me, name, opt, val) {
	if (me.checked) {
		if (val === undefined) {
			val = "on";
		}
	} else {
		val = "off";
	}
	chgFilterVal(name, val, opt);
}
// @@@	Cookie Set ( and Reload ) @@@@@@@@@@@@@@@@@@@@
function chgCookieVal(cookie_name, val, reload) {
	var pathname = location.pathname.substring(1);
	var parts = pathname.split('/');
	var my_session_path = '/' + parts[0] + '/';
	$.cookie(cookie_name, val, { path:my_session_path, expires:365 });
	if (reload == 'reload') {
		location.reload();
	}
}
// <select onChange="chgCookieSelect(this, 'cookie_cat', 'reload')">
function chgCookieSelect(me, cookie_name, reload) {
	var val = me.options[me.selectedIndex].value;
	if (reload === undefined) {
		reload = '';
	}
	chgCookieVal(cookie_name, val, reload);
}
// <input type="radio" onClick="chgCookieRadio(this, 'cookie_cat', 'reload')">
function chgCookieRadio(me, cookie_name, reload) {
	var val = me.value;
	if (reload === undefined) {
		reload = '';
	}
	chgCookieVal(cookie_name, val, reload);
}
// <input type="checkbox" onClick="chgCookieCheckOnOff(this, 'cookie_cat', 'reload')">
function chgCookieCheckOnOff(me, cookie_name, reload) {
	if (me.checked) {
		val = me.value;
	} else {
		val = '';
	}
	if (reload === undefined) {
		reload = '';
	}
	chgCookieVal(cookie_name, val, reload);
}
function encodeURL(str) {
	// by Favorite Labo
	// http://www.favorite-labo.org/archives/211.html
	var character = '';
	var unicode = '';
	var string = '';
	var i = 0;
	for (i = 0; i < str.length; i++) {
		character = str.charAt(i);
		unicode   = str.charCodeAt(i);
		if (character == ' ') {
			string += '+';
		} else {
			if (unicode == 0x2a || unicode == 0x2d || unicode == 0x2e || unicode == 0x5f || ((unicode >= 0x30) && (unicode <= 0x39)) || ((unicode >= 0x41) && (unicode <= 0x5a)) || ((unicode >= 0x61) && (unicode <= 0x7a))) {
				string = string + character;
			} else {
				if ((unicode >= 0x0) && (unicode <= 0x7f)) {
					character   = '0' + unicode.toString(16);
					string += '%' + character.substr(character.length - 2);
				} else if (unicode > 0x1fffff) {
					string += '%' + (oxf0 + ((unicode & 0x1c0000) >> 18)).toString(16);
					string += '%' + (0x80 + ((unicode & 0x3f000) >> 12)).toString(16);
					string += '%' + (0x80 + ((unicode & 0xfc0) >> 6)).toString(16);
					string += '%' + (0x80 + (unicode & 0x3f)).toString(16);
				} else if (unicode > 0x7ff) {
					string += '%' + (0xe0 + ((unicode & 0xf000) >> 12)).toString(16);
					string += '%' + (0x80 + ((unicode & 0xfc0) >> 6)).toString(16);
					string += '%' + (0x80 + (unicode & 0x3f)).toString(16);
				} else {
					string += '%' + (0xc0 + ((unicode & 0x7c0) >> 6)).toString(16);
					string += '%' + (0x80 + (unicode & 0x3f)).toString(16);
				}
			}
		}
	}
	return string;
}
function encode_amp(str) {
	return str.replace(/&/g,"%2526");	// %:%25 → &:%26
}
function textareaBigSmall(obj, bigSmall, min, upDown) {
	if (upDown === undefined) {
		upDown = 5;
	}
	if (min === undefined) {
		min = 5;
	}
	if (bigSmall == "大") {
		document.getElementById(obj).rows = document.getElementById(obj).rows + upDown;
	} else {
		if (document.getElementById(obj).rows > min) {
			document.getElementById(obj).rows = document.getElementById(obj).rows - upDown;
		}
	}
}
function getURLParameter(name) {	// by rainyday.js
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}
