<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const selectedUserLevel = ref('');
const currentStep = ref('user-level');
const schools = ref([]);
const programs = ref([]);
const terms = ref([]);
const degrees = ref([]);
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Form data
const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    gender: '',
    dob: '',
    mobile_number: '',
    telephone_number: '',
    company_name: '',
    company_street_address: '',
    company_brgy: '',
    company_city: '',
    company_province: '',
    company_zip_code: '',
    company_email: '',
    company_mobile_phone: '',
    category: '',
    first_name: '',
    last_name: '',
    middle_name: '',
    institution_type: '',
    institution_name: '',
    institution_address: '',
    institution_president_last_name: '',
    institution_president_first_name: '',
    institution_career_officer_first_name: '',
    institution_career_officer_middle_name: '',
    institution_career_officer_last_name: '',
    graduate_school_graduated_from: '',
    graduate_program_completed: '',
    graduate_year_graduated: '',
    graduate_first_name: '',
    graduate_middle_name: '',
    graduate_last_name: '',
    terms: false,
});

onMounted(() => {
    const path = window.location.pathname;
    if (path.includes('institution')) {
        form.role = 'institution';
        currentStep.value = 'institution';
    } else if (path.includes('graduate')) {
        form.role = 'graduate';
        currentStep.value = 'graduate';
    } else if (path.includes('company')) {
        form.role = 'company';
        currentStep.value = 'company';
    } else {
        currentStep.value = 'user-level';
    }
});

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear; i >= currentYear - 50; i--) {
        years.push(i);
    }
    return years;
});

const filteredPrograms = computed(() => {
    if (!form.graduate_school_graduated_from) return programs.value;
    return programs.value.filter(program =>
        program.institution_id === form.graduate_school_graduated_from
    );
});

const selectUserLevel = (level) => {
    selectedUserLevel.value = level;

    if (level === 'graduates') {
        // Navigate to graduate registration URL
        window.location.href = '/register/graduate';
    } else if (level === 'institution') {
        // Navigate to institution registration URL
        window.location.href = '/register/institution';
    } else if (level === 'industry') {
        // Navigate to company registration URL
        window.location.href = '/register/company';
    }
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const toggleConfirmPasswordVisibility = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};

const submitGraduateAccount = () => {
    form.post(route('register.graduate.store'), {
        onSuccess: () => {
            window.location.href = route('graduate.information');
        }
    });
};

const submitCompanyAccount = () => {
    form.post(route('register.company.store'), {
        onSuccess: () => {
            window.location.href = route('company.information');
        }
    });
};

const submitInstitutionAccount = () => {
    form.post(route('register.institution.store'), {
        onSuccess: () => {
            window.location.href = route('institution.information');
        }
    });
};
</script>

<template>
    <Head title="Sign Up" />
    
    <!-- Modern Gradient Background -->
    <div class="min-h-screen gradient-bg flex items-center justify-center p-4 overflow-hidden relative">
        <!-- Floating Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-10 w-32 h-32 gradient-card rounded-full opacity-20 animate-float"></div>
            <div class="absolute top-1/4 right-20 w-24 h-24 gradient-feature rounded-full opacity-30 animate-float-reverse"></div>
            <div class="absolute bottom-20 left-1/4 w-40 h-40 gradient-cta rounded-full opacity-15 animate-morph"></div>
            <div class="absolute top-1/2 right-1/3 w-16 h-16 bg-white rounded-full opacity-10 animate-pulse-glow"></div>
        </div>
        
        <div class="w-full max-w-lg relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 gradient-card rounded-2xl flex items-center justify-center mx-auto mb-4 animate-pulse-glow">
                    <span class="text-white font-bold text-2xl neon-text">P</span>
                </div>
                <h2 class="text-4xl font-bold text-white mb-3 neon-text">Join Pathway</h2>
                <p class="text-white/80 text-lg">Create your <span class="text-cyan-300 neon-text">Pathway</span> account</p>
            </div>

            <!-- Glass Card -->
            <div class="glass p-8 rounded-3xl shadow-2xl border border-white/20 backdrop-blur-xl">
                <!-- User Level Selection -->
                <div v-if="currentStep === 'user-level'" class="space-y-6">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-white mb-2 neon-text">Select User Type</h3>
                        <p class="text-white/80">Choose the option that best describes you</p>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Graduate Option -->
                        <div @click="selectUserLevel('graduates')" 
                             class="glass p-6 rounded-2xl hover:scale-105 cursor-pointer transition-all duration-300 border border-white/30 hover:border-cyan-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 gradient-feature rounded-xl flex items-center justify-center group-hover:animate-pulse-glow">
                                    <i class="fas fa-user-graduate text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white neon-text">Graduate</h4>
                                    <p class="text-white/70 text-sm">For recent graduates seeking opportunities</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Institution Option -->
                        <div @click="selectUserLevel('institution')" 
                             class="glass p-6 rounded-2xl hover:scale-105 cursor-pointer transition-all duration-300 border border-white/30 hover:border-purple-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 gradient-card rounded-xl flex items-center justify-center group-hover:animate-pulse-glow">
                                    <i class="fas fa-university text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white neon-text">Institution</h4>
                                    <p class="text-white/70 text-sm">For schools, colleges and universities</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Option -->
                        <div @click="selectUserLevel('industry')" 
                             class="glass p-6 rounded-2xl hover:scale-105 cursor-pointer transition-all duration-300 border border-white/30 hover:border-green-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 gradient-cta rounded-xl flex items-center justify-center group-hover:animate-pulse-glow">
                                    <i class="fas fa-building text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white neon-text">Company</h4>
                                    <p class="text-white/70 text-sm">For companies seeking qualified talent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center pt-4">
                        <p class="text-white/80">
                            Already have an account?
                            <Link href="/login" class="text-pink-300 hover:text-pink-100 hover:neon-text font-semibold transition-all duration-200 ml-2">
                                Sign In
                            </Link>
                        </p>
                    </div>
                </div>

                <!-- Graduate Registration Form -->
                <div v-else-if="currentStep === 'graduate'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-white neon-text">Graduate Registration</h3>
                        <button @click="currentStep = 'user-level'"
                                class="text-white/80 hover:text-white hover:neon-text p-2 rounded-xl glass transition-all duration-300 hover:scale-110">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitGraduateAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address" class="text-lg font-semibold text-white mb-2" />
                            <TextInput 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400 text-white placeholder-white/60" 
                                placeholder="Enter your email"
                                required />
                            <InputError class="mt-2 text-pink-300" :message="form.errors.email" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password" value="Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password" 
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-purple-400 focus:ring-2 focus:ring-purple-400 text-white placeholder-white/60" 
                                    placeholder="Enter your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation" 
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-green-400 focus:ring-2 focus:ring-green-400 text-white placeholder-white/60" 
                                    placeholder="Confirm your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password_confirmation" />
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-rainbow transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400 shadow-2xl disabled:opacity-50 disabled:transform-none animate-pulse-glow"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Graduate Account</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Institution Registration Form -->
                <div v-else-if="currentStep === 'institution'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-white neon-text">Institution Registration</h3>
                        <button @click="currentStep = 'user-level'"
                                class="text-white/80 hover:text-white hover:neon-text p-2 rounded-xl glass transition-all duration-300 hover:scale-110">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitInstitutionAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address" class="text-lg font-semibold text-white mb-2" />
                            <TextInput 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400 text-white placeholder-white/60" 
                                placeholder="Enter institution email"
                                required />
                            <InputError class="mt-2 text-pink-300" :message="form.errors.email" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password" value="Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password" 
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-purple-400 focus:ring-2 focus:ring-purple-400 text-white placeholder-white/60" 
                                    placeholder="Enter your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation" 
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-green-400 focus:ring-2 focus:ring-green-400 text-white placeholder-white/60" 
                                    placeholder="Confirm your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password_confirmation" />
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-rainbow transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400 shadow-2xl disabled:opacity-50 disabled:transform-none animate-pulse-glow"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Institution Account</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Company Registration Form -->
                <div v-else-if="currentStep === 'company'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-white neon-text">Company Registration</h3>
                        <button @click="currentStep = 'user-level'"
                                class="text-white/80 hover:text-white hover:neon-text p-2 rounded-xl glass transition-all duration-300 hover:scale-110">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitCompanyAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address" class="text-lg font-semibold text-white mb-2" />
                            <TextInput 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400 text-white placeholder-white/60" 
                                placeholder="Enter company email"
                                required />
                            <InputError class="mt-2 text-pink-300" :message="form.errors.email" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password" value="Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password" 
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-purple-400 focus:ring-2 focus:ring-purple-400 text-white placeholder-white/60" 
                                    placeholder="Enter your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password" />
                        </div>
                        
                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password" class="text-lg font-semibold text-white mb-2" />
                            <div class="relative">
                                <TextInput 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation" 
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-green-400 focus:ring-2 focus:ring-green-400 text-white placeholder-white/60" 
                                    placeholder="Confirm your password"
                                    required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:neon-text focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-pink-300" :message="form.errors.password_confirmation" />
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-rainbow transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400 shadow-2xl disabled:opacity-50 disabled:transform-none animate-pulse-glow"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Company Account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

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
</style>