    <script setup>
    import { ref } from 'vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import InputLabel from '@/Components/InputLabel.vue';
    import TextInput from '@/Components/TextInput.vue';
    import InputError from '@/Components/InputError.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AppLayout from '@/Layouts/AppLayout.vue';
    
    // You may want to fetch these from props or an API
    const categories = usePage().props.categories || [];
    
    const form = useForm({
        company_name: '',
        category: '',
        sector: '',
        company_street_address: '',
        company_brgy: '',
        company_city: '',
        company_province: '',
        company_zip_code: '',
        company_email: '',
        company_mobile_phone: '',
        telephone_number: '',
        first_name: '',
        last_name: '',
        middle_name: '',
        gender: '',
        dob: '',
        email: '',
        mobile_number: '',
    });
    
    function submit() {
        form.post(route('company.information.save'), {
        onSuccess: () => {
            // Optionally redirect or show a success message
        }
        });
    }
    </script>
<template>
  <AppLayout>
  <div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Complete Your Company Profile</h1>
    <form @submit.prevent="submit" class="space-y-8">
      <!-- Company Details -->
      <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Business Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel for="company_name" value="Business Name" />
            <TextInput id="company_name" v-model="form.company_name" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_name" />
          </div>
          <div>
            <InputLabel for="category" value="Sector" />
            <select id="category" v-model="form.category" required class="mt-1 block w-full border-gray-300 rounded">
              <option value="">Select Sector</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <InputError :message="form.errors.category" />
          </div>
          <div>
            <InputLabel for="company_street_address" value="Street Address" />
            <TextInput id="company_street_address" v-model="form.company_street_address" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_street_address" />
          </div>
          <div>
            <InputLabel for="company_brgy" value="Barangay" />
            <TextInput id="company_brgy" v-model="form.company_brgy" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_brgy" />
          </div>
          <div>
            <InputLabel for="company_city" value="City" />
            <TextInput id="company_city" v-model="form.company_city" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_city" />
          </div>
          <div>
            <InputLabel for="company_province" value="Province" />
            <TextInput id="company_province" v-model="form.company_province" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_province" />
          </div>
          <div>
            <InputLabel for="company_zip_code" value="ZIP Code" />
            <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_zip_code" />
          </div>
        </div>
      </div>

      <!-- Company Contact -->
      <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Company Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel for="company_email" value="Email Address" />
            <TextInput id="company_email" v-model="form.company_email" type="email" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_email" />
          </div>
          <div>
            <InputLabel for="company_mobile_phone" value="Mobile Number" />
            <TextInput id="company_mobile_phone" v-model="form.company_mobile_phone" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.company_mobile_phone" />
          </div>
          <div>
            <InputLabel for="telephone_number" value="Telephone Number" />
            <TextInput id="telephone_number" v-model="form.telephone_number" type="text" class="mt-1 block w-full" />
            <InputError :message="form.errors.telephone_number" />
          </div>
        </div>
      </div>

      <!-- HR Details -->
      <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">HR Officer Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel for="first_name" value="First Name" />
            <TextInput id="first_name" v-model="form.first_name" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.first_name" />
          </div>
          <div>
            <InputLabel for="last_name" value="Last Name" />
            <TextInput id="last_name" v-model="form.last_name" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.last_name" />
          </div>
          <div>
            <InputLabel for="middle_name" value="Middle Name" />
            <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-1 block w-full" />
            <InputError :message="form.errors.middle_name" />
          </div>
          <div>
            <InputLabel for="gender" value="Gender" />
            <select id="gender" v-model="form.gender" required class="mt-1 block w-full border-gray-300 rounded">
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
            <InputError :message="form.errors.gender" />
          </div>
          <div>
            <InputLabel for="dob" value="Date of Birth" />
            <TextInput id="dob" v-model="form.dob" type="date" required class="mt-1 block w-full" />
            <InputError :message="form.errors.dob" />
          </div>
          <div>
            <InputLabel for="email" value="HR Email Address" />
            <TextInput id="email" v-model="form.email" type="email" required class="mt-1 block w-full" />
            <InputError :message="form.errors.email" />
          </div>
          <div>
            <InputLabel for="mobile_number" value="HR Mobile Number" />
            <TextInput id="mobile_number" v-model="form.mobile_number" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.mobile_number" />
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <PrimaryButton :disabled="form.processing">Save Information</PrimaryButton>
      </div>
    </form>
  </div>
  </AppLayout>
</template>
