<script setup lang="ts">
import type { PageSectionData } from '~~/types/api'

const props = defineProps<{ data: PageSectionData }>()

// An empty PHP array serializes to JSON `[]`, not `{}` — coerce back to an
// object so it's a valid Nuxt UI `ui` slot-class map.
const ui = computed(() => (Array.isArray(props.data.ui) ? {} : (props.data.ui ?? {})))

// Resolve a stored brand-colour *name* to a CSS value that references the
// `--brand-<slug>` variable buildBrandThemeCss() injects, so it stays linked to
// the palette — change the colour in Settings → Brand and every section using it
// updates. A raw hex / explicit var() (legacy) is honoured verbatim; an unknown
// name resolves to an undefined variable and simply has no effect.
function resolveBrandColor(name: string | null | undefined): string | undefined {
  if (!name) return undefined
  return name.startsWith('#') || name.startsWith('var(') ? name : `var(--brand-${slugify(name)})`
}

// One style object for the wrapper:
// - backgroundColor paints the section band (below the image layer, which is
//   faded over it on mobile — see backgroundOpacityClass — so the colour shows
//   through).
// - textColor is exposed as --section-fg / --section-fg-muted, custom props that
//   ONLY the prose slots consume (see the <style> block below). We deliberately
//   do NOT override Nuxt UI's global --ui-text-* here: buttons read those too
//   (neutral variants use text-default / text-muted), so overriding them painted
//   button labels the section colour over their untouched light backgrounds. The
//   headline keeps the brand accent (--ui-primary), untouched.
const wrapperStyle = computed(() => {
  const style: Record<string, string> = {}

  const bg = resolveBrandColor(props.data.backgroundColor)
  if (bg) style.backgroundColor = bg

  const text = resolveBrandColor(props.data.textColor)
  if (text) {
    style['--section-fg'] = text
    style['--section-fg-muted'] = `color-mix(in oklab, ${text}, transparent 10%)`
  }

  return Object.keys(style).length ? style : undefined
})

// The background image is a positioned layer behind the content, so it can cover
// the whole section ('normal') or just one half ('left' / 'right') — a plain
// `background-position` can't do half-width, it only anchors a full-bleed image.
// A half-width image is unreadable on a phone, so left/right only split from `lg`
// up; below that the image goes full-width (`inset-0`).
const backgroundLayerClass = computed(() => {
  switch (props.data.backgroundPosition) {
    case 'left':
      return 'inset-0 lg:right-auto lg:w-1/2'
    case 'right':
      return 'inset-0 lg:left-auto lg:w-1/2'
    default:
      return 'inset-0'
  }
})

const backgroundLayerStyle = computed(() =>
  props.data.backgroundImage
    ? { backgroundImage: `url("${props.data.backgroundImage}")` }
    : undefined
)

// On mobile the image is full-width behind the text, so when a section colour is
// set we fade the image there to let the colour "shine through" as an on-brand
// wash and keep text legible — this works with any image (no transparent file
// needed). From `lg` up (more room, and the left/right split) it's full strength.
const backgroundOpacityClass = computed(() =>
  props.data.backgroundColor ? 'opacity-50 lg:opacity-100' : ''
)

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
  <!-- `relative isolate` so the background layer (below) anchors here and its
       negative z-index stays contained behind the section, not the whole page. -->
  <div
    class="relative isolate"
    :style="wrapperStyle"
  >
    <div
      v-if="data.backgroundImage"
      aria-hidden="true"
      class="absolute -z-10 bg-cover bg-center bg-no-repeat"
      :class="[backgroundLayerClass, backgroundOpacityClass]"
      :style="backgroundLayerStyle"
    />

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
      <!-- `v-if` on the slot itself (not the <img>) so the default slot is
           genuinely absent when there's no image. That lets UPageSection fall
           back to its `hidden lg:block` spacer for the second grid column, which
           is what `reverse` needs something to swap the text against — so reverse
           still works in horizontal sections that have no content image. -->
      <template v-if="data.image" #default>
        <img
          :src="data.image"
          :alt="data.title"
          class="w-full rounded-lg"
          loading="lazy"
        >
      </template>
    </UPageSection>
  </div>
</template>

<style scoped>
/* Apply the chosen text colour to the prose slots only — section title and
   feature titles take the full colour, descriptions a slightly softer mix.
   Each falls back to Nuxt UI's normal text variable, so a section with no
   textColor renders exactly as before. Buttons/links are intentionally NOT
   targeted, so their own theming (incl. neutral variants, whose labels read
   the same text vars) stays intact. */
:deep([data-slot='title']) {
  color: var(--section-fg, var(--ui-text-highlighted));
}

:deep([data-slot='description']) {
  color: var(--section-fg-muted, var(--ui-text-muted));
}
</style>
