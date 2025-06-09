<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import '@fortawesome/vue-fontawesome';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import axios from 'axios';

// Define props for the component
const props = defineProps({
    graduateData: {
        type: Object,
        default: () => ({
            name: '',
            title: '',
            location: '',
            phone: '',
            email: '',
            degree: '',
            socialLinks: [],
            about: "",
            careerGoals: {
                shortTerm: '',
                longTerm: '',
                industries: ''
            },
            employmentPreferences: [],
            personalInfo: {
                location: '',
                birthdate: '',
                gender: '',
                ethnicity: '',
                graduated: '',
                school: ''
            },
            education: [],
            educationSummary: '',
            academicAchievements: [],
            skillCategories: [],
            skills: [],
            proficiencyLegend: [
                { level: 'Expert', description: '5+ years', color: 'bg-green-500' },
                { level: 'Advanced', description: '3-5 years', color: 'bg-blue-500' },
                { level: 'Intermediate', description: '1-3 years', color: 'bg-yellow-500' },
                { level: 'Beginner', description: '< 1 year', color: 'bg-orange-400' }
            ],
            workExperience: [],
            careerHighlights: [],
            projects: [],
            certifications: [],
            achievements: [],
            testimonials: [],
            contactInfo: {
                location: '',
                email: '',
                phone: '',
                website: '',
                socialProfiles: []
            },
            contactForm: {
                name: '',
                email: '',
                subject: '',
                message: ''
            }
        })
    }
});

// Local reactive data for the portfolio (for internal component state)
const localGraduateData = ref({
    name: '',
    title: '',
    location: '',
    phone: '',
    email: '',
    degree: '',
    socialLinks: [],
    about: "",
    careerGoals: {
        shortTerm: '',
        longTerm: '',
        industries: ''
    },
    employmentPreferences: [],
    personalInfo: {
        location: '',
        birthdate: '',
        gender: '',
        ethnicity: '',
        graduated: '',
        school: ''
    },
    education: [],
    educationSummary: '',
    academicAchievements: [],
    // Skills section data
    skillCategories: [],
    skills: [],
    proficiencyLegend: [
        { level: 'Expert', description: '5+ years', color: 'bg-green-500' },
        { level: 'Advanced', description: '3-5 years', color: 'bg-blue-500' },
        { level: 'Intermediate', description: '1-3 years', color: 'bg-yellow-500' },
        { level: 'Beginner', description: '< 1 year', color: 'bg-orange-400' }
    ],
    // Work Experience section data
    workExperience: [],
    careerHighlights: [],
    // Projects section data
    projects: [],
    // Certifications & Achievements section data
    certifications: [],
    achievements: [],
    // Testimonials section data
    testimonials: [],
    // Contact Me section data
    contactInfo: {
        location: '',
        email: '',
        phone: '',
        website: '',
        socialProfiles: []
    },
    contactForm: {
        name: '',
        email: '',
        subject: '',
        message: ''
    }
});

// Computed properties and methods can be added here
const profileImageUrl = computed(() => {
    return '';
});

const personalImageUrl = computed(() => {
    return '';
});

const educationImageUrl = computed(() => {
    return '';
});

// Computed property to get skills grouped by category
const skillsByCategory = computed(() => {
    const result = {};
    
    // Initialize categories
    props.graduateData.skillCategories.forEach(category => {
        result[category.name] = [];
    });
    
    // Group skills by category
    props.graduateData.skills.forEach(skill => {
        if (result[skill.category]) {
            result[skill.category].push(skill);
        }
    });
    
    return result;
});

// Lifecycle hooks
onMounted(() => {
    // Any initialization code can go here
    console.log('Graduate Portfolio component mounted');
    
    // Here you would typically fetch data from an API or props
    // and populate the graduateData ref
});

// Enhanced form validation with real-time feedback
const contactForm = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
});
const formErrors = ref({});
const formTouched = ref({
    name: false,
    email: false,
    subject: false,
    message: false
});
const isSubmitting = ref(false);
const submitSuccess = ref(false);

// Validate individual field
const validateField = (field) => {
    formTouched.value[field] = true;
    
    switch(field) {
        case 'name':
            if (!contactForm.value.name.trim()) {
                formErrors.value.name = 'Name is required';
            } else if (contactForm.value.name.trim().length < 2) {
                formErrors.value.name = 'Name must be at least 2 characters';
            } else {
                delete formErrors.value.name;
            }
            break;
            
        case 'email':
            if (!contactForm.value.email.trim()) {
                formErrors.value.email = 'Email is required';
            } else if (!/^\S+@\S+\.\S+$/.test(contactForm.value.email)) {
                formErrors.value.email = 'Please enter a valid email address';
            } else {
                delete formErrors.value.email;
            }
            break;
            
        case 'subject':
            if (!contactForm.value.subject.trim()) {
                formErrors.value.subject = 'Subject is required';
            } else if (contactForm.value.subject.trim().length < 5) {
                formErrors.value.subject = 'Subject must be at least 5 characters';
            } else {
                delete formErrors.value.subject;
            }
            break;
            
        case 'message':
            if (!contactForm.value.message.trim()) {
                formErrors.value.message = 'Message is required';
            } else if (contactForm.value.message.trim().length < 10) {
                formErrors.value.message = 'Message must be at least 10 characters';
            } else {
                delete formErrors.value.message;
            }
            break;
    }
};

// Watch for changes in form fields and validate in real-time
watch(() => contactForm.value.name, () => validateField('name'));
watch(() => contactForm.value.email, () => validateField('email'));
watch(() => contactForm.value.subject, () => validateField('subject'));
watch(() => contactForm.value.message, () => validateField('message'));

const validateForm = () => {
    // Mark all fields as touched
    Object.keys(formTouched.value).forEach(field => {
        formTouched.value[field] = true;
    });
    
    // Validate all fields
    validateField('name');
    validateField('email');
    validateField('subject');
    validateField('message');
    
    return Object.keys(formErrors.value).length === 0;
};

const getFieldClass = (field) => {
    if (!formTouched.value[field]) return '';
    return formErrors.value[field] ? 'border-red-500 bg-red-50' : 'border-green-500 bg-green-50';
};

const submitForm = async () => {
    if (validateForm()) {
        isSubmitting.value = true;
        try {
            // Submit form to the backend using Inertia
            await axios.post(route('graduate.portfolio.contact'), contactForm.value);
            
            // Reset form fields after successful submission
            contactForm.value = {
                name: '',
                email: '',
                subject: '',
                message: ''
            };
            
            // Show success message
            submitSuccess.value = true;
            
            // Reset touched state
            Object.keys(formTouched.value).forEach(field => {
                formTouched.value[field] = false;
            });
            
            // Reset success message after a delay
            setTimeout(() => {
                submitSuccess.value = false;
            }, 5000);
        } catch (error) {
            console.error('Error submitting form:', error);
            // Handle validation errors from the server
            if (error.response && error.response.data && error.response.data.errors) {
                formErrors.value = error.response.data.errors;
                // Mark fields with errors as touched
                Object.keys(error.response.data.errors).forEach(field => {
                    formTouched.value[field] = true;
                });
            }
        } finally {
            isSubmitting.value = false;
        }
    }
};

// Section collapse functionality
const expandedSections = ref({
    about: true,
    education: true,
    skills: true,
    workExperience: true,
    projects: true,
    certifications: true,
    testimonials: true,
    contact: true
});

const toggleSection = (section) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

// Loading states
const isLoading = ref(true);
const loadingProgress = ref(0);

// Simulate loading progress
onMounted(() => {
    const interval = setInterval(() => {
        loadingProgress.value += 10;
        if (loadingProgress.value >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                isLoading.value = false;
            }, 500);
        }
    }, 200);
});

// Progress tracking
const currentSection = ref('');
const sections = [
    { id: 'about', name: 'About Me' },
    { id: 'education', name: 'Education' },
    { id: 'skills', name: 'Skills' },
    { id: 'workExperience', name: 'Work Experience' },
    { id: 'projects', name: 'Projects' },
    { id: 'certifications', name: 'Certifications' },
    { id: 'testimonials', name: 'Testimonials' },
    { id: 'contact', name: 'Contact Me' }
];

// Track current section based on scroll position
onMounted(() => {
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY + 100;
        
        for (const section of sections) {
            const element = document.getElementById(section.id);
            if (element) {
                const offsetTop = element.offsetTop;
                const offsetBottom = offsetTop + element.offsetHeight;
                
                if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                    currentSection.value = section.id;
                    break;
                }
            }
        }
    });
});

// Enhanced modal functionality with animation
const isModalOpen = ref(false);
const modalContent = ref('');

const openModal = (content) => {
    modalContent.value = content;
    isModalOpen.value = true;
    // Add escape key listener
    document.addEventListener('keydown', handleEscapeKey);
};

const closeModal = () => {
    isModalOpen.value = false;
    // Remove escape key listener
    document.removeEventListener('keydown', handleEscapeKey);
};

const handleEscapeKey = (event) => {
    if (event.key === 'Escape') {
        closeModal();
    }
};

// Click outside to close modal
const handleOutsideClick = (event) => {
    if (event.target.classList.contains('modal-backdrop')) {
        closeModal();
    }
};
</script>

<template>
    <Head title="Graduate Portfolio" />
    
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 bg-white z-50 flex flex-col items-center justify-center">
        <div class="w-64 h-6 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-blue-600 transition-all duration-300" :style="{ width: `${loadingProgress}%` }"></div>
        </div>
        <div class="mt-4 text-gray-700 font-medium">Loading portfolio... {{ loadingProgress }}%</div>
    </div>
    
    <!-- Progress Indicator/Breadcrumb -->
    <div class="sticky top-0 z-40 bg-white shadow-md py-2 px-4 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex overflow-x-auto py-2 scrollbar-hide">
                <template v-for="(section, index) in sections" :key="section.id">
                    <a :href="`#${section.id}`" 
                       class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all duration-300 mx-1 first:ml-0 last:mr-0"
                       :class="{
                           'bg-blue-600 text-white': currentSection === section.id,
                           'bg-gray-100 text-gray-700 hover:bg-gray-200': currentSection !== section.id
                       }">
                        {{ section.name }}
                    </a>
                    <div v-if="index < sections.length - 1" class="flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </template>
            </div>
        </div>
    </div>
    
    <div class="bg-white text-gray-800">
        <div id="app">
            <!-- Header -->
            <header
                class="bg-gradient-to-b from-[#cce7ff] to-[#6eb7ff] p-6 sm:p-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 sm:gap-0"
            >
                <div class="flex items-center gap-6 sm:gap-8">
                    <img
                        :alt="`Portrait of ${props.graduateData.name}`"
                        class="w-24 h-24 rounded-full border-4 border-white object-cover"
                        height="96"
                        :src="profileImageUrl"
                        width="96"
                    />
                    <div class="text-white">
                        <h1 class="font-semibold text-xl sm:text-2xl leading-tight">
                            {{ props.graduateData.name }}
                        </h1>
                        <p class="text-sm sm:text-base font-normal leading-tight">
                            {{ props.graduateData.title }}
                        </p>
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:gap-4 mt-1 text-xs sm:text-sm opacity-90"
                        >
                            <div class="flex items-center gap-1">
                                <i class="fas fa-map-marker-alt text-white text-xs sm:text-sm"></i>
                                <span>{{ props.graduateData.location }}</span>
                            </div>
                            <div class="hidden sm:block border-l border-white h-4 opacity-50"></div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-phone-alt text-white text-xs sm:text-sm"></i>
                                <span>{{ props.graduateData.phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-white text-xs sm:text-sm flex flex-col sm:items-end gap-1">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-envelope"></i>
                        <span>{{ props.graduateData.email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ props.graduateData.degree }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span
                            v-for="(link, index) in props.graduateData.socialLinks"
                            :key="index"
                            class="bg-white text-[#6eb7ff] text-xs sm:text-sm rounded-full px-3 py-1 flex items-center gap-1 shadow-sm hover:bg-[#d9eaff] transition"
                        >
                            <i :class="link.icon"></i> {{ link.name }}
                        </span>
                    </div>
                </div>
            </header>
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 mb-20">
                <!-- About Me Section -->
                <section id="aboutMe">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            About Me
                        </h2>
                        <button @click="toggleSection('aboutMe')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.aboutMe">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.aboutMe ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.aboutMe" class="transition-all duration-500 ease-in-out flex flex-col lg:flex-row gap-8">
                        <div class="lg:flex-1 text-xs sm:text-sm leading-tight text-justify">
                            <p class="mb-4">
                                {{ graduateData.about }}
                            </p>
                            <div class="mb-4">
                                <p class="font-semibold text-[10px] sm:text-xs mb-0.5">Career Goals</p>
                                <p class="font-semibold text-[10px] sm:text-xs mb-0.5">Short Term Goals</p>
                                <p class="mb-1 text-[9px] sm:text-[10px]">
                                    {{ graduateData.careerGoals.shortTerm }}
                                </p>
                                <p class="font-semibold text-[10px] sm:text-xs mb-0.5">Long Term Goals</p>
                                <p class="mb-1 text-[9px] sm:text-[10px]">
                                    {{ graduateData.careerGoals.longTerm }}
                                </p>
                                <p class="font-semibold text-[10px] sm:text-xs mb-0.5">
                                    Industries of Interest
                                </p>
                                <p class="mb-1 text-[9px] sm:text-[10px]">
                                    {{ graduateData.careerGoals.industries }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-[10px] sm:text-xs mb-1">Employment Preferences</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[9px] sm:text-[10px]">
                                    <div v-for="(pref, index) in graduateData.employmentPreferences" :key="index" class="border border-gray-300 rounded p-2">
                                        <p class="font-semibold mb-0.5">{{ pref.title }}</p>
                                        <p>{{ pref.details }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:w-[320px] flex flex-col gap-6">
                            <img
                                alt="Top view of a person wearing ripped jeans typing on a laptop with a tablet beside them on a wooden table"
                                class="rounded-lg shadow-lg object-cover w-full h-auto"
                                height="240"
                                :src="personalImageUrl"
                                width="320"
                            />
                            <div
                                class="bg-[#f9fbff] border border-gray-200 rounded p-4 text-[9px] sm:text-[10px] text-gray-700"
                            >
                                <p class="font-semibold mb-2">Personal Information</p>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                    <p class="font-semibold">Location:</p>
                                    <p>{{ graduateData.personalInfo.location }}</p>
                                    <p class="font-semibold">Birthdate:</p>
                                    <p>{{ graduateData.personalInfo.birthdate }}</p>
                                    <p class="font-semibold">Gender:</p>
                                    <p>{{ graduateData.personalInfo.gender }}</p>
                                    <p class="font-semibold">Ethnicity:</p>
                                    <p>{{ graduateData.personalInfo.ethnicity }}</p>
                                    <p class="font-semibold">Graduated:</p>
                                    <p>{{ graduateData.personalInfo.graduated }}</p>
                                    <p class="font-semibold">School:</p>
                                    <p>{{ graduateData.personalInfo.school }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Education Section -->
                <section id="education" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Education
                        </h2>
                        <button @click="toggleSection('education')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.education">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.education ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.education" class="transition-all duration-500 ease-in-out flex flex-col lg:flex-row gap-8">
                        <div class="lg:flex-1 flex flex-col gap-6">
                            <div v-for="(edu, index) in graduateData.education" :key="index" class="flex gap-4">
                                <img
                                    alt="Graduation caps being thrown in the air in front of a building with windows"
                                    class="rounded shadow object-cover w-28 h-28 flex-shrink-0"
                                    height="120"
                                    :src="educationImageUrl"
                                    width="120"
                                />
                                <div class="flex-1 text-[9px] sm:text-[10px] text-gray-800">
                                    <p class="font-semibold text-[10px] sm:text-xs mb-0.5 text-blue-700">
                                        {{ edu.degree }}
                                    </p>
                                    <p class="mb-1 font-semibold text-[9px] sm:text-[10px]">
                                        {{ edu.institution }}
                                    </p>
                                    <p
                                        class="mb-1 text-[8px] sm:text-[9px] font-semibold inline-block bg-[#d9eaff] text-[#1a4dbb] rounded px-1.5 py-0.5"
                                    >
                                        {{ edu.specialization }}
                                    </p>
                                    <p class="mb-2 text-[8px] sm:text-[9px] leading-tight">
                                        {{ edu.description }}
                                    </p>
                                    <p class="font-semibold mb-1 text-[9px] sm:text-[10px]">
                                        Relevant Coursework
                                    </p>
                                    <div
                                        class="flex flex-wrap gap-1 text-[7px] sm:text-[8px] text-gray-600"
                                    >
                                        <span v-for="(course, courseIndex) in edu.coursework" :key="courseIndex" class="border border-gray-300 rounded px-1.5 py-0.5">
                                            {{ course }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="text-[8px] sm:text-[9px] text-gray-500 whitespace-nowrap self-start"
                                >
                                    {{ edu.period }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="lg:w-[280px] bg-[#f9fbff] border border-gray-200 rounded p-4 text-[9px] sm:text-[10px] text-gray-700"
                        >
                            <p class="font-semibold mb-2">Education Summary</p>
                            <p class="mb-3 leading-tight text-[8px] sm:text-[9px]">
                                {{ graduateData.educationSummary }}
                            </p>
                            <p class="font-semibold mb-1">Key Academic Achievements</p>
                            <ul class="list-disc list-inside text-[8px] sm:text-[9px] space-y-1">
                                <li v-for="(achievement, index) in graduateData.academicAchievements" :key="index">
                                    {{ achievement }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
                
                <!-- Skills & Expertise Section -->
                <section id="skills" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Skills & Expertise
                        </h2>
                        <button @click="toggleSection('skills')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.skills">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.skills ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.skills" class="transition-all duration-500 ease-in-out flex flex-col gap-6">
                        <!-- Skill Categories -->
                        <div>
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-2">Skill Categories</h3>
                            <div class="space-y-2">
                                <div v-for="(category, categoryName) in skillsByCategory" :key="categoryName" class="mb-3">
                                    <div class="bg-gray-100 rounded-md p-2 mb-1">
                                        <p class="text-[10px] sm:text-xs font-semibold">{{ categoryName }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div v-for="skill in category" :key="skill.name" class="flex items-center">
                                            <p class="text-[9px] sm:text-[10px] w-24 sm:w-32">{{ skill.name }}</p>
                                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full" 
                                                    :class="{
                                                        'bg-green-500': skill.proficiency === 'Expert',
                                                        'bg-blue-500': skill.proficiency === 'Advanced',
                                                        'bg-yellow-500': skill.proficiency === 'Intermediate',
                                                        'bg-orange-400': skill.proficiency === 'Beginner'
                                                    }"
                                                    :style="{
                                                        width: `${(skill.years / 7) * 100}%`
                                                    }"
                                                ></div>
                                            </div>
                                            <p class="text-[8px] sm:text-[9px] text-gray-500 ml-2 w-10 text-right">{{ skill.years }} years</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Proficiency Legend -->
                        <div>
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-2">Proficiency Legend</h3>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="(level, index) in graduateData.proficiencyLegend" :key="index" class="flex items-center gap-1.5">
                                    <div :class="[level.color, 'w-4 h-2 rounded-full']"></div>
                                    <p class="text-[8px] sm:text-[9px] font-semibold">{{ level.level }}</p>
                                    <p class="text-[8px] sm:text-[9px] text-gray-500">({{ level.description }})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Work Experience Section -->
                <section id="workExperience" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Work Experience
                        </h2>
                        <button @click="toggleSection('workExperience')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.workExperience">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.workExperience ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.workExperience" class="transition-all duration-500 ease-in-out flex flex-col lg:flex-row gap-8">
                        <div class="lg:flex-1">
                            <div class="space-y-8">
                                <div v-for="(job, index) in graduateData.workExperience" :key="index" class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-[11px] sm:text-xs font-semibold text-blue-700">{{ job.title }}</h3>
                                                <p class="text-[9px] sm:text-[10px] font-medium">{{ job.company }}</p>
                                            </div>
                                            <p class="text-[8px] sm:text-[9px] text-gray-500">{{ job.period }}</p>
                                        </div>
                                        <div class="mt-1">
                                            <div class="inline-block bg-blue-100 text-blue-800 rounded-full px-2 py-0.5 text-[7px] sm:text-[8px] font-medium">
                                                {{ job.location }}
                                            </div>
                                        </div>
                                        <p class="mt-2 text-[8px] sm:text-[9px] text-gray-700 leading-relaxed">
                                            {{ job.description }}
                                        </p>
                                        <div class="mt-2">
                                            <p class="text-[8px] sm:text-[9px] font-semibold mb-1">Key Technologies</p>
                                            <div class="flex flex-wrap gap-1">
                                                <span 
                                                    v-for="(tech, techIndex) in job.keyTechnologies" 
                                                    :key="techIndex"
                                                    class="inline-block bg-gray-100 rounded px-1.5 py-0.5 text-[7px] sm:text-[8px] text-gray-700"
                                                >
                                                    {{ tech }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:w-[280px]">
                            <div class="bg-[#f9fbff] border border-gray-200 rounded p-4">
                                <h3 class="text-[11px] sm:text-xs font-semibold mb-3">Career Highlights</h3>
                                <ul class="space-y-2">
                                    <li v-for="(highlight, index) in graduateData.careerHighlights" :key="index" class="flex items-start gap-2">
                                        <div class="w-4 h-4 rounded-full bg-blue-500 flex-shrink-0 flex items-center justify-center mt-0.5">
                                            <i class="fas fa-check text-white text-[6px] sm:text-[7px]"></i>
                                        </div>
                                        <p class="text-[8px] sm:text-[9px] text-gray-700">{{ highlight }}</p>
                                    </li>
                                </ul>
                                <div class="mt-4">
                                    <button class="bg-blue-500 text-white text-[8px] sm:text-[9px] rounded px-3 py-1 w-full">
                                        Download Resume
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Projects Section -->
                <section id="projects" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Projects
                        </h2>
                        <button @click="toggleSection('projects')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.projects">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.projects ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.projects" class="transition-all duration-500 ease-in-out grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="(project, index) in graduateData.projects" :key="index" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                            <div class="h-48 bg-gray-200 relative">
                                <img 
                                    :src="project.image" 
                                    :alt="project.title" 
                                    class="w-full h-full object-cover"
                                    onerror="this.src='https://via.placeholder.com/400x200?text=Project+Image'"
                                />
                                <div class="absolute top-2 right-2 bg-white text-gray-700 text-[7px] sm:text-[8px] rounded px-1.5 py-0.5">
                                    {{ project.period }}
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="text-[11px] sm:text-xs font-semibold text-blue-700 mb-1">{{ project.title }}</h3>
                                <p class="text-[8px] sm:text-[9px] text-gray-700 mb-3 line-clamp-3">
                                    {{ project.description }}
                                </p>
                                <div class="mb-3">
                                    <p class="text-[8px] sm:text-[9px] font-semibold mb-1">Key Accomplishments</p>
                                    <ul class="list-disc list-inside text-[7px] sm:text-[8px] text-gray-600 space-y-0.5">
                                        <li v-for="(accomplishment, accIndex) in project.keyAccomplishments" :key="accIndex">
                                            {{ accomplishment }}
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-[8px] sm:text-[9px] font-semibold mb-1">Technologies</p>
                                    <div class="flex flex-wrap gap-1">
                                        <span 
                                            v-for="(tag, tagIndex) in project.tags" 
                                            :key="tagIndex"
                                            class="inline-block bg-gray-100 rounded px-1.5 py-0.5 text-[7px] sm:text-[8px] text-gray-700"
                                        >
                                            {{ tag }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2 border-t border-gray-100">
                                    <span class="text-blue-500 text-[8px] sm:text-[9px] hover:text-blue-700 transition">
                                        <i class="fas fa-external-link-alt mr-1"></i> View Project
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Certifications & Achievements Section -->
                <section id="certifications" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Certifications & Achievements
                        </h2>
                        <button @click="toggleSection('certifications')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.certifications">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.certifications ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.certifications" class="transition-all duration-500 ease-in-out flex flex-col lg:flex-row gap-8">
                        <!-- Certifications -->
                        <div class="lg:flex-1">
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-4">Certifications</h3>
                            <div class="space-y-6">
                                <div v-for="(cert, index) in graduateData.certifications" :key="index" class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                            <i class="fas fa-certificate"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-[11px] sm:text-xs font-semibold text-blue-700">{{ cert.name }}</h4>
                                                <p class="text-[9px] sm:text-[10px] font-medium">{{ cert.issuer }}</p>
                                            </div>
                                            <p class="text-[8px] sm:text-[9px] text-gray-500">{{ cert.period }}</p>
                                        </div>
                                        <p class="mt-1 text-[8px] sm:text-[9px] text-gray-700 leading-relaxed">
                                            {{ cert.description }}
                                        </p>
                                        <div class="mt-2">
                                            <span 
                                                class="text-blue-500 text-[8px] sm:text-[9px] hover:text-blue-700 transition"
                                            >
                                                <i class="fas fa-external-link-alt mr-1"></i> Verify Credential
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Achievements -->
                        <div class="lg:w-[320px]">
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-4">Achievements</h3>
                            <div class="space-y-4">
                                <div v-for="(achievement, index) in graduateData.achievements" :key="index" class="bg-[#f9fbff] border border-gray-200 rounded p-3">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-[10px] sm:text-[11px] font-semibold text-blue-700">{{ achievement.title }}</h4>
                                        <p class="text-[8px] sm:text-[9px] text-gray-500">{{ achievement.date }}</p>
                                    </div>
                                    <p class="text-[9px] sm:text-[10px] text-gray-600 mt-1">{{ achievement.event }}</p>
                                    <p class="text-[8px] sm:text-[9px] text-gray-700 mt-2 leading-relaxed">
                                        {{ achievement.description }}
                                    </p>
                                    <div v-if="achievement.hasVerification" class="mt-2 flex items-center gap-1">
                                        <i class="fas fa-check-circle text-green-500 text-[8px] sm:text-[9px]"></i>
                                        <span class="text-[7px] sm:text-[8px] text-gray-500">Verified</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Testimonials Section -->
                <section id="testimonials" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Testimonials
                        </h2>
                        <button @click="toggleSection('testimonials')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.testimonials">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.testimonials ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.testimonials" class="transition-all duration-500 ease-in-out grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="(testimonial, index) in graduateData.testimonials" :key="index" class="bg-[#f9fbff] border border-gray-200 rounded-lg p-5 relative">
                            <div class="text-blue-500 text-4xl absolute -top-3 -left-2 opacity-20">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-gray-700 italic leading-relaxed mb-4 relative z-10">
                                {{ testimonial.quote }}
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                    <span class="text-[10px] sm:text-[11px] font-bold">{{ testimonial.author.charAt(0) }}</span>
                                </div>
                                <div>
                                    <p class="text-[9px] sm:text-[10px] font-semibold">{{ testimonial.author }}</p>
                                    <p class="text-[8px] sm:text-[9px] text-gray-500">{{ testimonial.position }}</p>
                                    <p class="text-[7px] sm:text-[8px] text-gray-400">{{ testimonial.company }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Contact Me Section -->
                <section id="contact" class="mt-16">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-center font-semibold text-sm sm:text-base underline underline-offset-4"
                        >
                            Contact Me
                        </h2>
                        <button @click="toggleSection('contact')" class="text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-300 text-xs sm:text-sm">
                            <span v-if="expandedSections.contact">Hide Details</span>
                            <span v-else>Show Details</span>
                            <i class="fas" :class="expandedSections.contact ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                    <div v-show="expandedSections.contact" class="transition-all duration-500 ease-in-out flex flex-col lg:flex-row gap-8">
                        <!-- Get In Touch -->
                        <div class="lg:w-[280px]">
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-4">Get In Touch</h3>
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 flex-shrink-0">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] font-semibold">Location</p>
                                        <p class="text-[8px] sm:text-[9px] text-gray-600">{{ graduateData.contactInfo.location }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 flex-shrink-0">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] font-semibold">Email</p>
                                        <p class="text-[8px] sm:text-[9px] text-gray-600">{{ graduateData.contactInfo.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 flex-shrink-0">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] font-semibold">Phone</p>
                                        <p class="text-[8px] sm:text-[9px] text-gray-600">{{ graduateData.contactInfo.phone }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 flex-shrink-0">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] font-semibold">Website</p>
                                        <p class="text-[8px] sm:text-[9px] text-gray-600">{{ graduateData.contactInfo.website }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <p class="text-[9px] sm:text-[10px] font-semibold mb-2">Connect With Me</p>
                                <div class="flex gap-2">
                                    <span 
                                        v-for="(profile, index) in graduateData.contactInfo.socialProfiles" 
                                        :key="index"
                                        class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white hover:bg-blue-600 transition relative group cursor-pointer"
                                    >
                                        <i :class="profile.icon"></i>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap pointer-events-none">
                                            {{ profile.name }}
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Send Me a Message -->
                        <div class="lg:flex-1">
                            <h3 class="text-[11px] sm:text-xs font-semibold mb-4">Send Me a Message</h3>
                            <form @submit.prevent="submitForm" class="space-y-4">
                                <div>
                                    <label class="block text-[9px] sm:text-[10px] font-medium text-gray-700 mb-1">Your Name</label>
                                    <input 
                                        type="text" 
                                        v-model="contactForm.name" 
                                        :class="['w-full px-3 py-2 text-[9px] sm:text-[10px] border rounded focus:outline-none focus:ring-1', getFieldClass('name')]"
                                        placeholder="John Doe"
                                        @blur="validateField('name')"
                                    >
                                    <p v-if="formTouched.name && formErrors.name" class="text-red-500 text-[8px] sm:text-[9px] mt-1">{{ formErrors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-[9px] sm:text-[10px] font-medium text-gray-700 mb-1">Your Email</label>
                                    <input 
                                        type="email" 
                                        v-model="contactForm.email" 
                                        :class="['w-full px-3 py-2 text-[9px] sm:text-[10px] border rounded focus:outline-none focus:ring-1', getFieldClass('email')]"
                                        placeholder="john@example.com"
                                        @blur="validateField('email')"
                                    >
                                    <p v-if="formTouched.email && formErrors.email" class="text-red-500 text-[8px] sm:text-[9px] mt-1">{{ formErrors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-[9px] sm:text-[10px] font-medium text-gray-700 mb-1">Subject</label>
                                    <input 
                                        type="text" 
                                        v-model="contactForm.subject" 
                                        :class="['w-full px-3 py-2 text-[9px] sm:text-[10px] border rounded focus:outline-none focus:ring-1', getFieldClass('subject')]"
                                        placeholder="Job Opportunity"
                                        @blur="validateField('subject')"
                                    >
                                    <p v-if="formTouched.subject && formErrors.subject" class="text-red-500 text-[8px] sm:text-[9px] mt-1">{{ formErrors.subject }}</p>
                                </div>
                                <div>
                                    <label class="block text-[9px] sm:text-[10px] font-medium text-gray-700 mb-1">Message</label>
                                    <textarea 
                                        v-model="contactForm.message" 
                                        rows="4"
                                        :class="['w-full px-3 py-2 text-[9px] sm:text-[10px] border rounded focus:outline-none focus:ring-1', getFieldClass('message')]"
                                        placeholder="Your message here"
                                        @blur="validateField('message')"
                                    ></textarea>
                                    <p v-if="formTouched.message && formErrors.message" class="text-red-500 text-[8px] sm:text-[9px] mt-1">{{ formErrors.message }}</p>
                                </div>
                                <div v-if="submitSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-[9px] sm:text-[10px]">
                                    <i class="fas fa-check-circle mr-1"></i> Your message has been sent successfully!
                                </div>
                                <div>
                                    <button 
                                        type="submit" 
                                        class="w-full bg-blue-500 text-white text-[9px] sm:text-[10px] py-2 px-4 rounded hover:bg-blue-600 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed"
                                        :disabled="isSubmitting"
                                    >
                                        <span v-if="isSubmitting">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Sending...
                                        </span>
                                        <span v-else>
                                            <i class="fas fa-paper-plane mr-1"></i> Send Message
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
                
                <!-- Modal Component with Animation -->
                <TransitionRoot appear :show="isModalOpen" as="template">
                    <Dialog as="div" @close="closeModal" class="fixed inset-0 z-50 overflow-y-auto" @click="handleOutsideClick">
                        <div class="min-h-screen px-4 text-center">
                            <TransitionChild
                                as="template"
                                enter="ease-out duration-300"
                                enter-from="opacity-0"
                                enter-to="opacity-100"
                                leave="ease-in duration-200"
                                leave-from="opacity-100"
                                leave-to="opacity-0"
                            >
                                <DialogPanel class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop" />
                            </TransitionChild>

                            <!-- This element is to trick the browser into centering the modal contents. -->
                            <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
                            
                            <TransitionChild
                                as="template"
                                enter="ease-out duration-300"
                                enter-from="opacity-0 scale-95"
                                enter-to="opacity-100 scale-100"
                                leave="ease-in duration-200"
                                leave-from="opacity-100 scale-100"
                                leave-to="opacity-0 scale-95"
                            >
                                <DialogPanel class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg max-h-[90vh] overflow-y-auto">
                                    <div class="flex justify-between items-center mb-4">
                                        <DialogTitle as="h3" class="text-lg font-semibold">Information</DialogTitle>
                                        <button @click="closeModal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div v-html="modalContent"></div>
                                    <div class="mt-6 flex justify-end">
                                        <button 
                                            @click="closeModal"
                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Close
                                        </button>
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </Dialog>
                </TransitionRoot>
            </main>
        </div>
    </div>
</template>