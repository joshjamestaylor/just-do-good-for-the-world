// Generates a committed content snapshot from the live CMS, so the site can be
// built on Netlify (git push -> deploy) with no CMS reachable at build time.
//
// Usage (run with your local CMS up at http://localhost:8000):
//   npm run snapshot
//   SNAPSHOT_SOURCE=https://cms.example.com/api/v1 npm run snapshot
//
// Output (readable JSON, checked into git):
//   public/api-snapshot/navigation.json
//   public/api-snapshot/globals.json
//   public/api-snapshot/pages.json          (the page list)
//   public/api-snapshot/pages/<slug>.json   (one per page)
//
// The build reads these via NUXT_PUBLIC_API_BASE=/api-snapshot (see netlify.toml
// and nuxt.config.ts). Files are written verbatim — envelope and all — so the
// frontend's useApi() unwraps them exactly like a live API response.

import { mkdir, writeFile, rm } from 'node:fs/promises'
import { dirname, resolve } from 'node:path'

const SOURCE = (process.env.SNAPSHOT_SOURCE || 'http://localhost:8000/api/v1').replace(/\/$/, '')
const OUT = resolve(process.cwd(), 'public/api-snapshot')

async function getJson(path) {
  const res = await fetch(`${SOURCE}/${path}`)
  if (!res.ok) throw new Error(`GET ${SOURCE}/${path} -> ${res.status} ${res.statusText}`)
  return res.json()
}

async function save(path, body) {
  const file = resolve(OUT, `${path}.json`)
  await mkdir(dirname(file), { recursive: true })
  await writeFile(file, `${JSON.stringify(body, null, 2)}\n`)
  console.log(`  wrote api-snapshot/${path}.json`)
}

console.log(`Snapshotting CMS at ${SOURCE} ...`)

// Start clean so pages deleted in the CMS don't linger in the snapshot.
await rm(OUT, { recursive: true, force: true })

const [navigation, globals, pages] = await Promise.all([
  getJson('navigation'),
  getJson('globals'),
  getJson('pages')
])

await save('navigation', navigation)
await save('globals', globals)
await save('pages', pages)

const slugs = (pages.data ?? []).map(p => p.slug)
for (const slug of slugs) {
  await save(`pages/${slug}`, await getJson(`pages/${slug}`))
}

console.log(`\nDone — ${slugs.length} page(s) snapshotted to public/api-snapshot/.`)
console.log('Next: git add public/api-snapshot && git commit && git push')
