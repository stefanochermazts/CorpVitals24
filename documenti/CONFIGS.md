# Configurazioni Pratiche — CorpVitals24

Raccolta di config operative per avvio rapido e coerenza ambienti.

## Docker & Nginx
```dockerfile
# docker/php-fpm/Dockerfile
FROM php:8.3-fpm
RUN apt-get update && apt-get install -y git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

```nginx
# docker/nginx/default.conf
server {
  listen 80;
  server_name _;
  root /var/www/html/public;
  index index.php index.html;
  location / { try_files $uri $uri/ /index.php?$query_string; }
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass app:9000;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
  }
}
```

```yaml
# docker/docker-compose.yml (estratto)
services:
  app:
    build: ./php-fpm
    volumes: [ "../:/var/www/html" ]
    depends_on: [ db, redis ]
  web:
    image: nginx:1.27-alpine
    volumes:
      - "../:/var/www/html"
      - "./nginx/default.conf:/etc/nginx/conf.d/default.conf"
    ports: [ "8080:80" ]
  db:
    image: postgres:16
  redis:
    image: redis:7
```

## package.json
```json
{
  "name": "corpvitals24",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "typecheck": "vue-tsc --noEmit",
    "lint": "eslint resources/js --ext .ts,.vue",
    "format": "prettier -w .",
    "prepare": "husky install"
  }
}
```

## Vite, Tailwind, PostCSS
```ts
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
export default defineConfig({
  plugins: [vue()],
  resolve: { alias: { '@': '/resources/js' } }
});
```

```js
// tailwind.config.cjs
module.exports = {
  content: ['./resources/**/*.{vue,js,ts,blade.php}'],
  theme: { extend: {} },
  plugins: []
};
```

```js
// postcss.config.cjs
module.exports = { plugins: { tailwindcss: {}, autoprefixer: {} } };
```

## ESLint & Prettier
```js
// .eslintrc.cjs
module.exports = {
  root: true,
  env: { browser: true, es2023: true, node: true },
  parser: '@typescript-eslint/parser',
  plugins: ['@typescript-eslint','vue'],
  extends: ['eslint:recommended','plugin:@typescript-eslint/recommended','plugin:vue/vue3-recommended','prettier'],
  rules: { 'vue/multi-word-component-names': 'off' }
};
```

```js
// prettier.config.cjs
module.exports = { printWidth: 100, singleQuote: true, trailingComma: 'all', semi: true };
```

## PHP-CS-Fixer
```php
// php-cs-fixer.dist.php
<?php
$finder = PhpCsFixer\Finder::create()->in(__DIR__)->exclude(['vendor','storage','node_modules']);
return (new PhpCsFixer\Config())
  ->setRiskyAllowed(true)
  ->setRules(['@PSR12' => true,'strict_param' => true,'no_unused_imports' => true])
  ->setFinder($finder);
```

## Husky
```
# .husky/pre-commit
npx lint-staged
```

```json
// package.json (estratto)
{
  "devDependencies": { "husky": "^9.0.0", "lint-staged": "^15.0.0" },
  "lint-staged": {
    "*.{ts,vue,js}": ["eslint --fix"],
    "*.{php}": ["php vendor/bin/php-cs-fixer fix --dry-run --diff"]
  }
}
```

