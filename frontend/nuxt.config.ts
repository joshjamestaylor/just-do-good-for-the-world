// https://nuxt.com/docs/api/configuration/nuxt-config
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

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
     * At build time (nuxt generate) we need the list of published pages so each
     * slug is prerendered to a static file, with its content baked into the
     * output (the deployed site then serves content with zero API calls).
     *
     * Where that list comes from depends on NUXT_PUBLIC_API_BASE:
     *   - an absolute URL (e.g. http://localhost:8000/api/v1) → fetch the live CMS
     *   - a relative path  (e.g. /api-snapshot)               → read the committed
     *     JSON snapshot under public/ (see `npm run snapshot`), so CI/Netlify can
     *     build straight from git with no CMS reachable.
     */
    async 'nitro:config'(nitro) {
      if (nitro.dev) return

      const base = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1'
      const fromSnapshot = base.startsWith('/')
      const isCiDeploy = !!(process.env.NETLIFY || process.env.CI)
      const source = fromSnapshot ? `snapshot ${base}` : base

      try {
        let pages: Array<{ slug: string }>
        if (fromSnapshot) {
          const file = resolve(process.cwd(), 'public', base.replace(/^\//, ''), 'pages.json')
          const body = JSON.parse(readFileSync(file, 'utf8')) as { data?: Array<{ slug: string }> }
          pages = body.data ?? []
        } else {
          const res = await fetch(`${base}/pages`)
          if (!res.ok) throw new Error(`GET ${base}/pages responded ${res.status}`)
          const body = await res.json() as { data?: Array<{ slug: string }> }
          pages = body.data ?? []
        }

        const routes = pages.map(p => (p.slug === 'home' ? '/' : `/${p.slug}`))
        nitro.prerender ||= {}
        nitro.prerender.routes = [...(nitro.prerender.routes ?? []), ...routes]
        // eslint-disable-next-line no-console
        console.info(`[prerender] enumerated ${routes.length} page route(s) from ${source}`)
      } catch (err) {
        const detail = `could not read pages from ${source} — ${(err as Error).message}`
        if (isCiDeploy) {
          // Better a failed deploy than a green deploy that serves an empty site.
          throw new Error(
            `[prerender] ${detail}\n`
            + 'This is a CI/Netlify build. Either commit a content snapshot '
            + '(`npm run snapshot`, with NUXT_PUBLIC_API_BASE=/api-snapshot), or '
            + 'point NUXT_PUBLIC_API_BASE at a reachable public CMS API.'
          )
        }
        // eslint-disable-next-line no-console
        console.warn(`[prerender] ${detail} — relying on crawlLinks (local build).`)
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
