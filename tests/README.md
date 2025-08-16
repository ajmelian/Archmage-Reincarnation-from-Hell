# QA — Golden tests & E2E

## Golden (HTTP)
- Edita `tests/golden/*.json` con peticiones y expectativas.
- Arranca la app (Docker S47 o servidor local) y ejecuta:
```bash
php tests/golden/run_http.php http://localhost:8080/index.php
```

## PHPUnit
```bash
composer install
vendor/bin/phpunit
```

## Cypress (opcional)
```bash
npm init -y && npm install cypress --save-dev
npx cypress run
```

## CI
- El workflow `tests.yml` ejecuta PHPUnit y el runner HTTP sobre el servidor embebido de PHP.
