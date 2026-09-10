---
paths:
  - 'tests/**'
---

# Tests

## Avoid importing global classes like Generator in test files
Não use `use Generator;` (nem imports de classes globais) em arquivos de teste: PHP emite E_WARNING "use statement with non-compound name has no effect" a cada load do arquivo, e o Pest conta como warnings no resultado da suíte. Classes globais (Generator, stdClass, DateTime) referencie sem import trochado ou com FQCN completo.
