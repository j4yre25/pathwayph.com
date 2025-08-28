<script setup>
import { ref } from 'vue';
import { format } from 'date-fns';

const props = defineProps({
  entry: {
    type: Object,
    required: true
  },
  isArchived: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update', 'archive', 'unarchive', 'remove']);

const formatDate = (date) => {
  if (!date) return '';
  try {
    return format(new Date(date), 'MMM dd, yyyy');
  } catch (error) {
    return date;
  }
};

const handleUpdate = () => {
  emit('update', props.entry);
};

const handleArchive = () => {
  emit('archive', props.entry);
};

const handleUnarchive = () => {
  emit('unarchive', props.entry);
};

const handleRemove = () => {
  emit('remove', props.entry.id);
};
</script>

<template>
  <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 relative hover:shadow-lg transition-all duration-300 group"
    :class="{ 'bg-gray-50 opacity-85': isArchived }">
    <div>
      <div class="border-b pb-2">
        <h2 class="text-xl font-bold text-gray-800">{{ entry.graduate_projects_title }}</h2>
        <p class="text-sm text-gray-600">{{ entry.graduate_projects_role }}</p>
      </div>
      <div class="flex items-center text-gray-600 mt-2">
        <i class="far fa-calendar-alt mr-2 text-gray-500"></i>
        <span>
          {{ formatDate(entry.graduate_projects_start_date) }} -
          {{ entry.graduate_projects_end_date === null ? 'Present' :
            formatDate(entry.graduate_projects_end_date) }}
        </span>
      </div>
      <p class="mt-2">
        <strong>
          <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
        </strong>
        {{ entry.graduate_projects_description || 'No description provided' }}
      </p>
      <div class="mt-2">
        <strong>
          <i class="fas fa-link text-gray-500 mr-2"></i> Project URL:
        </strong>
        <span v-if="entry.graduate_projects_url">
          <a :href="entry.graduate_projects_url" target="_blank"
            class="text-blue-600 hover:underline break-all">
            {{ entry.graduate_projects_url }}
          </a>
        </span>
        <span v-else class="text-gray-500">No project URL provided</span>
      </div>
      <p class="mt-2">
        <strong>
          <i class="fas fa-trophy text-gray-500 mr-2"></i> Key Accomplishments:
        </strong>
        <span v-if="entry.graduate_projects_key_accomplishments">
          {{ entry.graduate_projects_key_accomplishments }}
        </span>
        <span v-else>No key accomplishment provided</span>
      </p>
      <div v-if="entry.graduate_project_file" class="mt-3">
        <img :src="`/storage/${entry.graduate_project_file}`" :alt="entry.graduate_projects_title"
          class="max-w-full h-auto rounded-lg shadow" />
      </div>
    </div>
    <div class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
      <button v-if="!isArchived" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition-colors duration-200" @click="handleUpdate">
        <i class="fas fa-edit"></i>
      </button>
      <button v-if="!isArchived" class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 p-1.5 rounded-full transition-colors duration-200" @click="handleArchive">
        <i class="fas fa-archive"></i>
      </button>
      <button v-if="isArchived" class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 p-1.5 rounded-full transition-colors duration-200" @click="handleUnarchive">
        <i class="fas fa-undo"></i>
      </button>
      <button v-if="isArchived" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-1.5 rounded-full transition-colors duration-200" @click="handleRemove">
        <i class="fas fa-trash"></i>
      </button>
    </div>
    <div v-if="isArchived" class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-md font-medium">
      Archived
    </div>
  </div>
</template>