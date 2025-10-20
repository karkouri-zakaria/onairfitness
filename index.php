<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="./dist/styles.css" rel="stylesheet">
		<title>On Air Fitness</title>
		<link rel="icon" type="image/png" href="./assets/images/logo.png">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Saira:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&display=swap" rel="stylesheet">
		<style>
			/* Animated gradient background */
			body {
			background-image: url('https://t3.ftcdn.net/jpg/09/56/94/66/240_F_956946677_weVJRUpNcRQcydJCh4h10zzNhq3dvUtb.jpg');
			background-size: cover;
			background-position: center;
			background-repeat: no-repeat;
			background-attachment: fixed; /* Keeps it stable on scroll if needed */
			}
			.result-animate {
			animation: resultFadeIn 0.8s cubic-bezier(0.23, 1, 0.32, 1);
			}
			#spin, #spin:disabled {
			cursor: pointer !important;
			}
			#spin:active, #spin.clicked {
			animation: spinClick 0.18s cubic-bezier(0.4,0,0.2,1);
			}
			@keyframes spinClick {
			0% { transform: scale(1); }
			50% { transform: scale(0.92); }
			100% { transform: scale(1); }
			}
			@keyframes resultFadeIn {
			0% {
			opacity: 0;
			transform: scale(0.8);
			}
			60% {
			opacity: 1;
			transform: scale(1.05);
			}
			100% {
			opacity: 1;
			transform: scale(1);
			}
			}
		</style>
	</head>
	<body class="bg-black text-white flex flex-col items-center justify-center h-screen font-saira min-h-screen overflow-hidden">
		<!-- Gradient overlay -->
		<div class="fixed inset-0 pointer-events-none z-0 opacity-60 bg-black text-center"></div>
		<div id="survey-screen" class="flex flex-col items-center justify-center h-screen z-20 bg-black bg-opacity-80 absolute inset-0">
			<img src="assets/images/logo.png" alt="On Air Fitness Logo" class="w-48 md:w-64 mb-8">
			<p class="text-2xl md:text-3xl font-orbitron font-bold mb-6 text-white drop-shadow-lg tracking-wide text-center">Avant de participer,<br>merci de remplir le sondage !</p>
			<a href="https://g.page/r/CVu3ttLuVipNEAI/review" target="_blank" class="m-6 font-orbitron text-center text-xl min-w-72 py-2 font-semibold bg-white text-espresso rounded-full hover:bg-gray-200 transition-colors z-50">Remplir le sondage</a>
			<button id="survey-done" class="text-xl py-2 font-orbitron min-w-72 bg-white text-black font-semibold rounded-full hover:bg-gray-200 transition-colors z-50">J'ai rempli le sondage</button>
		</div>
		<div id="game-screen" style="display:none;" class="flex flex-col items-center justify-center w-full h-full">
			<img src="assets/images/logo.png" alt="On Air Fitness Logo" class="w-48 md:w-64 z-10">
			<p class="text-2xl md:text-4xl font-orbitron font-bold my-8 text-white drop-shadow-lg tracking-wide z-30 text-center">
				Tournez la roue pour gagner
			</p>
			<div id="wheel-container" class="relative w-80 h-80 md:w-1/2 md:h-1/2 z-10">
				<svg id="wheel" class="absolute w-full h-full" viewBox="0 0 100 100">
					<!-- Thick black border -->
					<circle cx="50" cy="50" r="48" stroke="white" stroke-width="2" fill="none" stroke-dasharray="1" />
				</svg>
				<div id="indicator-container" class="absolute inset-0" style="transform-origin: 50% 50%;">
					<div id="indicator">
						<img src="assets/images/indicator.svg" class="absolute top-3 left-1/2 transform -translate-x-1/2 w-4 xl:w-6 glow"></img>
					</div>
				</div>
			</div>
			<button id="spin" class="mt-14 text-3xl min-w-72 py-1 font-orbitron bg-white text-espresso font-semibold rounded-full hover:bg-gray-200 transition-colors z-10 cursor-pointer">Lancer La Roue</button>
			<div id="result" class="mt-4 text-xl font-semibold text-center z-10"></div>
		</div>
		<script>
			const prizes = [
			  { name: "Gourde / Serviette", prob: 0.05 },
			  { name: "Café / Bouteille d’eau", prob: 0.20 },
			  { name: "Tshirt / On Air", prob: 0.04 },
			  { name: "1 mois / offert", prob: 0.01 },
			  { name: "Protein / drink", prob: 0.10 },
			  { name: "Oups!", prob: 0.60 },
			];
			const colors = ['#87221d', '#bf0007', '#5c1b18', '#fc002f'];
			const numSlices = prizes.length;
			const sliceAngle = 360 / numSlices;
			const svgNS = "http://www.w3.org/2000/svg";
			const centerX = 50, centerY = 50, radius = 48;
			
			let currentRotation = 0;
			let spinning = false;
			let animationFrameId = null;
			let lastTimestamp = null;
			
			function createSector(cx, cy, r, startAngle, endAngle, color) {
			  const x1 = cx + r * Math.cos(startAngle * Math.PI / 180);
			  const y1 = cy + r * Math.sin(startAngle * Math.PI / 180);
			  const x2 = cx + r * Math.cos(endAngle * Math.PI / 180);
			  const y2 = cy + r * Math.sin(endAngle * Math.PI / 180);
			  const largeArc = (endAngle - startAngle) > 180 ? 1 : 0;
			  const path = document.createElementNS(svgNS, 'path');
			  path.setAttribute('d', `M${cx},${cy} L${x1},${y1} A${r},${r} 0 ${largeArc},1 ${x2},${y2} Z`);
			  path.setAttribute('fill', color);
			  return path;
			}
			
			function createText(cx, cy, r, angle, textContent) {
			  const text = document.createElementNS(svgNS, 'text');
			  const rad = angle * Math.PI / 180;
			  const x = cx + (r * 0.7) * Math.cos(rad);
			  const y = cy + (r * 0.7) * Math.sin(rad);
			  text.setAttribute('x', x);
			  text.setAttribute('y', y);
			  text.setAttribute('text-anchor', 'middle');
			  text.setAttribute('fill', 'white');
			  text.setAttribute('font-size', '4.7');
			  const parts = textContent.split('/');
			  const lineHeight = 1.1;
			  let dy = - (parts.length - 1) * lineHeight / 2;
			  for (let part of parts) {
			    const tspan = document.createElementNS(svgNS, 'tspan');
			    tspan.setAttribute('x', x);
			    tspan.setAttribute('dy', `${dy}em`);
			    tspan.textContent = part.trim();
			    text.appendChild(tspan);
			    dy = lineHeight;
			  }
			  return text;
			}
			
			// Initialize wheel
			const svg = document.getElementById('wheel');
			const wheelGroup = document.createElementNS(svgNS, 'g');
			wheelGroup.id = 'wheel-group';
			svg.appendChild(wheelGroup);
			wheelGroup.style.transformOrigin = '50% 50%';
			let startAngle = 0;
			for (let i = 0; i < numSlices; i++) {
			  const endAngle = startAngle + sliceAngle;
			  const color = colors[i % colors.length];
			  const sector = createSector(centerX, centerY, radius, startAngle, endAngle, color);
			  wheelGroup.appendChild(sector);
			  const centerAngle = startAngle + sliceAngle / 2;
			  const text = createText(centerX, centerY, radius, centerAngle, prizes[i].name);
			  wheelGroup.appendChild(text);
			  startAngle = endAngle;
			}
			
			function getRandomPrize() {
			  const rand = Math.random();
			  let cumulative = 0;
			  for (let i = 0; i < prizes.length; i++) {
			    cumulative += prizes[i].prob;
			    if (rand < cumulative) {
			      return i;
			    }
			  }
			  return prizes.length - 1;
			}
			
			function continuousSpin(timestamp) {
			  if (!lastTimestamp) lastTimestamp = timestamp;
			  const deltaTime = (timestamp - lastTimestamp) / 1000; // Time in seconds
			  const rotationSpeed = 720; // Degrees per second, increased for testing
			  currentRotation += rotationSpeed * deltaTime;
			  wheelGroup.style.transform = `rotate(${-currentRotation}deg)`;
			  lastTimestamp = timestamp;
			  if (spinning) {
			    animationFrameId = requestAnimationFrame(continuousSpin);
			  }
			}
			
			const spinButton = document.getElementById('spin');
			const resultDiv = document.getElementById('result');
			const indicatorContainer = document.getElementById('indicator-container');
			
			spinButton.addEventListener('click', () => {
			  // Add click animation
			  spinButton.classList.add('clicked');
			  setTimeout(() => spinButton.classList.remove('clicked'), 180);
			
			  if (!spinning) {
			    // Start continuous spinning
			    spinning = true;
			    spinButton.textContent = 'Stop';
			    resultDiv.textContent = '';
			    resultDiv.classList.remove('result-animate');
			    lastTimestamp = null;
			    animationFrameId = requestAnimationFrame(continuousSpin);
			  } else {
			    // Stop spinning and transition to target
			    cancelAnimationFrame(animationFrameId);
			    spinning = false;
			    spinButton.textContent = 'Lancer La Roue';
			    const winnerIndex = getRandomPrize();
			    let centerAngle = (sliceAngle / 2) + (sliceAngle * winnerIndex);
			    centerAngle += (Math.random() - 0.5) * sliceAngle;
			    let finalDeg = ((centerAngle - 270) % 360 + 360) % 360;
			    const fullRotations = 3 + Math.floor(Math.random() * 3);
			    const additionalFull = 360 * fullRotations;
			    const currentRotMod = currentRotation % 360;
			    const additionalToTarget = (finalDeg - currentRotMod + 360) % 360;
			    const totalAdditional = additionalFull + additionalToTarget;
			    const totalRotation = currentRotation + totalAdditional;
			    const rotationSpeed = 400; // Degrees per second, matches continuous spin
			    const duration = Math.max(totalAdditional / rotationSpeed, 1); // Minimum 3 seconds
			    wheelGroup.style.transition = `transform ${duration}s ease-out`;
			    wheelGroup.style.transform = `rotate(${-totalRotation}deg)`;
			    wheelGroup.addEventListener('transitionend', () => {
			      currentRotation = totalRotation % 360;
			      wheelGroup.style.transition = 'transform 0s';
			      wheelGroup.style.transform = `rotate(${-currentRotation}deg)`;
			      if (prizes[winnerIndex].name === "Oups!") {
			        resultDiv.textContent = `🙁 Oups! Pas de chance !`;
			      } else {
			        resultDiv.textContent = `🎉 Vous avez gagné : ${prizes[winnerIndex].name}`;
			      }
			      resultDiv.classList.add('result-animate');
			      setTimeout(() => {
			        resultDiv.classList.remove('result-animate');
			      }, 900);
			    }, { once: true });
			  }
			});

			// Survey done logic
			const surveyScreen = document.getElementById('survey-screen');
			const gameScreen = document.getElementById('game-screen');
			const surveyDoneButton = document.getElementById('survey-done');

			surveyDoneButton.addEventListener('click', () => {
			  surveyScreen.style.display = 'none';
			  gameScreen.style.display = 'flex';
			});
		</script>
	</body>
</html>