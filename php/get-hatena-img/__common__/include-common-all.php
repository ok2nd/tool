<?php
	error_reporting(E_ERROR & ~E_NOTICE & ~E_PARSE);	// PHP 5.3対応
	$con = NULL;		// $con = mysqli_connect(_DB_SERVER, _DB_USERNAME, _DB_PASSWORD, $dbname)
//フォーム入力	タグ許可		form_str_adjust($str)				:stripslashes
//フォーム入力	タグ除去		↓	form_str_adjust_strip_tags($str)	:stripslashes, strip_tags
//						↓
//DB登録前				str_for_mysql($str)				:mysqli_real_escape_string
//					↓	↓
//HTML表示前	タグ無効化		my_htmlspecialchars($str)
//		改行を<br>		↓	ins_br($str)
//		＋キーワードハイライト	↓	ins_br_keystr($str, $keystr)
//					↓
//		タグ無効化＆Aタグ挿入	ins_atag_br($str, $keystr)			:my_htmlspecialchars
//		＋キーワードハイライト	ins_atag_br_keystr($str, $keystr)		:my_htmlspecialchars
//
//		5C(\)を含む文字の処理(表ソ十構能予申噂暴などの文字化け対策)		// Shift-JISの場合
//		$data =~ s/\\/\\\\/g;
//
// ****************************************************************
function get_contents_path() {
// ****************************************************************
	$contents_path = array();
	for ($ix = 1; $ix <= _CONTENTS_PATH_CNT; $ix++) {
		$contents_path[] = constant("_CONTENTS_PATH_" . $ix);
	}
	return $contents_path;
}
// ****************************************************************
function _GET_to_http_arg_pool(&$http_arg, $sub_prefix, $pool_use="", $script_pool=true) {
// ****************************************************************
//	ex.
//		$http_arg = array();
//		$http_arg['pl']		= $_GET[$'pl'];
//		$http_arg['sort']	= $_GET[$'sort'];
//		$http_arg['cat']	= $_GET[$'cat'];
//		$http_arg['key']	= $_GET[$'key'];
//	$pool_use="y,m,d,key"	: $GETがない場合、$_SESSION変数に保存した値を返す
//	+	$_SESSION変数に保存
// ****************************************************************
	if (isset($_GET['arg']) && $_GET['arg'] == "session") {
		redirect($_SERVER['SCRIPT_NAME']."?".$_SESSION[SESSION_PREFIX.$sub_prefix.'_http_arg']);
	}
	$pool_ary = explode(",", $pool_use);
	$http_arg_pool = http_arg_from_session_pool($sub_prefix);	// $_SESSION変数に保存した値
	foreach ($http_arg as $key=>$value) {
		if (isset($_GET[$key]) && $_GET[$key] <> "") {
			if ($_GET[$key] == "__reset__") {
				$http_arg[$key] = "";
			} else {
				$http_arg[$key] = my_GET($key);
			}
		} else {
			if (in_array($key, $pool_ary)) {
				if ($http_arg_pool[$key].'' <> '') {
					$http_arg[$key] = $http_arg_pool[$key];
				}
			}
		}
	}
	$_SESSION[SESSION_PREFIX.$sub_prefix.'_http_arg'] = query_from_http_arg_pool($http_arg);
	if ($script_pool) {
		$_SESSION[SESSION_PREFIX.'_SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];	// ??????/index.php redirect用
	}
}
// ****************************************************************
function arg_val_to_http_arg_pool($http_arg, $sub_prefix) {
// ****************************************************************
	// $_SESSION[SESSION_PREFIX.$sub_prefix.'_http_arg'] = query_from_http_arg_pool($http_arg);
	$_SESSION[SESSION_PREFIX.$sub_prefix.'_http_arg'] = $_SERVER['QUERY_STRING'];
}
// ****************************************************************
function http_arg_from_session_pool($sub_prefix) {
// ****************************************************************
	$pool_str = $_SESSION[SESSION_PREFIX.$sub_prefix.'_http_arg'];
	parse_str($pool_str, $http_arg);
	return $http_arg;
}
// ****************************************************************
function query_from_http_arg_pool($http_arg, $omit_arg="") {
// ****************************************************************
//	$http_arg = array();
//	$http_arg['pl']
//	$http_arg['sort']
//	$http_arg['cat']
//	$http_arg['key']
//	return ex.: pl=xxx,&sort=xxx,&cat=xxx,&key=xxx
// ****************************************************************
	$omit_ary = explode(",", $omit_arg);			// $omit_arg: $retから取り除く引数(複数可)
	$ret = "";
	foreach ($http_arg as $key=>$value) {
		//if ($key <> $omit_arg) {
		if (!in_array($key, $omit_ary)) {		// $omit_argに無い引数
			if ($value <> "") {
				if ($ret <> "") $ret .= "&";
				$ret .= $key."=".urlencode($value);
			}
		}
	}
	return ($ret);
}
// ****************************************************************
function my_session_start($cache_limiter = "") {
// ****************************************************************
	session_set_cookie_params(0, MY_SESSION_PATH);
	//session_set_cookie_params(0, "/");
	session_cache_limiter($cache_limiter);			// nocache, private, private_no_expire, public
	session_start();					//ヘッダー出力の前に定義
	$_SESSION['debug_error_msg'] = _DEBUG_ERROR_MSG;	//エラー情報出力
	mb_language("Japanese");
	mb_internal_encoding('UTF-8');				//UTF-8, Shift-JIS, iso-8859-1
}
// ****************************************************************
function my_mysqli_connect($dbname, $to_global=True) {
// ****************************************************************
	global $con;
	$link = mysqli_connect(_DB_SERVER, _DB_USERNAME, _DB_PASSWORD, $dbname);
	if ($to_global) {
		$con = $link;
	}
	if (mysqli_connect_errno()) {
		if (_DEBUG_ERROR_MSG == 'YES') {
			error_msg('Connect failed: '.mysqli_connect_error(), True);
		}
		fatal_error('データベース接続エラー');
	}
	mysqli_set_charset($link, 'utf8');
	return $link;
}
// ****************************************************************
function my_mysqli_query($sql, $err_msg="", $con_link=NULL, $html=False) {
// ****************************************************************
	global $con;
	if ($con_link == NULL) {
		$rs = mysqli_query($con, $sql);
	} else {
		$rs = mysqli_query($con_link, $sql);		// 複数データベース接続用
	}
	if (!$rs) {
		if ($html) {
			html_header("Error");
		}
		if ($_SESSION['debug_error_msg'] == "YES") {
			//	error_msg("SQL: (".$sql.")<br>");
			sql_print_once($sql);
			if ($err_msg == "") {
				fatal_error("不正クエリ: " . mysqli_error($con));
			} else {
				fatal_error($err_msg . ": " . mysqli_error($con));
			}
		} else {
			error_exit('<br><br>エラー：検索条件が間違っている可能性があります。<br><br><span style="color:#000080">システム管理者は、実運用定義ファイルで<br>define("_DEBUG_ERROR_MSG", "YES")<br>にするとSQL文を確認できます。</span>');
		}
	}
	return $rs;
}
// ****************************************************************
function my_mysqli_query_debug_print($sql, $err_msg="", $con_link=NULL, $html=False) {
// ****************************************************************
	if (_DEBUG_ERROR_MSG == "YES" and $_SESSION['debug'] == "y") {
		sql_print_once($sql);
	}
	return my_mysqli_query($sql, $err_msg, $con_link, $html);
}
// ****************************************************************
function my_mysqli_insert_id() {
// ****************************************************************
	global $con;
	return mysqli_insert_id($con);
}
// ****************************************************************
function sql_print_once($sql) {
// ****************************************************************
	static $print_done = False;	// 複数回コールされても1回だけプリント
	if (!$print_done) {
		sql_print($sql);
		$print_done = True;
	}
}
// ****************************************************************
function error_exit($message, $html = False, $ret = True) {
// ****************************************************************
	if ($html) {
		html_header("Error");
	}
	error_msg($message);
	if ($html) {
		if ($ret) {
			echo "<br><br><p class='a_cancel_back left_margin'><a href='javascript:history.back();'>戻る</a></p>";
		}
		html_footer();
	}
	exit();
}
// ****************************************************************
function fatal_error($message, $html = False) {
// ****************************************************************
	if ($html) {
		html_header("Error");
	}
	error_msg("致命的エラー(".$message.")");
	if ($html) {
		echo "<br><br><p class='a_cancel_back left_margin'><a href='javascript:history.back();'>戻る</a></p>";
		html_footer();
	}
	exit();
}
// ****************************************************************
function error_msg($message, $html = False) {
// ****************************************************************
	if ($html) {
		html_header("Error");
	}
	echo "<br><br><p><span class='error_msg'>".$message."</span></p>";
}
// ****************************************************************
function noramal_msg($message, $brnum=2) {
// ****************************************************************
	for ($ix=0; $ix<$brnum; $ix++) {
		echo "<br>";
	}
	echo "<p><span class='noramal_msg'>".$message."</span></p>";
}
// ****************************************************************
function sql_print($sql) {
// ****************************************************************
	echo '<br><p><span>';
	echo sql_layout($sql);
	echo '</span></p><br>';
}
// ****************************************************************
function sql_layout($sql) {
// ****************************************************************
	$sql = str_replace('/* ALGORITHM=UNDEFINED */', '', strtolower($sql));
	$sql = preg_replace('/(select | from | where | join | left join | right join | left outer join | right outer join | on| and | or )/', ' <br><font color=red>$0</font>', $sql);
	//$sql = str_replace(',', ',<br>', $sql);
	return $sql;
}
// ****************************************************************
function my_GET($name) {					//$_GETデータを stripslashes する
// ****************************************************************
	if (get_magic_quotes_gpc()) {
		return stripslashes($_GET[$name]);
	} else {
		return $_GET[$name];
	}
}
// ****************************************************************
function form_str_adjust($str) {				//form入力データは stripslashes する
// ****************************************************************
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	$str = mb_convert_kana($str, "sKV");			//半角カナ→全角カナ,全角スペース→半角スペース
	return trim($str);
}
// ****************************************************************
function escape_squote($str) {
// ****************************************************************
	return str_replace("'", "\'", $str);
}
// ****************************************************************
function form_str_filepath($str) {				//form入力データは stripslashes する
// ****************************************************************
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return trim($str);
}
// ****************************************************************
function form_str_adjust_strip_tags($str) {			//HTMLタグを取り除く
// ****************************************************************
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	$str = strip_tags($str);
	$str = mb_convert_kana($str, "sKV");			//半角カナ→全角カナ,全角スペース→半角スペース
	return trim($str);
}
// ****************************************************************
function get_post_str($post_name) {				//POSTデータ取得(タグ除去)
// ****************************************************************
	return form_str_adjust_strip_tags($_POST[$post_name]);
}
// ****************************************************************
function post_to_mysql($post_name) {				//POSTデータをMySQL用に変換する
// ****************************************************************
	return str_for_mysql(form_str_adjust($_POST[$post_name]));
}
// ****************************************************************
function post_to_mysql_strip_tags($post_name) {			//POSTデータをMySQL用に変換する(タグ除去)
// ****************************************************************
	return str_for_mysql(form_str_adjust_strip_tags($_POST[$post_name]));
}
// ****************************************************************
function date_from_mysql($format, $mysqltime) {			//MySQL日付時間をフォーマットする
// ****************************************************************
	return date($format, strtotime($mysqltime));
}
// ****************************************************************
function str_for_mysql($str) {					//DB登録前に mysqli_real_escape_string をする
// ****************************************************************
	global $con;
	//$strはstripslashes済みが前提
	//$str = $str . " ";					//表予申能十ソ試時事私 etc. 文字化け対策(Shift-JISのみ)
	return ltrim(mysqli_real_escape_string($con, $str));
}
// ****************************************************************
function my_htmlspecialchars($str) {
// ****************************************************************
			//$strはstripslashes済み
			//if (get_magic_quotes_gpc()) {
			//	//$str = stripcslashes($str);
			//	$str = stripslashes($str);
			//}
	return trim(str_replace("＼", chr(0x5c), htmlspecialchars($str, ENT_QUOTES)));
}
// ****************************************************************
function my_htmlspecialchars_notrim($str) {
// ****************************************************************
	return str_replace("＼", chr(0x5c), htmlspecialchars($str, ENT_QUOTES));
}
// ****************************************************************
function ins_br_strip_script($str) {
// ****************************************************************
	$str = str_ireplace("<script","<xxxxx",$str);		//大文字/小文字区別なし
	$str = str_replace("\n","<br>",$str);
	return trim(str_replace("＼", chr(0x5c), $str));
}
// ****************************************************************
function ins_br($str) {
// ****************************************************************
	//$str = my_htmlspecialchars($str);
	$str = str_replace("\n","<br>",$str);
	return trim(str_replace("＼", chr(0x5c), $str));
}
// ****************************************************************
function ins_br_keystr($str, $keystr) {				//キーワードハイライト
// ****************************************************************
	$str = keystr_highlight($str, $keystr);
	$str = str_replace("\n","<br>",$str);
	return trim(str_replace("＼", chr(0x5c), $str));
}
function keystr_highlight($str, $keystr) {
	$keystr = trim($keystr);			// キーワードに()含まれる場合、スペースが付いてくる。
	if ($keystr == '') return $str;
	$keyary = explode(' ', $keystr);
	$newary = array();
	foreach ($keyary as $key) {
		$key = str_replace(chr(0x5c),chr(0x5c).chr(0x5c),$key);		// \ エスケープ
		$newary[] = '/'.$key.'/iu';
		if (MP_LIST_SELECT_COLLATE == 'collate utf8_unicode_ci') {
			if (preg_match("/^[ぁ-んー]+$/u", $key)) {		// ひらがな ==> カタカナ
				$newary[] = '/'.mb_convert_kana($key, 'C').'/u';
			} elseif (preg_match("/^[ァ-ヶー]+$/u", $key)) {	// カタカナ ==> ひらがな
				$newary[] = '/'.mb_convert_kana($key, 'c').'/u';
			} elseif (preg_match("/^[０-ｚ]+$/u", $key)) {		// 全角英数 ==> 半角英数
				$newary[] = '/'.mb_convert_kana($key, 'nr').'/iu';
			} elseif (preg_match("/^[a-zA-Z0-9]+$/", $key)) {	// 半角英数 ==> 全角英数
				$newary[] = '/'.mb_convert_kana($key, 'NR').'/iu';
			}
		}
	}
	$str = preg_replace_callback($newary, 'keystr_HIGHLIGHT_TAG', $str);
	return str_replace('_@#~', '<span class="find_string">', str_replace('_#@~', '</span>', $str));
}
function keystr_HIGHLIGHT_TAG($matches) {
	return '_@#~'.$matches[0].'_#@~';
}
// ****************************************************************
function url_2_atag($str) {					//URLをリンク(Aタグ)に変換する
// ****************************************************************
	//return preg_replace('/((http|https):\/\/[0-9a-z-\/._?=&%\[\]~]+)/i', '<a href="\\1" target="_blank">\\1</a>', $str);
	//return preg_replace('/((http|https):\/\/[0-9a-z-\/.!~*;:_?=&%\[\]~]+)/i', '<a href="\\1" target="_blank">\\1</a>', $str);
	return preg_replace("/https?:\/\/[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,%#]+/", "<a href=\"$0\" target=\"_blank\">$0</a>", $str);
}
// ****************************************************************
function url_2_atag_short($str) {				//URLをリンク(Aタグ)に変換する (リンクテキストを短縮)
// ****************************************************************
// Powered by http://oshiete1.goo.ne.jp/qa1267934.html
	$URLFilter ="'(https?|ftp|news)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)'";
	return preg_replace_callback($URLFilter, "MakeURLLink", $str);
}
function MakeURLLink($matches) {
	$TempURLText = $matches[0];
	if (isset($GLOBALS['URL_2_ATAG_SHORT_LEN'])) {
		$maxlen = $GLOBALS['URL_2_ATAG_SHORT_LEN'];		//丸める文字数
	} else {
		$maxlen = 20;
	}
/*
	if (strlen($matches[0]) > $maxlen){
		$TempURLText = substr($matches[0], 0, $maxlen)."...";	//$maxlenで文字列を切って「...」をつける
	}
*/
	$TempURLText = mb_strimwidth($matches[0], 0, $maxlen, '...');
	return "<a href=".$matches[0]." title=".$matches[0]." target=\"_blank\">".$TempURLText."</a>";
}
// ****************************************************************
function ins_atag($str) {					//タグ無効化＆URLにAタグ挿入
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = url_2_atag($str);
	return $str;
}
// ****************************************************************
function ins_atag_br($str) {					//タグ無効化＆URLにAタグ挿入 改行も挿入
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = url_2_atag($str);
	$str = str_replace("\n","<br>",$str);
	return $str;
}
// ****************************************************************
function ins_atag_br_short($str) {				//タグ無効化＆URLにAタグ挿入 改行も挿入 (リンクテキストを短縮)
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = url_2_atag_short($str);
	$str = str_replace("\n","<br>",$str);
	return $str;
}
// ****************************************************************
function ins_atag_br_keystr($str, $keystr) {			//タグ無効化＆URLにAタグ挿入 キーワードハイライト
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = keystr_highlight($str, $keystr);
	$str = url_2_atag($str);
	$str = str_replace("\n","<br>",$str);
	return $str;
}
// ****************************************************************
function ins_atag_br_keystr_li($str, $keystr) {			// ins_atag_br_keystr + <li>(\n\nで)
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = keystr_highlight($str, $keystr);
	$str = url_2_atag($str);
	$str = str_replace("\r","",$str);
	$str = str_replace("\n\n","</li><li>",$str);
	$str = str_replace("\n","<br>",$str);
	return '<li>'.$str.'</li>';
}
// ****************************************************************
function ins_atag_br_li($str) {					// ins_atag_br + <li>(\n\nで)
// ****************************************************************
	$str = my_htmlspecialchars($str);
	$str = str_replace("<","&lt;",$str);
//	$str = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$str);
	$str = url_2_atag($str);
	$str = str_replace("\r","",$str);
	$str = str_replace("\n\n","</li><li>",$str);
	$str = str_replace("\n","<br>",$str);
	return '<li>'.$str.'</li>';
}
// ****************************************************************
function jquery_highlight($target, $keystr) {			// jquery.highlight.jsによるキーワードハイライト
// ****************************************************************
	$keystr = trim($keystr);
	if ($keystr == '') return;
	if (!($keyary = keystr_to_array($keystr))) return;
?>
	<style>
	.highlight {
		color: #ff0000;
		background-color: #ffe0c0;
	}
	</style>
	<script src="../scripts/jquery.highlight.js"></script>
	<script>
	$(function(){
<?php
		foreach ($keyary as $key) {
?>
		$('<?= $target ?>').highlight('<?= $key ?>');
<?php
		}
?>
	});
	</script>
<?php
}
function keystr_to_array($keystr) {
	$newary = array();
	$keystr = trim($keystr);			// キーワードに()含まれる場合、スペースが付いてくる。
	if ($keystr == '') return $newary;
	$keyary = explode(' ', $keystr);
	foreach ($keyary as $key) {
		$key = str_replace(chr(0x5c),chr(0x5c).chr(0x5c),$key);		// \ エスケープ
		$newary[] = $key;
		if (MP_LIST_SELECT_COLLATE == 'collate utf8_unicode_ci') {
			if (preg_match("/^[ぁ-んー]+$/u", $key)) {		// ひらがな ==> カタカナ
				$newary[] = mb_convert_kana($key, 'C');
			} elseif (preg_match("/^[ァ-ヶー]+$/u", $key)) {	// カタカナ ==> ひらがな
				$newary[] = mb_convert_kana($key, 'c');
			} elseif (preg_match("/^[０-ｚ]+$/u", $key)) {		// 全角英数 ==> 半角英数
				$newary[] = mb_convert_kana($key, 'nr');
			} elseif (preg_match("/^[a-zA-Z0-9]+$/", $key)) {	// 半角英数 ==> 全角英数
				$newary[] = mb_convert_kana($key, 'NR');
			}
		}
	}
	return $newary;
}
// ****************************************************************
function str2ang_verA($str, $key) {			// (Shift-JISの場合) \"' が文字列内にあると誤動作する？？
// ****************************************************************
	$keyw = date(_DB_ANGOUKA_KEY, $key);
//if (get_magic_quotes_gpc()) {			//magic_quotes_gpc = On/Off チェック
//	$str = stripcslashes($str);
//}
	//$str = str_replace('\"',".....",$str);
	$len = strlen($str);
	$str2 = "A";							//暗号化バージョン'A'
	$n2 = 0;
	for ($nn = 0; $nn < strlen($str); ++$nn) {
		$str2 .= "." . ((int)ord(substr($str, $nn, 1)) + (int)ord(substr($keyw, $nn, 1)));
	}
	return ($str2);
}
// ****************************************************************
function str2ang($str, $key) {				// (Shift-JISの場合) \"' が文字列内にあると誤動作する？？
// ****************************************************************
	$keyw = date(_DB_ANGOUKA_KEY, $key);
//if (get_magic_quotes_gpc()) {			//magic_quotes_gpc = On/Off チェック
//	$str = stripcslashes($str);
//}
	//$str = str_replace('\"',".....",$str);
	$len = strlen($str);
	if (defined("_DB_ANGOUKA_KEY_EOR") and _DB_ANGOUKA_KEY_EOR <> '') {
		$str = $str ^ seed_create($str, _DB_ANGOUKA_KEY_EOR);	//ビット演算XOR(排他的論理和)で暗号化
		$str2 = "B";					//暗号化バージョン'B'
	} else {
		$str2 = "A";
	}
	$n2 = 0;
	for ($nn = 0; $nn < strlen($str); ++$nn) {
		$str2 .= "." . ((int)ord(substr($str, $nn, 1)) + (int)ord(substr($keyw, $nn, 1)));
	}
	return ($str2);
}
// ****************************************************************
function ang2str($str, $key) {				// (Shift-JISの場合) \"' が文字列内にあると誤動作する？？
// ****************************************************************
	if (substr($str,0,1) == "B") {				//暗号化バージョン'B'
		$str = ang2str_in($str, $key);
		$str = $str ^ seed_create($str, _DB_ANGOUKA_KEY_EOR);	//ビット演算XOR(排他的論理和)で暗号化
	} elseif (substr($str,0,1) == "A") {			//暗号化バージョン'A'
		$str = ang2str_in($str, $key);
	}
	return (my_htmlspecialchars($str));
}
function seed_create($str, $seed) {
	$str_len = strlen($str);
	$seed_len = strlen($seed);
	$loopcnt = ceil($str_len / $seed_len);	//切り上げ
	$long_seed = '';
	for($ix=0; $ix<=$loopcnt; $ix++){
		$long_seed .= $seed;
	}
	return left($long_seed, $str_len);
}
// ****************************************************************
function ang2str_in($str, $key) {
// ****************************************************************
	$keyw = date(_DB_ANGOUKA_KEY, $key);
	$str2 = "";
	$str1 = substr($str,2);					//先頭2文字を除去
	$count = 0;
	$token = strtok($str1, ".");				//文字列分割
	while ($token) {
		$str2 .= chr((int)($token) - (int)ord(substr($keyw, $count, 1)));
		$token = strtok(".");
		$count++;
	}
	//$str2 = urldecode($str2);
	//return (addslashes($str2));
	//$str2 = str_replace(".....",'\"',$str2);
	return ($str2);
}
// ****************************************************************
function mt_rand_array($min, $max, $num) {			// 全てがユニークな値となる乱数を返す
// ****************************************************************
	$rand = array();
	for ($ix=0; $ix<=$num-1; $ix++) {
		do {
			$r1 = mt_rand($min, $max);
		} while (array_search($r1, $rand) !== FALSE);
		$rand[$ix] = $r1;
	}
	return($rand);
}
// ****************************************************************
function toInt($str) {
// ****************************************************************
	if ($str == "") {
		return 0;
	} else if (is_numeric($str)) {
		return intval($str);
	} else {
		return 0;
	}
}
// ****************************************************************
function left($str, $len) {
// ****************************************************************
// $lenには、mb_internal_encoding('UTF-8')設定状態で、mb_strpos(),mb_strrpos()の値を使わないと不具合発生。
// mb_substr()は、mb_internal_encoding('UTF-8')設定状態で利用しないと、substr()と同じ動作になる。
// 互換性維持のため、mb_substr()の第4引数での'utf-8'指定はあえてしない。
	return (mb_substr($str,0,$len));
}
// ****************************************************************
function left_short($str, $len) {
// ****************************************************************
	return (mb_strimwidth($str, 0, $len, '...'));
/*
	if (mb_strlen($str) > $len) {
		return (left($str,$len).'...');
	} else {
		return (left($str,$len));
	}
*/
}
// ****************************************************************
function right($str, $len) {
// ****************************************************************
// $lenには、mb_internal_encoding('UTF-8')設定状態で、mb_strpos(),mb_strrpos()の値を使わないと不具合発生。
// mb_substr()は、mb_internal_encoding('UTF-8')設定状態で利用しないと、substr()と同じ動作になる。
// 互換性維持のため、mb_substr()の第4引数での'utf-8'指定はあえてしない。
	return (mb_substr($str,-$len));
}
// ****************************************************************
function str_width($str) {
// ****************************************************************
// 文字列の文字幅(漢字は2)を求める
	return strlen(mb_convert_encoding($str,'SJIS','UTF-8'));
}
// ****************************************************************
function hex2rgb($hex) {
// ****************************************************************
// $hex: FFA500 → 255,165,0
	return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
// ****************************************************************
function get_intag($contents, $str1, $str2, &$pos) {
// ****************************************************************
// $str1と$str2で囲まれた文字列を返す 開始位置は$pos
// $posは$str2の後ろの位置に進める
	$slen1 = strlen($str1);
	$slen2 = strlen($str2);
	$pos1 = stripos($contents, $str1, $pos);		// 大文字小文字区別なし
	$pos2 = stripos($contents, $str2, $pos1+$slen1);	// 大文字小文字区別なし
	if (($pos1 !== False) && ($pos2!== False)) {		// ===　演算子
		$getstr = substr($contents, $pos1+$slen1, $pos2-$pos1-$slen1);
		$pos = $pos2 + $slen2;
		return $getstr;
	} else {
		return "";
	}
	return "";
}
// ****************************************************************
function get_intag_span($contents, $str1, $str2, &$pos) {	// $str1, $str2含んだ文字列を返す
// ****************************************************************
// $str1と$str2で囲まれた文字列を返す 開始位置は$pos
// $posは$str2の後ろの位置に進める
	$slen1 = strlen($str1);
	$slen2 = strlen($str2);
	$pos1 = stripos($contents, $str1, $pos);		// 大文字小文字区別なし
	$pos2 = stripos($contents, $str2, $pos1+$slen1);	// 大文字小文字区別なし
	if (($pos1 !== False) && ($pos2!== False)) {		// ===　演算子
		$getstr = substr($contents, $pos1, $pos2-$pos1);
		$pos = $pos2 + $slen2;
		return $getstr;
	} else {
		return "";
	}
	return "";
}
// ****************************************************************
function strip_span_str($contents, $str1, $str2) {	// $str1, $str2含んだ文字列を除去する
// ****************************************************************
	$slen1 = strlen($str1);
	$slen2 = strlen($str2);
	$ret = '';
	$pos = 0;
	while (($find = stripos($contents, $str1, $pos)) !== false) {	// ===　演算子
		$ret .= substr($contents, $pos, $find-$pos);
		$pos = $find + $slen1;
		if (($find = stripos($contents, $str2, $pos)) !== false) {
			$pos = $find + $slen2;
		}
	}
	$ret .= substr($contents, $pos);
	return $ret;
}
// ****************************************************************
function array_index(&$ary1, $num, $str) {
// ****************************************************************
// $ary1から$strを検索し、そのインデックスを返す
// $num: $ary1の個数
	for ($ix=0; $ix<$num; $ix++) {
		if ($ary1[$ix] == $str) {
			return $ix;
		}
	}
	return False;
}
// ****************************************************************
function redirect($url) {
// ****************************************************************
	header("Location: $url");				//Redirect browser
	exit;
}
// ****************************************************************
if (!function_exists('htmlspecialchars_decode')) {
// ****************************************************************
	function htmlspecialchars_decode($text) {
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}
// ****************************************************************
if (!function_exists('stripos')) {
// ****************************************************************
	function stripos($str, $needle, $offset=0) {
		return strpos(strtolower($str), strtolower($needle), $offset);
	}
}
// ****************************************************************
function stripos_multi_and($haystack, $needle) {
// ****************************************************************
	$needleary = explode(' ', trim(str_replace('　', ' ', $needle)));
	foreach ($needleary as $needle) {
		if (stripos($haystack, $needle) === false) {
			return false;
		}
	}
	return true;
}
// ****************************************************************
function file_upload($varName, $id, $storeFolder, $user_id=NULL) {
// ****************************************************************
	$fileName = $_FILES[$varName]['name'];
	$fileSize = $_FILES[$varName]['size'];
	$fileType = $_FILES[$varName]['type'];		// MIME タイプ
	$fileError = $_FILES[$varName]['error'];
	if ($fileSize > 0 ) {
		//アップロードされたテンポラリファイルの情報を取得します
		$fileInfo = pathinfo($fileName);
		$fileext = strtoupper($fileInfo[extension]);	//ファイルの拡張子
		$tmpfilename = $_FILES[$varName]['tmp_name'];
		if (is_uploaded_file($tmpfilename)) {
			$targetFileUTF8 = right("00000000".$id, 8)."-".$fileName;
			$targetFileSJIS = right("00000000".$id, 8)."-".myfile_ENCODE($fileName);
			$storeFolderSJIS = myfile_ENCODE($storeFolder);
			if ($user_id <> NULL) {
				$storeFolderSJIS = $storeFolderSJIS.$user_id.'/';	// $user_id毎のフォルダに格納
				if (!file_exists($storeFolderSJIS)) {
					mkdir($storeFolderSJIS);
				}
			}
			$targetPath = $storeFolderSJIS.$targetFileSJIS;
			if (!move_uploaded_file($tmpfilename , $targetPath)) {
				error_exit("ファイルアップロード失敗：".$fileName);
			}
		}
		return($targetFileUTF8);
	} else {
		return("");
	}
}
// ****************************************************************
function file_upload_to_name($varName, $storeFolder, $storeFileName) {
// ****************************************************************
	$fileName = $_FILES[$varName]['name'];
	$fileSize = $_FILES[$varName]['size'];
	$fileType = $_FILES[$varName]['type'];		// MIME タイプ
	$fileError = $_FILES[$varName]['error'];
	if ($fileSize > 0 ) {
		//アップロードされたテンポラリファイルの情報を取得します
		$fileInfo = pathinfo($fileName);
		$tmpfilename = $_FILES[$varName]['tmp_name'];
		if (is_uploaded_file($tmpfilename)) {
			$storeFileNameSJIS = myfile_ENCODE($storeFileName);
			$targetPath = myfile_ENCODE($storeFolder).$storeFileNameSJIS;
			if (!move_uploaded_file($tmpfilename , $targetPath)) {
				error_exit("ファイルアップロード失敗：".$fileName);
			}
		}
		return($storeFileName);
	} else {
		return("");
	}
}
// ****************************************************************
function current_contents_name($script_name) {
// ****************************************************************
	if (right($script_name,1) <> '/' && rightlower($script_name,4) <> '.php') {
		$script_name = $script_name . '/';
	}
	$cn_p2 = strrpos($script_name, '/');
	$cn_p1 = strrpos(mb_substr($script_name,0,$cn_p2), '/');
	return (substr($script_name, $cn_p1+1, $cn_p2-$cn_p1-1));
}
// ****************************************************************
function date_view($date) {
// ****************************************************************
	return(date_from_mysql("Y-m-d", $date).day_week_view($date));
}
// ****************************************************************
function day_week_view($date, $bracket=false) {
// ****************************************************************
	$day_week = mb_substr("日月火水木金土", date_from_mysql("w", $date), 1);
	if ($day_week == '日') {
		$day_class = "dayweek_sunday";
	} elseif ($day_week == '土') {
		$day_class = "dayweek_saturday";
	} else {
		$day_class = "dayweek_weekday";
	}
	if ($bracket) {
		$day_week = '('.$day_week.')';
	}
	return("<span class='".$day_class."'>".$day_week."</span>");
}
// ****************************************************************
function sch_time_format($time1, $time2, $min_0_off=False) {
//	$min_0_off=True : 分が0の時表示しない
// ****************************************************************
	$str = "";
	if ($time1."" <> "") {
		if ($min_0_off && date_from_mysql("i", $time1)=='00') {
			$str .= date_from_mysql("H", $time1);
		} else {
			$str .= date_from_mysql("H:i", $time1);
		}
	}
	if ($time2."" <> "") {
		if ($min_0_off && date_from_mysql("i", $time2)=='00') {
			$str .= "-".date_from_mysql("H", $time2);
		} else {
			$str .= "-".date_from_mysql("H:i", $time2);
		}
	}
	return($str);
}
// ****************************************************************
function images_print($dir_path, $column) {
// ****************************************************************
	$file_table = array();
	$file_count = 0;
	if ($dir = opendir($dir_path)) {
		//while (($file = readdir($dir)) !== false) {
		$files = scandir($dir_path);
		foreach ($files as $file) {
			if ($file != "." && $file != ".." && !is_dir($dir_path . "/" . $file) && is_img_filename($file)) {
				$file_table[] = $file;
				$file_count++;
			}
		} 
		closedir($dir);
	}
	if ($file_count != 0) {
		images_print_table($dir_path, $column, $file_table, $file_count);
	}
}
// ****************************************************************
function images_print_noprint_ary($dir_path, $column, $no_print) {
// ****************************************************************
	$file_table = array();
	$file_count = 0;
	if ($dir = opendir($dir_path)) {
		//while (($file = readdir($dir)) !== false) {
		$files = scandir($dir_path);
		foreach ($files as $file) {
			if ($file != "." && $file != ".." && !is_dir($dir_path . "/" . $file) && is_img_filename($file)) {
				if (!in_array($file, $no_print)) {
					$file_table[] = $file;
					$file_count++;
				}
			}
		} 
		closedir($dir);
	}
	if ($file_count != 0) {
		images_print_table($dir_path, $column, $file_table, $file_count);
	}
}
// ****************************************************************
function is_ext_filename_str($str, $extstr) {			// 拡張子チェック $extstr: 文字列(ex: 'avi,mpg')
// ****************************************************************
	return is_ext_filename($str, explode(',', $extstr));
}
// ****************************************************************
function is_ext_filename($str, $ext_ary, $mime_ary=NULL) {	// 拡張子チェック $ext_ary: 配列(ex: array('avi', 'mpg'))
// ****************************************************************
	for($ix=0; $ix<count($ext_ary); $ix++){ 
		if (strtolower(strrchr($str, '.')) == '.'.$ext_ary[$ix]) {
			if ($mime_ary <> NULL) {
				return $mime_ary[$ix];
			} else {
				return true;
			}
		}
	}
	return false;
}
// ****************************************************************
function is_video_filename($str) {
// ****************************************************************
	static $video_ext = NULL;
	static $video_mime = NULL;
	if ($video_ext == NULL) {
		if (IS_VIDEO_EXT.'' <> '') {
			$video_ext = explode(',', IS_VIDEO_EXT);
		}
		if (IS_VIDEO_MIME.'' <> '') {
			$video_mime = explode(',', IS_VIDEO_MIME);
		}
	}
	if ($video_ext != NULL) {
		return is_ext_filename($str, $video_ext, $video_mime);
	} else {
		return false;
	}
}
// ****************************************************************
function is_img_filename($str) {
// ****************************************************************
	static $img_ext = NULL;
	if (!defined("IS_IMG_EXT")) {
		define("IS_IMG_EXT", "jpeg,jpg,gif,png");
	}
	if ($img_ext == NULL) {
		$img_ext = explode(',', IS_IMG_EXT);
	}
	return is_ext_filename($str, $img_ext);
}
// ****************************************************************
function is_img_filename_OLD($str) {
// ****************************************************************
	if (rightlower($str,5) == ".jpeg" || rightlower($str,4) == ".jpg" || rightlower($str,4) == ".gif" || rightlower($str,4) == ".png") {
		return true;
	} else {
		return false;
	}
}
// ****************************************************************
function rightlower($str, $len) {		// 指定の長さを小文字にして取り出す
// ****************************************************************
	return (strtolower(right($str, $len)));
}
// ****************************************************************
function is_write_permit() {
// ****************************************************************
	if ($_SESSION['current_permit_type'] == "w") {		// 書込権限
		return True;
	} else {
		return False;
	}
}
// ****************************************************************
function up_folder_path($str) {
// ****************************************************************
	$str = str_replace(DIRECTORY_SEPARATOR, "/", right_slash_strip($str));
	if (($pos=mb_strrpos($str, "/")) !== false) {
		return (mb_substr($str, 0, $pos));
	}
	return ($str);
}
// ****************************************************************
function up_folder_direct($url, $path) {
// ****************************************************************
	$folders = '';
	$cpath = '';
	$pathary = explode('/', $path);
	foreach ($pathary as $path) {
		$cpath .= $path.'/';
		$folders .= '<a href="'.$url.urlencode($cpath).'">';
		$folders .= $path;
		$folders .= '</a>/';
	}
	return $folders;
}
// ****************************************************************
function right_slash_strip($str) {				// 右側のスラッシュを取り除く
// ****************************************************************
	return (rtrim($str, '/'));
}
// ****************************************************************
function concat_str_with_sep(&$str0, $str1, $comma=',') {
// ****************************************************************
	if ($str0 == '') {
		$str0 .= $str1;
	} else {
		$str0 .= $comma.$str1;
	}
}
// ****************************************************************
function textarea_rows($str, $width) {
// ****************************************************************
	$ary = explode("\n", $str);
	$rows = 0;
	foreach ($ary as $rowstr) {
		$strwidth = mb_strwidth($rowstr);
		$rows += ceil($strwidth / $width * 6.4);
	}
	return $rows;
}
// ****************************************************************
function ymd_reset(&$year, &$month, &$day) {
// ****************************************************************
	$ymd = mktime(0, 0, 0, $month, $day, $year);
	$year = date('Y', $ymd);
	$month = date('n', $ymd);
	$day = date('j', $ymd);
}
// ****************************************************************
function ymd_week($year, $month, $day) {
// ****************************************************************
	return mb_substr("日月火水木金土", date('w', strtotime($year.'/'.$month.'/'.$day)), 1);
}
// ****************************************************************
function keystr_fix($keystring) {
// ****************************************************************
	// 半角英数字,半角カナ→全角カナ,全角スペース→半角スペース
	// 連続する半角スペースを半角スペース1つに
	return trim(preg_replace('!\s+!', ' ', mb_convert_kana($keystring, 'sKV')));
}
// ****************************************************************
function keystr_and_or($keystr) {
// ****************************************************************
	$keystr = str_replace(array('&','|','*','+','(',')','＆','｜','＊','＋','（','）'), ' ', $keystr);
	// 連続する半角スペースを半角スペース1つに
	return preg_replace('!\s+!', ' ', $keystr);
}
// ****************************************************************
function query_and_or($items, $keystr, $table='') {
// ****************************************************************
	$keystr = str_replace(array('&','|','*','+','＆','｜','＊','＋','（','）'), 
			array('&','|','&','|','&','|','&','|','(',')'), $keystr);
	$keystr = trim($keystr);
	if ($keystr == '') return false;
	$keystr = str_replace(chr(0x5c),"＼",$keystr);		// \ エスケープ
	$keystr = str_replace('"','\"',$keystr);
	if (strstr($keystr, ')') || strstr($keystr, '(') || strstr($keystr, '|') || strstr($keystr, '&')) {
		return query_and_or_multi($items, $keystr, $table);
	} else {
		return query_and_or_simple($items, $keystr, $table);
	}
}
function query_and_or_simple($items, $keystr, $table='') {
	$sql = '';
	// 連続する半角スペースを半角スペース1つに
	$keystr = preg_replace('!\s+!', " ", $keystr);
	$keyary = explode(" ", $keystr);
	foreach ($keyary as $key) {
		if ($sql <> '') {
			$sql .= ' ) and ( ';
		}
		$insql = '';
		foreach ($items as $item) {
			if ($insql <> '') {
				$insql .= ' or ';
			}
			if ($table <> '') $insql .= $table.'.';
			$insql .= $item.' '.MP_LIST_SELECT_COLLATE.' like "%'.$key.'%"';
		}
		$sql .= $insql;
	}
	return '( '.$sql.' )';
}
function query_and_or_multi($items, $keystr, $table='') {
	$sql = '';
	$keystr = str_replace(array('(',')','&','|'), array(' ( ',' ) ',' & ',' | '), $keystr);
	// 連続する半角スペースを半角スペース1つに
	$keystr = preg_replace('!\s+!', ' ', $keystr);
	$keystr = trim($keystr);
	$keyary = explode(' ', $keystr);
	foreach ($keyary as $key) {
		if ($key == '(' || $key == ')') {
			$sql .= ' '.$key.' ';
		} elseif ($key == '&') {
			$sql .= ' and ';
		} elseif ($key == '|') {
			$sql .= ' or ';
		} else {
			$insql = '(';
			foreach ($items as $item) {
				if ($insql != '(') $insql .= ' or ';
				if ($table <> '') $insql .= $table.'.';
				$insql .= $item.' '.MP_LIST_SELECT_COLLATE.' like "%'.$key.'%"';
			}
			$insql .= ')';
			$sql .= $insql;
		}
	}
	return $sql;
}
// ****************************************************************
function check_post_account($post_login_id, $post_current_id=0) {
// ****************************************************************
	if ($post_login_id <> $_SESSION['login_id']) {
		error_exit("ログインユーザーが変更されているため、更新は無効です。", True, False);
	}
	if ($post_current_id <> 0) {
		if ($post_current_id <> $_SESSION['current_id']) {
			error_exit("カレントユーザーが変更されているため、更新は無効です。", True, False);
		}
	}
}
// ****************************************************************
function my_send_mail2($params, $to, $headers, $body) {
// ****************************************************************
/*
	$params['host'] = 'localhost';
	$params['port'] = 25;
	$params['auth'] = 'True'/'False';	// SMTP認証
	$params['username'] = '';
	$params['password'] = '';
	$headers['Subject'] = '';
	$headers['From'] = '';
	$headers['To'] = '';
	$headers['Cc'] = '';
	$headers['Bcc'] = '';
	$headers['Reply-To'] = '';
*/
	require_once('Mail.php');	// PEAR::Mail
	if (empty($params['port'])) {
		 $params['port'] = 25;
	}
	if ($params['auth'].'' == 'True') {
		 $params['auth'] = True;
	} else {
		 $params['auth'] = False;
	}
	$mailObj = Mail::factory('smtp', $params);
	mb_convert_variables('ISO-2022-JP', 'UTF-8', $headers);
	$body = mb_convert_encoding($body, 'ISO-2022-JP', 'UTF-8');
	$ret = $mailObj -> send($to, $headers, $body);
	if (PEAR::isError($ret)) {
		return False;
	}
	return True;
}
// ****************************************************************
function my_send_mail($from_account, $header_from, $to, $subject, $body, $host, $port=25, $auth=False, $authuser='', $authpass='') {
// ****************************************************************
	// $from_account : 未使用
	require_once('Mail.php');	// PEAR::Mail
	$params = array(
		'host' => $host
		,	'port' => $port
		,	'auth' => $auth
		,	'username' => $authuser
		,	'password' => $authpass
	);
	$mailObj = Mail::factory('smtp', $params);
	$headers = array(
		'To' => mb_convert_encoding($to, 'ISO-2022-JP', 'UTF-8'),
		'From' => mb_convert_encoding($header_from, 'ISO-2022-JP', 'UTF-8'),
		'Subject' => mb_convert_encoding($subject, 'ISO-2022-JP', 'UTF-8')
	);
	$body = mb_convert_encoding($body, 'ISO-2022-JP', 'UTF-8');
	$ret = $mailObj -> send($to, $headers, $body);
	if (PEAR::isError($ret)) {
		//echo $ret->getMessage();
		return False;
	}
	return True;
/*
	// -- mb_send_mail() ---
	ini_set('SMTP', $host);
	ini_set('sendmail_from', $from_account);
	$header = 'From: ' . $header_from;
	return mb_send_mail($to, $subject, $body, $header);
*/
}
// ****************************************************************
function roman_number($number) {
// ****************************************************************
	if ($number > 10) $number = 0;
//	$roman = array('', 'Ⅰ','Ⅱ','Ⅲ','Ⅳ','Ⅴ','Ⅵ','Ⅶ','Ⅷ','Ⅸ','Ⅹ');
	$roman = array('', 'Ｉ','II','III','IV','Ｖ','VI','VII','VIII','IX','Ｘ');
	return $roman[$number];
}
// ****************************************************************
function roman_number_PEAR($number) {
// ****************************************************************
	require_once 'Numbers/Roman.php';
	return Numbers_Roman::toNumeral($number);
}
// ****************************************************************
function write_error_log($filename, $p_str, $err_str) {
// ****************************************************************
// _SYSTEM_FILE_ENCODING (SJIS-win) で書き込み
	if (defined("ERROR_LOG_DIR")) {
		if (!@opendir(ERROR_LOG_DIR)) {
			@mkdir(ERROR_LOG_DIR);
		}
		if ($fp = @fopen(ERROR_LOG_DIR.'/'.$filename, 'a')) {
			if (!mb_check_encoding($err_str, _SYSTEM_FILE_ENCODING)) {
				$err_str = myfile_ENCODE($err_str);
			}
			@fwrite($fp, date("Y/m/d H:i:s").' ['.$_SERVER['SCRIPT_NAME'].'] '.$p_str.':'.$err_str."\r\n");
			@fclose($fp);
		}
	}
}
// ****************************************************************
function write_exec_log($filename, $p_str, &$exec_out) {
// ****************************************************************
	if (defined("ERROR_LOG_DIR")) {
		if (!@opendir(ERROR_LOG_DIR)) {
			@mkdir(ERROR_LOG_DIR);
		}
		if ($fp = @fopen(ERROR_LOG_DIR.'/'.$filename, 'a')) {
			foreach ($exec_out as $err_str) {
				if (!mb_check_encoding($err_str, _SYSTEM_FILE_ENCODING)) {
					$err_str = myfile_ENCODE($err_str);
				}
				@fwrite($fp, date("Y/m/d H:i:s").' ['.$_SERVER['SCRIPT_NAME'].'] '.$p_str.':'.$err_str."\r\n");
			}
			@fclose($fp);
		}
	}
	unset($exec_out);
}
// ****************************************************************
function my_file_get_contents($url) {
// ****************************************************************
	static $me_called = False;
	static $proxy_context;
	if (!$me_called) {
		if (HTTP_PROXY_HOST.'' <> '') {
			$proxy = array(
				'http' => array(
					'proxy' => 'tcp://'.HTTP_PROXY_HOST.':'.HTTP_PROXY_PORT,
					'request_fulluri' => true,
				),
			);
			$proxy_context = stream_context_create($proxy);
		} else	{
			$proxy_context = False;
		}
	}
	$me_called = True;
	// httpsはエラーになる
	if ($proxy_context) {
		$html = @file_get_contents($url, False, $proxy_context);
	} else {
		$html = @file_get_contents($url);
	}
	return $html;
}
// ****************************************************************
function my_simplexml_load_file($url) {
// ****************************************************************
	static $me_called = False;
	static $proxy_context;
	if (!$me_called) {
		if (HTTP_PROXY_HOST.'' <> '') {
			$r_default_context = stream_context_get_default ( array
				('http' => array(
					'proxy' => 'tcp://'.HTTP_PROXY_HOST.':'.HTTP_PROXY_PORT,
						'request_fulluri' => True,
					),
				)
			);
			libxml_set_streams_context($r_default_context);
		}
	}
	$me_called = True;
	$daten = simplexml_load_file($url);
	return $daten;
}
// ****************************************************************
function curl_get_contents($url, $timeout=120) {
// ****************************************************************
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	if (HTTP_PROXY_HOST.'' <> '') {
	//	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);	// これを指定するとエラーになる。
		curl_setopt($ch, CURLOPT_PROXY, 'tcp://'.HTTP_PROXY_HOST.':'.HTTP_PROXY_PORT);
		curl_setopt($ch, CURLOPT_PROXYPORT, HTTP_PROXY_PORT);
	}
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
// ****************************************************************
function quote_chg($str) {
// ****************************************************************
	return str_replace("'", '’', $str);
}
// ****************************************************************
function post_to_cookie($get_name, $post_name, $default='', $cookie_name='', $type='') {
// ****************************************************************
//	setcookieするため、HTML出力前にコールすること
	if ($get_name.'' <> '' and $_GET[$get_name] <> '') {
		if ($type == 'i') {
			$data = floor(my_GET($get_name));
		} else {
			$data = my_GET($get_name);
		}
	} elseif ($post_name.'' <> '' and $_POST[$post_name] <> '') {
		if ($type == 'i') {
			$data = floor($_POST[$post_name]);
		} else {
			$data = $_POST[$post_name];
		}
	} else {
		$data = $default;
	}
	if ($cookie_name <> '') {
		setcookie(SESSION_PREFIX.'_'.$cookie_name, $data, time() + LOGIN_COOKIE_EXPIRE, MY_SESSION_PATH);
	}
	return $data;
}
// ****************************************************************
function set_val_cookie($cookie_name, $default, $get_name='') {
// ****************************************************************
	if ($_COOKIE[SESSION_PREFIX.'_'.$cookie_name].'' <> '') {
		return $_COOKIE[SESSION_PREFIX.'_'.$cookie_name];
	}
	if ($get_name <> '' and $_GET[$get_name] <> '') {
		return my_GET($get_name);
	}
	return $default;
}
// ****************************************************************
function form_select($select_name, $options_str, $default, $cookie_name='') {
// ****************************************************************
	if ($cookie_name <> '') {
		if ($_COOKIE[SESSION_PREFIX.'_'.$cookie_name].'' <> '') {
			$selected = $_COOKIE[SESSION_PREFIX.'_'.$cookie_name];
		} else {
			$selected = $default;
		}
	} else {
		$selected = $default;
	}
?>
	<select name="<?= $select_name ?>">
	<?php
		$options = explode(',', $options_str);
		foreach ($options as $opt) {
	?>
		<option value="<?= $opt ?>"<?= $opt == $selected ? ' selected' : '' ?>><?= $opt ?>
	<?php
		}
	?>
		</select>
<?php
}
// ****************************************************************
function query_string_strip($key) {	// QUERY_STRING から、特定のKEY=VALUEのセットを除く
// ****************************************************************
	$querys = explode('&', $_SERVER['QUERY_STRING']);
	$striped = '';
	$len = strlen($key);
	foreach ($querys as $query) {
		if (substr($query, 0, $len+1) <> $key.'=') {
			if ($striped <> '') {
				$striped .= '&';
			}
			$striped .= $query;
		}
	}
	return $striped;
}
// ****************************************************************
function query_string_strip_multi($keys_str) {	// QUERY_STRING から、特定のKEY=VALUEのセットを除く
// ****************************************************************
	$keys = explode(',', $keys_str);
	$querys = explode('&', $_SERVER['QUERY_STRING']);
	$striped = '';
	foreach ($querys as $query) {
		$exist = False;
		foreach ($keys as $key) {
			$len = strlen($key);
			if (substr($query, 0, $len+1) == $key.'=') {
				$exist = True;
			}
		}
		if (!$exist) {
			if ($striped <> '') {
				$striped .= '&';
			}
			$striped .= $query;
		}
	}
	return $striped;
}
// ****************************************************************
function html_src_2_http($html) {
// ****************************************************************
	return preg_replace_callback("/ src\=[\"\']([^\"\']+)[\"\']/i", "html_src_2_http_callback", $html);
}
function html_src_2_http_callback($matches) {
	global $global_url;
	return ' src="'.get_abs_url($global_url, $matches[1]).'"';
}
// ****************************************************************
function html_href_2_http($html) {
// ****************************************************************
	return preg_replace_callback("/ href\=[\"\']([^\"\']+)[\"\']/i", "html_href_2_http_callback", $html);
}
function html_href_2_http_callback($matches) {
	global $global_url;
	return ' href="'.get_abs_url($global_url, $matches[1]).'"';
}
// ****************************************************************
function get_abs_url($baseURL, $relativePath) {
// ****************************************************************
// ベースURL と 相対パス情報から、絶対パス(http(s)://～～)を返す
// by http://d.hatena.ne.jp/ja9/20090930/1254292933
	if ($relativePath == '') {
		// 相対パスが空の場合は、baseURLをそのまま返す
		return $baseURL;
	}
	if (preg_match('@^https?://@iD', $relativePath)) {
		// 相対パスが http(s):// から始まる場合は、そのまま返す
		return $relativePath;
	}
	if (left($relativePath,2) == '//') {			// http:が省略されている場合 by ok.2nd
		// 相対パスが // から始まる場合は、そのまま返す
		return $relativePath;
	}
	// baseURL の分解
	if (!preg_match('@^(https?://[^/]+)/?(.*)$@iD',
		$baseURL, $tmpMatches)) {
		// http(s)://～～から始まらない場合
		if ($baseURL[0] != '/') {
			return false;
		}
		$tmpMatches = array(
			$baseURL,
			'',
			substr($baseURL, 1)
		);
	}
	$base = $tmpMatches[1];	 // e.g. http://www.example.com
	$tmpPath = $tmpMatches[2];  // e.g. hoge/fuga/index.php
	$path = array();
	if (preg_match('@^/@iD', $relativePath)) {
		// 相対パスが/から始まる場合
		return $base . $relativePath;
	}
	// baseURLパス情報にディレクトリが含まれていれば
	// baseURLのパス情報をディレクトリ情報のみに
	if (strlen($tmpPath) > 0 && strpos($tmpPath, '/') !== false) {
		if ($tmpPath[strlen($tmpPath) - 1] == '/') {
			// 最後が / なら/を削除
			$tmpPath = substr($tmpPath, 0, -1);
		} else {
			// 最後が / ではない(ファイル名)の場合、/ までを削除
			$tmpPath = substr($tmpPath, 0, strrpos($tmpPath, '/'));
		}
		// ディレクトリ名毎に配列にする
		$path = explode('/', $tmpPath);
	}
	// 相対パス情報をディレクトリ毎に配列にする
	$relativePath = explode('/', $relativePath);
	// 相対パスディレクトリ毎に
	foreach($relativePath as $dir) {
		if ($dir == '.') {
			// /./ は処理をスキップ
			continue;
		}
		if (preg_match('@^\.+$@iD', $dir)) {
			// /../ /.../ などなら、ディレクトリを上にたどる
			for ($i=1; $i < strlen($dir); $i++) {
				array_pop($path);
			}
			continue;
		}
		// .以外のディレクトリ名の場合は、そのまま追加
		$path[] = $dir;
	}
	// パスを/で結合
	$path = implode('/', $path);
	return $base . '/' . $path;
}
// ****************************************************************
function my_hash($password) {
// ****************************************************************
	return hash(PASSWORD_COOKIE_HASH_ALGO, $password);
}
// ****************************************************************
function myfile_fopen($path, $mode) {
	return fopen(myfile_ENCODE($path), $mode);
}
function myfile_file_exists($path) {
	return file_exists(myfile_ENCODE($path));
}
function myfile_is_file($path) {
	return is_file(myfile_ENCODE($path));
}
function myfile_rename($oldpath, $newpath) {
	return rename(myfile_ENCODE($oldpath), myfile_ENCODE($newpath));
}
function myfile_unlink($path) {
	return unlink(myfile_ENCODE($path));
}
function myfile_file_get_contents($path) {
	return file_get_contents(myfile_ENCODE($path));
}
function myfile_file_put_contents($path, $data, $flags=0) {
	return file_put_contents(myfile_ENCODE($path), $data, $flags);
}
function myfile_copy($srcpath, $dstpath) {
	return copy(myfile_ENCODE($srcpath), myfile_ENCODE($dstpath));
}
function myfile_scandir($path) {
	return scandir(myfile_ENCODE($path));
}
function myfile_opendir($path) {
	return opendir(myfile_ENCODE($path));
}
function myfile_is_dir($path) {
	return is_dir(myfile_ENCODE($path));
}
function myfile_mkdir($path, $mode='0777', $recursive=false) {
	return mkdir(myfile_ENCODE($path), $mode, $recursive);
}
function myfile_rmdir($path) {
	return rmdir(myfile_ENCODE($path));
}
function myfile_move_uploaded_file($uploaded_file, $path) {
	return move_uploaded_file($uploaded_file, myfile_ENCODE($path));
}
function myfile_filesize($path) {
	return filesize(myfile_ENCODE($path));
}
function myfile_getimagesize($path) {
	return @getimagesize(myfile_ENCODE($path));
}
function myfile_imagecreatefromjpeg($path) {
	return @imagecreatefromjpeg(myfile_ENCODE($path));
}
function myfile_imagecreatefromgif($path) {
	return @imagecreatefromgif(myfile_ENCODE($path));
}
function myfile_imagecreatefrompng($path) {
	return @imagecreatefrompng(myfile_ENCODE($path));
}
function myfile_exif_read_data($path) {
	return exif_read_data(myfile_ENCODE($path));
}
function myfile_imagejpeg($image, $path) {
	return imagejpeg($image, $path);
}
function myfile_ENCODE($path) {
	return mb_convert_encoding($path, _SYSTEM_FILE_ENCODING, 'UTF-8');	// PHPコード → OS(Windows)コード
}
function myfile_DECODE($path) {
	return mb_convert_encoding($path, 'UTF-8', _SYSTEM_FILE_ENCODING);	// PHPコード ← OS(Windows)コード
}
// ****************************************************************
?>
