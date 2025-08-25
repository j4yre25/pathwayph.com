<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Datepicker from 'vue3-datepicker';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { parseISO, isValid } from 'date-fns';

// Lazy-load components
const ProjectEntryCard = defineAsyncComponent(() => import('./components/ProjectEntryCard.vue').catch(() => {
  // Fallback to inline component if module doesn't exist
  return {
    render() {
      return h('div', { class: 'p-4 bg-white rounded-lg shadow' }, this.$slots.default);
    }
  };
}));

// Props
const props = defineProps({
  activeSection: { type: String, default: 'projects' },
  projectsEntries: Array,
  archivedProjectsEntries: Array,
});
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
// Map backend fields to frontend expected fields
function mapProjectFields(entry) {
  return {
    ...entry,
    graduate_projects_title: entry.title,
    graduate_projects_description: entry.description,
    graduate_projects_role: entry.role,
    graduate_projects_start_date: entry.start_date,
    graduate_projects_end_date: entry.end_date,
    graduate_projects_url: entry.url,
    graduate_projects_key_accomplishments: entry.key_accomplishments,
    graduate_project_file: entry.file,
    is_current: entry.end_date === null,
  };
}

const filteredProjectsEntries = computed(() => {
  // You can add filtering logic here if needed, for now just return all
  return projectsEntries.value;
});

const filteredArchivedProjectsEntries = computed(() => {
  // You can add filtering logic here if needed, for now just return all
  return archivedProjectsEntries.value;
});

const projectsEntries = ref((props.projectsEntries || []).map(mapProjectFields));
const archivedProjectsEntries = ref((props.archivedProjectsEntries || []).map(mapProjectFields));

// State
const isAddProjectModalOpen = ref(false);
const isUpdateProjectModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const noProjectUrl = ref(false);
const showArchivedProjects = ref(false);
const isProjectsExpanded = ref(true);
const isArchivedProjectsExpanded = ref(true);

const toggleArchivedProjects = () => {
  showArchivedProjects.value = !showArchivedProjects.value;
};

const toggleProjectsExpanded = () => {
  isProjectsExpanded.value = !isProjectsExpanded.value;
};

const toggleArchivedProjectsExpanded = () => {
  isArchivedProjectsExpanded.value = !isArchivedProjectsExpanded.value;
};

// Form
const form = useForm({
  graduate_projects_title: '',
  graduate_projects_description: '',
  graduate_projects_role: '',
  graduate_projects_start_date: null,
  graduate_projects_end_date: null,
  graduate_projects_url: '',
  graduate_projects_key_accomplishments: '',
  graduate_project_file: null,
  is_current: false,
  id: null,
});

// Helpers
const formatDate = (date) => {
  if (!date) return '';
  if (typeof date === 'string') return date;
  const d = new Date(date);
  if (isNaN(d.getTime())) return '';
  return d.toISOString().split('T')[0];
};

// Reset form
const resetForm = () => {
  form.reset();
  form.graduate_projects_start_date = null;
  form.graduate_projects_end_date = null;
  noProjectUrl.value = false;
};

// Modal handlers
const openAddProjectModal = () => {
  resetForm();
  isAddProjectModalOpen.value = true;
};
const closeAddProjectModal = () => {
  isAddProjectModalOpen.value = false;
  resetForm();
};
const openUpdateProjectModal = (entry) => {
  resetForm();
  form.id = entry.id;
  form.graduate_projects_title = entry.graduate_projects_title;
  form.graduate_projects_description = entry.graduate_projects_description || '';
  form.graduate_projects_role = entry.graduate_projects_role;
  form.graduate_projects_start_date = entry.graduate_projects_start_date ? parseISO(entry.graduate_projects_start_date) : null;
  form.graduate_projects_end_date = entry.is_current ? null : (entry.graduate_projects_end_date ? parseISO(entry.graduate_projects_end_date) : null);
  form.graduate_projects_url = entry.graduate_projects_url || '';
  form.graduate_projects_key_accomplishments = entry.graduate_projects_key_accomplishments || '';
  form.graduate_project_file = null;
  form.is_current = entry.is_current;
  noProjectUrl.value = !entry.graduate_projects_url;
  isUpdateProjectModalOpen.value = true;
};
const closeUpdateProjectModal = () => {
  isUpdateProjectModalOpen.value = false;
  resetForm();
};
const closeSuccessModal = () => { isSuccessModalOpen.value = false; };
const closeErrorModal = () => { isErrorModalOpen.value = false; };
const closeDuplicateModal = () => { isDuplicateModalOpen.value = false; };

// File upload
const handleFileUpload = (event) => {
  form.graduate_project_file = event.target.files[0];
};

// Validation
const validateProject = () => {
  const errors = {};
  if (!form.graduate_projects_title) errors.title = 'Project title is required';
  if (!form.graduate_projects_role) errors.role = 'Project role is required';
  if (!form.graduate_projects_start_date) errors.startDate = 'Start date is required';
  if (!form.is_current && !form.graduate_projects_end_date) {
    errors.endDate = 'End date is required when not current project';
  }
  if (
    form.graduate_projects_start_date &&
    form.graduate_projects_end_date &&
    !form.is_current
  ) {
    const start = new Date(form.graduate_projects_start_date);
    const end = new Date(form.graduate_projects_end_date);
    if (end < start) errors.endDate = 'End date cannot be earlier than start date';
  }
  return Object.keys(errors).length === 0 ? null : errors;
};

// Add Project
const addProject = () => {
  const errors = validateProject();
  if (errors) {
    errorMessage.value = Object.values(errors).join('\n');
    isErrorModalOpen.value = true;
    return;
  }
  form.graduate_projects_start_date = formatDate(form.graduate_projects_start_date);
  form.graduate_projects_end_date = form.is_current ? null : formatDate(form.graduate_projects_end_date);
  form.graduate_projects_url = noProjectUrl.value ? '' : form.graduate_projects_url;
  form.graduate_projects_description = form.graduate_projects_description?.trim() || 'No description provided';
  form.graduate_projects_key_accomplishments = form.graduate_projects_key_accomplishments?.trim() || '';
  form.post(route('profile.projects.add'), {
    forceFormData: true,
    onSuccess: (response) => {
      emit('close-all-modals');      if (response?.props?.projectsEntries) {
        projectsEntries.value = response.props.projectsEntries;
      }
      resetForm();
      isAddProjectModalOpen.value = false;
      successMessage.value = 'Project added successfully!';
      isSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add project. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    },
  });
};

// Update Project
const updateProject = () => {
  const errors = validateProject();
  if (errors) {
    errorMessage.value = Object.values(errors).join('\n');
    isErrorModalOpen.value = true;
    return;
  }
  form.graduate_projects_start_date = formatDate(form.graduate_projects_start_date);
  form.graduate_projects_end_date = form.is_current ? null : formatDate(form.graduate_projects_end_date);
  form.graduate_projects_url = noProjectUrl.value ? '' : form.graduate_projects_url;
  form.graduate_projects_description = form.graduate_projects_description?.trim() || 'No description provided';
  form.graduate_projects_key_accomplishments = form.graduate_projects_key_accomplishments?.trim() || '';
  form.put(route('profile.projects.update', form.id), {
    forceFormData: true,
    onSuccess: (response) => {
      emit('close-all-modals');      if (response?.props?.projectsEntries) {
        projectsEntries.value = response.props.projectsEntries;
      }
      resetForm();
      isUpdateProjectModalOpen.value = false;
      successMessage.value = 'Project updated successfully!';
      isSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update project. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    },
  });
};

// Archive Project
const archiveProject = (entry) => {
  useForm().post(route('profile.projects.archive', entry.id), {
    onSuccess: (response) => {
      if (response?.props?.projectsEntries) {
        projectsEntries.value = response.props.projectsEntries;
      }
      if (response?.props?.archivedProjectsEntries) {
        archivedProjectsEntries.value = response.props.archivedProjectsEntries;
      }
      successMessage.value = 'Project archived successfully!';
      isSuccessModalOpen.value = true;
    },
    onError: () => {
      errorMessage.value = 'Failed to archive project. Please try again.';
      isErrorModalOpen.value = true;
    },
  });
};

// Unarchive Project
const unarchiveProject = (entry) => {
  useForm().post(route('profile.projects.unarchive', entry.id), {
    onSuccess: (response) => {
      if (response?.props?.projectsEntries) {
        projectsEntries.value = response.props.projectsEntries;
      }
      if (response?.props?.archivedProjectsEntries) {
        archivedProjectsEntries.value = response.props.archivedProjectsEntries;
      }
      successMessage.value = 'Project unarchived successfully!';
      isSuccessModalOpen.value = true;
    },
    onError: () => {
      errorMessage.value = 'Failed to unarchive project. Please try again.';
      isErrorModalOpen.value = true;
    },
  });
};

// Remove Project
const removeProject = (id) => {
  if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
    useForm().delete(route('profile.projects.delete', id), {
      onSuccess: (response) => {
        if (response?.props?.projectsEntries) {
          projectsEntries.value = response.props.projectsEntries;
        }
        if (response?.props?.archivedProjectsEntries) {
          archivedProjectsEntries.value = response.props.archivedProjectsEntries;
        }
        successMessage.value = 'Project deleted successfully!';
        isSuccessModalOpen.value = true;
      },
      onError: () => {
        errorMessage.value = 'Failed to delete project. Please try again.';
        isErrorModalOpen.value = true;
      },
    });
  }
};

// Watchers
watch(() => form.is_current, (val) => {
  if (val) form.graduate_projects_end_date = null;
});
watch(() => noProjectUrl.value, (val) => {
  if (val) form.graduate_projects_url = '';
});

watch(
  () => form.graduate_projects_start_date,
  (val, oldVal) => {
    if (typeof val === 'string' && val) {
      const d = parseISO(val);
      form.graduate_projects_start_date = isValid(d) ? d : null;
    }
  }
);

watch(
  () => form.graduate_projects_end_date,
  (val, oldVal) => {
    if (typeof val === 'string' && val) {
      const d = parseISO(val);
      form.graduate_projects_end_date = isValid(d) ? d : null;
    }
  }
);
</script>

<template>
  <!-- Success Modal -->
  <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-check text-2xl text-green-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Success</h2>
      <p class="text-center mb-6">{{ successMessage }}</p>
      <div class="flex justify-center">
        <button @click="closeSuccessModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <!-- Error Modal -->
  <Modal :show="isErrorModalOpen" @close="closeErrorModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-times text-2xl text-red-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Error</h2>
      <p class="text-center mb-6">{{ errorMessage }}</p>
      <div class="flex justify-center">
        <button @click="closeErrorModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <!-- Duplicate Modal -->
  <Modal :show="isDuplicateModalOpen" @close="closeDuplicateModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-amber-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-exclamation-triangle text-2xl text-amber-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Duplicate Entry</h2>
      <p class="text-center mb-6">A project with similar details already exists.</p>
      <div class="flex justify-center">
        <button @click="closeDuplicateModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <div v-if="activeSection === 'projects'" class="w-full">
    <!-- Main Content Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
      <!-- Left Column -->
      <div class="space-y-6">
        
        <!-- Active Projects Card -->
        <div id="active-projects" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
          <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-600 to-blue-100 border-b border-blue-200">
            <div class="flex items-center">
              <div class="bg-white p-2 rounded-full mr-3 shadow-sm">
                <i class="fas fa-project-diagram text-blue-600"></i>
              </div>
              <h3 class="text-lg font-semibold text-white">Projects</h3>
            </div>
            <div class="flex space-x-2">
              <PrimaryButton class="bg-white text-blue-600 px-4 py-2 rounded flex items-center hover:bg-blue-50 border border-white"
                @click="openAddProjectModal">
                <i class="fas fa-plus mr-2"></i> Add Project
              </PrimaryButton>
              <button
                class="bg-white hover:bg-blue-50 text-blue-600 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer border border-white"
                @click="toggleArchivedProjects">
                <i class="fas" :class="showArchivedProjects ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span class="ml-2">{{ showArchivedProjects ? 'Hide Archived' : 'Show Archived' }}</span>
              </button>
              <button @click="toggleProjectsExpanded" class="text-white hover:text-blue-100 focus:outline-none">
                <i class="fas" :class="isProjectsExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
              </button>
            </div>
          </div>
          <div v-show="isProjectsExpanded" class="p-4 transition-all duration-300">
            <p class="text-gray-600 mb-6">Showcase your personal and professional projects</p>

            <!-- Project Entries -->
            <div class="mb-6">
              <h2 class="text-lg font-medium mb-4">Active Projects</h2>
              <div v-if="filteredProjectsEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <ProjectEntryCard 
                  v-for="entry in filteredProjectsEntries" 
                  :key="entry?.id"
                  :entry="entry"
                  :is-archived="false"
                  @update="openUpdateProjectModal"
                  @archive="archiveProject"
                />
              </div>

              <!-- If no projects exist -->
              <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No project entries added yet.</p>
              </div>
            </div>

            <!-- Archived Projects -->
            <div v-if="showArchivedProjects" class="mt-8">
              <div id="archived-projects" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 mb-6">
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-gray-500 to-gray-100 border-b border-gray-300">
                  <div class="flex items-center">
                    <div class="bg-white p-2 rounded-full mr-3 shadow-sm">
                      <i class="fas fa-archive text-gray-600"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white">Archived Projects</h2>
                  </div>
                  <button @click="toggleArchivedProjectsExpanded" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas" :class="isArchivedProjectsExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                  </button>
                </div>
                <div v-show="isArchivedProjectsExpanded" class="p-4 transition-all duration-300">
                  <div v-if="filteredArchivedProjectsEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <ProjectEntryCard 
                      v-for="entry in filteredArchivedProjectsEntries" 
                      :key="entry?.id"
                      :entry="entry"
                      :is-archived="true"
                      @unarchive="unarchiveProject"
                      @remove="removeProject"
                    />
                  </div>
                  <!-- If no archived project entries exist -->
                  <div v-else class="bg-white p-8 rounded-lg shadow">
                    <p class="text-gray-600">No archived project entries found.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

  <!-- Add Project Modal -->
    <div v-if="isAddProjectModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Project</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddProjectModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="addProject">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Project Title <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="form.graduate_projects_title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. E-commerce Platform" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="form.graduate_projects_description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="Describe your project..."></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.graduate_projects_role"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Your role in the project" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="form.graduate_projects_start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select start date" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="form.graduate_projects_end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select end date" :disabled="form.is_current" />
              <div class="mt-2">
                <input type="checkbox" v-model="form.is_current" id="isCurrentProject" />
                <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing project</label>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Project URL</label>
              <input type="url" v-model="form.graduate_projects_url"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
            </div>
            <div class="mb-4">
              <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
              <label for="noProjectUrl" class="text-sm text-gray-700 ml-2">No Project URL</label>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
              <textarea v-model="form.graduate_projects_key_accomplishments"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="What did you achieve?"></textarea>
            </div>
            <div class="mb-4">
              <label for="project-file" class="block text-sm font-medium text-gray-700">Upload File</label>
              <input type="file" id="project-file" @change="handleFileUpload"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Add
              Project</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Update Project Modal -->
    <div v-if="isUpdateProjectModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Update Project</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateProjectModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="updateProject">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Project Title <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="form.graduate_projects_title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. E-commerce Platform" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="form.graduate_projects_description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="Describe your personal or professional project..."></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.graduate_projects_role"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Your role in the project" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="form.graduate_projects_start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Start Date" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="form.graduate_projects_end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="End Date" :disabled="form.is_current" />
              <div class="mt-2">
                <input type="checkbox" v-model="form.is_current" id="isCurrentProject" />
                <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing
                  project</label>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Project URL</label>
              <input type="url" v-model="form.graduate_projects_url"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
            </div>
            <div class="mb-4">
              <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
              <label for="noProjectUrl" class="text-gray-700 font-medium ml-2">No Project URL</label>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
              <textarea v-model="form.graduate_projects_key_accomplishments"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="What did you achieve?"></textarea>
            </div>
            <div class="mb-4">
              <label for="project-file" class="block text-sm font-medium text-gray-700">Upload File</label>
              <input type="file" id="project-file" @change="handleFileUpload"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Update
              Project
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</template>