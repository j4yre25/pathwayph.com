<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';

const form = useForm({
    peso_first_name: '',
    peso_last_name: '',
    peso_middle_name: '',
    description: '',
    contact_number: '',
    telephone_number: '',
    address: '',
    logo: null,
    email: '',
    password: '',
    password_confirmation: '',
});

const showModal = ref(false);
const currentStep = ref(1);
const totalSteps = ref(2);

function nextStep() {
    if (canProceed.value && currentStep.value < totalSteps.value) {
        currentStep.value++;
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function goToStep(step) {
    if (step >= 1 && step <= totalSteps.value) {
        currentStep.value = step;
    }
}

function isStepValid(step) {
    switch (step) {
        case 1:
            return form.peso_first_name && form.peso_last_name && form.contact_number && form.address && form.email && form.password && form.password_confirmation;
        case 2:
            return true; // Only logo is optional
        default:
            return false;
    }
}

const canProceed = computed(() => isStepValid(currentStep.value));

function onFileChange(e) {
    form.logo = e.target.files[0];
}

function submit() {
    if (currentStep.value === totalSteps.value && canProceed.value) {
        form.post(route('admin.register.submit'), {
            forceFormData: true,
            onSuccess: () => {
                console.log('Registration succeeded');
                showModal.value = true;
            },
            onError: (errors) => {
                console.log('Registration failed', errors);
            }
        });
    }
}

function goToDashboard() {
    window.location.href = route('dashboard');
}
</script>

<template>
    <AppLayout>
        <div class="relative min-h-screen gradient-bg overflow-hidden">
            <div class="relative z-10 max-w-3xl mx-auto py-12 px-6">
                <div class="text-center mb-10 bg-white rounded-xl shadow p-6">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Register PESO Admin</h1>
                    <p class="text-lg text-gray-700">Fill in your details to create your account</p>
                </div>

                <!-- Step Progress Indicator -->
                <div class="flex justify-center mb-8">
                    <div class="flex items-center space-x-4">
                        <div v-for="step in totalSteps" :key="step" class="flex items-center">
                            <div @click="goToStep(step)" :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                                currentStep === step ? 'bg-blue-600 text-white scale-110' :
                                    currentStep > step ? 'bg-blue-400 text-white' : 'bg-gray-200 text-gray-400'
                            ]">
                                <span>{{ step }}</span>
                            </div>
                            <div v-if="step < totalSteps" class="w-10 h-0.5 bg-blue-200 mx-2"></div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Step 1: Personal Information -->
                    <div v-show="currentStep === 1" class="bg-white rounded-2xl shadow p-8">
                        <h2 class="text-xl font-semibold mb-4 text-blue-700">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="peso_first_name">First Name <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="peso_first_name" v-model="form.peso_first_name" type="text" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.peso_first_name" />
                            </div>
                            <div>
                                <InputLabel for="peso_last_name">Last Name <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="peso_last_name" v-model="form.peso_last_name" type="text" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.peso_last_name" />
                            </div>
                            <div>
                                <InputLabel for="peso_middle_name">Middle Name</InputLabel>
                                <TextInput id="peso_middle_name" v-model="form.peso_middle_name" type="text"
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.peso_middle_name" />
                            </div>
                            <div>
                                <InputLabel for="description">Description</InputLabel>
                                <TextInput id="description" v-model="form.description" type="text"
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.description" />
                            </div>
                            <div>
                                <InputLabel for="contact_number">Contact Number <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="contact_number" v-model="form.contact_number" type="text" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.contact_number" />
                            </div>
                            <div>
                                <InputLabel for="telephone_number">Telephone Number</InputLabel>
                                <TextInput id="telephone_number" v-model="form.telephone_number" type="text"
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.telephone_number" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="address">Address <span class="text-red-500">*</span></InputLabel>
                                <TextInput id="address" v-model="form.address" type="text" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.address" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="email">Email <span class="text-red-500">*</span></InputLabel>
                                <TextInput id="email" v-model="form.email" type="email" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div>
                                <InputLabel for="password">Password <span class="text-red-500">*</span></InputLabel>
                                <TextInput id="password" v-model="form.password" type="password" required
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel for="password_confirmation">Confirm Password <span
                                        class="text-red-500">*</span></InputLabel>
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    type="password" required class="mt-1 block w-full" />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Profile Picture -->
                    <div v-show="currentStep === 2" class="bg-white rounded-2xl shadow p-8">
                        <h2 class="text-xl font-semibold mb-4 text-blue-700">Profile Picture</h2>
                        <div>
                            <InputLabel for="logo">Upload Profile Picture</InputLabel>
                            <input id="logo" type="file" class="mt-2 block w-full" @change="onFileChange"
                                accept="image/*" />
                            <InputError :message="form.errors.logo" />
                            <div v-if="form.logo" class="mt-2 text-sm text-blue-600">
                                Selected: {{ form.logo.name }}
                            </div>
                            <p class="text-gray-500 text-xs mt-2">Accepted formats: JPG, JPEG, PNG, GIF</p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center mt-8">
                        <button v-if="currentStep > 1" type="button" @click="prevStep"
                            class="bg-blue-100 text-blue-700 px-6 py-2 rounded-xl font-medium hover:bg-blue-200 transition-all duration-300 flex items-center space-x-2">
                            <span>Previous</span>
                        </button>
                        <div v-else></div>
                        <div class="flex space-x-4">
                            <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
                                :disabled="!canProceed"
                                class="bg-blue-600 px-6 py-2 rounded-xl font-medium text-white hover:bg-blue-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                                <span>Next Step</span>
                            </button>
                            <button v-if="currentStep === totalSteps" type="submit"
                                :disabled="form.processing || !canProceed"
                                class="bg-blue-600 px-8 py-2 rounded-xl font-bold text-white hover:bg-blue-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                                <span v-if="form.processing">Registering...</span>
                                <span v-else>Register</span>
                            </button>
                        </div>
                    </div>
                </form>

                <Modal v-model="showModal">
                    <template #header>
                        <h2 class="text-2xl font-bold text-blue-600">Registration Successful!</h2>
                    </template>
                    <template #body>
                        <p class="mb-6 text-gray-700">
                            Your PESO admin account has been created.<br>
                            You will now be redirected to your dashboard.
                        </p>
                    </template>
                    <template #footer>
                        <PrimaryButton @click="goToDashboard">Go to Dashboard</PrimaryButton>
                    </template>
                </Modal>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.gradient-bg {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
}
</style>