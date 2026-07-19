<script setup lang="ts">
// Draft preview route. Filament's "Preview" action links here with the signed
// { expires, signature } query, which we forward to the token-guarded API.
const route = useRoute()
const { getPreview } = useApi()

const slug = computed(() => route.params.slug as string)
const query = computed(() => ({
  expires: route.query.expires,
  signature: route.query.signature
}))

const { data: page, error } = await useAsyncData(
  `preview:${slug.value}`,
  () => getPreview(slug.value, query.value),
  { watch: [slug] }
)

useSeoMeta({ robots: 'noindex, nofollow' })
</script>

<template>
  <div>
    <div class="sticky top-0 z-50 bg-warning/15 py-1.5 text-center text-sm font-medium text-warning">
      Preview — showing draft content
    </div>

    <BlockRenderer
      v-if="page"
      :blocks="page.blocks"
    />

    <UContainer
      v-else
      class="py-16"
    >
      <UAlert
        color="error"
        title="Preview unavailable"
        :description="'The preview link is invalid or has expired. Re-open it from Filament.'"
      />
    </UContainer>
  </div>
</template>
