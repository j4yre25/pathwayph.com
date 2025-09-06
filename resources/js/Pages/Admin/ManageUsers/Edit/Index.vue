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
    router.visit(route('admin.manage_users.index'));
};
</script>

<template>
    <AppLayout title="Edit User">
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-user-edit text-blue-500 mr-2"></i> Edit User
                </h2>
            </div>
        </template>

        <Container class="py-8 space-y-8">
            <!-- User Info Card -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-cog text-white text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Edit User Information</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-medium text-gray-500 bg-white rounded-full px-3 py-1 shadow-sm border border-gray-200">ID: {{ user.id }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <form @submit.prevent="updateUser" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Name Field -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-user text-blue-500 mr-2"></i> Full Name
                                </label>
                                <input 
                                    type="text" 
                                    v-model="user.name" 
                                    id="name" 
                                    required 
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-blue-300"
                                    placeholder="Enter full name"
                                />
                            </div>
                            
                            <!-- Email Field -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-envelope text-blue-500 mr-2"></i> Email Address
                                </label>
                                <input 
                                    type="email" 
                                    v-model="user.email" 
                                    id="email" 
                                    required 
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-blue-300"
                                    placeholder="Enter email address"
                                />
                            </div>
                            
                            <!-- Role Field -->
                            <div class="space-y-2">
                                <label for="role" class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-user-tag text-blue-500 mr-2"></i> User Role
                                </label>
                                <select 
                                    v-model="user.role" 
                                    id="role" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-blue-300"
                                >
                                    <option value="" disabled>Select a role</option>
                                    <option value="peso">Peso</option>
                                    <option value="company">Company</option>
                                    <option value="institution">Institution</option>
                                </select>
                            </div>
                            
                            <!-- Approved Field -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-check-circle text-blue-500 mr-2"></i> Account Status
                                </label>
                                <div class="flex items-center space-x-4 mt-3">
                                    <div class="flex items-center bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <input 
                                            type="checkbox" 
                                            v-model="user.approved" 
                                            id="approved"
                                            class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-all duration-200"
                                        />
                                        <label for="approved" class="ml-3 text-sm font-medium text-gray-700">
                                            Account Approved
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <span 
                                            :class="{
                                                'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border-green-200': user.approved,
                                                'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border-red-200': !user.approved,
                                            }"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shadow-sm"
                                        >
                                            <i :class="user.approved ? 'fas fa-check-circle mr-1.5' : 'fas fa-times-circle mr-1.5'"></i>
                                            {{ user.approved ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-end pt-6 border-t border-gray-100 space-x-4">
                            <button 
                                type="button"
                                @click="goBack"
                                class="px-6 py-3 border border-gray-200 rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 text-sm font-medium transition-all duration-200 flex items-center shadow-sm"
                            >
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 text-sm font-medium transition-all duration-200 flex items-center shadow-md hover:shadow-lg"
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