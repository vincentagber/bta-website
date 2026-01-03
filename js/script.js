// === Initialize AOS ===
AOS.init({ duration: 1000, once: true });

// === Smooth Scrolling for Nav Links (in-page only) ===
document.querySelectorAll('a.nav-link').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    if (href && href.startsWith('#')) {
      e.preventDefault();
      const targetId = href.substring(1);
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        window.scrollTo({
          top: targetElement.offsetTop - 70,
          behavior: 'smooth'
        });
      }
    }
  });
});

// === Counter Animation for Impact Stats ===
document.addEventListener('DOMContentLoaded', () => {
  const counters = document.querySelectorAll('.counter');
  const speed = 200;
  counters.forEach(counter => {
    const updateCount = () => {
      const target = +counter.getAttribute('data-target');
      const count = +counter.innerText;
      const increment = target / speed;
      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(updateCount, 20);
      } else {
        counter.innerText = target;
      }
    };
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        updateCount();
      }
    }, { threshold: 0.5 });
    observer.observe(counter);
  });
});

// === Quiz Functionality ===
function selectQuizOption(type) {
  const resultDiv = document.getElementById('quiz-result');
  if (type === 'visual') {
    resultDiv.innerHTML = 'You’re a visual storyteller! Explore our Documentary Filmmaking program to bring your vision to life.';
  } else {
    resultDiv.innerHTML = 'You’re an audio storyteller! Dive into our Podcasting program to share your voice with the world.';
  }
}

// === Stories Filter ===
function filterStories(category) {
  const stories = document.querySelectorAll('.story');
  const buttons = document.querySelectorAll('.btn-filter');
  buttons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  stories.forEach(story => {
    story.style.display = (category === 'all' || story.classList.contains(category)) ? 'block' : 'none';
  });
}

// === Mentorship Form ===
const mentorshipForm = document.getElementById('mentorship-form');
if (mentorshipForm) {
  mentorshipForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const interest = document.getElementById('interest')?.value || '';
    let message = 'Thank you for applying! ';
    if (interest === 'documentary') message += 'We’ll match you with a documentary filmmaking mentor.';
    else if (interest === 'podcast') message += 'We’ll match you with a podcasting mentor.';
    else message += 'We’ll match you with a broadcast journalism mentor.';
    alert(message);
    this.reset();
  });
}

// === Contact Form ===
const contactForm = document.getElementById('contact-form');
if (contactForm) {
  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Message sent successfully!');
    this.reset();
  });
}

// === Live Chat Widget ===
function toggleChat() {
  const chatBox = document.getElementById('chatBox');
  chatBox.style.display = chatBox.style.display === 'block' ? 'none' : 'block';
}

function sendMessage() {
  const chatInput = document.getElementById('chatInput');
  const chatBody = document.getElementById('chatBody');
  const message = chatInput.value.trim();
  if (message) {
    chatBody.innerHTML += `<p><strong>You:</strong> ${message}</p>`;
    setTimeout(() => {
      chatBody.innerHTML += `<p><strong>Bot:</strong> Thanks for your message! How else can I assist you?</p>`;
      chatBody.scrollTop = chatBody.scrollHeight;
    }, 1000);
    chatInput.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;
  }
}

// === Google Maps Init ===
function initMap() {
  const location = { lat: 6.5244, lng: 3.3792 };
  const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 15,
    center: location,
    styles: [
      { elementType: 'geometry', stylers: [{ color: '#1C2526' }] },
      { elementType: 'labels.text.fill', stylers: [{ color: '#F5F5F5' }] },
      { elementType: 'labels.text.stroke', stylers: [{ color: '#00B7EB' }] },
      { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#2A6A5C' }] },
      { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#B0B0B0' }] }
    ]
  });
  new google.maps.Marker({
    position: location,
    map: map,
    title: 'ABAHeadquarters',
    icon: { url: 'https://maps.google.com/mapfiles/ms/icons/gold.png' }
  });
}

// === Three.js WebGL Narrative Globe ===
if (document.getElementById('narrative-globe')) {
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
  const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('narrative-globe'), alpha: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(window.devicePixelRatio);

  const particlesGeometry = new THREE.BufferGeometry();
  const particleCount = 5000;
  const positions = new Float32Array(particleCount * 3);
  for (let i = 0; i < particleCount * 3; i++) {
    positions[i] = (Math.random() - 0.5) * 2000;
  }
  particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

  const particlesMaterial = new THREE.PointsMaterial({
    color: 0xD4A017,
    size: 2,
    transparent: true,
    opacity: 0.5,
  });
  const particles = new THREE.Points(particlesGeometry, particlesMaterial);
  scene.add(particles);
  camera.position.z = 500;

  function animate() {
    requestAnimationFrame(animate);
    particles.rotation.y += 0.001;
    renderer.render(scene, camera);
  }
  animate();

  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });
}
// Filter Stories Function
function filterStories(category, event) {
  // Update active filter button
  const buttons = document.querySelectorAll('.btn-filter');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    btn.setAttribute('aria-pressed', 'false');
  });
  event.target.classList.add('active');
  event.target.setAttribute('aria-pressed', 'true');

  // Filter stories
  const stories = document.querySelectorAll('.story');
  stories.forEach(story => {
    if (category === 'all' || story.classList.contains(category)) {
      story.style.display = 'block';
      story.classList.add('animate__animated', 'animate__fadeIn');
    } else {
      story.style.display = 'none';
      story.classList.remove('animate__animated', 'animate__fadeIn');
    }
  });
}

// Initialize Particle Effects (Assuming particles.js or similar library)
document.addEventListener('DOMContentLoaded', () => {
  // Example: Initialize particles for each story card
  document.querySelectorAll('.story-particles').forEach(canvas => {
    // Replace with actual particle library initialization, e.g., particles.js
    console.log('Initializing particles for:', canvas);
  });

  // Add animation to Explore button on scroll
  const exploreBtn = document.querySelector('.btn-explore');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 100) {
      exploreBtn.classList.add('animate__pulse');
    } else {
      exploreBtn.classList.remove('animate__pulse');
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  // Initialize AOS with mobile-friendly settings
  AOS.init({
    duration: 800,
    once: true,
    offset: 30,
    disable: window.innerWidth < 576 ? 'mobile' : false, // Disable AOS on small mobiles for performance
  });

  // Subtle Parallax Effect (Disabled on Mobile for Performance)
  const heroBackground = document.querySelector('.hero-premium-bg');
  if (window.innerWidth >= 768) {
    window.addEventListener('scroll', () => {
      const scrollPosition = window.scrollY;
      heroBackground.style.transform = `scale(${1.05 + scrollPosition / 6000})`;
    });
  }

  // Smooth Scroll for CTA
  document.querySelector('.btn-cta').addEventListener('click', (e) => {
    e.preventDefault();
    const target = document.querySelector(e.target.getAttribute('href'));
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // Touch Feedback for CTA
  const ctaButton = document.querySelector('.btn-cta');
  ctaButton.addEventListener('touchstart', () => {
    ctaButton.classList.add('active');
  });
  ctaButton.addEventListener('touchend', () => {
    ctaButton.classList.remove('active');
  });

  // Optimize Image Loading
  const heroImage = document.querySelector('.hero-premium-bg');
  heroImage.style.backgroundImage = `url('/assets/images/training.jpg')`; // Low-res initially
  const highResImage = new Image();
  highResImage.src = '/assets/images/training.jpg';
  highResImage.onload = () => {
    heroImage.style.backgroundImage = `url('/assets/images/training.jpg')`;
  };

  // Initialize Lucide Icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // Mouse-reactive Parallax for Hero Orbs
  const heroOrbs = document.querySelectorAll('.hero-orb');
  if (heroOrbs.length > 0) {
    window.addEventListener('mousemove', (e) => {
      const { clientX, clientY } = e;
      const centerX = window.innerWidth / 2;
      const centerY = window.innerHeight / 2;
      const moveX = clientX - centerX;
      const moveY = clientY - centerY;

      heroOrbs.forEach(orb => {
        const speed = orb.getAttribute('data-speed') || 0.05;
        const x = moveX * speed;
        const y = moveY * speed;
        orb.style.transform = `translate(${x}px, ${y}px)`;
      });
    });
  }
});
