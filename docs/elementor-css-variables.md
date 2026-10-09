# Elementor CSS Variables for gln-pharma-product

Tento soubor je referencni seznam CSS promennych z Elementoru, ktere mame pouzivat primo v SCSS.

Pravidlo:
- V SCSS pouzivej primo Elementor promenne `--e-*`.
- Nevytvarej nove aliasy typu `--gln-*` pro barvy a typografii.
- Kdyz promenna neni dostupna, pouzij lokalni hodnotu primo na vlastnosti (fallback).

## 1) Global colors (`--e-global-color-*`)
- `--e-global-color-primary`
- `--e-global-color-secondary`
- `--e-global-color-text`
- `--e-global-color-accent`

Poznamka:
- Elementor muze mit i dalsi user-defined global colors (napr. `--e-global-color-xxxxxxx`).

## 2) Global typography (`--e-global-typography-*`)
- `--e-global-typography-primary-font-family`
- `--e-global-typography-primary-font-size`
- `--e-global-typography-primary-font-weight`
- `--e-global-typography-primary-text-transform`
- `--e-global-typography-primary-font-style`
- `--e-global-typography-primary-text-decoration`
- `--e-global-typography-primary-line-height`
- `--e-global-typography-primary-letter-spacing`

- `--e-global-typography-secondary-font-family`
- `--e-global-typography-secondary-font-size`
- `--e-global-typography-secondary-font-weight`
- `--e-global-typography-secondary-text-transform`
- `--e-global-typography-secondary-font-style`
- `--e-global-typography-secondary-text-decoration`
- `--e-global-typography-secondary-line-height`
- `--e-global-typography-secondary-letter-spacing`

- `--e-global-typography-text-font-family`
- `--e-global-typography-text-font-size`
- `--e-global-typography-text-font-weight`
- `--e-global-typography-text-text-transform`
- `--e-global-typography-text-font-style`
- `--e-global-typography-text-text-decoration`
- `--e-global-typography-text-line-height`
- `--e-global-typography-text-letter-spacing`

- `--e-global-typography-accent-font-family`
- `--e-global-typography-accent-font-size`
- `--e-global-typography-accent-font-weight`
- `--e-global-typography-accent-text-transform`
- `--e-global-typography-accent-font-style`
- `--e-global-typography-accent-text-decoration`
- `--e-global-typography-accent-line-height`
- `--e-global-typography-accent-letter-spacing`

## 3) Layout variables
- `--container-max-width`
- `--content-width`
- `--e-con-grid-template-columns`
- `--e-con-grid-template-rows`

## 4) Elementor App tokens (`--e-a-*`)
Tyto promenne jsou vhodne jako fallback pro utility barvy, bordery a neutralni UI prvky.

### Background
- `--e-a-bg-active`
- `--e-a-bg-active-bold`
- `--e-a-bg-danger`
- `--e-a-bg-default`
- `--e-a-bg-hover`
- `--e-a-bg-chip`
- `--e-a-bg-info`
- `--e-a-bg-invert`
- `--e-a-bg-loading`
- `--e-a-bg-logo`
- `--e-a-bg-primary`
- `--e-a-bg-secondary`
- `--e-a-bg-success`
- `--e-a-bg-warning`

### Borders
- `--e-a-border-color`
- `--e-a-border-color-accent`
- `--e-a-border-color-bold`
- `--e-a-border-color-focus`
- `--e-a-border`
- `--e-a-border-bold`

### Button tokens
- `--e-a-btn-bg`
- `--e-a-btn-bg-accent`
- `--e-a-btn-bg-accent-active`
- `--e-a-btn-bg-accent-hover`
- `--e-a-btn-bg-active`
- `--e-a-btn-bg-danger`
- `--e-a-btn-bg-danger-active`
- `--e-a-btn-bg-danger-hover`
- `--e-a-btn-bg-disabled`
- `--e-a-btn-bg-hover`
- `--e-a-btn-bg-info`
- `--e-a-btn-bg-info-active`
- `--e-a-btn-bg-info-hover`
- `--e-a-btn-bg-primary`
- `--e-a-btn-bg-primary-active`
- `--e-a-btn-bg-primary-hover`
- `--e-a-btn-bg-success`
- `--e-a-btn-bg-success-active`
- `--e-a-btn-bg-success-hover`
- `--e-a-btn-bg-warning`
- `--e-a-btn-bg-warning-active`
- `--e-a-btn-bg-warning-hover`
- `--e-a-btn-color`
- `--e-a-btn-color-disabled`
- `--e-a-btn-color-invert`

### Text and semantic colors
- `--e-a-color-accent`
- `--e-a-color-accent-promotion`
- `--e-a-color-accent-promotion-disabled`
- `--e-a-color-black`
- `--e-a-color-circle-logo`
- `--e-a-color-danger`
- `--e-a-color-info`
- `--e-a-color-global`
- `--e-a-color-logo`
- `--e-a-color-primary`
- `--e-a-color-primary-bold`
- `--e-a-color-primary-bold-dark`
- `--e-a-color-secondary`
- `--e-a-color-success`
- `--e-a-color-txt`
- `--e-a-color-txt-accent`
- `--e-a-color-txt-active`
- `--e-a-color-txt-disabled`
- `--e-a-color-txt-hover`
- `--e-a-color-txt-invert`
- `--e-a-color-txt-muted`
- `--e-a-color-warning`
- `--e-a-color-white`

### Misc
- `--e-a-dark-bg`
- `--e-a-dark-color-txt`
- `--e-a-dark-color-txt-hover`
- `--e-a-dropdown-shadow`
- `--e-a-font-family`
- `--e-a-popover-shadow`
- `--e-a-transition-hover`

## 5) Practical examples
```scss
body {
  font-family: var(--e-global-typography-text-font-family, var(--e-global-typography-primary-font-family, sans-serif));
  color: var(--e-global-color-text, #7A7A7A);
  background: var(--e-a-bg-default, #fff);
}

.nav-menu a {
  color: var(--e-global-color-primary, #6E157B);
  font-weight: var(--e-global-typography-primary-font-weight, 400);
}

.card {
  border: 1px solid var(--e-a-border-color, #e6e8ea);
}
```
