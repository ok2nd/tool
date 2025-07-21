$(function() {
  var ctx = $('#Result')[0].getContext('2d'),
      btn = $('#Button'),
      info = $('#Info');

  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);

  Cavin.get($('#Image')[0].src, function(img) {
    btn.click(function() {
      try {
        var radius = parseInt($('#Radius').val()),
            blurSize = parseInt($('#BlurSize').val()),
            fish, smooth, dst, type, offset,
            start, time;

        start = new Date().getTime();
        fish = Fisheye.transform(img, 55, radius),
        type = parseInt($('input[name=view]:checked').val());
        if(type === 1) {
          smooth = Cavin.Filter.blur(img, blurSize);
          offset = 8;
          dst = montage(fish, smooth, radius - offset, offset);
        } else {
          var len = img.width*img.height*4,
              data = fish.data;
          for(var i=0; i<len; i+=4) {
            data[i + 3] = 255;
          }
          dst = fish;
        }
        time = (new Date().getTime() - start).toString();
        info.text('process time: ' + time + ' ms');
        Cavin.put(dst, ctx);
      } catch(e) {
        info.text(e.message);
      }
    });

    function montage(fisheye, back, radius, bandWidth) {
      var w = fisheye.width,
          h = fisheye.height,
          fishdata = fisheye.data,
          backdata = back.data,
          dstimg = ctx.getImageData(0, 0, w, h),
          dstdata = dstimg.data,
          len = w*h << 2,
          x2 = w >> 1,
          y2 = h >> 1,
          buff = [0, 0, 0],
          div = 1/9,
          i, j, k, step, kstep, r,
          sqrt = Math.sqrt;

      // invert
      for(i=0, j=(h*w<<2) - 4; i<len; i+=4, j-=4) {
        dstdata[i] = backdata[j];
        dstdata[i + 1] = backdata[j + 1];
        dstdata[i + 2] = backdata[j + 2];
        dstdata[i + 3] = 255;
      }
      // montage
      for(i=0; i<len; i+=4) {
        if(fishdata[i + 3] === 1) {
          dstdata[i] = fishdata[i];
          dstdata[i + 1] = fishdata[i + 1];
          dstdata[i + 2] = fishdata[i + 2];
        }
      }
      // bandary blur
      for(var y=-y2; y<y2; y++) {
        step = x2 + (y + y2)*w;
        for(var x=-x2; x<x2; x++) {
          i = (step + x) << 2;
          r = sqrt(x*x + y*y);
          buff[0] = buff[1] = buff[2] = 0;
          if(r > radius && r < (radius + bandWidth)) {
            for(var ky=-1; ky<=1; ky++) {
              kstep = ky*w;
              for(var kx=-1; kx<=1; kx++) {
                j = i + ((kstep + kx) << 2);
                buff[0] += dstdata[j];
                buff[1] += dstdata[j + 1];
                buff[2] += dstdata[j + 2];
              }
            }
            dstdata[i] = buff[0]*div;
            dstdata[i + 1] = buff[1]*div;
            dstdata[i + 2] = buff[2]*div;
            //dstdata[i + 3] = 128;
          }
        }
      }
      return dstimg;
    }
  });
});
