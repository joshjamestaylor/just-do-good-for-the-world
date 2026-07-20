import type { SiteGlobalsData, BrandColorData } from '~~/types/api'

/**
 * Turns the CMS brand palette into a block of CSS custom properties.
 *
 * The output is injected as an inline <style> (see app.vue) so it is baked into
 * every prerendered page — the detached static build carries its colours with no
 * runtime API call, exactly like the rest of the content.
 *
 * How it themes Nuxt UI: components read `--ui-color-<alias>-<shade>` (and the
 * `--ui-<alias>` DEFAULT that derives from shade 500/400). Nuxt UI defines those
 * inside `@layer theme`; we emit ours *unlayered* at :root, and unlayered rules
 * always win over layered ones — so a brand colour transparently re-themes the
 * whole UI. Shades are derived from the single stored hex via CSS color-mix(),
 * so the CMS only stores one value per colour.
 */

// How much white (light shades) / black (dark shades) to mix into the base hex,
// treating the stored colour as shade 500. Approximates a Tailwind-style ramp.
const RAMP: Record<number, string> = {
  50: 'white 92%',
  100: 'white 84%',
  200: 'white 68%',
  300: 'white 48%',
  400: 'white 24%',
  600: 'black 12%',
  700: 'black 28%',
  800: 'black 44%',
  900: 'black 58%',
  950: 'black 72%'
}

// Brand roles that map onto a Nuxt UI colour alias (so they drive the theme).
// `accent`, `background` and `text` have no Nuxt UI alias — they're still exposed
// as `--brand-*` for content to use, just not wired into components.
const ROLE_TO_ALIAS: Record<string, string> = {
  primary: 'primary',
  secondary: 'secondary',
  neutral: 'neutral'
}

const HEX = /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i

const isHex = (v: string | null | undefined): v is string => !!v && HEX.test(v.trim())

/** Slug used for the `--brand-<slug>` variable name. Exported so consumers that
 *  reference a brand colour by name (e.g. a block's background) resolve to the
 *  exact same variable this file emits. */
export const slugify = (name: string) =>
  name.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')

/** The 11 `--ui-color-<alias>-<shade>` lines that re-theme one Nuxt UI colour. */
function rampLines(alias: string, hex: string): string[] {
  return [
    ...Object.entries(RAMP).map(
      ([shade, mix]) => `--ui-color-${alias}-${shade}: color-mix(in oklab, ${hex}, ${mix});`
    ),
    `--ui-color-${alias}-500: ${hex};`
  ]
}

export function buildBrandThemeCss(globals: Pick<SiteGlobalsData, 'colors' | 'semanticColors'>): string {
  const lines: string[] = []

  const colors: BrandColorData[] = globals.colors ?? []
  for (const color of colors) {
    if (!isHex(color.hex)) continue
    const hex = color.hex.trim()

    // Every colour is addressable by name for content use.
    const slug = slugify(color.name)
    if (slug) lines.push(`--brand-${slug}: ${hex};`)

    if (color.role) {
      // A stable, role-based handle (e.g. --brand-primary) regardless of name.
      lines.push(`--brand-${color.role}: ${hex};`)

      const alias = ROLE_TO_ALIAS[color.role]
      if (alias) lines.push(...rampLines(alias, hex))
    }
  }

  // Status colours only override the conventional Nuxt UI defaults when opted in.
  const semantic = globals.semanticColors
  if (semantic?.enabled) {
    for (const alias of ['success', 'warning', 'error', 'info'] as const) {
      const hex = semantic[alias]
      if (isHex(hex)) lines.push(...rampLines(alias, hex.trim()))
    }
  }

  if (!lines.length) return ''

  return `:root {\n  ${lines.join('\n  ')}\n}`
}
