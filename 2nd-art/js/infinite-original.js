// by http://cly7796.net/wp/javascript/implement-infinite-scrolling/
// 無限スクロールを実装してみる
$(function() {
	// 使用する要素名
	var IScontentItems = '.list__item'; // 取得する要素
	var IScontent = '.list'; // 取得要素を追加するコンテンツ
	var ISlink = '.pager__next'; // 次のページのリンク
	var ISlinkarea = '.pager'; // 次のページのリンクの親要素
	var loadingFlag = false; // 読み込み中はtrueにして、複数回発生しないようにする

	$(window).on('load scroll', function() {
		// 次のページ読み込み中の場合は処理を行わない
		if(!loadingFlag) {
			var winHeight = $(window).height();
			var scrollPos = $(window).scrollTop();
			var linkPos = $(ISlink).offset().top;

			if(winHeight + scrollPos > linkPos) {
				loadingFlag = true;

				// 次のページのリンクを取得して、要素を削除しておく
				var nextPage = $(ISlink).attr('href');
				$(ISlink).remove();
				// 次のページの要素を取得
				$.ajax({
					type: 'GET',
					url: nextPage,
					dataType: 'html'
				}).done(function(data) {
					// 次のページのリンクを取得
					var nextLink = $(data).find(ISlink);
					// コンテンツ要素を取得
					var contentItems = $(data).find(IScontentItems);

					// コンテンツ要素を追加
					$(IScontent).append(contentItems);

					// 次のページがある場合はリンクを追加する
					if(nextLink.length > 0) {
						$(ISlinkarea).append(nextLink);
						loadingFlag = false; // 次のページがない場合はloadingFlagをtrueにしたままにして、処理を発生しないようにする
					}
				}).fail(function () {
					alert('ページの取得に失敗しました。');
				});
			}
		}
	});
});