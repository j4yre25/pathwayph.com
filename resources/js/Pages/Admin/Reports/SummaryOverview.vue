<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const summaries = ref({
    employment: '',
    referral: '',
    skills: '',
    career: '',
    jobmarket: '',
    diversity: '',
})

async function fetchSummaries() {
    loading.value = true
    try {
        // Fetch each summary from its respective endpoint
        const [
            employmentRes,
            referralRes,
            skillsRes,
            careerRes,
            jobmarketRes,
            diversityRes
        ] = await Promise.all([
            axios.get(route('peso.reports.employment.data')),
            axios.get(route('peso.reports.referral.data')),
            axios.get(route('peso.reports.skills.data')),
            axios.get(route('peso.reports.careers.data')),
            axios.get(route('peso.reports.jobmarket.data')),
            axios.get(route('peso.reports.diversity.data')),
        ])

        // Extract and format summaries (customize as needed)
        summaries.value.employment = (() => {
            const grads = employmentRes.data.graduates ?? [];
            if (!grads.length) return 'No employment summary available.';

            const employed = grads.filter(g => g.employment_status === 'Employed').length;
            const underemployed = grads.filter(g => g.employment_status === 'Underemployed').length;
            const unemployed = grads.filter(g => g.employment_status === 'Unemployed').length;
            const total = grads.length;

            // School Years
            const yearsSet = new Set();
            grads.forEach(g => {
                if (g.school_year?.school_year_range) yearsSet.add(g.school_year.school_year_range);
            });
            const years = Array.from(yearsSet).sort().reverse();

            // Top Program
            const programCounts = {};
            grads.forEach(g => {
                if (g.program?.name) programCounts[g.program.name] = (programCounts[g.program.name] || 0) + 1;
            });
            const topProgram = Object.entries(programCounts).sort((a, b) => b[1] - a[1])[0];

            // Top Institution
            const institutionCounts = {};
            grads.forEach(g => {
                if (g.institution?.institution_name) institutionCounts[g.institution.institution_name] =
                    (institutionCounts[g.institution.institution_name] || 0) + 1;
            });
            const topInstitution = Object.entries(institutionCounts).sort((a, b) => b[1] - a[1])[0];

            return `
        <ul class='list-disc ml-6 mb-2'>
            <li>
                <strong>Summary:</strong> Out of <b>${total}</b> filtered graduates:
                <span class="text-green-700 font-semibold">${employed}</span> employed,
                <span class="text-yellow-700 font-semibold">${underemployed}</span> underemployed,
                <span class="text-red-700 font-semibold">${unemployed}</span> unemployed.
            </li>
            <li><strong>School Years:</strong> ${years.join(', ') || 'N/A'}</li>
            <li><strong>Top Program:</strong> ${topProgram ? `${topProgram[0]} (${topProgram[1]})` : 'N/A'}</li>
            <li><strong>Top Institution:</strong> ${topInstitution ? `${topInstitution[0]} (${topInstitution[1]})` : 'N/A'}</li>
        </ul>
    `;
        })();
        summaries.value.referral = referralRes.data.funnelData
            ? `Total Referrals: <b>${referralRes.data.totalReferrals ?? 0}</b><br>
               Hired: <b>${referralRes.data.funnelData?.find(f => (f.name ?? '').toLowerCase() === 'hired')?.value ?? 0}</b>`
            : 'No referral summary available.'
        summaries.value.skills = (() => {
            const jobRoles = skillsRes.data.jobRolesWordCloud ?? [];
            const skills = skillsRes.data.skillsWordCloud ?? [];
            const bar = skillsRes.data.barChartData ?? [];
            const topSkillDemand = Object.entries(skillsRes.data.topSkillDemand ?? {}).map(([skill, count]) => ({ skill, count }));
            const topSkillSupply = Object.entries(skillsRes.data.topSkillSupply ?? {}).map(([skill, count]) => ({ skill, count }));
            const heatmap = skillsRes.data.heatmap ?? [];
            const bubble = skillsRes.data.bubbleData ?? [];

            let html = "<ul class='list-disc ml-6 mb-2'>";

            // Most frequent job role
            if (jobRoles.length) {
                const topRole = Array.isArray(jobRoles)
                    ? jobRoles.reduce((a, b) => (b.count > a.count ? b : a))
                    : Object.entries(jobRoles).reduce((a, b) => (b[1] > a[1] ? { role: b[0], count: b[1] } : a), { role: '', count: 0 });
                html += `<li>Most frequent job role: <strong>${topRole.role}</strong> (${topRole.count} mentions)</li>`;
            }

            // Most frequent skill
            if (skills.length) {
                const topSkill = Array.isArray(skills)
                    ? skills.reduce((a, b) => (b.count > a.count ? b : a))
                    : Object.entries(skills).reduce((a, b) => (b[1] > a[1] ? { skill: b[0], count: b[1] } : a), { skill: '', count: 0 });
                html += `<li>Most frequent skill: <strong>${topSkill.skill}</strong> (${topSkill.count} mentions)</li>`;
            }

            // Skill with highest demand (bar chart)
            if (bar.length) {
                const topBar = bar.reduce((a, b) => (b.demand > a.demand ? b : a));
                html += `<li>Skill with highest demand: <strong>${topBar.skill}</strong> (${topBar.demand})</li>`;
            }

            // Top skill by demand
            if (topSkillDemand.length) {
                html += `<li>Top skill by demand: <strong>${topSkillDemand[0].skill}</strong> (${topSkillDemand[0].count})</li>`;
            }

            // Top skill by supply
            if (topSkillSupply.length) {
                html += `<li>Top skill by supply: <strong>${topSkillSupply[0].skill}</strong> (${topSkillSupply[0].count})</li>`;
            }

            // Largest skill gap (heatmap)
            if (heatmap.length) {
                const topGap = heatmap.reduce((a, b) => (b.demand > a.demand ? b : a));
                html += `<li>Largest skill gap: <strong>${topGap.skill}</strong> in <strong>${topGap.industry}</strong> (Demand: ${topGap.demand})</li>`;
            }

            // Highest demand-supply gap (bubble)
            if (bubble.length) {
                const top = bubble.reduce((a, b) => ((b.demand - b.supply) > (a.demand - a.supply) ? b : a));
                html += `<li>Highest demand-supply gap: <strong>${top.skill}</strong> (Demand: ${top.demand}, Supply: ${top.supply})</li>`;
            }

            html += "</ul>";
            return html || 'No skills summary available.';
        })();
        summaries.value.career = (() => {
            const bar = careerRes.data.barChartData ?? [];
            const skillPrograms = careerRes.data.programSkillStrength ?? [];
            const salaryRanges = careerRes.data.salaryRanges ?? [];
            const entrySalaries = careerRes.data.entryLevelSalaries ?? [];
            const seniorSalaries = careerRes.data.seniorLevelSalaries ?? [];
            const expectedSalaries = careerRes.data.expectedSalaries ?? [];
            const offeredSalaries = careerRes.data.offeredSalaries ?? [];

            let html = "<ul class='list-disc ml-6 mb-2'>";

            // Highest employment rate by education level
            if (bar.length) {
                const highest = bar.reduce((a, b) => (a.employment_rate > b.employment_rate ? a : b));
                html += `<li>Highest employment rate: <b>${highest.education}</b> (${highest.employment_rate}%)</li>`;
                const lowest = bar.reduce((a, b) => (a.employment_rate < b.employment_rate ? a : b));
                html += `<li>Lowest employment rate: <b>${lowest.education}</b> (${lowest.employment_rate}%)</li>`;
            }

            // Program with strongest skill
            if (skillPrograms.length) {
                const strongest = skillPrograms.reduce((a, b) => (a.strength > b.strength ? a : b));
                html += `<li>Program with strongest skill: <b>${strongest.program}</b> (${strongest.skill}: ${strongest.strength})</li>`;
            }

            // Highest and lowest salary range
            if (salaryRanges.length) {
                const highest = salaryRanges.reduce((a, b) => (a.max > b.max ? a : b));
                const lowest = salaryRanges.reduce((a, b) => (a.min < b.min ? a : b));
                html += `<li>Highest salary range: <b>₱${highest.min} - ₱${highest.max}</b> (${highest.program})</li>`;
                html += `<li>Lowest salary range: <b>₱${lowest.min} - ₱${lowest.max}</b> (${lowest.program})</li>`;
            }

            // Highest entry-level salary
            if (entrySalaries.length) {
                const highestEntry = entrySalaries.reduce((a, b) => (a.salary > b.salary ? a : b));
                html += `<li>Highest entry-level salary: <b>₱${highestEntry.salary}</b> (${highestEntry.program})</li>`;
            }

            // Highest senior-level salary
            if (seniorSalaries.length) {
                const highestSenior = seniorSalaries.reduce((a, b) => (a.salary > b.salary ? a : b));
                html += `<li>Highest senior-level salary: <b>₱${highestSenior.salary}</b> (${highestSenior.program})</li>`;
            }

            // Lowest expected salary
            if (expectedSalaries.length) {
                const lowestExpected = expectedSalaries.reduce((a, b) => (a.salary < b.salary ? a : b));
                html += `<li>Lowest expected salary: <b>₱${lowestExpected.salary}</b> (${lowestExpected.program})</li>`;
            }

            // Lowest offered salary
            if (offeredSalaries.length) {
                const lowestOffered = offeredSalaries.reduce((a, b) => (a.salary < b.salary ? a : b));
                html += `<li>Lowest offered salary: <b>₱${lowestOffered.salary}</b> (${lowestOffered.program})</li>`;
            }

            html += "</ul>";
            return html || 'No career summary available.';
        })();


        summaries.value.jobmarket = (() => {
            const rate = jobmarketRes.data.matchingSuccessRate ?? 0;
            const funnel = jobmarketRes.data.funnelData ?? [];
            const pie = jobmarketRes.data.pieMatchData ?? [];
            const bar = jobmarketRes.data.barAlignment ?? {};
            const lineData = jobmarketRes.data.lineTrendData ?? [];
            const industrySeries = jobmarketRes.data.industryAreaChart ?? [];
            const skillSeries = jobmarketRes.data.skillAreaChart ?? [];
            const barQual = jobmarketRes.data.barQualificationData ?? [];
            const radarLabels = jobmarketRes.data.radarPriorityLabels ?? [];
            const radarData = jobmarketRes.data.radarPriorityData ?? [];

            let html = "<ul class='list-disc ml-6 mb-2'>";

            // Matching Success Rate
            html += `<li>Matching Success Rate: <strong>${rate}%</strong></li>`;

            // Bar Alignment: Meet, Exceed, Fall Short
            const meets = bar.Meets ?? 0;
            const exceeds = bar.Exceeds ?? 0;
            const fallsShort = bar['Falls Short'] ?? 0;
            const totalAlign = meets + exceeds + fallsShort;
            if (totalAlign > 0) {
                html += `<li>Applicants who meet requirements: <strong>${meets}</strong></li>`;
                html += `<li>Applicants who exceed requirements: <strong>${exceeds}</strong></li>`;
                html += `<li>Applicants who fall short: <strong>${fallsShort}</strong></li>`;
                // Most applicants' alignment
                if (exceeds > meets && exceeds > fallsShort) {
                    html += `<li>Most applicants <span class="font-semibold text-purple-600">exceed</span> job requirements.</li>`;
                } else if (fallsShort > meets && fallsShort > exceeds) {
                    html += `<li>Most applicants <span class="font-semibold text-red-600">fall short</span> of requirements.</li>`;
                } else if (meets > exceeds && meets > fallsShort) {
                    html += `<li>Most applicants <span class="font-semibold text-green-600">meet</span> job requirements.</li>`;
                }
            }

            // Funnel chart: hired
            if (funnel.length) {
                const hired = funnel.find(f => f.stage?.toLowerCase() === 'hired')?.count ?? 0;
                html += `<li>Applicants hired: <strong>${hired}</strong></li>`;
            }

            // Pie chart: matched vs unmatched
            if (pie.length) {
                const matched = pie.find(p => p.name === 'Matched')?.value ?? 0;
                const unmatched = pie.find(p => p.name === 'Unmatched')?.value ?? 0;
                html += `<li>Matched applicants: <strong>${matched}</strong></li>`;
                html += `<li>Unmatched applicants: <strong>${unmatched}</strong></li>`;
            }

            // Stability of job openings
            if (lineData.length) {
                const first = lineData[0]?.openings ?? 0;
                const last = lineData[lineData.length - 1]?.openings ?? 0;
                if (last > first) {
                    html += `<li>Job openings increased from <strong>${first}</strong> to <strong>${last}</strong>.</li>`;
                } else if (last < first) {
                    html += `<li>Job openings decreased from <strong>${first}</strong> to <strong>${last}</strong>.</li>`;
                } else {
                    html += `<li>Job openings remained stable at <strong>${first}</strong>.</li>`;
                }
            }

            // Highest demand industry
            if (industrySeries.length) {
                const totals = industrySeries.map(s => ({
                    name: s.name,
                    total: (s.data ?? []).reduce((a, b) => a + b, 0)
                }));
                const topIndustry = totals.sort((a, b) => b.total - a.total)[0];
                if (topIndustry && topIndustry.total > 0) {
                    html += `<li>Highest demand industry: <strong>${topIndustry.name}</strong> (${topIndustry.total} openings)</li>`;
                }
            }

            // Most in-demand skill
            if (skillSeries.length) {
                const totals = skillSeries.map(s => ({
                    name: s.name,
                    total: (s.data ?? []).reduce((a, b) => a + b, 0)
                }));
                const topSkill = totals.sort((a, b) => b.total - a.total)[0];
                if (topSkill && topSkill.total > 0) {
                    html += `<li>Most in-demand skill: <strong>${topSkill.name}</strong> (${topSkill.total} openings)</li>`;
                }
            }

            // Most requested qualifications
            if (barQual.length) {
                html += `<li>Most requested qualification: <strong>${barQual[0].qualification}</strong> (${barQual[0].count} mentions)</li>`;
                if (barQual.length > 1) {
                    html += `<li>Other in-demand qualifications: <strong>${barQual.slice(1, 4).map(q => q.qualification).join(', ')}</strong></li>`;
                }
            }

            // Top employer priority
            if (radarLabels.length && radarData.length) {
                const maxIdx = radarData.indexOf(Math.max(...radarData));
                html += `<li>Top employer priority: <strong>${radarLabels[maxIdx]}</strong></li>`;
            }

            html += "</ul>";
            return html || 'No job market summary available.';
        })();


        summaries.value.diversity = (() => {
            const gender = diversityRes.data.employmentByGender ?? [];
            const pie = (diversityRes.data.diversityIndustryPie ?? [])[0];
            const clustered = diversityRes.data.clusteredBarData ?? {};

            let html = "<ul class='list-disc ml-6 mb-2'>";

            // Employed and Unemployed per gender
            if (gender.length) {
                gender.forEach(g => {
                    html += `<li><b>${g.gender}:</b> Employed: <b>${g.employed ?? 0}</b>, Unemployed: <b>${(g.total ?? 0) - (g.employed ?? 0)}</b></li>`;
                });
            }

            // Diversity group counts
            if (pie && pie.groups) {
                Object.entries(pie.groups).forEach(([group, count]) => {
                    html += `<li><b>${group}:</b> ${count} in industry</li>`;
                });
            }

            // Most common jobseekers by age, education, experience
            if (clustered.age && Object.keys(clustered.age).length) {
                const topAge = Object.entries(clustered.age).sort((a, b) => b[1] - a[1])[0];
                html += `<li>Most common age group: <b>${topAge[0]}</b> (${topAge[1]} seekers)</li>`;
            }
            if (clustered.education && Object.keys(clustered.education).length) {
                const topEdu = Object.entries(clustered.education).sort((a, b) => b[1] - a[1])[0];
                html += `<li>Most common education: <b>${topEdu[0]}</b> (${topEdu[1]} seekers)</li>`;
            }
            if (clustered.experience && Object.keys(clustered.experience).length) {
                const topExp = Object.entries(clustered.experience).sort((a, b) => b[1] - a[1])[0];
                html += `<li>Most common experience: <b>${topExp[0]}</b> (${topExp[1]} seekers)</li>`;
            }

            html += "</ul>";
            return html || 'No diversity summary available.';
        })();
    } catch (e) {
        summaries.value = {
            employment: 'Error loading summary.',
            referral: 'Error loading summary.',
            skills: 'Error loading summary.',
            career: 'Error loading summary.',
            jobmarket: 'Error loading summary.',
            diversity: 'Error loading summary.',
        }
    } finally {
        loading.value = false
    }
}

onMounted(fetchSummaries)
</script>

<template>
    <AppLayout title="Analytics Summary Overview">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-8 text-gray-800">Analytics Summary Overview</h2>
            <div v-if="loading" class="flex justify-center items-center py-16">
                <span class="loader mr-3"></span>
                <span class="text-blue-500 text-lg font-semibold">Loading analytics summaries...</span>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Employment Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <i class="fas fa-briefcase text-blue-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-blue-700">Employment Overview</h3>
                            <p class="text-xs text-gray-500">Graduate employment status and top programs</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.employment"></div>
                    </div>
                </div>
                <!-- Referral Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                        <i class="fas fa-share-alt text-green-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-green-700">Referral Overview</h3>
                            <p class="text-xs text-gray-500">Referral funnel and hiring outcomes</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.referral"></div>
                    </div>
                </div>
                <!-- Skills Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-yellow-50 to-white">
                        <i class="fas fa-tools text-yellow-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-yellow-700">Skills Overview</h3>
                            <p class="text-xs text-gray-500">Top skills, gaps, and demand/supply</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.skills"></div>
                    </div>
                </div>
                <!-- Career Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                        <i class="fas fa-graduation-cap text-purple-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-purple-700">Career Overview</h3>
                            <p class="text-xs text-gray-500">Education, salary, and program highlights</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.career"></div>
                    </div>
                </div>
                <!-- Job Market Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-cyan-50 to-white">
                        <i class="fas fa-chart-line text-cyan-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-cyan-700">Job Market Overview</h3>
                            <p class="text-xs text-gray-500">Matching, demand, and employer priorities</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.jobmarket"></div>
                    </div>
                </div>
                <!-- Diversity Overview -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-0">
                    <div class="flex items-center px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-pink-50 to-white">
                        <i class="fas fa-users text-pink-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-bold text-pink-700">Diversity Overview</h3>
                            <p class="text-xs text-gray-500">Gender, age, education, and experience</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-html="summaries.diversity"></div>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>

<style scoped>
.bg-gradient-to-r {
    /* subtle background for headers */
    background: linear-gradient(90deg, var(--tw-gradient-stops));
}
.card-badge {
    @apply inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 ml-2;
}
ul.list-disc {
    margin: 0;
    padding-left: 1.25rem;
}
ul.list-disc li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}
</style>