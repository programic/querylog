# Programic - Query log

Met deze package worden alle database queries gelogt en berekend hoevaak dezelfde query word uitgevoerd. 
## Installatie
Om deze package te gebruiken, installeer je de package via composer.

Gebruik Composer installeer via commando:
```
composer require programic/querylog --dev
```

In je `composer.json`:
```json
{
  "require-dev": {
    "programic/querylog": "^1.0"
  }
}
```
We gebruiken de --dev flag om alleen de querylog te gebruiken in onze "dev" omgeving. Als je ook de log wilt 
gebruiken op productie verwijder je de --dev flag

## Configuratie
Kopieer het config bestand naar je project door het commando:
```php
php artisan vendor:publish
```

en zet query log aan via je environment:
```
QUERY_LOG_ENABLED=true
```
