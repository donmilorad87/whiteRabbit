---
name: oop-patterns
description: Object-oriented programming patterns for PHP and TypeScript. Use when designing classes, interfaces, traits, or refactoring code structure.
---

# OOP Patterns

## PHP
- Namespaced classes: `WiseRabbit\SlotManager\Admin\SettingsPage`
- Singleton for plugin orchestrator: `Plugin::get_instance()`
- Traits for cross-cutting: `LoggerTrait`, `OptionPrefixTrait`, `TemplateLoaderTrait`
- Dependency injection via constructor: `new SlotSaveHook( $cache )`
- One class per file, file named `class-{name}.php`

## TypeScript
- ES6 classes with typed properties: `public dialog: HTMLDialogElement`
- Constructor injection: `constructor( form: HTMLFormElement, fetchHandler: FetchHandler )`
- Export classes individually: `export class SlotCardBuilder { }`
- Interfaces for config objects: `export interface CardBuilderConfig { }`
- Static methods for utilities: `static buildStarsHTML( rating: number ): string`

## Patterns Used
- **Builder**: `SlotCardBuilder.build(slot)` — constructs HTML from data
- **Observer**: `IntersectionObserver` for infinite scroll
- **State Machine**: `FetchHandler` — idle → loading → success/error
- **Strategy**: `SlotLoadMore` — button mode vs infinite scroll mode
- **Template Method**: `TemplateLoaderTrait` — `load_template()` / `render_template()`
- **Queue**: `WebhookQueue` — enqueue/dequeue/process pattern

## Anti-Patterns to Avoid
- God classes — split large classes into focused ones
- Anemic domain models — put behavior with data
- Service locator — prefer constructor injection
- Static everything — static only for pure utilities
