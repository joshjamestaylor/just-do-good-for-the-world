<script setup lang="ts">
const { getNavigation, getGlobals } = useApi()

// Header/footer chrome is CMS-driven too — nav and site globals come from the API.
const { data: nav } = await useAsyncData('navigation', () => getNavigation(), {
  default: () => []
})
const { data: globals } = await useAsyncData('globals', () => getGlobals(), {
  default: () => ({ siteName: 'Site', year: new Date().getFullYear() })
})

const navItems = computed(() =>
  (nav.value ?? []).map(item => ({ label: item.label, to: item.to }))
)

useHead({
  htmlAttrs: { lang: 'en' },
  link: [{ rel: 'icon', href: '/favicon.ico' }],
  meta: [{ name: 'viewport', content: 'width=device-width, initial-scale=1' }]
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

      <UNavigationMenu :items="navItems" />

      <template #right>
        <UColorModeButton />
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
