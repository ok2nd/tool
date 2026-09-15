(function () {
  var html = [
    '<!--  ページ番号バー  -->',
    '<div id="page-bar">',
    '  <span id="page-num"></span>',
    '</div>',
    '<!--  前のページ（右端）右向き三角  -->',
    '<button id="btn-prev" class="nav-btn disabled" title="前のページ" aria-label="前のページ">',
    '  <svg width="20" height="44" viewBox="0 0 20 44" xmlns="http://www.w3.org/2000/svg">',
    '    <polygon points="18,22 2,4 2,40"/>',
    '  </svg>',
    '</button>',
    '<!--  次のページ（左端）左向き三角  -->',
    '<button id="btn-next" class="nav-btn" title="次のページ" aria-label="次のページ">',
    '  <svg width="20" height="44" viewBox="0 0 20 44" xmlns="http://www.w3.org/2000/svg">',
    '    <polygon points="2,22 18,4 18,40"/>',
    '  </svg>',
    '</button>'
  ].join('\n');

  document.body.insertAdjacentHTML('beforeend', html);
})();
