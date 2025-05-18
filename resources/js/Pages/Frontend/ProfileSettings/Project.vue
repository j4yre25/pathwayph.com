<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
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
    default: 'projects'
  }
});

// Projects form
const projects = useForm({
  id: null,
  graduate_projects_title: '',
  graduate_projects_description: '',
  graduate_projects_role: '',
  graduate_projects_start_date: null,
  graduate_projects_end_date: null,
  graduate_projects_url: '',
  graduate_projects_key_accomplishments: '',
  graduate_project_file: null,
  is_current: false
});

// File upload handling
const noProjectUrl = ref(false);
const handleFileUpload = (event) => {
  projects.graduate_project_file = event.target.files[0];
};

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return isValid(date) ? date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
};

// Project state variables
const projectsEntries = ref([props.projectsEntries || []]);
const archivedProjectsEntries = ref([]);
const showArchivedProjects = ref(false);
const isAddProjectModalOpen = ref(false);
const isUpdateProjectModalOpen = ref(false);
const currentProjectId = ref(null);

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Toggle archived projects
const toggleArchivedProjects = () => {
  showArchivedProjects.value = !showArchivedProjects.value;
};

// Close add project modal
const closeAddProjectModal = () => {
  isAddProjectModalOpen.value = false;
  projects.reset();
  projects.clearErrors();
};

// Close update project modal
const closeUpdateProjectModal = () => {
  isUpdateProjectModalOpen.value = false;
  projects.reset();
  projects.clearErrors();
};

// Open update project modal
const openUpdateProjectModal = (project) => {
  currentProjectId.value = project.id;
  projects.id = project.id;
  projects.graduate_projects_title = project.graduate_projects_title;
  projects.graduate_projects_description = project.graduate_projects_description || '';
  projects.graduate_projects_role = project.graduate_projects_role;
  projects.graduate_projects_start_date = project.graduate_projects_start_date ? new Date(project.graduate_projects_start_date) : null;
  projects.graduate_projects_end_date = project.graduate_projects_end_date ? new Date(project.graduate_projects_end_date) : null;
  projects.graduate_projects_url = project.graduate_projects_url || '';
  projects.graduate_projects_key_accomplishments = project.graduate_projects_key_accomplishments || '';
  projects.is_current = project.graduate_projects_end_date === null;
  isUpdateProjectModalOpen.value = true;
};

// Add project function
const addProject = () => {
  projects.post(route('profile.projects.add'), {
    onSuccess: () => {
      closeAddProjectModal();
      successMessage.value = 'Project added successfully!';
      isSuccessModalOpen.value = true;
      fetchProjects();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add project. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Update project function
const updateProject = () => {
  projects.post(route('profile.projects.update', currentProjectId.value), {
    onSuccess: () => {
      closeUpdateProjectModal();
      successMessage.value = 'Project updated successfully!';
      isSuccessModalOpen.value = true;
      fetchProjects();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update project. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Archive project function
const archiveProjectForm = useForm({
  is_archived: true
});

const archiveProject = (project) => {
  archiveProjectForm.post(route('profile.projects.archive', project.id), {
    onSuccess: () => {
      successMessage.value = 'Project archived successfully!';
      isSuccessModalOpen.value = true;
      fetchProjects();
    },
    onError: () => {
      errorMessage.value = 'Failed to archive project. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Unarchive project function
const unarchiveProjectForm = useForm({
  is_archived: false
});

const unarchiveProject = (project) => {
  unarchiveProjectForm.post(route('profile.projects.unarchive', project.id), {
    onSuccess: () => {
      successMessage.value = 'Project unarchived successfully!';
      isSuccessModalOpen.value = true;
      fetchProjects();
    },
    onError: () => {
      errorMessage.value = 'Failed to unarchive project. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Delete project function
const deleteProjectForm = useForm({});

const removeProject = (projectId) => {
  if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
    deleteProjectForm.delete(route('profile.projects.remove', projectId), {
      onSuccess: () => {
        successMessage.value = 'Project deleted successfully!';
        isSuccessModalOpen.value = true;
        fetchProjects();
      },
      onError: () => {
        errorMessage.value = 'Failed to delete project. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};

// Fetch projects function
const fetchProjectsForm = useForm({});

const fetchProjects = () => {
  router.get(route('profile.index'), {}, {
    onSuccess: (response) => {
      const data = response.props;
      // Get active projects
      if (data.projectsEntries) {
        projectsEntries.value = data.projectsEntries;
      }
      
      // Get archived projects
      if (data.archivedProjectsEntries) {
        archivedProjectsEntries.value = data.archivedProjectsEntries;
      }
    },
    onError: () => {
      errorMessage.value = 'Failed to fetch projects. Please refresh the page.';
      isErrorModalOpen.value = true;
    },
    preserveState: true
  });
};

// Watch for changes in is_current checkbox
watch(() => projects.is_current, (newValue) => {
  if (newValue) {
    projects.graduate_projects_end_date = null;
  }
});

// Watch for changes in noProjectUrl checkbox
watch(() => noProjectUrl.value, (newValue) => {
  if (newValue) {
    projects.graduate_projects_url = '';
  }
});

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

// Fetch projects on component mount
onMounted(() => {
  fetchProjects();
});


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

    <div v-if="activeSection === 'projects'" class="flex flex-col lg:flex-row">
        <div class="w-full mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold">Projects</h1>
                <div class="flex space-x-2">
                    <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700"
                        @click="isAddProjectModalOpen = true">
                        <i class="fas fa-plus mr-2"></i> Add Project
                    </PrimaryButton>
                    <button 
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
                        @click="toggleArchivedProjects">
                        <i class="fas" :class="showArchivedProjects ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span class="ml-2">{{ showArchivedProjects ? 'Hide Archived' : 'Show Archived' }}</span>
                    </button>
                </div>
            </div>
            <p class="text-gray-600 mb-6">Showcase your personal and professional projects</p>

            <!-- Project Entries -->
            <div>
                <h2 class="text-lg font-medium mb-4">Active Projects</h2>
                <div v-if="projectsEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="(entry, index) in projectsEntries" :key="entry.id" 
                        class="bg-white p-8 rounded-lg shadow relative">
                        <div>
                            <div class="border-b pb-2">
                                <h2 class="text-xl font-bold text-gray-800">{{ entry.graduate_projects_title }}</h2>
                                <p class="text-sm text-gray-600">{{ entry.graduate_projects_role }}</p>
                            </div>
                            <div class="flex items-center text-gray-600 mt-2">
                                <i class="far fa-calendar-alt mr-2 text-gray-500"></i>
                                <span>
                                {{ formatDate(entry.graduate_projects_start_date) }} - 
                                {{ entry.graduate_projects_end_date === null ? 'Present' : formatDate(entry.graduate_projects_end_date) }}
                                </span>
                            </div>
                            <p class="mt-2">
                                <strong>
                                <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                                </strong>
                                {{ entry.graduate_projects_description || 'No description provided' }}
                            </p>
                            <div class="mt-2">
                                <strong>
                                    <i class="fas fa-link text-gray-500 mr-2"></i> Project URL:
                                </strong>
                                <span v-if="entry.graduate_projects_url">
                                    <a :href="entry.graduate_projects_url" target="_blank" class="text-indigo-600 hover:underline break-all">
                                        {{ entry.graduate_projects_url }}
                                    </a>
                                </span>
                                <span v-else class="text-gray-500">No project URL provided</span>
                            </div>
                            <p class="mt-2">
                                <strong>
                                    <i class="fas fa-trophy text-gray-500 mr-2"></i> Key Accomplishments:
                                </strong>
                                <span v-if="entry.graduate_projects_key_accomplishments">
                                    {{ entry.graduate_projects_key_accomplishments }}
                                </span>
                                <span v-else>No key accomplishment provided</span>
                            </p>
                            <div v-if="entry.graduate_project_file" class="mt-3">
                                <img :src="`/storage/${entry.graduate_project_file}`" 
                                    :alt="entry.graduate_projects_title"
                                    class="max-w-full h-auto rounded-lg shadow"/>
                            </div>
                        </div>
                        <div class="absolute top-8 right-4 flex space-x-4">
                            <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateProjectModal(entry)">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="text-amber-600 hover:text-amber-800" @click="archiveProject(entry)">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </div>
                </div>
            
                <!-- If no projects exist -->
                <div v-else class="bg-white p-8 rounded-lg shadow">
                    <p class="text-gray-600">No project entries added yet.</p>
                </div>
            </div>
            
            <!-- Archived Projects -->
            <div v-if="showArchivedProjects" class="mt-8">
                <h2 class="text-lg font-medium mb-4">Archived Projects</h2>
                <div v-if="archivedProjectsEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="(entry, index) in archivedProjectsEntries" :key="entry.id" 
                        class="bg-gray-50 p-8 rounded-lg shadow relative border border-gray-200">
                        <div class="opacity-75">
                            <div class="border-b pb-2">
                                <h2 class="text-xl font-bold text-gray-800">{{ entry.graduate_projects_title }}</h2>
                                <p class="text-sm text-gray-600">{{ entry.graduate_projects_role }}</p>
                            </div>
                            <div class="flex items-center text-gray-600 mt-2">
                                <i class="far fa-calendar-alt mr-2 text-gray-500"></i>
                                <span>
                                {{ formatDate(entry.graduate_projects_start_date) }} - 
                                {{ entry.graduate_projects_end_date === null ? 'Present' : formatDate(entry.graduate_projects_end_date) }}
                                </span>
                            </div>
                            <p class="mt-2">
                                <strong>
                                    <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                                </strong>
                                    {{ entry.graduate_projects_description || 'No description provided' }}
                            </p>
                            <div class="mt-2">
                                <strong>
                                    <i class="fas fa-link text-gray-500 mr-2"></i> Project URL:
                                </strong>
                                <span v-if="entry.graduate_projects_url">
                                    <a :href="entry.graduate_projects_url" target="_blank" class="text-indigo-600 hover:underline break-all">
                                        {{ entry.graduate_projects_url }}
                                    </a>
                                </span>
                                <span v-else class="text-gray-500">No project URL provided</span>
                            </div>
                            <p class="mt-2">
                                <strong>
                                    <i class="fas fa-trophy text-gray-500 mr-2"></i> Key Accomplishments:
                                </strong>
                                <span v-if="entry.graduate_projects_key_accomplishments">
                                    {{ entry.graduate_projects_key_accomplishments }}
                                </span>
                                <span v-else>No key accomplishment provided</span>
                            </p>
                            <div v-if="entry.graduate_project_file" class="mt-3">
                                <img :src="`/storage/${entry.graduate_project_file}`" 
                                    :alt="entry.graduate_projects_title"
                                    class="max-w-full h-auto rounded-lg shadow"/>
                            </div>
                        </div>
                        <div class="absolute top-8 right-4 flex space-x-4">
                            <button class="text-green-600 hover:text-green-800" @click="unarchiveProject(entry)">
                                <i class="fas fa-box-open"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800" @click="removeProject(entry.id)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                            Archived
                        </div>
                    </div>
                </div>
                <!-- If no archived project entries exist -->
                <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No archived project entries found.</p>
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
                            <label class="block text-gray-700 font-medium mb-2">Project Title <span class="text-red-500">*</span></label>
                            <input type="text" v-model="projects.graduate_projects_title"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="e.g. E-commerce Platform" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Description</label>
                            <textarea v-model="projects.graduate_projects_description"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            rows="3" placeholder="Describe your project..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
                            <input type="text" v-model="projects.graduate_projects_role"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="Your role in the project" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                            <Datepicker v-model="projects.graduate_projects_start_date"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="Select start date" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">End Date</label>
                            <Datepicker v-model="projects.graduate_projects_end_date"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                placeholder="Select end date" :disabled="projects.is_current" />
                            <div class="mt-2">
                                <input type="checkbox" v-model="projects.is_current" id="isCurrentProject" />
                                <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing project</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Project URL</label>
                            <input type="url" v-model="projects.graduate_projects_url"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
                        </div>
                        <div class="mb-4">
                            <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
                            <label for="noProjectUrl" class="text-sm text-gray-700 ml-2">No Project URL</label>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
                            <textarea v-model="projects.graduate_projects_key_accomplishments"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                rows="3" placeholder="What did you achieve?"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="project-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                            <input type="file" id="project-file" @change="handleFileUpload"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Add Project</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Project Modal -->
        <div v-if="isUpdateProjectModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
                            <label class="block text-gray-700 font-medium mb-2">Project Title <span class="text-red-500">*</span></label>
                            <input type="text" v-model="projects.graduate_projects_title"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="e.g. E-commerce Platform" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Description</label>
                            <textarea v-model="projects.graduate_projects_description"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            rows="3" placeholder="Describe your personal or professional project..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
                            <input type="text" v-model="projects.graduate_projects_role"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="Your role in the project" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                            <Datepicker v-model="projects.graduate_projects_start_date"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="Start Date" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">End Date</label>
                            <Datepicker v-model="projects.graduate_projects_end_date"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="End Date" :disabled="projects.is_current" />
                            <div class="mt-2">
                                <input type="checkbox" v-model="projects.is_current" id="isCurrentProject" />
                                <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing
                                    project</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Project URL</label>
                            <input type="url" v-model="projects.graduate_projects_url"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
                        </div>
                        <div class="mb-4">
                            <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
                            <label for="noProjectUrl" class="text-gray-700 font-medium ml-2">No Project URL</label>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
                            <textarea v-model="projects.graduate_projects_key_accomplishments"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            rows="3" placeholder="What did you achieve?"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Update
                            Project
                        </button>
                    </form>
                </div>
            </div>
        </div>   
    </div>   
</template>