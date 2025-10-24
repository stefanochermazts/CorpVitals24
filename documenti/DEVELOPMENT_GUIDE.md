# CorpVitals24 — Development Guide

Versione: v1.0.0  
Ultimo aggiornamento: 2025-10-22

## Indice principale
- Stack & Versioni: `docs/STACK_VERSIONS.md`
- Struttura di Progetto: `docs/PROJECT_STRUCTURE.md`
- Coding Standards: `docs/CODING_STANDARDS.md`
- Best Practices (errori/logging/validazione/test): `docs/BEST_PRACTICES.md`
- Sicurezza (auth/RBAC/headers/CSRF/rate limit): `docs/SECURITY_GUIDE.md`
- Architettura (Layering, CQRS‑lite, DI): `docs/ARCHITECTURE.md`
- Configurazione & Segreti: `docs/CONFIGURATION.md`
- Database Design (schema/migrazioni/ORM): `docs/DATABASE_DESIGN.md`
- API Specification (REST v1): `docs/API_SPEC.md`
- Observability (logging/metrics/health): `docs/OBSERVABILITY.md`
- Development Workflow (Git/CI): `docs/DEV_WORKFLOW.md`
- Performance & Caching: `docs/PERFORMANCE.md`
- Esempi Pratici (snippets): `docs/EXAMPLES.md`
- Configurazioni pratiche: `docs/CONFIGS.md`

## Uso della guida
- Ogni sezione è autonoma e può essere seguita dal team backend/frontend.
- Gli snippet sono pronti all’uso e compatibili con lo stack fissato in `STACK_VERSIONS.md`.
- La pipeline CI deve validare l’aderenza agli standard di questa guida.

## Note
- Aggiornare versione e data ad ogni modifica significativa.
- In caso di conflitto tra documenti, `STACK_VERSIONS.md` e `CODING_STANDARDS.md` hanno priorità.
