<?php	// ★
	error_reporting(E_ERROR & ~E_NOTICE & ~E_PARSE);	// PHP 5.3対応

	$script_name = explode('/', $_SERVER['SCRIPT_NAME'], 3);
	define('MY_SESSION_NAME', $script_name[1]);
	define('MY_SESSION_PATH', '/'.$script_name[1].'/');

	define("_CONTENTS_PATH_1", "index");
	define("_CONTENTS_PATH_2", "calendar");
	define("_CONTENTS_PATH_3", "bbs");
	define("_CONTENTS_PATH_4", "rss");
	define("_CONTENTS_PATH_5", "memo");
	define("_CONTENTS_PATH_6", "photo");
	define("_CONTENTS_PATH_7", "abook");
	define("_CONTENTS_PATH_8", "sticky");
	define("_CONTENTS_PATH_9", "diary");
	define("_CONTENTS_PATH_10", "study");
	define("_CONTENTS_PATH_11", "kakeibo");
	define("_CONTENTS_PATH_12", "email");
	define("_CONTENTS_PATH_13", "gpslog");
	define("_CONTENTS_PATH_14", "id-manager");
	define("_CONTENTS_PATH_CNT", 14);

	// ★ _MY_DEFINE_COMMONで定義したファイルが存在する場合、その定義ファイルの設定が有効になります。
	// ★ サンプルとして、_myhome/__define_common_my_sample.phpを参考にしてください。
	define("_MY_DEFINE_COMMON", "../../_myhome_myset/__define_common_my.php");	// 実運用モードの__define_common.php
	define("_MY_DEFINE_COMMON_SAVE", "../../_myhome_myset/__define_common_my_x.php"); // 運用モード変更時のセーブファイル名

	// ★ index トップページ 検索ボタン定義ファイル 標準
	define("_DEFINE_INDEX_SEARCH", "__define_index_search.php");

if (file_exists(_MY_DEFINE_COMMON)) {
	require(_MY_DEFINE_COMMON);
} else {
	define("_DB_SERVER", "localhost");

	define("_DB_USERNAME", "myhome");			// MySQL ユーザー名	★ 設定必須
	define("_DB_PASSWORD", "pass123");			// MySQL パスワード	★ 設定必須

	define("_DB_ANGOUKA_KEY", "iYUwNZsidihmhdYdsisihhYmihYmsihiNZYUihdsihYmwNZsiYmhdsiYmUwNZmmsssihdYmssihhi");
	// ↑ ID管理のパスワードの暗号化用文字列。
	// ↑ date関数のformatパラメータ文字列として使用可能な文字をランダムに指定。
	// ↑ ★ 運用開始前に必ず修正すること。
	// ↑ ★ 運用開始後は絶対に修正してはいけません。修正するとパスワードが復元できなくなります。

//	define("PAGE_BODY_STYLE", "max-width: 980px;");		// ★ ページbodyのstyle(横幅)設定
	define("PAGE_BODY_STYLE", "max-width: 100%;");		// ★ ページbodyのstyle(横幅)設定

	define("LOGIN_COOKIE_EXPIRE", (3600 * 24 * 365));	// ログイン setcookie 保存時間
	define("IDMGR_COOKIE_EXPIRE", (3600 * 5));		// IDマネージャ setcookie 保存時間

	define("_STYLE_SHEET_FOLDER", "../style/original");		// CSSスタイルシート ディレクトリ
	define("_STYLE_SHEET_FOLDER_COMMON", "../style/original");	// CSSスタイルシート(common.css) ディレクトリ
	define("_CALENDAR_SELECT_FIRST_YEAR", 1980);		// カレンダー日付プルダウン表示開始年
	define("_DEBUG_ERROR_MSG", "YES");			// "YES":エラー詳細情報出力

	define("_ACCOUNT_CHANGE_USER_NUM", 5);			// カレントユーザ切り替えメニューをSELECTにするユーザ数

	// データベース指定
	define("_DB_ACCOUNT_SCHEMA", "_db_account");
	define("_DB_SCHEMA_account", "_db_account");
	define("_DB_SCHEMA_calendar", "_db_calendar");
	define("_DB_SCHEMA_index", "_db_index");
	define("_DB_SCHEMA_memo", "_db_memo");
	define("_DB_SCHEMA_zid_mgr_a", "_db_zid_mgr_a");
	define("_DB_SCHEMA_zid_mgr_b", "_db_zid_mgr_b");
	define("_DB_SCHEMA_bbs", "_db_bbs");
	define("_DB_SCHEMA_rss", "_db_rss");
	define("_DB_SCHEMA_chat", "_db_chat");
	define("_DB_SCHEMA_kakeibo", "_db_kakeibo");
	define("_DB_SCHEMA_study", "_db_study");
	define("_DB_SCHEMA_abook", "_db_abook");
	define("_DB_SCHEMA_diary", "_db_diary");
	define("_DB_SCHEMA_sticky", "_db_sticky");
	define("_DB_SCHEMA_photo", "_db_photo");
	define("_DB_SCHEMA_svg", "_db_svg");
	define("_DB_SCHEMA_draw", "_db_draw");
	define("_DB_SCHEMA_guide", "_db_guide");
	define("_DB_SCHEMA_psketch", "_db_psketch");
	define("_DB_SCHEMA_email", "_db_email");
	define("_DB_SCHEMA_gpslog", "_db_gpslog");

	// 電子メール
	define("_SENDMAIL_HOST", "localhost");			// smtp サーバ (php.ini)
	define("_SENDMAIL_PORT", 25);				// smtp ポート (25、587など)
	define("_SENDMAIL_EMAIL_NAME", "MyHome Portal");	// Emailに使う送信者名(日本語不可)
	define("_SENDMAIL_EMAIL_ADDR", "xxx@xxx.com");		// 送信者Emailアドレス
	define("_SENDMAIL_EMAIL_USER", "");			// 送信者Emailアカウント(smtp認証を使う場合)
	define("_SENDMAIL_EMAIL_PASS", "");			// 送信者Emailアカウントパスワード(smtp認証を使う場合)
	define("_SENDMAIL_AUTH_USE", False);			// smtp認証を使う場合: True
	define("_FORGOT_PASS_USE", False);			// パスワード忘れのメール送信機能を使う場合: True
	define("_SCHEDULE_SENDMAIL_USE", False);		// スケジュールメール送信機能を使う場合: True
	define("MAILTO_CONCATENATE_CHR", ",");			// mailto:で複数メールアドレスの結合文字
								// Thunderbird："," Outlook用：";"
	// 添付ファイルフォルダ
	define("ATTACH_FILE_FOLDER_account", "../_attach/account/");
	define("ATTACH_FILE_FOLDER_memo", "../_attach/memo/");
	define("ATTACH_FILE_FOLDER_calendar", "../_attach/calendar/");
	define("ATTACH_FILE_FOLDER_bbs", "../_attach/bbs/");
	define("ATTACH_FILE_FOLDER_diary", "../_attach/diary/");
	define("ATTACH_FILE_FOLDER_diary_marker", "../_attach/diary_marker/");
	define("ATTACH_FILE_FOLDER_guide", "../_attach/guide/");

	// イメージファイルフォルダ
	define("IMAGES_FOLDER_calendar", "images");			//カレンダーアイコン

	// カレンダースケジュール本文にhtmlタグを許可する場合"YES"
	//  ※注：<scriptだけは機能しないようにしていますが、それ以外は全て機能してしまいます。
	//	この機能はセキュリティ上、問題があります。
	//	家族など信頼できるユーザーだけで利用する以外は、
	//	define("TEXTAREA_HTML_USE", "NO")にして、このタグ機能を無効にすることをお勧めします。
	define("TEXTAREA_HTML_USE_calendar", "YES");

	// ★ index トップページ 検索フォーム優先サイト NAME属性 (Google)	Ver.4.09で廃止(不要)。
	// define("SEARCH_FORM_INPUT_TEXT_NAME_index", "q");	// (Googleの場合)

	// index livedoor天気ガジェット：表示有無
	define("GADGET_WEATHER_USE_index", "");				// 表示する場合:　"YES"
	// index 天気ガジェット：都市プルダウン定義ファイル
	define("WEATHER_CITY_DEFINE_index", "__define_weather_city.php");
	// index 天気ガジェット：リンク天気予報サイト
	define("LINK_WEATHER_SITE_index", "http://weathernews.jp/");

	// index チャット(Chat)表示
	define("CHAT_VIEW_FRAME_COLOR_index", "#87cefa");	// チャット(Chat)表示 枠カラー
	define("CHAT_READ_CHECK_INTERVAL_index", 2000);		// チャットAjax読取間隔 (ミリセカンド)
	define("CHAT_LOG_VIEW_CNT_MIN_index", 3);		// INDEXトップページ チャット表示 件数		(chat/read-min.php)
	define("CHAT_LOG_VIEW_INTERVAL_MIN_index", 30);		// INDEXトップページ チャット表示 経過時間 (分)	(chat/read-min.php)

	// index 掲示板スレッド表示
	define("BBS_VIEW_FRAME_COLOR_index", "#ff69b4");	// 掲示板スレッド表示 枠カラー
	define("BBS_VIEW_THREAD_index", 3);			// 掲示板スレッド表示 件数
	define("BBS_VIEW_INTERVAL_DAY_index", 7);		// 掲示板スレッド表示 表示日数 (n日以内の投稿があれば表示)

	// index ToDo
	define("TODO_VIEW_FRAME_COLOR_index", "#ff8c00");	// ToDo表示 枠カラー
	define("TODO_VIEW_CNT_MIN_index", 3);			// INDEXトップページ ToDo表示 件数	(todo/read-min.php)

	// calendar ToDo
	define("TODO_VIEW_FRAME_COLOR_calendar", "#ff8c00");	// カレンダーToDo表示 枠カラー
	define("TODO_VIEW_USE_calendar", "YES");		// カレンダーToDo表示 する場合:　"YES"

	// index スケジュール/カレンダー
	define("SCHEDULE_VIEW_FRAME_COLOR_index", "#228b22");	// スケジュール表示 枠カラー
	define("SCHEDULE_VIEW_DAY_index", 3);			// スケジュール表示 日数
	define("CALENDAR_VIEW_MONTH_index", 3);			// カレンダー表示 月数
	define("CALENDAR_VIEW_FIRST_index", -1);		// カレンダー表示 先頭月(-1:前月,0:今月)

	// index ブログパーツ：ディレクトリ
	define("BLOG_PARTS_FOLDER_index", "../blog-parts/");
	// index ブログパーツ：HTMLファイル			// HTMLファイルはUTF-8でお願いします。

	define("BLOG_PARTS_SCRIPT_TOP1_index", "");				// 右カレンダーの上
	define("BLOG_PARTS_SCRIPT_TOP2_index", "");				// 右カレンダーの上
	define("BLOG_PARTS_SCRIPT_BOTTOM1_index", "himekuri.inc");		// 右カレンダーの下
	define("BLOG_PARTS_SCRIPT_BOTTOM2_index", "");				// 右カレンダーの下

	//define("BLOG_PARTS_SCRIPT_RIGHT1_index", "clock-simple-blue.inc");	// 右カレンダーの右
	define("BLOG_PARTS_SCRIPT_RIGHT1_index", "myhome-clock.inc");		// 右カレンダーの右
	define("BLOG_PARTS_SCRIPT_RIGHT2_index", "weather-yahoo.inc");		// 右カレンダーの右
	define("BLOG_PARTS_SCRIPT_RIGHT3_index", "fx-news.inc");		// 右カレンダーの右
	define("BLOG_PARTS_SCRIPT_RIGHT4_index", "");				// 右カレンダーの右

	// proxy
	define("HTTP_PROXY_HOST", "");				// HTTP プロキシサーバ アドレス
	define("HTTP_PROXY_PORT", "");				// HTTP プロキシサーバ ポート

	// MagpieRSS
	define("MAGPIE_OUTPUT_ENCODING", "UTF-8");
	define("MAGPIE_CACHE_DIR", "../../_rss_cache");		// MagpieRSS キャッシュ ディレクトリパス
								// /htdocs/_rss_cache
	define("MAGPIE_CACHE_AGE", 5*60);			// MagpieRSS キャッシュ 秒数
	define("MAGPIE_PROXY_HOST", HTTP_PROXY_HOST);		// MagpieRSS プロキシサーバ アドレス
	define("MAGPIE_PROXY_PORT", HTTP_PROXY_PORT);		// MagpieRSS プロキシサーバ ポート

	// rss
	define("RSS_ARTICLE_VIEW_CNT_rss", 5);			// RSS 記事表示件数
	define("RSS_ARTICLE_TABLE_WIDTH_rss", 192);		// RSS 記事表示テーブル幅(px)
	define("RSS_ARTICLE_TABLE_WIDTH_POPUP_rss", 260);	// RSS ポップアップ記事表示テーブル幅(px)

	// photo
	define("photo_DEFAULT_IMAGES_FOLDER", "D:/Temp");
	define("photo_SMALL_SIZE", 120);
	define("photo_MID_SIZE", 240);
	define("photo_BIG_SIZE", 360);
	define("photo_MAX_SIZE", 720);
	define("photo_UPDOWN_SIZE", 120);
	define("photo_LBOX_DIR_SIZE", 60);
	define("photo_LIST_DIR_SIZE", 60);
	define("photo_TILE_DIR_SIZE", 120);
	define("photo_DIR_POP_SIZE", 360);

//	define("photo_LIMITED_IMAGES_FOLDER", "D:/XAMPP/htdocs/".MY_SESSION_NAME."/z_photo_sample");	// アルバムの表示ディレクトリ制限
		// ↑これを指定すると、このディレクトリ以下以外には移動できない。
		// ↑パスのディレクトリ区切り文字は、\でなく必ず/を使ってください。
		// ↑これを指定するとカレンダー連携のアルバムフォルダは、このディレクトリからの相対アドレスとなります。
//	define("photo_LIMITED_ADMIN_NOLIMIT", "YES");			// システム管理者のみ制限をはずす場合は、"YES"
		// ↑カレンダー連携のアルバムフォルダの相対アドレス化はそのまま。

	define("photo_IMAGE_MAX_SELECT", "5,10,20,50,100,200,1000");	//頁内に表示する画像数
	define("photo_IMAGE_MAX_DEFAULT", "10");			//頁内に表示する画像数（デフォルト）

	define("photo_SLIDE_MAIN_SIZE", 500);
	define("photo_SLIDE_SMALL_SIZE", 56);
	define("photo_SLIDE_SMALL_NUM", 10);
	define("photo_SLIDE_MAIN_BG_COLOR", "#202020");
	define("photo_SLIDE_SMALL_BG_COLOR", "#808080");

	define("photo_SLIDE_INTERVAL_TIME", 3);
	define("photo_SLIDE_MAIN_SIZE_SELECT", "100,200,300,400,500,600,800,1000,1200,1400,1600");

	define("photo_VIDEO_TIMELINE_THUMB_NUM", 5);		// 動画時間分割サムネイル表示個数
	define("photo_VIDEO_TIMELINE_THUMB_NUM_SELECT", "3,4,5,6,8,9,10,12,15,16,18,20,24,25,30,40,50,100");
	define("photo_VIDEO_TIMELINE_THUMB_SIZE", 160);		// 動画時間分割サムネイル表示サイズ
	define("photo_VIDEO_TIMELINE_THUMB_SIZE_SELECT", "80,120,160,200,240,320,400,480,640,800");

	define("photo_VIDEO_FFMPEG", "");	// 動画をサムネイル表示する場合:"YES"　★ffmpegがインストールされている事
	define("IS_VIDEO_EXT", "avi,flv,mpg,mov,swf,wmv,m2ts,mts,mp4");	// ↓同じ順序で対応するMIMEを定義
	define("IS_VIDEO_MIME","video/x-msvideo,video/x-flv,video/mpeg,video/quicktime,application/x-shockwave-flash,video/x-ms-wmv,video/avc,video/avc,video/mp4");
	define("IS_IMG_EXT", "jpeg,jpg,gif,png");

	define("photo_JIGSAW_PUZZLE_CREATE", "YES");		//ジグソーパズル生成機能を使う場合: "YES"

	define("photo_VIDEO_PREVIEW", "YES");			//FLV動画再生する場合: "YES"
	define("photo_VIDEO_PREVIEW_EXT", "flv,mp4,mp3,wmv");

	// account
	define("LOGINLOG_MAXREC_account", 100);			//list-loginlog.phpレコード表示件数
	define("EDIT_PUBLIC_PAGE_account", "");			//公開先メンバ修正ページ:""=全ユーザー一覧から選択式、"IDINP"=ID入力方式
	define("EDIT_FRIENDS_PAGE_account", "");		//My参照メンバ修正ページ:""=全ユーザー一覧から選択式、"IDINP"=ID入力方式
	define("NEWACCOUNT_CAPTCHASTR_USE_account", "YES");	//"YES":アカウント登録で、CAPTCHA(認証用絵文字コード)使用
	define("USER_SELF_NEW_ACCOUNT", "");			//ユーザー自身によるユーザー登録(newaccount.php)を使わない場合:"NO"

	// calendar スケジュール入力画面カラータグ挿入ボタン定義ファイル
	define("INPUT_COLOR_TAG_caledar", "__define_color_tag.php");

	// calendar スケジュール入力画面の日付入力補助ポップアップカレンダーの種類
	// デフォルトは、ホームページの素(http://www.kanaya440.com/)の日付入力補助
	define("INPUT_POPUP_CALENDAR_calendar", "");		// Yahoo! UI Library: Calendar を使う場合: "YUI"

	// calendar
	define("NO_SUBJECT_INPUT_MARK_calendar", "☆");		// 件名がない場合のスケジュール修正画面リンクマーク

	// bbs
	define("WYSIWYG_EDITOR_bbs", "openwysiwyg");
	//define("WYSIWYG_EDITOR_bbs", "YUI");

	define("VIEW_PHOTO_WIDTH_bbs", 400);
	define("VIDEO_PREVIEW_bbs", "YES");
	define("VIDEO_PREVIEW_EXT_bbs", "flv,mp4,mp3,wmv");
	define("VIDEO_PREVIEW_WIDTH_bbs", "400");
	define("VIDEO_PREVIEW_HEIGHT_bbs", "300");

	define("INPUT_DRAW_USE_bbs", "YES");			// 掲示板でお絵かきをする場合: "YES"
	define("INPUT_DRAW_WIDTH_bbs", "400");
	define("INPUT_DRAW_HEIGHT_bbs", "300");
	define("INPUT_DRAW_STROKE_STYLE_bbs", "3 1 rgb(32,32,32)");

	// memo
	define("VIEW_PHOTO_WIDTH_memo", 200);
	define("VIDEO_PREVIEW_memo", "YES");
	define("VIDEO_PREVIEW_EXT_memo", "flv,mp4,mp3,wmv");
	define("VIDEO_PREVIEW_WIDTH_memo", "240");
	define("VIDEO_PREVIEW_HEIGHT_memo", "180");

	// chat
	define("CHAT_READ_CHECK_INTERVAL_chat", 1000);			// チャットAjax読取間隔 (ミリセカンド)
	define("CHAT_LOG_VIEW_CNT_SELECT_chat", "5,10,20,50,100");	// チャット表示件数 選択肢
	define("CHAT_LOG_VIEW_CNT_SELECT_INIT_chat", "10");		// ↑ 初期値
	define("CHAT_LOG_VIEW_INTERVAL_SELECT_chat", "1:1時間,2:2時間,6:6時間,12:12時間,24:1日,168:1週間,720:30日,0:制限なし");
									// チャット表示経過時間 選択肢
	define("CHAT_LOG_VIEW_INTERVAL_SELECT_INIT_chat", "1");		// ↑ 初期値

	// id-manager
	define("POP_WIN1_LEFT_id_manager", 10);
	define("POP_WIN1_TOP_id_manager", 10);
	define("POP_WIN1_WIDTH_id_manager", 500);
	define("POP_WIN1_HEIGHT_id_manager", 130);
	define("POP_WIN2_LEFT_id_manager", 100);
	define("POP_WIN2_TOP_id_manager", 160);
	define("POP_WIN2_WIDTH_id_manager", 900);
	define("POP_WIN2_HEIGHT_id_manager", 560);

	// Google Maps API バージョン
	define("GOOGLE_MAPS_API_VERSION", "V3");

	// tools
//	define("CODE_FILE_VIEW_EDIT_DEFAULT_FOLDER_tools", "D:/xampp/htdocs");	// デフォルトは$_SERVER['DOCUMENT_ROOT']を使用
	define("CODE_FILE_VIEW_EDIT_TEXTAREA_HEIGHT_tools", "520px");

	define("ATTACH_FILE_FOLDER_excel_graph", "../_attach/excel-graph/");
	define("GRAPH_WIDTH_excel_graph", 350);

	// tools	HTML URL抽出&ファイルダウンロード対象ファイル
	define("GET_URL_FILE_TYPE_SELECTS_tools", "jpeg,jpg,gif,png,wmv,flv,avi,mpg,mp4,mov,swf,zip,lzh");

	define("_NEWACOUNT_KIYAKU_account", "ハンドル名とコメントは公開されます。
本システム利用による何らかの不利益に一切の責任を負いません。");
}
if (defined("_MY_DEFINE_CONTENTS_FILE") && file_exists(_MY_DEFINE_CONTENTS_FILE)) {		// コンテンツの定義ファイル
	define("_DEFINE_CONTENTS_FILE", _MY_DEFINE_CONTENTS_FILE);
} else {
	define("_DEFINE_CONTENTS_FILE", "__define_contents.php");
}
if (!defined("_MYHOME_REF_PATH")) {		// 実運用_myhome外部コンテンツ追加時の_myhome参照用
	define("_MYHOME_REF_PATH", "..");	// include-common-html.php, __logincheck.php用
}
if (!defined("_SYSTEM_FILE_ENCODING")) {	// OSのファイルシステムの文字エンコーディング：Windows="SJIS-win"
	define("_SYSTEM_FILE_ENCODING", "SJIS-win");
}
if (!defined("VIDEO_PREVIEW_WMV")) {
	define("VIDEO_PREVIEW_WMV", "wmv");
}
if (!defined("VIDEO_PREVIEW_CONVERT")) {
	define("VIDEO_PREVIEW_CONVERT", "mpg,mov,m2ts,mts");
}
if (!defined("VIDEO_PREVIEW_CONVERT_FLV_DIR")) {
	define("VIDEO_PREVIEW_CONVERT_FLV_DIR", "/___flv___/");
}
if (!defined("FFMPEG_CONVERT_OPTION_MPG")) {
	define("FFMPEG_CONVERT_OPTION_MPG", "-vcodec flv");
}
if (!defined("FFMPEG_CONVERT_OPTION_MOV")) {
	define("FFMPEG_CONVERT_OPTION_MOV", "-f flv -vcodec flv -r 25 -b 900 -s qvga -acodec libmp3lame -ar 44100 -ab 64k");
}
if (!defined("FFMPEG_CONVERT_OPTION_M2TS")) {
	//define("FFMPEG_CONVERT_OPTION_M2TS", "-ab 128 -r 29.97 -g 10 -ac 2 -ar 44100 -b 700k -s 720x480");
	define("FFMPEG_CONVERT_OPTION_M2TS", "-ar 44100 -b 700k -deinterlace");
}
if (!defined("CALENDAR_TODAY_MINI_BACKGROUND_COLOR")) {
	define("CALENDAR_TODAY_MINI_BACKGROUND_COLOR", "#00ced1");	// ミニカレンダー今日 背景色
}
if (!defined("CALENDAR_TODAY_MINI_LINK_COLOR")) {
	define("CALENDAR_TODAY_MINI_LINK_COLOR", "#ffffff");		// ミニカレンダー今日 文字色
}
if (!defined("FAVICON_ICON")) {
	define("FAVICON_ICON", "../images/favicon/house_red.ico");	// favicon アイコンファイル
}
if (!defined("PAGE_NAVI_LINK_COLOR")) {
	define("PAGE_NAVI_LINK_COLOR", "white");		// トップ・メニュー(コンテンツ・ナビ) デフォルト・カラー
	define("PAGE_NAVI_LINK_BG", "#404020");
	define("PAGE_NAVI_CURRENT_COLOR", "yellow");
	define("PAGE_NAVI_CURRENT_BG", "#9090ff");
	define("PAGE_NAVI_HOVER_COLOR", "white");
	define("PAGE_NAVI_HOVER_BG", "orange");
	define("PAGE_NAVI_ACTIVE_COLOR", "white");
	define("PAGE_NAVI_ACTIVE_BG", "gold");
}
if (!defined("EDIT_MYPROFILE_LOGIN_PASS_EXPIRE")) {
	define("EDIT_MYPROFILE_LOGIN_PASS_EXPIRE", (60 * 3));	// アカウント情報修正 パスワード保存時間 (3分)
}
if (!defined("MP_LIST_SELECT_COLLATE")) {
	define("MP_LIST_SELECT_COLLATE", 'collate utf8_unicode_ci');	// 一覧検索で、全角/半角、カタカナ/ひらがなを同一視する
}
if (!defined("PROFILE_ICON_IMAGE_SIZE")) {
	define("PROFILE_ICON_IMAGE_SIZE", 48);			// 個人アカウント アイコン画像サイズ
}
if (!defined("MB_CONVERT_ENCODING_AUTO")) {
	define("MB_CONVERT_ENCODING_AUTO", "ASCII,JIS,UTF-8,EUC-JP,SJIS-win,SJIS");	// mb_convert_encoding()
}
if (!defined("PASSWORD_COOKIE_HASH_ALGO")) {
	define("PASSWORD_COOKIE_HASH_ALGO", "sha256");		// パスワードcookie保存時の暗号化hash()アルゴリズム
}
if (!defined("CALENDAR_CATEGORYCOLOR_BACKGROUND")) {
	define("CALENDAR_CATEGORYCOLOR_BACKGROUND", "");	// カテゴリ設定色をスケジュールの背景に使用しない場合:"NO"
}
if (!defined("CALENDAR_SCHEDULE_PREFIX")) {
	define("CALENDAR_SCHEDULE_PREFIX", "◆");		// カレンダースケジュールの先頭に付ける文字
}
if (!defined("CALENDAR_SCHEDULE_BORDER_STYLE")) {		// 個々のスケジュールの枠のスタイル
	define("CALENDAR_SCHEDULE_BORDER_STYLE", "border-top:1px dotted #b0b0b0;margin-top:-1px;");
}
if (!defined("CALENDAR_SCHEDULE_TODAY_STYLE")) {		// 月間カレンダーの今日の枠のスタイル
	define("CALENDAR_SCHEDULE_TODAY_STYLE", "background-color:#d0fff0;border:#0080e0 2px solid;");
}
if (!defined("_STYLE_SHEET_BUTTON")) {
	define("_STYLE_SHEET_BUTTON", "../style/button/kube.css?20120922");	// CSSボタン・スタイルシート
}
if (!defined('FONT_ICON_STYLE_SHEET_FOLDER')) {
	define("FONT_ICON_STYLE_SHEET_FOLDER", '../style/font-awesome');
}
if (!defined('FONT_ICON_CLASS_INCLUDE')) {
	define('FONT_ICON_CLASS_INCLUDE', '../__common__/__define_font-icon.php');
}
?>
