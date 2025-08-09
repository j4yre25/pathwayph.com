<script setup>



import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, ref, defineComponent } from "vue";
import Welcome from "@/Components/Welcome.vue";
import Modal from "../Components/Modal.vue";
import CompanyDashboard from "../Pages/Company/CompanyDashboard.vue";
import InstitutionDashboard from "./Institutions/Dashboard/InstitutionDashboard.vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/vue3";
import jsPDF from "jspdf";



const page = usePage();
const userNotApproved = computed(() => page.props.userNotApproved ?? false);
const showModal = ref(false);
const showReferralModal = ref(false);


// Dummy Data
const kpi = computed(() => page.props.kpi ?? {});
const recentJobs = computed(() => page.props.recentJobs ?? []);
const expiringJobs = computed(() => page.props.expiringJobs ?? []);
const topSectorsChartOption = computed(() => page.props.topSectorsChartOption ?? {});
const referralTrendOption = computed(() => page.props.referralTrendOption ?? {});
const topEmployersOption = computed(() => page.props.topEmployersOption ?? {});
const pendingReferrals = computed(() => page.props.pendingReferrals ?? []);
const upcomingEvents = computed(() => page.props.upcomingEvents ?? []);
const eventAttendanceOption = computed(() => page.props.eventAttendanceOption ?? {});
const alerts = computed(() => page.props.alerts ?? {});
const sectorPieOption = computed(() => page.props.sectorPieOption ?? {});
const inDemandCategories = computed(() => page.props.inDemandCategories ?? []);




const referralForm = useForm({
    surname: "",
    first_name: "",
    middle_name: "",
    suffix: "",
    dob: "",
    sex: "",
    civil_status: "",
    religion: "",
    address: "",
    contact: "",
    email: "",
    tin: "",
    disability: "",
    employment_status: "",
    preferred_occupation: "",
    preferred_location: "",
    language: "",
    educational_background: "",
    training: "",
    eligibility: "",
    work_experience: "",
    other_skills: "",
    position: "",
    company: "",
    message: "",
});

const hasFilledReferral = ref(page.props.hasReferralLetter ?? false);

if (userNotApproved.value) {
    showModal.value = true;
}

console.log("isGraduate:", page.props.roles?.isGraduate);
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
                Dashboard
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
    <CompanyDashboard   
        :summary="page.props.summary"
        :recentApplications="page.props.recentApplications"
        :applicationTrends="page.props.applicationTrends"
        :jobPerformance="page.props.jobPerformance"
        />
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
            <!-- Dashboard Header -->


            <!-- KPI Cards -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Registered Employers</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.registeredEmployers }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Active Job Listings</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.activeJobListings }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Registered Job Seekers</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.registeredJobSeekers }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Referrals Issued (This Month)</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.referralsThisMonth }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Successful Placements</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.successfulPlacements }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Upcoming Career Guidance</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.upcomingCareerGuidance }}</span>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                        <span class="text-gray-500 text-sm">Pending Employer Registrations</span>
                        <span class="text-3xl font-bold text-gray-900 mt-2">{{ kpi.pendingEmployerRegistrations
                            }}</span>
                    </div>
                </div>
            </div>

            <!-- Job Posting Overview -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Recent Job Listings</h4>
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
                        <div>
                            <h5 class="font-semibold text-sm mb-1 text-gray-700">Expiring Soon</h5>
                            <ul class="space-y-1">
                                <li v-for="job in expiringJobs" :key="job.title" class="text-sm text-red-700">
                                    {{ job.title }} <span class="text-xs text-gray-500">({{ job.expires_at }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-center">
                        <h4 class="font-semibold mb-3 text-gray-800">Top Hiring Sectors</h4>
                        <VueECharts :option="topSectorsChartOption" style="height: 300px;" />
                    </div>
                </div>
            </div>

            <!-- Referral Activity -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Referrals This Month vs Last Month</h4>
                        <VueECharts :option="referralTrendOption" style="height: 200px;" />
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Top Employers Receiving Referrals</h4>
                        <VueECharts :option="topEmployersOption" style="height: 200px;" />
                        <h5 class="font-semibold text-sm mt-4 mb-1 text-gray-700">Pending Referrals</h5>
                        <ul class="space-y-1">
                            <li v-for="r in pendingReferrals" :key="r.job_seeker" class="text-sm">
                                {{ r.job_seeker }} - {{ r.employer }} <span class="text-xs text-gray-500">(Due: {{
                                    r.due_date
                                    }})</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Career Guidance & Events -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Upcoming Events</h4>
                        <ul class="space-y-1">
                            <li v-for="e in upcomingEvents" :key="e.title" class="text-sm">
                                {{ e.title }} <span class="text-xs text-gray-500">- {{ e.date }} @ {{ e.venue }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-center">
                        <h4 class="font-semibold mb-3 text-gray-800">Event Attendance Trends</h4>
                        <VueECharts :option="eventAttendanceOption" style="height: 200px;" />
                    </div>
                </div>
            </div>

            <!-- Compliance Alerts -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Reports & Compliance Alerts</h2>
                <ul class="list-disc ml-6 text-sm space-y-1">
                    <li v-if="alerts.employersNoPermit.length" class="text-red-700">Employers without updated Mayor’s
                        Permit
                    </li>
                    <li v-if="alerts.unreviewedApplications.length" class="text-yellow-700">Unreviewed Employer
                        Applications
                    </li>
                    <li v-if="alerts.inactiveEmployers.length" class="text-gray-700">Inactive Employers (no postings in
                        last 6
                        months)</li>
                </ul>
            </div>

            <!-- Sector & Category Insights -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">Job Distribution by Sector</h4>
                        <VueECharts :option="sectorPieOption" style="height: 250px;" />
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">In-demand Categories</h4>
                        <div>
                            <span v-for="tag in inDemandCategories" :key="tag.name"
                                class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full m-1 font-medium">
                                {{ tag.name }} <span class="text-xs text-gray-500">({{ tag.count }})</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <button
                        class="rounded-md px-6 py-2 bg-blue-600 text-white shadow hover:bg-blue-700 transition font-semibold">
                        Add New Job Post
                    </button>
                    <button
                        class="rounded-md px-6 py-2 bg-gray-600 text-white shadow hover:bg-gray-700 transition font-semibold">
                        Register Employer
                    </button>
                    <button
                        class="rounded-md px-6 py-2 bg-green-600 text-white shadow hover:bg-green-700 transition font-semibold">
                        Generate Report
                    </button>
                </div>
            </div>
        </div>






<div class="py-12">
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

                    <!-- Job Referral Letter Modal -->
                    <Modal v-if="showReferralModal" :show="showReferralModal" @close="showReferralModal = false"
                        max-width="2xl">
                        <template #title> Job Referral Letter </template>
                        <template #content>
                            <form @submit.prevent="submitReferral" id="referralForm">
                                <!-- Personal Info -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="surname" class="block text-sm font-medium text-gray-700">
                                            Surname
                                        </label>
                                        <input v-model="referralForm.surname" type="text" id="surname" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700">
                                            First Name
                                        </label>
                                        <input v-model="referralForm.first_name" type="text" id="first_name" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="middle_name" class="block text-sm font-medium text-gray-700">
                                            Middle Name
                                        </label>
                                        <input v-model="referralForm.middle_name" type="text" id="middle_name"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="suffix" class="block text-sm font-medium text-gray-700">
                                            Suffix
                                        </label>
                                        <input v-model="referralForm.suffix" type="text" id="suffix"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="dob" class="block text-sm font-medium text-gray-700">
                                            Date of Birth
                                        </label>
                                        <input v-model="referralForm.dob" type="date" id="dob" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="sex" class="block text-sm font-medium text-gray-700">
                                            Sex
                                        </label>
                                        <select v-model="referralForm.sex" id="sex" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                            <option value="">Select Sex</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="civil_status" class="block text-sm font-medium text-gray-700">
                                            Civil Status
                                        </label>
                                        <select v-model="referralForm.civil_status" id="civil_status" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                            <option value="">Select Civil Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="religion" class="block text-sm font-medium text-gray-700">
                                            Religion
                                        </label>
                                        <input v-model="referralForm.religion" type="text" id="religion"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700">
                                            Address
                                        </label>
                                        <input v-model="referralForm.address" type="text" id="address" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="contact" class="block text-sm font-medium text-gray-700">
                                            Contact Number
                                        </label>
                                        <input v-model="referralForm.contact" type="text" id="contact" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">
                                            Email
                                        </label>
                                        <input v-model="referralForm.email" type="email" id="email" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="position" class="block text-sm font-medium text-gray-700">
                                            Position
                                        </label>
                                        <input v-model="referralForm.position" type="text" id="position" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label for="company" class="block text-sm font-medium text-gray-700">
                                            Company
                                        </label>
                                        <input v-model="referralForm.company" type="text" id="company" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">TIN Number</label>
                                        <input v-model="referralForm.tin" type="text"
                                            class="w-full border rounded px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Disability</label>
                                        <select v-model="referralForm.disability"
                                            class="w-full border rounded px-3 py-2">
                                            <option value="">None</option>
                                            <option value="Visual">Visual</option>
                                            <option value="Hearing">Hearing</option>
                                            <option value="Speech">Speech</option>
                                            <option value="Physical">Physical</option>
                                            <option value="Mental">Mental</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Preferred Work Location</label>
                                        <input v-model="referralForm.preferred_location" type="text"
                                            class="w-full border rounded px-3 py-2" />
                                    </div>
                                </div>

                                <!-- Language/Dialect Proficiency -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Language/Dialect Proficiency</label>
                                    <input v-model="referralForm.language" type="text"
                                        class="w-full border rounded px-3 py-2" placeholder="e.g. English, Filipino" />
                                </div>

                                <!-- Educational Background -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Educational Background</label>
                                    <textarea v-model="referralForm.educational_background"
                                        class="w-full border rounded px-3 py-2" rows="2"
                                        placeholder="e.g. BSIT, 2023, University Name"></textarea>
                                </div>

                                <!-- Technical/Vocational and Other Training -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Technical/Vocational and Other
                                        Training</label>
                                    <textarea v-model="referralForm.training" class="w-full border rounded px-3 py-2"
                                        rows="2"></textarea>
                                </div>

                                <!-- Eligibility/Professional License -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Eligibility/Professional
                                        License</label>
                                    <input v-model="referralForm.eligibility" type="text"
                                        class="w-full border rounded px-3 py-2" />
                                </div>

                                <!-- Work Experience -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Work Experience</label>
                                    <textarea v-model="referralForm.work_experience"
                                        class="w-full border rounded px-3 py-2" rows="2"></textarea>
                                </div>

                                <!-- Other Skills Without Certificate -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Other Skills Without
                                        Certificate</label>
                                    <input v-model="referralForm.other_skills" type="text"
                                        class="w-full border rounded px-3 py-2" />
                                </div>

                                <!-- Message -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Message</label>
                                    <textarea v-model="referralForm.message" class="w-full border rounded px-3 py-2"
                                        rows="3"></textarea>
                                </div>
                            </form>
                        </template>
                        <template #footer>
                            <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded"
                                @click="showReferralModal = false">
                                Cancel
                            </button>
                            <button type="submit" form="referralForm" class="bg-blue-500 text-white px-4 py-2 rounded">
                                Submit Referral
                            </button>
                        </template>
                    </Modal>

                    <!-- Only show if graduate, approved, and has not filled up the referral letter -->
                    <button v-if="page.props.roles.isGraduate && !userNotApproved.value && !hasFilledReferral"
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded" @click="showReferralModal = true">
                        Create Job Referral Letter
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
