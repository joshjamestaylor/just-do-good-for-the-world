// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui'
  ],

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  /**
   * The ONLY seam between this frontend and the CMS. `apiBase` is overridable
   * per-environment via NUXT_PUBLIC_API_BASE — read at runtime in SSR/dev, and
   * baked in at `nuxt generate` time for the detached static build. Switching
   * between the integrated and detached frontends is just this one env var.
   */
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api/v1', // NUXT_PUBLIC_API_BASE
      siteMode: 'detached' // NUXT_PUBLIC_SITE_MODE — 'integrated' | 'detached' (cosmetic; data path is identical)
    }
  },

  routeRules: {
    // Homepage is always prerendered. Dynamic pages are enumerated in the
    // nitro:config hook below; preview routes stay client-rendered.
    '/': { prerender: true },
    '/preview/**': { prerender: false }
  },

  nitro: {
    prerender: {
      crawlLinks: true,
      failOnError: false
    }
  },

  hooks: {
    /**
     * At build time (nuxt generate), enumerate every published page from the API
     * so each slug is prerendered to a static file. Falls back to crawlLinks if
     * the API is unreachable, so the build never hard-fails on this.
     */
    async 'nitro:config'(nitro) {
      if (nitro.dev) return

      const base = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1'

      try {
        const res = await fetch(`${base}/pages`)
        const body = await res.json() as { data?: Array<{ slug: string }> }
        const routes = (body.data ?? []).map(p => (p.slug === 'home' ? '/' : `/${p.slug}`))

        nitro.prerender ||= {}
        nitro.prerender.routes = [...(nitro.prerender.routes ?? []), ...routes]
        // eslint-disable-next-line no-console
        console.info(`[prerender] enumerated ${routes.length} page route(s) from ${base}`)
      } catch (err) {
        // eslint-disable-next-line no-console
        console.warn(`[prerender] could not reach ${base}/pages — relying on crawlLinks. ${(err as Error).message}`)
      }
    }
  },

  compatibilityDate: '2026-06-30',

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  }
})
