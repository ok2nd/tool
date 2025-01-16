document.getElementById('upload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            
            for (let i = 0; i < data.length; i += 4) {
                const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                data[i] = avg;
                data[i + 1] = avg;
                data[i + 2] = avg;
            }
            
            ctx.putImageData(imageData, 0, 0);
            canvas.style.display = 'block';
        }
        img.src = e.target.result;
    }
    reader.readAsDataURL(file);
});

document.getElementById('download').addEventListener('click', function() {
    const canvas = document.getElementById('canvas');
    const link = document.createElement('a');
    link.href = canvas.toDataURL();
    link.download = 'monochrome_image.png';
    link.click();
});
