<!DOCTYPE html>
<html class="dark" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>@yield('title', 'IndieDate | Your Kind of Love')</title>
  <link rel="icon" href="{{ asset('indiedate.png') }}" type="image/png" />
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
  <link rel="apple-touch-icon" href="{{ asset('indiedate.png') }}" />
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Montserrat:wght@600;700&amp;display=swap" rel="stylesheet" />
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
            "display-lg-mobile": ["36px", { lineHeight: "1.1", letterSpacing: "-0.02em", fontWeight: "700" }],
            "label-md": ["14px", { lineHeight: "1.2", letterSpacing: "0.01em", fontWeight: "500" }],
            "label-sm": ["12px", { lineHeight: "1.2", letterSpacing: "0.05em", fontWeight: "600" }],
            "headline-md": ["20px", { lineHeight: "1.4", fontWeight: "600" }],
            "body-lg": ["18px", { lineHeight: "1.6", fontWeight: "400" }],
            "headline-lg-mobile": ["24px", { lineHeight: "1.2", fontWeight: "600" }],
            "display-lg": ["48px", { lineHeight: "1.1", letterSpacing: "-0.02em", fontWeight: "700" }],
            "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
            "headline-lg": ["32px", { lineHeight: "1.2", fontWeight: "600" }]
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
    .fade-in-up {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.8s ease-out forwards;
    }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-300 { animation-delay: 300ms; }
    .delay-400 { animation-delay: 400ms; }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Scroll Reveal Animation Classes */
    .reveal {
      opacity: 0;
      transform: translateY(30px) scale(0.98);
      transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
    }
    .reveal.active {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  </style>
</head>
<body class="bg-background text-on-background min-h-screen relative overflow-x-hidden antialiased">
  
  <!-- WebGL Background -->
  <div class="fixed inset-0 z-0 pointer-events-none">
    <div class="absolute inset-0 w-full h-full opacity-60" style="display:block;">
      <canvas id="shader-canvas-ANIMATION_2" style="display:block;width:100%;height:100%"></canvas>
      <script>
        (function() {
          const canvas = document.getElementById('shader-canvas-ANIMATION_2');
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
    float wave1 = sin(uv.x * 3.0 + u_time * 0.5) * 0.5 + 0.5;
    float wave2 = sin(uv.y * 2.0 - u_time * 0.3) * 0.5 + 0.5;
    float wave3 = sin((uv.x + uv.y) * 4.0 + u_time * 0.7) * 0.5 + 0.5;
    
    vec3 color1 = vec3(0.05, 0.04, 0.1); 
    vec3 color2 = vec3(0.31, 0.27, 0.9); 
    vec3 color3 = vec3(0.98, 0.44, 0.52); 
    
    vec3 finalColor = mix(color1, color2, wave1 * 0.5);
    finalColor = mix(finalColor, color3, wave2 * 0.3);
    finalColor += wave3 * 0.05;
    
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

          let mouse = { x: canvas.width / 2, y: canvas.height / 2 };
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
    <div class="absolute inset-0 bg-gradient-to-b from-background/40 via-background/60 to-background z-10 pointer-events-none"></div>
    
    <!-- Global Decorative Ambient Elements -->
    <div class="absolute top-20 left-[10%] w-[300px] h-[300px] bg-secondary/10 rounded-full blur-[100px] animate-pulse pointer-events-none z-10"></div>
    <div class="absolute top-[40%] right-[10%] w-[400px] h-[400px] bg-primary/10 rounded-full blur-[120px] animate-pulse delay-700 pointer-events-none z-10"></div>
    <div class="fixed bottom-0 left-[30%] w-[350px] h-[350px] bg-tertiary/10 rounded-full blur-[100px] animate-pulse delay-300 pointer-events-none z-10"></div>
  </div>

  <div class="relative z-10 flex flex-col min-h-screen">
    
    <!-- TopNavBar -->
    <header id="main-header" class="fixed top-0 w-full z-50 transition-all duration-300 bg-surface/40 backdrop-blur-md border-b border-white/5 py-4">
      <div class="flex justify-between items-center px-container-padding max-w-[1200px] mx-auto w-full relative">
        <a href="{{ url('/') }}" class="flex items-center gap-2 group relative z-10">
          <img src="{{ asset('indiedate.png') }}" alt="IndieDate Logo" class="h-10 w-auto transform group-hover:scale-105 transition-transform duration-300 drop-shadow-[0_0_15px_rgba(195,192,255,0.4)]" />
          <span class="text-xl font-display-lg font-bold tracking-tight text-white hidden sm:block">IndieDate</span>
        </a>
        <nav class="hidden md:flex gap-8 items-center bg-surface-container-highest/50 px-8 py-3 rounded-full border border-white/10 backdrop-blur-xl shadow-inner relative z-10">
          <a class="font-label-md text-sm font-semibold text-on-surface-variant hover:text-white transition-colors duration-300 relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full" href="{{ url('/') }}#features">Features</a>
          <a class="font-label-md text-sm font-semibold text-on-surface-variant hover:text-white transition-colors duration-300 relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full" href="{{ url('/') }}#privacy">Privacy</a>
          <a class="font-label-md text-sm font-semibold text-on-surface-variant hover:text-white transition-colors duration-300 relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full" href="{{ url('about') }}">About</a>
        </nav>
        <button class="relative z-10 overflow-hidden group bg-surface-bright text-white font-label-md text-sm px-6 py-2.5 rounded-full font-bold border border-white/20 shadow-xl active:scale-95 transform transition-all duration-300 flex items-center gap-2">
          <span class="absolute inset-0 bg-gradient-to-r from-secondary to-primary opacity-0 group-hover:opacity-100 transition-opacity duration-500"></span>
          <span class="relative z-10">Get the App</span>
          <span class="material-symbols-outlined relative z-10 text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center justify-start pt-[120px] pb-2xl px-container-padding max-w-[1200px] mx-auto w-full gap-2xl overflow-x-hidden">
      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full py-xl border-t border-outline-variant bg-surface/80 backdrop-blur-md z-10 mt-auto">
      <div class="flex flex-col md:flex-row justify-between items-center px-container-padding max-w-[1200px] mx-auto gap-lg">
        <div class="flex flex-col items-center md:items-start gap-sm">
          <img src="{{ asset('indiedate.png') }}" alt="IndieDate Logo" class="h-8 w-auto mb-2" />
          <span class="font-label-sm text-label-sm text-on-surface-variant">© {{ date('Y') }} IndieDate. Your Kind of Love.</span>
          <span class="font-label-sm text-label-sm text-on-surface-variant mt-1">Made with ❤️ by <a href="https://codeloomtechnologies.com" target="_blank" class="text-primary hover:underline">Codeloom Technologies</a></span>
        </div>
        <nav class="flex flex-wrap justify-center gap-md">
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors duration-200" href="{{ url('about') }}">About Us</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors duration-200" href="{{ url('privacy') }}">Privacy Policy</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors duration-200" href="{{ url('terms') }}">Terms of Service</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors duration-200" href="{{ url('safety') }}">Safety Tips</a>
          <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors duration-200" href="{{ url('contact') }}">Contact Us</a>
          <a class="font-label-sm text-label-sm text-error hover:text-error/80 transition-colors duration-200" href="{{ url('delete-account') }}">Delete Account</a>
        </nav>
      </div>
    </footer>

  </div>

  <!-- Scroll Header Script -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const header = document.getElementById("main-header");
      window.addEventListener("scroll", () => {
        if (window.scrollY > 50) {
          header.classList.add("bg-surface/90", "shadow-lg", "py-2");
          header.classList.remove("bg-surface/40", "py-4");
        } else {
          header.classList.add("bg-surface/40", "py-4");
          header.classList.remove("bg-surface/90", "shadow-lg", "py-2");
        }
      });
      // ... existing scroll reveal ...
      const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
      };

      const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
            observer.unobserve(entry.target); // Optional: only animate once
          }
        });
      }, observerOptions);

      document.querySelectorAll('.reveal').forEach((el) => {
        observer.observe(el);
      });
      
      // Also apply reveal to any existing .fade-in-up elements that aren't manually styled
      document.querySelectorAll('.fade-in-up').forEach((el) => {
          // If we want to override the load animation with scroll animation, we can add 'reveal'
          el.classList.add('reveal');
          observer.observe(el);
      });
    });
  </script>
</body>
</html>
