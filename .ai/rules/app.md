---
paths:
  - resources/views/components/**
  - resources/views/layouts/public.blade.php
---

# App

## Public ticket pages use the public layout
The public pages (home = ticket form, tickets.status) use `layouts::public` (resources/views/layouts/public.blade.php) via the `new #[Layout('layouts::public')] class` SFC syntax — the attribute goes between `new` and `class`, not above it. The admin panel (dashboard, admin/tickets) keeps the default `layouts::app` sidebar layout. Do not add admin sidebar chrome to public pages.