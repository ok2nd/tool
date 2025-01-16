const fileInput = document.getElementById('fileInput');
const downloadButton = document.getElementById('downloadButton');
downloadButton.addEventListener('click', () => {
	const files = fileInput.files;
	if (files.length === 0) {
		alert('ファイルを選択してください。');
		return;
	}
	const convertedImages = [];
	const zip = new JSZip();
	for (let i = 0; i < files.length; i++) {
		const file = files[i];
		const reader = new FileReader();
		reader.onload = (e) => {
			const img = new Image();
			img.onload = () => {
				const canvas = document.createElement('canvas');
				canvas.width = img.width;
				canvas.height = img.height;
				const ctx = canvas.getContext('2d');
				ctx.drawImage(img, 0, 0);
				canvas.toBlob((blob) => {
					const newFileName = file.name.replace('.webp', '.jpg');
					zip.file(newFileName, blob, {base64: true});
					convertedImages.push(blob);
					if (convertedImages.length === files.length) {
						zip.generateAsync({type: "blob"})
							.then(function(content) {
								// ダウンロードリンクの作成
								const a = document.createElement('a');
								const url = URL.createObjectURL(content);
								a.href = url;
								a.download = 'converted_images.zip';
								a.click();
								URL.revokeObjectURL(url);
							});
					}
				}, 'image/jpeg');
			};
			img.src = e.target.result;
		};
		reader.readAsDataURL(file);
	}
});
