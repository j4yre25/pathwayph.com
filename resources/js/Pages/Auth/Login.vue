<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Modal from '@/Components/Modal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

// Password visibility toggle
const showPassword = ref(false);

// Modal state
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');

// Login form
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// Form submission
const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
    onError: (errors) => {
      console.log('Errors:', errors); // Debugging
      if (errors.password) {
        modalTitle.value = 'Login Error';
        modalMessage.value = errors.password;
        showModal.value = true;
      } else if (errors.email) {
        modalTitle.value = 'Login Error';
        modalMessage.value = errors.email;
        showModal.value = true;
      }
    },
    onSuccess: () => {
      Inertia.visit(route('dashboard'));
    },
  });
};

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>

  <Head title="Sign In" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <template #registerForm>
      <div class="p-8 max-w-md mx-auto">
        <h2 class="text-2xl font-medium text-gray-900 mb-1">Sign In</h2>
        <p class="text-sm text-gray-600 mb-6">Enter your credentials to access your account</p>

        <form @submit.prevent="submit">
          <div>
            <InputLabel for="email" value="Email" />
            <TextInput 
              id="email" 
              v-model="form.email" 
              type="email" 
              class="mt-1 block w-full" 
              required 
              autofocus
              aria-autocomplete="username" />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div class="mt-4 relative">
            <InputLabel for="password" value="Password" />
            <div class="relative">
              <TextInput 
                id="password" 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'"
                class="mt-1 block w-full pr-10" 
                required 
                autocomplete="current-password"/>
              <button type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center mt-1 text-gray-600 focus:outline-none"
                @click="togglePasswordVisibility">
                <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye']"></i>
              </button>
            </div>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="flex items-center justify-between mt-4">
            <label class="flex items-center">
              <Checkbox v-model:checked="form.remember" name="remember" />
              <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
            <Link href="route('password.request')" class="text-sm text-blue-600 hover:underline">
              Forgot your password?
            </Link>
          </div>

          <div class="flex flex-col space-y-4 mt-6">
            <button type="submit"
              class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              :disabled="form.processing">
              <span v-if="form.processing">Processing...</span>
              <span v-else>Sign In</span>
            </button>
          </div>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Don't have an account?
            <Link href="/Auth/PathwayRegister" class="text-blue-600 hover:underline">
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </template>
  </AuthenticationCard>

  <Modal v-if="showModal" :show="showModal" @close="showModal = false">
    <template #title>
      {{ modalTitle }}
    </template>
    <template #content>
      <p>{{ modalMessage }}</p>
    </template>
    <template #footer>
      <PrimaryButton @click="showModal = false">Close</PrimaryButton>
    </template>
  </Modal>
</template>