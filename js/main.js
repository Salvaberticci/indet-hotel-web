document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 50,
    });

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Three.js starfield animation
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.getElementById('three-canvas').appendChild(renderer.domElement);

    const particles = new THREE.BufferGeometry();
    const particleCount = 7000;
    const positions = new Float32Array(particleCount * 3);
    for (let i = 0; i < particleCount * 3; i++) {
        positions[i] = (Math.random() - 0.5) * 15;
    }
    particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const createStarTexture = () => {
        const canvas = document.createElement('canvas');
        canvas.width = 64;
        canvas.height = 64;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = 'white';
        ctx.beginPath();
        ctx.moveTo(32, 2); ctx.lineTo(42, 24); ctx.lineTo(64, 24); ctx.lineTo(48, 40);
        ctx.lineTo(54, 62); ctx.lineTo(32, 50); ctx.lineTo(10, 62); ctx.lineTo(16, 40);
        ctx.lineTo(0, 24); ctx.lineTo(22, 24);
        ctx.closePath();
        ctx.fill();
        return new THREE.CanvasTexture(canvas);
    };

    const particleMaterial = new THREE.PointsMaterial({
        map: createStarTexture(),
        size: 0.1,
        transparent: true,
        blending: THREE.AdditiveBlending,
        color: 0xffffff, // White color
        depthWrite: false,
        opacity: 0.5,
    });
    const particleSystem = new THREE.Points(particles, particleMaterial);
    scene.add(particleSystem);
    camera.position.z = 5;

    // Mouse move effect
    document.addEventListener('mousemove', (e) => {
        const mouseX = (e.clientX / window.innerWidth) * 2 - 1;
        const mouseY = -(e.clientY / window.innerHeight) * 2 + 1;
        particleSystem.rotation.x = mouseY * 0.1;
        particleSystem.rotation.y = mouseX * 0.1;
    });

    const animate = () => {
        requestAnimationFrame(animate);
        particleSystem.rotation.z += 0.0005;
        renderer.render(scene, camera);
    };
    animate();

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
});
