<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Login from './Login.vue';
import Register from './Register.vue';
 
// Define props for modal states
const props = defineProps({
  showLoginModal: {
    type: Boolean,
    default: false
  },
  showRegisterModal: {
    type: Boolean,
    default: false
  }
});

const activeSection = ref('home');

// Modal states
const showLoginModal = ref(props.showLoginModal);
const showRegisterModal = ref(props.showRegisterModal);

// Header state
const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

// Login form
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');

// Navigate to login page with modal
const navigateToLogin = () => {
    router.visit('/login', {
        preserveState: true,
        preserveScroll: true
    });
};

// Navigate to register page with modal
const navigateToRegister = () => {
    router.visit('/register', {
        preserveState: true,
        preserveScroll: true
    });
};

// Navigate to specific register type
const navigateToRegisterType = (type) => {
    router.visit(`/register/${type}`, {
        preserveState: true,
        preserveScroll: true
    });
};

// Close modals
const closeLoginModal = () => {
    showLoginModal.value = false;
    router.visit('/', {
        preserveState: true,
        preserveScroll: true
    });
};

const closeRegisterModal = () => {
    showRegisterModal.value = false;
    router.visit('/', {
        preserveState: true,
        preserveScroll: true
    });
};

// Watch for prop changes
watch(() => props.showLoginModal, (newVal) => {
    showLoginModal.value = newVal;
});

watch(() => props.showRegisterModal, (newVal) => {
    showRegisterModal.value = newVal;
});

// Show toast notification
const showToastNotification = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 2000);
};

// Toggle mobile menu
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Smooth scroll function for navigation links
const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
    // Close mobile menu after navigation
    isMobileMenuOpen.value = false;
  }
};

// IntersectionObserver for better performance
let observer = null;

// Set active section based on IntersectionObserver
const setupIntersectionObserver = () => {
    const sections = document.querySelectorAll('section[id], div[id="home"]');
    
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
                    activeSection.value = entry.target.id;
                }
            });
        },
        {
            threshold: [0.5],
            rootMargin: '-50px 0px -50px 0px'
        }
    );
    
    sections.forEach((section) => {
        observer.observe(section);
    });
};

// Handle scroll for header background
const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    // Setup scroll listener for header
    window.addEventListener('scroll', handleScroll);
    
    // Setup intersection observer for active sections
    setupIntersectionObserver();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (observer) {
        observer.disconnect();
    }
});
</script>

<style scoped>
/* Modern gradient backgrounds */
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* Floating animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes float-reverse {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(20px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
    animation: float-reverse 4s ease-in-out infinite;
}

/* Pulse glow effect */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(79, 172, 254, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(79, 172, 254, 0.6);
    }
}

.animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}

/* Morphing shapes */
@keyframes morph {
    0%, 100% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    50% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
}

.animate-morph {
    animation: morph 8s ease-in-out infinite;
}

/* Colorful hover effects */
.hover-rainbow:hover {
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57, #ff9ff3);
    background-size: 400% 400%;
    animation: rainbow 2s ease infinite;
}

@keyframes rainbow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Glass morphism effect */
.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Neon glow text */
.neon-text {
    text-shadow: 0 0 5px currentColor, 0 0 10px currentColor, 0 0 15px currentColor;
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .animate-float,
    .animate-float-reverse {
        animation-duration: 3s;
    }
}

/* Scroll reveal animations */
.reveal {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.6s ease-out;
}

.reveal.active {
    opacity: 1;
    transform: translateY(0);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}
</style>

<template>
    <Head>
        <title>Pathway - Connecting Filipino Talent Worldwide</title>
        <meta name="description" content="Join Pathway to showcase your skills, connect with employers, and advance your career. The premier platform for Filipino professionals seeking global opportunities.">
        <meta property="og:title" content="Pathway - Connecting Filipino Talent Worldwide">
        <meta property="og:description" content="Join Pathway to showcase your skills, connect with employers, and advance your career.">
        <meta property="og:image" content="/images/pathway-og-image.jpg">
        <meta property="og:url" content="https://pathwayph.com">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="preload" href="/fonts/inter.woff2" as="font" type="font/woff2" crossorigin>
    </Head>

    <div class="relative min-h-screen gradient-bg overflow-hidden">
        <!-- Floating Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-10 w-64 h-64 gradient-card rounded-full opacity-20 animate-float"></div>
            <div class="absolute top-40 right-20 w-48 h-48 gradient-feature rounded-full opacity-30 animate-float-reverse"></div>
            <div class="absolute bottom-20 left-1/4 w-72 h-72 gradient-cta rounded-full opacity-15 animate-morph"></div>
            <div class="absolute top-1/3 right-1/3 w-32 h-32 bg-white rounded-full opacity-10 animate-pulse-glow"></div>
            <div class="absolute bottom-1/3 right-10 w-40 h-40 gradient-card rounded-full opacity-25 animate-float"></div>
        </div>
        
        <!-- Landing Section -->
        <div id="home" class="relative z-10">
            
            <!-- Modern Header -->
            <header 
                role="banner"
                class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" 
                :class="{
                    'glass shadow-lg py-3': isScrolled, 
                    'bg-transparent py-4': !isScrolled
                }"
            >
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex items-center justify-between">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <Link href="/" class="flex items-center space-x-3 focus:outline-none focus:ring-2 focus:ring-cyan-400 rounded">
                                <div class="w-10 h-10 gradient-card rounded-xl flex items-center justify-center animate-pulse-glow">
                                    <span class="text-white font-bold text-xl neon-text">P</span>
                                </div>
                                <span :class="[
                                    'font-extrabold text-xl leading-none select-none transition-colors duration-200 neon-text',
                                    isScrolled ? 'text-cyan-300' : 'text-white'
                                ]">Pathway</span>
                            </Link>
                        </div>

                        <!-- Desktop Navigation -->
                        <nav class="hidden md:flex space-x-8" role="navigation">
                            <a 
                                href="#home" 
                                @click.prevent="scrollToSection('home')"
                                :aria-current="activeSection === 'home' ? 'page' : null"
                                :class="[
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'home' 
                                        ? (isScrolled ? 'text-cyan-300 neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-cyan-600' : 'text-white neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-white')
                                        : isScrolled 
                                            ? 'text-gray-700 hover:text-cyan-300 hover:neon-text' 
                                            : 'text-white/90 hover:text-white hover:neon-text']">
                                Home
                            </a>
                            <a 
                                href="#about" 
                                @click.prevent="scrollToSection('about')"
                                :aria-current="activeSection === 'about' ? 'page' : null"
                                :class="[
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'about' 
                                        ? (isScrolled ? 'text-cyan-300 neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-cyan-600' : 'text-white neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-white')
                                        : isScrolled 
                                            ? 'text-gray-700 hover:text-cyan-300 hover:neon-text' 
                                            : 'text-white/90 hover:text-white hover:neon-text']">
                                About
                            </a>
                            <a 
                                href="#features" 
                                @click.prevent="scrollToSection('features')"
                                :aria-current="activeSection === 'features' ? 'page' : null"
                                :class="[
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'features' 
                                        ? (isScrolled ? 'text-cyan-300 neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-cyan-600' : 'text-white neon-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-white')
                                        : isScrolled 
                                            ? 'text-gray-700 hover:text-cyan-300 hover:neon-text' 
                                            : 'text-white/90 hover:text-white hover:neon-text']">
                                Features
                            </a>
                        </nav>

                        <!-- Desktop Auth Buttons -->
                        <div class="hidden md:flex items-center space-x-4">
                            <button 
                                @click="navigateToLogin"
                                :class="[
                                    'px-5 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-cyan-400',
                                    isScrolled 
                                        ? 'glass text-cyan-300 hover:text-cyan-700 hover:neon-text' 
                                        : 'glass text-white hover:text-white hover:neon-text']
                                ">
                                Sign In
                            </button>
                            <button 
                                @click="navigateToRegister"
                                class="px-6 py-2.5 gradient-cta text-white text-sm font-semibold rounded-xl hover-rainbow transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 shadow-lg animate-pulse-glow">
                                Sign up
                            </button>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="md:hidden">
                            <button 
                                @click="toggleMobileMenu"
                                :aria-expanded="isMobileMenuOpen"
                                aria-label="Toggle navigation menu"
                                :class="[
                                    'p-2 rounded-xl glass transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-cyan-400',
                                    isScrolled ? 'text-cyan-300 hover:neon-text' : 'text-white hover:neon-text'
                                ]"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path 
                                        v-if="!isMobileMenuOpen"
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        d="M4 6h16M4 12h16M4 18h16" 
                                    />
                                    <path 
                                        v-else
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        d="M6 18L18 6M6 6l12 12" 
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>
            <main class="min-h-screen flex flex-col items-center justify-center text-center px-4 z-20">
               <!-- Hero Title with Neon Effects -->
                <h1 class="text-white font-extrabold text-6xl md:text-8xl leading-tight mb-4 neon-text animate-float">
                    Pathway
                </h1>

                <!-- Tagline -->
                <h3 class="text-white font-bold text-3xl md:text-5xl leading-tight mb-8 animate-fade-in">
                    Connect. Grow. <span class="text-cyan-300 neon-text">Succeed.</span>
                </h3>

                <!-- Colorful Tagline -->
                <p class="text-white/90 text-xl md:text-2xl max-w-3xl mb-12 font-light animate-float-reverse">
                    The premier platform connecting <span class="text-pink-300 neon-text">Filipino talent</span> with <span class="text-green-300 neon-text">global opportunities</span>.
                </p>

                
                <!-- Primary and Secondary CTAs -->
                <div class="flex flex-col sm:flex-row gap-6 mb-12">
                    <button 
                        @click="navigateToRegister"
                        class="px-10 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-rainbow transform hover:scale-110 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400 focus:ring-offset-2 shadow-2xl animate-pulse-glow">
                        Join the Path
                        <svg class="inline-block ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                    <button 
                        @click="scrollToSection('about')"
                        class="px-8 py-4 glass text-white text-lg font-semibold rounded-2xl hover:neon-text transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-white/50">
                        Learn More
                    </button>
                </div>
                
                <!-- Colorful Scroll Indicator -->
                <div class="mt-auto mb-8 cursor-pointer animate-float" 
                     @click="scrollToSection('about')">
                    <div class="flex flex-col items-center space-y-2 text-white/80 hover:text-white hover:neon-text transition-all duration-300">
                        <span class="text-sm font-semibold">Discover More</span>
                        <div class="w-8 h-8 gradient-feature rounded-full flex items-center justify-center animate-pulse-glow">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </main>
        </div>



        <!-- About Section -->
        <section id="about" class="min-h-screen flex items-center py-20 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                        Connecting Talent with <span class="text-blue-600">Opportunity</span>
                    </h2>
                    <p class="text-lg md:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                        Pathway bridges the gap between educational institutions, fresh graduates, and industry needs,
                        creating a seamless ecosystem for growth and opportunity across the Philippines and beyond.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Graduates Card -->
                    <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 ease-in-out overflow-hidden border border-gray-100">
                        <div class="relative overflow-hidden rounded-xl mb-6">
                            <img src="/images/gradplanning.jpg" alt="Graduate planning career path and future opportunities" 
                                class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110" 
                                loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="flex items-center mb-6">
                            <div class="bg-blue-100 p-3 rounded-full mr-4 group-hover:bg-blue-200 transition-colors duration-300">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">Graduates</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            Showcase your skills, build your professional network, and discover
                            career opportunities tailored to your qualifications and aspirations.
                        </p>
                    </div>
                    
                    <!-- Institutions Card -->
                    <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 ease-in-out overflow-hidden border border-gray-100">
                        <div class="relative overflow-hidden rounded-xl mb-6">
                            <img src="/images/tracking.jpg" alt="University campus and educational institution" 
                                class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110" 
                                loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="flex items-center mb-6">
                            <div class="bg-blue-100 p-3 rounded-full mr-4 group-hover:bg-blue-200 transition-colors duration-300">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                                    <path d="M5 8a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zM5 11a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">Institutions</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            Track graduate outcomes, strengthen industry partnerships,
                            and enhance your educational offerings based on real market needs.
                        </p>
                    </div>
                    
                    <!-- Industry Card -->
                    <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 ease-in-out overflow-hidden border border-gray-100 md:col-span-2 lg:col-span-1">
                        <div class="relative overflow-hidden rounded-xl mb-6">
                            <img src="/images/industry.jpg" alt="Industry professionals collaborating in modern workplace" 
                                class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110" 
                                loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="flex items-center mb-6">
                            <div class="bg-blue-100 p-3 rounded-full mr-4 group-hover:bg-blue-200 transition-colors duration-300">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">Industry</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            Find qualified talent, collaborate with educational
                            institutions, and contribute to shaping the future workforce of the Philippines.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="min-h-screen flex items-center py-20 bg-white">
            <div class="container mx-auto px-8 w-full">
                <h2 class="text-4xl md:text-5xl font-bold text-center mb-6">Why Choose Pathway?</h2>
                <p class="text-center text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mb-16">
                    Our platform offers dedicated features for each user group, creating a complete ecosystem for
                    career development and talent acquisition.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-12">
                    <!-- Graduate Profiles -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-user-graduate text-4xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold mb-3">Graduate Profiles</h3>
                            <p class="text-lg text-gray-600 mb-4">
                                Create comprehensive profiles showcasing your education, skills, and career aspirations.
                                Connect with your alma mater and potential employers.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Customizable skill showcases</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Verified credentials from institutions</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Direct connections to industry opportunities</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Institution Dashboard -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-university text-4xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold mb-3">Institution Dashboard</h3>
                            <p class="text-lg text-gray-600 mb-4">
                                Monitor graduate outcomes, connect with industry partners, and showcase your
                                institution's unique strengths and programs.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Graduate tracking and analytics</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Industry collaboration tools</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Program outcome visualization</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Industry Portal -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-building text-4xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold mb-3">Industry Portal</h3>
                            <p class="text-lg text-gray-600 mb-4">
                                Access qualified talent pools, communicate with educational institutions, and streamline
                                your recruitment process.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Customized talent searches</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Opportunity posting and management</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Unified Ecosystem -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-handshake text-4xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold mb-3">Unified Ecosystem</h3>
                            <p class="text-lg text-gray-600 mb-4">
                                Our platform brings together all stakeholders in education and employment, creating a
                                seamless pathway from education to career.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Shared data insights</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span class="text-base">Collaborative workforce development</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Login Modal -->
        <div v-if="showLoginModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeLoginModal"></div>
                
                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="closeLoginModal">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <Login @close="closeLoginModal" />
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div v-if="showRegisterModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeRegisterModal"></div>
                
                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4 z-10">
                        <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="closeRegisterModal">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <Register @close="closeRegisterModal" />
                </div>
            </div>
        </div>
    </div>
</template>

<!-- <style scoped>


.animate-bounce {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.transition-colors {
  transition: background-color 0.3s, color 0.3s;
}
</style> -->