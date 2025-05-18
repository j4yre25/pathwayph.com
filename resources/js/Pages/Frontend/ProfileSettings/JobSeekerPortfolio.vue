<script setup>

</script>

<template>
    <div class="flex flex-col lg:flex-row lg:space-x-8">
        <!-- Profile Picture Section -->
        <div class="w-full lg:w-1/3 mb-8 lg:mb-0">
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
                <h2 class="text-xl font-semibold mb-2">Profile Picture</h2>
                <p class="text-gray-600 mb-4">Update your profile picture</p>
                <div class="flex flex-col items-center">
                    <div class="relative mb-4">
                        <img
                            id="profile-picture"
                            :src="profile.graduate_picture_url"
                            alt="Profile picture"
                            class="rounded-full w-48 h-48 object-cover border border-gray-200 shadow-sm"/>
                        <div class="absolute bottom-0 right-0">
                            <label 
                            for="file-input" 
                            class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white rounded-full p-2 cursor-pointer shadow-md">
                            <i class="fas fa-camera"></i>
                            </label>
                        </div>
                    </div>
                    <input
                    type="file"
                    id="file-input"
                    class="hidden"
                    accept="image/*"
                    @change="onFileChange"
                    disabled/>
                    <label for="file-input" class="text-indigo-600 hover:text-indigo-800 transition-colors font-medium cursor-pointer">
                    Choose an image
                    </label>
                </div>
            </div>
        
             <!-- Contact and Social Links Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h2 class="text-xl font-semibold mb-2">Contact and Social Links</h2>
                <p class="text-gray-600 mb-4">Add your professional networks and contact options</p>
          
                <div class="space-y-4">
                <!-- LinkedIn URL -->
                    <div class="relative">
                        <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
                        <div class="relative">
                            <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="url"
                                id="linkedin-url"
                                class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                                :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.linkedin_url}"
                                v-model="profile.linkedin_url"
                                placeholder="https://linkedin.com/in/yourprofile"
                                disabled/>
                        </div>
                        <div v-if="settingsForm.errors.linkedin_url" class="text-red-500 text-sm mt-1">
                            {{ settingsForm.errors.linkedin_url }}
                        </div>
                    </div>
                    
                    <!-- GitHub URL -->
                    <div class="relative">
                        <label for="github-url" class="block text-gray-700 font-medium mb-1">GitHub Profile</label>
                        <div class="relative">
                            <i class="fab fa-github absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                            type="url"
                            id="github-url"
                            class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.github_url}"
                            v-model="profile.github_url"
                            placeholder="https://github.com/yourusername"
                            disabled
                            />
                        </div>
                        <div v-if="settingsForm.errors.github_url" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.github_url }}
                        </div>
                    </div>
            
                    <!-- Personal Website -->
                    <div class="relative">
                        <label for="personal-website" class="block text-gray-700 font-medium mb-1">Personal Website</label>
                        <div class="relative">
                            <i class="fas fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                            type="url"
                            id="personal-website"
                            class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.personal_website}"
                            v-model="profile.personal_website"
                            placeholder="https://yourwebsite.com"
                            disabled/>
                    </div>
                    <div v-if="settingsForm.errors.personal_website" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.personal_website }}
                    </div>
                </div>
            
                <!-- Other Professional Networks -->
                <div class="relative">
                    <label for="other-social-links" class="block text-gray-700 font-medium mb-1">Other Professional Networks</label>
                    <div class="relative">
                        <i class="fas fa-share-alt absolute left-3 top-3 text-gray-400"></i>
                        <textarea
                            id="other-social-links"
                            class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            rows="2"
                            v-model="profile.other_social_links"
                            placeholder="Add other professional networks (one per line)"
                            disabled>
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
      <!-- Profile Information Section -->
    <div class="w-full lg:w-2/3">
        <form @submit.prevent="saveProfile" class="space-y-6">
          <!-- Personal Information Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h2 class="text-xl font-semibold mb-2">Personal Information</h2>
                <p class="text-gray-600 mb-4">Basic details about you</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <!-- Full Name -->
                    <div class="relative">
                        <label for="full-name" class="block text-gray-700 font-medium mb-1">Full Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="text"
                                id="full-name"
                                class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                                :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.graduate_first_name || settingsForm.errors.graduate_last_name}"
                                v-model="profile.fullName"
                                placeholder="Enter your full name"
                                disabled/>
                        </div>
                        <div v-if="settingsForm.errors.graduate_first_name" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.graduate_first_name }}
                        </div>
                        <div v-if="settingsForm.errors.graduate_last_name" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.graduate_last_name }}
                        </div>
                    </div>
  
                    <!-- Employment Status -->
                    <div class="relative">
                        <label for="employment-status" class="block text-gray-700 font-medium mb-1">Employment Status</label>
                        <div class="relative">
                        <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select
                                id="employment-status"
                                class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all appearance-none bg-white"
                                :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.employment_status}"
                                v-model="profile.employment_status"
                                disabled>
                                <option value="" disabled selected>Select employment status</option>
                                <option value="Employed">Employed</option>
                                <option value="Self-Employed">Self-Employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Underemployed">Underemployed</option>
                            </select>
                        </div>
                    </div>
  
                    <!-- Professional Title -->
                    <div class="relative">
                        <label for="professional-title" class="block text-gray-700 font-medium mb-1">Professional Title</label>
                        <div class="relative">
                            <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="text"
                                id="graduate_professional-title"
                                class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                                v-model="profile.graduate_professional_title"
                                placeholder="Enter your professional title"
                                disabled/>
                        </div>
                    </div>
  
                    <!-- Gender -->
                    <div class="relative">
                        <label for="gender" class="block text-gray-700 font-medium mb-1">Gender</label>
                        <div class="relative">
                            <i class="fas fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select
                                id="gender"
                                class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all appearance-none bg-white"
                                :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.gender}"
                                v-model="profile.graduate_gender"
                                disabled>
                                <option value="" disabled selected>Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Non-binary">Non-binary</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                        <div v-if="settingsForm.errors.gender" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.gender }}
                        </div>
                    </div>
        
                    <!-- Ethnicity -->
                    <div class="relative">
                        <label for="ethnicity" class="block text-gray-700 font-medium mb-1">Ethnicity</label>
                        <div class="relative">
                        <i class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            id="ethnicity"
                            class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            v-model="profile.graduate_ethnicity"
                            placeholder="Enter your ethnicity"
                            disabled
                        />
                        </div>
                    </div>
  
                    <!-- Birthdate -->
                    <div class="relative">
                        <label for="birthdate" class="block text-gray-700 font-medium mb-1">Birthdate</label>
                        <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 z-10"></i>
                        <Datepicker
                            v-model="profile.graduate_birthdate"
                            :format="datepickerConfig.format"
                            :enable-time-picker="datepickerConfig.enableTime"
                            input-class-name="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            placeholder="Select your birthdate"
                            :input-attributes="{ style: 'box-shadow: none !important;' }"
                            class="datepicker-no-shadow"
                            disabled
                        />
                        </div>
                    </div>
  
                    <!-- Location -->
                    <div class="relative">
                        <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
                        <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            id="address"
                            class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                            v-model="profile.graduate_address"
                            placeholder="City, Country"
                            disabled
                        />
                        </div>
                    </div>
                    </div>
                </div>
  
                <!-- Education Information Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h2 class="text-xl font-semibold mb-2">Education Information</h2>
                    <p class="text-gray-600 mb-4">Your academic background</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">              
                    <!-- School Graduated From -->
                        <div class="relative">
                        <label class="block text-gray-700 font-medium mb-1">School Graduated From</label>
                        <div class="relative">
                        <i class="fas fa-university absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                            {{ profile.graduate_school_graduated_from || 'Not specified' }}
                        </div>
                        </div>
                    </div>
  
                    <!-- Year Graduated -->
                    <div class="relative">
                        <label class="block text-gray-700 font-medium mb-1">Year Graduated</label>
                        <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                            {{ profile.graduate_year_graduated || 'Not specified' }}
                        </div>
                        </div>
                    </div>
              
                    <!-- Program Completed -->
                    <div class="relative">
                        <label class="block text-gray-700 font-medium mb-1">Program Completed</label>
                        <div class="relative">
                        <i class="fas fa-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                            {{ profile.program_id || 'Not specified' }}
                        </div>
                        </div>
                    </div>
  
                    <!-- Degree Completed -->
                    <div class="relative">
                        <label class="block text-gray-700 font-medium mb-1">Degree Completed</label>
                        <div class="relative">
                        <i class="fas fa-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                            {{ profile.program_id || 'Not specified' }}
                        </div>
                        </div>
                    </div>
                </div>
            </div>
  
            <!-- Work Experience-->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h2 class="text-xl font-semibold mb-2">Work Experience</h2>
                <p class="text-gray-600 mb-4">Graduate's professional journey</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <!-- Company Name -->
                <div class="relative">
                    <label for="company-name" class="block text-gray-700 font-medium mb-1">Company Name</label>
                    <div class="relative">
                    <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="company-name"
                        class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        v-model="profile.company_name"
                        placeholder="Enter company name"
                        disabled
                    />
                    </div>
                </div>
    
                <!-- Job Title -->
                <div class="relative">
                    <label for="job-title" class="block text-gray-700 font-medium mb-1">Job Title</label>
                    <div class="relative">
                    <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="job-title"
                        class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        v-model="profile.job_title"
                        placeholder="Enter job title"
                        disabled
                    />
                    </div>
                </div>

              <!-- Start Date -->   
                <div class="relative">
                <label for="start-date" class="block text-gray-700 font-medium mb-1">Start Date</label>
                    <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <Datepicker
                        v-model="profile.start_date"
                        :format="datepickerConfig.format"
                        :enable-time-picker="datepickerConfig.enableTime"
                        input-class-name="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        placeholder="Select start date"
                        :input-attributes="{ style: 'box-shadow: none!important;' }"
                        class="datepicker-no-shadow"
                        disabled
                        />
                    </div>
                </div>

                <!-- End Date -->
                <div class="relative">
                <label for="end-date" class="block text-gray-700 font-medium mb-1">End Date</label>
                    <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <Datepicker
                        v-model="profile.end_date"
                        :format="datepickerConfig.format"
                        :enable-time-picker="datepickerConfig.enableTime"
                        input-class-name="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        placeholder="Select end date"
                        :input-attributes="{ style: 'box-shadow: none!important;' }"
                        class="datepicker-no-shadow"
                        disabled
                        />
                    </div>
                </div>

                <!-- Currently Working -->
                <div class="relative">
                <label class="block text-gray-700 font-medium mb-1">Currently Working</label>
                    <div class="relative">
                        <i class="fas fa-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                        {{ profile.currently_working || 'Not specified' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Contact Information Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Contact Information</h2>
            <p class="text-gray-600 mb-4">How others can reach you</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <!-- Email Address -->
                <div class="relative">
                <label for="email-address" class="block text-gray-700 font-medium mb-1">Email Address <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                        type="email"
                        id="email-address"
                        class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.email}"
                        v-model="profile.email"
                        placeholder="Enter your email address"
                        disabled/>
                    </div>
                    <div v-if="settingsForm.errors.email" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.email }}
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="relative">
                    <label for="phone" class="block text-gray-700 font-medium mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                        type="text"
                        id="phone"
                        class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        :class="{'border-red-500 focus:ring-red-500': settingsForm.errors.contact_number}"
                        v-model="profile.graduate_phone"
                        placeholder="Enter your phone number"
                        disabled
                        />
                    </div>
                    <div v-if="settingsForm.errors.contact_number" class="text-red-500 text-sm mt-1">
                        {{ settingsForm.errors.contact_number }}
                    </div>
                </div>
            </div>
        </div>

         <!-- About Me Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">About Me</h2>
            <p class="text-gray-600 mb-4">Tell others about yourself</p>
            <div class="relative">
                <div class="relative">
                    <i class="fas fa-user-circle absolute left-3 top-3 text-gray-400"></i>
                    <textarea
                        id="about-me"
                        class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                        rows="4"
                        v-model="profile.graduate_about_me"
                        placeholder="Tell us about yourself"
                        maxlength="1000"
                        disabled>
                    </textarea>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                    {{ profile.graduate_about_me ? profile.graduate_about_me.length : 0 }}/1000
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
</template>