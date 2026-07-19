<script setup lang="ts">
const route = useRoute()
const { getPage } = useApi()

// '/' -> 'home'; '/about' -> 'about'; '/docs/intro' -> 'docs/intro'.
const slug = computed(() => {
  const parts = (route.params.slug as string[] | undefined) ?? []
  return parts.length ? parts.join('/') : 'home'
})

const { data: page, error } = await useAsyncData(
  `page:${slug.value}`,
  () => getPage(slug.value),
  { watch: [slug] }
)

if (error.value || !page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

useSeoMeta({
  title: () => page.value?.seo?.title || page.value?.title,
  description: () => page.value?.seo?.description || undefined,
  ogTitle: () => page.value?.seo?.title || page.value?.title,
  ogDescription: () => page.value?.seo?.description || undefined,
  ogImage: () => page.value?.seo?.ogImage || undefined
})
</script>

<template>
  <BlockRenderer
    v-if="page"
    :blocks="page.blocks"
  />
</template>
