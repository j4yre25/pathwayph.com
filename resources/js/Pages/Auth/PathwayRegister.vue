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
    role: '', // Set the role dynamically
    gender: '',
    dob: '', // date of birth
    contact_number: '',
    telephone_number: '',
    company_name: '',
    company_street_address: '',
    company_brgy: '',
    company_city: '',
    company_province: '',
    company_zip_code: '',
    company_email: '',
    company_contact_number: '',
    hr_first_name: '',
    hr_middle_name: '',
    hr_last_name: '',
    hr_email: '',
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
    } else if (path.includes('graduate')) {
        form.role = 'graduate';
    } else {
        form.role = 'company';
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
</script>

<template>

    <Head title="Sign Up" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <template #registerForm>
            <!-- User Level Selection -->
            <div v-if="currentStep === 'user-level'" class="p-8 max-w-md mx-auto">
                <h2 class="text-2xl font-medium text-gray-900 mb-1">Select User Level</h2>
                <p class="text-sm text-gray-600 mb-6">Choose the option that best describes you</p>

                <div class="space-y-4">
                    <!-- Graduate Option -->
                    <Link href="/register/graduate">
                    <div class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center"
                        :class="{ 'border-blue-500 bg-blue-50': selectedUserLevel === 'graduates' }">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-user-graduate text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Graduate</h3>
                            <p class="text-sm text-gray-600">For recent graduates seeking opportunities</p>
                        </div>
                    </div>
                    </Link>


                    <!-- Institution Option -->
                    <Link href="/register/institution">
                    <div class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center"
                        :class="{ 'border-blue-500 bg-blue-50': selectedUserLevel === 'institution' }">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-university text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Institution</h3>
                            <p class="text-sm text-gray-600">For schools, colleges and universities</p>
                        </div>
                    </div>
                    </Link>

                    <!-- Industry Option -->
                    <Link href="/register/company">

                    <div class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center"
                        :class="{ 'border-blue-500 bg-blue-50': selectedUserLevel === 'industry' }">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Industry</h3>
                            <p class="text-sm text-gray-600">For companies seeking qualified talent</p>
                        </div>
                    </div>
                    </Link>

                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <Link href="/login" class="text-blue-600 hover:underline">Sign In</Link>
                    </p>
                </div>
            </div>

            <!-- Graduate Registration Form -->
            <div v-else class="p-6 max-w-3xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <h2 class="text-2xl font-medium text-gray-900 mb-1">Graduate Information</h2>
                <p class="text-sm text-gray-600 mb-6">Provide key details about you.</p>

                <!-- Progress Tabs -->
                <div class="flex justify-between mb-8 border-b">
                    <div @click="currentStep = 'personal'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'personal' }">
                        <div
                            :class="[currentStep === 'personal' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-user"></i>
                        </div>
                        <span
                            :class="[currentStep === 'personal' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Personal</span>
                    </div>

                    <div @click="currentStep === 'education' || currentStep === 'contact' || currentStep === 'security' ? currentStep = 'education' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'education' }">
                        <div
                            :class="[currentStep === 'education' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span
                            :class="[currentStep === 'education' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Education</span>
                    </div>

                    <div @click="currentStep === 'contact' || currentStep === 'security' ? currentStep = 'contact' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'contact' }">
                        <div
                            :class="[currentStep === 'contact' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span
                            :class="[currentStep === 'contact' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Contact</span>
                    </div>

                    <div @click="currentStep === 'security' ? currentStep = 'security' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'security' }">
                        <div
                            :class="[currentStep === 'security' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span
                            :class="[currentStep === 'security' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Security</span>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <!-- Personal Information -->
                    <div v-if="currentStep === 'personal'" class="space-y-4">
                        <h3 class="text-lg font-medium">Personal Information</h3>
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
                        <div class="flex justify-end mt-6">
                            <PrimaryButton @click.prevent="goToNextStep" class="ml-4">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                    <div v-if="currentStep === 'education'" class="space-y-4">
                        <h3 class="text-lg font-medium">Education Information</h3>
                        <div>
                            <InputLabel for="graduate_school_graduated_from" value="School Graduated From" />
                            <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select your school</option>
                                <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}
                                </option>
                            </select>
                            <p v-if="loading" class="text-sm text-gray-500 mt-1">Loading schools...</p>
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
                            <p v-if="loading" class="text-sm text-gray-500 mt-1">Loading terms...</p>
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
                            <p v-if="loading" class="text-sm text-gray-500 mt-1">Loading programs...</p>
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
                            <p v-if="loading" class="text-sm text-gray-500 mt-1">Loading degrees...</p>
                            <InputError class="mt-2" :message="form.errors.degree" />
                        </div>
                        <div class="flex justify-between mt-6">
                            <button type="button" @click="goToPreviousStep"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Previous
                            </button>
                            <PrimaryButton @click.prevent="goToNextStep" class="ml-4">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                    <div v-if="currentStep === 'contact'" class="space-y-4">
                        <h3 class="text-lg font-medium">Contact Information</h3>
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
                        <div class="flex justify-between mt-6">
                            <button type="button" @click="goToPreviousStep"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Previous
                            </button>
                            <PrimaryButton @click.prevent="goToNextStep" class="ml-4">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                    <div v-if="currentStep === 'security'" class="space-y-4">
                        <h3 class="text-lg font-medium">Security</h3> <!-- Password -->
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                placeholder="Enter your password" required />
                            <p class="text-sm text-gray-600 mt-1">Password must be at least 8 characters with at least
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
                </form>
            </div>

            <!-- Institution -->
            <div v-else class="p-6 max-w-3xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <h2 class="text-2xl font-medium text-gray-900 mb-1">Institution Registration</h2>
                <p class="text-sm text-gray-600 mb-6">Register your institution detail</p>

                <!-- Progress Tabs -->
                <div class="flex justify-between mb-8 border-b">
                    <div @click="currentStep = 'institution'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'institution' }">
                        <div
                            :class="[currentStep === 'institution' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-building"></i>
                        </div>
                        <span
                            :class="[currentStep === 'institution' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Institution</span>
                    </div>

                    <div @click="currentStep = 'president'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'president' }">
                        <div
                            :class="[currentStep === 'president' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span
                            :class="[currentStep === 'president' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">President</span>
                    </div>

                    <div @click="currentStep = 'contact'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'contact' }">
                        <div
                            :class="[currentStep === 'contact' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span
                            :class="[currentStep === 'contact' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Contact</span>
                    </div>

                    <div @click="currentStep = 'security'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'security' }">
                        <div
                            :class="[currentStep === 'security' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span
                            :class="[currentStep === 'security' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Security</span>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div v-if="currentStep === 'institution'" class="space-y-4">
                        <h3 class="text-lg font-medium">Institution Detail</h3>
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
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition ease-in-out duration-150">
                                    Next
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="currentStep === 'president'" class="space-y-4">
                            <h3 class="text-lg font-medium">President Detail</h3>
                            <div v-if="currentStep === 'personal'" class="space-y-4">
                                <h3 class="text-lg font-medium">Personal Information</h3>
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
            <div v-else class="p-6 max-w-3xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <h2 class="text-2xl font-medium text-gray-900 mb-1">Company Registration</h2>
                <p class="text-sm text-gray-600 mb-6">Register your company and HR officer detail</p>

                <!-- Progress Tabs -->
                <div class="flex justify-between mb-8 border-b">
                    <div @click="currentStep = 'company'" class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'company' }">
                        <div
                            :class="[currentStep === 'company' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-user"></i>
                        </div>
                        <span
                            :class="[currentStep === 'company' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Personal</span>
                    </div>

                    <div @click="currentStep === 'contact' || currentStep === 'HR' || currentStep === 'security' ? currentStep = 'contact' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'contact' }">
                        <div
                            :class="[currentStep === 'contact' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-telephone"></i>
                        </div>
                        <span
                            :class="[currentStep === 'contact' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Contact</span>
                    </div>

                    <div @click="currentStep === 'HR' || currentStep === 'security' ? currentStep = 'HR' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'HR' }">
                        <div
                            :class="[currentStep === 'HR' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span :class="[currentStep === 'HR' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">HR
                            Information</span>
                    </div>

                    <div @click="currentStep === 'security' ? currentStep = 'security' : null"
                        class="flex flex-col items-center pb-4 cursor-pointer"
                        :class="{ 'border-b-2 border-blue-500': currentStep === 'security' }">
                        <div
                            :class="[currentStep === 'security' ? 'bg-blue-500 text-white' : 'bg-gray-200', 'rounded-full p-2']">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span
                            :class="[currentStep === 'security' ? 'text-blue-500' : 'text-gray-500', 'text-sm mt-1']">Security</span>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div v-if="currentStep === 'company'">
                        <h3 class="text-lg font-medium">Basic Information</h3>
                        <div>
                            <InputLabel for="company_name" value="Company Name" />
                            <TextInput id="company_name" v-model="form.company_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter company name" required />
                            <InputError class="mt-2" :message="form.errors.company_name" />
                        </div>

                        <h3 class="text-lg font-medium mt-4">Company Address</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="company_street_address" value="Street" />
                                <TextInput id="company_street_address" v-model="form.company_street_address" type="text"
                                    class="mt-1 block w-full" placeholder="Street" required />
                                <InputError class="mt-2" :message="form.errors.company_street_address" />
                            </div>
                            <div>
                                <InputLabel for="company_brgy" value="Barangay" />
                                <TextInput id="company_brgy" v-model="form.company_brgy" type="text"
                                    class="mt-1 block w-full" placeholder="Barangay" required />
                                <InputError class="mt-2" :message="form.errors.company_brgy" />
                            </div>
                            <div>
                                <InputLabel for="company_city" value="City" />
                                <TextInput id="company_city" v-model="form.company_city" type="text"
                                    class="mt-1 block w-full" placeholder="City" required />
                                <InputError class="mt-2" :message="form.errors.company_city" />
                            </div>
                            <div>
                                <InputLabel for="company_province" value="Province" />
                                <TextInput id="company_province" v-model="form.company_province" type="text"
                                    class="mt-1 block w-full" placeholder="Province" required />
                                <InputError class="mt-2" :message="form.errors.company_province" />
                            </div>
                            <div>
                                <InputLabel for="company_zip_code" value="ZIP Code" />
                                <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text"
                                    class="mt-1 block w-full" placeholder="ZIP Code" required />
                                <InputError class="mt-2" :message="form.errors.company_zip_code" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <InputLabel for="company_sector" value="Company Sector/Industry" />
                            <select id="company_sector" v-model="form.company_sector"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select sector</option>
                                <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.company_sector" />
                        </div>
                        <div class="flex justify-between mt-6">
                            <PrimaryButton type="button" @click="goToNextStep"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </Primarybutton>
                        </div>
                    </div>
                    <!-- Contact Information -->
                    <div v-if="currentStep === 'contact'">
                        <h3 class="text-lg font-medium">Contact Information</h3>
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                placeholder="Enter your email address" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="contact_number" value="Phone Number" />
                            <TextInput id="contact_number" v-model="form.contact_number" type="tel"
                                class="mt-1 block w-full" placeholder="Enter your phone number" required />
                            <InputError class="mt-2" :message="form.errors.contact_number" />
                        </div>
                        <div>
                            <InputLabel for="telephone_number" value="Telephone (Optional)" />
                            <TextInput id="telephone_number" v-model="form.telephone_number" type="tel"
                                class="mt-1 block w-full" placeholder="Enter your telephone number" required />
                            <InputError class="mt-2" :message="form.errors.telephone_number" />
                        </div>
                        <div class="flex justify-between mt-6">
                            <button type="button" @click="goToPreviousStep"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-arrow-left mr-2"></i> Previous
                            </button>
                            <PrimaryButton @click.prevent="goToNextStep" class="ml-4">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                    <!-- HR Information -->
                    <div v-if="currentStep === 'HR'">
                        <h3 class="text-lg font-medium">HR Information</h3>
                        <div>
                            <InputLabel for="hr_first_name" value="First Name" />
                            <TextInput id="hr_first_name" v-model="form.hr_first_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your first name" required />
                            <InputError class="mt-2" :message="form.errors.hr_first_name" />
                        </div>
                        <div>
                            <InputLabel for="hr_middle_name" value="Middle Name (optional)" />
                            <TextInput id="hr_middle_name" v-model="form.hr_middle_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your middle name" />
                            <InputError class="mt-2" :message="form.errors.hr_middle_name" />
                        </div>
                        <div>
                            <InputLabel for="hr_last_name" value="Last Name" />
                            <TextInput id="hr_last_name" v-model="form.hr_last_name" type="text"
                                class="mt-1 block w-full" placeholder="Enter your last name" required />
                            <InputError class="mt-2" :message="form.errors.hr_last_name" />
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
                        <div>
                            <InputLabel for="hr_email" value="HR Email" />
                            <TextInput id="hr_email" v-model="form.hr_email" type="email" class="mt-1 block w-full"
                                placeholder="Enter your HR email" required />
                            <InputError class="mt-2" :message="form.errors.hr_email" />
                        </div>
                        <div class="flex justify-between mt-6">
                            <button type="button" @click="goToPreviousStep"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-arrow-left mr-2"></i> Previous
                            </button>
                            <PrimaryButton @click.prevent="goToNextStep" class="ml-4">
                                Next
                                <i class="fas fa-arrow-right ml-2"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                    <!-- Security -->
                    <div v-if="currentStep === 'security'">
                        <h3 class="text-lg font-medium">Security</h3>
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                placeholder="Enter your password" required />
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
                </form>
            </div>
        </template>
    </AuthenticationCard>
</template>