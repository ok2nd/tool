document.getElementById('upload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.getElementById('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                
                // モノクロ変換
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                for (let i = 0; i < data.length; i += 4) {
                    const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    data[i] = avg;     // Red
                    data[i + 1] = avg; // Green
                    data[i + 2] = avg; // Blue
                    // Alphaはそのまま
                }
                ctx.putImageData(imageData, 0, 0);
                
                // モノクロ画像を表示
                const output = document.getElementById('output');
                output.src = canvas.toDataURL();
                output.style.display = 'block';
                
                // ダウンロードボタンの表示
                const downloadButton = document.getElementById('download');
                downloadButton.style.display = 'block';
                downloadButton.onclick = function() {
                    const link = document.createElement('a');
                    link.download = 'monochrome.png';
                    link.href = canvas.toDataURL();
                    link.click();
                };
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
