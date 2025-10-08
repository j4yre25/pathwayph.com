<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue'
import { usePage } from '@inertiajs/vue3'
import MultiSelect from '@/Components/MultiSelect.vue'

const loading = ref(false)
const analyticsData = ref({})
const page = usePage()
const reportType = ref(page?.props?.query?.reportType || 'job-seeker-demographics')

const dateFrom = ref('')
const dateTo = ref('')
const industries = ref(page.props.industries ?? [])
const genders = ref(['Male', 'Female', 'Other'])
const reportTypes = [
    { key: 'demographics', label: 'Gender & Diversity Metrics' },
    { key: 'job-seeker-demographics', label: 'Job Seeker Demographics' },
]
const ageGroups = ref(['18-24', '25-34', '35-44', '45-54', '55+'])
const educationLevels = ref(page.props.educationLevels ?? [])
const experienceLevels = ref(['None', '1-2 years', '3-5 years', '6-10 years', '10+ years'])

const selectedIndustries = ref([])
const selectedGenders = ref([])
const selectedAges = ref([])
const selectedEducations = ref([])
const selectedExperiences = ref([])
const selectedJobSeekerGenders = ref([])
const selectedReferralAges = ref([])
const selectedReferralGenders = ref([])
const selectedReferralEducations = ref([])

function fetchDiversityData() {
    loading.value = true
    axios.get(route('peso.reports.diversity.data'))
        .then(res => {
            analyticsData.value = res.data
        })
        .finally(() => {
            loading.value = false
        })
}
onMounted(fetchDiversityData)

// Defensive filter helpers
function filterObjectByKeys(obj, keys) {
    if (!keys.length) return obj
    const filtered = Object.fromEntries(Object.entries(obj).filter(([k]) => keys.includes(k)))
    // If filter results in empty, return zeroed object for selected keys
    return Object.keys(filtered).length ? filtered : Object.fromEntries(keys.map(k => [k, 0]))
}
function filterArrayByField(arr, field, selected) {
    if (!selected.length) return arr
    const selectedVals = selected.map(s => s.name || s.level_of_education || s)
    const filtered = arr.filter(item => selectedVals.includes(item[field]))
    return filtered.length ? filtered : arr
}

// Computed filters
const filteredEmploymentByGender = computed(() =>
    filterArrayByField(analyticsData.value.employmentByGender ?? [], 'gender', selectedGenders.value)
)
const filteredDiversityIndustryPie = computed(() =>
    filterArrayByField(analyticsData.value.diversityIndustryPie ?? [], 'industry', selectedIndustries.value)
)
const filteredJobSeekersByAge = computed(() =>
    filterObjectByKeys(analyticsData.value.clusteredBarData?.age ?? {}, selectedAges.value.map(a => a.name))
)
const filteredJobSeekersByEducation = computed(() =>
    filterObjectByKeys(analyticsData.value.clusteredBarData?.education ?? {}, selectedEducations.value.map(e => e.level_of_education))
)
const filteredJobSeekersByExperience = computed(() =>
    filterObjectByKeys(analyticsData.value.clusteredBarData?.experience ?? {}, selectedExperiences.value.map(e => e.name))
)
const filteredReferralAge = computed(() =>
    filterObjectByKeys(analyticsData.value.barReferralDemographics?.age ?? {}, selectedReferralAges.value.map(a => a.name))
)
const filteredReferralGender = computed(() =>
    filterObjectByKeys(analyticsData.value.barReferralDemographics?.gender ?? {}, selectedReferralGenders.value.map(g => g.name))
)
const filteredReferralEducation = computed(() =>
    filterObjectByKeys(analyticsData.value.barReferralDemographics?.education ?? {}, selectedReferralEducations.value.map(e => e.level_of_education))
)
const filteredLineDemographicTrends = computed(() => {
    let data = analyticsData.value.lineDemographicTrends ?? []
    if (selectedJobSeekerGenders.value.length) {
        const selected = selectedJobSeekerGenders.value.map(g => g.name)
        data = data.filter(d => selected.includes(d.group))
        return data.length ? data : analyticsData.value.lineDemographicTrends ?? []
    }
    return data
})

// Chart options (unchanged, but always use filtered data)
const genderStackedOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Employed', 'Unemployed'] },
    xAxis: { type: 'category', data: filteredEmploymentByGender.value.map(d => d.gender) },
    yAxis: { type: 'value', name: 'Count' },
    series: [
        {
            name: 'Employed',
            type: 'bar',
            stack: 'employment',
            data: filteredEmploymentByGender.value.map(d => d.employed),
            itemStyle: { color: '#34d399' }
        },
        {
            name: 'Unemployed',
            type: 'bar',
            stack: 'employment',
            data: filteredEmploymentByGender.value.map(d => (d.total - d.employed)),
            itemStyle: { color: '#f87171' }
        }
    ]
}))
const diversityPieOption = computed(() => {
    const pieData = []
    const pieSource = filteredDiversityIndustryPie.value[0]
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
const lineDemographicOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: Array.from(new Set(filteredLineDemographicTrends.value.map(d => d.group))) },
    xAxis: { type: 'category', data: Array.from(new Set(filteredLineDemographicTrends.value.map(d => d.month))) },
    yAxis: { type: 'value', name: 'Applications' },
    series: filteredLineDemographicTrends.value.reduce((acc, d) => {
        let s = acc.find(x => x.name === d.group)
        if (!s) {
            s = { name: d.group, type: 'line', data: [] }
            acc.push(s)
        }
        s.data.push(d.count)
        return acc
    }, [])
}))
const barReferralAgeOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: Object.keys(filteredReferralAge.value) },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(filteredReferralAge.value),
        itemStyle: { color: '#f87171' }
    }]
}))
const barReferralGenderOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: Object.keys(filteredReferralGender.value) },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(filteredReferralGender.value),
        itemStyle: { color: '#60a5fa' }
    }]
}))
const barReferralEducationOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: Object.keys(filteredReferralEducation.value) },
    yAxis: { type: 'value', name: 'Referred Candidates' },
    series: [{
        name: 'Referred',
        type: 'bar',
        data: Object.values(filteredReferralEducation.value),
        itemStyle: { color: '#34d399' }
    }]
}))
const radarDemographicOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarDemographicLabels ?? []).map(label => ({ name: label, max: 100 })),
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
}))
const radarGenderOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarGenderLabels ?? []).map(label => ({ name: label, max: 100 })),
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
}))
const radarEducationOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarEducationLabels ?? []).map(label => ({ name: label, max: 100 })),
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
}))

// Summaries (unchanged)
const demographicsSummary = computed(() => {
    const gender = filteredEmploymentByGender.value
    const pie = filteredDiversityIndustryPie.value[0]
    let summary = "<ul class='list-disc ml-6 mb-2'>"
    if (gender.length && gender.some(g => g.total > 0)) {
        const employed = gender.reduce((sum, g) => sum + (g.employed ?? 0), 0)
        const total = gender.reduce((sum, g) => sum + (g.total ?? 0), 0)
        summary += `<li><strong>${employed}</strong> out of <strong>${total}</strong> are employed.</li>`
        gender.forEach(g => {
            summary += `<li>${g.gender}: <strong>${g.employed}</strong> employed, <strong>${g.total - g.employed}</strong> unemployed</li>`
        })
    } else {
        summary += `<li>No gender data matches the current filters.</li>`
    }
    if (pie && pie.groups && Object.values(pie.groups).some(v => v > 0)) {
        const topGroup = Object.entries(pie.groups).sort((a, b) => b[1] - a[1])[0]
        if (topGroup) {
            summary += `<li>Largest diversity group: <strong>${topGroup[0]}</strong> (${topGroup[1]} members)</li>`
        }
    } else {
        summary += `<li>No diversity group data matches the current filters.</li>`
    }
    summary += "</ul>"
    return summary
})

const jobSeekerSummary = computed(() => {
    const age = filteredJobSeekersByAge.value
    const edu = filteredJobSeekersByEducation.value
    const exp = filteredJobSeekersByExperience.value
    const line = filteredLineDemographicTrends.value
    const referralAge = filteredReferralAge.value
    const referralGender = filteredReferralGender.value
    const referralEdu = filteredReferralEducation.value
    let summary = "<ul class='list-disc ml-6 mb-2'>"

    if (Object.keys(age).length && Object.values(age).some(v => v > 0)) {
        const topAge = Object.entries(age).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most common age group: <strong>${topAge[0]}</strong> (${topAge[1]} seekers)</li>`
    } else {
        summary += `<li>No age group data matches the current filters.</li>`
    }

    if (Object.keys(edu).length && Object.values(edu).some(v => v > 0)) {
        const topEdu = Object.entries(edu).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most common education: <strong>${topEdu[0]}</strong> (${topEdu[1]} seekers)</li>`
    } else {
        summary += `<li>No education data matches the current filters.</li>`
    }

    if (Object.keys(exp).length && Object.values(exp).some(v => v > 0)) {
        const topExp = Object.entries(exp).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most common experience: <strong>${topExp[0]}</strong> (${topExp[1]} seekers)</li>`
    } else {
        summary += `<li>No experience data matches the current filters.</li>`
    }

    if (line.length) {
        const first = line[0]?.count ?? 0
        const last = line[line.length - 1]?.count ?? 0
        if (last > first) {
            summary += `<li>Applications increased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`
        } else if (last < first) {
            summary += `<li>Applications decreased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`
        } else {
            summary += `<li>Applications remained stable at <strong>${first}</strong>.</li>`
        }
    } else {
        summary += `<li>No application trend data matches the current filters.</li>`
    }

    if (Object.keys(referralAge).length && Object.values(referralAge).some(v => v > 0)) {
        const topReferralAge = Object.entries(referralAge).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most referred age group: <strong>${topReferralAge[0]}</strong> (${topReferralAge[1]} candidates)</li>`
    } else {
        summary += `<li>No referred age group data matches the current filters.</li>`
    }

    if (Object.keys(referralGender).length && Object.values(referralGender).some(v => v > 0)) {
        const topReferralGender = Object.entries(referralGender).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most referred gender: <strong>${topReferralGender[0]}</strong> (${topReferralGender[1]} candidates)</li>`
    } else {
        summary += `<li>No referred gender data matches the current filters.</li>`
    }

    if (Object.keys(referralEdu).length && Object.values(referralEdu).some(v => v > 0)) {
        const topReferralEdu = Object.entries(referralEdu).sort((a, b) => b[1] - a[1])[0]
        summary += `<li>Most referred education: <strong>${topReferralEdu[0]}</strong> (${topReferralEdu[1]} candidates)</li>`
    } else {
        summary += `<li>No referred education data matches the current filters.</li>`
    }

    summary += "</ul>"
    return summary
})
</script>

<template>
    <AppLayout title="Diversity and Demographics Report">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Diversity and Demographics Reports</h2>
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
            <div v-if="reportType === 'demographics'"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" v-model="dateFrom" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" v-model="dateTo" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                        <MultiSelect v-model="selectedIndustries" :options="industries" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select industries" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <MultiSelect v-model="selectedGenders" :options="genders.map(g => ({ id: g, name: g }))"
                            label="name" track-by="id" :searchable="true" :multiple="true"
                            placeholder="Select genders" />
                    </div>
                </div>
                <div v-if="loading" class="flex justify-center items-center py-16">
                    <span class="loader mr-3"></span>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-blue-500"></i>
                            Employment by Gender <span class="font-bold text-blue-600">(Stacked Column)</span>
                        </h3>
                        <VueECharts v-if="filteredEmploymentByGender.length" :option="genderStackedOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No gender data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-green-500"></i>
                            Diversity Groups in Industry <span class="font-bold text-green-600">(Pie Chart)</span>
                        </h3>
                        <VueECharts
                            v-if="filteredDiversityIndustryPie.length && filteredDiversityIndustryPie[0]?.groups"
                            :option="diversityPieOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No diversity data available.</div>
                    </div>
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

            <!-- Job Seeker Demographics Section -->
            <div v-if="reportType === 'job-seeker-demographics'"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-green-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" v-model="dateFrom" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" v-model="dateTo" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                        <MultiSelect v-model="selectedAges" :options="ageGroups.map(a => ({ id: a, name: a }))"
                            label="name" track-by="id" :searchable="true" :multiple="true"
                            placeholder="Select age groups" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Education</label>
                        <MultiSelect v-model="selectedEducations" :options="educationLevels" label="level_of_education"
                            track-by="id" :searchable="true" :multiple="true" placeholder="Select education levels" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Experience</label>
                        <MultiSelect v-model="selectedExperiences"
                            :options="experienceLevels.map(e => ({ id: e, name: e }))" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select experience levels" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender (for Job Application
                            Trends)</label>
                        <MultiSelect v-model="selectedJobSeekerGenders"
                            :options="genders.map(g => ({ id: g, name: g }))" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select genders" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Referral Age</label>
                        <MultiSelect v-model="selectedReferralAges" :options="ageGroups.map(a => ({ id: a, name: a }))"
                            label="name" track-by="id" :searchable="true" :multiple="true"
                            placeholder="Select referral ages" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Referral Gender</label>
                        <MultiSelect v-model="selectedReferralGenders" :options="genders.map(g => ({ id: g, name: g }))"
                            label="name" track-by="id" :searchable="true" :multiple="true"
                            placeholder="Select referral genders" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Referral Education</label>
                        <MultiSelect v-model="selectedReferralEducations" :options="educationLevels"
                            label="level_of_education" track-by="id" :searchable="true" :multiple="true"
                            placeholder="Select referral education" />
                    </div>
                </div>
                <div v-if="loading" class="flex justify-center items-center py-16">
                    <span class="loader mr-3"></span>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-user-clock text-blue-500"></i>
                            Job Seekers by Age <span class="font-bold text-blue-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredJobSeekersByAge).length" :option="{
                            tooltip: { trigger: 'axis' },
                            xAxis: { type: 'category', data: Object.keys(filteredJobSeekersByAge) },
                            yAxis: { type: 'value', name: 'Count' },
                            series: [{
                                name: 'Age',
                                type: 'bar',
                                data: Object.values(filteredJobSeekersByAge),
                                itemStyle: { color: '#60a5fa' }
                            }]
                        }" style="height: 300px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No age data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-user-graduate text-green-500"></i>
                            Job Seekers by Education <span class="font-bold text-green-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredJobSeekersByEducation).length" :option="{
                            tooltip: { trigger: 'axis' },
                            xAxis: { type: 'category', data: Object.keys(filteredJobSeekersByEducation) },
                            yAxis: { type: 'value', name: 'Count' },
                            series: [{
                                name: 'Education',
                                type: 'bar',
                                data: Object.values(filteredJobSeekersByEducation),
                                itemStyle: { color: '#34d399' }
                            }]
                        }" style="height: 300px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No education data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-briefcase text-yellow-500"></i>
                            Job Seekers by Experience <span class="font-bold text-yellow-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredJobSeekersByExperience).length" :option="{
                            tooltip: { trigger: 'axis' },
                            xAxis: { type: 'category', data: Object.keys(filteredJobSeekersByExperience) },
                            yAxis: { type: 'value', name: 'Count' },
                            series: [{
                                name: 'Experience',
                                type: 'bar',
                                data: Object.values(filteredJobSeekersByExperience),
                                itemStyle: { color: '#f59e42' }
                            }]
                        }" style="height: 300px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No experience data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-line text-green-500"></i>
                            Job Applications Trends <span class="font-bold text-green-600">(Line Chart)</span>
                        </h3>
                        <VueECharts v-if="filteredLineDemographicTrends.length" :option="lineDemographicOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No trend data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-yellow-500"></i>
                            Referred Candidates by Age <span class="font-bold text-yellow-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredReferralAge).length" :option="barReferralAgeOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No referral age data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-blue-500"></i>
                            Referred Candidates by Gender <span class="font-bold text-blue-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredReferralGender).length" :option="barReferralGenderOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No referral gender data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-green-500"></i>
                            Referred Candidates by Education <span class="font-bold text-green-600">(Bar Chart)</span>
                        </h3>
                        <VueECharts v-if="Object.keys(filteredReferralEducation).length"
                            :option="barReferralEducationOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No referral education data available.</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-braille text-purple-500"></i>
                            Demographic Attributes vs Referral Success <span class="font-bold text-purple-600">(Radar
                                Charts)</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div>
                                <div class="text-center font-semibold mb-2 text-gray-700">Age</div>
                                <VueECharts v-if="analyticsData.radarDemographicData" :option="radarDemographicOption"
                                    style="height: 300px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No age radar data available.</div>
                            </div>
                            <div>
                                <div class="text-center font-semibold mb-2 text-gray-700">Gender</div>
                                <VueECharts v-if="analyticsData.radarGenderData" :option="radarGenderOption"
                                    style="height: 300px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No gender radar data available.
                                </div>
                            </div>
                            <div>
                                <div class="text-center font-semibold mb-2 text-gray-700">Education</div>
                                <VueECharts v-if="analyticsData.radarEducationData" :option="radarEducationOption"
                                    style="height: 300px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-12">No education radar data available.
                                </div>
                            </div>
                        </div>
                    </div>
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