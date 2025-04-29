<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    category: Object

})



const open = ref(false)

const archiveCategory= (r) => {
    router.delete(route('categories.delete', { category: props.category.id }));
};

</script>

<template>
    <ActionSection>
        <template #title>
            Archive Category
        </template>

        <template #description>
                This section to archive your category
        </template>

        <template #content>
            <DangerButton @click ="open = true">
                Archive Category

            </DangerButton>

            <ConfirmationModal @close="open = false" :show="open">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                        Are you sure you want to archive this category #{{ category.id }} {{ category.name }}
                </template>

                <template #footer>
                        <DangerButton @click="archiveCategory()" class="mr-2">
                            Archive Category
                        </DangerButton>
                        <SecondaryButton @click="open = false">Cancel</SecondaryButton>
                </template>

            </ConfirmationModal>
        </template>



    </ActionSection>


</template>