<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    sector: Object

})

console.log('props', props.sector)


const open = ref(false)

const archiveSector= (r) => {
    router.delete(route('sectors.delete', { sector: props.sector.id }));
};

</script>

<template>
    <ActionSection>
        <template #title>
            Archive Sector
        </template>

        <template #description>
                This section to archive your sector
        </template>

        <template #content>
            <DangerButton @click ="open = true">
                Archive Sector

            </DangerButton>

            

            
         

            <ConfirmationModal @close="open = false" :show="open">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                        Are you sure you want to archive this sector #{{ props.sector.id }} {{ props.sector.name }}
                </template>

                <template #footer>
                        <DangerButton @click="archiveSector()" class="mr-2">
                            Archive Sector
                        </DangerButton>
                        <SecondaryButton @click="open = false">Cancel</SecondaryButton>
                        
                </template>

            </ConfirmationModal>
        </template>



    </ActionSection>


</template>