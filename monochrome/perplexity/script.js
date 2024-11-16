const imageInput = document.getElementById('imageInput');
const originalImage = document.getElementById('originalImage');
const monochromeCanvas = document.getElementById('monochromeCanvas');
const convertButton = document.getElementById('convertButton');
const downloadButton = document.getElementById('downloadButton');
const downloadLink = document.getElementById('downloadLink');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            originalImage.src = event.target.result;
            originalImage.onload = function() {
                convertButton.disabled = false;
            }
        }
        reader.readAsDataURL(file);
    }
});

convertButton.addEventListener('click', function() {
    const ctx = monochromeCanvas.getContext('2d');
    monochromeCanvas.width = originalImage.width;
    monochromeCanvas.height = originalImage.height;
    
    ctx.drawImage(originalImage, 0, 0);
    const imageData = ctx.getImageData(0, 0, monochromeCanvas.width, monochromeCanvas.height);
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
        const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
        data[i] = data[i + 1] = data[i + 2] = avg;
    }
    
    ctx.putImageData(imageData, 0, 0);
    downloadButton.disabled = false;
});

downloadButton.addEventListener('click', function() {
    const dataURL = monochromeCanvas.toDataURL('image/png');
    downloadLink.href = dataURL;
});