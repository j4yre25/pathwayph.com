<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid, format } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Define props

const isSubmittingAchievement = ref(false);

const props = defineProps({
    activeSection: {
        type: String,
        default: 'achievements'
    },

    achievementEntries: Array,
    archivedAchievementEntries: Array,
});

const isAddAchievementModalOpen = ref(false);
const isUpdateAchievementModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const showArchivedAchievements = ref(false);

const confirmModal = reactive({
  show: false,
  type: '',      // 'archive' | 'unarchive' | 'delete'
  entry: null,
  message: '',
  confirmLabel: '',
  confirmAction: null,
});

function openConfirm(type, entry) {
  confirmModal.type = type;
  confirmModal.entry = entry;
  confirmModal.show = true;
  if (type === 'delete') {
    confirmModal.message = 'Are you sure you want to delete this achievement? This action cannot be undone.';
    confirmModal.confirmLabel = 'Delete';
    confirmModal.confirmAction = () => {
      useForm({}).delete(route('profile.achievements.remove', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Achievement deleted successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
        },
        onError: () => {
          errorMessage.value = 'Failed to delete achievement. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  } else if (type === 'archive') {
    confirmModal.message = 'Are you sure you want to archive this achievement?';
    confirmModal.confirmLabel = 'Archive';
    confirmModal.confirmAction = () => {
      useForm({}).put(route('profile.achievements.archive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Achievement archived successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
        },
        onError: () => {
          errorMessage.value = 'Failed to archive achievement. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  } else if (type === 'unarchive') {
    confirmModal.message = 'Restore this archived achievement?';
    confirmModal.confirmLabel = 'Restore';
    confirmModal.confirmAction = () => {
      useForm({}).post(route('profile.achievements.unarchive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Achievement restored successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
        },
        onError: () => {
          errorMessage.value = 'Failed to restore achievement. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  }
}

function closeConfirm() {
  confirmModal.show = false;
  confirmModal.type = '';
  confirmModal.entry = null;
  confirmModal.message = '';
  confirmModal.confirmLabel = '';
  confirmModal.confirmAction = null;
}

function deleteAchievement(entry) {
  openConfirm('delete', entry);
}
function archiveAchievement(entry) {
  openConfirm('archive', entry);
}
function unarchiveAchievement(entry) {
  openConfirm('unarchive', entry);
}


const achievementEntries = ref((props.achievementEntries || []));
const archivedAchievementEntries = ref((props.archivedAchievementEntries || []));



const achievementForm = useForm({
    id: null,
    title: '',
    type: '',
    issuer: '',
    date: null,
    description: '',
    url: '',
    noCredentialUrl: false,
    credential_picture: null,
    credential_picture_url: null
});

const formatDate = (date) => {
    if (!date) return '';
    if (typeof date === 'string' && date.length >= 10) return date.slice(0, 10);
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';
    return d.toISOString().split('T')[0];
};

const resetAchievementForm = () => {
    achievementForm.reset();
    achievementForm.clearErrors();
    achievementForm.date = null;
    achievementForm.credential_picture = null;
    achievementForm.credential_picture_url = null;
};

const openAddAchievementModal = () => {
    resetAchievementForm();
    isAddAchievementModalOpen.value = true;
};
const closeAddAchievementModal = () => {
    isAddAchievementModalOpen.value = false;
    resetAchievementForm();
};
const openUpdateAchievementModal = (entry) => {
    resetAchievementForm();
    achievementForm.id = entry.id;
    achievementForm.title = entry.title || '';
    achievementForm.type = entry.type || '';
    achievementForm.issuer = entry.issuer || '';
    achievementForm.date = entry.date ? new Date(entry.date) : null; // <-- convert to Date
    achievementForm.description = entry.description || '';
    achievementForm.url = entry.url || '';
    achievementForm.noCredentialUrl = !entry.url;
    achievementForm.credential_picture_url = entry.credential_picture_url || entry.credential_picture || null;
    isUpdateAchievementModalOpen.value = true;
};

const closeUpdateAchievementModal = () => {
    isUpdateAchievementModalOpen.value = false;
    resetAchievementForm();
};
const closeSuccessModal = () => { isSuccessModalOpen.value = false; };
const closeErrorModal = () => { isErrorModalOpen.value = false; };
const closeDuplicateModal = () => { isDuplicateModalOpen.value = false; };

const handleCredentialPictureUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        achievementForm.credential_picture = file;
        achievementForm.credential_picture_url = URL.createObjectURL(file);
    }
};
const updateAchievement = () => {
    isSubmittingAchievement.value = true;

    achievementForm
        .transform(data => ({
            title: data.title ? String(data.title) : '',
            type: data.type ? String(data.type) : '',
            issuer: data.issuer ? String(data.issuer) : '',
            date: data.date
                ? (data.date instanceof Date
                    ? formatDate(data.date)
                    : (typeof data.date === 'string' && data.date.includes('T')
                        ? data.date.slice(0, 10)
                        : data.date))
                : '',
            description: data.description ? String(data.description) : '',
            url: data.noCredentialUrl ? '' : (data.url ? String(data.url) : ''),
            credential_picture: data.credential_picture ?? undefined,
            _method: 'PUT', // <-- This is the key!
        }))
        .post(route('profile.achievements.update', achievementForm.id), {
            forceFormData: true,
            onSuccess: () => {
                closeUpdateAchievementModal();
                successMessage.value = 'Achievement updated successfully!';
                isSuccessModalOpen.value = true;
                resetAchievementForm();
            },
            onError: (errors) => {
                console.error(errors);
                if (errors.duplicate) {
                    isDuplicateModalOpen.value = true;
                } else {
                    errorMessage.value = 'Failed to update achievement. Please check the form and try again.';
                    isErrorModalOpen.value = true;
                }
            }
        });
};
const addAchievement = () => {
    isSubmittingAchievement.value = true;

    achievementForm
        .transform(data => ({
            title: data.title ? String(data.title) : '',
            type: data.type ? String(data.type) : '',
            issuer: data.issuer ? String(data.issuer) : '',
            date: data.date
                ? (data.date instanceof Date
                    ? formatDate(data.date)
                    : (typeof data.date === 'string' && data.date.includes('T')
                        ? data.date.slice(0, 10)
                        : data.date))
                : '',
            description: data.description ? String(data.description) : '',
            url: data.noCredentialUrl ? '' : (data.url ? String(data.url) : ''),
            credential_picture: data.credential_picture ?? undefined,
        }))
        .post(route('profile.achievements.add'), {
            forceFormData: true,
            onSuccess: (response) => {
                closeAddAchievementModal();
                successMessage.value = 'Achievement added successfully!';
                isSuccessModalOpen.value = true;
                resetAchievementForm();
            },
            onError: (errors) => {
                console.error(errors);
                if (errors.duplicate) {
                    isDuplicateModalOpen.value = true;
                } else {
                    errorMessage.value = 'Failed to add achievement. Please check the form and try again.';
                    isErrorModalOpen.value = true;
                }
            }
        });
};
const removeAchievement = (id) => {
    if (confirm('Are you sure you want to remove this achievement? This action cannot be undone.')) {
        achievementForm.delete(route('profile.achievements.remove', id), {
            onSuccess: () => {
                successMessage.value = 'Achievement removed successfully!';
                isSuccessModalOpen.value = true;
                // Optionally reload data here
            },
            onError: () => {
                errorMessage.value = 'Failed to remove achievement. Please try again.';
                isErrorModalOpen.value = true;
            }
        });
    }
};

</script>

<template>
    <div v-if="activeSection === 'achievements'" class="flex flex-col lg:flex-row">
        <!-- Success Modal -->
        <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-blue-100 rounded-full p-2">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
                <div class="flex justify-center">
                    <button @click="closeSuccessModal"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        OK
                    </button>
                </div>
            </div>
        </Modal>
        <!-- Confirmation Modal -->
        <Modal :show="confirmModal.show" @close="closeConfirm">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div :class="{
                        'bg-amber-100': confirmModal.type === 'archive' || confirmModal.type === 'unarchive',
                        'bg-red-100': confirmModal.type === 'delete'
                    }" class="rounded-full p-3">
                        <i v-if="confirmModal.type === 'archive'" class="fas fa-archive text-amber-500 text-xl"></i>
                        <i v-else-if="confirmModal.type === 'unarchive'" class="fas fa-undo text-green-500 text-xl"></i>
                        <i v-else-if="confirmModal.type === 'delete'" class="fas fa-trash text-red-500 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center text-gray-900 mb-2">
                    {{ confirmModal.confirmLabel }} Achievement
                </h3>
                <p class="text-center text-gray-600 mb-4">{{ confirmModal.message }}</p>
                <div class="mt-6 flex justify-center space-x-2">
                    <button type="button" @click="closeConfirm"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
                        Cancel
                    </button>
                    <button v-if="confirmModal.type === 'delete'" type="button" @click="confirmModal.confirmAction"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-200">Delete</button>
                    <button v-else type="button"
                        :class="confirmModal.type === 'archive' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700'"
                        class="text-white px-4 py-2 rounded transition duration-200"
                        @click="confirmModal.confirmAction">{{
                            confirmModal.confirmLabel }}</button>
                </div>
            </div>
        </Modal>

        <!-- Error Modal -->
        <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-red-100 rounded-full p-2">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
                <div class="flex justify-center">
                    <button @click="closeErrorModal"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        OK
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Duplicate Modal -->
        <Modal :modelValue="isDuplicateModalOpen" @close="closeDuplicateModal">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-yellow-100 rounded-full p-2">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center mb-4">This achievement already exists.</h3>
                <div class="flex justify-center">
                    <button @click="closeDuplicateModal"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        OK
                    </button>
                </div>
            </div>
        </Modal>
        <div class="w-full mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Achievements</h1>
                    <p class="mt-2 text-gray-600">Showcase your awards, recognitions, publications, and patents</p>
                </div>
                <div class="flex space-x-4">
                    <PrimaryButton class="flex items-center" @click="openAddAchievementModal">
                        <i class="fas fa-plus mr-2"></i>
                        Add Achievement
                    </PrimaryButton>
                    <SecondaryButton class="flex items-center cursor-pointer"
                        @click="showArchivedAchievements = !showArchivedAchievements">
                        <i class="fas" :class="showArchivedAchievements ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span class="ml-2">{{ showArchivedAchievements ? 'Hide' : 'Show' }} Archived</span>
                    </SecondaryButton>
                </div>
            </div>

            <!-- Achievement Entries -->
            <div>
                <h2 class="text-lg font-medium mb-4">Active Achievement</h2>
                <div v-if="achievementEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="achievement in achievementEntries" :key="achievement.id"
                        class="bg-white rounded-lg shadow-md p-4 space-y-3">
                        <div class="border-b pb-2">
                            <h3 class="text-lg font-semibold">{{ achievement.title }}</h3>
                            <span class="text-sm text-gray-600">{{ achievement.type }}</span>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm"><span class="font-medium">Issuer:</span> {{
                                achievement.issuer }}</p>
                            <p class="text-sm"><span class="font-medium">Date:</span> {{
                                formatDate(formatDate(achievement.date)) }}</p>
                        </div>
                        <div class="text-sm">
                            <p class="text-sm"><span class="font-medium">Description:</span> {{
                                achievement.description }}</p>
                        </div>
                        <div class="text-sm">
                            <p class="mt-2">
                                URL:
                                <span v-if="achievement.url">
                                    <a :href="achievement.url">{{
                                        achievement.url }}</a>
                                </span>
                                <span v-else class="text-gray-500">No URL</span>
                            </p>
                        </div>
                        <div v-if="achievement.credential_picture" class="mt-3">
                            <img :src="`/storage/${achievement.credential_picture}`" :alt="achievement.title"
                                class="max-w-full h-auto rounded-lg shadow" />
                        </div>
                        <div class="flex justify-end space-x-2 mt-3">
                            <button @click="openUpdateAchievementModal(achievement)"
                                class="inline-flex items-center px-2 py-1 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition ease-in-out duration-150">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                class="inline-flex items-center px-2 py-1 bg-amber-100 border border-amber-300 rounded-md font-semibold text-xs text-amber-700 hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 transition ease-in-out duration-150"
                                @click="archiveAchievement(achievement)">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Achievement Entries Message -->
                <div v-else class="bg-white p-8 rounded-lg shadow mb-4">
                    <p class="text-gray-600">No achievement entries added yet.</p>
                </div>
            </div>

            <!-- Archived Achievement Entries -->
            <div v-if="showArchivedAchievements" class="mt-8">
                <h2 class="text-lg font-medium mb-4">Archived Achievements</h2>
                <div v-if="archivedAchievementEntries.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="achievement in archivedAchievementEntries" :key="achievement.id"
                        class="bg-gray-50 rounded-lg shadow-md p-4 space-y-3 border border-gray-200 relative">
                        <div class="opacity-75">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold">{{ achievement.title }}</h3>
                                <span class="text-sm text-gray-600">{{ achievement.type
                                    }}</span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm"><span class="font-medium">Issuer:</span> {{
                                    achievement.issuer }}</p>
                                <p class="text-sm"><span class="font-medium">Date:</span> {{
                                    formatDate(formatDate(achievement.date)) }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-sm"><span class="font-medium">Description:</span> {{
                                    achievement.description }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="mt-2">
                                    URL:
                                    <span v-if="achievement.url && !achievement.noCredentialUrl">
                                        <a :href="achievement.url" class="text-blue-600 hover:underline"
                                            target="_blank">
                                            {{ achievement.url }}
                                        </a>
                                    </span>
                                    <span v-else class="text-gray-500">No URL</span>
                                </p>
                            </div>
                            <div v-if="achievement.credential_picture_url" class="mt-3">
                                <img :src="`/storage/${achievement.credential_picture_url}`" :alt="achievement.title"
                                    class="max-w-full h-auto rounded-lg shadow" />
                            </div>
                        </div>
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button
                                class="inline-flex items-center px-2 py-1 bg-green-100 border border-green-300 rounded-md font-semibold text-xs text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition ease-in-out duration-150"
                                @click="unarchiveAchievement(achievement)">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button
                                class="inline-flex items-center px-2 py-1 bg-red-100 border border-red-300 rounded-md font-semibold text-xs text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                                @click="deleteAchievement(achievement)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                            Archived
                        </div>
                    </div>
                </div>

                <!-- If no archived achievements exist -->
                <div v-else class="bg-white p-8 rounded-lg shadow">
                    <p class="text-gray-600">No archived achievements found.</p>
                </div>
            </div>
        </div>

        <!-- Add Achievement Modal -->
        <div v-if="isAddAchievementModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Add Achievement</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeAddAchievementModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">Add details about awards, recognition, or other achievements</p>
                <form @submit.prevent="addAchievement" enctype="multipart/form-data">
                    <div class="max-h-96 overflow-y-auto">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Title</label>
                            <input type="text" v-model="achievementForm.title"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Name of the Award or Certification" required>
                        </div>
                        <div class="mb-4">
                            <label for="achievementType" class="block text-gray-700">Achievement Type <span
                                    class="text-red-500">*</span></label>
                            <select id="achievementType" name="achievement_type" v-model="achievementForm.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                <option value="" disabled>Select achievement type</option>
                                <option value="Award">Award</option>
                                <option value="Recognition">Recognition</option>
                                <option value="Publication">Publication</option>
                                <option value="Patent">Patent</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Issuer <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="achievement_issuer" v-model="achievementForm.issuer"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Name of the Organization or Institution" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Date <span
                                    class="text-red-500">*</span></label>
                            <Datepicker name="achievement_date" v-model="achievementForm.date"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Select start date" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Description</label>
                            <textarea name="achievement_description" v-model="achievementForm.description"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Describe your achievement"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">URL</label>
                            <input type="url" v-model="achievementForm.url"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="e.g. https://example.com" :required="!achievementForm.noCredentialUrl"
                                :disabled="achievementForm.noCredentialUrl" />
                            <div class="mt-2">
                                <input type="checkbox" id="no-credential-url" v-model="achievementForm.noCredentialUrl"
                                    class="mr-2" />
                                <label for="no-credential-url" class="text-sm text-gray-700">No URL</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Credential Picture</label>
                            <input type="file" name="credential_picture" accept="image/*"
                                @change="handleCredentialPictureUpload"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" />
                            <img v-if="previewImage" :src="previewImage" class="mt-2 max-w-xs rounded"
                                alt="Credential Preview" />
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center"
                                :disabled="isSubmittingAchievement">
                                <i class="fas fa-spinner fa-spin mr-2" v-if="isSubmittingAchievement"></i>
                                <i class="fas fa-save mr-2" v-else></i>
                                {{ isSubmittingAchievement ? 'Saving...' : 'Add Achievement' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Achievement Modal -->
        <div v-if="isUpdateAchievementModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Update Achievement</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateAchievementModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">Update details about your achievement</p>
                <form @submit.prevent="updateAchievement" enctype="multipart/form-data">
                    <div class="max-h-96 overflow-y-auto">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Title <span
                                    class="text-red-500">*</span></label>
                            <input type="text" v-model="achievementForm.title"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Name of the Award or Certification" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Type</label>
                            <select v-model="achievementForm.type"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="" disabled>Select type</option>
                                <option value="Award">Award</option>
                                <option value="Recognition">Recognition</option>
                                <option value="Publication">Publication</option>
                                <option value="Patent">Patent</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Issuer <span
                                    class="text-red-500">*</span></label>
                            <input type="text" v-model="achievementForm.issuer"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Name of the Organization or Institution" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Date <span
                                    class="text-red-500">*</span></label>
                            <Datepicker v-model="achievementForm.date"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Select date" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Description</label>
                            <textarea v-model="achievementForm.description"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                rows="3" placeholder="Describe your achievement..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">URL</label>
                            <input type="url" v-model="achievementForm.url"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="e.g. https://example.com" :required="!achievementForm.noCredentialUrl"
                                :disabled="achievementForm.noCredentialUrl" />
                        </div>
                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="achievementForm.noCredentialUrl"
                                    class="form-checkbox h-5 w-5 text-blue-600" />
                                <span class="ml-2 text-gray-700">No URL</span>
                            </label>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Credential Picture</label>
                            <input type="file" accept="image/*" @change="handleCredentialPictureUpload"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" />
                            <img v-if="achievementForm.credential_picture_url"
                                :src="achievementForm.credential_picture_url" class="mt-2 max-w-xs rounded"
                                alt="Credential Preview" />
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center">
                                <i class="fas fa-save mr-2"></i>
                                Update Achievement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>
</template>

<style scoped>
/* Glow effects for form elements */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

input:focus,
textarea:focus,
select:focus {
    animation: pulse 1.5s infinite;
    transition: all 0.3s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.form-group {
    animation: fadeIn 0.6s ease-out;
}

.modal-content {
    animation: fadeIn 0.4s ease-out;
}
</style>