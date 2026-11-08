<?php
// 気象庁「潮位表」テキストデータの中継。ブラウザから直接取得できない場合（CORS）に使います。1日キャッシュ。
// 使い方: index.html と同じ場所に置くだけ（例: proxy.php?jma=AK&yr=2026）
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain; charset=utf-8');
$c = $_GET['jma'] ?? '';
$y = $_GET['yr'] ?? '';
if (!preg_match('/^[A-Za-z0-9]{2}$/', $c) || !preg_match('/^20\d\d$/', $y)) { http_response_code(400); exit('bad request'); }
$f = sys_get_temp_dir() . "/jma_{$c}_{$y}.txt";
if (is_file($f) && time() - filemtime($f) < 86400) { readfile($f); exit; }
$r = @file_get_contents("https://www.data.jma.go.jp/kaiyou/data/db/tide/suisan/txt/$y/$c.txt", false,
  stream_context_create(['http' => ['timeout' => 20]]));
if ($r === false || strlen($r) < 1000) { http_response_code(502); exit('upstream error'); }
file_put_contents($f, $r);
echo $r;
