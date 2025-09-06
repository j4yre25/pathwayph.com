<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MySectors from './MySectors.vue';
import '@fortawesome/fontawesome-free/css/all.css';



const page = usePage()

const props = defineProps ({
    sectors: Array
})

const sectors = page.props.sectors;

// Function to go back to previous page
const goBack = () => {
    window.history.back();
};

// Stats for display
const totalSectors = sectors ? sectors.length : 0;
const activeSectors = sectors ? sectors.filter(sector => !sector.deleted_at).length : 0;
const inactiveSectors = sectors ? sectors.filter(sector => sector.deleted_at).length : 0;


const form = useForm({
    name: '',

});

const createSector = () => {
    form.post(route('sectors', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            form.reset();
        }
    });   
}
</script>


<template>
    <AppLayout title="Sectors">
        <template #header>
            <div class="flex items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-industry text-blue-500 mr-2"></i> Sectors
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Sectors -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-industry text-white text-xl"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Sectors</h3>
                        <p class="text-3xl font-bold text-blue-700">{{ totalSectors }}</p>
                    </div>
                </div>

                <!-- Active Sectors -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <h3 class="text-green-800 text-sm font-bold uppercase tracking-wide">Active Sectors</h3>
                        <p class="text-3xl font-bold text-green-900">{{ activeSectors }}</p>
                    </div>
                </div>

                <!-- Inactive Sectors -->
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-times-circle text-white text-xl"></i>
                         </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Inactive Sectors</h3>
                        <p class="text-3xl font-bold text-red-900">{{ inactiveSectors }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100 mb-8">
                <div class="flex items-center space-x-4">
                    <Link :href="route('sectors.list', { user: page.props.auth.user.id })" 
                        :class="[route().current('sectors.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                        <i class="fas fa-list mr-2"></i>
                        <span>List of Sectors</span>
                    </Link>
                    <Link v-if="page.props.roles.isPeso" 
                        :href="route('sectors.archivedlist', { user: page.props.auth.user.id })" 
                        :class="[route().current('sectors.archivedlist') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                        <i class="fas fa-archive mr-2"></i>
                        <span>Archived Sectors</span>
                    </Link>
                </div>
                <Link :href="route('sectors.create', { user: page.props.auth.user.id })" 
                    :class="[route().current('sectors.create') ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600',
                        'px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm']">
                    <i class="fas fa-plus mr-2"></i> Add Sectors
                </Link>
            </div>

            <!-- Sectors List -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-building text-white"></i>
                    </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">My Sectors</h3>
                            <p class="text-sm text-gray-500">View and manage your sector assignments</p>
                        </div>
                    </div>
                </div>
                    <MySectors :sectors="sectors" />
            </div> 
        </Container>
    </AppLayout>
</template>