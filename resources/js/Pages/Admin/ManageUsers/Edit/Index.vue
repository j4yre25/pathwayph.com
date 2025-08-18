<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Container from '@/Components/Container.vue';
    import SectionBorder from '@/Components/SectionBorder.vue';
    import { ref } from 'vue';
    import { usePage, router } from '@inertiajs/vue3';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import '@fortawesome/fontawesome-free/css/all.css';

    const props = defineProps({
        user: Object
    });

    const updateUser = () => {
        router.put(route('admin.manage_users.update', { user: props.user.id }), props.user);
    };

    // Function to go back
    const goBack = () => {
        router.get(route('admin.manage_users.index'));
    };
</script>

<template>
    <AppLayout title="Edit User">
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-user-edit text-blue-500 mr-2"></i> Edit User
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- User Info Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-4 flex items-center justify-between border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-user-cog text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">User Information</h3>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">ID: {{ user.id }}</span>
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-check-circle text-gray-400 mr-1"></i> Status
                                </label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            v-model="user.approved" 
                                            id="approved"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <label for="approved" class="ml-2 text-sm text-gray-700">
                                            Approved
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <button 
                                type="button"
                                @click="goBack"
                                class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm transition-colors duration-200 flex items-center"
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