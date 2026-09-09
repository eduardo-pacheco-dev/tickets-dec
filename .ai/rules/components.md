---
paths:
  - 'resources/views/components/**'
---

# Components

## Livewire components use SFC format
New Livewire components are single-file components (SFC) under resources/views/components/ with the ⚡ emoji prefix (Livewire v4 default per config/livewire.php). Do not add a render() returning view() in an SFC — the blade file itself is the view; expose data via #[Computed] properties and reference as $this->tickets. Class attributes like #[Layout] must be placed between `new` and `class`: `new #[Layout('layouts::public')] class extends Component`.
