<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from '@/Components/Modal.vue';
import { usePage } from "@inertiajs/vue3";
import { computed, ref, defineComponent, onMounted } from "vue";
import Welcome from "@/Components/Welcome.vue";
import CompanyDashboard from "../Pages/Company/CompanyDashboard.vue";
import InstitutionDashboard from "./Institutions/Dashboard/InstitutionDashboard.vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/vue3";
import jsPDF from "jspdf";
import GraduateDashboard from "@/Pages/Frontend/GraduateDashboard.vue";



const page = usePage();
const userNotApproved = computed(() => page.props.userNotApproved ?? false);
const showModal = ref(false);
const showReferralModal = ref(false);

const showApprovalModal = ref(false)

const selectedCompany = ref("");
const searchQuery = ref("");
const companies = page.props.companies ?? [];

const filteredCompanies = computed(() =>
    companies.filter(c =>
        c.company_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
);

const dateFrom = ref('');
const dateTo = ref('');

function applyDashboardFilter() {
    Inertia.get(route('dashboard'), {
        date_from: dateFrom.value,
        date_to: dateTo.value
    }, { preserveState: true });
}

function resetDashboardFilter() {
    dateFrom.value = '';
    dateTo.value = '';
    Inertia.get(route('dashboard'), {}, { preserveState: true });
}

function selectCompany(company) {
    selectedCompany.value = company.id;
    searchQuery.value = company.company_name;
    showSuggestions.value = false;
    Inertia.get(route("dashboard"), { company_id: company.id });
}

const showSuggestions = ref(false);


const employerMetric = ref(page.props.employerMetric ?? "openings");
function filterEmployers() {
    Inertia.get(route("dashboard"), { employer_metric: employerMetric.value }, { preserveState: true });
}

const sectorSearch = ref("");
const sectors = computed(() => page.props.topSectorsChartOption?.series?.[0]?.data ?? []);
const filteredSectors = computed(() =>
    sectors.value.filter(s =>
        s.name.toLowerCase().includes(sectorSearch.value.toLowerCase())
    )
);

const categorySearch = ref("");
const categories = computed(() => page.props.inDemandCategories ?? []);
const filteredCategories = computed(() =>
    categories.value.filter(c =>
        c.name.toLowerCase().includes(categorySearch.value.toLowerCase())
    )
);


onMounted(() => {
    if (page.props.needsApproval) {
        showApprovalModal.value = true;
    }
});

// Dummy Data
const kpi = computed(() => page.props.kpi ?? {});
const recentJobs = page.props.recentJobs ?? [];

console.log('Jobs', recentJobs)
const expiringJobs = computed(() => page.props.expiringJobs ?? []);
const topSectorsChartOption = computed(() => page.props.topSectorsChartOption ?? {});
const referralTrendOption = computed(() => page.props.referralTrendOption ?? {});
const topEmployersOption = computed(() => page.props.topEmployersOption ?? {});
const pendingReferrals = computed(() => page.props.pendingReferrals ?? []);
const upcomingEvents = computed(() => page.props.upcomingEvents ?? []);
const eventAttendanceOption = computed(() => page.props.eventAttendanceOption ?? {});
const alerts = computed(() => page.props.alerts ?? {});
const sectorPieOption = computed(() => page.props.topSectorsChartOption ?? {});
const inDemandCategories = computed(() => page.props.inDemandCategories ?? []);





const hasFilledReferral = ref(page.props.hasReferralLetter ?? false);

if (userNotApproved.value) {
    showModal.value = true;
}

console.log("isGraduate:", page.props.roles);
console.log("userNotApproved:", userNotApproved.value);
console.log("hasFilledReferral:", hasFilledReferral.value);


const handleLogout = () => {
    Inertia.visit(route("logout"), { method: "post" });
};

function submitReferral() {
    const doc = new jsPDF();
    let y = 25;

    // Title
    doc.setFontSize(16);
    doc.setFont("times", "bold");
    doc.text("JOB REFERRAL LETTER", 105, y, { align: "center" });
    y += 15;

    // Date
    doc.setFontSize(12);
    doc.setFont("times", "normal");
    doc.text(`Date: ${new Date().toLocaleDateString()}`, 160, y, { align: "right" });
    y += 10;

    // Recipient
    doc.text(`To: ${referralForm.company}`, 15, y); y += 8;
    doc.text(`Subject: Application for ${referralForm.position}`, 15, y); y += 12;

    // Greeting
    doc.text("Dear Sir/Madam:", 15, y); y += 12;

    // Body
    const intro = `I am writing to formally refer myself for the position of ${referralForm.position} at ${referralForm.company}. Please find below my personal and professional details for your consideration.`;
    doc.text(doc.splitTextToSize(intro, 180), 15, y);
    y += 16;

    // Section: Applicant Information
    doc.setFont("times", "bold");
    doc.text("Applicant Information", 15, y); y += 8;
    doc.setFont("times", "normal");
    doc.text(`Full Name: ${referralForm.first_name} ${referralForm.middle_name} ${referralForm.surname} ${referralForm.suffix}`, 20, y); y += 8;
    doc.text(`Date of Birth: ${referralForm.dob}`, 20, y);
    doc.text(`Sex: ${referralForm.sex}   Civil Status: ${referralForm.civil_status}`, 100, y); y += 8;
    doc.text(`Religion: ${referralForm.religion}`, 20, y); y += 8;
    doc.text(`Address: ${referralForm.address}`, 20, y); y += 8;
    doc.text(`Contact Number: ${referralForm.contact}`, 20, y);
    doc.text(`Email: ${referralForm.email}`, 100, y); y += 8;
    doc.text(`TIN Number: ${referralForm.tin}`, 20, y); y += 8;
    doc.text(`Disability: ${referralForm.disability || "None"}`, 20, y); y += 10;

    // Section: Job Preference
    doc.setFont("times", "bold");
    doc.text("Job Preference", 15, y); y += 8;
    doc.setFont("times", "normal");
    doc.text(`Employment Status/Type: ${referralForm.employment_status}`, 20, y); y += 8;
    doc.text(`Preferred Occupation: ${referralForm.preferred_occupation}`, 20, y); y += 8;
    doc.text(`Preferred Work Location: ${referralForm.preferred_location}`, 20, y); y += 10;

    // Section: Qualifications
    doc.setFont("times", "bold");
    doc.text("Qualifications", 15, y); y += 8;
    doc.setFont("times", "normal");
    doc.text(`Language/Dialect Proficiency: ${referralForm.language}`, 20, y); y += 8;
    doc.text(`Educational Background: ${referralForm.educational_background}`, 20, y); y += 8;
    doc.text(`Technical/Vocational and Other Training: ${referralForm.training}`, 20, y); y += 8;
    doc.text(`Eligibility/Professional License: ${referralForm.eligibility}`, 20, y); y += 8;
    doc.text(`Work Experience: ${referralForm.work_experience}`, 20, y); y += 8;
    doc.text(`Other Skills (without certificate): ${referralForm.other_skills}`, 20, y); y += 10;

    // Section: Message
    if (referralForm.message) {
        doc.setFont("times", "bold");
        doc.text("Additional Message", 15, y); y += 8;
        doc.setFont("times", "normal");
        doc.text(doc.splitTextToSize(referralForm.message, 180), 20, y);
        y += 16;
    }

    // Closing
    y += 8;
    doc.text("Thank you very much for your kind consideration.", 15, y); y += 12;
    doc.text("Respectfully yours,", 15, y); y += 20;

    // Signature
    doc.setFont("times", "bold");
    doc.text(`${referralForm.first_name} ${referralForm.middle_name} ${referralForm.surname}`, 15, y);
    doc.setFont("times", "normal");

    doc.save("Job_Referral_Letter.pdf");
    showReferralModal.value = false;
    referralForm.reset();
    hasFilledReferral.value = true;
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome to Pathway!
            </h2>
        </template>


        <div v-if="page.props.roles?.isCompany" class="py-12">
            <Welcome v-if="!page.props.roles?.isCompany" />
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    Welcome to the Dashboard
                </h3>
                <p class="mt-2 text-gray-600">
                    Here you can manage your account and view your statistics.
                </p>
            </div>
            <CompanyDashboard :summary="page.props.summary" :recentApplications="page.props.recentApplications"
                :applicationTrends="page.props.applicationTrends" :jobPerformance="page.props.jobPerformance" />
        </div>

        <div v-else-if="page.props.roles?.isInstitution" class="py-12">
            <Welcome v-if="!page.props.roles?.isInstitution" />
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    Welcome to the Dashboard
                </h3>
                <p class="mt-2 text-gray-600">
                    Here you can manage your account and view your statistics.
                </p>
            </div>
            <InstitutionDashboard :summary="page.props.summary" :graduates="page.props.graduates"
                :programs="page.props.programs" :careerOpportunities="page.props.careerOpportunities"
                :schoolYears="page.props.schoolYears"
                :institutionCareerOpportunities="page.props.institutionCareerOpportunities" />
        </div>

        <div v-if="page.props.auth.user.role === 'peso'" class="py-12">

            <!-- KPI Cards -->
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-9 mb-9">
                <div class="grid grid-cols-8 gap-4">
                    <!-- Employees Card -->
                    <div
                        class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <span class="text-blue-700 text-xs font-medium text-center">Registered Employers</span>
                        <span class="text-blue-900 text-lg font-bold">{{ kpi.registeredEmployers }}</span>
                    </div>

                    <!-- Clients Card -->
                    <div
                        class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-briefcase text-white"></i>
                        </div>
                        <span class="text-orange-700 text-xs font-medium text-center">Active Job Listings</span>
                        <span class="text-orange-900 text-lg font-bold">{{ kpi.activeJobListings }}</span>
                    </div>

                    <!-- Projects Card -->
                    <div
                        class="bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-project-diagram text-white"></i>
                        </div>
                        <span class="text-cyan-700 text-xs font-medium text-center">Registered Job Seekers</span>
                        <span class="text-cyan-900 text-lg font-bold">{{ kpi.registeredJobSeekers }}</span>
                    </div>

                    <!-- Events Card -->
                    <div
                        class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <span class="text-red-700 text-xs font-medium text-center">Referrals Issued (This Month)</span>
                        <span class="text-red-900 text-lg font-bold">{{ kpi.referralsThisMonth }}</span>
                    </div>

                    <!-- Reports Card -->
                    <div
                        class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <span class="text-purple-700 text-xs font-medium text-center">Upcoming Career Guidance</span>
                        <span class="text-purple-900 text-lg font-bold">{{ kpi.upcomingCareerGuidance }}</span>
                    </div>

                    <!-- Unemployed Card -->
                    <div
                        class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-user-times text-white"></i>
                        </div>
                        <span class="text-yellow-700 text-xs font-medium text-center">Unemployed</span>
                        <span class="text-yellow-900 text-lg font-bold">{{ kpi.unemployed }}</span>
                    </div>

                    <!-- Underemployed Card -->
                    <div
                        class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-user-clock text-white"></i>
                        </div>
                        <span class="text-indigo-700 text-xs font-medium text-center">Underemployed</span>
                        <span class="text-indigo-900 text-lg font-bold">{{ kpi.underemployed }}</span>
                    </div>

                    <!-- Employed Card -->
                    <div
                        class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-user-check text-white"></i>
                        </div>
                        <span class="text-green-700 text-xs font-medium text-center">Employed</span>
                        <span class="text-green-900 text-lg font-bold">{{ kpi.employed }}</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Filters -->
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-9 mb-10">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="border-b border-gray-200 flex items-center justify-between pb-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-filter text-white text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800">Dashboard Filters</h3>
                        </div>
                        <button type="button" @click="resetDashboardFilter"
                            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300">
                            <i class="fas fa-undo mr-2"></i> Reset Filters
                        </button>
                    </div>
                    <div class="pt-6">
                        <form @submit.prevent="applyDashboardFilter" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="date-from"
                                    class="flex text-sm font-semibold text-gray-700 mb-2 items-center">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Date From
                                </label>
                                <input id="date-from" type="date" v-model="dateFrom" @change="applyDashboardFilter"
                                    class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all" />
                            </div>
                            <div>
                                <label for="date-to" class="flex text-sm font-semibold text-gray-700 mb-2 items-center">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Date To
                                </label>
                                <input id="date-to" type="date" v-model="dateTo" @change="applyDashboardFilter"
                                    class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Recent Job Listings By Company -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="mb-4">
                            <h4 class="font-semibold mb-3 text-gray-800">Recent Job Listings By Company</h4>

                            <ul v-if="showSuggestions && searchQuery"
                                class="absolute z-10 bg-white border rounded w-full max-w-xs shadow mt-1">
                                <li v-for="company in filteredCompanies" :key="company.id"
                                    @click="selectCompany(company)" class="px-3 py-2 cursor-pointer hover:bg-blue-100">
                                    {{ company.company_name }}
                                </li>
                                <li v-if="filteredCompanies.length === 0" class="px-3 py-2 text-gray-400">
                                    No companies found.
                                </li>
                            </ul>
                        </div>
                        <table class="min-w-full text-sm mb-4">
                            <thead>
                                <tr class="text-gray-600">
                                    <th class="py-2 px-2 text-left">Title</th>
                                    <th class="py-2 px-2 text-left">Sector</th>
                                    <th class="py-2 px-2 text-left">Employer</th>
                                    <th class="py-2 px-2 text-left">Date Posted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="job in recentJobs" :key="job.title" class="hover:bg-gray-50 transition">
                                    <td class="py-2 px-2">{{ job.title }}</td>
                                    <td class="py-2 px-2">{{ job.sector }}</td>
                                    <td class="py-2 px-2">{{ job.employer }}</td>
                                    <td class="py-2 px-2">{{ job.date_posted }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Top Sectors Table -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Top Sectors</h4>

                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-gray-600">
                                    <th class="py-2 px-2 text-left">Sector</th>
                                    <th class="py-2 px-2 text-left">Jobs Posted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sector in filteredSectors" :key="sector.name"
                                    class="hover:bg-gray-50 transition">
                                    <td class="py-2 px-2">{{ sector.name }}</td>
                                    <td class="py-2 px-2">{{ sector.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Referral Activity & Top Employers -->
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Referrals This Month vs Last Month By PESO</h4>
                        <VueECharts :option="referralTrendOption" style="height: 200px;" />
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Top Employers By</h4>
                        <div class="mb-4">
                            <select v-model="employerMetric" @change="filterEmployers"
                                class="border rounded px-3 py-2 w-full max-w-xs">
                                <option value="openings">Job Openings</option>
                                <option value="referrals">Referrals</option>
                                <option value="hired">Hired Applicants</option>
                            </select>
                        </div>
                        <VueECharts :option="topEmployersOption" style="height: 300px;" />
                    </div>
                </div>
            </div>

            <!-- In-demand Categories Table -->
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold mb-3 text-gray-800">In-demand Categories According to Sector</h4>

                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-gray-600">
                                <th class="py-2 px-2 text-left">Category</th>
                                <th class="py-2 px-2 text-left">Jobs Posted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cat in filteredCategories" :key="cat.name" class="hover:bg-gray-50 transition">
                                <td class="py-2 px-2">{{ cat.name }}</td>
                                <td class="py-2 px-2">{{ cat.count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Quick Actions -->

        </div>


        <div v-else-if="page.props.roles?.isGraduate" class="py-12">
            <GraduateDashboard :summary="page.props.summary" :kpi="page.props.kpi"
                :recommendedJobs="page.props.recommendedJobs" :graduate="page.props.graduate"
                :aboutMe="page.props.aboutMe" :currentJob="page.props.currentJob"
                :highestEducation="page.props.highestEducation"
                :featuredCompanies="page.props.featuredCompanies"
                :careerGoals="page.props.careerGoals" />
        </div>





        <div class=" py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Account Not Approved Modal -->
                    <Modal v-if="showModal" :show="showModal">
                        <template #title> Account Not Approved </template>
                        <template #content>
                            <p>
                                Your account is not approved yet. Some features
                                may be disabled.
                            </p>
                        </template>
                        <template #footer>
                            <button class="bg-blue-500 text-white px-4 py-2 rounded" @click="handleLogout">
                                Okay
                            </button>
                        </template>
                    </Modal>



                </div>
            </div>
        </div>
    </AppLayout>
</template>
