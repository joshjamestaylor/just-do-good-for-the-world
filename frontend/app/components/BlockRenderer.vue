<script setup lang="ts">
import type { Component } from 'vue'
import type { BlockData } from '~~/types/api'

defineProps<{ blocks: BlockData[] }>()

/**
 * Auto-register every block component in ./blocks. Convention: the file's
 * PascalCase basename maps to the block `type` in snake_case, e.g.
 * PageSection.vue <-> "page_section". Dropping a new .vue in here is all it
 * takes to render a new backend block — no manual wiring.
 */
const modules = import.meta.glob('./blocks/*.vue', { eager: true }) as Record<string, { default: Component }>

const registry: Record<string, Component> = {}
for (const path in modules) {
  const base = path.split('/').pop()!.replace(/\.vue$/, '')
  // Mirror Laravel's Str::snake exactly so the type matches the backend's block
  // key even for acronyms (FAQ -> f_a_q), which make:block also produces.
  const type = base.replace(/(.)(?=[A-Z])/g, '$1_').toLowerCase()
  registry[type] = modules[path]!.default
}
</script>

<template>
  <template
    v-for="(block, index) in blocks"
    :key="index"
  >
    <component
      :is="registry[block.type]"
      v-if="registry[block.type]"
      :data="block.data"
    />
    <UContainer
      v-else
      class="py-4"
    >
      <UAlert
        color="warning"
        variant="subtle"
        :title="`Unknown block type: ${block.type}`"
        description="No frontend component is registered for this block yet."
      />
    </UContainer>
  </template>
</template>
