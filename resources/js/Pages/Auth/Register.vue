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

const submitInstitutionAccount = () => {
    form.post(route('register.institution.store'), {
        onSuccess: () => {
            // Redirect to information section after successful registration
            window.location.href = route('institution.information');
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

                <!-- Institution Registration Step 1 -->
                <div v-if="currentStep === 'institution'">
                    <h3 class="text-lg font-medium">Institution Registration</h3>
                    <form @submit.prevent="submitInstitutionAccount">
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