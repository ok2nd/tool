/*
***********************************************************************************
	map-spots-plotRoute.js	Google Maps API V3による複数地点＆ルート表示
***********************************************************************************
	Version		1.06
	Copyright	2014, 2015, 2016, 2017 ok.2nd <m.ok.2nd@gmail.com>
	HomePage	http://ok2nd.web.fc2.com/
			MyHome Portal （個人向けWebポータル）
	Blog		http://ok2nd.blog87.fc2.com/
			中級プログラマの自宅でPHP ブログ
*/
var geocoder = null;
var map;
var ge;
var streetviewWin = false;
var streetviewInit = false;
var panoramioView = false;
var panoramio;
var LinePathUse = false;
var latLngWin;
var side_bar_html = "";
var userAgentMobile = false;
var polyline;
function initialize() {
	if (navigator.userAgent.indexOf('Android') !== -1
		|| navigator.userAgent.indexOf('iPhone') !== -1
		|| navigator.userAgent.indexOf('iPad') !== -1
		|| navigator.userAgent.indexOf('iPod') !== -1) {
		userAgentMobile = true;
	}
	geocoder = new google.maps.Geocoder();
	var myOptions = {
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		gestureHandling: 'greedy',
		// マップ タイプ（地図、衛星写真など）の切り替えに使うマップタイプコントロール
		// https://developers.google.com/maps/documentation/javascript/controls?hl=ja#ControlPositioning
		mapTypeControl: true,
		mapTypeControlOptions: {
			// マップ タイプ（地図、衛星写真など）の切り替えに使うマップタイプコントロールの場所を左下に
			position: google.maps.ControlPosition.LEFT_BOTTOM
		},
		streetViewControl: true
	}
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	panoramio = new google.maps.panoramio.PanoramioLayer();
	latLngWin = new google.maps.InfoWindow();
	google.maps.event.addListener(map, 'click',viewLatLngAddress);
	var lineOptions = {
		strokeColor: "#ff4500",
		strokeOpacity: 1.0,
		strokeWeight: 3
	}
	polyline = new google.maps.Polyline(lineOptions);
	polyline.setMap(map);
	google.maps.event.addListener(map, "click", drawLinePath);
	for (var ix=0; ix<others.length; ix++) {
		pos = new google.maps.LatLng(others[ix][0], others[ix][1]);
		showMarker(map, pos, others[ix][2], 'others', others[ix][3]);
	}
	var pos;
	for (var ix=0; ix<points.length; ix++) {
		pos = new google.maps.LatLng(points[ix][0], points[ix][1]);
		showMarker(map, pos, points[ix][2], 'spots');
	}
	if (points.length > 8) {	// 9地点以上はルート検索がエラーになる
		$("#plotAllRoute").css("display","none");
	}
	zoomFitPoints(points.concat(others));
}
function zoomFitPoints(points) {
	if (points.length == 0) return;
	var squarePoints = fitPoints(points);
	var southWest = new google.maps.LatLng(squarePoints['minLat'], squarePoints['minLng']);
	var northEast = new google.maps.LatLng(squarePoints['maxLat'], squarePoints['maxLng']);
	var bounds = new google.maps.LatLngBounds(southWest,northEast);
	map.fitBounds(bounds);
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
var presentLocation = false;
var watch_id = 0;
var current_marker = false;
var viewPresent_first = false;
function viewPresentOff() {
	if( watch_id > 0 ) {
		navigator.geolocation.clearWatch(watch_id);
	}
	presentLocation = false;
	watch_id = 0;
	if (current_marker) {
		current_marker.setMap(null);
		viewPresent_first = false;
	}
}
function viewPresentLocation() {
	if (presentLocation) {
		var options = {
			 timeout: 10000,
			 maximumAge: 20000,
			 enableHighAccuracy: true
		};
		navigator.geolocation.getCurrentPosition(successGetPos, errorGetPos, options);
		return false;
	}
	presentLocation = true;
	var position_options = {
		enableHighAccuracy: true
	};
	watch_id = navigator.geolocation.watchPosition(function(position) {
		var myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		if (current_marker) {
			current_marker.setMap(null);
		}
		if (!viewPresent_first) {
			map.setCenter(myLatlng);
			viewPresent_first = true;
		}
		var icon = new google.maps.MarkerImage('icon/hiking.png');
		current_marker = new google.maps.Marker({
			position: myLatlng,
			icon: icon,
			map: map
		});
	}, null, position_options);
}
function successGetPos(position) {
	var myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
	map.setCenter(myLatlng);
}
function errorGetPos(error) {
	return false;
}
function PlacesSearch() {
	clearPlacesMarker();
	var place_type = $("#places").val();
	var currentBounds = map.getBounds();
	var request = {
		bounds: currentBounds,
		types: [place_type]
	};
	var service = new google.maps.places.PlacesService(map);
	service.search(request, PlacesSearchCallback);
}
function myClick(markerCnt) {
	google.maps.event.trigger(placesMarker[markerCnt], 'click');
}
var markerZindex = 0;
var placesMarker = [];
var placesMarkerCnt = 0;
function PlacesSearchCallback(results, status) {
	if (status == google.maps.places.PlacesServiceStatus.OK && results.length > 0) {
		for (var i=0; i < results.length; i++) {
			createPlaceMarker(results[i]);
		}
	} else {
		alert("見つかりませんでした。");
	}
}
function createPlaceMarker(place) {
	if (!place.name) return;
	var placeLoc = place.geometry.location;
	var icon = markerLabelBubble(place.name, 'FFD700', 's');
	markerZindex++;
	var marker = new google.maps.Marker({
		map: map,
		icon: icon,
		title: place.name,
		zIndex: markerZindex,
		position: new google.maps.LatLng(placeLoc.lat(), placeLoc.lng())
	});
	var infowindow = new google.maps.InfoWindow({
		maxWidth:320
	});
	var html = '<div class="ttl">';
	html += (place.icon)?'<img src="'+place.icon+'">':'';
	html += '<a href="javascript:window.open(\'http://www.google.com/search?q='+encode_amp(place.name)+'\');void(0);">'+place.name+'</a>';
	html += '</div>';
	if (place.vicinity) {
		html += '<p style="white-space:nowrap;">'+place.vicinity+'</p>';
	}
	if (place.rating) {
		html += '<p>評価：'+place.rating+'</p>';
	}
	html += '<p><a href="javascript:window.open(\'http://maps.google.com/maps?q='+place.geometry.location.lat()+','+place.geometry.location.lng();
	html += '\');void(0);">→Googleマップ</a>';
	if (place.photos && place.photos.length >= 1) {
		html += '<p class="picframe"><img src="'+place.photos[0].getUrl({'maxWidth':160,'maxHeight':160})+'"></p>';
	}
	google.maps.event.addListener(marker, 'click', function() {
		current_infowinClose();
		infowindow.setContent('<div class="infowin">' + html + '</div>');
		infowindow.open(map, this);
		marker.setZIndex(++markerZindex);
		current_infowin = infowindow;
	});
	side_bar_html += '<li><a href="javascript:myClick(' + placesMarkerCnt + ')">' + place.name + '</a></li>';
	document.getElementById('side_bar').innerHTML = "<ul>"+side_bar_html+"</ul>";
//	$("#side_bar").css("display","");	// 2017.06.30 表示しない
	placesMarker[placesMarkerCnt] = marker;
	placesMarkerCnt++;
}
function clearPlacesMarker() {
	for (var ix=0; ix<placesMarkerCnt; ix++) {
		if (placesMarker[ix]) {
			placesMarker[ix].setMap(null);
		}
	}
	placesMarkerCnt = 0;
	$("#side_bar").css("display","none");
	side_bar_html = '';
	document.getElementById('side_bar').innerHTML = '';
}
function showCurrentPosWin(map, latlng) {
	var lat = latlng.lat().toString();
	var lng = latlng.lng().toString();
	geocoder.geocode({'latLng': latlng}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (results[1]) {
				 address = results[1].formatted_address;
			} else {
				 address = ""
			}
		} else {
			address = ""
		}
		var elevation = '';
		var locations = [latlng];
		var elevator = new google.maps.ElevationService();
		elevator.getElevationForLocations({
			locations: locations
		}, function(results, status) {
			if (status == google.maps.ElevationStatus.OK) {
				if (results[0].elevation) {
					elevation = results[0].elevation.toFixed(1);
				}
			}
			var html = '<div class="ttl">';
			html += '<a href="javascript:window.open(\'http://www.google.com/search?q='+encode_amp(address)+'\');void(0);">'+address.replace('日本,','')+'</a>';
			html += '</div>';
			html += '<p>標高：'+elevation+'m</p>';
			html += '<a href="javascript:window.open(\'http://maps.google.com/maps?q='+encodeURL(lat)+','+encodeURL(lng);
			html += '\');void(0);">→Googleマップ</a>';
			html += '<br><a style="" href="javascript:plotRoute1(\'current\', '+lat+','+lng+')">現在地から徒歩</a>';
			html += '<br><a style="" href="javascript:plotRoute1(\'marker1\', '+lat+','+lng+')">青マーカーから徒歩</a>';
			html += '<br><a style="" href="javascript:createMarker1('+lat+','+lng+')">青マーカー作成</a>';
			latLngWin.setContent('<div class="infowin">' + html + '</div>');
			latLngWin.setPosition(latlng);
			latLngWin.open(map);
			current_infowin = latLngWin;
		});
	 });
}
function viewLatLngAddress(e) {
	if (LinePathUse) return;
	if (streetviewWin) return;	// ストリートビュー状態では、現在地情報を表示しない (Ver.1.01)
	if (currentInfoOff) return;	// 現在地情報OFF状態では、現在地情報を表示しない (Ver.1.06)
	var latlng = e.latLng;
	showCurrentPosWin(map, latlng);
}
var markers = [];
var markersCnt = 0;
var current_infowin = null;
function showMarker(map, position, address, type, opt) {
	var elevation = '';
	var locations = [position];
	var elevator = new google.maps.ElevationService();
	elevator.getElevationForLocations({
		locations: locations
	}, function(results, status) {
		if (status == google.maps.ElevationStatus.OK) {
			if (results[0].elevation) {
				elevation = results[0].elevation.toFixed(1);
			}
		}
		address = address.trim();
		var markers_data = [position.lat(), position.lng(), address.replace(/:/g,'：')];
		var search_address = address.split('：');
		var html = '<div class="ttl">';
		html += '<a href="javascript:window.open(\'http://www.google.com/search?q='+encode_amp(search_address[0])+'\');void(0);">'+address+'</a>';
		html += '</div>';
		html += '<p>標高：'+elevation+'m</p>';
		html += '<p><a href="javascript:window.open(\'http://maps.google.com/maps?q='+position.lat()+','+position.lng();
		html += '\');void(0);">→Googleマップ</a>';
		html += '<br><a style="" href="javascript:plotRoute1(\'current\', '+position.lat()+','+position.lng()+')">現在地から徒歩</a>';
		html += '<br><a style="" href="javascript:plotRoute1(\'marker1\', '+position.lat()+','+position.lng()+')">青マーカーから徒歩</a>';
		var infowindow = new google.maps.InfoWindow({
			content: '<div class="infowin">' + html + '</div>'
		});
		if (type == 'others') {
			if (opt == 'b') {
				marker = spotsMarker(position, address, 'icon/ltblue-dot', 'FFC0CB', '', infowindow);
			} else if (opt == 'r') {
				marker = spotsMarker(position, address, 'icon/purple.png', 'FFFF80', 's', infowindow);
			} else {
				marker = spotsMarker(position, address, 'icon/orange.png', 'C1EFFF', 's', infowindow);
			}
		} else {
			marker = spotsMarker(position, address, 'icon/marker-small.png', 'FFC0CB', '', infowindow);
			markers[markersCnt] = marker;
			markersCnt++;
		}
	});
}
function spotsMarker(position, address, m_image, b_color, text_size, infowindow) {
	var icon = new google.maps.MarkerImage(m_image);
	var marker = new google.maps.Marker({
		icon: icon,
		map: map, 
		position: position
	});
	markerAddInfowin(marker, infowindow);
	icon = markerLabelBubble(address, b_color, text_size);
	markerZindex++;
	var marker_l = new google.maps.Marker({
		icon: icon,
		map: map,
		zIndex: markerZindex,
		position: position
	});
	markerAddInfowin(marker_l, infowindow);
	return marker;
}
function markerLabelBubble(address, color, size) {
	var marker_label = zen2han(address).replace(/&/g,'＆').replace(/’/g,"'");
	var label = 'http://chart.apis.google.com/chart?chst=d_bubble_text_small&chld=bb|'+marker_label+'|'+color+'|000000';
	var icon = new google.maps.MarkerImage(label);
	icon.anchor = new google.maps.Point(0, 24);
	if (size == 's') {
		icon.scaledSize = new google.maps.Size(str_width(marker_label)*4+20, 24);
	} else {
		icon.scaledSize = new google.maps.Size(str_width(marker_label)*5+20, 26);
	}
	return icon;
}
function markerLabeloOutline(address, color) {
	var marker_label = zen2han(address).replace(/&/g,'＆');
	var label = 'http://chart.apis.google.com/chart?chst=d_text_outline&chld='+color+'|13|l|fff|_|'+marker_label;
	var icon = new google.maps.MarkerImage(label);
	icon.anchor = new google.maps.Point(-5, 12);
	return icon;
}
function markerAddInfowin(addMarker, infowindow) {
	google.maps.event.addListener(addMarker, 'click', function() {
		current_infowinClose();
		infowindow.open(map, addMarker);
		addMarker.setZIndex(++markerZindex);
		current_infowin = infowindow;
	});
}
function markerVisible(onoff) {
	for (var ix=0; ix<markersCnt; ix++) {
		if (markers[ix]) {
			markers[ix].setOptions({visible:onoff});
		}
	}
}
function StreetViewOnOff() {
	if (!streetviewWin) {
		StreetViewOn();
	} else {
		StreetViewOff();
	}
}
var currentInfoOff = false;
function CurrentInfoOnOff() {
	if (currentInfoOff) {
		currentInfoOff = false;
		$("#cb_ci").attr("checked", false);
	} else {
		currentInfoOff = true;
		$("#cb_ci").attr("checked", true);
	}
}
var marker1 = null;
function createMarker1(lat, lng) {
	if (marker1) {
		deleteMarker1();
	}
	var icon = new google.maps.MarkerImage('icon/ltblue-dot.png');
	var marker1pos = new google.maps.LatLng(lat, lng);
	marker1 = new google.maps.Marker({
		icon: icon,
		map: map, 
		position: marker1pos
	});
	var html = '<div class="ttl">';
	html += '<a style="" href="javascript:plotRoute1(\'current\', '+lat+','+lng+')">現在地から徒歩</a>';
	html += '<br><a href="javascript:deleteMarker1()">青マーカー削除</a></div>';
	var infowindow = new google.maps.InfoWindow({
		content: '<div class="myinfowin" class="infowin">' + html + '</div>'
	});
	google.maps.event.addListener(marker1, 'click', function() {
		current_infowinClose();
		infowindow.open(map, marker1);
		marker1.setZIndex(++markerZindex);
		current_infowin = infowindow;
	});
	latLngWin.close(map);
}
function deleteMarker1() {
	marker1.setMap(null);
	marker1 = null;
}
var panorama;
function StreetViewOn() {
	$("#panorama").css("display","");
	if (!streetviewInit) {
		var panoramaOptions = {
			pov: {
				heading: 34,
				pitch: 10,
				zoom: 1
			}
		};
		panorama = new google.maps.StreetViewPanorama(document.getElementById("panowin"), panoramaOptions);
		map.setStreetView(panorama);
	}
	streetviewInit = true;
	streetviewWin = true;
}
function StreetViewOff() {
	$("#panorama").css("display","none");
	streetviewWin = false;
	$("#cb_sv").attr("checked", false);
	panorama.setVisible(false);
}
function PanoramioOnOff(me) {
	if (me.checked) {
		$("#panoramio_opt").css("display","");
		panoramio.setMap(map);
	} else {
		$("#panoramio_opt").css("display","none");
		panoramio.setMap(null);
	}
}
function LinePathOnOff() {
	if (LinePathUse) {
		LinePathUse = false;
	} else {
		LinePathUse = true;
	}
}
function drawLinePath(event) {
	if (LinePathUse) {
		var lpath = polyline.getPath();
		lpath.push(event.latLng);
		kyori = google.maps.geometry.spherical.computeLength(lpath.getArray());
		document.getElementById("lineLength").value = num_format(kyori,1);
	}
}
function clearLinePath() {
	polyline.setMap(null);
	var lineOptions = {
		strokeColor: "#ff4500",
		strokeOpacity: 1.0,
		strokeWeight: 3
	}
	polyline = new google.maps.Polyline(lineOptions);
	polyline.setMap(map);
	google.maps.event.addListener(map, "click", drawLinePath);
	document.getElementById("lineLength").value = "";
}
function zoomMarkerAll() {
	zoomFitPoints(points.concat(others));
}
var directionsService = new google.maps.DirectionsService();
var proute = null;
var coordinates = [];
var directions;
function travelModeSelect() {
	if (proute != null) {
		plotRouteClear();
		plotRouteView();
	}
}
function plotRouteOnOff() {
	if (proute == null) {
		proute = plotRouteView();
		markerVisible(false);
	} else {
		plotRouteClear();
		markerVisible(true);
		proute = null;
	}
}
function plotWalkOff() {
	plotRouteClear();
	$(".cb_pw_label").css("display","none");
	$("#cb_pr").attr('disabled', false);
}
var route_navi = false;
var directionsDisplay;
var waypoints_cnt;
function plotRouteView() {
	var waypoints = [];
	waypoints_cnt = 0;
	var start = null
	var end = null
	if (markersCnt < 2) {
		alert("マーカーが2つありません。");
		$("#cb_pr").attr('checked', false);
		return null;
	}
	for (var ix=0; ix<markersCnt; ix++) {
		if (markers[ix] != null) {
			var latlng = markers[ix].position;
			waypoints.push({
				location:latlng,
				stopover:true
			});
			waypoints_cnt += 1;
			if (start == null) {
				start = latlng;
			} else {
				end = latlng;
			}
		}
	}
	if (end == null) {
		alert("マーカーが2つありません。");
		$("#cb_pr").attr('checked', false);
		return null;
	}
	if ($("#travel_mode").val() == "d") {
		var travelMode = google.maps.DirectionsTravelMode.DRIVING;
		var avoidTolls = false;
	} else if ($("#travel_mode").val() == "t") {
		var travelMode = google.maps.DirectionsTravelMode.DRIVING;
		var avoidTolls = true;
	} else {
		var travelMode = google.maps.DirectionsTravelMode.WALKING;
		var avoidTolls = false;
	}
	plotRouteViewGo(waypoints, start, end, travelMode, avoidTolls);
	return 'on';
}
function plotRoute1(type, lat, lng) {
	var waypoints = [];
	var start = null;
	var end = new google.maps.LatLng(lat, lng);
	if (type == 'marker1') {
		if (marker1 == null) {
			alert("青マーカーがありません。");
			return null;
		}
		waypoints.push({
			location: marker1.position,
			stopover: true
		});
		start = marker1.position;
	} else {
		if (current_marker == false) {
			alert("現在地を表示してください。");
			return null;
		}
		waypoints.push({
			location: current_marker.position,
			stopover: true
		});
		start = current_marker.position;
	}
	var tpos = new google.maps.LatLng(lat, lng);
	waypoints.push({
		location: end,
		stopover: true
	});
	waypoints_cnt = 2;
	plotRouteViewGo(waypoints, start, end, google.maps.DirectionsTravelMode.WALKING, false);
	$(".cb_pw").attr('checked', true);
	$(".cb_pw_label").css("display", "");
	$("#cb_pr").attr('disabled', true);
	current_infowinClose();
}
function plotRouteViewGo(waypoints, start, end, travelMode, avoidTolls) {
	if (route_navi) {
		directionsDisplay.setMap(null);
		directionsDisplay.setPanel(null);
	}
//	if (userAgentMobile) {
//		var rendererOptions = {draggable: false };
//	} else {
		var rendererOptions = {draggable: true };
//	}
	directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
	directionsDisplay.setMap(map);
	route_navi = true;
	directionsDisplay.setPanel(document.getElementById("route_navi_hidden"));
	$("#route_navi").css("display","");
	$("#route_navi_open").css("display","none");
	var request = {
		origin: start,
		destination: end,
		waypoints: waypoints,
		optimizeWaypoints: true,
		avoidTolls: avoidTolls,
		travelMode: travelMode
	};
	directionsService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay.setDirections(response);
			route_all_calculate(response);
		}
	});
	google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
		route_all_calculate(directionsDisplay.directions);
	});
}
function route_all_calculate(response) {
	var route = response.routes[0];
	var distance = 0;
	var duration = 0;
	var dis_time = '';
	for (var i=0; i<=waypoints_cnt; i++) {
		distance += route.legs[i].distance.value;
		duration += route.legs[i].duration.value;
		dis_time += '<br>' + String.fromCharCode(i+66) + ') ' + cal_dis_time(route.legs[i].distance.value, route.legs[i].duration.value);
	}
	var route_all = cal_dis_time(distance, duration); 
	document.getElementById("route_all_time").innerHTML = '<strong>' + route_all + '</strong>' + dis_time;
}
function cal_dis_time(distance, duration) {
	var hh = 0;
	var dstKm = Math.round(distance / 100) / 10;
	if (duration >= 3600)	{
		hh = Math.floor(duration / 3600);
		duration = duration % 3600;
	}
	var mm = Math.ceil(duration / 60);
	var res = '<span class="c_route_desc">時間：</span>';
	if (hh > 0) {
		res += hh + '時間';
	}
	res += mm + '分'
	res += ' <span class="c_route_desc">距離：</span>' + dstKm + 'km';
	return res;
}
function plotRouteClear() {
	$("#route_navi").css("display","none");
	$("#route_navi_open").css("display","none");
	directionsDisplay.setMap(null);
}
function route_naviOff() {
	$("#route_navi").css("display","none");
	$("#route_navi_open").css("display","");
}
function route_naviOn() {
	$("#route_navi_open").css("display","none");
	$("#route_navi").css("display","");
}
function current_infowinClose() {
	if (current_infowin) {
		current_infowin.close();
		current_infowin = null;
	}
}
