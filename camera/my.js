function update() {
  const focalLength = document.getElementById("focal-length").value;
  const aperture = document.getElementById("aperture").value;
  const iso = document.getElementById("iso").value;

  const focus = calculateFocus(focalLength, aperture, iso);

  document.getElementById("focus").style.top = focus.top + "px";
  document.getElementById("focus").style.left = focus.left + "px";
}

document.addEventListener("DOMContentLoaded", update);

function calculateFocus(focalLength, aperture, iso) {
  // 焦点距離をメートルに変換
  const focalLengthInMeters = focalLength / 1000;

  // 絞り値をF値に変換
  const apertureInFStops = 2 ** (-aperture / 10);

  // 感度をフォトナー数に変換
  const isoInPhotons = iso * 10 ** 6;

  // 焦点位置を計算
  const focus = focalLengthInMeters * apertureInFStops / isoInPhotons;

  return focus;
}
