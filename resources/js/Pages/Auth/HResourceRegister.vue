<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';


const isPasswordFocused = ref(false);

// Define the form with additional fields for user types
const form = useForm({
    company_hr_first_name: '',
    company_hr_last_name: '',
    gender: '',
    dob: '',
    contact_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'hr',
    terms: false,
});

// Track the selected user type

const formattedContactNumber = computed({
    get: () => {
        let rawNumber = form.contact_number.replace(/\D/g, ""); // Remove non-numeric characters


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

        form.contact_number = rawValue;
    },
});


// Update 04/04
const passwordCriteria = computed(() => {
    const password = form.password;
    return {
        length: password.length >= 8, // Minimum 8 characters
        uppercaseLowercase: /[a-z]/.test(password) && /[A-Z]/.test(password), // Upper & lower case
        letter: /[a-zA-Z]/.test(password), // At least one letter
        number: /\d/.test(password), // At least one number
        symbol: /[@$!%*?&.]/.test(password), // At least one special character
    };
});


// Handle form submission
const submit = () => {
    form.post(route('hr.register'), {
        onFinish: () => {
            console.log("Form submission finished");
            console.log(form.errors);
            form.reset('password', 'password_confirmation');
        },
        onSuccess: () => {
            Inertia.visit(route('dashboard'));
        },
    });
};


</script>

<template>

    <Head title="Human Resource Officer Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <template #registerForm>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-3 mt-8 pt-5">
                    <!-- Company HR Information -->
                    <div class=" col-span-1">
                        <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
                        <p class="text-sm text-gray-600"></p>


                    </div>

                    <div class="col-span-2">

                        <div class="grid grid-cols-2 gap-4">
                               <div> <!-- First Name -->
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_hr_first_name" value= "First Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_hr_first_name" v-model="form.company_hr_first_name" type="text" class="mt-1 mb-4 block w-full" required />
                                        <InputError class="mt-2" :message="form.errors.company_hr_first_name" />
                                    </div>
                               </div>
                               <div> <!-- First Name -->
                                    <div class="flex items-center gap-1">
                                        <InputLabel for="company_hr_last_name" value= "Last Name" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <div>
                                        <TextInput id="company_hr_last_name" v-model="form.company_hr_last_name" type="text" class="mt-1 mb-4 block w-full" required />
                                        <InputError class="mt-2" :message="form.errors.company_hr_last_name" />
                                    </div>
                               </div>
                            </div>


                        <div class="grid grid-cols-2 gap-4">
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

                                    <TextInput 
                                        id="dob" 
                                        v-model="form.dob" 
                                        type="date" 
                                        class="mt-1 mb-4 block w-full" 
                                        required />
                                    <InputError class="mt-2" :message="form.errors.dob" />

                                </div>
                            </div>
                        </div>

                        <!-- HR Email -->
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

                        <!-- HR Contact Number -->
                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="contact_number" value="Contact Number" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div>

                                <TextInput 
                                    id="contact_number"
                                    v-model="formattedContactNumber"
                                    type="text"
                                    class="mt-1 mb-4 block w-full"
                                    required />
                                <InputError class="mt-2" :message="form.errors.contact_number" />
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
                                    required
                                    @focus="isPasswordFocused = true"
                                    @blur="isPasswordFocused = false" />
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>

                            <!-- Password validation tooltip UPDATE 04/04-->

                            <div v-if="isPasswordFocused && form.password" class="mt-2 p-3 bg-gray-800 text-white rounded-md w-64 text-sm shadow-lg">

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
                                        ✔ At least one special character (@$!%*?&.)
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
                <div class="flex items-center justify-end mt-8">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </template>
    </AuthenticationCard>
</template>