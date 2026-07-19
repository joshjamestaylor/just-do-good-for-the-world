<script setup lang="ts">
import type { PageSectionData } from '~~/types/api'

const props = defineProps<{ data: PageSectionData }>()

// An empty PHP array serializes to JSON `[]`, not `{}` — coerce back to an
// object so it's a valid Nuxt UI `ui` slot-class map.
const ui = computed(() => (Array.isArray(props.data.ui) ? {} : (props.data.ui ?? {})))

// Map each CMS LinkData onto a Nuxt UI ButtonProps object. `href` (external)
// wins over `to` (internal) and opens in a new tab.
const links = computed(() =>
  (props.data.links ?? []).map((link) => {
    const external = Boolean(link.href)
    return {
      label: link.label ?? undefined,
      to: link.href ?? link.to ?? undefined,
      target: external ? '_blank' : undefined,
      icon: link.icon ?? undefined,
      trailingIcon: link.trailingIcon ?? undefined,
      color: link.color || 'primary',
      variant: link.variant || 'solid'
    }
  })
)
</script>

<template>
  <UPageSection
    :headline="data.headline ?? undefined"
    :icon="data.icon ?? undefined"
    :title="data.title"
    :description="data.description ?? undefined"
    :orientation="(data.orientation as 'vertical' | 'horizontal')"
    :reverse="data.reverse"
    :links="(links as any)"
    :features="(data.features as any)"
    :ui="ui"
  >
    <img
      v-if="data.image"
      :src="data.image"
      :alt="data.title"
      class="w-full rounded-lg"
      loading="lazy"
    >
  </UPageSection>
</template>
