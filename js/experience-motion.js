/**
 * Experience in Motion - Light Luxury Ambient Three.js Background
 * Africa Broadcasting Academy | Editorial Light Theme
 * 
 * Aesthetic:
 * - Refined, shimmering pearl & gold broadcast wave frequencies
 * - Crisp gold (#C59D5F) and teal (#26C6DA) wave nodes on luminous light canvas
 * - Smooth 60fps organic wave simulation with gentle mouse parallax
 * - Zero-layout shift, automatic viewport throttling via IntersectionObserver
 */

(function initLightCinematicMotion() {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('threeMotionCanvas');
    const container = document.getElementById('video-showcase-gallery');

    if (!canvas || !container) return;

    // WebGL Capability Check
    function hasWebGL() {
      try {
        const test = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && 
          (test.getContext('webgl') || test.getContext('experimental-webgl')));
      } catch (e) {
        return false;
      }
    }

    if (!hasWebGL() || typeof THREE === 'undefined') {
      return;
    }

    try {
      // --- Scene & Perspective Camera Setup ---
      const scene = new THREE.Scene();

      let width = container.clientWidth || window.innerWidth;
      let height = container.clientHeight || 800;

      const camera = new THREE.PerspectiveCamera(50, width / height, 1, 1000);
      camera.position.set(0, 24, 80);
      camera.lookAt(0, -2, 0);

      const renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance'
      });
      renderer.setSize(width, height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

      // --- High Contrast Particle Sprite for Light Background ---
      function generateLightParticleTexture() {
        const pCanvas = document.createElement('canvas');
        pCanvas.width = 64;
        pCanvas.height = 64;
        const ctx = pCanvas.getContext('2d');

        const grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        grad.addColorStop(0, 'rgba(197, 157, 95, 1)');
        grad.addColorStop(0.35, 'rgba(38, 198, 218, 0.85)');
        grad.addColorStop(0.7, 'rgba(0, 151, 167, 0.25)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');

        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 64, 64);

        const tex = new THREE.Texture(pCanvas);
        tex.needsUpdate = true;
        return tex;
      }

      const particleTexture = generateLightParticleTexture();

      // --- 1. Primary Broadcast Wave Grid ---
      const cols = 90;
      const rows = 50;
      const spacingX = 2.4;
      const spacingZ = 2.4;
      const particleCount = cols * rows;

      const positions = new Float32Array(particleCount * 3);
      const colors = new Float32Array(particleCount * 3);

      const colorGold = new THREE.Color(0xC59D5F);
      const colorTeal = new THREE.Color(0x26C6DA);
      const colorDeepCyan = new THREE.Color(0x00838F);

      let pIndex = 0;
      for (let ix = 0; ix < cols; ix++) {
        for (let iz = 0; iz < rows; iz++) {
          const posX = (ix - cols / 2) * spacingX;
          const posZ = (iz - rows / 2) * spacingZ;
          const posY = 0;

          positions[pIndex * 3] = posX;
          positions[pIndex * 3 + 1] = posY;
          positions[pIndex * 3 + 2] = posZ;

          // Harmonic color distribution
          const distFromCenter = Math.sqrt(posX * posX + posZ * posZ) / 90;
          const blendFactor = (Math.sin(ix * 0.09) + Math.cos(iz * 0.09) + 2) / 4;
          const pColor = new THREE.Color();

          if (blendFactor < 0.5) {
            pColor.lerpColors(colorGold, colorTeal, blendFactor * 2);
          } else {
            pColor.lerpColors(colorTeal, colorDeepCyan, (blendFactor - 0.5) * 2);
          }

          // Gentle edge fading
          const edgeFade = Math.max(0.3, 1 - distFromCenter * 0.5);
          pColor.multiplyScalar(edgeFade);

          colors[pIndex * 3] = pColor.r;
          colors[pIndex * 3 + 1] = pColor.g;
          colors[pIndex * 3 + 2] = pColor.b;

          pIndex++;
        }
      }

      const waveGeometry = new THREE.BufferGeometry();
      waveGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
      waveGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

      const waveMaterial = new THREE.PointsMaterial({
        size: 2.2,
        vertexColors: true,
        transparent: true,
        opacity: 0.55,
        map: particleTexture,
        depthWrite: false
      });

      const waveMesh = new THREE.Points(waveGeometry, waveMaterial);
      waveMesh.position.y = -6;
      scene.add(waveMesh);

      // --- 2. Floating Storytelling Droplets / Embers ---
      const emberCount = 100;
      const emberPositions = new Float32Array(emberCount * 3);
      const emberSpeeds = new Float32Array(emberCount);

      for (let i = 0; i < emberCount; i++) {
        emberPositions[i * 3] = (Math.random() - 0.5) * 160;
        emberPositions[i * 3 + 1] = (Math.random() - 0.3) * 50;
        emberPositions[i * 3 + 2] = (Math.random() - 0.5) * 100;
        emberSpeeds[i] = 0.015 + Math.random() * 0.03;
      }

      const emberGeometry = new THREE.BufferGeometry();
      emberGeometry.setAttribute('position', new THREE.BufferAttribute(emberPositions, 3));

      const emberMaterial = new THREE.PointsMaterial({
        size: 2.6,
        color: 0xC59D5F,
        transparent: true,
        opacity: 0.45,
        map: particleTexture,
        depthWrite: false
      });

      const emberField = new THREE.Points(emberGeometry, emberMaterial);
      scene.add(emberField);

      // --- Interactive Mouse Parallax with Damping ---
      let mouseX = 0;
      let mouseY = 0;
      let currentX = 0;
      let currentY = 0;

      window.addEventListener('mousemove', (e) => {
        const rect = container.getBoundingClientRect();
        if (e.clientY >= rect.top - 300 && e.clientY <= rect.bottom + 300) {
          mouseX = (e.clientX / window.innerWidth - 0.5) * 12;
          mouseY = (e.clientY / window.innerHeight - 0.5) * 6;
        }
      }, { passive: true });

      // --- Resize Handling ---
      function onResize() {
        if (!container || !renderer || !camera) return;
        width = container.clientWidth;
        height = container.clientHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
      }

      window.addEventListener('resize', onResize, { passive: true });
      if (window.ResizeObserver) {
        new ResizeObserver(onResize).observe(container);
      }

      // --- Animation Loop ---
      let isVisible = true;
      let animationId = null;
      let clock = 0;

      function renderFrame() {
        if (!isVisible) {
          animationId = null;
          return;
        }

        clock += 0.012;

        // Damped camera follow
        currentX += (mouseX - currentX) * 0.04;
        currentY += (mouseY - currentY) * 0.04;

        camera.position.x = currentX;
        camera.position.y = 24 - currentY;
        camera.lookAt(0, -2, 0);

        // Fluid undulating wave calculation
        const posAttribute = waveGeometry.attributes.position;
        const posArr = posAttribute.array;

        let ptr = 0;
        for (let ix = 0; ix < cols; ix++) {
          for (let iz = 0; iz < rows; iz++) {
            const waveA = Math.sin((ix * 0.11) + (iz * 0.08) + clock) * 3.5;
            const waveB = Math.cos((iz * 0.13) - (ix * 0.05) + clock * 0.7) * 3.0;
            const waveC = Math.sin((ix * 0.04) - clock * 0.4) * 1.8;

            posArr[ptr * 3 + 1] = waveA + waveB + waveC;
            ptr++;
          }
        }
        posAttribute.needsUpdate = true;

        // Animate floating embers drifting upwards
        const emberAttr = emberGeometry.attributes.position;
        const emberArr = emberAttr.array;

        for (let i = 0; i < emberCount; i++) {
          emberArr[i * 3 + 1] += emberSpeeds[i];
          if (emberArr[i * 3 + 1] > 40) {
            emberArr[i * 3 + 1] = -20;
            emberArr[i * 3] = (Math.random() - 0.5) * 160;
          }
        }
        emberAttr.needsUpdate = true;

        renderer.render(scene, camera);
        animationId = requestAnimationFrame(renderFrame);
      }

      // --- Viewport Throttling ---
      const visibilityObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          isVisible = entry.isIntersecting;
          if (isVisible && !animationId) {
            renderFrame();
          }
        });
      }, { threshold: 0.05 });

      visibilityObserver.observe(container);
      renderFrame();

    } catch (err) {
      console.warn('Light Motion initialization notice:', err);
    }
  });
})();
