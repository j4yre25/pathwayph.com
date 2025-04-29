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
    name: props.user.name,
    email: props.user.email,
    photo: null,
    peso_first_name: props.user.peso_first_name,
    peso_last_name: props.user.peso_last_name,
    graduate_first_name: props.user.graduate_first_name || '',
    graduate_last_name: props.user.graduate_last_name || '',
    graduate_professional_title: props.user.graduate_professional_title || '',
    company_hr_first_name: props.user.company_hr_first_name || '',
    company_hr_last_name: props.user.company_hr_last_name || '',
    institution_president_first_name: props.user.institution_president_first_name || '',
    institution_president_last_name: props.user.institution_president_last_name || '',
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

    form.put(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
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
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div v-if="user.role === 'graduate'" class="col-span-6 sm:col-span-4">
                <InputLabel for="graduate_first_name" value="First Name" />
                <TextInput
                    id="graduate_first_name"
                    v-model="form.graduate_first_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="graduate_first_name"
                />
                <InputError :message="form.errors.graduate_first_name" class="mt-2" />
            </div>

            <div v-if="user.role === 'graduate'" class="col-span-6 sm:col-span-4">
                <InputLabel for="graduate_last_name" value="Last Name" />
                <TextInput
                    id="graduate_last_name"
                    v-model="form.graduate_last_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="graduate_last_name"
                />
                <InputError :message="form.errors.graduate_last_name" class="mt-2" />
            </div>

            <div v-if="user.role === 'company'" class="col-span-6 sm:col-span-4">
                <InputLabel for="company_hr_first_name" value="HR First Name" />
                <TextInput
                    id="company_hr_first_name"
                    v-model="form.company_hr_first_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="company_hr_first_name"
                />
                <InputError :message="form.errors.company_hr_first_name" class="mt-2" />
            </div>

            <div v-if="user.role === 'company'" class="col-span-6 sm:col-span-4">
                <InputLabel for="company_hr_last_name" value="HR Last Name" />
                <TextInput
                    id="company_hr_last_name"
                    v-model="form.company_hr_last_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="company_hr_last_name"
                />
                <InputError :message="form.errors.company_hr_last_name" class="mt-2" />
            </div>

            <div v-if="user.role === 'institution'" class="col-span-6 sm:col-span-4">
                <InputLabel for="institution_president_first_name" value="President First Name" />
                <TextInput
                    id="institution_president_first_name"
                    v-model="form.institution_president_first_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="institution_president_first_name"
                />
                <InputError :message="form.errors.institution_president_first_name" class="mt-2" />
            </div>

            <div v-if="user.role === 'institution'" class="col-span-6 sm:col-span-4">
                <InputLabel for="institution_president_last_name" value="President Last Name" />
                <TextInput
                    id="institution_president_last_name"
                    v-model="form.institution_president_last_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="institution_president_last_name"
                />
                <InputError :message="form.errors.institution_president_last_name" class="mt-2" />
            </div>
            <div v-if="user.role === 'peso'" class="col-span-6 sm:col-span-4">
                <InputLabel for="peso_first_name" value="Peso First Name" />
                <TextInput
                    id="peso_first_name"
                    v-model="form.peso_first_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="peso_first_name"
                />
                <InputError :message="form.errors.peso_first_name" class="mt-2" />
            </div>
            <div v-if="user.role === 'peso'" class="col-span-6 sm:col-span-4">
                <InputLabel for="peso_last_name" value="Peso Last Name" />
                <TextInput
                    id="peso_last_name"
                    v-model="form.peso_last_name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="peso_last_name"
                />
                <InputError :message="form.errors.peso_last_name" class="mt-2" />
            </div>

  

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
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
