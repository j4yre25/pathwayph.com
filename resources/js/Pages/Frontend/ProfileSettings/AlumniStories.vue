<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'alumniStories'
  },
  alumniStoriesEntries: {
    type: Array,
    default: () => []
  },
  archivedAlumniStoriesEntries: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close-all-modals', 'reset-all-states']);

// State variables
const alumniStoriesEntries = ref(props.alumniStoriesEntries || []);
const archivedAlumniStoriesEntries = ref(props.archivedAlumniStoriesEntries || []);
const showArchivedStories = ref(false);
const isAddStoryModalOpen = ref(false);
const isUpdateStoryModalOpen = ref(false);
const currentStoryId = ref(null);
const previewImage = ref(null);

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Alumni Story form
const storyForm = useForm({
  id: null,
  title: '',
  content: '',
  image: null,
  video_url: '',
  is_featured: false
});

// File upload handling
const handleFileUpload = (event) => {
  storyForm.image = event.target.files[0];
  
  // Create preview URL
  if (storyForm.image) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImage.value = e.target.result;
    };
    reader.readAsDataURL(storyForm.image);
  } else {
    previewImage.value = null;
  }
};

// Toggle archived stories
const toggleArchivedStories = () => {
  showArchivedStories.value = !showArchivedStories.value;
};

// Close add story modal
const closeAddStoryModal = () => {
  isAddStoryModalOpen.value = false;
  storyForm.reset();
  storyForm.clearErrors();
  previewImage.value = null;
};

// Close update story modal
const closeUpdateStoryModal = () => {
  isUpdateStoryModalOpen.value = false;
  storyForm.reset();
  storyForm.clearErrors();
  previewImage.value = null;
};

// Edit story
const editStory = (story) => {
  storyForm.id = story.id;
  storyForm.title = story.title;
  storyForm.content = story.content;
  storyForm.video_url = story.video_url || '';
  storyForm.is_featured = story.is_featured;
  storyForm.image = null;
  
  // Set preview image if exists
  if (story.image) {
    previewImage.value = `/storage/${story.image}`;
  } else {
    previewImage.value = null;
  }
  
  isUpdateStoryModalOpen.value = true;
};

// Add story function
const addStory = () => {
  storyForm.post(route('profile.alumni-stories.add'), {
    forceFormData: true,
    onSuccess: () => {
      closeAddStoryModal();
      successMessage.value = 'Alumni story added successfully!';
      isSuccessModalOpen.value = true;
      fetchStories();
    },
    onError: () => {
      errorMessage.value = 'Failed to add alumni story. Please check the form and try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Update story function
const updateStory = () => {
  storyForm.post(route('profile.alumni-stories.update', { id: storyForm.id }), {
    forceFormData: true,
    onSuccess: () => {
      closeUpdateStoryModal();
      successMessage.value = 'Alumni story updated successfully!';
      isSuccessModalOpen.value = true;
      fetchStories();
    },
    onError: () => {
      errorMessage.value = 'Failed to update alumni story. Please check the form and try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Delete story variables
  const isDeleteModalOpen = ref(false);
  const storyToDeleteId = ref(null);

  // Delete story function
  const deleteStory = (id) => {
    storyToDeleteId.value = id;
    isDeleteModalOpen.value = true;
  };

  // Close delete modal
  const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    storyToDeleteId.value = null;
  };

  // Confirm delete
  const confirmDelete = () => {
    router.delete(route('profile.alumni-stories.delete', { id: storyToDeleteId.value }), {
      onSuccess: () => {
        closeDeleteModal();
        successMessage.value = 'Alumni story deleted successfully!';
        isSuccessModalOpen.value = true;
        fetchStories();
      },
      onError: () => {
        closeDeleteModal();
        errorMessage.value = 'Failed to delete alumni story. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  };

// Restore story function
const restoreStory = (id) => {
  router.put(route('profile.alumni-stories.restore', { id }), {}, {
    onSuccess: () => {
      successMessage.value = 'Alumni story restored successfully!';
      isSuccessModalOpen.value = true;
      fetchStories();
    },
    onError: () => {
      errorMessage.value = 'Failed to restore alumni story. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Close success modal
const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
};

// Close error modal
const closeErrorModal = () => {
  isErrorModalOpen.value = false;
};

// Close all modals - emits event to parent
const closeAllModals = () => {
  closeAddStoryModal();
  closeUpdateStoryModal();
  closeDeleteModal();
  closeSuccessModal();
  closeErrorModal();
  emit('close-all-modals');
};

// Reset all states - emits event to parent
const resetAllStates = () => {
  storyForm.reset();
  storyForm.clearErrors();
  previewImage.value = null;
  currentStoryId.value = null;
  showArchivedStories.value = false;
  emit('reset-all-states');
};

// Fetch stories
const fetchStories = () => {
  router.reload({
    only: ['alumniStoriesEntries', 'archivedAlumniStoriesEntries'],
    onSuccess: (page) => {
      alumniStoriesEntries.value = page.props.alumniStoriesEntries;
      archivedAlumniStoriesEntries.value = page.props.archivedAlumniStoriesEntries;
    }
  });
};

// Watch for changes in props
watch(
  () => props.alumniStoriesEntries,
  (newValue) => {
    if (newValue) {
      alumniStoriesEntries.value = newValue;
    }
  }
);

watch(
  () => props.archivedAlumniStoriesEntries,
  (newValue) => {
    if (newValue) {
      archivedAlumniStoriesEntries.value = newValue;
    }
  }
);

// Initialize component on mount
onMounted(() => {
  // If no stories are loaded, fetch them
  if (alumniStoriesEntries.value.length === 0 && props.alumniStoriesEntries.length === 0) {
    fetchStories();
  }
});
</script>

<template>
  <!-- Success Modal -->
  <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
    <div class="p-6">
      <div class="flex items-center mb-5">
        <div class="bg-green-50 rounded-full p-3 mr-3">
          <i class="fas fa-check text-green-500"></i>
        </div>
        <h2 class="text-lg font-medium text-gray-800">Success</h2>
      </div>
      <p class="mb-6 text-gray-600 text-sm">{{ successMessage }}</p>
      <div class="flex justify-end">
        <PrimaryButton @click="closeSuccessModal" class="bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:ring-green-500">
          OK
        </PrimaryButton>
      </div>
    </div>
  </Modal>

  <!-- Error Modal -->
  <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
    <div class="p-6">
      <div class="flex items-center mb-5">
        <div class="bg-red-50 rounded-full p-3 mr-3">
          <i class="fas fa-exclamation-triangle text-red-500"></i>
        </div>
        <h2 class="text-lg font-medium text-gray-800">Error</h2>
      </div>
      <p class="mb-6 text-gray-600 text-sm">{{ errorMessage }}</p>
      <div class="flex justify-end">
        <DangerButton @click="closeErrorModal">
          OK
        </DangerButton>
      </div>
    </div>
  </Modal>

  <!-- Add Story Modal -->
  <Modal :modelValue="isAddStoryModalOpen" @close="closeAddStoryModal" maxWidth="2xl">
    <div class="p-6">
      <div class="flex items-center mb-6">
        <div class="bg-indigo-50 rounded-full p-3 mr-3">
          <i class="fas fa-book-open text-indigo-500"></i>
        </div>
        <h2 class="text-xl font-medium text-gray-800">Add Alumni Story</h2>
      </div>
      
      <form @submit.prevent="addStory">
        <!-- Title -->
        <div class="mb-5">
          <InputLabel for="title" value="Title" class="text-sm font-medium text-gray-700 mb-1" />
          <TextInput
            id="title"
            type="text"
            class="mt-1 block w-full"
            v-model="storyForm.title"
            placeholder="Enter a compelling title for your story"
            required
            autofocus
          />
          <InputError :message="storyForm.errors.title" class="mt-2" />
        </div>
        
        <!-- Content -->
        <div class="mb-5">
          <InputLabel for="content" value="Content" class="text-sm font-medium text-gray-700 mb-1" />
          <textarea
            id="content"
            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
            v-model="storyForm.content"
            rows="5"
            placeholder="Share your journey, challenges, and achievements..."
            required
          ></textarea>
          <InputError :message="storyForm.errors.content" class="mt-2" />
        </div>
        
        <!-- Image -->
        <div class="mb-5">
          <InputLabel for="image" value="Image (Optional)" class="text-sm font-medium text-gray-700 mb-1" />
          <div class="mt-1 flex items-center">
            <label for="image" class="cursor-pointer px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
              <i class="fas fa-upload mr-2"></i> Choose File
            </label>
            <input
              id="image"
              type="file"
              class="hidden"
              @change="handleFileUpload"
              accept="image/*"
            />
            <span v-if="storyForm.image" class="ml-3 text-sm text-gray-600">{{ typeof storyForm.image === 'string' ? storyForm.image.split('/').pop() : storyForm.image.name }}</span>
            <span v-else class="ml-3 text-sm text-gray-500">No file selected</span>
          </div>
          <p class="mt-1 text-xs text-gray-500">Add an image that represents your story (recommended size: 1200x800px)</p>
          <InputError :message="storyForm.errors.image" class="mt-2" />
          
          <!-- Image Preview -->
          <div v-if="previewImage" class="mt-3">
            <p class="text-sm text-gray-600 mb-1">Image Preview:</p>
            <img :src="previewImage" alt="Preview" class="max-w-full h-auto max-h-40 rounded-lg shadow-sm" />
          </div>
        </div>
        
        <!-- Video URL -->
        <div class="mb-5">
          <InputLabel for="video_url" value="Video URL (Optional)" class="text-sm font-medium text-gray-700 mb-1" />
          <TextInput
            id="video_url"
            type="url"
            class="mt-1 block w-full"
            v-model="storyForm.video_url"
            placeholder="https://www.youtube.com/watch?v=..."
          />
          <p class="mt-1 text-xs text-gray-500">Add a link to your video on YouTube, Vimeo, etc.</p>
          <InputError :message="storyForm.errors.video_url" class="mt-2" />
        </div>
        
        <!-- Featured -->
        <div class="mb-6">
          <label class="flex items-center">
            <input
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
              v-model="storyForm.is_featured"
            />
            <span class="ml-2 text-sm text-gray-600">Mark as featured story</span>
          </label>
          <p class="mt-1 text-xs text-gray-500 ml-6">Featured stories appear prominently on your profile</p>
          <InputError :message="storyForm.errors.is_featured" class="mt-2" />
        </div>
        
        <!-- Actions -->
        <div class="flex justify-end space-x-3">
          <SecondaryButton type="button" @click="closeAddStoryModal">
            Cancel
          </SecondaryButton>
          <PrimaryButton type="submit" :disabled="storyForm.processing">
             <i class="fas fa-save mr-2"></i> Save Story
           </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>

  <!-- Update Story Modal -->
  <Modal :modelValue="isUpdateStoryModalOpen" @close="closeUpdateStoryModal" maxWidth="2xl">
    <div class="p-6">
      <div class="flex items-center mb-6">
        <div class="bg-indigo-50 rounded-full p-3 mr-3">
          <i class="fas fa-edit text-indigo-500"></i>
        </div>
        <h2 class="text-xl font-medium text-gray-800">Edit Alumni Story</h2>
      </div>
      
      <form @submit.prevent="updateStory">
        <!-- Title -->
        <div class="mb-5">
          <InputLabel for="update_title" value="Title" class="text-sm font-medium text-gray-700 mb-1" />
          <TextInput
            id="update_title"
            type="text"
            class="mt-1 block w-full"
            v-model="storyForm.title"
            required
            placeholder="Enter a compelling title for your story"
          />
          <InputError :message="storyForm.errors.title" class="mt-2" />
        </div>
        
        <!-- Content -->
        <div class="mb-5">
          <InputLabel for="update_content" value="Content" class="text-sm font-medium text-gray-700 mb-1" />
          <textarea
            id="update_content"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            v-model="storyForm.content"
            rows="5"
            required
            placeholder="Share your journey, challenges, and achievements..."
          ></textarea>
          <InputError :message="storyForm.errors.content" class="mt-2" />
        </div>
        
        <!-- Image -->
        <div class="mb-5">
          <InputLabel for="update_image" value="Image (Optional)" class="text-sm font-medium text-gray-700 mb-1" />
          <div class="mt-1 flex items-center">
            <label for="update_image" class="cursor-pointer px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
              <i class="fas fa-upload mr-2"></i> Choose File
            </label>
            <input
              id="update_image"
              type="file"
              class="hidden"
              @change="handleFileUpload"
              accept="image/*"
            />
            <span v-if="storyForm.image && typeof storyForm.image !== 'string'" class="ml-3 text-sm text-gray-600">{{ storyForm.image.name }}</span>
            <span v-else-if="previewImage" class="ml-3 text-sm text-gray-600">Current image</span>
            <span v-else class="ml-3 text-sm text-gray-500">No file selected</span>
          </div>
          <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current image (recommended size: 1200x800px)</p>
          <InputError :message="storyForm.errors.image" class="mt-2" />
          
          <!-- Image Preview -->
          <div v-if="previewImage" class="mt-3">
            <p class="text-sm text-gray-600 mb-1">Image Preview:</p>
            <img :src="previewImage" alt="Preview" class="max-w-full h-auto max-h-40 rounded-lg shadow-sm" />
          </div>
        </div>
        
        <!-- Video URL -->
        <div class="mb-5">
          <InputLabel for="update_video_url" value="Video URL (Optional)" class="text-sm font-medium text-gray-700 mb-1" />
          <TextInput
            id="update_video_url"
            type="url"
            class="mt-1 block w-full"
            v-model="storyForm.video_url"
            placeholder="https://www.youtube.com/watch?v=..."
          />
          <p class="mt-1 text-xs text-gray-500">Add a link to your video on YouTube, Vimeo, etc.</p>
          <InputError :message="storyForm.errors.video_url" class="mt-2" />
        </div>
        
        <!-- Featured -->
        <div class="mb-6">
          <label class="flex items-center">
            <input
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
              v-model="storyForm.is_featured"
            />
            <span class="ml-2 text-sm text-gray-600">Mark as featured story</span>
          </label>
          <p class="mt-1 text-xs text-gray-500 ml-6">Featured stories appear prominently on your profile</p>
          <InputError :message="storyForm.errors.is_featured" class="mt-2" />
        </div>
        
        <!-- Actions -->
        <div class="flex justify-end space-x-3">
          <SecondaryButton type="button" @click="closeUpdateStoryModal">
            Cancel
          </SecondaryButton>
          <PrimaryButton type="submit" :disabled="storyForm.processing">
             <i class="fas fa-save mr-2"></i> Update Story
           </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>

  <!-- Delete Confirmation Modal -->
  <Modal :modelValue="isDeleteModalOpen" @close="closeDeleteModal" maxWidth="md">
    <div class="p-6">
      <div class="flex items-center mb-5">
        <div class="bg-red-50 rounded-full p-3 mr-3">
          <i class="fas fa-exclamation-triangle text-red-500"></i>
        </div>
        <h2 class="text-xl font-medium text-gray-800">Delete Alumni Story</h2>
      </div>
      
      <p class="mb-6 text-gray-600 text-sm">Are you sure you want to delete this story? This action will archive the story and can be reversed later.</p>
      
      <div class="flex justify-end space-x-3">
        <SecondaryButton type="button" @click="closeDeleteModal">
           Cancel
         </SecondaryButton>
        <DangerButton @click="confirmDelete" :disabled="storyForm.processing">
           <i class="fas fa-trash-alt mr-2"></i> Delete Story
         </DangerButton>
      </div>
    </div>
  </Modal>

  <div class="py-8 space-y-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2 flex items-center">
          <i class="fas fa-book text-indigo-500 mr-2"></i>
          Alumni Stories
        </h2>
        <p class="text-gray-600 text-sm">Share your journey and inspire others with your success story</p>
      </div>
      <div>
        <PrimaryButton @click="isAddStoryModalOpen = true" class="text-sm flex items-center">
           <i class="fas fa-plus-circle mr-2"></i>  Add Your Story
         </PrimaryButton>
      </div>
    </div>

    <!-- Active Stories -->
    <div v-if="alumniStoriesEntries.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center transition-all duration-200 hover:shadow-md">
      <div class="flex flex-col items-center justify-center py-8">
        <div class="bg-indigo-50 rounded-full p-6 mb-6">
          <i class="fas fa-book-open text-4xl text-indigo-500"></i>
        </div>
        <h3 class="text-xl font-medium text-gray-800 mb-3">No Alumni Stories Yet</h3>
        <p class="text-gray-600 text-sm mb-6 max-w-md mx-auto">Share your journey and inspire others by adding your first alumni story. Your experiences can help guide future graduates.</p>
      </div>
    </div>

    <div v-else class="space-y-6">
      <div v-for="story in alumniStoriesEntries" :key="story.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200 hover:shadow-md group story-card-transition">
        <div class="flex flex-col md:flex-row">
          <!-- Image Section -->
          <div v-if="story.image" class="md:w-1/3 relative image-hover">
            <img :src="`/storage/${story.image}`" :alt="story.title" class="w-full h-full object-cover" style="max-height: 250px;" />
          </div>
          
          <!-- Content Section -->
          <div class="p-6 md:w-2/3">
            <div class="flex justify-between items-start mb-3">
              <h3 class="text-lg font-semibold text-gray-800">{{ story.title }}</h3>
              <div class="flex space-x-3">
                <button @click="editStory(story)" class="text-gray-400 hover:text-indigo-600 transition-colors duration-200">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="deleteStory(story.id)" class="text-gray-400 hover:text-red-500 transition-colors duration-200">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
            
            <div class="mb-4 flex items-center">
              <span v-if="story.is_featured" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 mr-2 featured-badge">
                <i class="fas fa-star mr-1 text-amber-500"></i> Featured
              </span>
              <span class="text-xs text-gray-500">Added on {{ new Date(story.created_at).toLocaleDateString() }}</span>
            </div>
            
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ story.content }}</p>
            
            <div v-if="story.video_url" class="mt-4">
              <a :href="story.video_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 transition-colors duration-200 flex items-center text-sm">
                <i class="fas fa-video mr-2"></i> Watch Video
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Archived Stories -->
    <div v-if="archivedAlumniStoriesEntries.length > 0" class="mt-12">
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center">
          <i class="fas fa-archive text-gray-400 mr-2"></i>
          <h3 class="text-lg font-medium text-gray-800">Archived Stories</h3>
        </div>
        <button 
          @click="toggleArchivedStories" 
          class="text-sm text-gray-500 hover:text-indigo-600 transition-colors duration-200 flex items-center px-3 py-1 rounded hover:bg-gray-50 btn-hover-effect"
        >
          <span>{{ showArchivedStories ? 'Hide' : 'Show' }}</span>
          <i :class="[showArchivedStories ? 'fa-chevron-up' : 'fa-chevron-down', 'fas ml-1']"></i>
        </button>
      </div>

      <div v-if="showArchivedStories" class="space-y-4">
        <div v-if="archivedAlumniStoriesEntries.length === 0" class="bg-gray-50 rounded-xl border border-gray-100 p-6 text-center">
          <p class="text-gray-500 text-sm">No archived stories</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="story in archivedAlumniStoriesEntries" :key="story.id" class="bg-gray-50 rounded-xl border border-gray-100 p-5 transition-all duration-200 hover:bg-gray-100">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-medium text-gray-700 text-base">{{ story.title }}</h4>
                <p class="text-xs text-gray-500 mt-1">Archived on {{ new Date(story.deleted_at).toLocaleDateString() }}</p>
              </div>
              <button @click="restoreStory(story.id)" class="text-indigo-500 hover:text-indigo-700 transition-colors duration-200 text-sm flex items-center btn-hover-effect px-3 py-1 rounded">
                <i class="fas fa-undo-alt mr-1"></i> Restore
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Story card styles */
.story-card-transition {
  transition: all 0.3s ease;
}

/* Image hover effect */
.image-hover {
  overflow: hidden;
}

.image-hover img {
  transition: transform 0.5s ease;
}

.image-hover:hover img {
  transform: scale(1.05);
}

/* Featured badge pulse effect */
.featured-badge {
  position: relative;
}

.featured-badge::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 9999px;
  box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(79, 70, 229, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
  }
}

/* Content line clamp for multi-line truncation */
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Button hover effects */
.btn-hover-effect {
  transition: all 0.3s ease;
}

.btn-hover-effect:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

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

.form-group {
  animation: fadeIn 0.6s ease-out;
}

.modal-content {
  animation: fadeIn 0.4s ease-out;
}
</style>