/*
The MIT License

Copyright (c) 2010 ANDO Yasushi

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

b2Settings.b2_maxPolyVertices = 50;

/* demos/demo_base.js */
function createWorld() {
  var worldAABB = new b2AABB();
  worldAABB.minVertex.Set(-500, -500);
  worldAABB.maxVertex.Set(1500, 1500);
  var gravity = new b2Vec2(0, 300);
  var doSleep = true;
  var world = new b2World(worldAABB, gravity, doSleep);
  groundBody = createGround(world);
  return world;
}

function createGround(world) {
  var groundSd = new b2BoxDef();
  groundSd.extents.Set(2000, 50);
  groundSd.restitution = 0.2;
  var groundBd = new b2BodyDef();
  groundBd.AddShape(groundSd);
  groundBd.position.Set(-500, 815);
  return world.CreateBody(groundBd)
}

function createBall(world, x, y) {
  var ballSd = new b2CircleDef();
  ballSd.density = 1.0;
  ballSd.radius = 20;
  ballSd.restitution = 1.0;
  ballSd.friction = 0;
  var ballBd = new b2BodyDef();
  ballBd.AddShape(ballSd);
  ballBd.position.Set(x,y);
  return world.CreateBody(ballBd);
}

function createBox(world, x, y, width, height, fixed) {
  if (typeof(fixed) == 'undefined') fixed = true;
  var boxSd = new b2BoxDef();
  if (!fixed) boxSd.density = 1.0;
  boxSd.extents.Set(width, height);
  var boxBd = new b2BodyDef();
  boxBd.AddShape(boxSd);
  boxBd.position.Set(x,y);
  return world.CreateBody(boxBd)
}

/* physicSketch original part */
function createBall(world, x, y, rad, fixed) {
  var ballSd = new b2CircleDef();
  if (!fixed) ballSd.density = 1.0;
  ballSd.radius = rad || 10;
  ballSd.restitution = 0.2;
  var ballBd = new b2BodyDef();
  ballBd.AddShape(ballSd);
  ballBd.position.Set(x,y);
  return world.CreateBody(ballBd);
};


var Stroke = Class.create({
  initialize: function(x, y) {
    this.points = $A([[0, 0]]);
    this.baseX = x;
    this.baseY = y;
    this.topIndex = 0;
    this.angles = $A();
    this.body = null;
    this.bodyType = null;
    this.selected = false;
    this.draggedPoint = null;
    this.cullPoints = null;
    this.fillStyle = '#FFFFFF';
  },

  getFillStyle: function() {
    //return this.selected ? '#EEEEFF' : this.fillStyle;
    return this.fillStyle;
  },

  getStrokeStyle: function() {
    // not used
    return this.selected ? '#FF0000' : this.strokeStyle;
  },

  addPoint: function(x, y) {
    x -= this.baseX;
    y -= this.baseY;
    this.points.push([x, y]);
    if (this.points[this.topIndex][1] < y) {
      this.topIndex = this.points.length - 1;
    }
  },

  becomeBodyIn: function(w) {
    if (this.points.length < 3) return;
    var firstPoint = new b2Vec2(this.points.first()[0], this.points.first()[1]);
    var lastPoint = new b2Vec2(this.points.last()[0], this.points.last()[1]);
    firstPoint.Subtract(lastPoint);
    if (firstPoint.Length() < 30.0) {
      this.drawShape();
    }
    else {
      this.drawLines();
    }
  },

  drawShape: function() {
    var ps = $A();
    for (var i = 0; i < this.points.length; i++) {
      ps.push(this.points[(this.topIndex + i) % this.points.length]);
    }
    ps.push(this.points[this.topIndex]);

    var cw = $A();
    var cwAngles = $A();
    for (var i = 0; i < ps.length; i++) {
      this.addConvexPoint(cw, ps[i], cwAngles);
    }
    var ccw = $A();
    var ccwAngles = $A();
    for (var i = ps.length - 1; 0 <= i; i--) {
      this.addConvexPoint(ccw, ps[i], ccwAngles);
    }
    var massPoints;
    if (cw.length < ccw.length) {
      massPoints = ccw;
      this.angles = ccwAngles;
    }
    else {
      massPoints = cw;
      this.angles = cwAngles;
    }
    massPoints.pop();

    this.cullPoints = $A();
    if (massPoints.length < b2Settings.b2_maxPolyVertices) {
      this.cullPoints = massPoints;
    }
    else {
      for (var i = 0; i < massPoints.length - 1; i++) {
        if (i % Stroke.INTERVAL == 0) {
          this.cullPoints.push(massPoints[i]);
        }
      }
      this.cullPoints.push(massPoints[massPoints.length - 1]);
    }
    if (2 < this.cullPoints.length) {
      this.body = this.createPoly(world, this.baseX, this.baseY, this.cullPoints);
      this.bodyType = 'poly';
    }
  },

  addConvexPoint: function(ary, p, angleAry) {
    if (ary.length < 2) {
      ary.push(p);
      return;
    }
    var p1 = ary[ary.length-2];
    var p2 = ary[ary.length-1];
    var p3 = p;
    var v12 = new b2Vec2(p2[0] - p1[0], p2[1] - p1[1]);
    var v13 = new b2Vec2(p3[0] - p1[0], p3[1] - p1[1]);
    if (0 < b2Math.b2CrossVV(v12, v13)) {
      var angle = Math.acos(b2Math.b2CrossVV(v12, v13) / (v13.Length() * v12.Length()));
      angleAry.push(angle);
      ary.push(p);
    }
    else {
      angleAry.pop();
      ary.pop();
      this.addConvexPoint(ary, p, angleAry);
    }
  },

  drawLines: function() {
    this.cullPoints = $A();
    for (var i = 0; i < this.points.length - 1; i++) {
      //if (i % Stroke.INTERVAL == 0) {
      if (i % 10 == 0) {
        this.cullPoints.push(this.points[i]);
      }
    }
    if (this.cullPoints.last()[0] != this.points.last()[0] || 
        this.cullPoints.last()[1] != this.points.last()[1]) {
      this.cullPoints.push(this.points.last());
    }
    this.body = this.createLines(world, this.baseX, this.baseY, this.cullPoints);
    this.bodyType = 'lines';
  },

  createPoly: function(world, x, y, points) {
    var polySd = new b2PolyDef();
    polySd.density = 1.0;
    polySd.vertexCount = points.length;
    for (var i = 0; i < points.length; i++) {
      polySd.vertices[i].Set(points[i][0], points[i][1]);
    }
    var polyBd = new b2BodyDef();
    polyBd.AddShape(polySd);
    polyBd.position.Set(x, y);
    return world.CreateBody(polyBd)
  },

  createLines: function(world, x, y, points) {
    var linesBd = new b2BodyDef();
    for (var i = 1; i < points.length; i++) {
      var p1 = points[i-1];
      var p2 = points[i]; 
      var w = new b2Vec2(p2[1] - p1[1], p1[0] - p2[0]);
      w.Normalize();
      w.Multiply(10);
      var lineSd = new b2PolyDef();
      lineSd.density = 1.0;
      lineSd.vertexCount = 4;
      lineSd.vertices[0].Set(p1[0], p1[1]);
      lineSd.vertices[1].Set(p2[0], p2[1]);
      lineSd.vertices[2].Set(p2[0] - w.x, p2[1] - w.y);
      lineSd.vertices[3].Set(p1[0] - w.x, p1[1] - w.y);
      linesBd.AddShape(lineSd);
    }
    linesBd.position.Set(x, y);
    var body = world.CreateBody(linesBd);
    return body;
  },

  hasBody: function() {
    return this.body != null;
  },

  toJSON: function() {
    var json = '{\n';
    json += '  points:[\n';
    for (var i = 0; i < this.points.length; i++) {
      var point = this.points[i];
      json += '    [' + point[0] + ',' + point[1] + '],\n'
    }
    json += '  ],\n';
    json += '  baseX:' + this.baseX + ',\n';
    json += '  baseY:' + this.baseY + ',\n';
    json += '  topIndex:' + this.topIndex + ',\n';
    json += '  angles:[\n';
    for (var i = 0; i < this.angles.length; i++) {
      json += '    ' + this.angles[i] + ',\n';
    }
    json += '  ],\n';
    json += '  bodyType:"' + this.bodyType + '",\n';
    var body = this.body;
    json += '  body:{\n';
    json += '    m_position:{x:' + body.m_position.x + ',y:' + body.m_position.y + '},\n';
    json += '    m_center:{x:' + body.m_center.x + ',y:' + body.m_center.y + '},\n';
    json += '    m_rotation:' + body.m_rotation + ',\n';
    json += '  },\n';
    json += '  selected:' + (this.selected ? 'true' : 'false') + ',\n';
    if (this.draggedPoint) {
      json += '  draggedPoint:{x:' + this.draggedPoint.x + ',y:' + this.draggedPoint.y + '},\n';
    }
    json += '  cullPoints:[\n';
    for (var i = 0; i < this.cullPoints.length; i++) {
      var cullPoint = this.cullPoints[i];
      json += '    [' + cullPoint[0] + ',' + cullPoint[1] + '],\n';
    }
    json += '  ],\n';
    json += '  fillStyle:"' + this.fillStyle + '"\n';
    json += '}';
    return json;
  },

  fromJSON: function(json) {
    this.points = json.points;
    this.baseX = json.baseX;
    this.baseY = json.baseY;
    this.topIndex = json.topIndex;
    this.angles = json.angles;
    this.bodyType = json.bodyType;
    //this.selected = json.selected;
    this.draggedPoint = json.draggedPoint;
    this.cullPoint = json.cullPoint;
    this.fillStyle = json.fillStyle;
    this.becomeBodyIn(world);
    var body = json.body;
	var m_R = new b2Mat22(body.m_rotation);
    var position = b2Math.SubtractVV(body.m_position, b2Math.b2MulMV(m_R, body.m_center));
    this.body.SetOriginPosition(position, body.m_rotation);
  }
});
Stroke.INTERVAL = 3;

/* demos/demos.js */
var groundBody; // TODO
var world = createWorld();
var ctx;
var canvasWidth;
var canvasHeight;
var canvasTop;
var canvasLeft;
var strokes = $A();
var trackingStroke = null;
var selectedStroke = null;
var draggingStroke = null;
var draggedMouseXY = null;
var clocking = true;
var bodyVisibility = false;
var usePin = true;

var trackingStrokeStyle = '#FFFF00';
var jointStrokeStyle = '#8888FF';
var strokeStyle = '#A9A9A9';
var selectedStrokeStyle = '#FF0000';
var backgroundStyle = bg_color;

function getStrokeStyle(selected) {
  return selected ? selectedStrokeStyle : strokeStyle;
}
function changeStrokeColor(code) {
  strokeStyle = code;
}
function changeSelectedStrokeColor(code) {
  selectedStrokeStyle = code;
}
function changeBGColor(code) {
  backgroundStyle = code;
}

function hideSidebar() {
  $('sidebar2').style.display = 'none';
}
function showSidebar() {
  $('sidebar2').style.display = 'block';
}
function toggleTimer() {
  clocking = !clocking;
/*
  if (arguments.length != 0) {
    if (clocking) {
      elm.innerHTML = 'Stop Timer';
    }
    else {
      elm.innerHTML = 'Start Timer';
    }
  }
*/
}
function toggleBodyVisibility(elm) {
  bodyVisibility = !bodyVisibility;
  if (bodyVisibility) {
    elm.innerHTML = 'Hide Physical Body';
  }
  else {
    elm.innerHTML = 'Show Physical Body';
  }
}
function showInformation(stroke) {
  var body = stroke.body;
  var position = body.GetCenterPosition();
  $('position-x').innerHTML = Math.floor(position.x);
  $('position-y').innerHTML = Math.floor(position.y);
  //$('angle').innerHTML = Math.floor(body.m_rotation * 180.0 % 360);
  $('angle').innerHTML = Math.floor(body.m_rotation * 60.0 % 360); //TODO: what's 60.0?
  //$('body-color').value = stroke.fillStyle;
}
function deleteSelectedBody() {
  if (!selectedStroke) return;
  var newStrokes = $A();
  for (var i = 0; i < strokes.length; i++) {
    var stroke = strokes[i];
    if (selectedStroke == stroke) {
      world.DestroyBody(stroke.body);
    }
    else {
      newStrokes.push(stroke);
    }
  }
  strokes = newStrokes;
}
function togglePin(elm) {
  usePin = !usePin;
  if (usePin) {
    elm.innerHTML = 'Not Use Pin';
  }
  else {
    elm.innerHTML = 'Use Pin';
  }
}
function changeFillColor(code) {
  selectedStroke.fillStyle = code;
}
function drawStrokes(context) {
  function pointInWorld(stroke, index) {
    var body = stroke.body;
    var shape = body.GetShapeList();
    var point = stroke.points[index];
    var vec = new b2Vec2(point[0], point[1]);
    var baseVec = body.m_center;
    var tmpPoint = b2Math.SubtractVV(vec, baseVec);
    return b2Math.AddVV(body.m_position, b2Math.b2MulMV(body.m_R, tmpPoint));
  }

  context.lineWidth = 5;
  for (var i = 0; i < strokes.length; i++) {
    //context.strokeStyle = strokes[i].getStrokeStyle();
    context.strokeStyle = getStrokeStyle(strokes[i].selected);
    context.fillStyle = strokes[i].getFillStyle();
    var point = pointInWorld(strokes[i], 0);
    context.beginPath();
    context.moveTo(point.x, point.y);
    for (var j = 1; j < strokes[i].points.length; j++) {
      point = pointInWorld(strokes[i], j);
      context.lineTo(point.x, point.y);
    }
    if (strokes[i].bodyType == 'poly') {
      point = pointInWorld(strokes[i], 0);
      context.lineTo(point.x, point.y);
      context.fill();
      context.stroke();
    }
    else {
      context.stroke();
    }
  }
}
function drawTracking(context) {
  if (!trackingStroke) return;
  context.strokeStyle = trackingStrokeStyle;
  context.lineWidth = 5;
  var points = trackingStroke.points;
  if (points.length < 2) return;
  context.beginPath();
  context.moveTo(trackingStroke.baseX + points[0][0], trackingStroke.baseY + points[0][1]);
  for (var i = 1; i < points.length; i++) {
    context.lineTo(trackingStroke.baseX + points[i][0], trackingStroke.baseY + points[i][1]);
  }
  context.stroke();
}
function drawJoints(context) {
  for (var joint = world.m_jointList; joint; joint = joint.m_next) {
    var p = joint.GetAnchor1();
    context.strokeStyle = jointStrokeStyle;
    context.lineWidth = 3;
    context.beginPath();
    context.moveTo(p.x - 5, p.y - 5)
    context.lineTo(p.x + 5, p.y + 5)
    context.stroke();
    context.beginPath();
    context.moveTo(p.x + 5, p.y - 5)
    context.lineTo(p.x - 5, p.y + 5)
    context.stroke();
  }
}
function jointStrokesAt(stroke1, stroke2, x, y) {
  var jointDef = new b2RevoluteJointDef();
  jointDef.anchorPoint.Set(x, y);
  jointDef.body1 = stroke1.body;
  jointDef.body2 = stroke2 ? stroke2.body : world.GetGroundBody();
  world.CreateJoint(jointDef);
}
function strokesAt(xy) {
  var strokesAtThisPoint = $A();
  for (var i = 0; i < strokes.length; i++) {
    if (!strokes[i].body) continue;
    var test = false;
    for (var shape = strokes[i].body.GetShapeList(); shape != null; shape = shape.GetNext()) {
      if (shape.TestPoint(new b2Vec2(xy[0], xy[1]))) {
        test = true;
      }
    }
    if (test) strokesAtThisPoint.push(strokes[i]);
  }
  return strokesAtThisPoint;
}
function step(cnt) {
  cnt = cnt || 0;
  var stepping = false;
  var timeStep = 1.0/60;
  var iteration = 1;
  if (clocking && !trackingStroke) {
    world.Step(timeStep, iteration);
    $('timer').style.WebkitTransform = 'rotate(' + ((cnt * 2) % 360) + 'deg)';
    cnt++;
  }
  //ctx.clearRect(0, 0, canvasWidth, canvasHeight);
  ctx.fillStyle = backgroundStyle;
  ctx.fillRect(0, 0, canvasWidth, canvasHeight);
  drawStrokes(ctx);
  drawTracking(ctx);
  drawJoints(ctx);
  if (selectedStroke) {
    $('stroke-menu').show();
    showInformation(selectedStroke);
  }
  else {
    $('stroke-menu').hide();
  }
  if (bodyVisibility) drawWorld(world, ctx);
  setTimeout('step(' + cnt + ')', stepTime);
}
function setupCanvas() {
  //var width = window.innerWidth;
  //var height = window.innerHeight - 50; // #base
  if (canvas_zoom == 'y') {
	  var zoom_scale = (self.innerHeight - 50) / (canvas_height);	// ok.2nd
	  var width = canvas_width;					// ok.2nd
	  var height = canvas_height;					// ok.2nd
	  document.getElementById('canvas').style.zoom = zoom_scale;	// ok.2nd
  } else {
	  var width = self.innerWidth;					// ok.2nd
	  var height = self.innerHeight - 50;				// ok.2nd
  }
  var canvasElm = $('canvas');
  canvasElm.width = width;
  canvasElm.height = height;
  canvasWidth = parseInt(width);
  canvasHeight = parseInt(height);
  canvasTop = parseInt(canvasElm.style.top);
  canvasLeft = parseInt(canvasElm.style.left);

  //groundBody.SetCenterPosition(new b2Vec2(-500, height + 45), 0);
  groundBody.SetCenterPosition(new b2Vec2(-500, height + 50), 0);
}
Event.observe(window, 'resize', setupCanvas);
Event.observe(window, 'load', function() {
  setupCanvas();
  ctx = $('canvas').getContext('2d');
  function getXY(e) {
    return [Event.pointerX(e) - canvasLeft, Event.pointerY(e) - canvasTop];
  }
  function clearSelectStroke() {
    for (var i = 0; i < strokes.length; i++) {
      if (!strokes[i].body) continue;
      strokes[i].selected = false;
    }
    selectedStroke = null;
  }
  function selectStroke(xy) {
    clearSelectStroke();
    var selectedStrokes = strokesAt(xy);
    if (selectedStrokes.length != 0) {
      selectedStroke = selectedStrokes.last();
      selectedStroke.selected = true;
    }
  }
/*
  Event.observe('canvas', 'mousedown', function(e) {
    var xy = getXY(e);
    var strokesAtThisPoint = strokesAt(xy);
    if (selectedStroke && !clocking && strokesAtThisPoint.length != 0) {
      var clickSelectedStroke = false;
      for (var i = 0; i < strokesAtThisPoint.length; i++) {
        clickSelectedStroke = clickSelectedStroke || (strokesAtThisPoint[i] == selectedStroke);
      }
      if (clickSelectedStroke) {
        draggedMouseXY = xy;
        selectedStroke.draggedPoint = selectedStroke.body.GetCenterPosition().Copy();
      }
      else {
        trackingStroke = new Stroke(xy[0], xy[1]);
      }
    }
    else {
      trackingStroke = new Stroke(xy[0], xy[1]);
    }
  });
  Event.observe('canvas', 'mousemove', function(e) {
    var xy = getXY(e);
    if (selectedStroke && !clocking && draggedMouseXY) {
      if (selectedStroke.draggedPoint) {
        var selectedBody = selectedStroke.body;
        var nextPosition = selectedStroke.draggedPoint.Copy();
        nextPosition.Add(new b2Vec2(xy[0] - draggedMouseXY[0], xy[1] - draggedMouseXY[1]));
        selectedBody.SetCenterPosition(nextPosition, selectedBody.m_rotation);
      }
    }
    else if (trackingStroke) {
      trackingStroke.addPoint(xy[0], xy[1]);
    }
  });
  Event.observe('canvas', 'mouseup', function(e) {
    var xy = getXY(e);
    if (selectedStroke && !clocking && draggedMouseXY) {
      if (draggedMouseXY[0] == xy[0] && draggedMouseXY[1] == xy[1]) {
        if (usePin) {
          var strokesAtThisPoint = strokesAt(xy);
          if (0 < strokesAtThisPoint.length) {
            jointStrokesAt(strokesAtThisPoint[0], strokesAtThisPoint[1], xy[0], xy[1]);
          }
        }
        else {
          selectStroke(xy);
        }
      }
      draggedMouseXY = null;
      selectedStroke.draggedPoint = null;
    }
    else if (trackingStroke) {
      if (trackingStroke.points.length < 2) {
        trackingStroke = null;
        selectStroke(xy);
      }
      else {
        trackingStroke.addPoint(xy[0], xy[1]);
        trackingStroke.becomeBodyIn(world);
        if (trackingStroke.hasBody()) strokes.push(trackingStroke);
        trackingStroke = null;
      }
    }
  });
*/
  step();
});
