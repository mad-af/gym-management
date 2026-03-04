<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import ApplicationLogo from '@/components/common/ApplicationLogo.vue';
import ThemeProvider from '@/components/layout/ThemeProvider.vue';
import {
  MenuIcon,
  CloseIcon,
  DatabaseIcon,
  EyeIcon,
  SuccessIcon,
  ShieldCheckIcon,
  ArrowRightIcon,
} from '@/icons';

const isMenuOpen = ref(false);

const bannerImages = [
  { thumbnail: '/images/banner/SAVE_20260211_132439.jpg.jpeg' },
  { thumbnail: '/images/banner/web_pemkab.png' },
];

const currentImageIndex = ref(0);
let carouselInterval: ReturnType<typeof setInterval> | null = null;

const startCarousel = () => {
  if (bannerImages.length <= 1) return;
  carouselInterval = setInterval(() => {
    currentImageIndex.value = (currentImageIndex.value + 1) % bannerImages.length;
  }, 3000);
};

onMounted(() => {
  startCarousel();
});

onUnmounted(() => {
  if (carouselInterval) clearInterval(carouselInterval);
});

const navLinks = [
  { name: 'Fitur', href: '#fitur' },
  { name: 'Statistik', href: '#statistik' },
];

const features = [
  {
    icon: DatabaseIcon,
    title: 'Pengelolaan Terpusat',
    description: 'Kendali penuh aset dalam satu platform tunggal yang terintegrasi.',
  },
  {
    icon: EyeIcon,
    title: 'Transparansi',
    description: 'Alur data yang terbuka dan mudah diaudit secara komprehensif.',
  },
  {
    icon: SuccessIcon,
    title: 'Akurasi Informasi',
    description: 'Data validitas tinggi untuk pengambilan keputusan yang tepat sasaran.',
  },
  {
    icon: ShieldCheckIcon,
    title: 'Good Governance',
    description: 'Mendukung tata kelola pemerintahan yang bersih dan akuntabel.',
  },
];
</script>

<template>
  <ThemeProvider>

    <Head>
      <title>SIMA - Sistem Informasi Manajemen Aset Daerah</title>
    </Head>

    <!-- Floating Banner (Fixed Bottom Right, 9:16 Ratio) -->
    <div class="fixed bottom-4 right-4 z-50 group">
      <a href="https://tanahbumbukab.go.id" target="_blank" rel="noopener noreferrer">
        <div
          class="w-80 lg:group-hover:w-xl transition-all duration-300 ease-in-out shadow-2xl rounded-xl overflow-hidden border-2 border-white dark:border-gray-700 bg-gray-900 aspect-video">
          <div class="relative w-full h-full bg-black">
            <img v-for="(image, index) in bannerImages" :key="index" :src="image.thumbnail"
              class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
              :class="{ 'opacity-100': currentImageIndex === index, 'opacity-0': currentImageIndex !== index }"
              alt="Banner" />
          </div>
        </div>
      </a>
    </div>

    <div
      class="relative font-outfit text-gray-900 dark:text-white antialiased min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">

      <!-- Rich Gradient Background -->
      <div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden">
        <!-- Base Gradient -->
        <div
          class="absolute inset-0 bg-gradient-to-br from-white via-gray-50 to-white dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">
        </div>

        <!-- Grid Pattern -->
        <div
          class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-size-[64px_64px]">
        </div>

        <!-- Gradient Orbs -->
        <!-- <div
          class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-brand-400/40 to-brand-600/40 blur-[120px] mix-blend-multiply dark:mix-blend-screen animate-pulse">
        </div> -->

        <!-- <div
          class="absolute bottom-0 left-0 translate-y-12 -translate-x-12 w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-brand-600/40 to-indigo-500/40 blur-[100px] mix-blend-multiply dark:mix-blend-screen animate-pulse">
        </div> -->

        <div
          class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full bg-gradient-to-r from-brand-300/20 via-brand-500/20 to-indigo-400/20 blur-[130px] mix-blend-multiply dark:mix-blend-screen opacity-70">
        </div>
      </div>

      <!-- Navigation -->
      <nav
        class="fixed top-0 w-full z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-brand-500/10 transition-colors duration-300">
        <div class="container-custom py-4 flex items-center justify-between">
          <ApplicationLogo />

          <!-- Desktop Menu -->
          <div class="hidden md:flex items-center gap-10">
            <a v-for="link in navLinks" :key="link.name" :href="link.href"
              class="text-sm font-semibold hover:text-brand-500 transition-colors">
              {{ link.name }}
            </a>
          </div>

          <!-- Auth Buttons -->
          <div class="hidden md:flex items-center gap-4">
            <Link href="/login">
              <button
                class="bg-brand-500 text-white text-sm font-bold px-6 py-2.5 rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-brand-500/20">
                Login
              </button>
            </Link>
          </div>

          <!-- Mobile Menu Button -->
          <div class="md:hidden flex items-center">
            <button @click="isMenuOpen = !isMenuOpen"
              class="text-gray-900 dark:text-white hover:text-brand-500 transition-colors">
              <component :is="isMenuOpen ? CloseIcon : MenuIcon" class="w-8 h-8" />
            </button>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isMenuOpen" class="md:hidden bg-gray-50 dark:bg-gray-950 border-b border-brand-500/10">
          <div class="px-6 py-4 space-y-4">
            <a v-for="link in navLinks" :key="link.name" :href="link.href"
              class="block text-base font-semibold hover:text-brand-500 transition-colors" @click="isMenuOpen = false">
              {{ link.name }}
            </a>
            <div class="pt-4 border-t border-brand-500/10 flex flex-col gap-3">
              <Link href="/login" class="w-full">
                <button
                  class="w-full bg-brand-500 text-white text-sm font-bold px-6 py-2.5 rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-brand-500/20">
                  Login
                </button>
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <!-- Hero Section -->
      <section class="section-spacing pt-48 flex flex-col items-center text-center">
        <div class="container-custom">
          <div
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 text-brand-500 text-xs font-bold tracking-wider uppercase mb-8">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-500 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
            </span>
            Transformasi Digital Aset
          </div>
          <h1 class="text-5xl md:text-7xl font-black leading-[1.1] tracking-tight mb-8 text-gray-900 dark:text-white">
            Sistem Informasi Manajemen <span class="text-brand-500">Aset Daerah</span>
          </h1>
          <p class="text-lg md:text-xl text-gray-900/70 dark:text-white/70 max-w-3xl mx-auto mb-12 leading-relaxed">
            Digitalisasi pengelolaan aset daerah yang efisien, transparan, dan akuntabel untuk masa depan tata kelola
            yang lebih baik. Platform SaaS andal untuk efisiensi birokrasi.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link href="/register">
              <button
                class="w-full sm:w-auto bg-brand-500 text-white text-lg font-bold px-10 py-4 rounded-xl hover:scale-105 transition-transform shadow-xl shadow-brand-500/20">
                Mulai Sekarang
              </button>
            </Link>
            <a href="#fitur">
              <button
                class="w-full sm:w-auto border-2 border-gray-900/10 dark:border-white/10 text-lg font-bold px-10 py-4 rounded-xl hover:bg-gray-900/5 dark:hover:bg-white/5 transition-colors">
                Pelajari Selengkapnya
              </button>
            </a>
          </div>
        </div>
      </section>

      <!-- Stats Ribbon -->
      <section class="py-20 border-y border-brand-500/10 bg-white dark:bg-gray-900/30" id="statistik">
        <div class="container-custom">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
            <div class="flex flex-col gap-2 group cursor-default">
              <p class="text-4xl font-black text-brand-500 group-hover:scale-110 transition-transform duration-300">100%
              </p>
              <p class="text-sm font-bold uppercase tracking-widest text-gray-900/50 dark:text-white/50">Transparansi
                Data</p>
            </div>
            <div class="flex flex-col gap-2 group cursor-default">
              <p class="text-4xl font-black text-brand-500 group-hover:scale-110 transition-transform duration-300">24/7
              </p>
              <p class="text-sm font-bold uppercase tracking-widest text-gray-900/50 dark:text-white/50">Akses Real-time
              </p>
            </div>
            <div class="flex flex-col gap-2 group cursor-default">
              <p class="text-4xl font-black text-brand-500 group-hover:scale-110 transition-transform duration-300">
                Terjamin</p>
              <p class="text-sm font-bold uppercase tracking-widest text-gray-900/50 dark:text-white/50">Keamanan Siber
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Core Features Grid -->
      <section class="section-spacing" id="fitur">
        <div class="container-custom text-center mb-20">
          <h2 class="text-4xl font-black tracking-tight mb-4">Core Features</h2>
          <p class="text-gray-900/60 dark:text-white/60">Solusi terpadu untuk efisiensi pengelolaan aset pemerintah
            daerah.</p>
        </div>
        <div class="container-custom">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(feature, index) in features" :key="index"
              class="p-8 rounded-2xl bg-white dark:bg-gray-900 border border-gray-900/5 dark:border-white/5 hover:border-brand-500/30 transition-all group hover:shadow-xl hover:shadow-brand-500/5">
              <div
                class="w-12 h-12 bg-brand-500/10 rounded-xl flex items-center justify-center text-brand-500 mb-6 group-hover:bg-brand-500 group-hover:text-white transition-colors duration-300">
                <component :is="feature.icon" class="w-6 h-6" />
              </div>
              <h3 class="text-lg font-bold mb-3">{{ feature.title }}</h3>
              <p class="text-sm text-gray-900/60 dark:text-white/60 leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Deep Dive Features -->
      <section class="section-spacing bg-white dark:bg-gray-900/20 overflow-hidden">
        <div class="container-custom">
          <!-- Feature 1 -->
          <div class="flex flex-col lg:flex-row items-center gap-16 mb-40">
            <div class="flex-1">
              <div class="inline-block text-brand-500 font-bold text-sm tracking-widest uppercase mb-4">Security First
              </div>
              <h2 class="text-4xl font-black mb-6 leading-tight text-gray-900 dark:text-white">Autentikasi & Hak Akses
              </h2>
              <p class="text-lg text-gray-900/60 dark:text-white/60 leading-relaxed mb-8">
                Keamanan data adalah prioritas utama kami. Dengan sistem autentikasi berlapis dan manajemen hak akses
                yang granular, setiap aset hanya dapat diakses oleh pihak yang berwenang.
              </p>
              <ul class="space-y-4">
                <li class="flex items-center gap-3">
                  <SuccessIcon class="w-6 h-6 text-brand-500" />
                  <span class="font-semibold text-gray-900 dark:text-white">Multi-level Permissions</span>
                </li>
                <li class="flex items-center gap-3">
                  <SuccessIcon class="w-6 h-6 text-brand-500" />
                  <span class="font-semibold text-gray-900 dark:text-white">Audit Logs Lengkap</span>
                </li>
              </ul>
            </div>
            <div class="flex-1 w-full lg:w-auto">
              <div
                class="relative rounded-2xl overflow-hidden border border-gray-900/5 dark:border-white/5 aspect-video bg-gray-50 dark:bg-gray-950 shadow-2xl">
                <div
                  class="absolute inset-0 bg-gradient-to-br from-brand-500/10 to-transparent pointer-events-none z-10">
                </div>
                <AppImage
                  src="https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=800&q=80"
                  alt="Security interface"
                  class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
              </div>
            </div>
          </div>

          <!-- Feature 2 (Reverse) -->
          <div class="flex flex-col lg:flex-row-reverse items-center gap-16 mb-40">
            <div class="flex-1">
              <div class="inline-block text-brand-500 font-bold text-sm tracking-widest uppercase mb-4">Data Management
              </div>
              <h2 class="text-4xl font-black mb-6 leading-tight text-gray-900 dark:text-white">Manajemen Master Data
              </h2>
              <p class="text-lg text-gray-900/60 dark:text-white/60 leading-relaxed mb-8">
                Organisir seluruh aset daerah dalam satu pusat data yang terstruktur. Dari gedung hingga peralatan
                kecil, semua terdata secara mendetail dengan standar penamaan internasional.
              </p>
              <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-brand-500/5 border border-brand-500/10">
                  <p class="text-xl font-bold text-brand-500">Single Source</p>
                  <p class="text-xs font-medium opacity-60 text-gray-900 dark:text-white">Truth for all assets</p>
                </div>
                <div class="p-4 rounded-xl bg-brand-500/5 border border-brand-500/10">
                  <p class="text-xl font-bold text-brand-500">QR Ready</p>
                  <p class="text-xs font-medium opacity-60 text-gray-900 dark:text-white">Instant Identification</p>
                </div>
              </div>
            </div>
            <div class="flex-1 w-full lg:w-auto">
              <div
                class="relative rounded-2xl overflow-hidden border border-gray-900/5 dark:border-white/5 aspect-video bg-gray-50 dark:bg-gray-950 shadow-2xl">
                <div
                  class="absolute inset-0 bg-gradient-to-tl from-brand-500/10 to-transparent pointer-events-none z-10">
                </div>
                <AppImage
                  src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80"
                  alt="Asset inventory management"
                  class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
              </div>
            </div>
          </div>

          <!-- Feature 3 -->
          <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="flex-1">
              <div class="inline-block text-brand-500 font-bold text-sm tracking-widest uppercase mb-4">Real-time
                Insight</div>
              <h2 class="text-4xl font-black mb-6 leading-tight text-gray-900 dark:text-white">Laporan & Monitoring</h2>
              <p class="text-lg text-gray-900/60 dark:text-white/60 leading-relaxed mb-8">
                Pantau pergerakan dan nilai aset secara real-time melalui dashboard interaktif. Hasilkan laporan
                otomatis untuk kebutuhan audit tanpa perlu proses manual yang melelahkan.
              </p>
              <Link href="/register">
                <button class="flex items-center gap-2 font-bold text-brand-500 group hover:underline">
                  Lihat Contoh Dashboard
                  <ArrowRightIcon class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </button>
              </Link>
            </div>
            <div class="flex-1 w-full lg:w-auto">
              <div
                class="relative rounded-2xl overflow-hidden border border-gray-900/5 dark:border-white/5 aspect-video bg-gray-50 dark:bg-gray-950 shadow-2xl">
                <div
                  class="absolute inset-0 bg-gradient-to-br from-brand-500/10 to-transparent pointer-events-none z-10">
                </div>
                <AppImage
                  src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80"
                  alt="Analytics dashboard"
                  class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA Footer Section -->
      <section class="container-custom py-24 mb-12">
        <div class="bg-gray-900 rounded-[2rem] p-12 md:p-20 text-center relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/20 rounded-full blur-[120px] -mr-32 -mt-32"></div>
          <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-500/20 rounded-full blur-[120px] -ml-32 -mb-32"></div>
          <div class="relative z-10">
            <h2 class="text-white text-4xl md:text-5xl font-black mb-6 leading-tight">Siap mendigitalisasi aset Anda?
            </h2>
            <p class="text-white/60 text-lg mb-10 max-w-2xl mx-auto">Mulai hari ini dan rasakan kemudahan pengelolaan
              aset daerah yang transparan dan efisien.</p>
            <Link href="/register">
              <button
                class="bg-brand-500 text-white text-lg font-bold px-12 py-5 rounded-2xl hover:scale-105 transition-transform shadow-2xl shadow-brand-500/30">
                Dapatkan Demo Sekarang
              </button>
            </Link>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="bg-white dark:bg-gray-900 py-20 border-t border-gray-900/5 dark:border-white/5 mt-auto">
        <div class="container-custom">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <div class="col-span-1 md:col-span-1">
              <ApplicationLogo class="mb-6" />
              <p class="text-sm text-gray-900/50 dark:text-white/50 leading-relaxed">
                Solusi tata kelola aset daerah terpercaya untuk pemerintahan yang lebih modern.
              </p>
            </div>
            <div>
              <h4 class="font-bold mb-6 text-gray-900 dark:text-white">Platform</h4>
              <ul class="space-y-4 text-sm text-gray-900/50 dark:text-white/50">
                <li><a class="hover:text-brand-500 transition-colors" href="#fitur">Fitur Utama</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#statistik">Statistik</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">API Docs</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-6 text-gray-900 dark:text-white">Bantuan</h4>
              <ul class="space-y-4 text-sm text-gray-900/50 dark:text-white/50">
                <li><a class="hover:text-brand-500 transition-colors" href="#">Pusat Bantuan</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">Kontak Kami</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">Panduan Pengguna</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">Status Sistem</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-6 text-gray-900 dark:text-white">Legal</h4>
              <ul class="space-y-4 text-sm text-gray-900/50 dark:text-white/50">
                <li><a class="hover:text-brand-500 transition-colors" href="#">Kebijakan Privasi</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">Syarat & Ketentuan</a></li>
                <li><a class="hover:text-brand-500 transition-colors" href="#">Kepatuhan Audit</a></li>
              </ul>
            </div>
          </div>
          <div
            class="pt-8 border-t border-gray-900/5 dark:border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-gray-900/40 dark:text-white/40">
            <p>&copy; {{ new Date().getFullYear() }} SIMA Platform. All rights reserved.</p>
            <div class="flex gap-6">
              <a class="hover:text-brand-500 transition-colors" href="#">Twitter</a>
              <a class="hover:text-brand-500 transition-colors" href="#">LinkedIn</a>
              <a class="hover:text-brand-500 transition-colors" href="#">GitHub</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </ThemeProvider>
</template>

<style>
.section-spacing {
  padding-top: 150px;
  padding-bottom: 150px;
}

.container-custom {
  max-width: 1440px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 15%;
  padding-right: 15%;
}

@media (max-width: 768px) {
  .container-custom {
    padding-left: 5%;
    padding-right: 5%;
  }

  .section-spacing {
    padding-top: 80px;
    padding-bottom: 80px;
  }
}
</style>
