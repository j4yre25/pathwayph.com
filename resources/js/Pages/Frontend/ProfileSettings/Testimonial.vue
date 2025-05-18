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
import { isValid } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'testimonials'
  }
});

// State variables
const testimonialEntries = ref([props.testimonialEntries || []]);
const archivedTestimonialEntries = ref([]);
const showArchivedTestimonial = ref(false);
const isAddTestimonialsModalOpen = ref(false);
const isUpdateTestimonialsModalOpen = ref(false);
const currentTestimonialId = ref(null);
const previewImage = ref(null);

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Testimonial form
const testimonials = useForm({
  id: null,
  graduate_testimonial_author: '',
  graduate_testimonial_position: '',
  graduate_testimonial_company: '',
  graduate_testimonial_content: '',
  graduate_testimonial_file: null
});

// File upload handling
const handleFileUpload = (event) => {
  testimonials.graduate_testimonial_file = event.target.files[0];
};

// Toggle archived testimonials
const toggleArchivedTestimonial = () => {
  showArchivedTestimonial.value = !showArchivedTestimonial.value;
};

// Close add testimonial modal
const closeAddTestimonialsModal = () => {
  isAddTestimonialsModalOpen.value = false;
  testimonials.reset();
  testimonials.clearErrors();
};

// Close update testimonial modal
const closeUpdateTestimonialsModal = () => {
  isUpdateTestimonialsModalOpen.value = false;
  testimonials.reset();
  testimonials.clearErrors();
};

// Edit testimonial
const editTestimonial = (testimonial) => {
  currentTestimonialId.value = testimonial.id;
  testimonials.id = testimonial.id;
  testimonials.graduate_testimonial_author = testimonial.graduate_testimonial_author;
  testimonials.graduate_testimonial_position = testimonial.graduate_testimonial_position;
  testimonials.graduate_testimonial_company = testimonial.graduate_testimonial_company;
  testimonials.graduate_testimonial_content = testimonial.graduate_testimonial_content;
  isUpdateTestimonialsModalOpen.value = true;
};

// Add testimonial function
const addTestimonials = () => {
  testimonials.post('/profile/testimonials', {
    onSuccess: () => {
      closeAddTestimonialsModal();
      successMessage.value = 'Testimonial added successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add testimonial. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Update testimonial function
const updateTestimonials = () => {
  testimonials.put(route('profile.testimonials.update', currentTestimonialId.value), {
    onSuccess: () => {
      closeUpdateTestimonialsModal();
      successMessage.value = 'Testimonial updated successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update testimonial. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Archive testimonial function
const archiveTestimonialForm = useForm({
  is_archived: true
});

const archiveTestimonial = (testimonial) => {
  archiveTestimonialForm.put(route('profile.testimonials.archive', testimonial.id), {
    onSuccess: () => {
      successMessage.value = 'Testimonial archived successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
    },
    onError: () => {
      errorMessage.value = 'Failed to archive testimonial. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Unarchive testimonial function
const unarchiveTestimonialForm = useForm({
  is_archived: false
});

const unarchiveTestimonial = (testimonial) => {
  unarchiveTestimonialForm.post(route('profile.testimonials.unarchive', testimonial.id), {
    onSuccess: () => {
      successMessage.value = 'Testimonial unarchived successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
    },
    onError: () => {
      errorMessage.value = 'Failed to unarchive testimonial. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Delete testimonial function
const deleteTestimonialForm = useForm({});

const removeTestimonials = (testimonialId) => {
  if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
    deleteTestimonialForm.delete(route('profile.testimonials.remove', testimonialId), {
      onSuccess: () => {
        successMessage.value = 'Testimonial deleted successfully!';
        isSuccessModalOpen.value = true;
        fetchTestimonials();
      },
      onError: () => {
        errorMessage.value = 'Failed to delete testimonial. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};

// Fetch testimonials function
const fetchTestimonialsForm = useForm({});

const fetchTestimonials = () => {
  router.get(route('profile.index'), {}, {
    onSuccess: (response) => {
      const data = response.props;
      // Get active testimonials
      if (data.testimonialEntries) {
        testimonialEntries.value = data.testimonialEntries;
      }
      
      // Get archived testimonials
      if (data.archivedTestimonialEntries) {
        archivedTestimonialEntries.value = data.archivedTestimonialEntries;
      }
    },
    onError: () => {
      errorMessage.value = 'Failed to fetch testimonials. Please refresh the page.';
      isErrorModalOpen.value = true;
    },
    preserveState: true
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

// Close duplicate modal
const closeDuplicateModal = () => {
  isDuplicateModalOpen.value = false;
};

// Fetch testimonials on component mount
onMounted(() => {
  fetchTestimonials();
});
</script>

<template>
        <div v-if="activeSection === 'testimonials'" class="flex flex-col lg:flex-row">
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
                    <h3 class="text-lg font-medium text-center mb-4">This testimonial already exists.</h3>
                    <div class="flex justify-center">
                        <button @click="closeDuplicateModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            OK
                        </button>
                    </div>
                </div>
            </Modal>
            <div class="w-full mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-semibold mb-4">Testimonials</h1>
                    <div class="flex space-x-4">
                        <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700 transition-colors"
                            @click="isAddTestimonialsModalOpen = true">
                            <i class="fas fa-plus mr-2"></i>
                            Add Testimonial
                        </PrimaryButton>
                        <button 
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
                            @click="toggleArchivedTestimonial">
                            <i class="fas" :class="showArchivedTestimonial ? 'fa-eye-slash' : 'fa-eye'"></i>
                            <span class="ml-2">{{ showArchivedTestimonial ? 'Hide Archived' : 'Show Archived' }}</span>
                        </button>
                    </div>
                </div>
                <p class="text-gray-600 mt-2 mb-6">Recommendations from colleagues and clients</p>
              
                <!-- Testimonials Entries -->
                <div>
                    <h2 class="text-lg font-medium mb-4">Active Testimonials</h2>
                    <div v-if="testimonialEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(entry, index) in testimonialEntries" :key="entry.id"
                            class="bg-white rounded-lg shadow-md p-4 space-y-3 relative">
                            <div class="flex-grow">
                              <h2 class="text-xl font-bold text-gray-900">{{ entry.graduate_testimonials_name }}</h2>
                              <p class="text-gray-600 font-medium">{{ entry.graduate_testimonials_role_title }}</p>
                              <p class="mt-3 text-gray-700 italic">"{{ entry.graduate_testimonials_testimonial }}"</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm italic">"{{ entry.graduate_testimonial_content }}"</p>
                            </div>
                            <div v-if="testimonials.graduate_testimonials_letters" class="md:w-1/3">
                              <img :src="`/storage/${testimonials.graduate_testimonials_letters}`" 
                                  :alt="testimonials.graduate_testimonials_name"
                                  class="w-full h-auto rounded-lg shadow-sm"
                              />
                            </div>
                            <div class="flex justify-end space-x-2 mt-3">
                                <button class="text-gray-600 hover:text-indigo-600" 
                                    @click="editTestimonial(entry)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="text-amber-600 hover:text-amber-800" 
                                    @click="archiveTestimonial(entry)">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- If no testimonials entries exist -->
                    <div v-else class="bg-white p-8 rounded-lg shadow">
                        <p class="text-gray-600">No testimonial entries added yet.</p>
                    </div>
                </div>

                <!-- Archived Testimonials Entries -->
                <div v-if="showArchivedTestimonial" class="mt-8">
                    <h2 class="text-lg font-medium mb-4">Archived Testimonials</h2>
                    <div v-if="archivedTestimonialEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(entry, index) in archivedTestimonialEntries" :key="entry.id"
                            class="bg-gray-50 rounded-lg shadow-md p-4 space-y-3 border border-gray-200 relative">
                            <div class="opacity-75">
                                <div class="border-b pb-2">
                                    <h3 class="text-lg font-semibold">{{ entry.graduate_testimonials_name }}</h3>
                                    <span class="text-sm text-gray-600">{{ entry.graduate_testimonials_role_title }}</span>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm italic">"{{ entry.graduate_testimonial_content }}"</p>
                                </div>
                                <div v-if="entry.graduate_testimonial_file" class="mt-3">
                                    <img :src="`/storage/${entry.graduate_testimonial_file}`" 
                                        :alt="entry.graduate_testimonial_author"
                                        class="max-w-full h-auto rounded-lg shadow"/>
                                </div>
                            </div>
                            <div class="absolute top-2 right-2 flex space-x-2">
                                <button class="text-green-600 hover:text-green-800" @click="unarchiveTestimonial(entry)">
                                    <i class="fas fa-box-open"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800" @click="deleteTestimonial(entry.id)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                                Archived
                            </div>
                        </div>
                    </div>

                    <!-- If no archived testimonials exist -->
                    <div v-else class="bg-white p-8 rounded-lg shadow">
                        <p class="text-gray-600">No archived testimonials found.</p>
                    </div>
                </div>
            </div>

            <!-- Add Testimonials Modal -->
            <div v-if="isAddTestimonialsModalOpen"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Add Testimonials</h2>
                        <button class="text-gray-500 hover:text-gray-700" @click="closeAddTestimonialsModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Add a recommendation from a colleague or client</p>
                      <form @submit.prevent="addTestimonials">
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" v-model="testimonials.graduate_testimonials_name"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. John Doe" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Role/Title <span class="text-red-500">*</span></label>
                        <input type="text" v-model="testimonials.graduate_testimonials_role_title"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. Manager" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Testimonial <span class="text-red-500">*</span></label>
                        <textarea v-model="testimonials.graduate_testimonials_testimonial"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            rows="3" placeholder="Write the testimonial here..." required></textarea>
                      </div>
                      <div class="mb-4">
                        <label for="testimonial-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                        <input type="file" id="testimonial-file" @change="handleFileUpload"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      </div>
                      <div class="flex justify-end">
                        <button type="submit"
                          class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                          <i class="fas fa-save mr-2"></i>
                          Add Testimonial
                        </button>
                      </div>
                    </form>
                  </div>
              </div>

              <!-- Update Testimonials Modal -->
              <div v-if="isUpdateTestimonialsModalOpen"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                      <div class="flex justify-between items-center mb-4">
                          <h2 class="text-xl font-semibold">Update Testimonials</h2>
                          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateTestimonialsModal">
                              <i class="fas fa-times"></i>
                          </button>
                      </div>
                      <p class="text-gray-600 mb-4">Update a recommendation from a colleague or client</p>
                      <form @submit.prevent="updateTestimonials">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Name <span class="text-red-500">*</span></label>
                      <input type="text" v-model="testimonials.graduate_testimonials_name"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. John Doe" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Role/Title <span class="text-red-500">*</span></label>
                      <input type="text" v-model="testimonials.graduate_testimonials_role_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Manager" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Testimonial <span class="text-red-500">*</span></label>
                      <textarea v-model="testimonials.graduate_testimonials_testimonial"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Write the testimonial here..." required></textarea>
                    </div>
                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Testimonial
                      </button>
                    </div>
                  </form>
                </div>
            </div>

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
                    <h3 class="text-lg font-medium text-center mb-4">Duplicate Entry</h3>
                    <p class="text-center mb-4">A testimonial with the same information already exists.</p>
                    <div class="flex justify-center">
                        <button @click="closeDuplicateModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            OK
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
</template>