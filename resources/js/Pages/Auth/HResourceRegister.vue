<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref,  watchEffect } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber';
import { usePasswordCriteria } from '@/Composables/usePasswordCriteria';
import '@fortawesome/fontawesome-free/css/all.min.css';



const isPasswordFocused = ref(false);

// Define the form with additional fields for user types
const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    dob: '',
    mobile_number: null,
    email: '',
    password: '',
    password_confirmation: '',
    role: 'company',
    terms: false,
});


const { formattedMobileNumber} = useFormattedMobileNumber(form, 'mobile_number');
const { passwordCriteria } = usePasswordCriteria(form);

const maxDate = ref(new Date().toISOString().split('T')[0]);
const minDate = ref('1900-01-01');

const page = usePage();
const showModal = ref(false);
const message = ref('');

watchEffect(() => {
    if (page.props.flash?.banner) {
        showModal.value = true;
    }
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
            Inertia.reload({ only: ['hrCount'] });
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
            <div class="flex flex-col items-center justify-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Register New Human Resource Officer </h1>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Please fill out the form below to register.</p>
                </div>
            </div>
            <div class="w-full border-t border-gray-300 mb-6"></div>
            <form @submit.prevent="submit" class="max-w-2xl mx-auto px-4">
                <div >
                    <div class="grid grid-cols-3 gap-4">
                        <div> <!-- First Name -->
                            <div class="flex items-center gap-1">
                                <InputLabel for="first_name" value= "First Name" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-4 text-gray-400"></i>
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mb-4 mt-1 w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Enter first name"
                                    v-model="form.first_name"
                                    required
                                    @keydown.enter.prevent
                                />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>
                        </div>
                            
                        <div > <!-- Last Name -->
                            <div class="flex items-center gap-1">
                                <InputLabel for="last_name" value= "Last Name" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-4 text-gray-400"></i>
                                <TextInput 
                                    id="last_name" 
                                    type="text" 
                                    class="mb-4 mt-1 w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600 "
                                    placeholder="Enter last name"
                                    v-model="form.last_name" 
                                    required
                                    @keydown.enter.prevent
                                    />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                        </div>

                         <div > <!-- Miidle Name -->
                            <div class="flex items-center gap-1">
                                <InputLabel for="middle_name" value= "Middle Name" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-4 text-gray-400"></i>
                                <TextInput 
                                    id="middle_name" 
                                    type="text" 
                                    class="mb-4 mt-1 w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600 "
                                    placeholder="Enter middle name"
                                    v-model="form.middle_name" 
                                    required
                                    @keydown.enter.prevent
                                    />
                                <InputError class="mt-2" :message="form.errors.middle_name" />
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
                            <div class="relative">
                                <i class="fa-solid fa-mars-and-venus absolute left-3 top-4 text-gray-400"></i>
                                    <select 
                                    id="gender" v-model="form.gender" 
                                    class="mt-1 mb-4 block w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600 " required>
                                    <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                    <option value="Female">Female</option>
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
                                    :max="maxDate"
                                    :min="minDate"  
                                    type="date" 
                                    class="mt-1 block w-full" 
                                    required />
                                <InputError class="mt-1" :message="form.errors.dob" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- HR Email -->
                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="email" value="Email Address" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-3 top-3 text-gray-400"></i>
                                <TextInput 
                                    id="email" 
                                    type="email" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Enter email address"
                                    v-model="form.email" 
                                    required />
                                <InputError class="mt-1" :message="form.errors.email" />
                            </div>
                        </div>

                        <!-- HR Contact Number -->
                        <div>
                            <div class="flex items-center gap-1">
                                <InputLabel for="contact_number" value="Contact Number" />
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-3 top-3 text-gray-400"></i>
                                <TextInput 
                                    id="contact_number"
                                    placeholder="+63 912 345 6789"
                                    type="tel"
                                    class="mt-1 block w-full border border-gray-300 rounded-md p-2 pl-10  outline-none focus:ring-2 focus:ring-indigo-600"
                                    v-model="formattedMobileNumber"
                                    required />
                                <InputError class="mt-1" :message="form.errors.contact_number" />
                            </div>
                        </div>
                    </div>

                        
                    <!-- Set Password -->
                    <h3 class="mt-5 mb-2 font-semibold">Set Password</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
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
                        </div>

                        <div class="col-span-1">
                            <!-- Confirm Password -->
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

            <ConfirmationModal :show="showModal" @close="showModal = false">
                <template #title>Success</template>
                <template #content>{{ message }}</template>
                <template #footer>
                <button @click="showModal = false" class="bg-green-600 text-white px-4 py-2 rounded">OK</button>
                </template>
            </ConfirmationModal>
        </template>
    </AuthenticationCard>
</template>