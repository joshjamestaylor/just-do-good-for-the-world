import type { PageData, PageListItemData } from '~~/types/api'

/** Every response from the CMS is wrapped in a `data` envelope. */
export interface ApiEnvelope<T> {
  data: T
}

export interface NavItem {
  label: string
  to: string
}

export interface SiteGlobals {
  siteName: string
  year: number
}

/**
 * The single gateway to the CMS JSON API. Everything the frontend fetches goes
 * through here, so pointing at a different backend (integrated vs detached) is
 * purely a matter of the NUXT_PUBLIC_API_BASE env var — no code changes.
 */
export function useApi() {
  const apiBase = useRuntimeConfig().public.apiBase

  // A relative apiBase (e.g. "/api-snapshot") means we read the committed content
  // snapshot instead of a live API. During SSR/prerender there is no server on
  // the static host, so we read the JSON straight from disk; on the client we
  // fetch the shipped static file. An absolute base talks to the live CMS.
  const fromSnapshot = apiBase.startsWith('/')

  async function request<T>(path: string, query?: Record<string, unknown>): Promise<T> {
    const rel = `${apiBase.replace(/\/$/, '')}/${path.replace(/^\//, '')}`

    if (fromSnapshot) {
      const file = `${rel}.json`
      if (import.meta.server) {
        // Read the committed snapshot from public/ at prerender time.
        const [{ readFileSync }, { resolve }] = await Promise.all([
          import('node:fs'),
          import('node:path')
        ])
        const abs = resolve(process.cwd(), 'public', file.replace(/^\//, ''))
        return JSON.parse(readFileSync(abs, 'utf8')) as T
      }
      return $fetch<T>(file, query ? { query } : undefined)
    }

    return $fetch<T>(rel, query ? { query } : undefined)
  }

  const unwrap = <T>(res: ApiEnvelope<T>) => res.data

  return {
    apiBase,

    getPage: (slug: string) =>
      request<ApiEnvelope<PageData>>(`pages/${slug}`).then(unwrap),

    getPages: () =>
      request<ApiEnvelope<PageListItemData[]>>('pages').then(unwrap),

    getNavigation: () =>
      request<ApiEnvelope<NavItem[]>>('navigation').then(unwrap),

    getGlobals: () =>
      request<ApiEnvelope<SiteGlobals>>('globals').then(unwrap),

    // Draft preview needs the live CMS (signed { expires, signature } query from
    // Filament's "Preview" action); it has no snapshot equivalent.
    getPreview: (slug: string, query: Record<string, unknown>) =>
      request<ApiEnvelope<PageData>>(`preview/pages/${slug}`, query).then(unwrap)
  }
}
