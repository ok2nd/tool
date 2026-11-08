<?php
// tide736 API の中継（ブラウザからの直接取得がCORSで拒否される場合に使用）。結果は1日キャッシュ。
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
$k = [];
foreach (['pc','hc','yr','mn','dy'] as $p) {
  if (!isset($_GET[$p]) || !ctype_digit($_GET[$p])) { http_response_code(400); echo '{"status":0}'; exit; }
  $k[$p] = $_GET[$p];
}
$k['rg'] = 'day';
$q = http_build_query($k);
$f = sys_get_temp_dir() . '/t736_' . md5($q) . '.json';
if (is_file($f) && time() - filemtime($f) < 86400) { readfile($f); exit; }
$r = @file_get_contents('https://api.tide736.net/get_tide.php?' . $q, false, stream_context_create(['http' => ['timeout' => 15]]));
if ($r === false) { http_response_code(502); echo '{"status":0}'; exit; }
file_put_contents($f, $r);
echo $r;
