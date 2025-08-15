<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';

// Get the user's name from props if available
const props = defineProps({
  auth: Object,
});

const userName = props.auth?.user?.name || 'User';
</script>

<template>
  <Head title="Verification Success" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="text-center mb-6">
      <div class="flex justify-center mb-4">
        <div class="bg-green-100 rounded-full p-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
      </div>
      
      <h1 class="text-2xl font-bold mb-2">Nicely done, {{ userName }}</h1>
      <p class="text-gray-600 mb-6">Your basic PathwayProfile is now up and running.</p>
      <p class="text-gray-600 mb-8">The more you add to your profile, the more you stand out from other candidates</p>
      
      <div class="flex flex-col space-y-4">
        <Link :href="route('profile.index')" class="w-full">
          <PrimaryButton type="button" class="w-full justify-center py-3 bg-pink-600 hover:bg-pink-700">
            Complete your profile
          </PrimaryButton>
        </Link>
        
        <Link :href="route('job.search')" class="text-blue-600 hover:text-blue-800 font-medium flex items-center justify-center">
          Continue
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </Link>
      </div>
    </div>
  </AuthenticationCard>
</template>