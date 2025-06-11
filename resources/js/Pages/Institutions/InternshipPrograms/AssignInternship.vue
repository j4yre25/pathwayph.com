<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { EyeIcon, PlusIcon, XIcon } from 'lucide-vue-next';

const props = defineProps({
    internshipPrograms: Array,
    graduates: Array,
    programs: Array,
    institutionCareerOpportunities: Array,
});

// Dropdown logic
const showTopGraduateDropdown = ref(false);
const topGraduateDropdown = ref(null);
const topGraduateBtn = ref(null);

// Filters
const graduateProgramFilter = ref('');
const graduateCareerFilter = ref('');

// Multi-select
const topSelectedGraduateIds = ref([]);
const topSelectedInternshipId = ref('');
const showTopAssignConfirm = ref(false);

// Program filter options
const allPrograms = computed(() => props.programs);

// Career opportunity options, filtered by selected program
const filteredCareerOpportunities = computed(() => {
  let list = props.institutionCareerOpportunities;
  if (graduateProgramFilter.value) {
    list = list.filter(co => co.program_id == graduateProgramFilter.value);
  }
  // Remove duplicates by id
  const seen = new Set();
  return list.filter(co => {
    if (seen.has(co.id)) return false;
    seen.add(co.id);
    return true;
  });
});

// Filtered graduates based on selected program and career opportunity
const filteredGraduates = computed(() => {
    return props.graduates.filter(g => {
        const matchesProgram = graduateProgramFilter.value
            ? g.program_id == graduateProgramFilter.value
            : true;
        const matchesCareer = graduateCareerFilter.value
            ? g.current_job_title === (
                props.institutionCareerOpportunities.find(co => co.id == graduateCareerFilter.value)?.title
            )
            : true;
        return matchesProgram && matchesCareer;
    });
});

// Dropdown click outside logic
function handleClickOutside(event) {
    if (
        topGraduateDropdown.value &&
        !topGraduateDropdown.value.contains(event.target) &&
        topGraduateBtn.value &&
        !topGraduateBtn.value.contains(event.target)
    ) {
        showTopGraduateDropdown.value = false;
    }
}
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const showDetails = ref(null); // internship id
const showAddDropdown = ref(null); // internship id
const selectedGraduateIds = ref([]);
const showConfirmAdd = ref(false);
const showConfirmRemove = ref(false);
const graduateToRemove = ref({ internshipId: null, graduateId: null });

function toggleDetails(id) {
    showDetails.value = showDetails.value === id ? null : id;
}

function toggleAddDropdown(id) {
    showAddDropdown.value = showAddDropdown.value === id ? null : id;
    selectedGraduateIds.value = [];
}

function confirmAdd(internshipId) {
    if (!selectedGraduateIds.value.length) return;
    showConfirmAdd.value = internshipId;
}

function addGraduates(internshipId) {
    router.post(route('internship-programs.assign'), {
        graduate_ids: selectedGraduateIds.value,
        internship_program_id: internshipId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showAddDropdown.value = null;
            showConfirmAdd.value = false;
            selectedGraduateIds.value = [];
        }
    });
}

function confirmRemove(internshipId, graduateId) {
    graduateToRemove.value = { internshipId, graduateId };
    showConfirmRemove.value = true;
}

function removeGraduate() {
    router.post(route('internship-programs.remove-graduate'), {
        internship_program_id: graduateToRemove.value.internshipId,
        graduate_id: graduateToRemove.value.graduateId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmRemove.value = false;
            graduateToRemove.value = { internshipId: null, graduateId: null };
        }
    });
}

function confirmTopAssign() {
    if (!topSelectedGraduateIds.value.length || !topSelectedInternshipId.value) return;
    showTopAssignConfirm.value = true;
}

function assignTopSelected() {
    router.post(route('internship-programs.assign'), {
        graduate_ids: topSelectedGraduateIds.value,
        internship_program_id: topSelectedInternshipId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showTopAssignConfirm.value = false;
            topSelectedGraduateIds.value = [];
            topSelectedInternshipId.value = '';
        }
    });
}
</script>

<template>
    <AppLayout title="Assign Graduates to Internship Programs">
        <div class="py-8 space-y-8 max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Active Internship Programs</h2>

            <!-- TOP ASSIGN SECTION -->
            <div class="bg-white rounded-xl shadow p-6 mb-8 flex flex-col md:flex-row md:items-end gap-4">
                <!-- Custom Dropdown for Graduate Multi-Select -->
                <div class="flex-1 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Graduate(s)</label>
                    <!-- FILTERS -->
                    <div class="flex gap-2 mb-2">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Filter by Program</label>
                            <select v-model="graduateProgramFilter" class="w-full border-gray-300 rounded">
                                <option value="">All Programs</option>
                                <option v-for="p in props.programs" :key="p.id" :value="p.id">
                                    {{ p.degree }} - {{ p.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Filter by Career Opportunity</label>
                            <select v-model="graduateCareerFilter" class="w-full border-gray-300 rounded">
                                <option value="">All Careers</option>
                                <option v-for="c in filteredCareerOpportunities" :key="c.id" :value="c.id">
                                    {{ c.title }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <!-- DROPDOWN -->
                    <button ref="topGraduateBtn" type="button"
                        @click.stop="showTopGraduateDropdown = !showTopGraduateDropdown"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-left bg-white">
                        <span v-if="topSelectedGraduateIds.length === 0" class="text-gray-400">Select
                            graduates...</span>
                        <span v-else>
                            {{props.graduates.filter(g => topSelectedGraduateIds.includes(g.id)).map(g => g.first_name
                                + ' ' + g.last_name).join(', ') }}
                        </span>
                        <span class="float-right">&#9662;</span>
                    </button>
                    <div v-if="showTopGraduateDropdown" ref="topGraduateDropdown" id="top-graduate-dropdown"
                        class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <div v-for="g in filteredGraduates" :key="g.id"
                            class="flex items-center px-3 py-2 hover:bg-gray-100">
                            <input type="checkbox" :id="'top-graduate-' + g.id" :value="g.id"
                                v-model="topSelectedGraduateIds" class="mr-2" />
                            <label :for="'top-graduate-' + g.id" class="cursor-pointer select-none">
                                {{ g.first_name }} {{ g.last_name }}
                            </label>
                        </div>
                        <div v-if="filteredGraduates.length === 0" class="px-3 py-2 text-gray-400">No graduates found.
                        </div>
                    </div>
                </div>
                <!-- Internship Program Dropdown -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Internship Program</label>
                    <select v-model="topSelectedInternshipId" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="">Select Internship</option>
                        <option v-for="ip in internshipPrograms" :key="ip.id" :value="ip.id">
                            {{ ip.title }}
                        </option>
                    </select>
                </div>
                <div class="flex-shrink-0 mt-2 md:mt-0">
                    <button @click="confirmTopAssign"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition w-full"
                        :disabled="!topSelectedGraduateIds.length || !topSelectedInternshipId">
                        Assign Selected
                    </button>
                </div>
            </div>

            <!-- Confirm Top Assign Modal -->
            <div v-if="showTopAssignConfirm"
                class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
                    <div class="text-blue-600 text-3xl mb-2">+</div>
                    <div class="text-lg font-semibold mb-2">Confirm Assignment</div>
                    <div class="mb-4">Assign selected graduates to this internship program?</div>
                    <div class="mt-4 flex justify-center gap-4">
                        <button @click="assignTopSelected"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Yes
                        </button>
                        <button @click="showTopAssignConfirm = false"
                            class="px-4 py-2 rounded border border-gray-300 bg-white hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="ip in internshipPrograms" :key="ip.id"
                    class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-200 border border-gray-100 relative flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">{{ ip.title }}</h3>
                        </div>
                        <button @click="toggleDetails(ip.id)"
                            class="text-gray-400 hover:text-blue-600 p-2 rounded-full transition"
                            :title="showDetails === ip.id ? 'Hide Details' : 'View Details'">
                            <EyeIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div v-if="showDetails === ip.id" class="p-4 flex-1 flex flex-col gap-3">
                        <div>
                            <span class="font-semibold text-gray-700">Programs:</span>
                            <span v-if="ip.programs.length" class="text-gray-600">{{ip.programs.map(p =>
                                p.name).join(', ') }}</span>
                            <span v-else class="text-gray-400">None</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Career Opportunities:</span>
                            <span v-if="ip.career_opportunities.length" class="text-gray-600">{{
                                ip.career_opportunities.map(c => c.title).join(', ') }}</span>
                            <span v-else class="text-gray-400">None</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Assigned Graduates:</span>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <span v-if="ip.graduates.length === 0" class="text-gray-400">None</span>
                                <span v-for="g in ip.graduates" :key="g.id"
                                    class="inline-flex items-center bg-blue-50 text-blue-800 px-2 py-1 rounded-full text-sm shadow-sm">
                                    {{ g.first_name }} {{ g.last_name }}
                                    <button @click="confirmRemove(ip.id, g.id)"
                                        class="ml-1 text-red-500 hover:text-red-700 p-1 rounded-full transition"
                                        title="Remove Graduate">
                                        <XIcon class="w-4 h-4" />
                                    </button>
                                </span>
                            </div>
                        </div>
                        <!-- Add Graduate Dropdown -->
                        <div class="mt-2">
                            <button @click="toggleAddDropdown(ip.id)"
                                class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition"
                                title="Add Graduate">
                                <PlusIcon class="w-4 h-4 mr-1" /> Add Graduate
                            </button>
                            <div v-if="showAddDropdown === ip.id"
                                class="mt-2 bg-gray-50 border border-gray-200 rounded p-3 shadow-lg">
                                <div class="max-h-40 overflow-y-auto">
                                    <div v-for="g in graduates.filter(g => !ip.graduates.some(ig => ig.id === g.id))"
                                        :key="g.id" class="flex items-center mb-1">
                                        <input type="checkbox" :id="`add-gra-${ip.id}-${g.id}`" :value="g.id"
                                            v-model="selectedGraduateIds" class="mr-2 accent-blue-600" />
                                        <label :for="`add-gra-${ip.id}-${g.id}`"
                                            class="cursor-pointer select-none text-gray-700">
                                            {{ g.first_name }} {{ g.last_name }}
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-end gap-2">
                                    <button @click="confirmAdd(ip.id)"
                                        class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition"
                                        :disabled="selectedGraduateIds.length === 0">
                                        Add Selected
                                    </button>
                                    <button @click="toggleAddDropdown(null)"
                                        class="px-4 py-1 rounded border border-gray-300 bg-white hover:bg-gray-100 transition">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Add Modal -->
            <div v-if="showConfirmAdd"
                class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
                    <div class="text-blue-600 text-3xl mb-2">+</div>
                    <div class="text-lg font-semibold mb-2">Confirm Add</div>
                    <div class="mb-4">Add selected graduates to this internship?</div>
                    <div class="mt-4 flex justify-center gap-4">
                        <button @click="addGraduates(showConfirmAdd)"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Yes
                        </button>
                        <button @click="showConfirmAdd = false"
                            class="px-4 py-2 rounded border border-gray-300 bg-white hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirm Remove Modal -->
            <div v-if="showConfirmRemove"
                class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
                    <div class="text-red-600 text-3xl mb-2">×</div>
                    <div class="text-lg font-semibold mb-2">Confirm Remove</div>
                    <div class="mb-4">Remove this graduate from the internship?</div>
                    <div class="mt-4 flex justify-center gap-4">
                        <button @click="removeGraduate"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                            Yes
                        </button>
                        <button @click="showConfirmRemove = false"
                            class="px-4 py-2 rounded border border-gray-300 bg-white hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>