---
name: headless-frontend
description: Headless WordPress with modern frontend frameworks. Use when building Next.js, Astro, Remix, Angular, or SolidJS frontends consuming WordPress REST API or WPGraphQL.
---

# Headless WordPress Frontend

## Architecture
WordPress serves as a **headless CMS** — content managed in WP admin, consumed via API by a separate frontend application.

## API Options

### REST API (current project)
```
GET /wp-json/wr-slot-manager/v1/slots
Authorization: Bearer <api_key>
X-Signature: <hmac>
X-Auth-Nonce: <nonce>
X-Origin: <consumer_url>
```

### WPGraphQL (alternative)
Install `wp-graphql` plugin, then:
```graphql
query GetSlots {
    slots(first: 20) {
        nodes {
            id
            title
            slotMeta { starRating providerName rtp minWager maxWager }
            featuredImage { node { sourceUrl } }
        }
    }
}
```

## Framework Integration

### Next.js (App Router)
```typescript
// app/slots/page.tsx
async function getSlots() {
    const res = await fetch('https://api.example.com/wp-json/wr-slot-manager/v1/slots', {
        headers: { 'Authorization': 'Bearer ...' },
        next: { revalidate: 60 }
    });
    return res.json();
}

export default async function SlotsPage() {
    const { data: slots } = await getSlots();
    return <div>{slots.map(slot => <SlotCard key={slot.id} {...slot} />)}</div>;
}
```

### Astro
```astro
---
// src/pages/slots.astro
const res = await fetch('https://api.example.com/wp-json/wr-slot-manager/v1/slots', {
    headers: { 'Authorization': 'Bearer ...' }
});
const { data: slots } = await res.json();
---
<div>{slots.map(slot => <SlotCard {...slot} />)}</div>
```

### Remix
```typescript
// app/routes/slots.tsx
export async function loader() {
    const res = await fetch('https://api.example.com/wp-json/wr-slot-manager/v1/slots', {
        headers: { 'Authorization': 'Bearer ...' }
    });
    return json(await res.json());
}
```

### Angular / SolidJS
Similar pattern — fetch from REST API in service/resource, render in components.

## Key Considerations
- **CORS**: WordPress needs `Access-Control-Allow-Origin` header for cross-origin requests
- **Auth**: Use the 3-layer AuthSigner for authenticated endpoints
- **Cache**: ISR (Next.js) or stale-while-revalidate patterns
- **Preview**: WordPress preview links can integrate with frontend preview mode
- **SEO**: Use SSR/SSG frameworks for SEO-friendly pages
- **Webhooks**: WordPress pushes updates to frontend rebuild triggers
