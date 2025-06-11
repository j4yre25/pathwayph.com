<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref, watch, computed } from "vue";

const props = defineProps({
    graduate: Object,
    skills: Array,
    experiences: Array,
    education: Array,
    projects: Array,
    achievements: Array,
    certifications: Array,
    testimonials: Array,
    resume: Object,
    careerGoals: Object,
    employmentPreferences: Object,
});

const fullName = computed(() =>
    [props.graduate_first_name, props.graduate_last_name]
        .filter(Boolean)
        .join(' ')
);

console.log('Graduate Name:', fullName);

console.log('Name:', props.graduate.first_name);

const localAboutMe = ref(props.graduate.about_me || '');

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    if (isNaN(date)) return dateStr; // fallback if not a valid date
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatUrl(url, base) {
    if (!url) return null;
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    if (url.startsWith('www.')) return 'https://' + url;
    if (base && !url.includes(base)) {
        return `https://${base}/${url.replace(/^\/+/, '')}`;
    }
    return 'https://' + url;
}

watch(() => props.graduate.graduate_about_me, (newVal) => {
    localAboutMe.value = newVal || '';
});

const showAllProjects = ref(false);
const showAllAchievements = ref(false);
const showAllCertifications = ref(false);

const PROJECTS_LIMIT = 3;
const ACHIEVEMENTS_LIMIT = 3;
const CERTIFICATIONS_LIMIT = 3;

function fileIcon(url) {
    if (!url) return "fas fa-paperclip";
    if (url.endsWith('.pdf')) return "fas fa-file-pdf text-red-500";
    if (url.match(/\.(jpg|jpeg|png|gif)$/i)) return "fas fa-image text-green-500";
    return "fas fa-paperclip";
}

const latestEducation = computed(() => {
    if (!props.education || props.education.length === 0) return null;
    // Prefer current education
    const current = props.education.find(e => e.is_current);
    if (current) return current;
    // Otherwise, get the one with the latest end_date
    return [...props.education]
        .filter(e => e.end_date)
        .sort((a, b) => new Date(b.end_date) - new Date(a.end_date))[0] || props.education[0];
});

const education = computed(() => props.education || []);

console.log('Education:', props.education);
</script>

<template>
    <AppLayout title="Graduate Profile">
        <!-- Cover Photo -->
        <div class="relative h-48 bg-gray-800">
            <img :src="graduate.cover_photo_path || '/images/default-cover.jpg'" alt="Cover Photo"
                class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
        </div>

        <!-- Changed -mt-20 to -mt-8 for less overlap -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 md:-mt-12">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar -->
                <aside class="md:w-1/3 w-full space-y-6 relative">
                    <div class="relative bg-white rounded-lg shadow-lg p-6 flex flex-col items-center pt-20">
                        <!-- Profile Picture: absolute, overlaps cover photo -->
                        <div class="absolute left-1/2 -top-16 transform -translate-x-1/2 z-20">
                            <img :src="graduate.graduate_picture ? `/storage/${graduate.graduate_picture}` : '/images/default-avatar.png'"
                                alt="Graduate Picture"
                                class="w-32 h-32 rounded-full object-cover border-4 border-white shadow" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 text-center mt-2">{{ graduate.first_name }} {{
                            graduate.last_name }}</h2>
                        <p class="text-gray-600 text-center mt-1">{{ graduate.current_job_title || 'No professional title' }}</p>
                        <div class="flex justify-center space-x-4 mt-3">
                            <a v-if="graduate.linkedin_url" :href="formatUrl(graduate.linkedin_url, 'linkedin.com')"
                                target="_blank" class="text-gray-500 hover:text-indigo-600 text-2xl"><i
                                    class="fab fa-linkedin"></i></a>
                            <a v-if="graduate.github_url" :href="formatUrl(graduate.github_url, 'github.com')"
                                target="_blank" class="text-gray-500 hover:text-indigo-600 text-2xl"><i
                                    class="fab fa-github"></i></a>
                            <a v-if="graduate.personal_website" :href="formatUrl(graduate.personal_website)"
                                target="_blank" class="text-gray-500 hover:text-indigo-600 text-2xl"><i
                                    class="fas fa-globe"></i></a>
                        </div>
                        <div class="mt-6 w-full">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-envelope text-indigo-500 mr-2"></i>
                                <span class="text-gray-700">{{ graduate.user?.email || 'N/A' }}</span>
                            </div>
                            <div class="flex items-center mb-2">
                                <i class="fas fa-phone text-indigo-500 mr-2"></i>
                                <span class="text-gray-700">{{ '0' + graduate.contact_number || 'N/A' }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
                                <span class="text-gray-700">{{ graduate.address || graduate.location || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Skills</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="skill in skills" :key="skill.id"
                                class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm">
                                {{ skill.skill_name }}
                            </span>
                        </div>
                    </div>

                    <!-- Resume -->
                    <div v-if="resume && resume.file_url" class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Resume</h4>
                        <div class="flex items-center gap-4 mb-4">
                            <i v-if="resume.file_type && resume.file_type.includes('pdf')"
                                class="fas fa-file-pdf text-3xl text-red-500"></i>
                            <i v-else-if="resume.file_type && resume.file_type.includes('word')"
                                class="fas fa-file-word text-3xl text-blue-500"></i>
                            <i v-else class="fas fa-file-alt text-3xl text-gray-500"></i>
                            <span class="font-semibold">{{ resume.file_name }}</span>
                            <a :href="resume.file_url" download target="_blank"
                                class="inline-flex items-center text-indigo-600 hover:underline font-semibold ml-4">
                                <i class="fas fa-file-download mr-2"></i>
                                Download
                            </a>
                        </div>
                        <!-- PDF Preview -->
                        <div v-if="resume.file_type && resume.file_type.includes('pdf')"
                            class="border rounded mt-4 overflow-hidden">
                            <iframe :src="resume.file_url" width="100%" height="400px"></iframe>
                        </div>
                    </div>
                    <div v-else class="bg-white rounded-lg shadow-lg p-6 text-gray-600">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Resume</h4>
                        No resume uploaded.
                    </div>
                </aside>

                <!-- Main Content -->
                <!-- Added pt-8 for extra top padding so About Me is never covered -->
                <main class="md:w-2/3 w-full space-y-8 pt-8">
                    <!-- About Me -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">About Me</h4>
                        <p class="text-gray-600">{{ localAboutMe || 'No description available.' }}</p>
                    </section>

                    <!-- Education -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Education</h4>
                        <ul class="space-y-4">
                            <li v-for="edu in education.filter(e => !latestEducation || e.id !== latestEducation.id)" :key="edu.id"
                                class="flex flex-col md:flex-row md:items-center md:justify-between bg-gray-50 rounded-lg p-4 shadow-sm">
                                <div>
                                    <div class="text-lg font-semibold text-indigo-700">{{ edu.program }}</div>
                                    <div class="text-gray-600">{{ edu.education }}</div>
                                </div>
                                <div class="mt-2 md:mt-0 text-sm text-gray-500 md:text-right">
                                    <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                        {{ edu.start_date ? formatDate(edu.start_date.substring(0, 10)) : 'N/A' }}
                                        -
                                        {{ edu.end_date ? formatDate(edu.end_date.substring(0, 10)) : 'N/A' }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Experience -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Experience</h4>
                        <ul class="space-y-4">
                            <li v-for="exp in experiences" :key="exp.id"
                                class="flex flex-col md:flex-row md:items-center md:justify-between bg-gray-50 rounded-lg p-4 shadow-sm">
                                <div>
                                    <div class="text-lg font-semibold text-indigo-700">{{ exp.title }}</div>
                                    <div class="text-gray-600">{{ exp.company }}</div>
                                    <div class="text-gray-600 text-sm mt-2">{{ exp.description }}</div>
                                </div>
                                <div class="mt-2 md:mt-0 text-sm text-gray-500 md:text-right">
                                    <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                        {{ formatDate(exp.start_date) ? formatDate(exp.start_date) : 'N/A' }}
                                        -
                                        {{ exp.is_current ? 'Present' : (formatDate(exp.end_date) ?
                                            formatDate(exp.end_date) : 'N/A') }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Projects -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Projects</h4>
                        <ul class="space-y-4">
                            <li v-for="proj in (showAllProjects ? projects : projects.slice(0, PROJECTS_LIMIT))"
                                :key="proj.id" class="bg-gray-50 rounded-lg p-4 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-lg font-semibold text-indigo-700">{{ proj.title }}</div>
                                        <div class="text-gray-600 text-sm mt-1">{{ proj.description }}</div>
                                        <div v-if="proj.file" class="mt-2">
                                            <a :href="proj.file ? `/storage/${proj.file}` : ''" target="_blank"
                                                class="text-indigo-600 hover:underline flex items-center gap-1">
                                                <i :class="fileIcon(proj.file)"></i>
                                                View Attachment
                                            </a>
                                        </div>
                                    </div>
                                    <div v-if="proj.url" class="mt-2 md:mt-0">
                                        <a :href="proj.url" target="_blank"
                                            class="text-indigo-600 hover:underline break-all">
                                            {{ proj.url }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div v-if="projects.length > PROJECTS_LIMIT" class="mt-4 text-center">
                            <button @click="showAllProjects = !showAllProjects"
                                class="text-indigo-600 hover:underline font-semibold">
                                {{ showAllProjects ? 'Show Less' : 'See More' }}
                            </button>
                        </div>
                    </section>

                    <!-- Achievements -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Achievements</h4>
                        <ul class="space-y-4">
                            <li v-for="ach in (showAllAchievements ? achievements : achievements.slice(0, ACHIEVEMENTS_LIMIT))"
                                :key="ach.id" class="bg-gray-50 rounded-lg p-4 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-lg font-semibold text-indigo-700">{{ ach.title }}</div>
                                        <div class="text-gray-600 text-sm mt-1">{{ ach.description }}</div>
                                        <div v-if="ach.credential_picture" class="mt-2">
                                            <a :href="ach.credential_picture ? `/storage/${ach.credential_picture}` : ''"
                                                target="_blank"
                                                class="text-indigo-600 hover:underline flex items-center gap-1">
                                                <i :class="fileIcon(ach.credential_picture)"></i>
                                                View Attachment
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-2 md:mt-0 text-sm text-gray-500 md:text-right">
                                        <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                            {{ ach.issuer }} ({{ formatDate(ach.date) }})
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div v-if="achievements.length > ACHIEVEMENTS_LIMIT" class="mt-4 text-center">
                            <button @click="showAllAchievements = !showAllAchievements"
                                class="text-indigo-600 hover:underline font-semibold">
                                {{ showAllAchievements ? 'Show Less' : 'See More' }}
                            </button>
                        </div>
                    </section>

                    <!-- Certifications -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Certifications</h4>
                        <ul class="space-y-4">
                            <li v-for="cert in (showAllCertifications ? certifications : certifications.slice(0, CERTIFICATIONS_LIMIT))"
                                :key="cert.id"
                                class="bg-gray-50 rounded-lg p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-indigo-700">{{ cert.name }}</div>
                                    <div class="text-gray-600 text-sm mt-1">{{ cert.issuer }}</div>
                                    <div v-if="cert.file_path" class="mt-2">
                                        <a :href="cert.file_path ? `/storage/${cert.file_path}` : ''" target="_blank"
                                            class="text-indigo-600 hover:underline flex items-center gap-1">
                                            <i :class="fileIcon(cert.file_path)"></i>
                                            View Attachment
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-2 md:mt-0 text-sm text-gray-500 md:text-right">
                                    <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                        {{ formatDate(cert.issue_date) }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <div v-if="certifications.length > CERTIFICATIONS_LIMIT" class="mt-4 text-center">
                            <button @click="showAllCertifications = !showAllCertifications"
                                class="text-indigo-600 hover:underline font-semibold">
                                {{ showAllCertifications ? 'Show Less' : 'See More' }}
                            </button>
                        </div>
                    </section>

                    <!-- Testimonials -->
                    <section class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Testimonials</h4>
                        <ul class="space-y-4">
                            <li v-for="test in testimonials" :key="test.id" class="bg-gray-50 rounded-lg p-4 shadow-sm">
                                <div class="flex flex-col">
                                    <div class="font-semibold text-indigo-700">{{ test.author }}</div>
                                    <div class="text-gray-500 text-sm mb-2">{{ test.position }}</div>
                                    <span class="italic text-gray-700">"{{ test.content }}"</span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Employment Preferences -->
                    <section v-if="employmentPreferences" class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Employment Preferences</h4>
                        <div class="space-y-2">
                            <p><strong>Job Types:</strong> {{ employmentPreferences.job_types }}</p>
                            <p><strong>Salary Expectations:</strong>
                                <span v-if="employmentPreferences.salary_expectations">
                                    {{
                                        (() => {
                                            try {
                                                const s = JSON.parse(employmentPreferences.salary_expectations);
                                                return (s.range ? s.range : '') + (s.frequency ? ' ' + s.frequency : '');
                                            } catch { return employmentPreferences.salary_expectations }
                                        })()
                                    }}
                                </span>
                            </p>
                            <p><strong>Preferred Locations:</strong> {{ employmentPreferences.preferred_locations }}</p>
                            <p><strong>Work Environment:</strong> {{ employmentPreferences.work_environment }}</p>
                            <p><strong>Additional Notes:</strong> {{ employmentPreferences.additional_notes }}</p>
                        </div>
                    </section>

                    <!-- Career Goals -->
                    <section v-if="careerGoals" class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Career Goals</h4>
                        <div class="space-y-2">
                            <p><strong>Short-term Goals:</strong> {{ careerGoals.short_term_goals || 'N/A' }}</p>
                            <p><strong>Long-term Goals:</strong> {{ careerGoals.long_term_goals || 'N/A' }}</p>
                            <p><strong>Industries of Interest:</strong>
                                <span v-if="careerGoals.industries_of_interest">
                                    {{ careerGoals.industries_of_interest.split(',').join(', ') }}
                                </span>
                                <span v-else>N/A</span>
                            </p>
                            <p><strong>Career Path:</strong> {{ careerGoals.career_path || 'N/A' }}</p>
                            <p><strong>Additional Notes:</strong> {{ careerGoals.additional_notes || 'N/A' }}</p>
                        </div>
                    </section>

                    <!-- Latest Education Section - Show at the top -->
                    <section v-if="latestEducation" class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ latestEducation.is_current ? 'Current School' : 'Latest School Graduated From' }}
                        </h4>
                        <div class="text-lg font-bold text-indigo-700">{{ latestEducation.education }}</div>
                        <div class="text-gray-600">{{ latestEducation.program }}<span v-if="latestEducation.field_of_study"> in {{ latestEducation.field_of_study }}</span></div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ latestEducation.start_date ? formatDate(latestEducation.start_date) : '' }}
                            -
                            {{ latestEducation.is_current ? 'Present' : (latestEducation.end_date ? formatDate(latestEducation.end_date) : '') }}
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </AppLayout>
</template>