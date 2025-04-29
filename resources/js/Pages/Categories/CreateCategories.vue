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

const props = defineProps ({
   categories: Array,
   sectors: Array,
   sector: Object

})


const sector = page.props.sector;
console.log(sector);


const categories = page.props.categories;
console.log('User ID:', page.props);

const form = useForm({
    name: '',
    sector_id: props.sector.id,
});

const createCategory = () => {
    console.log('sector ID:', form.sector_id)
    console.log('form:', form)
    form.post(route('categories.store', { sector: form.sector_id }), {
        onSuccess: () => {
            form.reset();
        }
    });
    
}

</script>


<template>
   <AppLayout>
        <template >
            Categories
        </template>
        


        <Container class="py-16">


            <!-- <PrimaryButton @click="createJob()" class="">Post Job</PrimaryButton> --> 
            <div class="mt-8">
                <FormSection @submitted="createCategory()">
                    <template #title>
                       Add a New Category
                    </template>

                    <template #description>
                        Fill in the details below to add a new category.
                    </template>

                    <template #form>
                        

                        <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="sector" value="Sector" />
                        <select
                            id="sector"
                            v-model="form.sector_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select a sector</option>
                            <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                {{ sector.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.sector_id" class="mt-2" />
                    </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Category Name" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.name" class="mt-2" />
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
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Add Category
                        </PrimaryButton>
                    </template>
                </FormSection>


            </div>
 
        </Container>

  

     

   
    </AppLayout>
</template>
