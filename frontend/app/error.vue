<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps<{ error: NuxtError }>()
</script>

<template>
  <UApp>
    <UMain>
      <UPageSection
        :title="`${error.statusCode}`"
        :description="error.statusMessage || 'Something went wrong.'"
        :links="[{ label: 'Back to home', to: '/', icon: 'i-lucide-arrow-left', onClick: () => clearError({ redirect: '/' }) }]"
      />
    </UMain>
  </UApp>
</template>
