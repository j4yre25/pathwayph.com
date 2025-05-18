<script setup>
import { ref, computed, onMounted, watch } from 'vue';
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

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'achievements'
  }
});

// State variables
const achievementEntries = ref([props.achievementEntries || []]);
const archivedAchievementEntries = ref([]);
const showArchivedAchievements = ref(false);
const isAddAchievementModalOpen = ref(false);
const isUpdateAchievementModalOpen = ref(false);
const isSubmittingAchievement = ref(false);
const previewImage = ref(null);

// Achievement form
const achievementForm = useForm({
  id: null,
  graduate_achievement_title: '',
  graduate_achievement_type: '',
  graduate_achievement_issuer: '',
  graduate_achievement_date: null,
  graduate_achievement_description: '',
  graduate_achievement_url: '',
  noCredentialUrl: false,
  credential_picture: null,
  credential_picture_url: null
});

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Close success modal
const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
};

// Close error modal
const closeErrorModal = () => {
  isErrorModalOpen.value = false;
};

// Close duplicate modal
const closeDuplicateModal = () => {
  isDuplicateModalOpen.value = false;
};

// Methods
const formatDate = (date) => {
  if (!date) return '';
  return format(new Date(date), 'MMM dd, yyyy');
};

const handleCredentialPictureUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    achievementForm.credential_picture = file;
    previewImage.value = URL.createObjectURL(file);
  }
};

const closeAddAchievementModal = () => {
  isAddAchievementModalOpen.value = false;
  resetAchievementForm();
};

const closeUpdateAchievementModal = () => {
  isUpdateAchievementModalOpen.value = false;
  resetAchievementForm();
};

const resetAchievementForm = () => {
  achievementForm.reset();
  achievementForm.clearErrors();
  previewImage.value = null;
};

const addAchievement = () => {
  achievementForm.post(route('profile.achievements.add'), {
    onSuccess: () => {
      closeAddAchievementModal();
      successMessage.value = 'Achievement added successfully!';
      isSuccessModalOpen.value = true;
      fetchAchievements();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add achievement. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

const editAchievement = (achievement) => {
  achievementForm.id = achievement.id;
  achievementForm.graduate_achievement_title = achievement.graduate_achievement_title;
  achievementForm.graduate_achievement_type = achievement.graduate_achievement_type;
  achievementForm.graduate_achievement_issuer = achievement.graduate_achievement_issuer;
  achievementForm.graduate_achievement_date = new Date(achievement.graduate_achievement_date);
  achievementForm.graduate_achievement_description = achievement.graduate_achievement_description;
  achievementForm.graduate_achievement_url = achievement.graduate_achievement_url;
  achievementForm.noCredentialUrl = !achievement.graduate_achievement_url;
  achievementForm.credential_picture = null;
  achievementForm.credential_picture_url = achievement.credential_picture_url ? `/storage/${achievement.credential_picture_url}` : null;
  isUpdateAchievementModalOpen.value = true;
};

const updateAchievement = () => {
  achievementForm.put(route('profile.achievements.update', achievementForm.id), {
    onSuccess: () => {
      closeUpdateAchievementModal();
      successMessage.value = 'Achievement updated successfully!';
      isSuccessModalOpen.value = true;
      fetchAchievements();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update achievement. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

const archiveAchievement = (achievement) => {
  achievementForm.put(route('profile.achievements.archive', achievement.id), {
    onSuccess: () => {
      successMessage.value = 'Achievement archived successfully!';
      isSuccessModalOpen.value = true;
      fetchAchievements();
    },
    onError: () => {
      errorMessage.value = 'Failed to archive achievement. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

const unarchiveAchievement = (achievement) => {
  achievementForm.post(route('profile.achievements.unarchive', achievement.id), {
    onSuccess: () => {
      successMessage.value = 'Achievement unarchived successfully!';
      isSuccessModalOpen.value = true;
      fetchAchievements();
    },
    onError: () => {
      errorMessage.value = 'Failed to unarchive achievement. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

const removeAchievement = (id) => {
  if (confirm('Are you sure you want to remove this achievement? This action cannot be undone.')) {
    achievementForm.delete(route('profile.achievements.remove', id), {
      onSuccess: () => {
        successMessage.value = 'Achievement removed successfully!';
        isSuccessModalOpen.value = true;
        fetchAchievements();
      },
      onError: () => {
        errorMessage.value = 'Failed to remove achievement. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};

const addPreferredLocation = () => {
  // This method is referenced in the template but not used in this component
  // It's likely a copy-paste error from another component
};

const removePreferredLocation = () => {
  // This method is referenced in the template but not used in this component
  // It's likely a copy-paste error from another component
};

// Fetch achievements on component mount
onMounted(() => {
  fetchAchievements();
});

// Fetch achievements function
const fetchAchievementsForm = useForm({});

const fetchAchievements = () => {
  router.get(route('profile.index'), {}, {
    onSuccess: (response) => {
      const data = response.props;
      // Get active achievements
      if (data.achievementEntries) {
        achievementEntries.value = data.achievementEntries;
      }
      
      // Get archived achievements
      if (data.archivedAchievementEntries) {
        archivedAchievementEntries.value = data.archivedAchievementEntries;
      }
    },
    onError: () => {
      errorMessage.value = 'Failed to fetch achievements. Please refresh the page.';
      isErrorModalOpen.value = true;
    },
    preserveState: true
  });
}

</script>

<template>
        <div v-if="activeSection === 'achievements'" class="flex flex-col lg:flex-row">
            <!-- Success Modal -->
            <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
                <div class="p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-green-100 rounded-full p-2">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
                    <div class="flex justify-center">
                        <button @click="closeSuccessModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            OK
                        </button>
                    </div>
                </div>
            </Modal>
            
            <!-- Error Modal -->
            <Modal :show="isErrorModalOpen" @close="closeErrorModal">
                <div class="p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-red-100 rounded-full p-2">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
                    <div class="flex justify-center">
                        <button @click="closeErrorModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            OK
                        </button>
                    </div>
                </div>
            </Modal>
            
            <!-- Duplicate Modal -->
            <Modal :show="isDuplicateModalOpen" @close="closeDuplicateModal">
                <div class="p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-yellow-100 rounded-full p-2">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-medium text-center mb-4">This achievement already exists.</h3>
                    <div class="flex justify-center">
                        <button @click="closeDuplicateModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            OK
                        </button>
                    </div>
                </div>
            </Modal>
            <div class="w-full mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-semibold mb-4">Achievements</h1>
                    <div class="flex space-x-4">
                        <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center" @click="isAddAchievementModalOpen = true">
                            <i class="fas fa-plus mr-2"></i>
                            Add Achievement
                        </PrimaryButton>
                        <button 
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
                            @click="showArchivedAchievements = !showArchivedAchievements">
                            <i class="fas" :class="showArchivedAchievements ? 'fa-eye-slash' : 'fa-eye'"></i>
                            <span class="ml-2">{{ showArchivedAchievements ? 'Hide Archived' : 'Show Archived' }}</span>
                        </button>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">Showcase your awards and recognition</p>

                <!-- Achievement Entries -->
                <div>
                    <h2 class="text-lg font-medium mb-4">Active Achievement</h2>
                    <div v-if="achievementEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="achievement in achievementEntries" :key="achievement.id" class="bg-white rounded-lg shadow-md p-4 space-y-3">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold">{{ achievement.graduate_achievement_title }}</h3>
                                <span class="text-sm text-gray-600">{{ achievement.graduate_achievement_type }}</span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm"><span class="font-medium">Issuer:</span> {{ achievement.graduate_achievement_issuer }}</p>
                                <p class="text-sm"><span class="font-medium">Date:</span> {{ formatDate(achievement.graduate_achievement_date) }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-sm"><span class="font-medium">Description:</span> {{ achievement.graduate_achievement_description }}</p>
                            </div>
                            <div class="text-sm">
                            <p class="mt-2">
                                URL:
                                <span v-if="achievement.graduate_achievement_url && !achievement.noCredentialUrl">
                                <a :href="achievement.graduate_achievement_url" class="text-blue-600 hover:underline" target="_blank">
                                    {{ achievement.graduate_achievement_url }}
                                </a>
                                </span>
                                <span v-else class="text-gray-500">No URL</span>
                            </p>
                            </div>
                            <div v-if="achievement.credential_picture_url" class="mt-3">
                            <img :src="`/storage/${achievement.credential_picture_url}`" 
                                :alt="achievement.graduate_achievement_title"
                                class="max-w-full h-auto rounded-lg shadow"/>
                            </div>
                            <div class="flex justify-end space-x-2 mt-3">
                                <button @click="editAchievement(achievement)" 
                                    class="text-gray-600 hover:text-indigo-600">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button @click="removeAchievement(achievement)" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
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
                    <div v-if="archivedAchievementEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="achievement in archivedAchievementEntries" :key="achievement.id" class="bg-gray-50 rounded-lg shadow-md p-4 space-y-3 border border-gray-200 relative">
                            <div class="opacity-75">
                                <div class="border-b pb-2">
                                    <h3 class="text-lg font-semibold">{{ achievement.graduate_achievement_title }}</h3>
                                    <span class="text-sm text-gray-600">{{ achievement.graduate_achievement_type }}</span>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm"><span class="font-medium">Issuer:</span> {{ achievement.graduate_achievement_issuer }}</p>
                                    <p class="text-sm"><span class="font-medium">Date:</span> {{ formatDate(achievement.graduate_achievement_date) }}</p>
                                </div>
                                <div class="text-sm">
                                    <p class="text-sm"><span class="font-medium">Description:</span> {{ achievement.graduate_achievement_description }}</p>
                                </div>
                                <div class="text-sm">
                                    <p class="mt-2">
                                        URL:
                                        <span v-if="achievement.graduate_achievement_url && !achievement.noCredentialUrl">
                                        <a :href="achievement.graduate_achievement_url" class="text-blue-600 hover:underline" target="_blank">
                                            {{ achievement.graduate_achievement_url }}
                                        </a>
                                        </span>
                                        <span v-else class="text-gray-500">No URL</span>
                                    </p>
                                </div>
                                <div v-if="achievement.credential_picture_url" class="mt-3">
                                    <img :src="`/storage/${achievement.credential_picture_url}`" 
                                        :alt="achievement.graduate_achievement_title"
                                        class="max-w-full h-auto rounded-lg shadow"/>
                                </div>
                            </div>
                            <div class="absolute top-2 right-2 flex space-x-2">
                                <button class="text-green-600 hover:text-green-800" @click="unarchiveAchievement(achievement)">
                                    <i class="fas fa-box-open"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800" @click="deleteAchievement(achievement.id)">
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
            <div v-if="isAddAchievementModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
                                <input type="text" 
                                    v-model="achievementForm.graduate_achievement_title"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Name of the Award or Certification" 
                                    required>
                            </div>
                            <div class="mb-4">
                                <label for="achievementType" class="block text-gray-700">Achievement Type <span class="text-red-500">*</span></label>
                                <select id="achievementType" name="graduate_achievement_type" v-model="achievementForm.graduate_achievement_type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="" disabled>Select achievement type</option>
                                    <option value="Award">Award</option>
                                    <option value="Recognition">Recognition</option>
                                    <option value="Publication">Publication</option>
                                    <option value="Patent">Patent</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
                                <input type="text" name="graduate_achievement_issuer" v-model="achievementForm.graduate_achievement_issuer"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Name of the Organization or Institution" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Date <span class="text-red-500">*</span></label>
                                <Datepicker name="graduate_achievement_date" v-model="achievementForm.graduate_achievement_date"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                placeholder="Select start date" required />
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Description</label>
                                <textarea name="graduate_achievement_description" v-model="achievementForm.graduate_achievement_description"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Describe your achievement"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">URL</label>
                                <input type="url" v-model="achievementForm.graduate_achievement_url"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="e.g. https://example.com" 
                                    :required="!achievementForm.noCredentialUrl"
                                    :disabled="achievementForm.noCredentialUrl" />
                                <div class="mt-2">
                                    <input type="checkbox" id="no-credential-url" v-model="achievementForm.noCredentialUrl" class="mr-2" />
                                    <label for="no-credential-url" class="text-sm text-gray-700">No URL</label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Credential Picture</label>
                                <input
                                    type="file"
                                    name="credential_picture"
                                    accept="image/*"
                                    @change="handleCredentialPictureUpload"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"/>
                                <img
                                    v-if="previewImage"
                                    :src="previewImage"
                                    class="mt-2 max-w-xs rounded"
                                    alt="Credential Preview"/>
                            </div>
                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center"
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
            <div v-if="isUpdateAchievementModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Update Achievement</h2>
                        <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateAchievementModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Update details about your achievement</p>
                    <form @submit.prevent="updateAchievement">
                        <div class="max-h-96 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Title <span class="text-red-500">*</span></label>
                                <input type="text" v-model="achievementForm.graduate_achievement_title"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Name of the Award or Certification" required />
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Type</label>
                                <select v-model="achievementForm.graduate_achievement_type"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                    <option value="" disabled>Select type</option>
                                    <option value="Award">Award</option>
                                    <option value="Recognition">Recognition</option>
                                    <option value="Publication">Publication</option>
                                    <option value="Patent">Patent</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
                                <input type="text" v-model="achievementForm.graduate_achievement_issuer"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Name of the Organization or Institution" required />
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Date <span class="text-red-500">*</span></label>
                                <Datepicker v-model="achievementForm.graduate_achievement_date"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Select date" required />
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Description</label>
                                <textarea v-model="achievementForm.graduate_achievement_description"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    rows="3" placeholder="Describe your achievement..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">URL</label>
                                <input type="url" v-model="achievementForm.graduate_achievement_url"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                    placeholder="e.g. https://example.com" 
                                    :required="!achievementForm.noCredentialUrl"
                                    :disabled="achievementForm.noCredentialUrl" />
                            </div>
                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" v-model="achievementForm.noCredentialUrl"
                                    class="form-checkbox h-5 w-5 text-indigo-600" />
                                    <span class="ml-2 text-gray-700">No URL</span>
                                </label>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Credential Picture</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleCredentialPictureUpload"
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"/>
                                <img
                                    v-if="achievementForm.credential_picture_url"
                                    :src="achievementForm.credential_picture_url"
                                    class="mt-2 max-w-xs rounded"
                                    alt="Credential Preview"/>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
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