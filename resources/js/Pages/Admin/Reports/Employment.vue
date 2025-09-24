<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';
import { usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue';
import axios from 'axios'


const tabs = [
    { label: 'Employment Overview', value: 'overview' },
    { label: 'Unemployment Rate', value: 'unemployment' },
    { label: 'Demand-Supply Career Gap Map', value: 'demandSupply' },
    { label: 'School-wise Employability', value: 'schoolwise' },

]
const activeTab = ref('overview')

// --- Filter state ---
const selectedYear = ref('')
const selectedProgram = ref('')
const selectedStatus = ref('')
const selectedInstitution = ref('')

const smartSchoolPages = computed(() => {
    const total = schoolTotalPages.value;
    const current = schoolPage.value;
    const pages = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        pages.push(1);
        if (current > 4) pages.push('...');
        for (let i = Math.max(2, current - 2); i <= Math.min(total - 1, current + 2); i++) {
            pages.push(i);
        }
        if (current < total - 3) pages.push('...');
        pages.push(total);
    }
    return pages;
});

// --- Static filter options from page.props ---
const page = usePage()
const institutions = ref(page.props.institutions ?? [])
const programs = ref(page.props.programs ?? [])

// --- Analytics data (fetched asynchronously) ---
const analyticsData = ref({})
const loading = ref(false)

// --- Fetch analytics data from backend ---
function fetchAnalyticsData() {
    loading.value = true
    let params = {}
    if (activeTab.value === 'overview') {
        params = {
            year: selectedYear.value,
            program_id: selectedProgram.value,
            institution_id: selectedInstitution.value,
            status: selectedStatus.value,
        }
    } else if (activeTab.value === 'unemployment') {
        params = {
            year: selectedYear.value,
            program_id: selectedProgram.value,
            institution_id: selectedInstitution.value,
            // status: selectedStatus.value, // <-- DO NOT SEND STATUS!
        }
    } else if (activeTab.value === 'demandSupply') {
        params = {};
    }
    axios.get(route('peso.reports.employment.data'), { params })
        .then(res => {
            analyticsData.value = res.data
            currentPage.value = 1
        })
        .finally(() => {
            loading.value = false
        })
}

// Fetch on mount and when filters change
onMounted(fetchAnalyticsData)
watch([selectedYear, selectedProgram, selectedInstitution, activeTab], fetchAnalyticsData)
watch(analyticsData, () => { }, { deep: true })
// --- Graduates and computed values ---
const graduates = computed(() => analyticsData.value.graduates ?? [])
const currentPage = ref(1)
const pageSize = ref(10)
const paginatedGraduates = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return safeFilteredGraduates.value.slice(start, start + pageSize.value)
})
const totalPages = computed(() => Math.ceil(safeFilteredGraduates.value.length / pageSize.value))

// --- Filtered graduates ---
const safeFilteredGraduates = computed(() => graduates.value) // Already filtered by backend

// --- KPI values ---
const employed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Employed').length)
const unemployed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Unemployed').length)
const underemployed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Underemployed').length)
const totalGraduates = computed(() => safeFilteredGraduates.value.length)

// --- Years for dropdown ---
const years = computed(() => {
    const set = new Set()
    graduates.value.forEach(g => {
        if (g.school_year?.school_year_range) set.add(g.school_year.school_year_range)
    })
    return Array.from(set).sort().reverse()
})

// --- Status counts for chart ---
const statusCounts = computed(() => analyticsData.value.statusCounts ?? {})
const filteredStatusCounts = computed(() => statusCounts.value)

// --- Chart options -
const pieOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
    legend: { orient: 'vertical', left: 'left' },
    series: [
        {
            name: 'Status',
            type: 'pie',
            radius: '60%',
            data: [
                { name: 'Employed', value: filteredStatusCounts.value.Employed },
                { name: 'Underemployed', value: filteredStatusCounts.value.Underemployed },
                { name: 'Unemployed', value: filteredStatusCounts.value.Unemployed }
            ],
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            },
            label: {
                formatter: '{b}: {d}%',
                color: '#374151',
                fontWeight: 'bold'
            }
        }
    ]
}))

// --- Industry/Program chart options ---
const industryGraduateCounts = computed(() => analyticsData.value.industryGraduateCounts ?? [])
const industryNames = computed(() => analyticsData.value.industryNames ?? [])
const industryJobRoles = computed(() => analyticsData.value.industryJobRoles ?? [])
const industryApplicants = computed(() => analyticsData.value.industryApplicants ?? [])
const programNames = computed(() => analyticsData.value.programNames ?? [])
const employedByProgram = computed(() => analyticsData.value.employedByProgram ?? [])
const unemployedByProgram = computed(() => analyticsData.value.unemployedByProgram ?? [])

// --- Bar chart for employment by program ---
const barOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Employed', 'Unemployed'] },
    xAxis: { type: 'category', data: programNames.value },
    yAxis: { type: 'value' },
    series: [
        {
            name: 'Employed',
            type: 'bar',
            stack: 'total',
            data: employedByProgram.value,
            itemStyle: { color: '#22c55e' }
        },
        {
            name: 'Unemployed',
            type: 'bar',
            stack: 'total',
            data: unemployedByProgram.value,
            itemStyle: { color: '#ef4444' }
        }
    ]
}))

// --- Industry bar chart ---
const industryBarOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: industryGraduateCounts.value.map(i => i.name) },
    yAxis: { type: 'value', name: 'Graduates' },
    series: [
        {
            name: 'Graduates',
            type: 'bar',
            data: industryGraduateCounts.value.map(i => i.value),
            itemStyle: { color: '#3b82f6' }
        }
    ]
}))

// --- Treemap ---
const industryTreemapOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    series: [
        {
            type: 'treemap',
            data: industryGraduateCounts.value,
            label: { show: true, formatter: '{b}' }
        }
    ]
}))

// --- Stacked Column Chart ---
const industryStackedOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Job Roles', 'Applicants'] },
    xAxis: { type: 'category', data: industryNames.value },
    yAxis: { type: 'value' },
    series: [
        {
            name: 'Job Roles',
            type: 'bar',
            stack: 'total',
            data: industryJobRoles.value,
            itemStyle: { color: '#10b981' }
        },
        {
            name: 'Applicants',
            type: 'bar',
            stack: 'total',
            data: industryApplicants.value,
            itemStyle: { color: '#f59e42' }
        }
    ]
}))

// --- Unemployment Rate Over Time Chart Data ---
const unemploymentOverTime = computed(() => analyticsData.value.unemploymentOverTime ?? [])

// ===== Summary / Interpretation Computeds for Unemployment Tab =====
const employmentStatusSummary = computed(() => {
    // Use raw counts to avoid rounding drift
    const dataArr = pieOption.value.series[0].data || []
    const totals = dataArr.reduce((acc, item) => {
        acc.total += (item.value || 0)
        acc[item.name] = item.value || 0
        return acc
    }, { total: 0, Employed: 0, Underemployed: 0, Unemployed: 0 })

    if (!totals.total) {
        return {
            hasData: false,
            narrative: 'No employment status data available for the current filters.'
        }
    }

    const pct = (val) => ((val / totals.total) * 100).toFixed(1)
    // Determine leading status
    const ranking = [
        { label: 'Employed', value: totals.Employed },
        { label: 'Underemployed', value: totals.Underemployed },
        { label: 'Unemployed', value: totals.Unemployed }
    ].sort((a, b) => b.value - a.value)

    const leader = ranking[0]
    let leaderPhrase = ''
    if (leader.value > 0) {
        leaderPhrase = `${leader.label} graduates form the largest group (${pct(leader.value)}%).`
    }

    return {
        hasData: true,
        employedPct: pct(totals.Employed),
        underemployedPct: pct(totals.Underemployed),
        unemployedPct: pct(totals.Unemployed),
        leader: leader.label,
        narrative:
            `Of the ${totals.total} graduates in the current selection: ` +
            `<strong>${pct(totals.Employed)}%</strong> are employed, ` +
            `<strong>${pct(totals.Underemployed)}%</strong> underemployed, and ` +
            `<strong>${pct(totals.Unemployed)}%</strong> unemployed. ${leaderPhrase}`
    }
})

const unemploymentTrendSummary = computed(() => {
    const arr = unemploymentOverTime.value
    if (!Array.isArray(arr) || !arr.length) {
        return {
            hasData: false,
            narrative: 'No historical unemployment rate data available.'
        }
    }

    // Basic stats
    const avg = (arr.reduce((s, r) => s + (r.unemployment_rate || 0), 0) / arr.length) || 0
    const highest = arr.reduce((a, b) => (a.unemployment_rate > b.unemployment_rate ? a : b))
    const lowest = arr.reduce((a, b) => (a.unemployment_rate < b.unemployment_rate ? a : b))

    // Trend detection: compare earliest vs latest
    const first = arr[0]
    const last = arr[arr.length - 1]
    const delta = (last.unemployment_rate ?? 0) - (first.unemployment_rate ?? 0)
    let direction
    if (Math.abs(delta) < 0.5) {
        direction = 'relatively stable'
    } else if (delta < 0) {
        direction = `declining (↓ ${(Math.abs(delta)).toFixed(1)} pts)`
    } else {
        direction = `increasing (↑ ${delta.toFixed(1)} pts)`
    }

    return {
        hasData: true,
        narrative:
            `Average unemployment rate across the shown years: <strong>${avg.toFixed(1)}%</strong>. ` +
            `Highest: <strong>${highest.year}</strong> (${highest.unemployment_rate}%). ` +
            `Lowest: <strong>${lowest.year}</strong> (${lowest.unemployment_rate}%). ` +
            `Overall trend appears <strong>${direction}</strong> (from ${first.unemployment_rate}% in ${first.year} to ` +
            `${last.unemployment_rate}% in ${last.year}).`
    }
})

const areaOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Unemployment Rate'] },
    xAxis: { type: 'category', data: unemploymentOverTime.value.map(d => d.year) },
    yAxis: { type: 'value', name: '%' },
    series: [
        {
            name: 'Unemployment Rate',
            type: 'line',
            areaStyle: {},
            data: unemploymentOverTime.value.map(d => d.unemployment_rate),
            itemStyle: { color: '#ef4444' }
        }
    ]
}));

// Demand-Supply Career Gap Map logic
const inDemandJobs = computed(() => analyticsData.value.inDemandJobs ?? []);
const roleFilter = ref('');
const careerGapYear = ref('');
const careerGapCurrentPage = ref(1);
const careerGapPageSize = ref(10);

console.log(inDemandJobs.value)

const filteredInDemandJobs = computed(() => {
    let jobs = inDemandJobs.value;
    if (roleFilter.value) {
        jobs = jobs.filter(job =>
            job.role && job.role.toLowerCase().includes(roleFilter.value.toLowerCase())
        );
    }
    if (careerGapYear.value) {
        jobs = jobs.filter(job =>
            job.year === careerGapYear.value
        );
    }
    return jobs;
});

const paginatedInDemandJobs = computed(() => {
    const start = (careerGapCurrentPage.value - 1) * careerGapPageSize.value;
    return filteredInDemandJobs.value.slice(start, start + careerGapPageSize.value);
});

const careerGapTotalPages = computed(() =>
    Math.ceil(filteredInDemandJobs.value.length / careerGapPageSize.value)
);

function getLocalGraduateCount(job) {
    if (
        !safeFilteredGraduates.value ||
        !Array.isArray(safeFilteredGraduates.value) ||
        !job.program_ids ||
        !Array.isArray(job.program_ids) ||
        !job.program_ids.length
    ) return 0;

    return safeFilteredGraduates.value.filter(g => {
        if (careerGapYear.value && g.school_year?.school_year_range !== careerGapYear.value) return false;
        const byProgramId = g.program_id && job.program_ids.map(Number).includes(Number(g.program_id));
        return byProgramId;
    }).length;
}

const uniqueRoleSuggestions = computed(() => {
    const set = new Set(inDemandJobs.value.map(job => job.role));
    return Array.from(set).sort();
});

const careerGapBarOption = computed(() => {
    const roles = inDemandJobs.value.map(j => j.role);
    const demand = inDemandJobs.value.map(j => j.demand);
    const supply = inDemandJobs.value.map(job => getLocalGraduateCount(job));
    return {
        tooltip: { trigger: 'axis' },
        legend: { data: ['Demand', 'Local Graduate Supply'] },
        xAxis: { type: 'category', data: roles, axisLabel: { rotate: 30 } },
        yAxis: { type: 'value' },
        series: [
            {
                name: 'Demand',
                type: 'bar',
                data: demand,
                itemStyle: { color: '#f59e42' }
            },
            {
                name: 'Local Graduate Supply',
                type: 'bar',
                data: supply,
                itemStyle: { color: '#3b82f6' }
            }
        ]
    };
});

const topInDemandCareer = computed(() => {
    const sorted = filteredInDemandJobs.value
        .map(job => ({ role: job.role, supply: getLocalGraduateCount(job) }))
        .sort((a, b) => b.supply - a.supply);
    return sorted[0] || null;
});

const schoolFilters = ref({
    institution_id: '',
    program_id: '',
    timeline: ''
});
const schoolInstitutions = computed(() => institutions.value);
const schoolPrograms = computed(() => programs.value);
const schoolGraduates = computed(() => analyticsData.value.schoolEmployabilityList ?? []);
const schoolPage = ref(1);
const schoolPageSize = 10;
const schoolPaginatedGraduates = computed(() => {
    const start = (schoolPage.value - 1) * schoolPageSize;
    return schoolGraduates.value.slice(start, start + schoolPageSize);
});
const schoolTotalPages = computed(() =>
    Math.ceil(schoolGraduates.value.length / schoolPageSize)
);

const schoolTopSchool = computed(() => {
    if (schoolFilters.value.institution_id) {
        const selectedInstitution = schoolInstitutions.value.find(i => i.id == schoolFilters.value.institution_id);
        if (!selectedInstitution) return "N/A";
        const programCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution === selectedInstitution.institution_name && grad.program) {
                programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
            }
        });
        const sorted = Object.entries(programCounts).sort((a, b) => b[1] - a[1]);
        return sorted.length ? sorted[0][0] : "N/A";
    } else {
        const institutionCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution) {
                institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
            }
        });
        const sorted = Object.entries(institutionCounts).sort((a, b) => b[1] - a[1]);
        return sorted.length ? sorted[0][0] : "N/A";
    }
});
const schoolTotalHired = computed(() => schoolGraduates.value.length);

const schoolDynamicChartLabels = computed(() => {
    if (schoolFilters.value.institution_id) {
        const selectedInstitution = schoolInstitutions.value.find(i => i.id == schoolFilters.value.institution_id);
        if (!selectedInstitution) return [];
        const programCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution === selectedInstitution.institution_name && grad.program) {
                programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
            }
        });
        return Object.keys(programCounts);
    } else {
        const institutionCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution) {
                institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
            }
        });
        return Object.keys(institutionCounts);
    }
});
const schoolDynamicChartData = computed(() => {
    if (schoolFilters.value.institution_id) {
        const selectedInstitution = schoolInstitutions.value.find(i => i.id == schoolFilters.value.institution_id);
        if (!selectedInstitution) return [];
        const programCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution === selectedInstitution.institution_name && grad.program) {
                programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
            }
        });
        return Object.values(programCounts);
    } else {
        const institutionCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution) {
                institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
            }
        });
        return Object.values(institutionCounts);
    }
});
const schoolBarColors = [
    '#4F46E5', '#22D3EE', '#F59E42', '#10B981', '#F43F5E', '#6366F1', '#FBBF24', '#8B5CF6', '#EC4899', '#14B8A6',
];
const schoolChartOptions = computed(() => ({
    xAxis: {
        type: 'category',
        data: schoolDynamicChartLabels.value,
        name: schoolFilters.value.institution_id ? 'Programs' : 'Institutions',
        nameLocation: 'end',
        nameGap: 30,
        axisLabel: { rotate: 20, interval: 0, formatter: value => value },
    },
    yAxis: {
        type: 'value',
        name: 'Number of Hires',
        nameLocation: 'middle',
        nameGap: 40,
    },
    series: [{
        data: schoolDynamicChartData.value.map((val, idx) => ({
            value: val,
            itemStyle: { color: schoolBarColors[idx % schoolBarColors.length] },
            label: { show: true, position: 'top', formatter: '{c}' },
        })),
        type: 'bar',
        label: { show: true, position: 'top', formatter: '{c}' },
    }]
}));

function schoolGoToPage(p) {
    schoolPage.value = p;
}
function schoolApplyFilters() {
    // If you want to refetch, call fetchAnalyticsData or similar here
    fetchAnalyticsData();
    // Or filter client-side if all data is loaded
}

const schoolEmployabilitySummaryInsight = computed(() => {
    const total = schoolTotalHired.value;
    const timeline = schoolFilters.value.timeline;
    const selectedInstitution = schoolInstitutions.value.find(i => i.id == schoolFilters.value.institution_id);
    const selectedProgram = schoolPrograms.value.find(p => p.id == schoolFilters.value.program_id);

    const institutionPart = selectedInstitution ? ` from <strong>${selectedInstitution.institution_name}</strong>` : '';
    const programPart = selectedProgram ? ` under the <strong>${selectedProgram.name}</strong> program` : '';
    const timelinePart = timeline
        ? ` for the month of <strong>${new Date(timeline).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</strong>`
        : '';

    if (total === 0) {
        return `No graduates were hired locally${institutionPart}${programPart}${timelinePart}.`;
    }

    let rankedList = [];
    let rankingTitle = "";

    if (selectedInstitution) {
        const programCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.program && grad.institution === selectedInstitution.institution_name) {
                programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
            }
        });
        rankedList = Object.entries(programCounts)
            .sort((a, b) => b[1] - a[1])
            .map(([name, count], index) => `${index + 1}. ${name} (${count} hires)`)
            .slice(0, 5);

        rankingTitle = `The top programs within <strong>${selectedInstitution.institution_name}</strong> are:<br><strong>${rankedList.join('<br>')}</strong>.`;
    } else {
        const institutionCounts = {};
        schoolGraduates.value.forEach(grad => {
            if (grad.institution) {
                institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
            }
        });
        rankedList = Object.entries(institutionCounts)
            .sort((a, b) => b[1] - a[1])
            .map(([name, count], index) => `${index + 1}. ${name} (${count} hires)`)
            .slice(0, 5);

        rankingTitle = `The top institutions based on local hiring are:<br><strong>${rankedList.join('<br>')}</strong>.`;
    }

    const programCounts = {};
    schoolGraduates.value.forEach(grad => {
        if (grad.program) {
            programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
        }
    });
    const topProgram = Object.entries(programCounts).sort((a, b) => b[1] - a[1])[0];

    const sorted = schoolGraduates.value
        .filter(grad => grad.hired_at)
        .sort((a, b) => new Date(a.hired_at) - new Date(b.hired_at));

    const first5 = sorted.slice(0, 5).length;
    const last5 = sorted.slice(-5).length;
    let trend = "";

    if (first5 < last5) {
        trend = `There is a noticeable <strong>increasing trend</strong> in graduate hiring.`;
    } else if (first5 > last5) {
        trend = `Recent data shows a <strong>decline</strong> in graduate hiring.`;
    } else {
        trend = `Hiring activity appears to be <strong>stable</strong> over time.`;
    }

    return `
        A total of <strong>${total}</strong> graduates were hired locally${institutionPart}${programPart}${timelinePart}.<br>
        The top academic program overall is <strong>${topProgram?.[0] || 'N/A'}</strong> with <strong>${topProgram?.[1] || 0}</strong> hires.<br>
        ${rankingTitle}<br>
        ${trend}
    `;
});


</script>

<template>
    <AppLayout title="Employment Overview">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Employability Report</h2>
            <!-- FILTER CONTROLS -->
            <div class="mb-8">
                <div class="flex space-x-2 border-b border-gray-200">
                    <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value" :class="[
                        'px-4 py-2 font-semibold focus:outline-none',
                        activeTab === tab.value ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'
                    ]">
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div v-if="activeTab === 'overview'">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Filter Reports
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select v-model="selectedYear"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                                <option value="">All Years</option>
                                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                            <select v-model="selectedProgram"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                                <option value="">All Programs</option>
                                <option v-for="program in programs" :key="program.id" :value="program.id">{{
                                    program.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="selectedStatus"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                                <option value="">All Statuses</option>
                                <option value="Employed">Employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Underemployed">Underemployed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                            <select v-model="selectedInstitution"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                                <option value="">All Locations</option>
                                <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{
                                    inst.institution_name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Loading Spinner -->
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <!-- KPI Cards -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                    <div
                        class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-gray-600 text-sm font-medium mb-2">Employed</h3>
                                <p class="text-3xl font-bold text-green-600">{{ employed }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ ((employed / totalGraduates) * 100).toFixed(1)
                                }}%
                                    of graduates</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                                <i class="fas fa-briefcase text-green-500"></i>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-gray-600 text-sm font-medium mb-2">Unemployed</h3>
                                <p class="text-3xl font-bold text-red-600">{{ unemployed }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ ((unemployed / totalGraduates) *
                                    100).toFixed(1)
                                }}% of graduates</p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3 flex items-center justify-center">
                                <i class="fas fa-user-clock text-red-500"></i>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-gray-600 text-sm font-medium mb-2">Total Graduates</h3>
                                <p class="text-3xl font-bold text-blue-600">{{ totalGraduates }}</p>
                                <p class="text-sm text-gray-500 mt-1">Across all programs</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                                <i class="fas fa-user-graduate text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="!loading" class="mb-2 text-sm text-gray-500">
                    Showing {{ safeFilteredGraduates.length }} of {{ totalGraduates }} graduates
                </p>
                <div v-if="!loading && safeFilteredGraduates.length" class="bg-white rounded-xl shadow p-8 mb-10">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Filtered Graduates</h3>
                    <table class="min-w-full text-sm text-gray-800">
                        <thead>
                            <tr>
                                <th class="px-2 py-1 text-left">Name</th>
                                <th class="px-2 py-1 text-left">Program</th>
                                <th class="px-2 py-1 text-left">Status</th>
                                <th class="px-2 py-1 text-left">Year</th>
                                <th class="px-2 py-1 text-left">Institution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in paginatedGraduates" :key="g.id" class="hover:bg-gray-100">
                                <td class="px-2 py-1">{{ g.first_name }} {{ g.last_name }}</td>
                                <td class="px-2 py-1">{{ g.program?.name }}</td>
                                <td class="px-2 py-1">{{ g.employment_status }}</td>
                                <td class="px-2 py-1">{{ g.school_year?.school_year_range }}</td>
                                <td class="px-2 py-1">{{ g.institution?.institution_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="totalPages > 1" class="flex items-center justify-between mt-4">
                        <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="currentPage === 1"
                            @click="currentPage--">Prev</button>
                        <span class="text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
                        <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                            :disabled="currentPage === totalPages" @click="currentPage++">Next</button>
                    </div>
                </div>

                <!-- Employment Status Chart & Industry/Program Charts -->
                <div v-if="!loading && statusCounts && Object.keys(statusCounts).length"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col lg:flex-row gap-12 items-center justify-between">
                        <div class="w-full lg:w-2/5 mb-8 lg:mb-0">
                            <h3 class="text-lg font-semibold mb-6 text-gray-700 flex items-center">
                                <i class="fas fa-list-alt text-blue-500 mr-2"></i>
                                Detailed Status
                            </h3>
                            <ul class="space-y-4">
                                <li v-for="(count, status) in filteredStatusCounts" :key="status"
                                    class="flex justify-between items-center px-4 py-2 rounded hover:bg-gray-50 transition">
                                    <span class="capitalize text-gray-600">{{ status }}</span>
                                    <span class="font-semibold text-gray-900">{{ count }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="w-full lg:w-3/5 flex justify-center">
                            <div class="bg-gray-50 rounded-lg p-4 w-full flex justify-center">
                                <VueECharts v-if="pieOption.series[0].data.length" :option="pieOption"
                                    style="height: 350px; width: 100%; max-width: 420px;" />
                                <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
                            </div>
                        </div>
                    </div>
                    <!-- Employment by Industry -->
                    <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employment by Industry</h2>

                    <!-- Bar/Clustered Column Chart -->
                    <div class="bg-white rounded-xl shadow p-8 mb-12">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Graduates per Industry</h3>
                        <VueECharts :option="industryBarOption" style="height: 400px; width: 100%;" />
                    </div>

                    <!-- Treemap -->
                    <div class="bg-white rounded-xl shadow p-8 mb-12">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Industry Share (Treemap)</h3>
                        <VueECharts :option="industryTreemapOption" style="height: 400px; width: 100%;" />
                    </div>

                    <!-- Stacked Column Chart -->
                    <div class="bg-white rounded-xl shadow p-8">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Roles vs. Applicants by Industry</h3>
                        <VueECharts :option="industryStackedOption" style="height: 400px; width: 100%;" />
                    </div>

                    <!-- Employment By Program -->
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                        Employment by Program
                    </h2>
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-10 hover:shadow-md transition-shadow duration-200">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                            Employment Rate by Program
                        </h3>
                        <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
                    </div>
                </div>

                <!-- Summary Block -->
                <div v-if="!loading" class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="text-blue-900 space-y-2">
                        <div>
                            <span class="font-semibold">Summary:</span>
                            <span>
                                Out of <span class="font-bold">{{ totalGraduates }}</span> filtered graduates:
                                <span class="text-green-700 font-semibold">{{ employed }}</span> employed ({{ ((employed
                                    /
                                    totalGraduates) * 100).toFixed(1) }}%),
                                <span class="text-yellow-700 font-semibold">{{ underemployed }}</span> underemployed ({{
                                    ((underemployed
                                        / totalGraduates) * 100).toFixed(1) }}%),
                                <span class="text-red-700 font-semibold">{{ unemployed }}</span> unemployed ({{
                                    ((unemployed
                                        /
                                        totalGraduates) * 100).toFixed(1) }}%).
                            </span>
                        </div>
                        <div v-if="years.length">
                            <span class="font-semibold">School Years:</span>
                            <span>{{ years.join(', ') }}</span>
                        </div>
                        <div v-if="safeFilteredGraduates.length">
                            <span class="font-semibold">Top Program:</span>
                            <span>
                                {{
                                    (() => {
                                        const counts = {};
                                        safeFilteredGraduates.forEach(g => {
                                            if (g.program?.name) counts[g.program.name] = (counts[g.program.name] || 0) + 1;
                                        });
                                        const top = Object.entries(counts).sort((a, b) => b[1] - a[1])[0];
                                        return top ? `${top[0]} (${top[1]})` : 'N/A';
                                    })()
                                }}
                            </span>
                        </div>
                        <div v-if="safeFilteredGraduates.length">
                            <span class="font-semibold">Top Institution:</span>
                            <span>
                                {{
                                    (() => {
                                        const counts = {};
                                        safeFilteredGraduates.forEach(g => {
                                            if (g.institution?.institution_name) counts[g.institution.institution_name] =
                                                (counts[g.institution.institution_name] || 0) + 1;
                                        });
                                        const top = Object.entries(counts).sort((a, b) => b[1] - a[1])[0];
                                        return top ? `${top[0]} (${top[1]})` : 'N/A';
                                    })()
                                }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <div v-else-if="activeTab === 'unemployment'">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Unemployment Rate</h2>

                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <!-- Pie Chart: Distribution by Employment Status -->
                    <div class="bg-white rounded-xl shadow p-8 mb-10">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Distribution by Employment Status (Pie
                            Chart)</h3>
                        <VueECharts v-if="pieOption.series[0].data.length" :option="pieOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
                    </div>

                    <!-- Area Chart: Unemployment Rate Over Time -->
                    <div class="bg-white rounded-xl shadow p-8 mb-10">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Unemployment Rate Over Time (Area Chart)
                        </h3>
                        <VueECharts v-if="Array.isArray(unemploymentOverTime) && unemploymentOverTime.length"
                            :option="areaOption" :key="JSON.stringify(unemploymentOverTime)"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
                    </div>
                    <!-- Summary Block for Unemployment Rate -->
                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-5 rounded">
                        <div class="space-y-4 text-blue-900 text-sm leading-relaxed">
                            <div>
                                <p class="font-semibold mb-1">Employment Status Insights:</p>
                                <p v-if="employmentStatusSummary.hasData" v-html="employmentStatusSummary.narrative">
                                </p>
                                <p v-else class="text-gray-500">No status distribution data available.</p>
                            </div>
                            <div>
                                <p class="font-semibold mb-1">Unemployment Trend Analysis:</p>
                                <p v-if="unemploymentTrendSummary.hasData" v-html="unemploymentTrendSummary.narrative">
                                </p>
                                <p v-else class="text-gray-500">No unemployment trend data available.</p>
                            </div>
                            <div v-if="employmentStatusSummary.hasData && unemploymentTrendSummary.hasData"
                                class="pt-2 border-t border-blue-100">

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="activeTab === 'demandSupply'">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Demand-Supply Career Gap Map</h2>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Filter Demand-Supply Map
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select v-model="careerGapYear"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                                <option value="">All Years</option>
                                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <input v-model="roleFilter" type="text" placeholder="Filter by role..."
                                class="w-full border-gray-300 rounded-md shadow-sm px-2 py-1 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
                                list="role-suggestions" autocomplete="off" />
                            <datalist id="role-suggestions">
                                <option v-for="job in uniqueRoleSuggestions" :key="job" :value="job" />
                            </datalist>
                        </div>

                    </div>
                </div>

                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>

                <div v-else>
                    <!-- Table: In-Demand Careers vs Local Graduate Availability -->
                    <div class="bg-white rounded-xl shadow p-8 mb-10">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">In-Demand Careers vs Graduate Supply</h3>

                        <table class="min-w-full text-sm text-gray-800">
                            <thead>
                                <tr>
                                    <th class="px-2 py-1 text-left">Job</th>
                                    <th class="px-2 py-1 text-left">Category</th>
                                    <th class="px-2 py-1 text-left">Sector</th>
                                    <th class="px-2 py-1 text-left">Programs</th>
                                    <th class="px-2 py-1 text-left">Skills</th>
                                    <th class="px-2 py-1 text-left">Local Graduates</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="job in paginatedInDemandJobs"
                                    :key="job.role + '-' + job.program_ids.join('-')" class="hover:bg-gray-100">
                                    <td class="px-2 py-1">{{ job.role }}</td>
                                    <td class="px-2 py-1">{{ job.category }}</td>
                                    <td class="px-2 py-1">{{ job.sector }}</td>
                                    <td class="px-2 py-1">{{ job.programs.join(', ') }}</td>
                                    <td class="px-2 py-1">{{ job.skills ? job.skills.join(', ') : '' }}</td>
                                    <td class="px-2 py-1">{{ getLocalGraduateCount(job) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="careerGapTotalPages > 1" class="flex items-center justify-between mt-4">
                            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                :disabled="careerGapCurrentPage === 1" @click="careerGapCurrentPage--">Prev</button>
                            <span class="text-gray-700">Page {{ careerGapCurrentPage }} of {{ careerGapTotalPages
                            }}</span>
                            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                :disabled="careerGapCurrentPage === careerGapTotalPages"
                                @click="careerGapCurrentPage++">Next</button>
                        </div>
                    </div>

                    <!-- Bar Chart Visualization -->
                    <div class="bg-white rounded-xl shadow p-8 mb-10">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">Career Demand-Supply Map</h3>
                        <VueECharts :option="careerGapBarOption" style="height: 400px; width: 100%;" />
                    </div>
               

                <!-- Data Interpretation -->
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="text-blue-900 space-y-2">
                        <div>
                            <span class="font-semibold">Analytics Summary:</span>
                            <ul class="list-disc ml-6 space-y-1">
                                <li>
                                    <span class="font-semibold">Total Roles Analyzed:</span>
                                    {{ inDemandJobs.length }}
                                </li>
                                <li>
                                    <span class="font-semibold">Average Local Graduate Supply per Role:</span>
                                    {{
                                        (() => {
                                            const total = inDemandJobs.reduce((sum, job) => sum +
                                                getLocalGraduateCount(job),
                                                0);
                                            return inDemandJobs.length ? Math.round(total / inDemandJobs.length) : 0;
                                        })()
                                    }}
                                </li>
                                <li>
                                    <span class="font-semibold">Roles with Zero Local Supply:</span>
                                    {{
                                        inDemandJobs.filter(job => getLocalGraduateCount(job) === 0).length
                                    }}
                                </li>
                                <li>
                                    <span class="font-semibold">Role with Largest Gap:</span>
                                    {{
                                        (() => {
                                            const gaps = inDemandJobs.map(job => ({
                                                role: job.role,
                                                demand: job.demand,
                                                supply: getLocalGraduateCount(job),
                                                gap: job.demand - getLocalGraduateCount(job)
                                            }));
                                            const largest = gaps.sort((a, b) => b.gap - a.gap)[0];
                                            return largest ? `${largest.role} (Demand: ${largest.demand}, Supply:
                                    ${largest.supply})` :
                                                'N/A';
                                        })()
                                    }}
                                </li>
                                <li>
                                    <span class="font-semibold">Top 3 Roles by Local Graduate Supply:</span>
                                    {{
                                        (() => {
                                            const sorted = inDemandJobs
                                                .map(job => ({ role: job.role, supply: getLocalGraduateCount(job) }))
                                                .sort((a, b) => b.supply - a.supply)
                                                .slice(0, 3);
                                            return sorted.map(j => `${j.role} (${j.supply})`).join(', ') || 'N/A';
                                        })()
                                    }}
                                </li>
                                <li>
                                    <span class="font-semibold">Top In-Demand Career:</span>
                                    {{ topInDemandCareer ? `${topInDemandCareer.role} (${topInDemandCareer.supply})`
                                        :
                                        'N/A' }}
                                </li>
                            </ul>
                        </div>
                        <div>
                            <span>
                                <strong>Interpretation:</strong>
                                This analytics dashboard compares the demand for key job roles with the local
                                graduate
                                supply.
                                Roles with a high demand but low or zero local supply indicate potential talent
                                shortages and
                                opportunities for targeted upskilling or recruitment.
                                Conversely, roles with high supply may signal a need for career guidance or employer
                                engagement.
                                Use the table and chart above to identify which careers require the most attention
                                for
                                workforce
                                planning.
                            </span>
                        </div>
                    </div>
                </div>
                 </div>
            </div>

            <div v-else-if="activeTab === 'schoolwise'">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">School-Wise Employment Report</h2>

                <!-- FILTERS -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Institution</label>
                        <select v-model="schoolFilters.institution_id" class="rounded border-gray-300">
                            <option value="">All Institutions</option>
                            <option v-for="inst in schoolInstitutions" :key="inst.id" :value="inst.id">
                                {{ inst.institution_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
                        <select v-model="schoolFilters.program_id" class="rounded border-gray-300">
                            <option value="">All Programs</option>
                            <option v-for="prog in schoolPrograms" :key="prog.id" :value="prog.id">
                                {{ prog.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Timeline</label>
                        <input v-model="schoolFilters.timeline" type="month" class="rounded border-gray-300" />
                    </div>
                    <button @click="schoolApplyFilters" class="bg-blue-600 text-white px-4 py-2 rounded mt-6">
                        Apply
                    </button>
                </div>
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                            <span class="text-gray-500 text-sm">Total Hired Graduates</span>
                            <span class="text-3xl font-bold text-blue-600">{{ schoolTotalHired }}</span>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                            <span class="text-gray-500 text-sm">
                                {{ schoolFilters.institution_id ? 'Top Program' : 'Top School' }}
                            </span>
                            <span class="text-3xl font-bold text-green-600">{{ schoolTopSchool }}</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10 mb-10">
                        <h3 class="text-2xl font-bold text-gray-700">List of Hired Graduates</h3>
                        <div v-if="schoolPaginatedGraduates.length" class="mt-2">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Institution</th>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Job Title</th>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Company</th>
                                        <th class="px-2 py-1 text-left font-medium text-gray-700">Hired At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="grad in schoolPaginatedGraduates" :key="grad.name + grad.hired_at">
                                        <td class="px-2 py-1">{{ grad.name }}</td>
                                        <td class="px-2 py-1">{{ grad.institution }}</td>
                                        <td class="px-2 py-1">{{ grad.current_job_title }}</td>
                                        <td class="px-2 py-1">{{ grad.program }}</td>
                                        <td class="px-2 py-1">{{ grad.company_name }}</td>
                                        <td class="px-2 py-1">
                                            {{ grad.hired_at ? new Date(grad.hired_at).toLocaleDateString() : '' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination Controls -->
                            <div class="flex justify-center flex-wrap overflow-x-auto mt-4" v-if="schoolTotalPages > 1">
                                <button class="mx-1 px-3 py-1 rounded border bg-white text-blue-600"
                                    :disabled="schoolPage === 1" @click="schoolGoToPage(schoolPage - 1)">
                                    Prev
                                </button>
                                <template v-for="p in smartSchoolPages">
                                    <button v-if="p !== '...'" :key="p" :class="[
                                        'mx-1 px-3 py-1 rounded border',
                                        { 'bg-blue-600 text-white': p === schoolPage, 'bg-white text-blue-600': p !== schoolPage }
                                    ]" @click="schoolGoToPage(p)">{{ p }}</button>
                                    <span v-else :key="'ellipsis-' + Math.random()"
                                        class="mx-1 px-3 py-1 text-gray-400 select-none">...</span>
                                </template>
                                <button class="mx-1 px-3 py-1 rounded border bg-white text-blue-600"
                                    :disabled="schoolPage === schoolTotalPages" @click="schoolGoToPage(schoolPage + 1)">
                                    Next
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
                    </section>
                    <!-- Bar Chart -->
                    <div class="bg-white rounded-xl shadow p-8 mb-10">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700">
                            {{ schoolFilters.institution_id ? 'Hires by Program' : 'Hires by School' }}
                        </h3>
                        <div class="flex justify-center">
                            <VueECharts v-if="schoolDynamicChartLabels.length" :option="schoolChartOptions"
                                style="height: 450px; width: 100%; max-width: 100%;" />
                            <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
                        </div>
                        <div class="mt-6 bg-blue-50 border border-green-200 rounded-lg p-4 text-blue-900">
                            <p class="font-semibold mb-1">📊 Summary Insight:</p>
                            <p v-html="schoolEmployabilitySummaryInsight"></p>
                        </div>
                    </div>
                </div>
            </div>









        </Container>
    </AppLayout>
</template>