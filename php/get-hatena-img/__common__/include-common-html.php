<?php
// ****************************************************************
function html_header($title, $add_header_file='', $background_color='', $body_opt='', $add_meta='', $my_header='__html-my-header.php') {
// ****************************************************************
	ini_set("default_charset","UTF-8");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<?php if ($add_meta <> '') echo $add_meta."\n"; ?>
<?php
	require($my_header);
	//	if ($add_header_file <> "") {
	//		$array = file($add_header_file);
	//		$add_header = join("",$array);
	//		echo $add_header;
	//	}
	if ($add_header_file <> '') {
		require($add_header_file);
	}
	if (!defined("PAGE_BODY_STYLE")) {
		define("PAGE_BODY_STYLE", "");
	}
	if (!defined("SIMPLE_MEMO_ICON")) {
		define("SIMPLE_MEMO_ICON", "../icon/pencil.png");
	}
	if (!defined("TIMER_ALARM_ICON")) {
		define("TIMER_ALARM_ICON", "../icon/alarm-clock.png");
	}
	if (!defined("TENKI_YOHOU_ICON")) {
		define("TENKI_YOHOU_ICON", "../icon/weather_sun.png");
	}
?>
<style>
#page_navi a {
	color: <?= $_SESSION['page_navi_link_color'] ?>;
	background-color: <?= $_SESSION['page_navi_link_bg'] ?>;
}
#page_navi_current a {
	color: <?= $_SESSION['page_navi_current_color'] ?>;
	background-color: <?= $_SESSION['page_navi_current_bg'] ?>;
}
#page_navi a:hover {
	color: <?= $_SESSION['page_navi_hover_color'] ?>;
	background-color: <?= $_SESSION['page_navi_hover_bg'] ?>;
}
#page_navi a:active {
	color: <?= $_SESSION['page_navi_active_color'] ?>;
	background-color: <?= $_SESSION['page_navi_active_bg'] ?>;
}
</style>
<link rel="shortcut icon" href="<?= FAVICON_ICON ?>" type="image/ico">
<link rel="icon" href="<?= FAVICON_ICON ?>" type="image/ico">
<script src="../scripts/cookie.js"></script>
<title><?= $title ?></title>
</head>
<?php	if ($background_color <> '') { ?>
<body style="<?= PAGE_BODY_STYLE ?> background-color: <?= $background_color ?>;"<?= $body_opt ?>>
<?php	} elseif ($_SESSION['login_friends_body_background_color_'.$_SESSION['current_id']].'' <> '') { ?>
<body style="<?= PAGE_BODY_STYLE ?> background-color: <?= $_SESSION['login_friends_body_background_color_'.$_SESSION['current_id']] ?>;"<?= $body_opt ?>>
<?php	} else { ?>
<body style="<?= PAGE_BODY_STYLE ?>"<?= $body_opt ?>>
<?php	} ?>
<div id="page_body">
<?php
}
// ****************************************************************
function html_footer() {
// ****************************************************************
?>
</div><!-- id="page_body" -->
</body>
</html>
<?php
}
// ****************************************************************
function page_header($id_check = True) {
// ****************************************************************
	require(_DEFINE_CONTENTS_FILE);		// default: "__define_contents.php"
?>
<div id="page_header">
	<div id="page_navi">
	<ul>
<?php
	$pos = strrpos($_SERVER['SCRIPT_NAME'],'/');
	$url = substr(strtolower($_SERVER['SCRIPT_NAME']),0,$pos);
	$pos = strrpos($url,"/");
	$contents = substr($url,$pos+1).'/';
	$len = strlen($contents);
	$contents_check = False;
	foreach ($navi_item as $id=>$item) {
		if ($contents == strtolower(right($item['href'],$len))) {
			$contents_check = True;
?>
		<li id="page_navi_current"><a href="<?= _MYHOME_REF_PATH ?>/<?= $item['href'] ?>?<?= $item['query'] ?>"><?= $item['name'] ?></a></li>
<?php
		} else {
?>
		<li><a href="<?= _MYHOME_REF_PATH ?>/<?= $item['href'] ?>?<?= $item['query'] ?>"><?= $item['name'] ?></a></li>
<?php
		}
	}
?>
		<?php if (SIMPLE_MEMO_USE <> 'NO') { ?>
		<li id="simple_memo"><a href="../index/memo.php" target="_blank"><img id="simple_memo_img" src="<?= SIMPLE_MEMO_ICON ?>"></a></li>
		<?php } ?>
		<?php if (TIMER_ALARM_USE <> 'NO') { ?>
		<li id="timer_alarm"><a href="../index/alarm.php" target="_blank"><img id="timer_alarm_img" src="<?= TIMER_ALARM_ICON ?>"></a></li>
		<?php } ?>
		<li id="tenki_yohou_icon"><a href="../index/tenki.php" target="_blank"><img id="tenki_yohou_img"  src="<?= TENKI_YOHOU_ICON ?>"></a></li>
	</ul>
	</div>
	<div id="page_login_account">
<script>
function accountMenuChange() {
	if (loadCookie("account_menu_view") == "none") {
		document.getElementById('accountmenu').style.display='inline';
		document.getElementById('arrow_left').style.display='none';
		document.getElementById('arrow_right').style.display='inline';
		saveCookie("account_menu_view", "inline", 365, '<?= MY_SESSION_PATH ?>');
	} else {
		document.getElementById('accountmenu').style.display='none';
		document.getElementById('arrow_left').style.display='inline';
		document.getElementById('arrow_right').style.display='none';
		saveCookie("account_menu_view", "none", 365, '<?= MY_SESSION_PATH ?>');
	}
}
</script>
	<?php
		if ($_COOKIE['account_menu_view'] == 'none') {
			$account_menu = 'none';
			$arrow_right = 'none';
			$arrow_left = 'inline';
		} else {
			$account_menu = 'inline';
			$arrow_right = 'inline';
			$arrow_left = 'none';
		}
	?>
	<span style="display:inline;float:left;"><a href="javascript:accountMenuChange()" style="background-color:transparent;"><img id="arrow_left" style="display:<?= $arrow_left ?>;margin:5px 5px 0 0;" src="../icon/arrow_left.png"><img id="arrow_right" style="display:<?= $arrow_right ?>;margin:5px 5px 0 0;" src="../icon/arrow_right.png"></a></span>
	<table id="accountmenu" style="float:left; display:<?= $account_menu ?>;"><tr>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/guide/?arg=session">利用ガイド</a></td>
<?php
	if ($_SESSION['logincheck'] == "OK") {
?>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/list-user.php">ユーザ一覧</a></td>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/myprofile.php">My設定</a></td>
	<?php if ($_SESSION['システム管理者'] == "YES") { ?>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/logout.php">ログアウト</a></td>
	<?php } else { ?>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/logout.php">ログアウト</a></td>
	<?php } ?>
		<td nowrap id="login_account_handle"><?= $_SESSION['login_handle'] ?></td>
<?php
	} else {
		if (USER_SELF_NEW_ACCOUNT <> 'NO') {
?>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/newaccount.php">ユーザー登録</a></td>
<?php
		}
?>
		<td nowrap><a href="<?= _MYHOME_REF_PATH ?>/account/login.php">ログイン</a></td>
<?php
	}
?>
	</tr></table>
	</div>
</div><!-- id="page_header" -->
<div id="page_contents">
<?php
	if (!$contents_check) {
		if (strpos($_SERVER['SCRIPT_NAME'],'guide') === False and strpos($_SERVER['SCRIPT_NAME'],'account') === False) {
			error_msg ("このコンテンツは利用できません。");		// __define_contents.phpで定義されていない場合
			html_footer();
			exit();
		}
	}
	if ($id_check == True) {
		if ($_SESSION['login_id'] == "") {
			//error_msg("ログインしてください。");
			echo "<div><a href='../account/login.php'>ログイン</a><div>";
			html_footer();
			exit();
		}
	}
}
// ****************************************************************
function page_header_return_index() {
// ****************************************************************
?>
<a href="../tools/" class="return_index">■</a>
<?php
}
// ****************************************************************
function page_footer() {
// ****************************************************************
?>
</div><!-- id="page_contents" -->
<div id="page_footer">
<a href="http://ok2nd.web.fc2.com/" target="_blank" style="color:#8080ff; font-size: 12px;">Powered by ok.2nd</a>
<?php
	if (!defined('TIMER_ALARM_MAXNUM')) {
		define('TIMER_ALARM_MAXNUM', 10);
	}
	if (TIMER_ALARM_USE <> 'NO') {
		$con = my_mysqli_connect(_DB_SCHEMA_index);
		$sql = "select * from m_alarm where id_account = '".$_SESSION['login_id']."'";
		$sql .= " and c_valid = 'on' and c_done <> '".date('Y-m-d')."'";
		$sql .= " order by c_hour, c_min";
		$rs = my_mysqli_query($sql);
		$alarm_set = '';
		$tooltip = '';
		$num = 0;
		while ($rec=mysqli_fetch_array($rs)) {
			if ($rec['c_day'] == 0 and $rec['c_week'] == -1) {
				timer_alarm_str($alarm_set, $tooltip, $num, $rec);
				$num++;
			} elseif ($rec['c_day'] == intval(date('j'))) {
				timer_alarm_str($alarm_set, $tooltip, $num, $rec);
				$num++;
			} elseif ($rec['c_week'] == intval(date('w'))) {
				timer_alarm_str($alarm_set, $tooltip, $num, $rec);
				$num++;
			}
		}
		if ($num > 0) {
			timer_alarm_set($alarm_set);
		}
	}
?>
<?= $tooltip ?>
</div><!-- id="page_footer" -->
<?php
}
function timer_alarm_str(&$alarm_set, &$tooltip, $num, $rec) {
	$msg = str_replace("'","’",$rec['c_message']);
	if ($rec['c_day'] <> 0 and $rec['c_day'] == intval(date('j'))) {
		$day = $rec['c_day'];
	}
	if ($rec['c_week'] <> -1 and $rec['c_week'] == intval(date('w'))) {
		$week = mb_substr('日月火水木金土',$rec['c_week'],1);
	}
	$alarm_set .= "alarm[".$num."] = { no:'".$rec['id_num']."', ";
	$alarm_set .= "hour:'".$rec['c_hour']."', ";
	$alarm_set .= "min:'".$rec['c_min']."', ";
	if ($day) {
		$alarm_set .= "day:'".$day."', ";
	}
	if ($week) {
		$alarm_set .= "week:'".$week."', ";
	}
	$alarm_set .= "msg:'".$msg."', ";
	$alarm_set .= "done:'0' };\n";
	$tooltip_msg = '';
	if ($day) {
		$tooltip_msg .= $day.'日 ';
	}
	if ($week) {
		$tooltip_msg .= $week.'曜日 ';
	}
	$tooltip_time = right('0'.$rec['c_hour'],2).":".right('0'.$rec['c_min'],2);
	$tooltip_msg .= $tooltip_time.' '.$msg;
	$tooltip .= "<span id='timer_alarm_tooltip' class='timer_alarm_tooltip' title='".$tooltip_msg."'>".$tooltip_time."</span>";
	return $alarm_set;
}
function timer_alarm_set($alarm_set) {
	if (!defined("TIMER_ALARM_TIME_INTERVAL")) {
		define("TIMER_ALARM_TIME_INTERVAL", 1000);	// 1秒間隔
	}
	if (!defined("TIMER_ALARM_JALERT_USE")) {
		define("TIMER_ALARM_JALERT_USE", "");		// jAlertを使う場合"YES"
	}
?>
<style>
.timer_alarm_tooltip {
	margin-left: 6px;
	color: #888;
	text-decoration: underline;
}
</style>
<link href="../scripts/tipTip/tipTip.css" rel="stylesheet">
<script src="../scripts/tipTip/jquery.tipTip.js"></script>
<script>
$(function() {
	$(".timer_alarm_tooltip").tipTip({
		maxWidth: "auto",	//ツールチップ最大幅
		defaultPosition: "top", //ツールチップ表示場所
		delay: 0,
		fadeIn: 200,		//フェードインの速度
		adeOut: 200,		//フェードアウトの速度
		edgeOffset: 10		//要素からのオフセット距離
	});
});
</script>
<?php
	if (TIMER_ALARM_JALERT_USE == 'YES') {
?>
<link href="../scripts/jquery.alerts.css" rel="stylesheet">
<script src="../scripts/jquery.ui.draggable.js"></script>
<script src="../scripts/jquery.alerts.js"></script>
<?php
	}
?>
<script>
$(function() {
	timer_alarm();
});
var alarm = new Array();
<?= $alarm_set ?>
function timer_alarm() {
	var today = new Date();
	var hour_min = today.getHours()*60+today.getMinutes();
	for (var i=0; i < alarm.length; i++) {
		if (hour_min >= (parseInt(alarm[i]['hour'])*60+parseInt(alarm[i]['min'])) && alarm[i]['done'] == '0') {
			var msg = '【';
			if (alarm[i]['day']) {
				msg += ''+alarm[i]['day']+'日 ';
			}
			if (alarm[i]['week']) {
				msg += ''+alarm[i]['week']+'曜日 ';
			}
			msg += ''+alarm[i]['hour']+'時'+alarm[i]['min']+'分】　'+alarm[i]['msg'];
<?php	if (TIMER_ALARM_JALERT_USE == 'YES') { ?>
			jAlert(msg, 'タイマーアラート');
<?php	} else { ?>
			alert(msg);
<?php	} ?>
			alarm[i]['done'] = '1';
			$.ajax({
				type: "GET",
				url: "../index/alarm-done.php?no="+alarm[i]['no'],
				async: false
				/*	, success: function(res){
						alert(res);
					}
				*/
			});
		}
	}
	setTimeout('timer_alarm()', <?= TIMER_ALARM_TIME_INTERVAL ?>);
}
</script>
<?php
}
// ****************************************************************
function contents_menu($menu_item) {
// ****************************************************************
?>
<div id="contents_navi">
	<ul>
<?php
	$basename = basename(strtolower($_SERVER['SCRIPT_NAME']));
	foreach ($menu_item as $id=>$item) {
		if ($item['query'].'' <> '') {
			$query = '?'.$item['query'];
		} else {
			$query = '';
		}
		$href = explode('?', $item['href']);
		if ($basename == $href[0]) {
?>
		<li id="contents_navi_current"><a href="<?= $item['href'] ?><?= $query ?>"><?= $item['name'] ?></a></li>
<?php
		} else {
?>
		<li><a href="<?= $item['href'] ?><?= $query ?>"<?php if ($item['target']<>'') echo ' target="'.$item['target'].'"' ?>><?= $item['name'] ?></a></li>
<?php
		}
?>
<?php
	}
?>
	</ul>
</div><!-- id="contents_navi" -->
<?php
}
// ****************************************************************
function change_account_menu() {
// ****************************************************************
	if ($_SESSION['login_friends_number'] == 0) return;
	if ($_SESSION['login_friends_number'] >= _ACCOUNT_CHANGE_USER_NUM) {
?>
	<script>
	function SelectionAccount(form, sel) {
		for (i = 0; i < sel.options.length; i++) {
			if (sel.options[i].selected == true) {
				window.location.href = "<?= _MYHOME_REF_PATH ?>/account/change-account.php?uid=" + escape(sel.options[i].value) + "&ret=<?= $_SERVER['SCRIPT_NAME'] ?>";
			}
		}
	}
	</script>
<div id="contents_change_account_select">
	<input type="checkbox" name="cb_ac" id="cb_ac" value="一括" onClick="CheckboxAccountChgSw()"<?= $_COOKIE['account_chg_all'] == 'all' ? ' checked' : '' ?>><label for="cb_ac">一括</label>&nbsp;&nbsp;
	<select name="Account" onChange="SelectionAccount(this.form, this)">
<?php
		for ($ix=0; $ix<=$_SESSION['login_friends_number']; $ix++) {
			$friends_id = $_SESSION['login_friends_id_'.$ix];
?>
		<option value="<?= $friends_id ?>"<?= $friends_id == $_SESSION['current_id'] ? ' selected' : '' ?>><?= $_SESSION['login_friends_handle_'.$friends_id] ?>
<?php
		}
?>
	</select>
</div><!-- id="contents_change_account_select" -->
<?php
	} else {
?>
<div id="contents_change_account">
	<table><tr><td style="vertical-align: top;">
	<input type="checkbox" name="cb_ac" id="cb_ac" value="一括" style="width: 14px; height: 14px;"
		onClick="CheckboxAccountChgSw()"<?= $_COOKIE['account_chg_all'] == 'all' ? ' checked' : '' ?>>
	</td><td style="vertical-align: top;"><ul><label for="cb_ac">一括</label>&nbsp;&nbsp;
<?php
		for ($ix=0; $ix<=$_SESSION['login_friends_number']; $ix++) {
			$friends_id = $_SESSION['login_friends_id_'.$ix];
			if ($friends_id == $_SESSION['current_id']) {
?><li id="contents_change_account_current"><?= $_SESSION['login_friends_handle_'.$friends_id] ?></li><?php
			} else {
?><li><a href='../account/change-account.php?uid=<?= $friends_id ?>&ret=<?= $_SERVER['SCRIPT_NAME'] ?>'><?= $_SESSION['login_friends_handle_'.$friends_id] ?></a></li><?php
			}
		}
?></ul>
	</td></tr></table>
</div><!-- id="contents_change_account" -->
<?php
	}
?>
<script>
function CheckboxAccountChgSw() {
	if (loadCookie("account_chg_all") == "all") {
		saveCookie("account_chg_all", "each", 365, '<?= MY_SESSION_PATH ?>');
	} else {
		saveCookie("account_chg_all", "all", 365, '<?= MY_SESSION_PATH ?>');
	}
}
</script>
<?php
}
// ****************************************************************
function attach_file_view($user_id, $filename, $add_str="", $img_width=50, $img_view=True, $name_view=True,
		$video_view=False, $video_width=100, $video_height=75, $folder='') {
// ****************************************************************
	if ($filename <> "") {
		if ($folder == '') {
			$folder = ATTACH_FILE_FOLDER;
		}
		if ($user_id <> '') {
			$filepath = $folder.$user_id.'/'.$filename;
		} else {
			$filepath = $folder.$filename;
		}
		if (VIDEO_PREVIEW == 'YES' && $video_view && is_preview_video_filename($filename)) {
			if (photo_VIDEO_FFMPEG == "YES") {
				$video_jpeg = '../photo/video-jpeg.php?file='.urlencode(realpath($filepath));
			} else {
				$video_jpeg = '';
			}
			if (is_ext_filename_str($filename, VIDEO_PREVIEW_WMV)) {
?>
		<div id="<?= $filename ?>">the player will be placed here</div>
		<script>
			wmv_preview_no_auto("<?= $filename ?>", "<?= $filepath ?>", "<?= $video_width ?>", "<?= $video_height ?>");
		</script>
<?php
			} else {
				$flv_view = '../__common__/flv-view.php?file=' . $filepath;	// 日本語名ファイル対策
?>
		<div id="<?= $filename ?>">the player will be placed here</div>
		<script>
			movie_preview_no_auto("<?= $filename ?>", "<?= $flv_view ?>", "<?= $video_width ?>", "<?= $video_height ?>");
		</script>
<?php
			}
		} elseif ($img_view && is_img_filename($filename)) {
			list($width, $height) = getimagesize(myfile_ENCODE(realpath($filepath)));
			if ($width >= $height) {
				$width_height = 'width';
			} else {
				$width_height = 'height';
			}
?>
		<a href="<?= $filepath ?>" target="_blank"><img src="<?= $filepath ?>" class="bicubic" border=0 <?= $width_height ?>=<?= $img_width ?> /></a>
<?php
		} else {
			if ($name_view) {
				$view = '<img src="../images/paste.png" border=0 />'.mb_substr($filename,9);
			} else {
				$view = '<img src="../images/paste.png" border=0 />';
			}
?>
		<a class="std" href="<?= $filepath ?>" download="<?= substr($filename, 9) ?>"><?= $view ?></a>
<?php
		}
		echo $add_str;
	}
?>
<?php
	return(0);
}
// ****************************************************************
function is_preview_video_filename($str) {
// ****************************************************************
	static $video_ext = NULL;
	if ($video_ext == NULL) {
		if (VIDEO_PREVIEW_EXT.'' <> '') {
			$video_ext = explode(',', VIDEO_PREVIEW_EXT);
		}
	}
	if ($video_ext != NULL) {
		return is_ext_filename($str, $video_ext);
	} else {
		return false;
	}
}
// ****************************************************************
function images_print_table($dir_path, $column, $file_table, $file_count) {
// ****************************************************************
?>
<table>
<tr><td>
	<table>
<?php
	if ($file_count > $column) {
		$num_in_coloumn = ceil($file_count / $column);	// 切り上げ整数化
	} else {
		$num_in_coloumn = $file_count;
	}
	$cnt = 0;
	foreach ($file_table as $file) {
		$filePath = $dir_path . "/" . $file;
		$cnt++;
		if ($cnt > $num_in_coloumn) {
?>
	</table>
</td>
<td>
	<table>
<?php
			$cnt = 1;
		}
?>
		<tr><td><img src="<?= $filePath ?>"></td><td nowrap><?= $file ?></td></tr>
<?php
	}
?>
	</table>
</td></tr>
</table>
<?php
}
?>
