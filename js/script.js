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
    title: 'BTA Headquarters',
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
