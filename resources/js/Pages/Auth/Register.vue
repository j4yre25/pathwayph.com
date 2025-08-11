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
        currentStep.value = 'graduate';
    } else if (path.includes('company')) {
        form.role = 'company';
        currentStep.value = 'company';
    } else {
        currentStep.value = 'user-level';
        console.error('Unknown role:', path);
    }
    console.log('Current Role:', form.role);
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
        form.role = 'graduate';
        currentStep.value = 'graduate';
    } else if (level === 'institution') {
        form.role = 'institution';
        currentStep.value = 'institution';
    } else if (level === 'industry') {
        form.role = 'company';
        currentStep.value = 'company';
    }
}

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
                    <div @click="selectUserLevel('graduates')" class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-user-graduate text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Graduate</h3>
                            <p class="text-sm text-gray-600">For recent graduates seeking opportunities</p>
                        </div>
                    </div>
                    <!-- Institution Option -->
                    <div @click="selectUserLevel('institution')" class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-university text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Institution</h3>
                            <p class="text-sm text-gray-600">For schools, colleges and universities</p>
                        </div>
                    </div>
                    <!-- Industry Option -->
                    <div @click="selectUserLevel('industry')" class="p-4 border rounded-lg hover:border-blue-500 cursor-pointer transition-all flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium">Industry</h3>
                            <p class="text-sm text-gray-600">For companies seeking qualified talent</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <Link href="/login" class="text-blue-600 hover:underline">Sign In</Link>
                    </p>
                </div>
            </div>

            <!-- Graduate Registration Form -->
            <div v-else-if="currentStep === 'graduate'" class="p-6 max-w-3xl mx-auto">
                <button @click="currentStep = 'user-level'"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h3 class="text-lg font-medium">Graduate Registration</h3>
                <form @submit.prevent="submitGraduateAccount">
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

            <!-- Institution Registration Form -->
            <div v-else-if="currentStep === 'institution'" class="p-6 max-w-3xl mx-auto">
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

            <!-- Company Registration Form -->
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
        </template>
    </AuthenticationCard>
</template>