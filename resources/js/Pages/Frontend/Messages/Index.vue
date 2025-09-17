<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  threads: { type: Array, default: () => [] },
})
</script>

<template>
  <AppLayout title="Messages">
    <div class="max-w-6xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Messages</h1>
      <div class="bg-white rounded-xl shadow divide-y">
        <div v-if="threads.length === 0" class="p-6 text-gray-500">
          No conversations yet.
        </div>
        <Link
          v-for="t in threads"
          :key="t.conversation_id"
          :href="route('messages.show', t.conversation_id)"
          class="flex items-center gap-4 p-4 hover:bg-gray-50"
        >
          <img :src="t.avatar_url || '/images/default-logo.png'"
               class="w-12 h-12 rounded-full object-cover border" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-gray-800 truncate">
                {{ t.company_name || 'Company' }}
              </div>
              <div class="text-xs text-gray-400">{{ t.last_message.created_at }}</div>
            </div>
            <div class="text-sm text-gray-500 truncate">
              {{ t.job_title }}
            </div>
            <div class="text-sm text-gray-600 truncate mt-0.5">
              {{ t.last_message.body_preview }}
            </div>
          </div>
          <span v-if="t.unread_count" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs">
            {{ t.unread_count }}
          </span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>