<script setup lang="ts">
import type { PageSectionData } from '~~/types/api'

const props = defineProps<{ data: PageSectionData }>()

// An empty PHP array serializes to JSON `[]`, not `{}` — coerce back to an
// object so it's a valid Nuxt UI `ui` slot-class map.
const ui = computed(() => (Array.isArray(props.data.ui) ? {} : (props.data.ui ?? {})))

// 'normal' | 'left' | 'right' -> CSS background-position (with `cover` sizing).
const BACKGROUND_POSITION: Record<string, string> = {
  normal: 'center',
  left: 'left center',
  right: 'right center'
}

// Optional CMS-set background colour and/or image. Bound as an inline style so
// any hex/URL works and is baked into the prerendered HTML. `inheritAttrs` is on,
// so it falls through to the section root element. The colour sits under the
// image, so a transparent PNG shows the colour through it.
const rootStyle = computed(() => {
  const style: Record<string, string> = {}

  if (props.data.backgroundColor) {
    style.backgroundColor = props.data.backgroundColor
  }

  if (props.data.backgroundImage) {
    style.backgroundImage = `url("${props.data.backgroundImage}")`
    style.backgroundSize = 'cover'
    style.backgroundRepeat = 'no-repeat'
    style.backgroundPosition = BACKGROUND_POSITION[props.data.backgroundPosition] ?? 'center'
  }

  return Object.keys(style).length ? style : undefined
})

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
    :style="rootStyle"
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
