<script setup>
import { ref, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
 
const activeSection = ref('landing-page.index');

// Header state
const isScrolled = ref(false);

// Login form
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// Navigate to login page
const navigateToLogin = () => {
    window.location.href = route('login');
};

// Navigate to register page
const navigateToRegister = () => {
    window.location.href = route('pathway.register');
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

// Smooth scroll function for navigation links
const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};

// Set active section based on scroll position and handle header background
onMounted(() => {
  window.addEventListener('scroll', () => {
    const sections = ['home', 'about', 'features'];
    const scrollPosition = window.scrollY;
    
    // Update header background based on scroll position
    isScrolled.value = scrollPosition > 50;
    
    sections.forEach(section => {
      const element = document.getElementById(section);
      if (element) {
        const offsetTop = element.offsetTop;
        const height = element.offsetHeight;
        
        if (scrollPosition >= offsetTop - 100 && scrollPosition < offsetTop + height - 100) {
          activeSection.value = section;
        }
      }
    });
  });
});
</script>

<template>
    <div class="relative min-h-screen">
        <!-- Landing Section -->
        <div id="home" class="relative">
            <img src="/images/LandingPage.jpg" alt="Graduates throwing graduation caps"
                class="w-full h-[800px] object-cover" width="1920" height="1080" />
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
            <header class="fixed top-0 left-0 right-0 flex items-center justify-between px-6 py-4 z-50 transition-all duration-300" 
                :class="{'bg-blue-600 shadow-md': isScrolled, 'bg-transparent': !isScrolled}">
                <div>
                    <Link href="/" class="text-white font-extrabold text-lg leading-none select-none">
                    Pathway
                    </Link>
                </div>
                <nav class="hidden md:flex space-x-8 text-white text-sm font-normal">
                    <a @click.prevent="scrollToSection('about')" href="#about" class="hover:underline cursor-pointer"
                        :class="{ 'font-semibold': activeSection === 'about' }">About</a>
                    <a @click.prevent="scrollToSection('features')" href="#features"
                        class="hover:underline cursor-pointer"
                        :class="{ 'font-semibold': activeSection === 'features' }">Features</a>
                </nav>
                <div class="hidden md:flex space-x-4">
                    <button @click="navigateToLogin"
                        class="bg-white text-black rounded-md px-4 py-2 text-sm font-normal hover:bg-gray-100">
                        Sign In
                    </button>
                    <button @click="navigateToRegister"
                        class="bg-white text-black rounded-md px-4 py-2 text-sm font-normal hover:bg-gray-100">
                        Sign Up
                    </button>
                </div>
            </header>
            <main class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-white font-extrabold text-5xl md:text-6xl leading-tight max-w-4xl">
                    Pathway
                </h1>
                <p class="text-white text-lg md:text-xl max-w-3xl mt-4">
                    Empowering graduates, institutions, and industries to connect and grow
                    together.
                </p>
                <div class="mt-12 animate-bounce cursor-pointer" @click="scrollToSection('about')">
                    <i class="fas fa-chevron-down text-white text-xl"></i>
                </div>
            </main>
        </div>

        <!-- Navigation -->
        <nav class="flex md:hidden justify-center space-x-8 text-black text-sm font-normal mt-4">
            <a @click.prevent="scrollToSection('about')" href="#about" class="hover:underline cursor-pointer"
                :class="{ 'font-semibold': activeSection === 'about' }">About</a>
            <a @click.prevent="scrollToSection('features')" href="#features" class="hover:underline cursor-pointer"
                :class="{ 'font-semibold': activeSection === 'features' }">Features</a>
        </nav>
        <div class="flex md:hidden justify-center space-x-4 mt-4 px-4 mb-8">
            <button @click="navigateToLogin"
                class="bg-blue-600 text-white rounded-md px-4 py-2 text-sm font-normal w-full max-w-xs hover:bg-blue-700">
                Sign In
            </button>
            <button @click="navigateToRegister"
                class="bg-blue-600 text-white rounded-md px-4 py-2 text-sm font-normal w-full max-w-xs hover:bg-blue-700">
                Sign Up
            </button>
        </div>

        <!-- About Section -->
        <section id="about" class="min-h-screen flex items-center py-20 bg-gray-50">
            <div class="container mx-auto px-8 w-full">
                <h2 class="text-3xl font-bold text-center mb-12">Connecting Talent with Opportunity</h2>
                <p class="text-center text-gray-600 max-w-3xl mx-auto mb-16">
                    Pathway bridges the gap between educational institutions, fresh graduates, and industry needs,
                    creating a seamless ecosystem for growth and opportunity.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Graduates Card -->
                    <div class="bg-white p-6 rounded-lg shadow-md overflow-hidden">
                        <img src="/images/gradplanning.jpg" alt="Graduate planning career" 
                            class="w-full h-36 object-cover mb-6 rounded" />
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i class="fas fa-user-graduate text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold">Graduates</h3>
                        </div>
                        <p class="text-gray-600">
                            Showcase your skills, connect with institutions and discover
                            career opportunities tailored to your qualifications.
                        </p>
                    </div>
                    
                    <!-- Institutions Card -->
                    <div class="bg-white p-6 rounded-lg shadow-md overflow-hidden">
                        <img src="/images/tracking.jpg" alt="University campus" 
                            class="w-full h-36 object-cover mb-6 rounded" />
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i class="fas fa-university text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold">Institutions</h3>
                        </div>
                        <p class="text-gray-600">
                            Track graduate outcomes, strengthen industry partnerships,
                            and enhance your educational offerings based on market needs.
                        </p>
                    </div>
                    
                    <!-- Industry Card -->
                    <div class="bg-white p-6 rounded-lg shadow-md overflow-hidden">
                        <img src="/images/industry.jpg" alt="Industry professionals meeting" 
                            class="w-full h-36 object-cover mb-6 rounded" />
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold">Industry</h3>
                        </div>
                        <p class="text-gray-600">
                            Find qualified talent, collaborate with educational
                            institutions, and contribute to shaping the future workforce.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="min-h-screen flex items-center py-20 bg-white">
            <div class="container mx-auto px-8 w-full">
                <h2 class="text-3xl font-bold text-center mb-6">Why Choose Pathway?</h2>
                <p class="text-center text-gray-600 max-w-3xl mx-auto mb-16">
                    Our platform offers dedicated features for each user group, creating a complete ecosystem for
                    career development and talent acquisition.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-12">
                    <!-- Graduate Profiles -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-user-graduate text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-3">Graduate Profiles</h3>
                            <p class="text-gray-600 mb-4">
                                Create comprehensive profiles showcasing your education, skills, and career aspirations.
                                Connect with your alma mater and potential employers.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Customizable skill showcases</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Verified credentials from institutions</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Direct connections to industry opportunities</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Institution Dashboard -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-university text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-3">Institution Dashboard</h3>
                            <p class="text-gray-600 mb-4">
                                Monitor graduate outcomes, connect with industry partners, and showcase your
                                institution's unique strengths and programs.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Graduate tracking and analytics</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Industry collaboration tools</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Program outcome visualization</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Industry Portal -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-building text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-3">Industry Portal</h3>
                            <p class="text-gray-600 mb-4">
                                Access qualified talent pools, communicate with educational institutions, and streamline
                                your recruitment process.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Customized talent searches</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Opportunity posting and management</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Unified Ecosystem -->
                    <div class="flex items-start">
                        <div class="text-blue-500 mr-4">
                            <i class="fas fa-handshake text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-3">Unified Ecosystem</h3>
                            <p class="text-gray-600 mb-4">
                                Our platform brings together all stakeholders in education and employment, creating a
                                seamless pathway from education to career.
                            </p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Shared data insights</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-amber-500 mr-2"></i>
                                    <span>Collaborative workforce development</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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