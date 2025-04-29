<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed } from 'vue';
import { MaskDirective } from 'vue-the-mask';
import { onMounted, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';


const props = defineProps ({
    insti_users: Array,
    year_graduated: Array,
 
})

console.log(props)



// Define the form with additional fields for user types
const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    role: '', // Set the role dynamically
    company_name: '',
    company_street_address: '',
    company_brgy: '',
    company_city: '',
    company_province: '',
    company_zip_code: '',
    company_email: '',
    company_contact_number: '',
    company_telephone_number: '',
    company_hr_full_name: '',
    company_hr_gender: '',
    company_hr_dob: '',
    company_hr_contact_number: '',
    institution_type: '',
    institution_name: '',
    institution_address: '',
    institution_contact_number: '',
    institution_president_last_name: '',
    institution_president_first_name: '',
    institution_career_officer_first_name: '',
    graduate_school_graduated_from: '',
    graduate_program_completed: '',
    graduate_year_graduated: '',
    graduate_first_name: '',
    graduate_middle_initial: '',
    graduate_last_name: '',
});


onMounted(async () => {
    if (!props.initialUsers || props.initialUsers.length === 0) {
        try {
            const response = await axios.get('/users');
            users.value = response.data;
        } catch (error) {
            console.error('Error fetching users:', error);
        }
    }
});

onMounted(() => {
    
    const path = window.location.pathname;
    if (path.includes('institution')) {
        form.role = 'institution';
    } else if (path.includes('graduate')) {
        form.role = 'graduate';
    } else {
        form.role = 'company'; // Default to company if not specified
    }
    console.log('Current Role:', form.role);
});

console.log(form.role);

// Format the company contact number
const formattedContactNumber = computed({
    get: () => {
        let rawNumber = form.company_contact_number.replace(/\D/g, ""); // Remove non-numeric characters
        
        // Ensure only the first 10 digits are considered
        if (rawNumber.length > 10) {
            rawNumber = rawNumber.slice(0, 10);
        }

        // Ensure that the displayed format is always "+63 XXX XXX XXXX"
        return `+63 ${rawNumber.replace(/(\d{3})(\d{3})(\d{4})/, "$1 $2 $3")}`.trim();
    },
    set: (value) => {
        // Remove non-numeric characters except for numbers
        let rawValue = value.replace(/\D/g, "");

        // Ensure only the last 10 digits are stored
        if (rawValue.startsWith("63")) {
            rawValue = rawValue.slice(2);
        }

        if (rawValue.startsWith("0")) {
            rawValue = rawValue.slice(1);
        }

        if (rawValue.length > 10) {
            rawValue = rawValue.slice(0, 10);
        }

        form.company_contact_number = rawValue;
    },
});

// Handle form submission
const submit = () => {
    // Determine the route based on the role
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

    form.post(route(routeName), {
        onFinish: () => {
            console.log("Form submission finished");
            console.log(form.errors);
            form.reset('password', 'password_confirmation');
        },
        onSuccess: () => {
            Inertia.visit(route('login'));
        },
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit" autocomplete="off">
    
 <!-- Graduate Fields -->
 <div v-if="form.role === 'graduate'" class="mt-4">
                <!-- Graduate First Name -->
                <InputLabel for="graduate_first_name" value="Graduate First Name" />
                <TextInput
                    id="graduate_first_name"
                    v-model="form.graduate_first_name"
                    type="text"
                    class="mt-1 block w-full"
                    required/>
                <InputError class="mt-2" :message="form.errors.graduate_first_name" />

                <!-- Graduate Middle Initial -->
                <InputLabel for="graduate_last_name" value="Graduate Last Name" />
                <TextInput
                    id="graduate_middle_initial"
                    v-model="form.graduate_middle_initial"
                    type="text"
                    class="mt-1 block w-full"
                    required/>  
                <InputError class="mt-2" :message="form.errors.graduate_middle_initial" />

                <!-- Graduate Last Name -->
                <InputLabel for="graduate_last_name" value="Graduate Last Name" />
                <TextInput
                    id="graduate_last_name"
                    v-model="form.graduate_last_name"
                    type="text"
                    class="mt-1 block w-full"
                    required/>
                <InputError class="mt-2" :message="form.errors.graduate_last_name" />

          

               
                <!-- Graduate Graduated From -->
                <div class="mt-4">
                    <InputLabel for="graduate_school_graduated_from" value="School Graduated From" />
                    <select
        id="graduate_school_graduated_from"
        v-model="form.graduate_school_graduated_from"
        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        required
    >
        <option value="" disabled>Select a School</option>
        <option 
            v-for="user in insti_users" 
            :key="user.id" 
            :value="user.institution_name"
        >
            {{ user.institution_name }}
        </option>
    </select>
                    <InputError class="mt-2" :message="form.errors.graduate_school_graduated_from" />
                </div>

                <!-- Program Completed -->
               

                <!-- Year Graduated -->
                <div class="mt-4">
                    <InputLabel for="graduate_year_graduated" value="Year Graduated" />
                    <select
                        id="graduate_year_graduated"
                        v-model="form.graduate_year_graduated"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required>
                        <option value="" disabled>Select Year</option>
                        <option v-for="year in year_graduated" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.graduate_year_graduated" />
                </div>
            </div>



            <div class="grid grid-cols-3 ">
                <div class=" col-span-1">
                    <h2 class="text-lg font-semibold text-gray-900"></h2>
                    <p class="text-sm text-gray-600"></p>
                </div>

                <div class=" col-span-2">
                <!-- Gender -->
                    <div>
                        <div class="flex items-center gap-1">
                            <InputLabel for="company_hr_gender" value="Gender" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div>
                            <select id="company_hr_gender" v-model="form.company_hr_gender" class="mt-1 mb-4 block w-full" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <InputError class="" :message="form.errors.company_hr_gender" />
                        </div>
                    </div>
                    <!-- Date of Birth -->
                    <div>
                        <div class="flex items-center gap-1">
                            <InputLabel for="company_hr_dob" value="Date of Birth" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div>
                            <TextInput 
                                id="company_hr_dob" 
                                v-model="form.company_hr_dob" 
                                type="date" 
                                class="mt-1 mb-4 block w-full" 
                                required />
                            <InputError class="mt-2" :message="form.errors.company_hr_dob" />
                        </div>
                    </div>
                    <!-- HR Email -->
                    <div>
                        <div class="flex items-center gap-1">
                            <InputLabel for="email" value="Email Address" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div>
                            <TextInput 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                class="mt-1 mb-4 block w-full" 
                                required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>

                    <!-- HR Contact Number -->
                    <div>
                        <div class="flex items-center gap-1">
                            <InputLabel for="company_hr_contact_number" value="Contact Number" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div>
                            <TextInput 
                                id="company_hr_contact_number"
                                v-model="form.company_hr_contact_number"
                                v-mask="'# (###) ###-####'"
                                type="text"
                                class="mt-1 mb-4 block w-full"
                                required />
                            <InputError class="mt-2" :message="form.errors.company_hr_contact_number" />
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
                            <TextInput 
                                id="password" 
                                v-model="form.password" 
                                type="password" 
                                class="mt-1 block w-full" 
                                required />
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center gap-1">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <span class="text-red-500">*</span>
                        </div>
                        <div class="mb-2">
                            <TextInput 
                                id="password_confirmation" 
                                v-model="form.password_confirmation" 
                                type="password" 
                                class="mt-1 mb-4 block w-full" 
                                required />
                            <InputError class="mt-1" :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>
            </div>


               

            

            <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-12">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Already registered?
                </Link>
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>