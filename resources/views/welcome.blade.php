<!DOCTYPE html>

<html class="dark" lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>IndieDate | Your Kind of Love</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Montserrat:wght@600;700&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "secondary-fixed": "#ffdadc",
            "surface-tint": "#c3c0ff",
            "on-primary-fixed-variant": "#3323cc",
            "inverse-primary": "#4d44e3",
            "on-error-container": "#ffdad6",
            "outline-variant": "#464555",
            "on-primary-fixed": "#0f0069",
            "on-secondary": "#67001f",
            "error-container": "#93000a",
            "on-secondary-container": "#ff97a3",
            "on-surface-variant": "#c7c4d8",
            "on-tertiary-container": "#ffd4a4",
            "tertiary": "#ffb95f",
            "surface-container-high": "#2a2933",
            "error": "#ffb4ab",
            "surface-container-highest": "#35343e",
            "on-surface": "#e4e1ee",
            "inverse-surface": "#e4e1ee",
            "on-secondary-fixed-variant": "#891933",
            "secondary-container": "#891933",
            "tertiary-fixed": "#ffddb8",
            "primary": "#c3c0ff",
            "on-primary": "#1d00a5",
            "primary-container": "#4f46e5",
            "surface-dim": "#13121b",
            "surface-container-lowest": "#0e0d16",
            "on-tertiary": "#472a00",
            "primary-fixed-dim": "#c3c0ff",
            "on-secondary-fixed": "#400010",
            "on-tertiary-fixed-variant": "#653e00",
            "outline": "#918fa1",
            "secondary-fixed-dim": "#ffb2b9",
            "surface-container-low": "#1b1b24",
            "primary-fixed": "#e2dfff",
            "background": "#13121b",
            "secondary": "#ffb2b9",
            "tertiary-fixed-dim": "#ffb95f",
            "surface": "#13121b",
            "inverse-on-surface": "#302f39",
            "tertiary-container": "#885500",
            "on-error": "#690005",
            "surface-container": "#1f1f28",
            "surface-bright": "#393842",
            "on-tertiary-fixed": "#2a1700",
            "on-background": "#e4e1ee",
            "on-primary-container": "#dad7ff",
            "surface-variant": "#35343e"
          },
          borderRadius: {
            DEFAULT: "0.25rem",
            lg: "0.5rem",
            xl: "0.75rem",
            full: "9999px"
          },
          spacing: {
            "container-padding": "20px",
            "xs": "4px",
            "base": "4px",
            "stack-gap": "12px",
            "sm": "8px",
            "2xl": "48px",
            "xl": "32px",
            "md": "16px",
            "lg": "24px"
          },
          fontFamily: {
            "display-lg-mobile": ["Montserrat"],
            "label-md": ["Inter"],
            "label-sm": ["Inter"],
            "headline-md": ["Montserrat"],
            "body-lg": ["Inter"],
            "headline-lg-mobile": ["Montserrat"],
            "display-lg": ["Montserrat"],
            "body-md": ["Inter"],
            "headline-lg": ["Montserrat"]
          },
          fontSize: {
            "display-lg-mobile": ["36px", {
              lineHeight: "1.1",
              letterSpacing: "-0.02em",
              fontWeight: "700"
            }],
            "label-md": ["14px", {
              lineHeight: "1.2",
              letterSpacing: "0.01em",
              fontWeight: "500"
            }],
            "label-sm": ["12px", {
              lineHeight: "1.2",
              letterSpacing: "0.05em",
              fontWeight: "600"
            }],
            "headline-md": ["20px", {
              lineHeight: "1.4",
              fontWeight: "600"
            }],
            "body-lg": ["18px", {
              lineHeight: "1.6",
              fontWeight: "400"
            }],
            "headline-lg-mobile": ["24px", {
              lineHeight: "1.2",
              fontWeight: "600"
            }],
            "display-lg": ["48px", {
              lineHeight: "1.1",
              letterSpacing: "-0.02em",
              fontWeight: "700"
            }],
            "body-md": ["16px", {
              lineHeight: "1.6",
              fontWeight: "400"
            }],
            "headline-lg": ["32px", {
              lineHeight: "1.2",
              fontWeight: "600"
            }]
          }
        }
      }
    }
  </script>
  <style>
    .glass-panel {
      background: rgba(19, 18, 27, 0.6);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .glass-panel-active {
      background: rgba(19, 18, 27, 0.8);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .neon-glow-rose {
      box-shadow: 0 0 20px rgba(255, 178, 185, 0.3);
    }

    .neon-text-gradient {
      background: linear-gradient(to right, #ffb2b9, #ffb95f);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    /* Entrance Animations */
    .fade-in-up {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.8s ease-out forwards;
    }

    .delay-100 {
      animation-delay: 100ms;
    }

    .delay-200 {
      animation-delay: 200ms;
    }

    .delay-300 {
      animation-delay: 300ms;
    }

    .delay-400 {
      animation-delay: 400ms;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body class="bg-background text-on-background min-h-screen relative overflow-x-hidden antialiased">
  <!-- WebGL Background -->
  <div class="fixed inset-0 z-0 pointer-events-none">
    <!-- STITCH_SHADER_START:ANIMATION_2 class="absolute inset-0 w-full h-full opacity-60" -->
    <div class="absolute inset-0 w-full h-full opacity-60" style="display:block;">
      <canvas id="shader-canvas-ANIMATION_2" style="display:block;width:100%;height:100%"></canvas>
      <script>
        (function() {
          const canvas = document.getElementById('shader-canvas-ANIMATION_2');

          // Sync the WebGL drawing-buffer size with the CSS-driven layout size.
          // This fires on initial layout and whenever the element is resized.
          function syncSize() {
            const w = canvas.clientWidth || 1280;
            const h = canvas.clientHeight || 720;
            if (canvas.width !== w || canvas.height !== h) {
              canvas.width = w;
              canvas.height = h;
            }
          }
          if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(syncSize).observe(canvas);
          }
          syncSize();

          const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
          if (!gl) return;
          const vs = `attribute vec2 a_position;
varying vec2 v_texCoord;
void main() {
  v_texCoord = a_position * 0.5 + 0.5;
  gl_Position = vec4(a_position, 0.0, 1.0);
}`;
          const fs = `precision highp float;
uniform float u_time;
uniform vec2 u_resolution;
varying vec2 v_texCoord;

void main() {
    vec2 uv = v_texCoord;
    
    // Create organic, flowing motion
    float wave1 = sin(uv.x * 3.0 + u_time * 0.5) * 0.5 + 0.5;
    float wave2 = sin(uv.y * 2.0 - u_time * 0.3) * 0.5 + 0.5;
    float wave3 = sin((uv.x + uv.y) * 4.0 + u_time * 0.7) * 0.5 + 0.5;
    
    // Blend colors from the IndieDate palette (Indigo, Rose, Midnight)
    vec3 color1 = vec3(0.05, 0.04, 0.1); // Deep Midnight
    vec3 color2 = vec3(0.31, 0.27, 0.9); // Indigo
    vec3 color3 = vec3(0.98, 0.44, 0.52); // Rose
    
    vec3 finalColor = mix(color1, color2, wave1 * 0.5);
    finalColor = mix(finalColor, color3, wave2 * 0.3);
    finalColor += wave3 * 0.05; // Add a subtle glow
    
    gl_FragColor = vec4(finalColor, 1.0);
}`;

          function cs(type, src) {
            const s = gl.createShader(type);
            gl.shaderSource(s, src);
            gl.compileShader(s);
            return s;
          }
          const prog = gl.createProgram();
          gl.attachShader(prog, cs(gl.VERTEX_SHADER, vs));
          gl.attachShader(prog, cs(gl.FRAGMENT_SHADER, fs));
          gl.linkProgram(prog);
          gl.useProgram(prog);
          const buf = gl.createBuffer();
          gl.bindBuffer(gl.ARRAY_BUFFER, buf);
          gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
          const pos = gl.getAttribLocation(prog, 'a_position');
          gl.enableVertexAttribArray(pos);
          gl.vertexAttribPointer(pos, 2, gl.FLOAT, false, 0, 0);
          const uTime = gl.getUniformLocation(prog, 'u_time');
          const uRes = gl.getUniformLocation(prog, 'u_resolution');
          const uMouse = gl.getUniformLocation(prog, 'u_mouse');

          // u_mouse is in pixel coordinates matching u_resolution (ShaderToy convention).
          // Shaders that need normalized coords should use: u_mouse / u_resolution.
          let mouse = {
            x: canvas.width / 2,
            y: canvas.height / 2
          };
          window.addEventListener('mousemove', (event) => {
            const rect = canvas.getBoundingClientRect();
            if (rect.width && rect.height) {
              const nx = (event.clientX - rect.left) / rect.width;
              const ny = 1.0 - (event.clientY - rect.top) / rect.height;
              mouse.x = nx * canvas.width;
              mouse.y = ny * canvas.height;
            }
          });

          function render(t) {
            if (typeof ResizeObserver === 'undefined') syncSize();
            gl.viewport(0, 0, canvas.width, canvas.height);
            if (uTime) gl.uniform1f(uTime, t * 0.001);
            if (uRes) gl.uniform2f(uRes, canvas.width, canvas.height);
            if (uMouse) gl.uniform2f(uMouse, mouse.x, mouse.y);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
            requestAnimationFrame(render);
          }
          render(0);
        })();
      </script>
    </div>
    <!-- STITCH_SHADER_END:ANIMATION_2 -->
    <div class="absolute inset-0 bg-gradient-to-b from-background/40 via-background/60 to-background z-10 pointer-events-none"></div>
  </div>
  <div class="relative z-10 flex flex-col min-h-screen">
    <!-- TopNavBar -->
    <header class="fixed top-0 w-full z-50 bg-surface/60 backdrop-blur-xl border-b border-white/10 shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)]">
      <div class="flex justify-between items-center px-container-padding py-md max-w-[1200px] mx-auto w-full">
        <a href="#">
          <img src="{{ asset('indiedate.png') }}" alt="IndieDate Logo" class="h-10 w-auto" />
        </a>
        <nav class="hidden md:flex gap-lg items-center">
          <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors hover:bg-white/10 rounded-lg px-md py-sm transition-all duration-300" href="#">Discover</a>
          <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors hover:bg-white/10 rounded-lg px-md py-sm transition-all duration-300" href="#">Matches</a>
          <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors hover:bg-white/10 rounded-lg px-md py-sm transition-all duration-300" href="#">Premium</a>
          <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors hover:bg-white/10 rounded-lg px-md py-sm transition-all duration-300" href="#">About</a>
        </nav>
        <button class="bg-gradient-to-r from-secondary to-tertiary text-on-secondary font-label-md text-label-md px-lg py-sm rounded-full font-bold shadow-lg hover:opacity-90 active:scale-95 transform transition-all duration-300 neon-glow-rose">Get Started</button>
      </div>
    </header>
    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center justify-center pt-[100px] pb-2xl px-container-padding max-w-[1200px] mx-auto w-full gap-2xl">
      <!-- Hero Section -->
      <section class="flex flex-col items-center text-center py-2xl lg:py-[80px] w-full max-w-3xl fade-in-up">
        <div class="inline-flex items-center gap-xs px-md py-xs rounded-full glass-panel mb-lg">
          <span class="material-symbols-outlined text-secondary text-[16px]">favorite</span>
          <span class="font-label-sm text-label-sm text-on-surface">Premium Dating Experience</span>
        </div>
        <h1 class="font-display-lg-mobile lg:font-display-lg text-display-lg-mobile lg:text-display-lg text-on-surface mb-md">
          Find your rhythm.<br />
          <span class="neon-text-gradient">Discover your match.</span>
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-xl max-w-2xl mx-auto">
          Dive into a world of electric romanticism. Connect with genuine people in an immersive, beautifully designed space tailored for meaningful interactions.
        </p>
        <div class="flex flex-col sm:flex-row gap-md">
          <button class="bg-gradient-to-r from-secondary to-tertiary text-on-secondary font-headline-md text-headline-md px-xl py-md rounded-full font-bold shadow-lg hover:opacity-90 active:scale-95 transform transition-all duration-300 neon-glow-rose">
            Join Now
          </button>
          <button class="glass-panel text-on-surface font-headline-md text-headline-md px-xl py-md rounded-full font-semibold hover:bg-white/10 active:scale-95 transform transition-all duration-300">
            Explore Features
          </button>
        </div>
      </section>
      <!-- Image Grid Sub-Hero -->
      <section class="w-full grid grid-cols-1 md:grid-cols-3 gap-md fade-in-up delay-200">
        <div class="glass-panel rounded-xl overflow-hidden aspect-[4/5] relative group">
          <img class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500" data-alt="A candid, beautifully lit portrait of a stylish young adult in a moody, neon-lit urban nightlife setting, capturing a sense of modern romance and electric energy. The color palette features deep blacks, rich purples, and vibrant rose accents." src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3jyI-otf3Ofh-nWOz1E6gd7W4qmjA2n9DgyjsR-r0A_2ANodV_l0dXU7wxn70jEaGY1_eb3WlJDpY7M9YlznlSfX4btrhqp2XQzEdfVCLRjeFh51_1FTN_7UgvFwIxw4BnqWC85CUzHERuqkOyNjJVBUnGVyBIDUkmvViz-3uud7JWDXxaU8A8S7b3TCOzW4QVOiUcXbanpZAYyYrgTYV1Bzwu-jBXUTpiTsARKphbwkFR47YwF8xjZoNaPzCLK0JQZgfU5Hmn4eM" />
          <div class="absolute bottom-0 left-0 w-full p-md bg-gradient-to-t from-background to-transparent">
            <p class="font-headline-md text-headline-md text-on-surface">Alex, 26</p>
            <p class="font-label-md text-label-md text-on-surface-variant flex items-center gap-xs"><span class="material-symbols-outlined text-[14px]">location_on</span> New York</p>
          </div>
        </div>
        <div class="glass-panel rounded-xl overflow-hidden aspect-[4/5] relative group mt-0 md:mt-xl">
          <img class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500" data-alt="A close-up portrait of an elegant person laughing in a softly lit, high-end lounge setting. Warm ambient light highlights their features, evoking intimacy and sophisticated charm. The background is slightly blurred to emphasize depth and focus on the connection." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDkv7CYjUCAPm8aq_S6HKF1HVcPA3ZbPAQktdHVgrNWvvodJ0xAQkMpaTnf5wZHshgrwpuf1SK9jJ27JcVA9oeTcbnF9woJFjqDyhwZmlrRtzWQjFqzk3NKqWq8BpgIgoKz8DoryIhrKiliiuXziXLjHk70O_fqyEN_seVOedI1B9LAaUIHAtMHw7iCoXyeQyEL7cIAegFv67XRoog_DDzzQe_yQG0Uq8S4s6Fz0xfJ1gsN9NVvpM06Uxg21lsVRvCw-jS4AKP5_G2L" />
          <div class="absolute bottom-0 left-0 w-full p-md bg-gradient-to-t from-background to-transparent">
            <p class="font-headline-md text-headline-md text-on-surface">Sam, 29</p>
            <p class="font-label-md text-label-md text-on-surface-variant flex items-center gap-xs"><span class="material-symbols-outlined text-[14px]">location_on</span> London</p>
          </div>
        </div>
        <div class="glass-panel rounded-xl overflow-hidden aspect-[4/5] relative group">
          <img class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500" data-alt="A cinematic shot of two people sharing a meaningful glance across a table in an upscale, dimly lit restaurant with vibrant neon accents reflecting off glass surfaces. The scene captures the tension and excitement of a modern romantic date." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAWLrUcbBzom8jqwjwNNs9dwknuyQrCTWJ2PjEpbyoF5fNVnMG_QglTI8PUE-PxOAhGEecaTeReTGKEMm28_geGnxPB7I-iGbiEwH_qWq_1D7LyURztaVMf7DqZwezvDv1iBQ_TtcdFF5nlfIhmvi0cpU5NF8IbuNoQWNvWtkqvfODq63t3qSCyofksmlcOllj-Nkox-PRWoqpsP-72rDt-Nb6m5EJIBsArCIZyCEUKiP9657fRPNeNrOQy4dpB8Oyx70ZxOJjU-bCx" />
          <div class="absolute bottom-0 left-0 w-full p-md bg-gradient-to-t from-background to-transparent">
            <p class="font-headline-md text-headline-md text-on-surface">Jordan, 24</p>
            <p class="font-label-md text-label-md text-on-surface-variant flex items-center gap-xs"><span class="material-symbols-outlined text-[14px]">location_on</span> Tokyo</p>
          </div>
        </div>
      </section>
      <!-- Features Bento -->
      <section class="w-full py-2xl fade-in-up delay-300">
        <div class="text-center mb-xl">
          <h2 class="font-headline-lg-mobile lg:font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface mb-sm">Why IndieDate?</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Designed for depth, built for connection.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
          <!-- Feature 1 -->
          <div class="glass-panel-active rounded-xl p-lg flex flex-col items-start gap-md group hover:-translate-y-1 transition-transform duration-300">
            <div class="w-12 h-12 rounded-full bg-secondary/20 flex items-center justify-center text-secondary mb-sm group-hover:neon-glow-rose transition-shadow duration-300">
              <span class="material-symbols-outlined text-[24px]">favorite</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Meaningful Connections</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Move beyond the superficial. Our platform encourages deeper conversations and genuine interactions.</p>
          </div>
          <!-- Feature 2 -->
          <div class="glass-panel-active rounded-xl p-lg flex flex-col items-start gap-md group hover:-translate-y-1 transition-transform duration-300">
            <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-primary mb-sm">
              <span class="material-symbols-outlined text-[24px]">verified_user</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Verified Profiles</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Trust is our foundation. Every profile is rigorously verified to ensure a safe, authentic dating environment.</p>
          </div>
          <!-- Feature 3 -->
          <div class="glass-panel-active rounded-xl p-lg flex flex-col items-start gap-md group hover:-translate-y-1 transition-transform duration-300">
            <div class="w-12 h-12 rounded-full bg-tertiary/20 flex items-center justify-center text-tertiary mb-sm">
              <span class="material-symbols-outlined text-[24px]">psychology</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Smart Matching</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Our intelligent algorithm learns your preferences to suggest highly compatible matches tailored just for you.</p>
          </div>
        </div>
      </section>
    </main>
    <!-- Footer -->
    <footer class="w-full py-xl border-t border-outline-variant bg-surface-container-lowest z-10">
      <div class="flex flex-col md:flex-row justify-between items-center px-container-padding max-w-[1200px] mx-auto gap-lg">
        <div class="flex flex-col items-center md:items-start gap-sm">
          <img src="{{ asset('indiedate.png') }}" alt="IndieDate Logo" class="h-8 w-auto mb-2" />
          <span class="font-label-sm text-label-sm text-on-surface-variant">© 2024 IndieDate. Your Kind of Love</span>
        </div>
        <nav class="flex flex-wrap justify-center gap-md">
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-tertiary transition-colors duration-200" href="#">Privacy Policy</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-tertiary transition-colors duration-200" href="#">Terms of Service</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-tertiary transition-colors duration-200" href="#">Safety Tips</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-tertiary transition-colors duration-200" href="#">Contact Us</a>
        </nav>
      </div>
    </footer>
  </div>
  <script>
    // Simple script to handle entrance animations based on scroll could be added here
    // For now, they run on load via CSS animations.
  </script>
</body>

</html>