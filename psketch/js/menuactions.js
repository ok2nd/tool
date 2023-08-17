var THUMBNAIL_SIZE = {x:250, y:250};

function inputDataname(dataname) {
  clocking = false;
  if (arguments.length == 0 || dataname == undefined || dataname == '') {
    alert('Input name');
    return;
  }
  if (localStorage.getItem(dataname)) {
    if (!window.confirm('Data "' + dataname + '" exists. Overwrite anyway?')) {
      return;
    }
  }
  store(dataname);
  hideDialog();
}

function initDatanames() {
  var html = '<ul>';
  for (var i = 0; i < localStorage.length; i++) {
    var key = localStorage.key(i);
    html += '<li><a href="javascript:restore(\'' + key + '\'); hideDialog();">' + key + '</a></li>';
  }
  html += '</ul>';
  $('datanames').innerHTML = html;
}

function openUploadDialog(dialogId) {
  clocking = false;
  myLightWindow.activateWindow({
    href:dialogId, 
    title:'Input data to upload',
    width:220,
    height:180
  });
}

function checkUpload() {
  var titleElm = $('upload-title');
  if (titleElm && titleElm.value && titleElm.value != '') {
    var descriptionElm = $('upload-description');
    var params = 'title=' + encodeURI(titleElm.value) + 
      '&description=' + encodeURI(descriptionElm.value) + 
      '&strokes=' + escape(getAppData()) + 
      '&thumbnail=' + escape(getThumbnailData());
    new Ajax.Request(
      '/app/upload',
      {
        method:'post',
        parameters:params,
        onComplete:function(req) {
          alert('Uploaded!');
          hideDialog();
        }
      }
    );
  }
  else {
    alert('Input title');
  }
}

function getThumbnailData() {
  return canvas.toDataURL('image/jpeg', 0.1);
}

function getAppData() {
  var json = '({version:"1.0", data:{\n';
  json += 'strokes:[\n'
  for (var i = 0; i < strokes.length; i++) {
    var stroke = strokes[i];
    if (!stroke.body) continue;
    json += stroke.toJSON() + ',\n';
  }
  json += '],\n'
  json += 'joints:[\n'
  for (var joint = world.m_jointList; joint; joint = joint.m_next) {
    var p = joint.GetAnchor1();
    json += '[' + p.x + ',' + p.y + '],\n';
  }
  json += ']\n'
  json += '}})';
  return json;
}

function openColorChooser(dialogId, title) {
  myLightWindow.activateWindow({href:dialogId, title:title, width:400, height:180});
}

function store(dataname) {
  localStorage.setItem(dataname, getAppData());
}

function restore(dataname) {
  var jsonStrokes = localStorage.getItem(dataname);
  if (!jsonStrokes) return;
  restoreFromJson(jsonStrokes);
}

function restoreFromJson(jsonStrokes) {
  var json = eval(jsonStrokes);

  var strokeData = json.data.strokes;
  for (var i = 0; i < strokeData.length; i++) {
    var stroke = new Stroke();
    stroke.fromJSON(strokeData[i]);
    strokes.push(stroke);
  }

  var jointData = json.data.joints;
  for (var i = 0; i < jointData.length; i++) {
    var xy = jointData[i];
    var strokesAtThisPoint = strokesAt(xy);
    if (0 < strokesAtThisPoint.length) {
      jointStrokesAt(strokesAtThisPoint[0], strokesAtThisPoint[1], xy[0], xy[1]);
    }
  }
}

function upload(dataname) {
}
