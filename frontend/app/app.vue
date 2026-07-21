<script setup lang="ts">
const { getNavigation, getGlobals } = useApi()

// Header/footer chrome is CMS-driven too — nav and site globals come from the API.
const { data: nav } = await useAsyncData('navigation', () => getNavigation(), {
  default: () => []
})
const { data: globals } = await useAsyncData('globals', () => getGlobals(), {
  default: () => ({
    siteName: 'Site',
    year: new Date().getFullYear(),
    colors: [],
    semanticColors: { enabled: false, success: null, warning: null, error: null, info: null }
  })
})

const navItems = computed(() =>
  (nav.value ?? []).map(item => ({ label: item.label, to: item.to }))
)

// The CMS brand palette, applied as CSS variables. Injecting it here means it is
// rendered into the (pre)rendered HTML — the static build ships its own theme.
const brandThemeCss = computed(() => (globals.value ? buildBrandThemeCss(globals.value) : ''))

useHead({
  htmlAttrs: { lang: 'en' },
  link: [{ rel: 'icon', href: '/favicon.ico' }],
  meta: [{ name: 'viewport', content: 'width=device-width, initial-scale=1' }],
  style: [{ id: 'brand-theme', innerHTML: brandThemeCss }]
})

useSeoMeta({
  titleTemplate: title => (title ? `${title} · ${globals.value?.siteName}` : globals.value?.siteName),
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <UApp>
    <UHeader>
      <template #left>
        <NuxtLink
          to="/"
          class="font-bold text-highlighted"
        >
          {{ globals?.siteName }}
        </NuxtLink>
      </template>

      <!-- Desktop nav, right-aligned: the #right slot grows (`lg:flex-1`) and
           justifies to the end. Hidden below `lg`, where the header's hamburger
           toggle + the #body drawer take over. -->
      <template #right>
        <UNavigationMenu
          :items="navItems"
          class="hidden lg:flex"
        />
      </template>

      <!-- Mobile menu. The default (center) slot is `hidden lg:flex`, so the nav
           has to be repeated here to appear in the drawer the toggle opens —
           without this the mobile menu is empty. Vertical for the stacked list. -->
      <template #body>
        <UNavigationMenu
          :items="navItems"
          orientation="vertical"
        />
      </template>
    </UHeader>

    <UMain>
      <NuxtPage />
    </UMain>

    <UFooter>
      <template #left>
        <p class="text-sm text-muted">
          © {{ globals?.year }} {{ globals?.siteName }} · Filament + Nuxt UI
        </p>
      </template>
    </UFooter>
  </UApp>
</template>
