const imageUpload = document.getElementById('imageUpload');
const canvas = document.getElementById('canvas');
const downloadButton = document.getElementById('downloadButton');

imageUpload.addEventListener('change', (event) => {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);

            // グレースケール変換
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            for (let i = 0; i < data.length; i += 4) {
                const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                data[i] = avg;
                data[i + 1] = avg;
                data[i + 2] = avg;
            }
            ctx.putImageData(imageData, 0, 0);
        };
        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
});

downloadButton.addEventListener('click', () => {
    const link = document.createElement('a');
    link.download = 'grayscale.png';
    link.href = canvas.toDataURL();
    link.click();
});