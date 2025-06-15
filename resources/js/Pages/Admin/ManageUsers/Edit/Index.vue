<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Container from '@/Components/Container.vue';
    import SectionBorder from '@/Components/SectionBorder.vue';
    import { ref } from 'vue';
    import { usePage, router } from '@inertiajs/vue3';
    import PrimaryButton from '@/Components/PrimaryButton.vue';

    const props = defineProps({
        user: Object
    });

    const updateUser = () => {
        router.put(route('admin.manage_users.update', { user: props.user.id }), props.user);
    };

</script>

<template>
    <AppLayout title="Edit User">
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="router.get(route('admin.manage_users.index'))"
                    class="mr-3 p-1 rounded-full hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50"
                >
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </button>
                <i class="fas fa-user-edit text-gray-700 mr-2"></i>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User #{{ user.id }}</h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Edit User Form Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-user-cog text-gray-500 mr-2"></i>
                        User Information
                    </h3>
                </div>
                
                <div class="p-6">
                    <form @submit.prevent="updateUser" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-user text-gray-400 mr-1"></i> Name
                                </label>
                                <input 
                                    type="text" 
                                    v-model="user.name" 
                                    id="name" 
                                    required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                />
                            </div>
                            
                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-envelope text-gray-400 mr-1"></i> Email
                                </label>
                                <input 
                                    type="email" 
                                    v-model="user.email" 
                                    id="email" 
                                    required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                />
                            </div>
                            
                            <!-- Role Field -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-user-tag text-gray-400 mr-1"></i> Role
                                </label>
                                <select 
                                    v-model="user.role" 
                                    id="role" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                                    <option value="graduate">Graduate</option>
                                    <option value="company">Company</option>
                                    <option value="institution">Institution</option>
                                </select>
                            </div>
                            
                            <!-- Approved Field -->
                            <div class="flex items-center">
                                <div class="flex items-center h-full pt-6">
                                    <input 
                                        type="checkbox" 
                                        v-model="user.approved" 
                                        id="approved"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label for="approved" class="ml-2 block text-sm font-medium text-gray-700">
                                        <i class="fas fa-check-circle text-gray-400 mr-1"></i> Approved
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                            >
                                <i class="fas fa-save mr-2"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>