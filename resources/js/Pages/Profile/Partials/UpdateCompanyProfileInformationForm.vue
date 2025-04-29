<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue'; 


const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    photo: null,

    // Company Profile Fields
    company_name: props.user.company_name || '',
    company_email: props.user.company_email || '',  
    company_street_address: props.user.company_street_address || '',
    company_brgy: props.user.company_brgy || '',
    company_city: props.user.company_city || '',
    company_province: props.user.company_province || '',
    company_zip_code: props.user.company_zip_code || '',
    company_contact_number: props.user.company_contact_number || '',
    telephone_number: props.user.telephone_number || '',
});



const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const showModal = ref(false);
const modalTitle = ref('Profile Updated');
const modalMessage = ref('Your profile information has been successfully updated.');

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.put(route('company-profile.update'), {
        errorBag: 'updateCompanyProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            showModal.value = true;
            // location.reload();
            // Inertia.reload({ only: ['user'] });    
        } 
        ,
    });

    console.log('Submitting form:', form);
    console.log('Route:', route('user-profile-information.update'));

};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const reloadPage = () => {
    location.reload();
    Inertia.reload({ only: ['user'] });    
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">

        <template #description>
            Update your company account's profile and contact information.
        </template>

        <template #form>
            <!-- Profile Photo Section -->
            <div v-if="user.role === 'company' && $page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <InputLabel for="photo" value="Profile Photo" />

                <!-- File input (hidden) -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                />

                <!-- Preview Section -->
                <div class="mt-2">
                    <!-- Old Photo -->
                    <div v-if="!photoPreview">
                        <img :src="company.profile_photo_url" :alt="user.name" class="rounded-full size-24 object-cover border shadow" />
                    </div>

                    <!-- New Photo Preview -->
                    <div v-else>
                        <img :src="photoPreview" class="rounded-full size-24 object-cover border shadow" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-3">
                    <SecondaryButton type="button" @click.prevent="selectNewPhoto">
                        Select A New Photo
                    </SecondaryButton>

                    <SecondaryButton
                        v-if="user.profile_photo_path"
                        type="button"
                        @click.prevent="deletePhoto"
                    >
                        Remove Photo
                    </SecondaryButton>
                </div>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

                

            <!-- Company Address Section -->
            <div v-if="user.role === 'company'" class="col-span-6 sm:col-span-6">
                <h3 class="text-lg font-semibold text-gray-700 mt-2 mb-2 border-b pb-1">Company Basic Information</h3>
                
                <div class="border p-4 rounded-xl bg-gray-50 mt-2">
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                         <!-- Company Name -->
                        <div  class="col-span-6 sm:col-span-4">
                            <InputLabel for="company_name" value="Company Name" />
                            <TextInput
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="company_name"
                            />
                            <InputError :message="form.errors.company_name" class="mt-2" />
                        </div>

                        <!-- Street Address -->
                        <div  class="col-span-3 sm:col-span-4"> 
                            <InputLabel for="company_street_address" value="Street Address" />
                            <TextInput
                                id="company_street_address"
                                v-model="form.company_street_address"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_street_address" class="mt-2" />
                        </div>

                        <!-- Barangay -->
                        <div  class="col-span-3 sm:col-span-1">
                            <InputLabel for="company_brgy" value="Barangay" />
                            <TextInput
                                id="company_brgy"
                                v-model="form.company_brgy"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_brgy" class="mt-2" />
                        </div>

                        <!-- City -->
                        <div>
                            <InputLabel for="company_city" value="City / Municipality" />
                            <TextInput
                                id="company_city"
                                v-model="form.company_city"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_city" class="mt-2" />
                        </div>

                        <!-- Province -->
                        <div>
                            <InputLabel for="company_province" value="Province" />
                            <TextInput
                                id="company_province"
                                v-model="form.company_province"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_province" class="mt-2" />
                        </div>

                        <!-- ZIP Code -->
                        <div>
                            <InputLabel for="company_zip_code" value="ZIP Code" />
                            <TextInput
                                id="company_zip_code"
                                v-model="form.company_zip_code"
                                type="text"
                                maxlength="4"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_zip_code" class="mt-2" />
                        </div>

                    </div>
                </div>
            </div>

           
            <h3 class="text-lg font-semibold text-gray-700 mt-2 mb-2 border-b pb-1">Contact Information</h3>
                
                <div class="border p-4 rounded-xl bg-gray-50 mt-2">
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Email -->
                        <div class="mt-2 ol-span-6 sm:col-span-2">
                            <InputLabel for="company_email" value="Email Address" />
                            <TextInput
                                id="company_email"
                                v-model="form.company_email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                                autocomplete="company_email"
                            />
                            <InputError :message="form.errors.company_email" class="mt-2" />
                        </div>
                      
                        <!-- Contact Number -->
                        <div>
                            <InputLabel for="company_contact_number" value="Mobile Contact Number" />
                            <TextInput
                                id="company_contact_number"
                                v-model="form.company_contact_number"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.company_contact_number" class="mt-2" />
                        </div>

                        <!-- Telephone Number -->
                        <div>
                            <InputLabel for="telephone_number" value="Telephone Number (Landline)" />
                            <TextInput
                                id="telephone_number"
                                v-model="form.telephone_number"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.telephone_number" class="mt-2" />
                        </div>
                    </div>
                </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>

    <Modal :show="showModal" @close="reloadPage()">
        <template #title>
            {{ modalTitle }}
        </template>
        <template #content>
            <p>{{ modalMessage }}</p>
        </template>
        <template #footer>
            <PrimaryButton @click="reloadPage">Close</PrimaryButton>
        </template>
    </Modal>
</template>
