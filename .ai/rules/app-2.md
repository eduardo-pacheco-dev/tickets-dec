---
paths:
  - 'app/**, resources/**'
---

# App 2

## OPENSSL_CONF must be set before PHP starts to generate VAPID keys
VAPID nesta máquina: o PHP do Herd não tem openssl.cnf; geração de chave EC (openssl_pkey_new P-256) falha sem ele. Criar/existe C:\Users\Eduardo\.config\herd\config\php\openssl.cnf e setar OPENSSL_CONF como variável de ambiente do processo ANTES do PHP iniciar (putenv() em runtime não funciona — a extensão lê o env na inicialização). A curva funciona como prime256v1 (WebPush/web-token já mapeia P-256). Comando: php artisan webpush:vapid --force. As chaves ficam no .env em VAPID_PUBLIC_KEY/VAPID_PRIVATE_KEY; VAPID_SUBJECT precisa ser URL/mailto válida (Apple/Safari rejeita TLD inválido).
