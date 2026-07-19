// Generates a committed content snapshot from the live CMS, so the site can be
// built on Netlify (git push -> deploy) with no CMS reachable at build time.
//
// Usage (run with your local CMS up at http://localhost:8000):
//   npm run snapshot
//   SNAPSHOT_SOURCE=https://cms.example.com/api/v1 npm run snapshot
//
// Output (readable JSON + downloaded media, checked into git):
//   public/api-snapshot/navigation.json
//   public/api-snapshot/globals.json
//   public/api-snapshot/pages.json          (the page list)
//   public/api-snapshot/pages/<slug>.json   (one per page)
//   public/api-snapshot/assets/<path>       (every CMS-hosted image, localized)
//
// CMS media URLs (${origin}/storage/…) are downloaded into assets/ and the JSON
// is rewritten to point at the static copy (/api-snapshot/assets/…), so the
// deployed site never links back to the CMS. The build reads all of this via
// NUXT_PUBLIC_API_BASE=/api-snapshot (see netlify.toml and nuxt.config.ts).

import { mkdir, writeFile, rm } from 'node:fs/promises'
import { dirname, resolve } from 'node:path'

const SOURCE = (process.env.SNAPSHOT_SOURCE || 'http://localhost:8000/api/v1').replace(/\/$/, '')
const ORIGIN = new URL(SOURCE).origin
const ASSET_PREFIX = `${ORIGIN}/storage/` // where the CMS's public disk serves uploads
const WEB_ASSET_BASE = '/api-snapshot/assets' // path the deployed site serves them from

const OUT = resolve(process.cwd(), 'public/api-snapshot')
const ASSETS_DIR = resolve(OUT, 'assets')

// remote asset URL -> disk-relative path (under assets/). Deduped across pages.
const assets = new Map()

async function getJson(path) {
  const res = await fetch(`${SOURCE}/${path}`)
  if (!res.ok) throw new Error(`GET ${SOURCE}/${path} -> ${res.status} ${res.statusText}`)
  return res.json()
}

// Walk any JSON value; rewrite CMS media URLs to their static path and register
// them for download. Returns a localized copy (does not mutate the input).
function localize(node) {
  if (typeof node === 'string') {
    if (node.startsWith(ASSET_PREFIX)) {
      const rest = decodeURIComponent(node.slice(ASSET_PREFIX.length).split('?')[0])
      assets.set(rest, node)
      return `${WEB_ASSET_BASE}/${rest}`
    }
    return node
  }
  if (Array.isArray(node)) return node.map(localize)
  if (node && typeof node === 'object') {
    return Object.fromEntries(Object.entries(node).map(([k, v]) => [k, localize(v)]))
  }
  return node
}

async function save(path, body) {
  const file = resolve(OUT, `${path}.json`)
  await mkdir(dirname(file), { recursive: true })
  await writeFile(file, `${JSON.stringify(body, null, 2)}\n`)
  console.log(`  wrote api-snapshot/${path}.json`)
}

async function downloadAsset(rest, url) {
  const res = await fetch(url)
  if (!res.ok) throw new Error(`GET ${url} -> ${res.status} ${res.statusText}`)
  const file = resolve(ASSETS_DIR, rest)
  await mkdir(dirname(file), { recursive: true })
  await writeFile(file, Buffer.from(await res.arrayBuffer()))
  console.log(`  wrote api-snapshot/assets/${rest}`)
}

console.log(`Snapshotting CMS at ${SOURCE} ...`)

// Start clean so content/media deleted in the CMS don't linger in the snapshot.
await rm(OUT, { recursive: true, force: true })

const [navigation, globals, pages] = await Promise.all([
  getJson('navigation'),
  getJson('globals'),
  getJson('pages')
])

const slugs = (pages.data ?? []).map(p => p.slug)
const pageDocs = {}
for (const slug of slugs) {
  pageDocs[slug] = await getJson(`pages/${slug}`)
}

// Rewrite media URLs (this also fills the `assets` map), then persist.
await save('navigation', localize(navigation))
await save('globals', localize(globals))
await save('pages', localize(pages))
for (const slug of slugs) {
  await save(`pages/${slug}`, localize(pageDocs[slug]))
}

// Download every referenced CMS asset into the static output.
for (const [rest, url] of assets) {
  await downloadAsset(rest, url)
}

console.log(`\nDone — ${slugs.length} page(s), ${assets.size} asset(s) snapshotted to public/api-snapshot/.`)
console.log('Next: git add public/api-snapshot && git commit && git push')
