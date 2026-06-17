<?php
	$_SESSION['img_host'] = 'cdn-ak.f.st-hatena.com';	// はてなフォトサーバー
//
	require("../__common__/__define_common.php");
	require("../__common__/include-common-all.php");
	require("../__common__/include-common-html.php");
	my_session_start();
//	require("../account/__logincheck.php");
//	if ($_SESSION['システム管理者'] <> "YES") {
//		exit;
//	}
//	if (defined("GET_URL_FILE_TYPE_SELECTS_tools")) {
//		define("GET_URL_FILE_TYPE_SELECTS", GET_URL_FILE_TYPE_SELECTS_tools);
//	} else {
//		define("GET_URL_FILE_TYPE_SELECTS", "jpeg,jpg,gif,png,wmv,flv,avi,mpg,mov,swf,zip,lzh");
//	}
	define("GET_URL_FILE_TYPE_SELECTS", "jpeg,jpg,gif,png,wmv,flv,avi,mpg,mov,mp4");
	if ($_POST) {
		if (isset($_POST['reset'])) {
			$url = 'https://f.hatena.ne.jp/{はてなID}/{ディレクトリ}/';
		} else {
			$url = str_replace(DIRECTORY_SEPARATOR, '/', form_str_filepath($_POST['url']));
		}
		if ($_POST['endpage']) {
			$_SESSION['endpage'] = intval($_POST['endpage']);
		}
		if ($_SESSION['endpage'] < 1) {
			$_SESSION['endpage'] = 1;
		}
	//	$_SESSION['other_site'] = $_POST['other_site'];
	//	$_SESSION['get_img'] = $_POST['get_img'];
		$_SESSION['view_url'] = $_POST['view_url'];
		$_SESSION['save_dir']= str_replace(DIRECTORY_SEPARATOR, "/", right_slash_strip(form_str_filepath($_POST['save_dir'])));
		$_SESSION['img_size_limit'] = $_POST['img_size_limit'];
		setcookie("html_get_http_url", $url, time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		setcookie("html_get_http_endpage", $_SESSION['endpage'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
	//	setcookie("html_get_http_other_site", $_SESSION['other_site'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		setcookie("html_get_http_get_img", $_SESSION['get_img'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		setcookie("html_get_http_view_url", $_SESSION['view_url'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		setcookie("html_get_http_save_dir", $_SESSION['save_dir'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		setcookie("html_get_http_img_size_limit", $_SESSION['img_size_limit'], time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
		$get_filetype = '';
		if (isset($_POST['get_filetype'])) {
			if ($ary = $_POST['get_filetype']) {
				foreach ($ary as $val) {
					$get_filetype .= $val.' ';
				}
			}
		}
		$_SESSION['get_filetype'] = $get_filetype;
		setcookie("html_get_http_get_filetype", $get_filetype, time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
	} else {
		if ($_COOKIE['html_get_http_url'].'' <> '') {
			$url = $_COOKIE['html_get_http_url'];
		} else {
			$url = 'https://f.hatena.ne.jp/art2nd/Hatena%20Blog/';
		}
		$_SESSION['endpage'] = 1;
	//	$_SESSION['other_site'] = $_COOKIE['html_get_http_other_site'];
		$_SESSION['get_img'] = $_COOKIE['html_get_http_get_img'];
		$_SESSION['view_url'] = $_COOKIE['html_get_http_view_url'];
		$_SESSION['save_dir'] = $_COOKIE['html_get_http_save_dir'];
		$_SESSION['img_size_limit'] = $_COOKIE['html_get_http_img_size_limit'];
		$_SESSION['get_filetype'] = $_COOKIE['html_get_http_get_filetype'];
	}
	$_SESSION['other_site'] = 'Y';		// 固定
	$_SESSION['search_level'] = 2;		// 固定
	$G_url_pool = array();			// URL
	$G_url_pool_other = array();		// 別サーバ:True
	$G_url_pool_level = array();		// 階層
	$G_url_pool_done = array();		// 処理済:True
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="author" content="ok.2nd">
<title>はてなブログ＆はてなフォトライフ：写真一括ダウンロード</title>
<style>
body {
	background-color: #ffffff;
	margin: 10px;
	padding: 0px;
	font-size: 80%;
}
h1 {
	font-size: 120%;
	font-weight: bold;
	color: #00f;
}
p {
	color: #f00;
}
.get_img {
	margin:0 0 0 16px;
}
.get_url {
	color: #ff0000;
}
a.other_url:link { color: #ff0000; }
a.other_url:visited { color: #ff0000; }
a.other_url:hover { color: #ff8000; }
a.other_url:active { color: #ff8000; }

a.img_url:link { color: #008000; }
a.img_url:visited { color: #008000; }
a.img_url:hover { color: #ff0000; }
a.img_url:active { color: #ff0000; }

a.error_url:link { color: #808080; }
a.error_url:visited { color: #808080; }
a.error_url:hover { color: #ff0000; }
</style>
</head>
<body>
<p>
<h1>はてなブログ＆はてなフォトライフ：写真一括ダウンロード (Ver.1.1)</h1>
<p>※「はてなフォトライフ」で公開範囲をパブリックにして、お使い下さい。</p>
<form method="POST" action="<?= $_SERVER['SCRIPT_NAME'] ?>">
	URL：<input class="text" type="text" name="url" value="<?= $url ?>" size=80>
	<input type="submit" name="gethtml" value="ダウンロード">
	<input type="submit" name="reset" value="リセット"><br>
	ページ最後（数字）：<input class="text" type="text" name="endpage" value="<?= $_SESSION['endpage'] ?>" size=5><br>
	保存先：<input class="text" type="text" name="save_dir" value="<?= $_SESSION['save_dir'] ?>" size=80><br>
	<a href="http://localhost/_myhome/tools/file-manager.php?path=<?= $_SESSION['save_dir'] ?>" target="_blank" style="margin-left:40px;"><?= $_SESSION['save_dir'] ?></a><br
</form>
<?php
	if (isset($_POST['gethtml']) and $url <> '') {
		post_done_proc($url);
	}
?>
<div id="footer" style="margin: 10px 0 0 0;">
<a href="http://ok2nd.web.fc2.com/" target="_blank" style="color:#b0b0ff;">Powered by ok.2nd</a>
</div>
</body>
</html>
<?php
function post_done_proc($url) {
	global $G_url_pool;
	global $G_url_pool_other;
	global $G_url_pool_level;
	global $G_url_pool_done;
	//	if ($_SESSION['get_img'] == 'Y') {
		if ($_SESSION['save_dir'].'' == '') {
			error_msg('保存先フォルダの指定がありません。');
			return;
		}
		$_SESSION['save_dir_SJIS'] = mb_convert_encoding($_SESSION['save_dir'], _SYSTEM_FILE_ENCODING, 'UTF-8');
		if (!@opendir($_SESSION['save_dir_SJIS'])) {
			error_msg ('保存先フォルダが存在しません。');
			return;
		}
	//	}
	set_time_limit(0);			//実行時間制御なし　max_execution_time
	ob_implicit_flush();			//自動フラッシュをオン(出力をバッファリングしない)
	$top_hostname = get_hostname($url);

//echo '<p class="get_url">'.$url.'</p>';
//get_http($url, $top_hostname, 1);

	for ($page_num=1; $page_num<=$_SESSION['endpage']; $page_num++) {
		$get_url_page = rtrim($url,'/').'/?page='.$page_num;
		echo '<p class="get_url">'.$get_url_page.'</p>';
		get_http($get_url_page, $top_hostname, 1);
	}
	echo '<p class="get_url">終了</p>';
}
function get_http($url, $top_hostname, $level, $other=False) {
	global $G_url_pool;
	global $G_url_pool_other;
	global $G_url_pool_level;
	global $G_url_pool_done;
	$my_hostname = get_hostname($url);
//	if (check_and_get_image($url, $level)) {
//		return;
//	}
//	print_url($url, $level, '■', $other);
	$html = my_file_get_contents($url);
	if ($html.'' == '') {
		echo '<p style="padding-left: 30px; color: red ">↑not found.</p>';
		return;
	}
	$html = @mb_convert_encoding($html, 'UTF-8', MB_CONVERT_ENCODING_AUTO);
	if ($html.'' == '') {
		echo '<p style="padding-left: 30px; color: red ">↑not found.</p>';
		return;
	}
	$my_parent_path = get_parent_path($url);
	if (substr($url,0,5) == "http:") {
		$my_http = "http:";
	} elseif (substr($url,0,6) == "https:") {
		$my_http = "https:";
	}
	//if (check_img_url($html, $img_url_ary)) {
	//	foreach ($img_url_ary as $img_url) {
	//		check_and_get_image(url_fullpath($img_url, $my_http, $my_parent_path, $my_hostname), $level);
	//	}
	//}
	$fotolist = get_intag($html, '<ul class="fotolist">', '</ul>', $pos);	// はてなブログ　写真リスト *******
//echo $fotolist;
	// 正規表現でタグの属性値を取り出す
	// by http://ml.php.gr.jp/pipermail/php-users/2006-September/030789.html
	if (preg_match_all('/<a\b[^>]*?>/si', $fotolist, $matches)) {		// Aタグ取り出し
		foreach ($matches[0] as $value) {
			if (($href = get_html_attrib('href', $value)) !== False) {;
				$href = url_fullpath($href, $my_http, $my_parent_path, $my_hostname);
//echo $href.'<br>';
				get_http2($href, $top_hostname, 1);			// ********
			}
		}
	}
}
function get_http2($url, $top_hostname, $level, $other=False) {
	global $G_url_pool;
	global $G_url_pool_other;
	global $G_url_pool_level;
	global $G_url_pool_done;
	$my_hostname = get_hostname($url);
	$html = my_file_get_contents($url);
	if ($html.'' == '') {
		echo '<p style="padding-left: 30px; color: red ">↑not found.</p>';
		return;
	}
	$html = @mb_convert_encoding($html, 'UTF-8', MB_CONVERT_ENCODING_AUTO);
	if ($html.'' == '') {
		echo '<p style="padding-left: 30px; color: red ">↑not found.</p>';
		return;
	}
//echo '<p>get_http2</p>';
// <img src="https://cdn-ak.f.st-hatena.com/images/fotolife/o/xx/xx/2017xxxxxx.jpg" alt="2017xxxxxx" title="2017xxxxxx" width="400" height="252" class="foto" style="" />
	$pos = 0;
	while (($img = get_intag_span($html, '<img src="https://cdn-ak.f.st-hatena.com/images/fotolife/', '>', $pos)) <> '') {	// はてなブログ　写真 *******
		if (strpos($img, 'class="foto"') !== false) {
//echo $img."<br>\n";
			if (check_img_url($img, $img_url_ary)) {
				foreach ($img_url_ary as $img_url) {
					$img_url = substr($img_url, 0, strcspn($img_url, '?'));
//echo $img_url."<br>\n";
					check_and_get_image(url_fullpath($img_url, $my_http, $my_parent_path, $my_hostname), $level);
				}
			}
		}
	}
}
function url_fullpath($url, $my_http, $my_parent_path, $hostname) {
	if (substr($url,0,5) == 'http:' || substr($url,0,6) == 'https:') {
		return $url;
	}
	if (substr($url,0,2) == './') {
		$url = substr($url,2);
	}
	if (substr($url,0,1) == '/') {
		return $my_http.'//'.$hostname.$url;
	} elseif (substr($url,0,3) != '../') {
		return $my_parent_path.'/'.$url;
	}
	while (substr($url,0,3) == '../') {
		if ($pos = strrpos($my_parent_path, '/')) {
			$my_parent_path = substr($my_parent_path,0,$pos);
		}
		$url = substr($url,3);
	}
	return $my_parent_path.'/'.$url;
}
function get_hostname($url) {
	preg_match('@^(?:https?://)?([^/]+)@i', $url, $matches);
	return $matches[1];
}
function get_parent_path($url) {
	$pos = strrpos($url, "/");
	if ($pos < 10) {
		return $url;
	}
	return substr($url,0,$pos);
}
function get_html_attrib($attrib, $tag) {
	// 正規表現でタグの属性値を取り出す
	// by http://ml.php.gr.jp/pipermail/php-users/2006-September/030789.html
	$regex = "/[\s'\"]$attrib\s*=\s*([^\s'\">]+|'[^']+'|\"[^\"]+\")/si";
	return preg_match($regex, $tag, $matches) ? preg_replace('/^\s*[\'"](.+)[\'"]\s*$/s', '$1', $matches[1]) : False;
}
function check_img_url($html, &$img_url_ary) {
	// imgタグ以外のsrc属性全て取得。タグ外のsrc=を拾う可能性あり。
	$img_url_ary = array();
	$exist = False;
	$pos = 0;
	$html = str_replace('>', ' >', $html);
	while (($img = get_intag($html, 'src=', ' ', $pos)) <> '') {
	 	$img = str_replace("\"", '', $img);
	 	$img = str_replace("'", '', $img);
		$img_url_ary[] = $img;
		$exist = True;
	}
	return $exist;
}
function check_and_get_image($url, $level) {
//echo $url.'<br>';
	static $me_called = False;
	static $selects_filetype = array();	// 対象ファイルタイプ選択肢
	static $get_filetype = array();		// 取得ファイルタイプ
	if (!$me_called) {
		$selects_filetype = explode(',', GET_URL_FILE_TYPE_SELECTS);
		$get_filetype = explode(' ', $_SESSION['get_filetype']);
	}
	$me_called = True;
	if (!is_ext_filename($url, $selects_filetype)) {
		return False;
	}
	get_imagefile($url, $level);
	return True;
}
function get_imagefile($url, $level) {
	static $done_img_url = array();
	if (in_array($url, $done_img_url)) {	//処理済み
		return;
	}
	$done_img_url[] = $url;
	if (($filename = get_filename($url, $parent_folder)) == '') {
		return;
	}
	$save_dir = $_SESSION['save_dir_SJIS'].DIRECTORY_SEPARATOR.get_hostname($url);
	if (!@opendir($save_dir)) {
		@mkdir($save_dir);
	}
	if ($img_data = my_file_get_contents($url)) {
		$size = (int)(strlen($img_data)/1000);
		// if ($size >= intval($_SESSION['img_size_limit'])) {
			if ($parent_folder <> '') {
				$save_file = $save_dir.DIRECTORY_SEPARATOR.$parent_folder.'_'.$filename;
			} else {
				$save_file = $save_dir.DIRECTORY_SEPARATOR.$filename;
			}
			$fp = fopen($save_file, "w");
			fputs($fp, $img_data);
			fclose($fp);
			print_img_url($url, $size);
		//	} elseif ($_SESSION['view_url']) {
		//		print_img_url($url, $size, '');
		//	}
	} else {
		print_img_url($url, -1, 'error');
	}
}
function get_filename($url, &$parent_folder) {
	$urlary = explode('/', $url);
	if (count($urlary) < 4) {
		return '';
	}
	$imgfile = array_pop($urlary);		// 配列の最後から取り出す
	if (count($urlary) >= 4) {
		$parent_folder = array_pop($urlary);
	} else {
		$parent_folder = '';
	}
	$pos = strrpos($imgfile, '=');
	if ($pos !== False) {
		$imgfile = substr($imgfile, $pos+1);
	}
	$pos = strrpos($parent_folder, '=');
	if ($pos !== False) {
		$imgfile = substr($parent_folder, $pos+1);
	}
	return $imgfile;
}
function print_url($url, $level, $chr, $other=False) {
	if ($other) {
		$class = 'other_url';
	}
	echo '<p>('.$level.')'.$chr.'<a class="'.$class.'" href="'.$url.'" target="_blank">'.$url.'</a></p>';
}
function print_img_url($url, $size, $err_msg=False) {
	if ($size > -1) {
		$prt_size = ' ('.$size.' KB)';
	} else {
		$prt_size = '';
	}
	if ($err_msg !== False) {
		echo '<p class="get_img">===> <a class="error_url" href="'.$url.'" target="_blank">'.$url.'</a>'.$prt_size.' '.$err_msg.'</p>';
	} else {
		echo '<p class="get_img">===> <a class="img_url" href="'.$url.'" target="_blank">'.$url.'</a>'.$prt_size.'</p>';
	}
}
?>
