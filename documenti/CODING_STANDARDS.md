# Coding Standards & Convenzioni — CorpVitals24

Questa guida definisce regole unificate per PHP/Laravel, TypeScript/Vue, struttura file, formattazione, commit e qualità del codice. L’obiettivo è ridurre attrito in review, migliorare leggibilità e prevenire difetti.

## Indice
- PHP & Laravel
- TypeScript & Vue 3 (SFC)
- Inertia & organizzazione SPA
- Tailwind CSS & accessibilità
- Naming & struttura file
- Commenti & gestione errori
- Commit messages
- Linting/Formatting & hook
- Snippet di configurazione

---

## PHP & Laravel
- Standard: PSR-12, tipizzazione forte; evitare `mixed/array` generici nelle API interne.
- Controller sottili: orchestrano input/output, delegano a Services.
- Services: singola responsabilità, dipendenze via costruttore (DI), niente stato condiviso.
- Repositories: incapsulano query e ottimizzazioni Postgres (indici, eager loading, CTE solo se necessario).
- FormRequest: tutta la validazione input; messaggi localizzati.
- Eccezioni di dominio (es. `ImportException`, `KpiCalculationException`) e conversione a risposta JSON Problem Details a livello handler.
- Convenzioni DB: tabelle `snake_case`, `id` bigint, FK indicizzate, `created_at/updated_at` automatici, `jsonb` per metadati.

## TypeScript & Vue 3 (SFC)
- SFC con `<script setup lang="ts">`; evitare `any`, preferire tipi espliciti.
- Composables (`resources/js/composables/`) per logica riusabile (fetch, i18n helpers, a11y helpers).
- Store Pinia tipizzati; evitare store monolitici.
- Emissione eventi tipizzata (`defineEmits<{ (e: 'select', id: number): void }>()`).
- Strict mode TS, `noImplicitAny`, `exactOptionalPropertyTypes` abilitati.

## Inertia & organizzazione SPA
- `pages/` corrisponde alle route; non contenere logica business.
- `components/` per UI riutilizzabile; preferire Headless UI.
- Import dinamico per moduli pesanti (ECharts, RevoGrid).
- i18n: namespaces per pagina/feature, lazy-loaded.

## Tailwind CSS & accessibilità
- Utility-first con classi semantiche locali; tokens in config quando opportuno.
- Focus visibile coerente su elementi interattivi; colori con contrasto AA.
- Componenti Headless UI per gestione tastiera e ARIA; descrizioni testuali per grafici.

## Naming & struttura file
- Classi PHP: PascalCase; metodi/variabili: camelCase; tabelle/colonne: snake_case.
- Vue SFC: PascalCase (`KpiCard.vue`), store: suffisso `Store` (`useKpiStore`).
- Files TS utilità in `utils/` con nome esplicativo (`formatCurrency.ts`).
- I DTO usano nomi verbo-oggetto (`CalculateKpiCommand`, `FetchKpiQuery`).

## Commenti & gestione errori
- Commenti solo per razionale non ovvio, invarianti e caveat performance/sicurezza.
- Niente commenti ovvi; il codice deve essere autoesplicativo.
- Error handling centralizzato: mappare eccezioni → Problem Details JSON; log con `context` ricco e `request-id`.

## Commit messages
- Convenzione: Conventional Commits.
  - `feat:`, `fix:`, `docs:`, `refactor:`, `perf:`, `test:`, `build:`, `ci:`, `chore:`, `revert:`
  - Scopo opzionale: `feat(kpi): add snapshot cache`.

## Linting/Formatting & hook
- PHP-CS-Fixer per PSR-12.
- ESLint + Prettier per TS/Vue.
- Husky pre-commit: `npm run lint && composer lint`.
- CI: job `lint` fallisce su qualunque violazione.

---

## Snippet di configurazione

### ESLint (Vue + TS)
```js
// .eslintrc.cjs
module.exports = {
  root: true,
  env: { browser: true, es2023: true, node: true },
  parser: '@typescript-eslint/parser',
  parserOptions: { ecmaVersion: 'latest', sourceType: 'module' },
  plugins: ['@typescript-eslint', 'vue'],
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/vue3-recommended',
    'prettier'
  ],
  rules: {
    'vue/multi-word-component-names': 'off',
    '@typescript-eslint/explicit-module-boundary-types': 'off'
  }
};
```

### Prettier
```js
// prettier.config.cjs
module.exports = {
  printWidth: 100,
  singleQuote: true,
  trailingComma: 'all',
  semi: true
};
```

### PHP-CS-Fixer
```php
// php-cs-fixer.dist.php
<?php
$finder = PhpCsFixer\Finder::create()->in(__DIR__)
  ->exclude(['vendor','storage','node_modules']);
return (new PhpCsFixer\Config())
  ->setRiskyAllowed(true)
  ->setRules([
    '@PSR12' => true,
    'strict_param' => true,
    'no_unused_imports' => true,
  ])
  ->setFinder($finder);
```

### Scripts Composer
```json
// composer.json (estratto)
{
  "scripts": {
    "lint": ["@php vendor/bin/php-cs-fixer fix --dry-run --diff"],
    "lint:fix": ["@php vendor/bin/php-cs-fixer fix"],
    "test": ["@php artisan test --colors"]
  }
}
```

### Scripts npm
```json
// package.json (estratto)
{
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

### Regole aggiuntive Vue
```json
// tsconfig.json (estratto)
{
  "compilerOptions": {
    "strict": true,
    "noImplicitOverride": true,
    "verbatimModuleSyntax": true,
    "paths": { "@/*": ["resources/js/*"] }
  }
}
```

### Linee guida Tailwind
- Usare classi per stato focus coerente (`focus:outline focus:outline-2 focus:outline-offset-2`).
- Evitare over-qualifying; estrarre componenti quando le utility diventano ripetitive.

### Regole per import dinamico
```ts
// Esempio import on-demand ECharts
const echarts = await import('echarts');
```

### Problem Details JSON (Handler)
```php
// app/Exceptions/Handler.php (estratto)
public function render($request, Throwable $e)
{
    if ($request->expectsJson()) {
        $status = $this->statusFrom($e);
        return response()->json([
            'type' => 'https://corpvitals24/docs/errors#'.class_basename($e),
            'title' => class_basename($e),
            'status' => $status,
            'detail' => $e->getMessage(),
        ], $status);
    }
    return parent::render($request, $e);
}
```

---

## Pitfall comuni e come evitarli
- Controller gonfi: spostare logica in Services e validazione in FormRequest.
- Tipi TS deboli: preferire DTO e tipi espliciti per payload e risposte.
- Mancata sincronizzazione lint/prettier tra IDE e CI: usare le stesse versioni e config.
- Import statici di librerie pesanti (grafici/griglie): usare import dinamico per migliorare TTI.
