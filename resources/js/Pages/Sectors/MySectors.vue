<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue'
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    sectors: Array,
    sector: Object
});

const selectedSector = ref(null);

console.log('props', props)
const archiveSector= () => {
  if (selectedSector.value) {
        router.delete(route('sectors.delete', { sector: selectedSector.value.id }));
        open.value = false; 
        selectedSector.value = null; 
    }
};

const confirmArchive = (sector) => {
    selectedSector.value = sector;
    open.value = true; 
};

const open = ref(false)


</script>

<template>

    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200">
        <thead>
          <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
            <th class="border border-gray-200 px-6 py-3 text-left">ID</th>
            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          <tr v-for="sector in sectors" :key="sector.id" class="border-b border-gray-200 hover:bg-gray-100">
            <td class="border border-gray-200 px-6 py-4">{{ sector.id }}</td>
            <td class="border border-gray-200 px-6 py-4">{{ sector.name }}</td>
            <td class="border border-gray-200 px-6 py-4">
            <Link :href="route('sectors.edit', { sector: sector.id })">
              <PrimaryButton class="mr-2">Edit</PrimaryButton>
            </Link>
            <DangerButton @click ="confirmArchive(sector)">
                Archive Sector

            </DangerButton>
        
          </td>
          </tr>
        </tbody>
      </table>
    </div>
    <ConfirmationModal @close="open = false" :show="open">
        <template #title>
            Are you sure?
        </template>

        <template #content>
            Are you sure you want to archive this sector #{{ selectedSector?.id }} {{ selectedSector?.name }}?
        </template>

        <template #footer>
            <DangerButton @click="archiveSector" class="mr-2">
                Archive Sector
            </DangerButton>
            <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
    </ConfirmationModal>
 

</template>