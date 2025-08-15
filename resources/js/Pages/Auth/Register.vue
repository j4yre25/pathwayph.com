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
        currentStep.value = 'personal';
    } else if (path.includes('company')) {
        form.role = 'company';
        currentStep.value = 'company';
    } else {
        currentStep.value = 'user-level';
        console.error('Unknown role:', path);
    }
    console.log('Current Role:', form.role);
});

console.log(form.role)

// Available years for graduation year dropdown
const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear; i >= currentYear - 50; i--) {
        years.push(i);
    }
    return years;
});

// Filter programs based on selected school
const filteredPrograms = computed(() => {
    if (!form.graduate_school_graduated_from) return programs.value;
    return programs.value.filter(program =>
        program.institution_id === form.graduate_school_graduated_from
    );
});


// Select user level and proceed to registration steps
const selectUserLevel = (level) => {
    selectedUserLevel.value = level;

    if (level === 'graduates') {
        currentStep.value = 'personal';
    } else if (level === 'institution') {
        currentStep.value = 'institution';
    } else if (level === 'industry') {
        currentStep.value = 'company';
    }
}

// Navigate to previous step
const goToPreviousStep = () => {
    if (currentStep.value === 'personal') {
        currentStep.value = 'user-level';
    } else if (currentStep.value === 'education') {
        currentStep.value = 'personal';
    } else if (currentStep.value === 'contact') {
        currentStep.value = 'education';
    } else if (currentStep.value === 'security') {
        currentStep.value = 'contact';
    }

    if (currentStep.value === 'institution') {
        currentStep.value = 'user-level';
    } else if (currentStep.value === 'president') {
        currentStep.value = 'institution';
    } else if (currentStep.value === 'career_officer') {
        currentStep.value = 'president';
    } else if (currentStep.value === 'security') {
        currentStep.value = 'career_officer';
    }

    if (currentStep.value === 'company') {
        currentStep.value = 'user-level';
    } else if (currentStep.value === 'contact') {
        currentStep.value = 'company';
    } else if (currentStep.value === 'HR') {
        currentStep.value = 'contact';
    } else if (currentStep.value === 'security') {
        currentStep.value = 'HR';
    }
}

// Navigate to next step
const goToNextStep = () => {
    if (currentStep.value === 'personal') {
        currentStep.value = 'education';
    } else if (currentStep.value === 'education') {
        currentStep.value = 'contact';
    } else if (currentStep.value === 'contact') {
        currentStep.value = 'security';
    }

    if (currentStep.value === 'institution') {
        currentStep.value = 'president';
    } else if (currentStep.value === 'president') {
        currentStep.value = 'career_officer';
    } else if (currentStep.value === 'career_officer') {
        currentStep.value = 'security';
    }

    if (currentStep.value === 'company') {
        currentStep.value = 'contact';
    } else if (currentStep.value === 'contact') {
        currentStep.value = 'HR';
    } else if (currentStep.value === 'HR') {
        currentStep.value = 'security';
    }
}


// Submit the form
const submit = () => {
    let routeName;
    if (form.role === 'graduate') {
        routeName = 'register.graduate.store';
    } else if (form.role === 'institution') {
        routeName = 'register.institution.store';
    } else if (form.role === 'company') {
        routeName = 'register.company.store';
    } else {
        console.error('Unknown role:', form.role);
        return;
    }
    
    form.post(route(routeName), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}

const submitCompanyAccount = () => {
    form.post(route('register.company.store'), {
        onSuccess: () => {
            // Redirect to information section after successful registration
            window.location.href = route('company.information');
        }
    });
};     

</script>

<template>
    <Head title="Sign Up" />
    
    <!-- Modern Colorful Background -->
    <div class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Floating Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-32 h-32 gradient-feature rounded-full animate-float opacity-20"></div>
            <div class="absolute top-40 right-20 w-24 h-24 gradient-card rounded-full animate-float-reverse opacity-30"></div>
            <div class="absolute bottom-32 left-1/4 w-40 h-40 gradient-cta animate-morph opacity-25"></div>
            <div class="absolute bottom-20 right-10 w-28 h-28 gradient-feature rounded-full animate-pulse-glow opacity-20"></div>
        </div>
        
        <!-- Main Content -->
        <div class="relative z-10">
            <!-- Logo Section -->
            <div class="text-center pt-8 pb-4">
                <div class="inline-flex items-center space-x-2">
                    <div class="w-12 h-12 bg-gradient-to-r from-cyan-400 to-purple-500 rounded-xl flex items-center justify-center animate-pulse-glow">
                        <span class="text-white font-bold text-xl">P</span>
                    </div>
                    <span class="text-3xl font-bold text-white neon-text">Pathway</span>
                </div>
            </div>
            
            <!-- User Level Selection -->
            <div v-if="currentStep === 'user-level'" class="px-4 sm:px-6 lg:px-8 py-12 max-w-md mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-white neon-text mb-6 animate-pulse-glow">Select User Level ✨</h2>
                    <p class="text-lg md:text-xl text-white/80 leading-relaxed">Choose the option that best describes <span class="text-cyan-300 neon-text">you</span></p>
                </div>
                <div class="glass p-8 rounded-2xl backdrop-blur-lg border border-white/20 animate-pulse-glow">
                    <div class="space-y-6">
                        <!-- Graduate Option -->
                        <Link href="/register/graduate">
                        <div class="p-6 glass rounded-2xl hover:animate-pulse-glow cursor-pointer transition-all duration-300 flex items-center group hover:scale-105">
                            <div class="gradient-feature p-4 rounded-full mr-6 group-hover:animate-pulse-glow transition-all duration-300">
                                <i class="fas fa-user-graduate text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white group-hover:neon-text transition-all duration-300">Graduate 🎓</h3>
                                <p class="text-lg text-white/80 leading-relaxed">For recent graduates seeking <span class="text-cyan-300">opportunities</span></p>
                            </div>
                        </div>
                        </Link>

                        <!-- Institution Option -->
                        <Link href="/register/institution">
                        <div class="p-6 glass rounded-2xl hover:animate-pulse-glow cursor-pointer transition-all duration-300 flex items-center group hover:scale-105">
                            <div class="gradient-card p-4 rounded-full mr-6 group-hover:animate-pulse-glow transition-all duration-300">
                                <i class="fas fa-university text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white group-hover:neon-text transition-all duration-300">Institution 🏛️</h3>
                                <p class="text-lg text-white/80 leading-relaxed">For schools, colleges and <span class="text-pink-300">universities</span></p>
                            </div>
                        </div>
                        </Link>

                        <!-- Industry Option -->
                        <Link href="/register/company">
                            <div class="p-6 glass rounded-2xl hover:animate-pulse-glow cursor-pointer transition-all duration-300 flex items-center group hover:scale-105">
                                <div class="gradient-cta p-4 rounded-full mr-6 group-hover:animate-pulse-glow transition-all duration-300">
                                    <i class="fas fa-building text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white group-hover:neon-text transition-all duration-300">Industry 🏢</h3>
                                    <p class="text-lg text-white/80 leading-relaxed">For companies seeking qualified <span class="text-green-300">talent</span></p>
                                </div>
                            </div>
                        </Link>

                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-base text-white/80">
                            Already have an account?
                            <Link href="/login" class="text-cyan-300 hover:text-cyan-100 hover:neon-text font-semibold transition-all duration-200 ml-2">Sign In ✨</Link>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Graduate Registration Form -->
            <div v-else-if="currentStep === 'graduate' || currentStep === 'personal' || currentStep === 'education' || currentStep === 'contact' || currentStep === 'security'" class="px-4 sm:px-6 lg:px-8 py-12 max-w-4xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-8 right-8 text-white/80 hover:text-white hover:neon-text z-20 glass p-3 rounded-xl transition-all duration-300 hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-white neon-text mb-6 animate-pulse-glow">Graduate Information 🎓</h2>
                    <p class="text-lg md:text-xl text-white/80 leading-relaxed">Provide key details about <span class="text-cyan-300 neon-text">you</span>.</p>
                </div>

                <div class="glass p-8 rounded-2xl backdrop-blur-lg border border-white/20 animate-pulse-glow">

                    <!-- Progress Tabs -->
                    <div class="flex justify-between mb-12 border-b-2 border-white/20">
                        <div @click="currentStep = 'personal'" class="flex flex-col items-center pb-6 cursor-pointer transition-all duration-300 hover:scale-105"
                            :class="{ 'border-b-4 border-cyan-400': currentStep === 'personal' }">
                            <div
                                :class="[currentStep === 'personal' ? 'gradient-feature text-white animate-pulse-glow' : 'glass text-white/60', 'rounded-full p-4 mb-3 transition-all duration-300']">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <span
                                :class="[currentStep === 'personal' ? 'text-cyan-400 font-bold neon-text' : 'text-white/60', 'text-base transition-all duration-300']">Personal</span>
                        </div>

                        <div @click="currentStep === 'education' || currentStep === 'contact' || currentStep === 'security' ? currentStep = 'education' : null"
                            class="flex flex-col items-center pb-6 cursor-pointer transition-all duration-300 hover:scale-105"
                            :class="{ 'border-b-4 border-pink-400': currentStep === 'education' }">
                            <div
                                :class="[currentStep === 'education' ? 'gradient-card text-white animate-pulse-glow' : 'glass text-white/60', 'rounded-full p-4 mb-3 transition-all duration-300']">
                                <i class="fas fa-graduation-cap text-xl"></i>
                            </div>
                            <span
                                :class="[currentStep === 'education' ? 'text-pink-400 font-bold neon-text' : 'text-white/60', 'text-base transition-all duration-300']">Education</span>
                        </div>

                        <div @click="currentStep === 'contact' || currentStep === 'security' ? currentStep = 'contact' : null"
                            class="flex flex-col items-center pb-6 cursor-pointer transition-all duration-300 hover:scale-105"
                            :class="{ 'border-b-4 border-green-400': currentStep === 'contact' }">
                            <div
                                :class="[currentStep === 'contact' ? 'gradient-cta text-white animate-pulse-glow' : 'glass text-white/60', 'rounded-full p-4 mb-3 transition-all duration-300']">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <span
                                :class="[currentStep === 'contact' ? 'text-green-400 font-bold neon-text' : 'text-white/60', 'text-base transition-all duration-300']">Contact</span>
                        </div>

                        <div @click="currentStep === 'security' ? currentStep = 'security' : null"
                            class="flex flex-col items-center pb-6 cursor-pointer transition-all duration-300 hover:scale-105"
                            :class="{ 'border-b-4 border-purple-400': currentStep === 'security' }">
                            <div
                                :class="[currentStep === 'security' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white animate-pulse-glow' : 'glass text-white/60', 'rounded-full p-4 mb-3 transition-all duration-300']">
                                <i class="fas fa-lock text-xl"></i>
                            </div>
                            <span
                                :class="[currentStep === 'security' ? 'text-purple-400 font-bold neon-text' : 'text-white/60', 'text-base transition-all duration-300']">Security</span>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <!-- Personal Information -->
                        <div v-if="currentStep === 'personal'" class="space-y-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6">Personal Information</h3>
                        <div>
                            <InputLabel for="graduate_first_name" value="First Name" />
                            <TextInput id="graduate_first_name" v-model="form.graduate_first_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your first name" required />
                            <InputError class="mt-2" :message="form.errors.graduate_first_name" />
                        </div>
                        <div>
                            <InputLabel for="graduate_middle_name" value="Middle Name (optional)" />
                            <TextInput id="graduate_middle_name" v-model="form.graduate_middle_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your middle name" />
                            <InputError class="mt-2" :message="form.errors.graduate_middle_name" />
                        </div>
                        <div>
                            <InputLabel for="graduate_last_name" value="Last Name" />
                            <TextInput id="graduate_last_name" v-model="form.graduate_last_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your last name" required />
                            <InputError class="mt-2" :message="form.errors.graduate_last_name" />
                        </div>
                        <div>
                            <InputLabel for="gender" value="Gender" />
                            <select id="gender" v-model="form.gender"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select your gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.gender" />
                        </div>
                        <div>
                            <InputLabel for="dob" value="Birthdate" />
                            <TextInput id="dob" v-model="form.dob" type="date" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.dob" />
                        </div>
                            <div class="flex justify-end mt-8">
                                <PrimaryButton @click.prevent="goToNextStep" class="px-8 py-4 text-lg font-medium">
                                    Next
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </PrimaryButton>
                            </div>
                        </div>
                        <div v-if="currentStep === 'education'" class="space-y-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6">Education Information</h3>
                        <div>
                            <InputLabel for="graduate_school_graduated_from" value="School Graduated From" />
                            <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select your school</option>
                                <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}
                                </option>
                            </select>
                            <p v-if="loading" class="text-xs text-gray-500 mt-1">Loading schools...</p>
                            <InputError class="mt-2" :message="form.errors.graduate_school_graduated_from" />
                        </div>
                        <div>
                            <InputLabel for="graduate_year_graduated" value="Year Graduated" />
                            <select id="graduate_year_graduated" v-model="form.graduate_year_graduated"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select year</option>
                                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.graduate_year_graduated" />
                        </div>
                        <div>
                            <InputLabel for="term" value="Term" />
                            <select id="term" v-model="form.term"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select term</option>
                                <option v-for="term in terms" :key="term" :value="term">{{ term }}</option>
                            </select>
                            <p v-if="loading" class="text-xs text-gray-500 mt-1">Loading terms...</p>
                            <InputError class="mt-2" :message="form.errors.term" />
                        </div>
                        <div>
                            <InputLabel for="graduate_program_completed" value="Program Completed" />
                            <select id="graduate_program_completed" v-model="form.graduate_program_completed"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select program</option>
                                <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">{{
                                    program.name }}</option>
                            </select>
                            <p v-if="loading" class="text-xs text-gray-500 mt-1">Loading programs...</p>
                            <InputError class="mt-2" :message="form.errors.graduate_program_completed" />
                        </div>
                        <div>
                            <InputLabel for="degree" value="Degree" />
                            <select id="degree" v-model="form.degree"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select degree</option>
                                <option v-for="degree in degrees" :key="degree.id" :value="degree.id">{{ degree.name }}
                                </option>
                            </select>
                            <p v-if="loading" class="text-xs text-gray-500 mt-1">Loading degrees...</p>
                            <InputError class="mt-2" :message="form.errors.degree" />
                        </div>
                            <div class="flex justify-between mt-8">
                                <button type="button" @click="goToPreviousStep"
                                    class="inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 rounded-2xl text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Previous
                                </button>
                                <PrimaryButton @click.prevent="goToNextStep" class="ml-4 px-8 py-4 text-lg font-medium">
                                    Next
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </PrimaryButton>
                            </div>
                        </div>
                        <div v-if="currentStep === 'contact'" class="space-y-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h3>
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                placeholder="Enter your email address" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="phone_number" value="Phone Number" />
                            <TextInput id="phone_number" v-model="form.phone_number" type="tel"
                                class="mt-1 block w-full" placeholder="Enter your phone number" required />
                            <InputError class="mt-2" :message="form.errors.phone_number" />
                        </div>
                            <div class="flex justify-between mt-8">
                                <button type="button" @click="goToPreviousStep"
                                    class="inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 rounded-2xl text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Previous
                                </button>
                                <PrimaryButton @click.prevent="goToNextStep" class="ml-4 px-8 py-4 text-lg font-medium">
                                    Next
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </PrimaryButton>
                            </div>
                        </div>
                        <div v-if="currentStep === 'security'" class="space-y-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6">Security</h3> <!-- Password -->
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                placeholder="Enter your password" required />
                            <p class="text-xs text-gray-600 mt-1">Password must be at least 8 characters with at least
                                one uppercase letter, one lowercase letter, and one number.</p>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                                class="mt-1 block w-full" placeholder="Confirm your password" required />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                        <div class="mt-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.terms" name="terms" />
                                <span class="ml-2 text-xs text-gray-600">
                                    I accept the <a href="#" class="text-blue-600 hover:underline">Terms and
                                        Conditions</a>
                                </span>
                            </label>
                            <InputError class="mt-2" :message="form.errors.terms" />
                        </div>
                            <div class="flex justify-between mt-8">
                                <button type="button" @click="goToPreviousStep"
                                    class="inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 rounded-2xl text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i> Previous
                                </button>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-8 py-4 text-lg font-medium">
                                    Register
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Institution -->
            <div v-else-if="currentStep === 'institution' || currentStep === 'institution-info' || currentStep === 'institution-contact' || currentStep === 'institution-security'" class="px-4 sm:px-6 lg:px-8 py-20 max-w-4xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-8 right-8 text-gray-500 hover:text-gray-700 z-10">
                    <i class="fas fa-times text-2xl"></i>
                </button>

                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Institution Registration</h2>
                    <p class="text-lg md:text-xl text-gray-600 leading-relaxed">Register your institution detail</p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">

                <!-- Progress Tabs -->
                <div class="flex justify-between mb-8 border-b">
                    <div @click="currentStep = 'institution'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'institution' }">
                        <div
                            :class="[currentStep === 'institution' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-building"></i>
                        </div>
                        <span
                            :class="[currentStep === 'institution' ? 'text-blue-500' : 'text-gray-500', 'text-xs mt-1']">Institution</span>
                    </div>

                    <div @click="currentStep = 'president'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'president' }">
                        <div
                            :class="[currentStep === 'president' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span
                            :class="[currentStep === 'president' ? 'text-blue-500' : 'text-gray-500', 'text-xs mt-1']">President</span>
                    </div>

                    <div @click="currentStep = 'contact'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'contact' }">
                        <div
                            :class="[currentStep === 'contact' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span
                            :class="[currentStep === 'contact' ? 'text-blue-500' : 'text-gray-500', 'text-xs mt-1']">Contact</span>
                    </div>

                    <div @click="currentStep = 'security'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'security' }">
                        <div
                            :class="[currentStep === 'security' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span
                            :class="[currentStep === 'security' ? 'text-blue-500' : 'text-gray-500', 'text-xs mt-1']">Security</span>
                    </div>
                </div>
                </div>

                <form @submit.prevent="submit">
                    <div v-if="currentStep === 'institution'" class="space-y-4">
                        <h3 class="text-base font-medium">Institution Detail</h3>
                        <div>
                            <InputLabel for="institution_type" value="Institution Type" />
                            <select id="institution_type" v-model="form.institution_type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select institution type</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.institution_type" />
                            <div>
                                <InputLabel for="institution_name" value="Institution Name" />
                                <TextInput id="institution_name" v-model="form.institution_name" type="text"
                                    class="mt-1 block w-full" placeholder="Enter your institution name" required />
                                <InputError class="mt-2" :message="form.errors.institution_name" />
                            </div>
                            <div>
                                <InputLabel for="institution_address" value="Institution Address" />
                                <TextInput id="institution_address" v-model="form.institution_address" type="text"
                                    class="mt-1 block w-full" placeholder="Enter your institution address" required />
                                <InputError class="mt-2" :message="form.errors.institution_address" />
                            </div>
                            <div>
                                <InputLabel for="institution_email" value="Institution Email" />
                                <TextInput id="institution_email" v-model="form.institution_email" type="email"
                                    class="mt-1 block w-full" placeholder="Enter your institution email" required />
                                <InputError class="mt-2" :message="form.errors.institution_email" />
                            </div>
                            <div>
                                <InputLabel for="institution_phone_number" value="Phone Number" />
                                <TextInput id="institution_phone_number" v-model="form.institution_phone_number"
                                    type="tel" class="mt-1 block w-full"
                                    placeholder="Enter your institution phone number" required />
                                <InputError class="mt-2" :message="form.errors.institution_phone_number" />
                            </div>
                            <div>
                                <InputLabel for="institution_telephone_number" value="Telephone Number (Optional)" />
                                <TextInput id="institution_telephone_number" v-model="form.institution_telephone_number"
                                    type="tel" class="mt-1 block w-full"
                                    placeholder="Enter your institution telephone number" required />
                                <InputError class="mt-2" :message="form.errors.institution_telephone_number" />
                            </div>
                            <div class="flex justify-between mt-6">
                                <button type="button" @click="goToNextStep"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-full text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition ease-in-out duration-150">
                                    Next
                                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="currentStep === 'president'" class="space-y-4">
                            <h3 class="text-base font-medium">President Detail</h3>
                            <div v-if="currentStep === 'personal'" class="space-y-4">
                                <h3 class="text-base font-medium">Personal Information</h3>
                                <div>
                                    <InputLabel for="president_first_name" value="First Name" />
                                    <TextInput id="president_first_name" v-model="form.president_first_name" type="text"
                                        class="mt-1 block w-full" placeholder="Enter your President first name"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.president_first_name" />
                                </div>
                                <div>
                                    <InputLabel for="president_middle_name" value="Middle Name (optional)" />
                                    <TextInput id="president_middle_name" v-model="form.president_middle_name"
                                        type="text" class="mt-1 block w-full" placeholder="Enter your middle name" />
                                    <InputError class="mt-2" :message="form.errors.president_middle_name" />
                                </div>
                                <div>
                                    <InputLabel for="president_last_name" value="Last Name" />
                                    <TextInput id="president_last_name" v-model="form.president_last_name" type="text"
                                        class="mt-1 block w-full" placeholder="Enter your last name" required />
                                    <InputError class="mt-2" :message="form.errors.president_last_name" />
                                </div>
                                <div>
                                    <InputLabel for="gender" value="Gender" />
                                    <select id="gender" v-model="form.gender"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="" disabled>Select your gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gender" />
                                </div>
                                <div>
                                    <InputLabel for="birthdate" value="Birthdate" />
                                    <TextInput id="birthdate" v-model="form.birthdate" type="date"
                                        class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.birthdate" />
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button type="button" @click="goToPreviousStep"
                                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        <i class="fas fa-arrow-left mr-2 text-xs"></i> Previous
                                    </button>
                                    <button type="button" @click="goToNextStep"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-full text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition ease-in-out duration-150">
                                        Next
                                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="currentStep === 'career_officer'" class="space-y-4">
                            <h3 class="text-lg font-medium">Career Officer Information</h3>
                            <div>
                                <InputLabel for="career_officer_first_name" value="First Name" />
                                <TextInput id="career_officer_first_name" v-model="form.career_officer_first_name"
                                    type="text" class="mt-1 block w-full"
                                    placeholder="Enter your Career Officer first name" required />
                                <InputError class="mt-2" :message="form.errors.career_officer_first_name" />
                            </div>
                            <div>
                                <InputLabel for="career_officer_middle_name" value="Middle Name (optional)" />
                                <TextInput id="career_officer_middle_name" v-model="form.career_officer_middle_name"
                                    type="text" class="mt-1 block w-full" placeholder="Enter your middle name" />
                                <InputError class="mt-2" :message="form.errors.career_officer_middle_name" />
                            </div>
                            <div>
                                <InputLabel for="career_officer_last_name" value="Last Name" />
                                <TextInput id="career_officer_last_name" v-model="form.career_officer_last_name"
                                    type="text" class="mt-1 block w-full" placeholder="Enter your last name" required />
                                <InputError class="mt-2" :message="form.errors.career_officer_last_name" />
                            </div>
                            <div>
                                <InputLabel for="gender" value="Gender" />
                                <select id="gender" v-model="form.gender"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    <option value="" disabled>Select your gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.gender" />
                            </div>
                            <div>
                                <InputLabel for="birthdate" value="Birthdate" />
                                <TextInput id="birthdate" v-model="form.birthdate" type="date" class="mt-1 block w-full"
                                    required />
                                <InputError class="mt-2" :message="form.errors.birthdate" />
                            </div>
                            <div class="flex justify-between mt-6">
                                <button type="button" @click="goToPreviousStep"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i> Previous
                                </button>
                                <button type="button" @click="goToNextStep"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition ease-in-out duration-150">
                                    Next
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="currentStep === 'security'" class="space-y-4">
                            <h3 class="text-lg font-medium">Security Information</h3>
                            <div>
                                <InputLabel for="email" value="Email Address" />
                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                    placeholder="Enter your email address" required />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput id="password" v-model="form.password" type="password"
                                    class="mt-1 block w-full" placeholder="Enter your password" required />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    type="password" class="mt-1 block w-full" placeholder="Confirm your password"
                                    required />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                            <div class="mt-4">
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.terms" name="terms" />
                                    <span class="ml-2 text-sm text-gray-600">
                                        I accept the <a href="#" class="text-blue-600 hover:underline">Terms and
                                            Conditions</a>
                                    </span>
                                </label>
                                <InputError class="mt-2" :message="form.errors.terms" />
                            </div>
                            <div class="flex justify-between mt-6">
                                <button type="button" @click="goToPreviousStep"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i> Previous
                                </button>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Register
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Industry Registration Form -->
            <div v-else-if="currentStep === 'company'" class="p-6 max-w-3xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <!-- Company Registration Step 1 -->
                <div v-if="currentStep === 'company'">
                    <h3 class="text-lg font-medium">Company Registration</h3>
                    <form @submit.prevent="submitCompanyAccount">
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                required />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                                class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                        <div class="flex justify-end mt-6">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Register
                            </PrimaryButton>
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