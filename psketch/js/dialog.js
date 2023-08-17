var hideDialog;
function showDialog(dialogId, dialogWidth, dialogHeight) {
  hideDialog = function() {
    $('dialog-back').hide();
    $(dialogId).hide();
  };
  clocking = false;
  var dialogBack = $('dialog-back')
  dialogBack.onclick = function() {
    hideDialog(dialogId);
  };
  dialogBack.show();
  var dialog = $(dialogId);
  if (dialog.getAttribute('onLoad')) {
    eval(dialog.getAttribute('onLoad'));
  }
  dialog.style.width = dialogWidth + 'px';
  dialog.style.height = dialogHeight + 'px';
  dialog.style.top = Math.floor(((canvasHeight - dialogHeight)) / 2) + 'px';
  dialog.style.left = Math.floor(((canvasWidth - dialogWidth)) / 2) + 'px';
  dialog.show();
}
