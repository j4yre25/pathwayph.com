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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Sectors</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ totalSectors }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Active Sectors</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ activeSectors }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Inactive Sectors</h3>
                    <p class="text-3xl font-bold text-red-600">
                        {{ inactiveSectors }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
                <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Manage Sectors</h3>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                        <Link :href="route('sectors.create', { user: page.props.auth.user.id })" 
                              class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Add Sectors
                        </Link>
                        <Link :href="route('sectors.list', { user: page.props.auth.user.id })" 
                              class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                            <i class="fas fa-list text-blue-500 mr-2"></i> All Sectors
                        </Link>
                        <Link v-if="page.props.roles.isPeso" 
                              :href="route('sectors.archivedlist', { user: page.props.auth.user.id })" 
                              :active="route().current('sectors.archivedlist', { user: page.props.auth.user.id })"
                              class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                            <i class="fas fa-archive text-gray-500 mr-2"></i> Archived Sectors
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sectors List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">My Sectors</h3>
                </div>
                <div class="p-4">
                    <MySectors :sectors="sectors" />
                </div>
            </div>

 
        </Container>

  

     

    </AppLayout>
 
</template>