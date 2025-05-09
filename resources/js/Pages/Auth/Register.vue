<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber';
import { useFormattedTelephoneNumber } from '@/Composables/useFormattedTelephoneNumber';
import { usePasswordCriteria } from '@/Composables/usePasswordCriteria';
import { defineProps, onMounted, ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    insti_users: Array,
    school_years: Array,
    programs: Array,
});

console.log(props);

const isPasswordFocused = ref(false);
const showModal = ref(false);
const showVerificationModal = ref(false); // New modal for verification code
const verificationForm = useForm({
    email: '',
    verification_code: '',
});


// Define the form with additional fields for user types
const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    gender: '',
    dob: '',
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
    company_hr_first_name: '',
    company_hr_last_name: '',
    institution_type: '',
    institution_name: '',
    institution_address: '',
    institution_president_last_name: '',
    institution_president_first_name: '',
    institution_career_officer_first_name: '',
    institution_career_officer_last_name: '',
    graduate_school_graduated_from: '',
    graduate_program_completed: '',
    graduate_year_graduated: '',
    graduate_first_name: '',
    graduate_middle_initial: '',
    graduate_last_name: '',
    employment_status: '',
    current_job_title: '',
});

// Determine user role based on URL
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

function handleEmploymentStatus() {
  if(form.employment_status === 'Unemployed') {
    form.current_job_title = 'N/A';
  }
}


// Computed properties for filtering programs and school years
const filteredPrograms = computed(() => {
    return props.programs.filter(
        (program) => Number(program.institution_id) === Number(form.graduate_school_graduated_from)
    );
});

const filteredSchoolYears = computed(() => {
    return props.school_years.filter(
        (year) => Number(year.institution_id) === Number(form.graduate_school_graduated_from)
    );
});

// Utility functions
const { formattedMobileNumber: contactNumber } = useFormattedMobileNumber(form, 'contact_number');
const { formattedMobileNumber: companyContactNumber } = useFormattedMobileNumber(form, 'company_contact_number');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');
const { passwordCriteria } = usePasswordCriteria(form);

const maxDate = ref(new Date().toISOString().split('T')[0]);
const minDate = ref('1900-01-01');

// Handle form submission
const submit = () => {
    let routeName;
    if (form.role === 'company') {
        routeName = 'register.company.store';
    } else if (form.role === 'institution') {
        routeName = 'register.institution.store';
    } else if (form.role === 'graduate') {
        routeName = 'register.graduate.store';
    } else {
        console.error('Unknown role:', form.role);
        return;
    }

    console.log('Form Data:', form);
    form.post(route(routeName), {
        onFinish: () => {
            console.log("Form submission finished");
            console.log(form.errors);
            form.reset('password', 'password_confirmation');
        },
        onSuccess: () => {
            showVerificationModal.value = true; // Show verification modal
            verificationForm.email = form.email;
        },
    });
};

const verifyCode = () => {
    verificationForm.post(route('verify.email'), {
        onSuccess: () => {
            showVerificationModal.value = false;
            Inertia.visit(route('login')); // Redirect to login after successful verification
        },
    });
};

// Redirect to login after successful registration
const redirectToLogin = () => {
    Inertia.visit(route('login'));
};
</script>


<template>

    <Head title="Register" />

    <AuthenticationCard>


        <!-- UPDATE 04/04 Putting form inside the template for registrtion form layout -->
        <template #registerForm>



            <form @submit.prevent="submit" autocomplete="off">
                <!-- Company Fields -->
                <div v-if="form.role === 'company'" class="flex space-x-12  ">
                    <div
                        class="flex-1 flex flex-col items-start justify-center p-6 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg shadow-lg text-white ">
                        <AuthenticationCardLogo class="mx-20 fill-white-100" />
                        <h2 class="text-6xl font-bold">Welcome to</h2>
                        <h1 class="text-7xl font-extrabold">Pathway</h1>
                        <p class="mt-4 text-sm">
                            Join us in shaping the future of education. We are excited to partner with you in this
                            journey.
                        </p>
                    </div>

                    <div class="flex-1 space-y-2">
                        <h2 class="text-xl font-semibold text-gray-900">Company Information</h2>
                        <p class="text-sm text-gray-600">Provide key details about your company.</p>

                        <div class="grid grid-cols-1 gap-4">
                            <!-- Company Name -->
                            <div>
                                <div class="flex items-center gap-1 col-span-6 sm:col-span-4">
                                    <InputLabel for="company_name" value="Company Name" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <div>
                                    <TextInput id="company_name" v-model="form.company_name" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.company_name" />
                                </div>
                            </div>

                            <!-- Company Address -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_street_address" value="Street Address" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_street_address" v-model="form.company_street_address"
                                            type="text"
                                            class="mt-1 mb-4 block w-full l border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-1" :message="form.errors.company_street_address" />
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_brgy" value="Barangay" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_brgy" v-model="form.company_brgy" type="text"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-1" :message="form.errors.company_brgy" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-5 gap-4">
                                <div class="col-span-2">
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_city" value="City" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_city" v-model="form.company_city" type="text"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-1" :message="form.errors.company_city" />
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_province" value="Province" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_province" v-model="form.company_province" type="text"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-1" :message="form.errors.company_province" />
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_zip_code" value="ZIP Code" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-1" :message="form.errors.company_zip_code" />
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200"></div>
                            <h2 class="text-xl font-semibold text-gray-900">Company Contact Information</h2>
                            <p class="text-sm text-gray-600 ">
                                Provide the official contact details of your company.
                            </p>
                            <!-- Company Email -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="company_email" value="Email Address" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <div>
                                    <TextInput id="company_email" v-model="form.company_email" type="email"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.company_email" />
                                </div>
                            </div>

                            <!-- Mobile and Telephone Number -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div>
                                        <div class="flex items-center gap-1">
                                            <InputLabel for="company_contact_number" value="Mobile Number" />
                                            <span class="text-red-500">*</span>
                                        </div>
                                        <TextInput id="company_contact_number" v-model="companyContactNumber"
                                            v-mask="'# (###) ###-####'" type="text"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.company_contact_number" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel for="telephone_number" value="Telephone Number" />
                                    <TextInput id="telephone_number" v-model="formattedTelephoneNumber" type="text"
                                        :placeholder="formattedTelephoneNumber === '(##) ###-####' ? '(02) 123-5678' : '(0XX) XXX-XXXX'"
                                        class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg" />
                                    <InputError class="mt-2" :message="form.errors.telephone_number" />
                                </div>
                            </div>

                            <div class="border-t border-gray-200"></div>
                            <h2 class="text-xl font-semibold text-gray-900">Human Resource Officer Personal Information
                            </h2>
                            <p class="text-sm text-gray-600 ">
                                Provide the personal details of your HR.
                            </p>

                            <!-- HR's First and Last Name -->
                            <div class="grid grid-cols-2 gap-x-4">
                                <!-- First Name -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_hr_first_name" value="First Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_hr_first_name" v-model="form.company_hr_first_name"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.company_hr_first_name" />
                                    </div>
                                </div>
                                <div> <!-- Last Name -->
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_hr_last_name" value="Last Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_hr_last_name" v-model="form.company_hr_last_name"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.company_hr_last_name" />
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Gender -->
                                <div>
                                    <div class="flex items-center gap-x-4">
                                        <InputLabel for="gender" value="Gender" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <select id="gender" v-model="form.gender"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <InputError class="" :message="form.errors.gender" />
                                    </div>
                                </div>
                                <!-- Date of Birth -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="dob" value="Date of Birth" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="dob" v-model="form.dob" type="date" :max="maxDate" :min="minDate"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.dob" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-x-4">
                                <!-- HR Email -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="email" value="Email Address" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="email" v-model="form.email" type="email"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>
                                </div>

                                <!-- HR Contact Number -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="contact_number" value="Mobile Number" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="contact_number" v-model="contactNumber"
                                            v-mask="'# (###) ###-####'"
                                            :placeholder="contactNumber === '(##) ###-####' ? '(02) 1234-5678' : '(0XX) XXXX-XXXX'"
                                            type="text"
                                            class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                            required />
                                        <InputError class="mt-2" :message="form.errors.contact_number" />
                                    </div>
                                </div>
                            </div>

                            <!-- Set Password -->
                            <h2 class="text-lg font-semibold text-gray-900">Set Password</h2>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="password" value="Password" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div class="mb-2">
                                        <TextInput id="password" v-model="form.password" type="password"
                                            class="mt-1 block w-full" required @focus="isPasswordFocused = true"
                                            @blur="isPasswordFocused = false" />


                                    </div>

                                    <div v-if="isPasswordFocused && form.password"
                                        class="relative z-10 mt-2 p-3 bg-gray-800 text-white rounded-md w-64 text-sm shadow-lg">
                                        <p class="font-semibold text-gray-200">Password must meet the following:</p>
                                        <ul class="mt-1">
                                            <li :class="passwordCriteria.length ? 'text-green-400' : 'text-red-400'">
                                                ✔ Minimum 8 characters
                                            </li>
                                            <li
                                                :class="passwordCriteria.uppercaseLowercase ? 'text-green-400' : 'text-red-400'">
                                                ✔ Upper & lower case letters
                                            </li>
                                            <li :class="passwordCriteria.number ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one number
                                            </li>
                                            <li :class="passwordCriteria.symbol ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one special character (@$!%*?.)
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="password_confirmation" value="Confirm Password" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div class="mb-2">
                                        <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                            type="password" class="mt-1 mb-4 block w-full" required />
                                        <InputError class="mt-1" :message="form.errors.password" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Institution Fields UPDATED 04/04/2025
                        Separated the Password and Personal Information (Gender, and etc.)
                        so it won't affect Institution. Naka Global abi -->
                <div v-if="form.role === 'institution'" class="flex space-x-12">
                    <!-- Left Side: Welcome Section -->
                    <div
                        class="flex-1 flex flex-col items-start justify-center p-6 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg shadow-lg text-white">
                        <h2 class="text-6xl font-bold">Welcome to</h2>
                        <h1 class="text-7xl font-extrabold">Pathway</h1>
                        <p class="mt-4 text-sm">
                            Join us in shaping the future of education. We are excited to partner with you in this
                            journey.
                        </p>
                    </div>

                    <!-- Right Side: Institution Fields -->
                    <div class="flex-1 space-y-2">
                        <h2 class="text-xl font-semibold text-gray-900">Institution Information</h2>
                        <p class="text-sm text-gray-600">Provide key details about your institution.</p>

                        <div class="grid grid-cols-1 gap-4">
                            <!-- Institution Type -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="institution_type" value="Institution Type" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <select id="institution_type" v-model="form.institution_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm transition duration-300 ease-in-out transform hover:shadow-lg"
                                    required>
                                    <option value="college">College</option>
                                    <option value="university">University</option>
                                    <option value="institution">Institution</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.institution_type" />
                            </div>

                            <!-- College/University/Institution Name -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="institution_name" value="College/University/Institution Name" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <TextInput id="institution_name" v-model="form.institution_name" type="text"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                    required />
                                <InputError class="mt-2" :message="form.errors.institution_name" />
                            </div>

                            <!-- Institution Address -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="institution_address" value="Institution Address" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <TextInput id="institution_address" v-model="form.institution_address" type="text"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                    required />
                                <InputError class="mt-2" :message="form.errors.institution_address" />
                            </div>

                            <!-- Institution Email -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="email" value="Institution Email" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <TextInput id="email" v-model="form.email" type="email"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                    required />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <!-- Mobile and Telephone Number -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="contact_number" value="Mobile Number" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="contact_number" v-model="contactNumber" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.contact_number" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="telephone_number" value="Telephone Number" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="telephone_number" v-model="formattedTelephoneNumber" type="text"
                                        :placeholder="formattedTelephoneNumber === '(##) ###-####' ? '(02) 123-5678' : '(0XX) XXX-XXXX'"
                                        class="mt-1 mb-4 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg" />
                                    <InputError class="mt-2" :message="form.errors.telephone_number" />
                                </div>
                            </div>
                            

                            <!-- President's First and Last Name -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="institution_president_first_name"
                                            value="President First Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="institution_president_first_name"
                                        v-model="form.institution_president_first_name" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.institution_president_first_name" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="institution_president_last_name" value="President Last Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="institution_president_last_name"
                                        v-model="form.institution_president_last_name" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.institution_president_last_name" />
                                </div>
                            </div>

                            <!-- Career Officer's First and Last Name -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="institution_career_officer_first_name"
                                            value="Career Officer First Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="institution_career_officer_first_name"
                                        v-model="form.institution_career_officer_first_name" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2"
                                        :message="form.errors.institution_career_officer_first_name" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="institution_career_officer_last_name"
                                            value="Career Officer Last Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput id="institution_career_officer_last_name"
                                        v-model="form.institution_career_officer_last_name" type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                        required />
                                    <InputError class="mt-2"
                                        :message="form.errors.institution_career_officer_last_name" />
                                </div>
                            </div>

                            <!-- Set Password -->
                            <h2 class="text-lg font-semibold text-gray-900">Set Password</h2>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="password" value="Password" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div class="mb-2">
                                        <TextInput id="password" v-model="form.password" type="password"
                                            class="mt-1 block w-full" required @focus="isPasswordFocused = true"
                                            @blur="isPasswordFocused = false" />
                                        <InputError class="mt-1" :message="form.errors.password" />
                                    </div>

                                    <div v-if="isPasswordFocused && form.password"
                                        class="mt-2 p-3 bg-gray-800 text-white rounded-md w-64 text-sm shadow-lg">
                                        <p class="font-semibold text-gray-200">Password must meet the following:</p>
                                        <ul class="mt-1">
                                            <li :class="passwordCriteria.length ? 'text-green-400' : 'text-red-400'">
                                                ✔ Minimum 8 characters
                                            </li>
                                            <li
                                                :class="passwordCriteria.uppercaseLowercase ? 'text-green-400' : 'text-red-400'">
                                                ✔ Upper & lower case letters
                                            </li>
                                            <li :class="passwordCriteria.number ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one number
                                            </li>
                                            <li :class="passwordCriteria.symbol ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one special character (@$!%*?&)
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="password_confirmation" value="Confirm Password" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div class="mb-2">
                                        <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                            type="password" class="mt-1 mb-4 block w-full" required />
                                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Graduate Fields -->
                <div v-if="form.role === 'graduate'" class="flex space-x-12">
                    <div
                        class="flex-1 flex flex-col items-start justify-center p-6 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg shadow-lg text-white ">
                        <AuthenticationCardLogo class="mx-20 fill-white-100" />
                        <h2 class="text-6xl font-bold">Welcome to</h2>
                        <h1 class="text-7xl font-extrabold">Pathway</h1>
                        <p class="mt-4 text-sm">
                            Join us in shaping the future of education. We are excited to partner with you in this
                            journey.
                        </p>
                    </div>

                    <div class="flex-1 space-y-2">
                        <h2 class="text-xl font-semibold text-gray-900">Graduate Information</h2>
                        <p class="text-sm text-gray-600">Provide key details abot you.</p>
                        <!-- Graduate First Name -->
                        <div class="grid grid-cols-1 gap-4">

                            <InputLabel for="graduate_first_name" value="First Name" />
                            <TextInput id="graduate_first_name" v-model="form.graduate_first_name" type="text"
                                class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.graduate_first_name" />
                        </div>

                        <!-- Graduate Middle Initial -->
                        <InputLabel for="graduate_middle_initial" value="GMiddle Initial" />
                        <TextInput id="graduate_middle_initial" v-model="form.graduate_middle_initial" type="text"
                            class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.graduate_middle_initial" />

                        <!-- Graduate Last Name -->
                        <InputLabel for="graduate_last_name" value="Last Name" />
                        <TextInput id="graduate_last_name" v-model="form.graduate_last_name" type="text"
                            class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.graduate_last_name" />

                        <!-- Graduate Graduated From -->
                        <div class="mt-4">
                            <InputLabel for="graduate_school_graduated_from" value="School Graduated From" />
                            <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Select a School</option>
                                <option v-for="user in insti_users" :key="user.id" :value="user.id">
                                    {{ user.institution_name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.graduate_school_graduated_from" />
                        </div>

                        <!-- Program Completed -->
                        <div class="mt-4">
                            <InputLabel for="graduate_program_completed" value="Program Completed" />
                            <select id="graduate_program_completed" v-model="form.graduate_program_completed"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Select Program</option>
                                <option v-for="program in filteredPrograms" :key="program.id" :value="program.name">
                                    {{ program.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.graduate_program_completed" />
                        </div>

                        <!-- Year Graduated -->
                        <div class="mt-4">
                            <InputLabel for="graduate_year_graduated" value="Year Graduated" />
                            <select id="graduate_year_graduated" v-model="form.graduate_year_graduated"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Select Year</option>
                                <option v-for="year in filteredSchoolYears" :key="year.id"
                                    :value="year.school_year_range">
                                    {{ year.school_year_range }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.graduate_year_graduated" />

                        </div>
                        <div class="border-t border-gray-200"></div>
                        <h2 class="text-xl font-semibold text-gray-900">Graduate's Other Information
                        </h2>
                        <p class="text-sm text-gray-600 ">
                            Provide the other details.
                        </p>

                        <div class="grid grid-cols-2 gap-x-4">
                            <!-- Gender -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="gender" value="Gender" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <div>
                                    <select id="gender" v-model="form.gender" class="mt-1 mb-4 block w-full" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <InputError class="" :message="form.errors.gender" />
                                </div>
                            </div>
                            <!-- Date of Birth -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <InputLabel for="dob" value="Date of Birth" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <div>
                                    <TextInput id="dob" v-model="form.dob" type="date" class="mt-1 mb-4 block w-full"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.dob" />
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="email" value="Email Address" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div>
                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 mb-4 block w-full"
                                    required />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                        </div>

                        <!--  Contact Number -->
                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="contact_number" value="Mobile Number" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div>
                                <TextInput id="contact_number" v-model="contactNumber" type="text"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:shadow-lg"
                                    required />
                                <InputError class="mt-2" :message="form.errors.contact_number" />
                            </div>
                        </div>

                    <div>
                        <!-- Employment Status -->
                        <div class="flex items-center gap-1">
                            <InputLabel for="employment_status" value="Employment Status" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div>
                            <select id="employment_status" v-model="form.employment_status"
                                @change="handleEmploymentStatus" required class="w-full border rounded px-3 py-2">
                                <option value="" disabled>Select Employment Status</option>
                                <option value="Employed">Employed</option>
                                <option value="Underemployed">Underemployed</option>
                                <option value="Unemployed">Unemployed</option>
                            </select>
                            <InputError :message="form.errors.employment_status" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <!-- Current Job Title – conditionally shown -->
                        <div v-if="form.employment_status !== 'Unemployed'">
                            <div class="flex items-center gap-1">
                                <InputLabel for="current_job_title" value="Current Job Title" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div>
                            <TextInput id="current_job_title" v-model="form.current_job_title" type="text" required
                                class="w-full border rounded px-3 py-2" />
                            <InputError :message="form.errors.current_job_title" class="mt-1" />
                            </div>
                        </div>
                    </div>

                        <!-- Set Password -->
                        <h3 class="mt-6 mb-2 font-semibold">Set Password</h3>

                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="password" value="Password" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="mb-2">
                                <TextInput id="password" v-model="form.password" type="password"
                                    class="mt-1 block w-full" required @focus="isPasswordFocused = true"
                                    @blur="isPasswordFocused = false" />
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>

                            <!-- Password validation tooltip UPDATE 04/04-->
                            <div v-if="isPasswordFocused && form.password"
                                class="mt-2 p-3 bg-gray-800 text-white rounded-md w-64 text-sm shadow-lg">
                                <p class="font-semibold text-gray-200">Password must meet the following:</p>
                                <ul class="mt-1">
                                    <li :class="passwordCriteria.length ? 'text-green-400' : 'text-red-400'">
                                        ✔ Minimum 8 characters
                                    </li>
                                    <li
                                        :class="passwordCriteria.uppercaseLowercase ? 'text-green-400' : 'text-red-400'">
                                        ✔ Upper & lower case letters
                                    </li>
                                    <li :class="passwordCriteria.number ? 'text-green-400' : 'text-red-400'">
                                        ✔ At least one number
                                    </li>
                                    <li :class="passwordCriteria.symbol ? 'text-green-400' : 'text-red-400'">
                                        ✔ At least one special character (@$!%*?&)
                                    </li>
                                </ul>
                            </div>

                            <div class="flex items-center gap-1">
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="mb-2">
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    type="password" class="mt-1 mb-4 block w-full" required />
                                <InputError class="mt-1" :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>


                <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-12">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Register
                    </PrimaryButton>
                </div>



            </form>


        </template>
    </AuthenticationCard>


   <Modal v-if="showVerificationModal" :show="showVerificationModal" @close="redirectToLogin">
    <template #default>
        <div class="text-center">
            <h2 class="text-lg font-semibold">Verify Your Email</h2>
            <p class="mt-2 text-gray-600">
                A verification code has been sent to your email. Please enter the code below to verify your email address.
                The code will expire in 10 minutes.
            </p>
            <form @submit.prevent="verifyCode" class="mt-4">
                <div>
                    <InputLabel for="verification_code" value="Verification Code" />
                    <TextInput id="verification_code" v-model="verificationForm.verification_code" type="text" required />
                    <InputError :message="verificationForm.errors.verification_code" class="mt-2" />
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <PrimaryButton :class="{ 'opacity-25': verificationForm.processing }" :disabled="verificationForm.processing">
                        Verify
                    </PrimaryButton>
                    <button
                        type="button"
                        @click="resendCode"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Resend Verification Code
                    </button>
                </div>
            </form>
        </div>
    </template>
</Modal>

</template>
