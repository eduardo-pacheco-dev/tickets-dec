---
paths:
  - 'resources/views/components/**'
  - resources/views/layouts/public.blade.php
  - 'resources/views/layouts/**'
---

# App

## Public ticket pages use the public layout
The public pages (home = ticket form, tickets.status) use `layouts::public` (resources/views/layouts/public.blade.php) via the `new #[Layout('layouts::public')] class` SFC syntax — the attribute goes between `new` and `class`, not above it. The admin panel (dashboard, admin/tickets) keeps the default `layouts::app` sidebar layout. Do not add admin sidebar chrome to public pages.

## Flux sidebar desktop collapse uses boolean collapsible
To collapse the sidebar to an icon rail on desktop while keeping the mobile off-canvas overlay, use `<flux:sidebar collapsible ...>` (boolean true). The value `collapsible="all"` does NOT exist — it disables mobile overlay behavior in PHP (`$collapsibleOnMobile`) while JS compares the attr to the literal strings "true"/"mobile". Keep `<flux:sidebar.collapse>` desktop-only with `class="hidden lg:flex"`; mobile uses `<flux:sidebar.toggle>` in the header.

## Avoid #[Computed] + cached relations for counts that change in actions
#[Computed] and Eloquent relations are memoized on the component/User instance, so after an action that mutates the DB (e.g. markAllAsRead) a re-render still shows stale values — both in the browser request and in Livewire tests. For dynamic values that change within an action, query through the relationship builder instead: auth()->user()->notifications()->whereNull('read_at')->count(), and mutate via ->update() or ->markAsRead() on a freshly fetched model. See ⚡notification-bell and admin/⚡notification-list.
