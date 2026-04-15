<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/Common/ThemeSwitcher.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps<{
  canLogin?: boolean;
  canRegister?: boolean;
  applications: Array<{ id: number; name: string; slug: string; description?: string; color?: string }>;
}>();

const scrollToApps = () => {
    const el = document.getElementById('explore-apps')
    if (el) el.scrollIntoView({ behavior: 'smooth' })
}

</script>

<template>
  <Head title="Welcome — SILVER Docs" />

  <div class="welcome-root bg-[#f8fafc] dark:bg-[#0a0a0f] text-slate-900 dark:text-white transition-colors duration-300">
    <!-- Animated background orbs -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
    <div class="bg-orb orb-3"></div>

    <!-- Noise texture overlay -->
    <div class="noise-overlay"></div>

    <div class="page-wrapper">
      <!-- ── Navbar ── -->
      <header class="navbar">
        <div class="navbar-brand">
          <ApplicationLogo size="sm" show-name />
        </div>

        <div class="navbar-actions flex items-center gap-3">
          <!-- Theme Switcher always available -->
          <ThemeSwitcher />
          
          <!-- Optional Divider -->
          <div v-if="canLogin" class="w-px h-4 bg-slate-200 dark:bg-slate-800 mx-1"></div>

          <nav v-if="canLogin">
            <Link :href="route('login')" class="nav-btn-login" id="nav-login-btn">
              Sign In
            </Link>
          </nav>
        </div>

      </header>

      <!-- ── Hero ── -->
      <main class="hero">
        <div class="hero-badge">
          <span class="badge-dot"></span>
          Documentation Management System
        </div>

        <h1 class="hero-title">
          Centralize. Manage.<br />
          <span class="hero-gradient">Document Everything.</span>
        </h1>

        <p class="hero-subtitle">
          A unified platform to create, organize, and share documentation across all your applications —
          with role-based access, version control, and real-time insights.
        </p>

        <!-- Feature Cards -->
        <div class="feature-grid">
          <div class="feature-card" id="feature-docs">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12h6M9 16h6M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Document Library</h3>
              <p>Organize and maintain rich documentation for every application and team.</p>
            </div>
          </div>

          <div class="feature-card" id="feature-access">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Role-Based Access</h3>
              <p>Granular permissions for admins and users across all documentation portals.</p>
            </div>
          </div>

          <div class="feature-card" id="feature-apps">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Multi-Application</h3>
              <p>Manage documentation for multiple applications from a single dashboard.</p>
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div class="cta-group">
          <button v-if="applications.length > 0" @click="scrollToApps" class="btn-primary" id="hero-browse-btn">
            Browse Documentation
          </button>
          <Link :href="route('login')" class="btn-secondary-custom" id="hero-login-btn">
            Login to Portal
          </Link>
        </div>

        <!-- ── Explore Applications Section ── -->
        <section v-if="applications.length > 0" id="explore-apps" class="explore-section">
          <div class="section-header">
            <h2 class="section-title text-slate-900 dark:text-white">Available <span class="hero-gradient">Documentations</span></h2>
            <p class="section-desc text-slate-500 dark:text-slate-400">Select an application below to view its technical guides and resources.</p>
          </div>

          <div class="apps-grid">
            <Link 
              v-for="app in applications" 
              :key="app.id"
              :href="route('app.show.doc', { appSlug: app.slug })"
              class="app-card bg-white dark:bg-[#111116] border border-slate-200 dark:border-slate-800 transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500 dark:hover:border-indigo-400"
            >
              <div class="app-card-header mb-5 flex items-center justify-between">
                <div class="app-brand flex items-center gap-3">
                  <div :class="['brand-dot w-2.5 h-2.5 rounded-full shadow-[0_0_10px_currentColor]', app.color || 'bg-indigo-500']"></div>
                  <h4 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ app.name }}</h4>
                </div>
                <div class="app-status text-[9px] font-extrabold tracking-wider text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-md uppercase">Active</div>
              </div>
              <p class="app-description text-sm leading-relaxed text-slate-600 dark:text-slate-400 flex-1">{{ app.description || 'Access comprehensive guides and documentation for ' + app.name + '.' }}</p>
              <div class="app-footer mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="footer-link-text text-sm font-bold text-indigo-600 dark:text-indigo-400">Read Documentation</span>
                <div class="footer-icon w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 transition-all">
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
              </div>
            </Link>
          </div>
        </section>



      </main>

      <!-- ── Footer ── -->
      <footer class="site-footer">
        <p>© {{ new Date().getFullYear() }} SILVER Docs. All rights reserved.</p>
      </footer>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

/* ── Root ── */
.welcome-root {
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* ── Background Orbs ── */
.bg-orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  z-index: 0;
}
.orb-1 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
  top: -200px; left: -200px;
  animation: floatOrb 12s ease-in-out infinite;
}

.orb-1 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
  top: -200px; left: -200px;
  animation: floatOrb 12s ease-in-out infinite;
}
.orb-2 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%);
  bottom: -100px; right: -100px;
  animation: floatOrb 16s ease-in-out infinite reverse;
}

.orb-3 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(59,130,246,0.05) 0%, transparent 70%);
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  animation: floatOrb 20s ease-in-out infinite;
}

@keyframes floatOrb {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -40px) scale(1.05); }
  66% { transform: translate(-20px, 30px) scale(0.95); }
}

/* ── Noise ── */
.noise-overlay {
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 1;
  opacity: 0.4;
}

/* ── Page Wrapper ── */
.page-wrapper {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  width: 100%;
}

/* ── Navbar ── */
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 0;
  border-bottom: none;
}



.navbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 20px rgba(99,102,241,0.4);
}

.brand-svg {
  width: 18px; height: 18px;
  color: #fff;
}

.brand-name {
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.3px;
}

.brand-accent {
  color: #818cf8;
}

.nav-btn-login {
  display: inline-flex;
  align-items: center;
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  text-decoration: none;
  transition: all 0.2s ease;
  border: 1px solid #e2e8f0;
}

:global(.dark) .nav-btn-login {
  color: #ffffff !important;
  border-color: rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.05);
}



.nav-btn-login:hover {
  background: #f8fafc;
  color: #6366f1;
  border-color: #6366f1;
}

:global(.dark) .nav-btn-login:hover {
  background: rgba(255,255,255,0.05);
  color: #fff;
  border-color: #818cf8;
}


/* ── Hero ── */
.hero {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 80px 0 60px;
  gap: 28px;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  border-radius: 100px;
  background: rgba(99,102,241,0.1);
  border: 1px solid rgba(99,102,241,0.25);
  color: #a5b4fc;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.3px;
  text-transform: uppercase;
  animation: fadeInDown 0.6s ease both;
}

.badge-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: #6366f1;
  box-shadow: 0 0 8px rgba(99,102,241,0.8);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; box-shadow: 0 0 8px rgba(99,102,241,0.8); }
  50% { opacity: 0.6; box-shadow: 0 0 4px rgba(99,102,241,0.4); }
}

.hero-title {
  font-size: clamp(40px, 6vw, 72px);
  font-weight: 900;
  color: #0f172a;
  line-height: 1.05;
  letter-spacing: -2px;
  margin: 0;
  animation: fadeInUp 0.7s ease 0.1s both;
}

.hero-title {
  font-size: clamp(40px, 6vw, 72px);
  font-weight: 900;
  color: #0f172a;
  line-height: 1.05;
  letter-spacing: -2px;
  margin: 0;
  animation: fadeInUp 0.7s ease 0.1s both;
}

.hero-gradient {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 40%, #60a5fa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  max-width: 560px;
  font-size: 16px;
  font-weight: 400;
  color: #4b5563;
  line-height: 1.7;
  margin: 0;
  animation: fadeInUp 0.7s ease 0.2s both;
}

.hero-subtitle {
  max-width: 560px;
  font-size: 16px;
  font-weight: 400;
  color: #4b5563;
  line-height: 1.7;
  margin: 0;
  animation: fadeInUp 0.7s ease 0.2s both;
}

/* ── Feature Cards ── */
.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  width: 100%;
  max-width: 900px;
  margin-top: 8px;
  animation: fadeInUp 0.7s ease 0.3s both;
}

@media (max-width: 700px) {
  .feature-grid {
    grid-template-columns: 1fr;
    max-width: 420px;
  }
}

.feature-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  text-align: left;
  transition: all 0.25s ease;
  cursor: default;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
}

.feature-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  text-align: left;
  transition: all 0.25s ease;
  cursor: default;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
}

.feature-card:hover {
  background: rgba(99,102,241,0.07);
  border-color: rgba(99,102,241,0.25);
  transform: translateY(-3px);
  box-shadow: 0 12px 40px rgba(99,102,241,0.1);
}

.feature-icon {
  width: 40px; height: 40px;
  flex-shrink: 0;
  border-radius: 10px;
  background: rgba(99,102,241,0.12);
  border: 1px solid rgba(99,102,241,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
}

.feature-card:hover .feature-icon {
  background: rgba(99,102,241,0.2);
  box-shadow: 0 0 20px rgba(99,102,241,0.2);
}

.feature-icon svg {
  width: 18px; height: 18px;
  color: #818cf8;
}

.feature-text h3 {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  margin: 0 0 6px;
  letter-spacing: -0.2px;
}

.feature-text h3 {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  margin: 0 0 6px;
  letter-spacing: -0.2px;
}

.feature-text p {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
}

/* ── CTA ── */
.cta-group {
  display: flex;
  gap: 12px;
  align-items: center;
  animation: fadeInUp 0.7s ease 0.4s both;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 32px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.25s ease;
  box-shadow: 0 8px 30px rgba(99,102,241,0.35);
  letter-spacing: -0.2px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(99,102,241,0.5);
  background: linear-gradient(135deg, #7176f8 0%, #9b6df6 100%);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-icon {
  width: 16px; height: 16px;
}

.btn-secondary-custom {
  display: inline-flex;
  align-items: center;
  padding: 12px 24px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  background: #fff;
  transition: all 0.2s ease;
}

:global(.dark) .btn-secondary-custom {
  background: transparent;
  border-color: rgba(255,255,255,0.1);
  color: #94a3b8;
}

.btn-secondary-custom:hover {
  border-color: #cbd5e1;
  color: #475569;
}

:global(.dark) .btn-secondary-custom:hover {
  border-color: rgba(255,255,255,0.2);
  color: #fff;
}

/* ── Explore Section ── */
.explore-section {
  width: 100%;
  padding: 80px 0;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.section-header {
  text-align: left;
}

.section-title {
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 8px;
}

.section-desc {
  font-size: 15px;
}

.apps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.app-card {
  border-radius: 16px;
  padding: 28px;
  display: flex;
  flex-direction: column;
  text-decoration: none;
}

.app-card-header h4 {
  margin: 0;
  letter-spacing: -0.01em;
}

.app-description {
  line-height: 1.6;
}

.app-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.app-card:hover .footer-icon {
  background: #6366f1 !important;
  color: #fff !important;
  transform: translateX(3px);
}




/* ── Footer ── */
.site-footer {
  text-align: center;
  padding: 24px 0;
  border-top: 1px solid rgba(0,0,0,0.03);
  color: #64748b;
  font-size: 12px;
  font-weight: 500;
}

:global(.dark) .site-footer {
  border-top: 1px solid rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.2);
}

/* ── Keyframes ── */
@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-16px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<style>
/* Global Dark Mode Overrides for Welcome Page */
html.dark .welcome-root .orb-1 {
  background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);
}
html.dark .welcome-root .orb-2 {
  background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 70%);
}
html.dark .welcome-root .orb-3 {
  background: radial-gradient(circle, rgba(59,130,246,0.10) 0%, transparent 70%);
}
html.dark .welcome-root .navbar {
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
html.dark .welcome-root .btn-login {
  border: 1px solid rgba(99,102,241,0.5);
  color: #a5b4fc;
  background: rgba(99,102,241,0.08);
}
html.dark .welcome-root .hero-title {
  color: #fff;
}
html.dark .welcome-root .hero-subtitle {
  color: rgba(255,255,255,0.45);
}
html.dark .welcome-root .feature-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  box-shadow: none;
  backdrop-filter: blur(10px);
}
html.dark .welcome-root .feature-text h3 {
  color: #e2e8f0;
}
html.dark .welcome-root .feature-text p {
  color: rgba(255,255,255,0.38);
}
html.dark .welcome-root .site-footer {
  border-top: 1px solid rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.2);
}
#explore-apps {
  border-top: none !important;
}
.welcome-root {
  border-top: none !important;
}

</style>
