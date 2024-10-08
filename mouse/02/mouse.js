const mouse = document.getElementById('mouse');
let prevX = 0;
let prevY = 0;
let delay = 100; // 遅延時間(ms)
function setMouseImage(direction) {
	mouse.src = `mouse_${direction}.jpg`;
}
document.addEventListener('mousemove', (event) => {
	const currentX = event.clientX;
	const currentY = event.clientY;
	// 方向判定
	let direction = 'right';
	if (currentX < prevX) {
		direction = 'left';
	} else if (currentY < prevY) {
		direction = 'up';
	} else if (currentY > prevY) {
		direction = 'down';
	}
	setTimeout(() => {
		setMouseImage(direction);
		mouse.style.left = currentX - 150 + 'px';
		mouse.style.top = currentY - 150 + 'px';
	}, delay);
	prevX = currentX;
	prevY = currentY;
});
