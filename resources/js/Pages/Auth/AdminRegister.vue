<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';

const showModal = ref(false);

const form = useForm({
    peso_first_name: '',
    peso_last_name: '',
    gender: '',
    dob: '',
    contact_number: '',
    telephone_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'peso', // Default role for PESO admin
    terms: false,
    is_approved: true, // Automatically approve PESO admin
});

const formattedContactNumber = computed({
    get: () => {
        let rawNumber = form.contact_number.replace(/\D/g, "");
        if (rawNumber.length > 10) {
            rawNumber = rawNumber.slice(0, 10);
        }
        return `+63 ${rawNumber.replace(/(\d{3})(\d{3})(\d{4})/, "$1 $2 $3")}`.trim();
    },
    set: (value) => {
        let rawValue = value.replace(/\D/g, "");
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

const passwordCriteria = computed(() => {
    const password = form.password;
    return {
        length: password.length >= 8,
        uppercaseLowercase: /[a-z]/.test(password) && /[A-Z]/.test(password),
        number: /\d/.test(password),
        symbol: /[~!@#$%^&*()-=+_/>.,<;'']/.test(password),
    };
});

const submit = () => {
    form.post(route('admin.register.submit'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
        onSuccess: () => {
            showModal.value = true;
        },
    });
};

const redirectToDashboard = () => {
    Inertia.visit(route('dashboard'));
};
</script>


<template>

    <Head title="Peso Admin Register" />

    <AuthenticationCard>
    <template #registerForm>

        <div class="flex space-x-12">
            <!-- Left Side: Welcome Section -->
            <div
                class="flex-1 flex flex-col items-start justify-center p-6 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg shadow-lg text-white">
                <AuthenticationCardLogo class="mx-20 fill-white-100" />
                <h2 class="text-6xl font-bold">Welcome to</h2>
                <h1 class="text-7xl font-extrabold">Pathway</h1>
                <p class="mt-4 text-sm">
                    Join us in shaping the future of education. We are excited to partner with you in this journey.
                </p>
            </div>

            <!-- Right Side: Registration Form -->
            <div class="flex-1 space-y-6">
                <h2 class="text-xl font-semibold text-gray-900">PESO Registration</h2>
                <p class="text-sm text-gray-600">Provide your personal and account details below.</p>

                <form @submit.prevent="submit" autocomplete="off">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <InputLabel for="peso_first_name" value="First Name" />
                            <TextInput id="peso_first_name" v-model="form.peso_first_name" type="text"
                                class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.peso_first_name" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <InputLabel for="peso_last_name" value="Last Name" />
                            <TextInput id="peso_last_name" v-model="form.peso_last_name" type="text"
                                class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.peso_last_name" />
                        </div>

                        <!-- Gender -->
                        <div>
                            <InputLabel for="gender" value="Gender" />
                            <select id="gender" v-model="form.gender"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.gender" />
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <InputLabel for="dob" value="Date of Birth" />
                            <TextInput id="dob" v-model="form.dob" type="date" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.dob" />
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <!-- Contact Number -->
                        <div>
                            <InputLabel for="contact_number" value="Contact Number" />
                            <TextInput id="contact_number" v-model="formattedContactNumber" type="text"
                                class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.contact_number" />
                        </div>

                        <!-- Telephone Number -->
                        <div>
                            <InputLabel for="telephone_number" value="Telephone Number" />
                            <TextInput id="telephone_number" v-model="form.telephone_number" type="text"
                                class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.telephone_number" />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mt-4">
                        <InputLabel for="email" value="Email Address" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                            required />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end mt-6">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Register
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </template>

    </AuthenticationCard>

    <!-- Success Modal -->
    <Modal v-if="showModal" :show="showModal" @close="redirectToDashboard">
        <template #default>
            <div class="text-center">
                <h2 class="text-lg font-semibold">Registration Successful</h2>
                <p class="mt-2 text-gray-600">You have registered successfully.</p>
                <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition" @click="redirectToDashboard">
                    Okay
                </button>
            </div>
        </template>
    </Modal>
</template>
