/* tts-reader.js
   自動生成: 読み上げ機能用の共通スクリプト。全HTMLから共通で読み込まれます。 */
(function () {
  // コントロールUI(再生/一時停止/停止/速度/音声選択)をJSで動的生成する。
  // HTML側には何も埋め込まず、このスクリプトが読み込まれたページに
  // 自動的にバーを追加する。
  function buildControls() {
    var div = document.createElement('div');
    div.id = 'tts-controls';
    div.innerHTML =
      '<button id="tts-play" title="再生">&#9654; 再生</button>' +
      '<button id="tts-pause" title="一時停止" disabled>&#10074;&#10074; 一時停止</button>' +
      '<button id="tts-stop" title="停止" disabled>&#9632; 停止</button>' +
      '<label>速度' +
      '<input type="range" id="tts-rate" min="0.5" max="2" step="0.1" value="1">' +
      '<span id="tts-rate-label">1.0x</span>' +
      '</label>' +
      '<label>音声' +
      '<select id="tts-voice"></select>' +
      '</label>';
    document.body.insertBefore(div, document.body.firstChild);
    return div;
  }

  var sentences = Array.prototype.slice.call(document.querySelectorAll('.tts-sentence'));
  if (sentences.length === 0) return; // 読み上げ対象が無いページでは何もしない

  var controlsEl = buildControls();

  var idx = 0;
  var isPlaying = false;
  var rate = 1.0;
  var selectedVoice = null;
  var voices = [];
  // 再生セッションID。cancel() 後に遅れて届く古い onend/onerror を
  // 無視するために使う（これが無いと、クリックで飛んだ直後に
  // キャンセルされたはずの前の発話の onend が発火し、
  // ハイライトが1文余分に先へ進んでしまう）。
  var playSession = 0;

  var btnPlay = document.getElementById('tts-play');
  var btnPause = document.getElementById('tts-pause');
  var btnStop = document.getElementById('tts-stop');
  var rateInput = document.getElementById('tts-rate');
  var rateLabel = document.getElementById('tts-rate-label');
  var voiceSelect = document.getElementById('tts-voice');

  // このバー自体の実際の高さ(折り返しで2段になる場合など可変)を
  // CSS変数 --tts-bar-h に反映する。#content-wrapper 側のCSSがこの値を
  // 使って本文エリアの高さ・上端位置を調整し、バーが本文を隠さないようにする。
  function updateBarHeightVar() {
    if (!controlsEl) return;
    var h = controlsEl.offsetHeight;
    if (h > 0) {
      document.documentElement.style.setProperty('--tts-bar-h', h + 'px');
    }
  }
  updateBarHeightVar();

  var barResizeTimer = null;
  window.addEventListener('resize', function () {
    if (barResizeTimer) clearTimeout(barResizeTimer);
    barResizeTimer = setTimeout(function () {
      updateBarHeightVar();
      if (window.__ttsReaderAPI && typeof window.__ttsReaderAPI.recalculate === 'function') {
        window.__ttsReaderAPI.recalculate();
      }
    }, 150);
  });

  function updateButtons() {
    btnPlay.disabled = isPlaying;
    btnPause.disabled = !isPlaying;
    btnStop.disabled = !isPlaying && idx === 0;
  }

  // scrollIntoView() を呼んだ直後は、フォントの非同期読み込みなどで
  // レイアウトがまだ確定しておらず、ブラウザが「既に画面内にある」と
  // 誤判定してスクロールをスキップすることがある（クリックで飛んだ
  // 場所によってスクロールする/しないが切り替わって見える一因）。
  // そこで、スクロール後に requestAnimationFrame を2回挟んで実際に
  // 画面内に入ったか検証し、入っていなければもう一度スクロールし直す
  // 自己修復的な仕組みにする。
  function isElementVisible(el, margin) {
    var rect = el.getBoundingClientRect();
    var vw = window.innerWidth;
    var vh = window.innerHeight;
    return (
      rect.top >= -margin &&
      rect.left >= -margin &&
      rect.bottom <= vh + margin &&
      rect.right <= vw + margin &&
      rect.width > 0 &&
      rect.height > 0
    );
  }

  function doScrollIntoView(el) {
    el.scrollIntoView({ behavior: 'auto', block: 'center', inline: 'center' });
  }

  // このページが独自の「ページめくり」機構（transformで#contentを
  // 動かすタイプ等）を持っている場合、common.js 側にTTS連携用の
  // window.__ttsReaderAPI.centerElement() が注入されていればそれを使う。
  // これなら「ページ番号表示」「前/次ボタンの有効・無効状態」など
  // サイト本来の状態管理と矛盾なく中央寄せできる。
  // 無ければ通常のブラウザスクロール（scrollIntoView）にフォールバックする。
  function scrollToElement(el) {
    if (window.__ttsReaderAPI && typeof window.__ttsReaderAPI.centerElement === 'function') {
      try {
        window.__ttsReaderAPI.centerElement(el, true);
        return;
      } catch (e) {
        // フォールバックへ続行
      }
    }
    scrollToElementFallback(el);
  }

  function scrollToElementFallback(el) {
    // 呼び出し前に強制的にレイアウトを確定させる
    void document.body.offsetHeight;
    doScrollIntoView(el);

    // レイアウトが遅れて確定するケースに備え、2フレーム後に再検証する
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        if (!isElementVisible(el, 4)) {
          doScrollIntoView(el);
          // それでもダメな場合、もう一段階だけ再試行する
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              if (!isElementVisible(el, 4)) {
                doScrollIntoView(el);
              }
            });
          });
        }
      });
    });
  }

  function clearHighlight() {
    for (var i = 0; i < sentences.length; i++) sentences[i].classList.remove('tts-active');
  }

  function loadVoices() {
    var all = window.speechSynthesis.getVoices() || [];
    voices = all.filter(function (v) { return v.lang && v.lang.toLowerCase().indexOf('ja') === 0; });
    if (voices.length === 0) voices = all; // 日本語音声が無ければ全部表示
    voiceSelect.innerHTML = '';
    voices.forEach(function (v, i) {
      var opt = document.createElement('option');
      opt.value = i;
      opt.textContent = v.name + ' (' + v.lang + ')';
      voiceSelect.appendChild(opt);
    });
    if (voices.length > 0) selectedVoice = voices[0];
  }

  if (typeof window.speechSynthesis !== 'undefined') {
    loadVoices();
    window.speechSynthesis.onvoiceschanged = loadVoices;
  }

  voiceSelect.addEventListener('change', function () {
    selectedVoice = voices[parseInt(voiceSelect.value, 10)];
  });

  rateInput.addEventListener('input', function () {
    rate = parseFloat(rateInput.value);
    rateLabel.textContent = rate.toFixed(1) + 'x';
  });

  // session: この呼び出しが発行された時点の playSession。
  // 発話の onend/onerror が実際に発火した時点で playSession が
  // 変わっていれば（＝途中でキャンセル/新規セッション開始済み）、
  // 古いイベントとして無視する。
  function speakFrom(i, session) {
    if (session !== playSession) return; // 既に無効化されたセッション
    if (i >= sentences.length) {
      isPlaying = false;
      idx = 0;
      clearHighlight();
      updateButtons();
      return;
    }
    idx = i;
    clearHighlight();
    var el = sentences[idx];
    el.classList.add('tts-active');
    scrollToElement(el);

    var utter = new SpeechSynthesisUtterance(el.textContent);
    utter.lang = (selectedVoice && selectedVoice.lang) || 'ja-JP';
    utter.rate = rate;
    if (selectedVoice) utter.voice = selectedVoice;
    utter.onend = function () {
      if (session !== playSession) return;
      if (isPlaying) speakFrom(idx + 1, session);
    };
    utter.onerror = function () {
      if (session !== playSession) return;
      if (isPlaying) speakFrom(idx + 1, session);
    };
    window.speechSynthesis.speak(utter);
  }

  // 新しい再生セッションを開始する（Play押下 / クリックでの位置指定 共通処理）
  function startPlaybackFrom(i) {
    playSession++; // 古いセッションのonend/onerrorを無効化
    var mySession = playSession;
    isPlaying = true;
    window.speechSynthesis.cancel();
    speakFrom(i, mySession);
    updateButtons();
  }

  btnPlay.addEventListener('click', function () {
    if (isPlaying) return;
    startPlaybackFrom(idx);
  });

  btnPause.addEventListener('click', function () {
    playSession++; // 以降届く古いonend/onerrorを無効化
    isPlaying = false;
    window.speechSynthesis.cancel();
    updateButtons();
  });

  btnStop.addEventListener('click', function () {
    playSession++;
    isPlaying = false;
    window.speechSynthesis.cancel();
    idx = 0;
    clearHighlight();
    updateButtons();
  });

  sentences.forEach(function (el, i) {
    el.style.cursor = 'pointer';
    el.addEventListener('click', function () {
      if (isPlaying) {
        startPlaybackFrom(i);
      } else {
        playSession++; // 念のため保留中のイベントを無効化
        window.speechSynthesis.cancel();
        idx = i;
        clearHighlight();
        el.classList.add('tts-active');
        scrollToElement(el);
      }
    });
  });

  updateButtons();
})();
