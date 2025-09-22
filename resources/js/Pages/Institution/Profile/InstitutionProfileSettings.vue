<template>
    <AppLayout title="Institution Profile Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Institution Profile Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-medium text-gray-900">
                            Institution Profile Settings
                        </h1>
                        <p class="mt-6 text-gray-500 leading-relaxed">
                            Update your institution's profile information, contact details, and social media links.
                        </p>
                    </div>

                    <div class="bg-gray-200 bg-opacity-25 p-6 lg:p-8">
                        <form @submit.prevent="updateSettings" enctype="multipart/form-data">
                            <!-- Basic Information Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                
                                <!-- Institution Name -->
                                <div class="mb-4">
                                    <label for="institution_name" class="block text-sm font-medium text-gray-700">Institution Name *</label>
                                    <input
                                        id="institution_name"
                                        v-model="form.institution_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.institution_name }"
                                        required
                                    />
                                    <div v-if="form.errors.institution_name" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.institution_name }}
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.email }"
                                        required
                                    />
                                    <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <!-- Contact Number -->
                                <div class="mb-4">
                                    <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number *</label>
                                    <input
                                        id="contact_number"
                                        v-model="form.contact_number"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.contact_number }"
                                        required
                                    />
                                    <div v-if="form.errors.contact_number" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.contact_number }}
                                    </div>
                                </div>

                                <!-- Telephone Number -->
                                <div class="mb-4">
                                    <label for="telephone_number" class="block text-sm font-medium text-gray-700">Telephone Number</label>
                                    <input
                                        id="telephone_number"
                                        v-model="form.telephone_number"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.telephone_number }"
                                    />
                                    <div v-if="form.errors.telephone_number" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.telephone_number }}
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.description }"
                                        placeholder="Describe your institution..."
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <!-- Website -->
                                <div class="mb-4">
                                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                                    <input
                                        id="website"
                                        v-model="form.website"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.website }"
                                        placeholder="https://example.com"
                                    />
                                    <div v-if="form.errors.website" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.website }}
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media Links Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Social Media Links</h3>
                                
                                <!-- Indeed Profile -->
                                <div class="mb-4">
                                    <label for="indeed_profile" class="block text-sm font-medium text-gray-700">Indeed Profile</label>
                                    <input
                                        id="indeed_profile"
                                        v-model="form.indeed_profile"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.indeed_profile }"
                                        placeholder="https://indeed.com/company/..."
                                    />
                                    <div v-if="form.errors.indeed_profile" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.indeed_profile }}
                                    </div>
                                </div>

                                <!-- Facebook -->
                                <div class="mb-4">
                                    <label for="facebook" class="block text-sm font-medium text-gray-700">Facebook</label>
                                    <input
                                        id="facebook"
                                        v-model="form.facebook"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.facebook }"
                                        placeholder="https://facebook.com/..."
                                    />
                                    <div v-if="form.errors.facebook" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.facebook }}
                                    </div>
                                </div>

                                <!-- Twitter -->
                                <div class="mb-4">
                                    <label for="twitter" class="block text-sm font-medium text-gray-700">Twitter</label>
                                    <input
                                        id="twitter"
                                        v-model="form.twitter"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.twitter }"
                                        placeholder="https://twitter.com/..."
                                    />
                                    <div v-if="form.errors.twitter" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.twitter }}
                                    </div>
                                </div>

                                <!-- LinkedIn -->
                                <div class="mb-4">
                                    <label for="linkedin" class="block text-sm font-medium text-gray-700">LinkedIn</label>
                                    <input
                                        id="linkedin"
                                        v-model="form.linkedin"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.linkedin }"
                                        placeholder="https://linkedin.com/company/..."
                                    />
                                    <div v-if="form.errors.linkedin" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.linkedin }}
                                    </div>
                                </div>

                                <!-- Instagram -->
                                <div class="mb-4">
                                    <label for="instagram" class="block text-sm font-medium text-gray-700">Instagram</label>
                                    <input
                                        id="instagram"
                                        v-model="form.instagram"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.instagram }"
                                        placeholder="https://instagram.com/..."
                                    />
                                    <div v-if="form.errors.instagram" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.instagram }}
                                    </div>
                                </div>
                            </div>

                            <!-- Logo Upload Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Institution Logo</h3>
                                
                                <div class="mb-4">
                                    <label for="logo" class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                    <input
                                        id="logo"
                                        ref="logoInput"
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        :class="{ 'border-red-500': form.errors.logo }"
                                        @change="handleLogoUpload"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">JPG, JPEG, PNG up to 1MB</p>
                                    <div v-if="form.errors.logo" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.logo }}
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Change Password Section -->
                        <div class="bg-gray-200 bg-opacity-25 p-6 lg:p-8 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Password & Security</h3>
                                    <p class="text-sm text-gray-600">Update your password to keep your account secure</p>
                                </div>
                                <button
                                    @click="showPasswordModal = true"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <i class="fas fa-key mr-2"></i>
                                    Change Password
                                </button>
                            </div>
                        </div>

                        <!-- Change Password Modal -->
                        <div v-if="showPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                                <div class="mt-3">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                                        <button @click="closePasswordModal" class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <form @submit.prevent="updatePassword" class="space-y-6">
                                        <!-- Current Password -->
                                        <div>
                                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                            <div class="mt-1 relative">
                                                <input
                                                    id="current_password"
                                                    v-model="passwordForm.current_password"
                                                    type="password"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :class="{ 'border-red-500': passwordForm.errors.current_password }"
                                                    required
                                                />
                                                <i class="fas fa-lock absolute right-3 top-3 text-gray-400"></i>
                                            </div>
                                            <div v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">
                                                {{ passwordForm.errors.current_password }}
                                            </div>
                                        </div>

                                        <!-- New Password -->
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                            <div class="mt-1 relative">
                                                <input
                                                    id="password"
                                                    v-model="passwordForm.password"
                                                    type="password"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :class="{ 'border-red-500': passwordForm.errors.password }"
                                                    required
                                                />
                                                <i class="fas fa-key absolute right-3 top-3 text-gray-400"></i>
                                            </div>
                                            <div v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">
                                                {{ passwordForm.errors.password }}
                                            </div>
                                        </div>

                                        <!-- Password Strength Indicator -->
                                        <div v-if="passwordForm.password" class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-600">Password Strength:</span>
                                                <span class="text-sm font-medium" :class="{
                                                    'text-red-600': passwordStrength === 1,
                                                    'text-yellow-600': passwordStrength === 2,
                                                    'text-green-600': passwordStrength === 3
                                                }">{{ passwordStrengthText }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div 
                                                    class="h-2 rounded-full transition-all duration-300" 
                                                    :class="passwordStrengthColor"
                                                    :style="{ width: (passwordStrength / 3) * 100 + '%' }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Password Requirements -->
                                        <div v-if="showPasswordRequirements" class="bg-gray-50 p-4 rounded-md">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">Password Requirements:</h4>
                                            <ul class="space-y-1 text-sm">
                                                <li class="flex items-center" :class="passwordValidation.minLength ? 'text-green-600' : 'text-red-600'">
                                                    <i :class="passwordValidation.minLength ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                                                    At least 8 characters
                                                </li>
                                                <li class="flex items-center" :class="passwordValidation.hasUppercase ? 'text-green-600' : 'text-red-600'">
                                                    <i :class="passwordValidation.hasUppercase ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                                                    One uppercase letter
                                                </li>
                                                <li class="flex items-center" :class="passwordValidation.hasLowercase ? 'text-green-600' : 'text-red-600'">
                                                    <i :class="passwordValidation.hasLowercase ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                                                    One lowercase letter
                                                </li>
                                                <li class="flex items-center" :class="passwordValidation.hasNumber ? 'text-green-600' : 'text-red-600'">
                                                    <i :class="passwordValidation.hasNumber ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                                                    One number
                                                </li>
                                                <li class="flex items-center" :class="passwordValidation.hasSpecialChar ? 'text-green-600' : 'text-red-600'">
                                                    <i :class="passwordValidation.hasSpecialChar ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                                                    One special character
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                            <div class="mt-1 relative">
                                                <input
                                                    id="password_confirmation"
                                                    v-model="passwordForm.password_confirmation"
                                                    type="password"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :class="{ 
                                                        'border-red-500': passwordForm.password_confirmation && !passwordsMatch,
                                                        'border-green-500': passwordForm.password_confirmation && passwordsMatch
                                                    }"
                                                    required
                                                />
                                                <i class="fas fa-check-double absolute right-3 top-3" :class="{
                                                    'text-green-500': passwordsMatch && passwordForm.password_confirmation,
                                                    'text-gray-400': !passwordForm.password_confirmation,
                                                    'text-red-500': passwordForm.password_confirmation && !passwordsMatch
                                                }"></i>
                                            </div>
                                            <div v-if="passwordForm.password_confirmation && !passwordsMatch" class="mt-2 text-sm text-red-600">
                                                Passwords do not match
                                            </div>
                                            <div v-if="passwordsMatch && passwordForm.password_confirmation" class="mt-2 text-sm text-green-600">
                                                Passwords match
                                            </div>
                                        </div>

                                        <!-- Modal Actions -->
                                        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                                            <button
                                                @click="closePasswordModal"
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                :class="{ 'opacity-25': passwordForm.processing || !validatePasswordForm() }"
                                                :disabled="passwordForm.processing || !validatePasswordForm()"
                                            >
                                                <span v-if="passwordForm.processing" class="flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Updating...
                                                </span>
                                                <span v-else>Update Password</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Password Success Modal -->
                        <div v-if="showPasswordSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="mt-3 text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                        <i class="fas fa-check text-green-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Password Updated Successfully!</h3>
                                    <div class="mt-2 px-7 py-3">
                                        <p class="text-sm text-gray-500">Your password has been updated successfully.</p>
                                    </div>
                                    <div class="items-center px-4 py-3">
                                        <button
                                            @click="showPasswordSuccessModal = false"
                                            class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300"
                                        >
                                            OK
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Error Modal -->
                        <div v-if="showPasswordErrorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="mt-3 text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Password Update Failed</h3>
                                    <div class="mt-2 px-7 py-3">
                                        <p class="text-sm text-gray-500">{{ passwordErrorMessage }}</p>
                                    </div>
                                    <div class="items-center px-4 py-3">
                                        <button
                                            @click="showPasswordErrorModal = false"
                                            class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
                                        >
                                            OK
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="updateSettings" enctype="multipart/form-data">
                            <!-- Submit Button -->
                            <div class="flex items-center justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Updating...</span>
                                    <span v-else>Update Settings</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Success!</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Institution profile settings updated successfully!</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button
                            @click="showSuccessModal = false"
                            class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message Modal -->
        <div v-if="showErrorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Error!</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">{{ errorMessage }}</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button
                            @click="showErrorModal = false"
                            class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import '@fortawesome/fontawesome-free/css/all.css'

// Props
const props = defineProps({
    institution: Object,
    flash: Object
})

// Reactive data
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorMessage = ref('')
const logoInput = ref(null)

// Form setup using Inertia's useForm
const form = useForm({
    institution_name: '',
    email: '',
    contact_number: '',
    telephone_number: '',
    description: '',
    website: '',
    indeed_profile: '',
    facebook: '',
    twitter: '',
    linkedin: '',
    instagram: '',
    logo: null
})

// Initialize form with institution data
onMounted(() => {
    if (props.institution) {
        form.institution_name = props.institution.institution_name || ''
        form.email = props.institution.email || ''
        form.contact_number = props.institution.contact_number || ''
        form.telephone_number = props.institution.telephone_number || ''
        form.description = props.institution.description || ''
        form.website = props.institution.website || ''
        
        // Initialize social links
        if (props.institution.social_links) {
            form.indeed_profile = props.institution.social_links.indeed_profile || ''
            form.facebook = props.institution.social_links.facebook || ''
            form.twitter = props.institution.social_links.twitter || ''
            form.linkedin = props.institution.social_links.linkedin || ''
            form.instagram = props.institution.social_links.instagram || ''
        }
    }

    // Check for flash messages
    if (props.flash?.success) {
        showSuccessModal.value = true
    }
    if (props.flash?.error) {
        errorMessage.value = props.flash.error
        showErrorModal.value = true
    }
})

// Handle logo upload
const handleLogoUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.logo = file
    }
}

// Submit form
const updateSettings = () => {
    form.post(route('institution.profile.settings.update'), {
        onSuccess: () => {
            showSuccessModal.value = true
            // Reset logo input
            if (logoInput.value) {
                logoInput.value.value = ''
            }
            form.logo = null
        },
        onError: (errors) => {
            console.error('Form errors:', errors)
            if (Object.keys(errors).length > 0) {
                errorMessage.value = 'Please check the form for errors and try again.'
                showErrorModal.value = true
            }
        }
    })
}

// Password Management
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: ''
})

// Password validation refs
const passwordValidation = ref({
    minLength: false,
    hasUppercase: false,
    hasLowercase: false,
    hasNumber: false,
    hasSpecialChar: false
})

const passwordsMatch = ref(false)
const showPasswordRequirements = ref(false)
const showPasswordModal = ref(false)
const showPasswordSuccessModal = ref(false)
const showPasswordErrorModal = ref(false)
const passwordErrorMessage = ref('')

// Watch for password changes to validate in real-time
watch(() => passwordForm.password, (newPassword) => {
    if (newPassword) {
        passwordValidation.value = {
            minLength: newPassword.length >= 8,
            hasUppercase: /[A-Z]/.test(newPassword),
            hasLowercase: /[a-z]/.test(newPassword),
            hasNumber: /\d/.test(newPassword),
            hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword)
        }
        showPasswordRequirements.value = true
    } else {
        showPasswordRequirements.value = false
    }
    
    // Check if passwords match
    passwordsMatch.value = passwordForm.password === passwordForm.password_confirmation && passwordForm.password_confirmation !== ''
})

watch(() => passwordForm.password_confirmation, () => {
    passwordsMatch.value = passwordForm.password === passwordForm.password_confirmation && passwordForm.password_confirmation !== ''
})

// Computed properties for password strength
const passwordStrength = computed(() => {
    const validations = Object.values(passwordValidation.value)
    const validCount = validations.filter(Boolean).length
    
    if (validCount === 0) return 0
    if (validCount <= 2) return 1
    if (validCount <= 4) return 2
    return 3
})

const passwordStrengthText = computed(() => {
    switch (passwordStrength.value) {
        case 0: return ''
        case 1: return 'Weak'
        case 2: return 'Medium'
        case 3: return 'Strong'
        default: return ''
    }
})

const passwordStrengthColor = computed(() => {
    switch (passwordStrength.value) {
        case 1: return 'bg-red-500'
        case 2: return 'bg-yellow-500'
        case 3: return 'bg-green-500'
        default: return 'bg-gray-300'
    }
})

// Validate password form
const validatePasswordForm = () => {
    const validations = Object.values(passwordValidation.value)
    const allValid = validations.every(Boolean)
    return allValid && passwordsMatch.value && passwordForm.current_password
}

// Update password
const updatePassword = () => {
    if (!validatePasswordForm()) {
        passwordErrorMessage.value = 'Please ensure all password requirements are met.'
        showPasswordErrorModal.value = true
        return
    }

    passwordForm.put(route('user-password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset()
            showPasswordModal.value = false
            showPasswordSuccessModal.value = true
            passwordValidation.value = {
                minLength: false,
                hasUppercase: false,
                hasLowercase: false,
                hasNumber: false,
                hasSpecialChar: false
            }
            passwordsMatch.value = false
            showPasswordRequirements.value = false
        },
        onError: (errors) => {
            console.error('Password update errors:', errors)
            if (errors.current_password) {
                passwordErrorMessage.value = errors.current_password
            } else if (errors.password) {
                passwordErrorMessage.value = errors.password
            } else {
                passwordErrorMessage.value = 'Failed to update password. Please try again.'
            }
            showPasswordErrorModal.value = true
        }
    })
}

// Close password modal
const closePasswordModal = () => {
    showPasswordModal.value = false
    passwordForm.reset()
    passwordValidation.value = {
        minLength: false,
        hasUppercase: false,
        hasLowercase: false,
        hasNumber: false,
        hasSpecialChar: false
    }
    passwordsMatch.value = false
    showPasswordRequirements.value = false
}
</script>