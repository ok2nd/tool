const mouse = document.getElementById('mouse');

document.addEventListener('mousemove', (event) => {
	mouse.style.left = event.clientX - 25 + 'px';
	mouse.style.top = event.clientY - 25 + 'px';
});
