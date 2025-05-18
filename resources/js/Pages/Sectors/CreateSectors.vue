<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';


const page = usePage()

const props = defineProps({
    sectors: Array
})

console.log('User ID:', page.props);

const form = useForm({
    name: '',
    division: '',
});

const divisionOptions = [
    "01-03",
    "05-09",
    "10-33",
    "35",
    "36-39",
    "41-43",
    "45-47",
    "49-53",
    "55-56",
    "58-63",
    "64-66",
    "68",
    "69-75",
    "77-82",
    "84",
    "85",
    "86-88",
    "90-93",
    "94-96",
    "98-98",
    "99"
];

const createSector = () => {
    form.post(route('sectors.store', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            form.reset();
        }
    });

}

</script>


<template>
    <AppLayout>
        <template>
            Sectors
        </template>



        <Container class="py-16">


            <!-- <PrimaryButton @click="createJob()" class="">Post Job</PrimaryButton> -->
            <div class="mt-8">
                <FormSection @submitted="createSector()">
                    <template #title>
                        Add a New Sector
                    </template>

                    <template #description>
                        Fill in the details below to add a new sector.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Sector Name" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <InputLabel for="division" value="Division" />
                            <select id="division" v-model="form.division"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" disabled>Select a division</option>
                                <option v-for="div in divisionOptions" :key="div" :value="div">
                                    {{ div }}
                                </option>
                            </select>
                            <InputError :message="form.errors.division" class="mt-2" />
                        </div>



                        <!-- <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="from_datetime" value="From Date and Time" />
                            <TextInput id="from_datetime" v-model="form.from_datetime" type="datetime-local" class="mt-1 block w-full" />
                            <InputError :message="form.errors.from_datetime" class="mt-2" />
                        </div> -->

                        <!-- <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="to_datetime" value="To Date and Time" />
                            <TextInput id="to_datetime" v-model="form.to_datetime" type="datetime-local" class="mt-1 block w-full" />
                            <InputError :message="form.errors.to_datetime" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="location" value="Location" />
                            <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.location" class="mt-2" />
                        </div> -->
                    </template>
                    <template #actions>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            Post Sectors
                        </PrimaryButton>
                    </template>
                </FormSection>


            </div>

        </Container>






    </AppLayout>
</template>
