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

  const url = (path: string) =>
    `${apiBase.replace(/\/$/, '')}/${path.replace(/^\//, '')}`

  const unwrap = <T>(res: ApiEnvelope<T>) => res.data

  return {
    apiBase,
    url,

    getPage: (slug: string) =>
      $fetch<ApiEnvelope<PageData>>(url(`pages/${slug}`)).then(unwrap),

    getPages: () =>
      $fetch<ApiEnvelope<PageListItemData[]>>(url('pages')).then(unwrap),

    getNavigation: () =>
      $fetch<ApiEnvelope<NavItem[]>>(url('navigation')).then(unwrap),

    getGlobals: () =>
      $fetch<ApiEnvelope<SiteGlobals>>(url('globals')).then(unwrap),

    // Draft preview — `query` carries the signed { expires, signature } params
    // handed over by Filament's "Preview" action.
    getPreview: (slug: string, query: Record<string, unknown>) =>
      $fetch<ApiEnvelope<PageData>>(url(`preview/pages/${slug}`), { query }).then(unwrap)
  }
}
