<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue'
import { usePage } from '@inertiajs/vue3';

const loading = ref(false)
const analyticsData = ref({})
const page = usePage();
if (page?.props?.query?.reportType) {
    reportType.value = page.props.query.reportType;
}
const reportType = ref('job-seeker-demographics') // 'demographics' or 'alignment' or 'employer'

const reportTypes = [
    { key: 'demographics', label: 'Gender & Diversity Metrics' },
    { key: 'job-seeker-demographics', label: 'Job Seeker Demographics' },
]

function fetchDiversityData() {
    loading.value = true
    axios.get(route('peso.reports.diversity.data'))
        .then(res => {
            analyticsData.value = res.data
            console.log('Diversity Analytics:', res.data)
        })
        .finally(() => {
            loading.value = false
        })
}

onMounted(fetchDiversityData)

// Stacked Column: Employment by Gender
const genderStackedOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Employed', 'Unemployed'] },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.employmentByGender ?? []).map(d => d.gender)
    },
    yAxis: { type: 'value', name: 'Count' },
    series: [
        {
            name: 'Employed',
            type: 'bar',
            stack: 'employment',
            data: (analyticsData.value.employmentByGender ?? []).map(d => d.employed),
            itemStyle: { color: '#34d399' }
        },
        {
            name: 'Unemployed',
            type: 'bar',
            stack: 'employment',
            data: (analyticsData.value.employmentByGender ?? []).map(d => (d.total - d.employed)),
            itemStyle: { color: '#f87171' }
        }
    ]
}))

// Pie Chart: Distribution of Diverse Groups in Industries (first industry as example)
const diversityPieOption = computed(() => {
    const pieData = []
    const pieSource = (analyticsData.value.diversityIndustryPie ?? [])[0]
    if (pieSource && pieSource.groups) {
        for (const [ethnicity, count] of Object.entries(pieSource.groups)) {
            pieData.push({ name: ethnicity, value: count })
        }
    }
    return {
        tooltip: { trigger: 'item' },
        legend: { orient: 'vertical', left: 'left' },
        series: [{
            name: 'Ethnicity',
            type: 'pie',
            radius: '60%',
            data: pieData,
            emphasis: {
                itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.2)' }
            }
        }]
    }
})

// Clustered Bar Chart: Job Seekers by Age, Education, Experience
const clusteredBarOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Age', 'Education', 'Experience'] },
    xAxis: {
        type: 'category',
        data: Object.keys(analyticsData.value.clusteredBarData?.age ?? {})
    },
    yAxis: { type: 'value', name: 'Count' },
    series: [
        {
            name: 'Age',
            type: 'bar',
            data: Object.values(analyticsData.value.clusteredBarData?.age ?? {}),
            itemStyle: { color: '#60a5fa' }
        },
        {
            name: 'Education',
            type: 'bar',
            data: Object.values(analyticsData.value.clusteredBarData?.education ?? {}),
            itemStyle: { color: '#34d399' }
        },
        {
            name: 'Experience',
            type: 'bar',
            data: Object.values(analyticsData.value.clusteredBarData?.experience ?? {}),
            itemStyle: { color: '#f59e42' }
        }
    ]
}));

// Line Chart: Job Applications Trends by Demographic Group
const lineDemographicOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: Array.from(new Set((analyticsData.value.lineDemographicTrends ?? []).map(d => d.group))) },
    xAxis: {
        type: 'category',
        data: Array.from(new Set((analyticsData.value.lineDemographicTrends ?? []).map(d => d.month)))
    },
    yAxis: { type: 'value', name: 'Applications' },
    series: (analyticsData.value.lineDemographicTrends ?? []).reduce((acc, d) => {
        let s = acc.find(x => x.name === d.group);
        if (!s) {
            s = { name: d.group, type: 'line', data: [] };
            acc.push(s);
        }
        s.data.push(d.count);
        return acc;
    }, [])
}));



// Bar Chart: Referred Candidates by Demographics (Age as example)
const barReferralAgeOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: Object.keys(analyticsData.value.barReferralDemographics?.age ?? {})
    },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(analyticsData.value.barReferralDemographics?.age ?? {}),
        itemStyle: { color: '#f87171' }
    }]
}));

// Gender Bar Chart Option
const barReferralGenderOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: Object.keys(analyticsData.value.barReferralDemographics?.gender ?? {})
    },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(analyticsData.value.barReferralDemographics?.gender ?? {}),
        itemStyle: { color: '#60a5fa' }
    }]
}));

// Education Bar Chart Option
const barReferralEducationOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: Object.keys(analyticsData.value.barReferralDemographics?.education ?? {})
    },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(analyticsData.value.barReferralDemographics?.education ?? {}),
        itemStyle: { color: '#34d399' }
    }]
}));

// Radar Chart: Demographic Attributes vs Referral Success
const radarDemographicOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarDemographicLabels ?? []).map(label => ({
            name: label, max: 100
        })),
        radius: 90
    },
    series: [{
        type: 'radar',
        data: [{
            value: analyticsData.value.radarDemographicData ?? [],
            name: 'Referral Success Rate (%)'
        }],
        areaStyle: { opacity: 0.1 }
    }]
}));


// Gender Radar Chart Option
const radarGenderOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarGenderLabels ?? []).map(label => ({
            name: label, max: 100
        })),
        radius: 90
    },
    series: [{
        type: 'radar',
        data: [{
            value: analyticsData.value.radarGenderData ?? [],
            name: 'Referral Success Rate (%)'
        }],
        areaStyle: { opacity: 0.1 }
    }]
}));

// Education Radar Chart Option
const radarEducationOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarEducationLabels ?? []).map(label => ({
            name: label, max: 100
        })),
        radius: 90
    },
    series: [{
        type: 'radar',
        data: [{
            value: analyticsData.value.radarEducationData ?? [],
            name: 'Referral Success Rate (%)'
        }],
        areaStyle: { opacity: 0.1 }
    }]
}));

// Demographics Summary (Gender & Diversity)
const demographicsSummary = computed(() => {
    const gender = analyticsData.value.employmentByGender ?? [];
    const pie = (analyticsData.value.diversityIndustryPie ?? [])[0];
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Gender employment
    if (gender.length) {
        const employed = gender.reduce((sum, g) => sum + (g.employed ?? 0), 0);
        const total = gender.reduce((sum, g) => sum + (g.total ?? 0), 0);
        summary += `<li><strong>${employed}</strong> out of <strong>${total}</strong> are employed.</li>`;
        gender.forEach(g => {
            summary += `<li>${g.gender}: <strong>${g.employed}</strong> employed, <strong>${g.total - g.employed}</strong> unemployed</li>`;
        });
    }

    // Diversity groups in industry
    if (pie && pie.groups) {
        const topGroup = Object.entries(pie.groups).sort((a, b) => b[1] - a[1])[0];
        if (topGroup) {
            summary += `<li>Largest diversity group: <strong>${topGroup[0]}</strong> (${topGroup[1]} members)</li>`;
        }
    }

    summary += "</ul>";
    return summary;
});

// Job Seeker Demographics Summary
const jobSeekerSummary = computed(() => {
    const clustered = analyticsData.value.clusteredBarData ?? {};
    const line = analyticsData.value.lineDemographicTrends ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Age
    if (clustered.age && Object.keys(clustered.age).length) {
        const topAge = Object.entries(clustered.age).sort((a, b) => b[1] - a[1])[0];
        summary += `<li>Most common age group: <strong>${topAge[0]}</strong> (${topAge[1]} seekers)</li>`;
    }
    // Education
    if (clustered.education && Object.keys(clustered.education).length) {
        const topEdu = Object.entries(clustered.education).sort((a, b) => b[1] - a[1])[0];
        summary += `<li>Most common education: <strong>${topEdu[0]}</strong> (${topEdu[1]} seekers)</li>`;
    }
    // Experience
    if (clustered.experience && Object.keys(clustered.experience).length) {
        const topExp = Object.entries(clustered.experience).sort((a, b) => b[1] - a[1])[0];
        summary += `<li>Most common experience: <strong>${topExp[0]}</strong> (${topExp[1]} seekers)</li>`;
    }
    // Application trends
    if (line.length) {
        const first = line[0]?.count ?? 0;
        const last = line[line.length - 1]?.count ?? 0;
        if (last > first) {
            summary += `<li>Applications increased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`;
        } else if (last < first) {
            summary += `<li>Applications decreased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`;
        } else {
            summary += `<li>Applications remained stable at <strong>${first}</strong>.</li>`;
        }
    }

    summary += "</ul>";
    return summary;
});







</script>

<template>
    <AppLayout title="Diversity and Demographics Report">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Diversity and Demographics Reports</h2>

            <!-- Report Type Filter -->
            <div class="flex justify-center mb-8">
                <div class="inline-flex rounded-lg shadow bg-gray-100 overflow-hidden">
                    <button v-for="type in reportTypes" :key="type.key" @click="reportType = type.key" :class="[
                        'px-6 py-2 text-sm font-semibold focus:outline-none transition',
                        reportType === type.key
                            ? 'bg-white text-blue-600 shadow'
                            : 'text-gray-500 hover:bg-gray-200'
                    ]">
                        {{ type.label }}
                    </button>
                </div>
            </div>

            <!-- Demographics Section -->
            <div v-if="reportType === 'demographics'">

                <div v-if="loading" class="flex justify-center items-center py-16">
                    <span class="loader mr-3"></span>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>

                <div v-else>

                    <!-- Stacked Column: Employment by Gender -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-blue-500"></i>
                            Employment by Gender <span class="font-bold text-blue-600">(Stacked Column)</span>
                        </h3>
                        <div v-if="loading" class="flex justify-center items-center py-16">
                            <span class="loader mr-3"></span>
                            <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                        </div>
                        <div v-else>
                            <VueECharts
                                v-if="analyticsData.employmentByGender && analyticsData.employmentByGender.length"
                                :option="genderStackedOption" style="height: 400px; width: 100%;" />
                            <div v-else class="text-gray-400 text-center py-12">No gender data available.</div>
                        </div>
                    </div>

                    <!-- Pie Chart: Diversity Groups in Industry (first industry as example) -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-green-500"></i>
                            Diversity Groups in Industry <span class="font-bold text-green-600">(Pie Chart)</span>
                        </h3>

                        <VueECharts
                            v-if="analyticsData.diversityIndustryPie && analyticsData.diversityIndustryPie.length"
                            :option="diversityPieOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No diversity data available.</div>
                    </div>

                    <!-- Analytics Summary Card for Demographics -->
                    <div class="mt-8 flex justify-center">
                        <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                            <div class="flex items-center px-6 pt-6 pb-2">
                                <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                                </svg>
                                <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                            </div>
                            <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                                <span v-html="demographicsSummary"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



                <div v-if="reportType === 'job-seeker-demographics'">
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>

                    <div v-else>

                        <!-- Clustered Bar Chart: Job Seekers by Age, Education, Experience -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-user-clock text-blue-500"></i>
                                Job Seekers by Age <span class="font-bold text-blue-600">(Bar Chart)</span>
                            </h3>
                            <VueECharts v-if="analyticsData.clusteredBarData" :option="{
                                tooltip: { trigger: 'axis' },
                                xAxis: { type: 'category', data: Object.keys(analyticsData.clusteredBarData?.age ?? {}) },
                                yAxis: { type: 'value', name: 'Count' },
                                series: [{
                                    name: 'Age',
                                    type: 'bar',
                                    data: Object.values(analyticsData.clusteredBarData?.age ?? {}),
                                    itemStyle: { color: '#60a5fa' }
                                }]
                            }" style="height: 300px; width: 100%;" />
                        </div>

                        <!-- Education Bar Chart -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-user-graduate text-green-500"></i>
                                Job Seekers by Education <span class="font-bold text-green-600">(Bar Chart)</span>
                            </h3>
                            <VueECharts v-if="analyticsData.clusteredBarData" :option="{
                                tooltip: { trigger: 'axis' },
                                xAxis: { type: 'category', data: Object.keys(analyticsData.clusteredBarData?.education ?? {}) },
                                yAxis: { type: 'value', name: 'Count' },
                                series: [{
                                    name: 'Education',
                                    type: 'bar',
                                    data: Object.values(analyticsData.clusteredBarData?.education ?? {}),
                                    itemStyle: { color: '#34d399' }
                                }]
                            }" style="height: 300px; width: 100%;" />
                        </div>

                        <!-- Experience Bar Chart -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-briefcase text-yellow-500"></i>
                                Job Seekers by Experience <span class="font-bold text-yellow-600">(Bar Chart)</span>
                            </h3>
                            <VueECharts v-if="analyticsData.clusteredBarData" :option="{
                                tooltip: { trigger: 'axis' },
                                xAxis: { type: 'category', data: Object.keys(analyticsData.clusteredBarData?.experience ?? {}) },
                                yAxis: { type: 'value', name: 'Count' },
                                series: [{
                                    name: 'Experience',
                                    type: 'bar',
                                    data: Object.values(analyticsData.clusteredBarData?.experience ?? {}),
                                    itemStyle: { color: '#f59e42' }
                                }]
                            }" style="height: 300px; width: 100%;" />
                        </div>

                        <!-- Line Chart: Job Applications Trends by Demographic Group -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-chart-line text-green-500"></i>
                                Job Applications Trends <span class="font-bold text-green-600">(Line Chart)</span>
                            </h3>
                            <div v-if="loading" class="flex justify-center items-center py-16">
                                <span class="loader mr-3"></span>
                                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                            </div>
                            <div v-else>
                                <VueECharts v-if="analyticsData.lineDemographicTrends" :option="lineDemographicOption"
                                    style="height: 400px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No trend data available.</div>
                            </div>
                        </div>

                        <!-- Bar Chart: Referred Candidates by Age -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-chart-bar text-yellow-500"></i>
                                Referred Candidates by Age <span class="font-bold text-yellow-600">(Bar Chart)</span>
                            </h3>
                            <div v-if="loading" class="flex justify-center items-center py-16">
                                <span class="loader mr-3"></span>
                                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                            </div>
                            <div v-else>
                                <VueECharts v-if="analyticsData.barReferralDemographics" :option="barReferralAgeOption"
                                    style="height: 400px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No referral demographic data
                                    available.
                                </div>
                            </div>
                        </div>
                        <!-- Bar Chart: Referred Candidates by Gender -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-chart-bar text-blue-500"></i>
                                Referred Candidates by Gender <span class="font-bold text-blue-600">(Bar Chart)</span>
                            </h3>
                            <div v-if="loading" class="flex justify-center items-center py-16">
                                <span class="loader mr-3"></span>
                                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                            </div>
                            <div v-else>
                                <VueECharts v-if="analyticsData.barReferralDemographics"
                                    :option="barReferralGenderOption" style="height: 400px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No referral gender data available.
                                </div>
                            </div>
                        </div>

                        <!-- Bar Chart: Referred Candidates by Education -->
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-chart-bar text-green-500"></i>
                                Referred Candidates by Education <span class="font-bold text-green-600">(Bar
                                    Chart)</span>
                            </h3>
                            <div v-if="loading" class="flex justify-center items-center py-16">
                                <span class="loader mr-3"></span>
                                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                            </div>
                            <div v-else>
                                <VueECharts v-if="analyticsData.barReferralDemographics"
                                    :option="barReferralEducationOption" style="height: 400px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No referral education data
                                    available.
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-braille text-purple-500"></i>
                                Demographic Attributes vs Referral Success <span
                                    class="font-bold text-purple-600">(Radar
                                    Charts)</span>
                            </h3>
                            <div v-if="loading" class="flex justify-center items-center py-16">
                                <span class="loader mr-3"></span>
                                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <!-- Age Radar Chart -->
                                <div>
                                    <div class="text-center font-semibold mb-2 text-gray-700">Age</div>
                                    <VueECharts v-if="analyticsData.radarDemographicData"
                                        :option="radarDemographicOption" style="height: 300px; width: 100%;" />
                                    <div v-else class="text-gray-400 text-center py-12">No age radar data available.
                                    </div>
                                </div>
                                <!-- Gender Radar Chart -->
                                <div>
                                    <div class="text-center font-semibold mb-2 text-gray-700">Gender</div>
                                    <VueECharts v-if="analyticsData.radarGenderData" :option="radarGenderOption"
                                        style="height: 300px; width: 100%;" />
                                    <div v-else class="text-gray-400 text-center py-12">No gender radar data available.
                                    </div>
                                </div>
                                <!-- Education Radar Chart -->
                                <div>
                                    <div class="text-center font-semibold mb-2 text-gray-700">Education</div>
                                    <VueECharts v-if="analyticsData.radarEducationData" :option="radarEducationOption"
                                        style="height: 300px; width: 100%;" />
                                    <div v-else class="text-gray-400 text-center py-12">No education radar data
                                        available.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Analytics Summary Card for Job Seeker Demographics -->
                        <div class="mt-8 flex justify-center">
                            <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                                <div class="flex items-center px-6 pt-6 pb-2">
                                    <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                                    </svg>
                                    <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                                </div>
                                <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                                    <span v-html="jobSeekerSummary"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        </Container>
    </AppLayout>
</template>