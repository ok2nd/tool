var ymap = new Y.Map("map_canvas");
function initialize() {
	ymap.setConfigure('dragging',true);
	ymap.setConfigure('doubleClickZoom',true);
	ymap.setConfigure('continuousZoom',true);
	ymap.setConfigure('scrollWheelZoom',true);
	ymap.addControl(new Y.ScaleControl());
	ymap.bind('click', mapClicked);
	zoomFitPoints(points.concat(others));
	var pos;
	for (var ix=0; ix<others.length; ix++) {
		pos = new Y.LatLng(others[ix][0], others[ix][1]);
		showMarker(pos, others[ix][2], 'others', others[ix][3]);
	}
	for (var ix=0; ix<points.length; ix++) {
		pos = new Y.LatLng(points[ix][0], points[ix][1]);
		showMarker(pos, points[ix][2], 'spots');
	}
	$(window).bind("resize", function(e){
		ymap.updateSize();
	});
}
function zoomFitPoints(points) {
	if (points.length == 0) return;
	var squarePoints = fitPoints(points);
	var southWest = new Y.LatLng(squarePoints['minLat'], squarePoints['minLng']);
	var northEast = new Y.LatLng(squarePoints['maxLat'], squarePoints['maxLng']);
	ymap.drawBounds(new Y.LatLngBounds(southWest, northEast), Y.LayerSetId.NORMAL);
}
function fitPoints(points) {
	var maxLat = -999;
	var minLat = 999;
	var maxLng = -999;
	var minLng = 999;
	for (var ix=0; ix<points.length; ix++) {
		var lat = points[ix][0];
		var lng = points[ix][1];
		if (maxLat < lat) maxLat = lat;
		if (minLat > lat) minLat = lat;
		if (maxLng < lng) maxLng = lng;
		if (minLng > lng) minLng = lng;
	}
	return {'maxLat':maxLat, 'minLat':minLat, 'maxLng':maxLng, 'minLng':minLng};
}
var markers = [];
var markersCnt = 0;
var current_infowin = null;
function showMarker(latlng, name, type, opt) {
	if (type == 'others') {
		if (opt == 'b') {
			var icon = new Y.Icon('icon/yellow-dot.png');
		} else if (opt == 'r') {
			var icon = new Y.Icon('icon/purple-dot.png');
		} else {
			var icon = new Y.Icon('icon/orange-dot.png');
		}
	} else {
		var icon = new Y.Icon('icon/red-dot.png');
	}
	var marker = new Y.Marker(latlng, {icon: icon});
	marker.bind('click', spotMarkerClicked);
	ymap.addFeature(marker);
	var label = new Y.Label(new Y.LatLng(latlng.lat(), latlng.lng()), name);
	ymap.addFeature(label);
}
var spotMarkerClicked = function() {
	var latlng = this.getLatLng()
	if (targrt_label) {
		ymap.removeFeature(targrt_label);
	}
	var lat = latlng.lat().toString();
	var lng = latlng.lng().toString();
	var html = '';
	html += '<a style="" href="javascript:plotRouteMarker2Spot(false,' + lat + ',' + lng + ')">青マーカー → 徒歩</a><br><br>';
	html += '<a style="" href="javascript:plotRouteMarker2Spot(true,' + lat + ',' + lng + ')">青マーカー → 車</a>';
	targrt_label = new Y.Label(new Y.LatLng(latlng.lat(), latlng.lng()), html);
	ymap.addFeature(targrt_label);
}
var plotRouteOn = false;
var routeSearchLayer = false;
var routeStartLatLng = false;
var routeEndLatLng = false;
var marker_LatLng = false;
var current_LatLng = false;
var clickPosLatLng = false;
var targrt_marker = false;
var targrt_label = false;
var mapClicked = function(latlng) {
	clickPosLatLng = latlng;
	if (targrt_label) {
		ymap.removeFeature(targrt_label);
	}
	var lat = latlng.lat();
	var lng = latlng.lng();
	var html = '';
	html += '<a style="" href="javascript:newTargrtMarker()">青マーカー作成</a><br><br>';
	html += '<a style="" href="javascript:plotRouteMarker2ClickPos(false)">青マーカー → 徒歩</a><br><br>';
	html += '<a style="" href="javascript:plotRouteMarker2ClickPos(true)">青マーカー → 車</a><br><br>';
	html += '<a href="javascript:window.open(\'https://maps.google.com/maps?q='+lat+','+lng;
	html += '\');void(0);">→Googleマップ</a>';
	targrt_label = new Y.Label(new Y.LatLng(latlng.lat(), latlng.lng()), html);
	ymap.addFeature(targrt_label);
}
var targrt_markerClicked = function() {
	if (targrt_label) {
		ymap.removeFeature(targrt_label);
	}
	var html = '';
	html += '<a style="" href="javascript:deleteTargrtMarker()">マーカー削除</a>';
	targrt_label = new Y.Label(new Y.LatLng(marker_LatLng.lat(), marker_LatLng.lng()), html);
	ymap.addFeature(targrt_label);
}
function newTargrtMarker () {
	if (targrt_label) {
		ymap.removeFeature(targrt_label);
	}
	target_marker_create(clickPosLatLng);
}
function target_marker_create(latlng) {
	deleteTargrtMarker();
	marker_LatLng = latlng;
	var icon = new Y.Icon('icon/ltblue-dot.png');
	targrt_marker = new Y.Marker(latlng, {icon: icon});
	targrt_marker.bind('click', targrt_markerClicked);
	ymap.addFeature(targrt_marker);
}
function deleteTargrtMarker() {
	if (targrt_marker) {
		ymap.removeFeature(targrt_marker);
		targrt_marker = false;
	}
	deleteTargrtLabel();
}
function deleteTargrtLabel() {
	if (targrt_label) {
		ymap.removeFeature(targrt_label);
		targrt_label = false;
	}
}
function plotRouteCurrent2Spot(use_car, lat, lng) {
	if (!currentLocation) {
		alert("現在地を表示してください。");
		return null;
	}
	useCarNow = use_car;
	deleteTargrtLabel();
	routeStartLatLng = current_LatLng;
	routeEndLatLng = new Y.LatLng(lat, lng);;
	RouteView(routeStartLatLng, routeEndLatLng, use_car);
}
function plotRouteMarker2Spot(use_car, lat, lng) {
	if (!targrt_marker) {
		alert("青マーカーがありません。");
		return null;
	}
	useCarNow = use_car;
	ymap.removeFeature(targrt_label);
	routeStartLatLng = marker_LatLng;
	routeEndLatLng = new Y.LatLng(lat, lng);
	RouteView(routeStartLatLng, routeEndLatLng, use_car);
}
function plotRouteCurrent2Marker(use_car) {
	if (!currentLocation) {
		alert("現在地を表示してください。");
		return null;
	}
	useCarNow = use_car;
	deleteTargrtLabel();
	routeStartLatLng = current_LatLng;
	routeEndLatLng = marker_LatLng;
	RouteView(routeStartLatLng, routeEndLatLng, use_car);
}
function plotRouteCurrent2ClickPos(use_car) {
	if (!currentLocation) {
		alert("現在地を表示してください。");
		return null;
	}
	useCarNow = use_car;
	deleteTargrtLabel();
	routeStartLatLng = current_LatLng;
	routeEndLatLng = clickPosLatLng;
	RouteView(routeStartLatLng, routeEndLatLng, use_car);
}
function plotRouteMarker2ClickPos(use_car) {
	if (!targrt_marker) {
		alert("青マーカーがありません。");
		return null;
	}
	useCarNow = use_car;
	ymap.removeFeature(targrt_label);
	routeStartLatLng = marker_LatLng;
	routeEndLatLng = clickPosLatLng;
	RouteView(routeStartLatLng, routeEndLatLng, use_car);
}
function RouteView(start, end, use_car) {
	if (routeSearchLayer) {
		ymap.removeLayer(routeSearchLayer);
	}
	routeSearchLayer = new Y.RouteSearchLayer();
	routeSearchLayer.bind('drawend', function(result) {
		dispRouteInfo(result);
	});
	ymap.addLayer(routeSearchLayer);
	plotRouteOn = true;
	createRoute(start, end, use_car);
}
var createRoute = function(start, end, use_car) {
	if (!plotRouteOn) {
		return;
	}
	if (start && end) {
		var config = {
			enableRestrict: true,	// 交通規制を考慮する
			useCar: use_car,	// 自動車を使う
			useFerry: true		// フェリーを使用する
		};
		routeSearchLayer.execute([start, end], config);
	}
	ymap.unbind('click');
}
function target_RouteGo() {
	if (routeSearchLayer) {
		ymap.removeLayer(routeSearchLayer);
	}
	routeSearchLayer = new Y.RouteSearchLayer();
	routeSearchLayer.bind('drawend', function(result) {
		dispRouteInfo(result);
	});
	ymap.addLayer(routeSearchLayer);
	routeStartLatLng = current_LatLng;
	routeEndLatLng = marker_LatLng;
	createRoute(routeStartLatLng, routeEndLatLng);
}
function plotRouteOff() {
	deleteTargrtLabel();
	if (routeSearchLayer) {
		ymap.removeLayer(routeSearchLayer);
		routeSearchLayer = false;
	}
	plotRouteClear();
	plotRouteOn = false;
}
function plotRouteClear() {
	$("#route_navi").css("display","none");
}
var markerCenter = false;
var currentLocation = false;
var watch_id = 0;
var current_marker = false;
var viewCurrent_first = false;
function viewCurrentOff() {
	if( watch_id > 0 ) {
		navigator.geolocation.clearWatch(watch_id);
	}
	currentLocation = false;
	watch_id = 0;
	if (current_marker) {
		ymap.removeFeature(current_marker);
		viewCurrent_first = false;
	}
}
function viewCurrentLocation() {
	if (currentLocation) {
		viewCurrentOff();
	}
	currentLocation = true;
	var position_options = {
		enableHighAccuracy: true
	};
	watch_id = navigator.geolocation.watchPosition(function(position) {
		var lat = position.coords.latitude;
		var lng = position.coords.longitude;
		var latlng = new Y.LatLng(lat, lng);
		current_LatLng = latlng;
		if (current_marker) {
			ymap.removeFeature(current_marker);
		}
		if (!viewCurrent_first) {
			ymap.setCenter(latlng);
			viewCurrent_first = true;
		}
		showCurrentMarker(latlng);
	}, null, position_options);
}
function showCurrentMarker(latlng) {
	var icon = new Y.Icon('icon/hiking.png');
	current_marker = new Y.Marker(latlng, {icon: icon});
	ymap.addFeature(current_marker);
}
var useCarNow;
var dispRouteInfo = function(result) {
	$("#route_navi").css("display","");
	var route_all = cal_dis_time(result.TotalDistance , result.TotalTime);
	if (useCarNow) {
		var useCarText = '- 車 -<br>';
	} else {
		var useCarText = '- 徒歩 -<br>';
	}
	document.getElementById("route_all_time").innerHTML = '<strong>' + useCarText + route_all + '</strong>';
}
function cal_dis_time(distance, duration) {
	hh = 0;
	dstKm = Math.round(distance / 100) / 10;
	if (duration >= 60)	{
		hh = Math.floor(duration / 60);
		duration = duration % 60;
	}
	var res = '';
	if (hh > 0) {
		res += hh + '時間';
	}
	res += duration + '分'
	res += '<br>' + dstKm + 'km';
	return res;
}
function showCurrentMarker(latlng) {
	var icon = new Y.Icon('icon/hiking.png');
	current_marker = new Y.Marker(latlng, {icon: icon});
	ymap.addFeature(current_marker);
}
var weatherOn = false;
function weatherOnOff() {
	if (!weatherOn) {
		if (ymap.getZoom() > 16) {
			ymap.setZoom(15, true);
		}
		ymap.setConfigure('weatherOverlay', true);
		weatherOn = true;
	} else {
		ymap.setConfigure('weatherOverlay', false);
		weatherOn = false;
	}
}