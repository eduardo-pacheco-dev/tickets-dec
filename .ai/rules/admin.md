---
paths:
  - 'resources/views/components/admin/**'
---

# Admin

## No Alpine :bindings with PHP loop vars in Livewire SFC
In Livewire single-file components, `:class`/`:disabled`/`:aria-label` on raw HTML element attributes are compiled to client-side Alpine bindings, so PHP-only variables like `$stepNumber` (from @foreach/@php) and `$stationSteps` do NOT exist in the browser — they throw ReferenceError. Interpolate server-side instead: `class="... {{ $cond ? 'x' : 'y' }}"`, `@disabled($cond)`, `aria-label="... {{ $var }}"`. Note: `:attr` on Flux/Blade components (e.g. `<flux:button :aria-label="...">`) IS evaluated server-side and is fine.
