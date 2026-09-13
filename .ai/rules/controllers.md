---
paths:
  - 'app/Exports/**,app/Imports/**,app/Http/Controllers/StationTemplateController.php,**/*.blade.php'
---

# Controllers

## Stations import UI traps
Trap: `flux:input.file` renders with `wire:ignore`, so `wire:model` never syncs — use a native `<input type="file" wire:model="...">` inside the Flux modal instead. Also the `Excel` Facade can't resolve class constants like `Excel::XLSX`; pass the string `'Xlsx'` as the reader type.
