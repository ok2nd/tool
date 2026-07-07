(function () {
'use strict';
/* nav.js は同期的に実行済みなので、DOM 確定後にそのまま初期化 */
function initReader() {
	var currentPage  = 0;
	var totalPages   = 1;
	var scrollOffset = 0;   // 現在のスクロール位置（px）
	var lineWidth    = 40;  // 行ひとつ分の幅（フォント確定後に更新）

	var content = document.getElementById('content');
	var wrapper = document.getElementById('content-wrapper');
	var pageNum = document.getElementById('page-num');
	var btnPrev = document.getElementById('btn-prev');
	var btnNext = document.getElementById('btn-next');

	/* ページ数・行幅を再計算 */
	function computeMetrics() {
		var cw = content.scrollWidth;
		var vw = wrapper.clientWidth;
		totalPages = Math.max(1, Math.ceil(cw / vw));
		var fs = parseFloat(window.getComputedStyle(content).fontSize) || 16;
		lineWidth = Math.max(1, Math.round(fs * 1.7));
	}

	/* offset の上限 */
	function maxOffset() {
		return (totalPages - 1) * wrapper.clientWidth;
	}

	/* offset を有効範囲にクランプ */
	function clampOffset(v) {
		return Math.max(0, Math.min(v, maxOffset()));
	}

	/* 行単位スナップ */
	function snapToLine(offset) {
		return Math.round(offset / lineWidth) * lineWidth;
	}

	/* 任意 offset を適用し、UI を更新 */
	function applyOffset(offset, animate) {
		scrollOffset = clampOffset(offset);
		currentPage  = Math.min(
			Math.round(scrollOffset / wrapper.clientWidth),
			totalPages - 1
		);
		if (animate === false) {
			content.style.transition = 'none';
			content.style.transform  = 'translateX(' + scrollOffset + 'px)';
			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					content.style.transition = '';
				});
			});
		} else {
			content.style.transform = 'translateX(' + scrollOffset + 'px)';
		}
		updateUI();
	}

	/* ページ単位移動（ナビボタン・キー用） */
	function goToPage(page, animate) {
		computeMetrics();
		page = Math.max(0, Math.min(page, totalPages - 1));
		applyOffset(page * wrapper.clientWidth, animate);
	}

	/* ボタン・ページ番号の表示更新 */
	function updateUI() {
		pageNum.textContent = (currentPage + 1) + ' ／ ' + totalPages;
		btnPrev.classList.toggle('disabled', currentPage === 0);
		btnNext.classList.toggle('disabled', currentPage >= totalPages - 1);
	}

	/* ナビボタン */
	btnPrev.addEventListener('click', function () { goToPage(currentPage - 1); });
	btnNext.addEventListener('click', function () { goToPage(currentPage + 1); });

	/* キーボード */
	window.addEventListener('keydown', function (e) {
		if (e.key === 'ArrowLeft'  || e.key === 'PageDown') goToPage(currentPage + 1);
		if (e.key === 'ArrowRight' || e.key === 'PageUp')   goToPage(currentPage - 1);
	});

	/* ──────────────────────────────────────────────
	   ドラッグ：中央エリアで行単位スクロール
	   三角ボタン幅 52px の外側をナビゾーンとして除外
	   ────────────────────────────────────────────── */
	var NAV_ZONE  = 56;   // ボタン幅 + 余裕（px）
	var isDragging    = false;
	var dragStartX    = 0;
	var dragStartOff  = 0;

	function inNavZone(clientX) {
		var rect = wrapper.getBoundingClientRect();
		var rx   = clientX - rect.left;
		return rx < NAV_ZONE || rx > rect.width - NAV_ZONE;
	}

	function onDragStart(clientX) {
		isDragging   = true;
		dragStartX   = clientX;
		dragStartOff = scrollOffset;
		content.style.transition = 'none';
		wrapper.classList.add('is-dragging');
	}

	function onDragMove(clientX) {
		if (!isDragging) return;
		var delta = clientX - dragStartX;
		/* 右ドラッグ → 次ページ方向（offset 増加） */
		var newOff = clampOffset(dragStartOff + delta);
		content.style.transform = 'translateX(' + newOff + 'px)';
	}

	function onDragEnd(clientX) {
		if (!isDragging) return;
		isDragging = false;
		wrapper.classList.remove('is-dragging');
		content.style.transition = '';
		var delta    = clientX - dragStartX;
		var rawOff   = clampOffset(dragStartOff + delta);
		var snapped  = snapToLine(rawOff);
		applyOffset(snapped, true);
	}

	/* ── タッチ ── */
	var activeTouchId = null;

	wrapper.addEventListener('touchstart', function (e) {
		if (activeTouchId !== null) return;
		var t = e.changedTouches[0];
		if (inNavZone(t.clientX)) return;
		activeTouchId = t.identifier;
		onDragStart(t.clientX);
	}, { passive: true });

	wrapper.addEventListener('touchmove', function (e) {
		if (!isDragging) return;
		for (var i = 0; i < e.changedTouches.length; i++) {
			if (e.changedTouches[i].identifier === activeTouchId) {
				onDragMove(e.changedTouches[i].clientX);
				break;
			}
		}
	}, { passive: true });

	wrapper.addEventListener('touchend', function (e) {
		if (!isDragging) return;
		for (var i = 0; i < e.changedTouches.length; i++) {
			if (e.changedTouches[i].identifier === activeTouchId) {
				activeTouchId = null;
				onDragEnd(e.changedTouches[i].clientX);
				break;
			}
		}
	}, { passive: true });

	wrapper.addEventListener('touchcancel', function () {
		if (!isDragging) return;
		activeTouchId = null;
		isDragging = false;
		wrapper.classList.remove('is-dragging');
		content.style.transition = '';
		applyOffset(scrollOffset, true); /* 元位置に戻す */
	}, { passive: true });

	/* ── マウス ── */
	wrapper.addEventListener('mousedown', function (e) {
		if (e.button !== 0) return;
		if (inNavZone(e.clientX)) return;
		onDragStart(e.clientX);
		e.preventDefault(); /* テキスト選択防止 */
	});

	window.addEventListener('mousemove', function (e) {
		onDragMove(e.clientX);
	});

	window.addEventListener('mouseup', function (e) {
		if (e.button !== 0) return;
		onDragEnd(e.clientX);
	});

	/* リサイズ対応 */
	window.addEventListener('resize', function () {
		computeMetrics();
		goToPage(Math.min(currentPage, totalPages - 1), false);
	});

	/* 初期化：フォント・レイアウト確定後に実行 */
	function init() {
		computeMetrics();
		goToPage(0, false);
	}
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(init);
	} else {
		window.addEventListener('load', init);
	}
}
initReader();
})();
