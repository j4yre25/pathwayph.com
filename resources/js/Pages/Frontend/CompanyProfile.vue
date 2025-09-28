<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, reactive, ref, watch, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useFormattedMobileNumber } from "@/Composables/useFormattedMobileNumber.js";
import { useFormattedTelephoneNumber } from "@/Composables/useFormattedTelephoneNumber.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    company: Object,
    userRole: {
        type: String,
        required: true, // 'company' or 'graduate'
    },
});

console.log(props.userRole)

const page = usePage();
const user = page.props.auth?.user;

console.log(user)

const jobsPerPage = 5;
const currentPage = ref(1);

const totalJobs = computed(() => props.company.jobs?.length || 0);
const totalPages = computed(() => Math.ceil(totalJobs.value / jobsPerPage));

const paginatedJobs = computed(() => {
    if (!props.company.jobs) return [];
    const start = (currentPage.value - 1) * jobsPerPage;
    return props.company.jobs.slice(start, start + jobsPerPage);
});

function goToPage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

const isEditing = ref(false);
const localDescription = ref(props.company.description || '');

watch(() => props.company.description, (newVal) => {
    localDescription.value = newVal || '';
});

const canEdit = computed(() => props.userRole === 'company');

const saveDescription = () => {
    if (!canEdit.value) return;

    const payload = {
        company_name: props.company.company_name,
        company_street_address: props.company.company_street_address,
        company_brgy: props.company.company_brgy,
        company_city: props.company.company_city,
        company_province: props.company.company_province,
        company_zip_code: props.company.company_zip_code,
        company_contact_number: props.company.company_contact_number,
        company_email: props.company.company_email,
        company_telephone_number: props.company.company_telephone_number,
        company_description: localDescription.value,
    };

    router.post(route('company-profile.post'), payload, {
        onSuccess: () => {
            isEditing.value = false;
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

const cancelEditing = () => {
    isEditing.value = false;
    localDescription.value = props.company.description || '';
};

const contactForm = reactive({
    contact: props.company?.company_contact_number || '',
    telephone: props.company?.telephone_number || '',
});

const { formattedMobileNumber } = useFormattedMobileNumber(contactForm, 'contact');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(contactForm, 'telephone')


function requestReferral(companyId, jobId) {
    router.post(route('graduate.referral.request'), {
        company_id: companyId,
        job_id: jobId,
    }, {
        onSuccess: () => {
            alert('Referral request sent to PESO!');
        },
        onError: () => {
            alert('Could not request referral. Please try again.');
        }
    });
}

</script>

<template>
    <AppLayout title="Company Profile">
        <!-- White Gradient Background -->
        <div class="min-h-screen bg-gradient-to-br from-white via-gray-50 to-gray-100">
            <!-- Subtle Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-100 rounded-full opacity-20"></div>
                <div class="absolute top-1/2 -left-20 w-60 h-60 bg-purple-100 rounded-full opacity-20"></div>
                <div class="absolute bottom-20 right-1/4 w-40 h-40 bg-pink-100 rounded-full opacity-20"></div>
            </div>

            <!-- Modern Gradient Header -->
            <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-pink-500 h-64">
                <!-- Abstract Background Shapes -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-0 left-1/4 w-96 h-96 bg-white opacity-10 rounded-full transform -translate-y-1/2"></div>
                    <div class="absolute top-1/2 right-0 w-80 h-80 bg-white opacity-5 rounded-full transform translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-white opacity-10 rounded-full transform translate-y-1/2 -translate-x-1/2"></div>
                </div>

                <!-- Navigation Breadcrumb -->
                <div class="relative z-10 pt-6 px-4 sm:px-6 lg:px-8">
                    <nav class="flex items-center space-x-2 text-white/80 text-sm">
                        <a href="#" class="hover:text-white transition-colors">Home</a>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Company Profile</span>
                    </nav>
                </div>

                <!-- Star Icon -->
                <div class="absolute top-6 right-6 text-white/60">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="relative -mt-32 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <!-- Company Avatar and Info -->
                    <div class="px-8 py-8">
                        <div class="flex flex-col items-center text-center">
                            <!-- Company Logo -->
                            <div class="relative mb-6">
                                <img :src="company.profile_photo_path || '/images/default-logo.png'" alt="Company Logo"
                                    class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg" />
                                <span v-if="company.is_featured"
                                    class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                    Featured
                                </span>
                            </div>

                            <!-- Company Name and Info -->
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ company.company_name }}</h1>
                            <p class="text-gray-600 mb-6 flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ company.address || 'Location not available' }}</span>
                                <a v-if="company.map_link" :href="company.map_link" target="_blank"
                                    class="text-blue-500 hover:underline text-sm">
                                    View on Map
                                </a>
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center justify-center space-x-8 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ company.job_post_count || 0 }}</div>
                                    <div class="text-sm text-gray-500">Jobs Posted</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ company.employees_count || 0 }}</div>
                                    <div class="text-sm text-gray-500">Employees</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ company.years_active || 0 }}</div>
                                    <div class="text-sm text-gray-500">Years Active</div>
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="flex items-center justify-center space-x-4 mb-6">
                                <a v-for="(link, key) in company.social_links || {}" :key="key" :href="link" target="_blank"
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors">
                                    <i :class="`fab fa-${key}`"></i>
                                </a>
                            </div>

                            <!-- Action Button -->
                            <a v-if="canEdit" :href="route('profile.show', { id: company.id, edit: 'company' })"
                                class="bg-blue-500 text-white px-8 py-3 rounded-full font-medium hover:bg-blue-600 transition-colors shadow-lg">
                                Edit Profile
                            </a>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="border-t border-gray-200">
                        <nav class="flex justify-center space-x-8 px-8">
                            <button class="flex items-center space-x-2 py-4 border-b-2 border-blue-500 text-blue-600 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                </svg>
                                <span>About</span>
                            </button>
                            <button class="flex items-center space-x-2 py-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
                                </svg>
                                <span>Jobs</span>
                            </button>
                            <button class="flex items-center space-x-2 py-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Gallery</span>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="py-12">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">


                <!-- Overview Section -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800">Date Joined</h4>
                        <p class="text-gray-600">{{ company.created_at || 'N/A' }}</p>
                    </div>
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800">Branch</h4>
                        <p class="text-gray-600">{{ company.branch || 'N/A' }}</p>
                    </div>
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800">Posted Jobs</h4>
                        <p class="text-gray-600">{{ company.job_post_count || 0 }}</p>
                    </div>
                </div>

                <!-- Description and Contact Info in Grid -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Company Description (spans 2 cols on md+) -->
                    <div class="md:col-span-2 bg-white c">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xl font-semibold text-gray-800">Company Description</h4>
                            <Pencil v-if="canEdit" @click="isEditing = true"
                                class="w-5 h-5 text-gray-500 hover:text-blue-500 cursor-pointer" />
                        </div>

                        <!-- Edit Mode -->
                        <div v-if="isEditing" class="mt-4">
                            <textarea v-model="localDescription"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                                rows="4"></textarea>
                            <button @click="saveDescription"
                                class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                                Save
                            </button>
                            <button @click="cancelEditing"
                                class="ml-2 mt-2 bg-red-600 text-white px-4 py-1 rounded hover:bg-blue-700">Cancel</button>
                        </div>

                        <!-- View Mode -->
                        <p v-else class="text-gray-600 mt-4">{{ localDescription || 'No description available.' }}</p>
                    </div>
                    <!-- Contact Information -->
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-4">Contact Information</h4>
                        <div class="mt-4 space-y-4">
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-map-marker-alt text-indigo-500 text-lg"></i>
                                <p class="text-gray-600">
                                    <strong>Address:</strong> {{ company.address || 'N/A' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-envelope text-indigo-500 text-lg"></i>
                                <p class="text-gray-600">
                                    <strong>Email Address:</strong> {{ company.company_email || 'N/A' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-phone text-indigo-500 text-lg"></i>
                                <p class="text-gray-600">
                                    <strong>Mobile Number:</strong> {{ formattedMobileNumber || 'N/A' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-phone text-indigo-500 text-lg"></i>
                                <p class="text-gray-600">
                                    <strong>Telephone Number:</strong> {{ formattedTelephoneNumber || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!--     Jobs Section -->
                <div v-if="company.jobs && company.jobs.length" class="mt-10">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Available Jobs</h4>
                        <div class="divide-y divide-gray-100">
                            <div v-for="job in paginatedJobs" :key="job.id"
                                class="flex flex-col md:flex-row md:items-center justify-between py-4">
                                <div>
                                    <span class="block text-lg font-medium text-gray-900">{{ job.job_title }}</span>
                                    <span class="block text-sm text-gray-500" v-if="job.sector">{{ job.sector }}</span>
                                </div>

                            </div>
                        </div>
                        <!-- Pagination Controls -->
                        <div v-if="totalPages > 1" class="flex justify-center mt-6 space-x-2">
                            <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300"
                                :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                                Prev
                            </button>
                            <button v-for="pageNum in totalPages" :key="pageNum" class="px-3 py-1 rounded"
                                :class="currentPage === pageNum ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                @click="goToPage(pageNum)">
                                {{ pageNum }}
                            </button>
                            <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300"
                                :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Team Members Section
            <div v-if="company.team_members && company.team_members.length" class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800">Team Members</h4>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="member in company.team_members"
                        :key="member.id"
                        class="bg-gray-50 shadow rounded-lg p-4 flex items-center space-x-4">
                        <img
                            :src="member.photo || '/images/default-avatar.png'"
                            alt="Team Member"
                            class="w-16 h-16 rounded-full object-cover"
                        />
                    <div>
                        <h5 class="text-lg font-semibold text-gray-800">{{ member.name }}</h5>
                        <p class="text-sm text-gray-600">{{ member.role }}</p>
                    </div>
                    </div>
                </div>
            </div> -->

                </div>
            </div>
        </div>
    </AppLayout>
</template>
