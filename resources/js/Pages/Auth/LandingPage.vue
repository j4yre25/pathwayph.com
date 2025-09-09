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
/* CSS Variables for new color scheme */
:root {
    --primary-bg: #2c3e50;
    --secondary-bg: #34495e;
    --accent-color: #e74c3c;
    --text-accent: #ec7063;
    --primary-text: #2c3e50;
    --secondary-text: #7f8c8d;
    --light-text: #ecf0f1;
    --accent-bg: rgba(231, 76, 60, 0.1);
    --accent-text: #c0392b;
}

/* Background with subtle gradient */
.gradient-bg {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
}

.gradient-card {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
}

/* Removed floating animations for cleaner design */

/* Pulse glow effect */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(30, 64, 175, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(30, 64, 175, 0.5);
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

/* Accent hover effects */
.hover-accent:hover {
    background: linear-gradient(45deg, var(--accent-color), var(--text-accent), #e74c3c, #ec7063);
    background-size: 400% 400%;
    animation: accent-shift 2s ease infinite;
}

@keyframes accent-shift {
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

/* Subtle text enhancement */
.enhanced-text {
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
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
        <!-- Background overlay for better text readability -->
        <div class="absolute inset-0 z-0"></div>
        
        <!-- Landing Section -->
        <div id="home" class="relative z-10">
            
            <!-- Modern Header -->
           <header 
                role="banner"
                class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" 
                :class="{
                    'glass shadow-lg py-3': isScrolled, 
                    'bg-transparent py-4': !isScrolled
                }">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex items-center justify-between">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <Link href="/" class="flex items-center space-x-3 focus:outline-none focus:ring-2 focus:ring-cyan-400 rounded">
                                <div class="w-10 h-10 gradient-card rounded-xl flex items-center justify-center animate-pulse-glow">
                                    <span class="text-white font-bold text-xl enhanced-text">P</span>
                                </div>
                                <span :class="[
                                    'font-extrabold text-xl leading-none select-none transition-colors duration-200 enhanced-text',
                                    isScrolled ? 'text-blue-600' : 'text-blue-600'
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
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'home' 
                                        ? 'text-blue-600 enhanced-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-blue-600' 
                                        : 'text-blue-600/80 hover:text-blue-600 hover:enhanced-text'
                                ]">
                                Home
                            </a>
                            <a 
                                href="#about" 
                                @click.prevent="scrollToSection('about')"
                                :aria-current="activeSection === 'about' ? 'page' : null"
                                :class="[
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'about' 
                                        ? 'text-blue-600 enhanced-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-blue-600' 
                                        : 'text-blue-600/80 hover:text-blue-600 hover:enhanced-text'
                                ]">
                                About
                            </a>
                            <a 
                                href="#features" 
                                @click.prevent="scrollToSection('features')"
                                :aria-current="activeSection === 'features' ? 'page' : null"
                                :class="[
                                    'text-sm font-medium transition-all duration-200 relative focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 rounded px-3 py-2',
                                    activeSection === 'features' 
                                        ? 'text-blue-600 enhanced-text after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-blue-600' 
                                        : 'text-blue-600/80 hover:text-blue-600 hover:enhanced-text'
                                ]">
                                Features
                            </a>
                        </nav>

                        <!-- Desktop Auth Buttons -->
                        <div class="hidden md:flex items-center space-x-4">
                            <button 
                                @click="navigateToLogin"
                                class="px-5 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 text-blue-600 hover:text-blue-700 hover:enhanced-text">
                                Sign In
                            </button>
                            <button 
                                @click="navigateToRegister"
                                class="px-6 py-2.5 gradient-cta text-white text-sm font-semibold rounded-xl hover-blue transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 shadow-lg animate-pulse-glow">
                                Sign up
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="min-h-screen flex items-center px-4 z-20 relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-9 mb-9">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <!-- Left Column - Content -->
                        <div class="text-left order-2 lg:order-1">
                            <!-- Hero Title -->
                            <h1 class="text-gray-900 font-extrabold text-6xl md:text-8xl leading-tight mb-4 enhanced-text">
                                Pathway
                            </h1>

                            <!-- Tagline -->
                            <h3 class="text-gray-800 font-bold text-3xl md:text-5xl leading-tight mb-8">
                                Connect. Grow. <span class="text-[var(--text-accent)] enhanced-text">Succeed.</span>
                            </h3>

                            <!-- Professional Tagline -->
                            <p class="text-gray-700 text-xl md:text-2xl max-w-3xl mb-12 font-light">
                                The premier platform connecting <span class="text-[var(--text-accent)] enhanced-text">Filipino talent</span> with <span class="text-[var(--accent-color)] enhanced-text">global opportunities</span>.
                            </p>

                            <!-- Primary and Secondary CTAs -->
                            <div class="flex flex-col sm:flex-row gap-6 mb-12 justify-start items-start">
                                <button 
                                    @click="navigateToRegister"
                                    class="px-8 py-4 gradient-cta text-white text-lg font-semibold rounded-2xl hover-blue transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 shadow-lg animate-pulse-glow">
                                    Join the Path
                                    
                                </button>
                                <button 
                                    @click="scrollToSection('about')"
                                    class="px-8 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 text-lg font-semibold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-gray-300">
                                    Learn More
                                </button>
                            </div>
                        </div>
                        
                        <!-- Right Column - Image -->
                        <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
                            <div class="relative max-w-full">
                                <img 
                                    src="/images/vector_home.png" 
                                    alt="Pathway Platform Illustration" 
                                    class="w-full h-auto max-w-lg lg:max-w-xl xl:max-w-2xl object-contain"
                                />
                            </div>
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
                            <div class="bg-emerald-100 p-3 rounded-full mr-4 group-hover:bg-emerald-200 transition-colors duration-300">
                                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                                    <path d="M5 8a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zM5 11a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors duration-300">Institutions</h3>
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
                            <div class="bg-amber-100 p-3 rounded-full mr-4 group-hover:bg-amber-200 transition-colors duration-300">
                                <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-amber-600 transition-colors duration-300">Industry</h3>
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