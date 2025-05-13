<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Container from '@/Components/Container.vue';
    // import UpdateForm from './UpdateForm.vue';
    // import DeleteForm from './DeleteForm.vue';
    import SectionBorder from '@/Components/SectionBorder.vue';
    import { ref } from 'vue';
    import { usePage, router } from '@inertiajs/vue3';
   


    const props = defineProps({
        user: Object
    })

    const updateUser  = () => {
    router.put(route('admin.manage_users.update', { user: props.user.id }), props.user);
};

</script>

<template>
    <AppLayout title="Edit User">
        <template #header>
           Edit #{{ user.id }}


            <Container class="py-16">
                <form @submit.prevent="updateUser ">
                <div>
                    <label for="name">Name</label>
                    <input type="text" v-model ="user.name" id="name" required />
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" v-model="user.email" id="email" required />
                </div>
                <div>
                    <label for="role">Role</label>
                    <select v-model="user.role" id="role" required>
                        <option value="graduate">Graduate</option>
                        <option value="company">Company</option>
                        <option value="institution">Institution</option>
                    </select>
                </div>
                <div>
                    <label for="approved">Approved</label>
                    <input type="checkbox" v-model="user.approved" id="approved" />
                </div>
                <PrimaryButton type="submit">Update User</PrimaryButton>
            </form>

            </Container>


        </template>

    </AppLayout>

</template>