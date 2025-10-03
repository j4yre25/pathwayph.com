<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();

const referrals = computed(() => props.referrals.data || []);
const filters = ref({
    status: props.filters?.status || '',
    company: props.filters?.company || '',
    search: props.filters?.search || '',
});

function applyFilters() {
    router.get(route('graduate.referrals'), filters.value, { preserveState: true, replace: true });
}

function resetFilters() {
    filters.value = { status: '', company: '', search: '' };
    applyFilters();
}

function downloadCertificate(path) {
    if (path) {
        window.open(`/storage/${path}`, '_blank');
    }
}

function viewCertificate(referralId) {
    window.open(route('referral.certificate.view', referralId), '_blank');
}
</script>

<template>
    <AppLayout title="My Job Referrals">
        <div class="py-8 max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    <h3 class="font-medium text-gray-700">Filter Referrals</h3>
                    <div class="ml-auto">
                        <button type="button" @click="resetFilters"
                            class="px-6 py-2 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 border border-gray-300">
                            <i class="fas fa-undo mr-2"></i> Reset Filter
                        </button>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <select v-model="filters.status" class="border rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Declined</option>
                    </select>
                    <input v-model="filters.company" type="text" placeholder="Company name"
                        class="border rounded px-3 py-2" />
                    <input v-model="filters.search" type="text" placeholder="Search..." class="border rounded px-3 py-2"
                        @keyup.enter="applyFilters" />
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-blue-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
                        <tr>
                            <th class="px-6 py-4">Job Title</th>
                            <th class="px-6 py-4">Company</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Referred At</th>
                            <th class="px-6 py-4">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ref in referrals" :key="ref.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ ref.job_title }}</td>
                            <td class="px-6 py-4">{{ ref.company }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                                    'bg-green-100 text-green-800': ref.status === 'success',
                                    'bg-yellow-100 text-yellow-800': ref.status === 'pending',
                                    'bg-red-100 text-red-800': ref.status === 'rejected'
                                }">
                                    {{ ref.status.charAt(0).toUpperCase() + ref.status.slice(1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ ref.referred_at }}</td>
                            <td class="px-6 py-4">
                                <button v-if="ref.certificate_path" @click="viewCertificate(ref.id)"
                                    class="text-blue-600 underline">
                                    View Certificate
                                </button>
                                <span v-else class="text-gray-400 italic">Not available</span>
                            </td>
                        </tr>
                        <tr v-if="referrals.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No referrals found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>