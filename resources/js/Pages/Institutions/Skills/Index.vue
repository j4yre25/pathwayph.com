<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();
const userId = page.props.auth.user.id;

const careerOpportunities = page.props.careerOpportunities;
const skills = ref([...page.props.skills]);
const selectedCareerOpportunity = ref(page.props.selectedCareerOpportunity ?? '');

// Confirmation modal state
const showConfirmModal = ref(false);
const skillToArchive = ref(null);

// Edit modal state
const showEditModal = ref(false);
const skillToEdit = ref(null);
const editForm = useForm({
  skill_name: '',
  career_opportunity_id: '',
});

// Stats for display
const totalSkills = computed(() => skills.value.length);
const uniqueOpportunities = computed(() => {
  const uniqueOppIds = new Set(skills.value.map(s => s.career_opportunity_id).filter(Boolean));
  return uniqueOppIds.size;
});
const uniqueSkillNames = computed(() => {
  const skillNames = new Set(skills.value.map(s => s.skill?.name).filter(Boolean));
  return skillNames.size;
});

function confirmArchive(skill) {
  skillToArchive.value = skill;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  if (!skillToArchive.value) return;
  router.delete(route('instiskills.archive', skillToArchive.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      skills.value = skills.value.filter((skill) => skill.id !== skillToArchive.value.id);
      showConfirmModal.value = false;
      skillToArchive.value = null;
    },
  });
}

function cancelArchive() {
  showConfirmModal.value = false;
  skillToArchive.value = null;
}

// Edit skill functions
function openEditModal(skill) {
  skillToEdit.value = skill;
  editForm.skill_name = skill.skill?.name || '';
  editForm.career_opportunity_id = skill.career_opportunity_id;
  showEditModal.value = true;
}

function submitEdit() {
  if (!skillToEdit.value) return;
  
  editForm.put(route('instiskills.update', skillToEdit.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Update the skill in the local array
      const index = skills.value.findIndex(s => s.id === skillToEdit.value.id);
      if (index !== -1) {
        // Update the skill name and career opportunity
        skills.value[index].skill.name = editForm.skill_name;
        skills.value[index].career_opportunity_id = editForm.career_opportunity_id;
        
        // Update the career opportunity title
        const careerOpp = careerOpportunities.find(c => c.id === editForm.career_opportunity_id);
        if (careerOpp) {
          skills.value[index].career_opportunity.title = careerOpp.title;
        }
      }
      
      closeEditModal();
    },
  });
}

function closeEditModal() {
  showEditModal.value = false;
  skillToEdit.value = null;
  editForm.reset();
  editForm.clearErrors();
}

// Get unique career opportunities for dropdown
const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return (careerOpportunities || []).filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

// Filter skills by selected career opportunity
const filteredSkills = computed(() => {
  if (!selectedCareerOpportunity.value) return skills.value;
  return skills.value.filter(
    (item) => String(item.career_opportunity_id) === String(selectedCareerOpportunity.value)
  );
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 30;
const totalPages = computed(() => Math.ceil(filteredSkills.value.length / itemsPerPage));
const paginatedSkills = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return filteredSkills.value.slice(startIndex, endIndex);
});

const goToPage = (page) => {
  currentPage.value = page;
};

// Reset pagination when filters change
onMounted(() => {
  currentPage.value = 1;
});

function applyFilter() {
  router.get(route('instiskills', { user: userId }), {
    preserveScroll: true,
    preserveState: true,
    career_opportunity_id: selectedCareerOpportunity.value,
  });
  currentPage.value = 1; // Reset pagination when filter is applied
}
</script>

<template>
  <AppLayout title="Manage Skills">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-tools text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Skills</h2>
      </div>
    </template>

    <Container class="py-8">

      <!-- Navigation Menu -->
      <div class="mb-8 max-w-3xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <!-- Section Header -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 text-center">Data Entries</h3>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 p-4 justify-center">

          <Link :href="route('school-years', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> School Years
          </Link>

          <Link :href="route('degrees', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-graduation-cap mr-2 text-green-500"></i> Degrees
          </Link>

          <Link :href="route('programs', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-200">
          <i class="fas fa-book mr-2 text-indigo-500"></i> Programs
          </Link>

          <Link :href="route('careeropportunities', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600 transition-all duration-200">
          <i class="fas fa-briefcase mr-2 text-purple-500"></i> Career Opportunities
          </Link>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total Skills</h3>
              <p class="text-3xl font-bold text-blue-600">
                {{ totalSkills }}
              </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-tools text-blue-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Career Opportunities</h3>
              <p class="text-3xl font-bold text-green-600">
                {{ uniqueOpportunities }}
              </p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-briefcase text-green-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Skills</h3>
              <p class="text-3xl font-bold text-purple-600">
                {{ uniqueSkillNames }}
              </p>
            </div>
            <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-cogs text-purple-500"></i>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Skills List</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredSkills.length }} of {{ totalSkills }}</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('instiskills.create', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Add Skill
            </Link>
            <Link :href="route('instiskills.list', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-list text-blue-500 mr-2"></i> All Skills
            </Link>
            <Link :href="route('instiskills.archivedlist', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
            </Link>
          </div>
        </div>

        <!-- Filters -->
        <div class="p-4 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center gap-4">
            <div class="relative">
              <label for="opportunity" class="block text-sm font-medium text-gray-700 mb-1">Filter by Career Opportunity</label>
              <div class="relative">
                <select
                  id="opportunity"
                  v-model="selectedCareerOpportunity"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                >
                  <option value="">All Career Opportunities</option>
                  <option v-for="item in careerOpportunities" :key="item.id" :value="item.id">
                    {{ item.title }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                </div>
              </div>
            </div>
            <div class="mt-6">
              <button @click="applyFilter" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200 flex items-center">
                <i class="fas fa-filter mr-2"></i> Apply Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
              <tr>
                <th class="px-6 py-4">Career Opportunity</th>
                <th class="px-6 py-4">Skill</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="item in paginatedSkills"
                :key="item.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                      <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ item.career_opportunity?.title || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 mr-2">
                      <i class="fas fa-cog mr-1"></i> Skill
                    </span>
                    <span class="text-sm font-medium">{{ item.skill?.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button @click="() => openEditModal(item)" class="text-blue-500 hover:text-blue-700 mr-3">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="() => confirmArchive(item)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredSkills.length === 0">
                <td colspan="3" class="py-12 text-center">
                  <i class="fas fa-tools text-gray-300 text-5xl mb-4"></i>
                  <h3 class="text-lg font-medium text-gray-700">No skills found</h3>
                  <p class="text-gray-500 mt-1">
                    {{ selectedCareerOpportunity ? 'Try adjusting your filters' : 'Add skills to see them listed here' }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredSkills.length) }}</span> of
              <span class="font-medium">{{ filteredSkills.length }}</span> results
            </div>
            <div class="flex space-x-2">
              <button 
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <div class="flex space-x-1">
                <template v-if="totalPages <= 7">
                  <button 
                    v-for="page in totalPages" 
                    :key="page" 
                    @click="goToPage(page)"
                    :class="{
                      'bg-blue-600 text-white': page === currentPage,
                      'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                    }"
                    class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                  >
                    {{ page }}
                  </button>
                </template>
                <template v-else>
                  <!-- First page -->
                  <button 
                    @click="goToPage(1)"
                    :class="{
                      'bg-blue-600 text-white': 1 === currentPage,
                      'bg-white text-gray-700 hover:bg-gray-50': 1 !== currentPage
                    }"
                    class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                  >
                    1
                  </button>
                  
                  <!-- Ellipsis if needed -->
                  <span v-if="currentPage > 3" class="px-3 py-1 text-gray-500">...</span>
                  
                  <!-- Pages around current page -->
                  <template v-for="page in totalPages" :key="page">
                    <button 
                      v-if="page !== 1 && page !== totalPages && Math.abs(page - currentPage) < 2"
                      @click="goToPage(page)"
                      :class="{
                        'bg-blue-600 text-white': page === currentPage,
                        'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                      }"
                      class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                    >
                      {{ page }}
                    </button>
                  </template>
                  
                  <!-- Ellipsis if needed -->
                  <span v-if="currentPage < totalPages - 2" class="px-3 py-1 text-gray-500">...</span>
                  
                  <!-- Last page -->
                  <button 
                    @click="goToPage(totalPages)"
                    :class="{
                      'bg-blue-600 text-white': totalPages === currentPage,
                      'bg-white text-gray-700 hover:bg-gray-50': totalPages !== currentPage
                    }"
                    class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                  >
                    {{ totalPages }}
                  </button>
                </template>
              </div>
              <button 
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
              <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600 mb-2">Are you sure you want to archive the skill:</p>
          <p class="font-medium text-gray-800">"{{ skillToArchive?.skill?.name }}"</p>
          <p class="text-sm text-gray-600 mt-1">for career opportunity:</p>
          <p class="font-medium text-gray-800">"{{ skillToArchive?.career_opportunity?.title }}"</p>
          <p class="text-sm text-gray-500 mt-2">This action can be reversed later.</p>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button @click="cancelArchive" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </button>
            <button @click="archiveConfirmed" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </button>
          </div>
        </template>
      </ConfirmationModal>

      <!-- Edit Skill Modal -->
      <ConfirmationModal :show="showEditModal" @close="closeEditModal">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
              <i class="fas fa-edit text-blue-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Skill</h3>
          </div>
        </template>
        <template #content>
          <div class="space-y-4">
            <!-- Skill Name -->
            <div>
              <InputLabel for="skill_name" value="Skill Name" class="mb-1" />
              <input
                id="skill_name"
                v-model="editForm.skill_name"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                autocomplete="off"
              />
              <InputError :message="editForm.errors.skill_name" class="mt-1" />
            </div>

            <!-- Career Opportunity Dropdown -->
            <div>
              <InputLabel for="career_opportunity_id" value="Career Opportunity" class="mb-1" />
              <select
                id="career_opportunity_id"
                v-model="editForm.career_opportunity_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
              >
                <option disabled value="">Select one</option>
                <option
                  v-for="op in uniqueCareerOpportunities"
                  :key="op.id"
                  :value="op.id"
                >
                  {{ op.title }}
                </option>
              </select>
              <InputError :message="editForm.errors.career_opportunity_id" class="mt-1" />
            </div>
          </div>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button @click="closeEditModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </button>
            <button 
              @click="submitEdit" 
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
              :disabled="editForm.processing"
            >
              <span v-if="editForm.processing">
                <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
              </span>
              <span v-else>Update Skill</span>
            </button>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
