# Dokumentace šablony Glenmark Product

Tato dokumentace je určena agenturám a vývojářům, kteří připravují nebo spravují weby Glenmark pro český trh. Rodičovská šablona obsahuje společné funkce a komponenty; konkrétní obsah a odlišnosti webu patří do child šablony a do administrace WordPressu.

## Uživatelský návod

- [Informace pro spolupracující agenturu](agency-handoff.md): stručné představení koncepce a možností úprav.
- [Práce se šablonou v administraci](agency-guide.md): instalace, Elementor, Customizer, hlavička, zápatí, pluginy a běžná správa webu.
- [Produkty, ACF a produktový feed](product-content-and-feed.md): zadávání produktů, význam polí, výchozí detail, Schema.org a veřejný JSON-LD feed.

## Vývoj

- [Rozšiřování child šablony](development-guide.md): konfigurace, dědičnost, CSS/SCSS, přepis šablon, PHP moduly, widgety a kontrola změn.
- [Projektové standardy](project-standards.md): závazná pravidla pro umístění a styl kódu.
- [Elementor CSS proměnné](elementor-css-variables.md): dostupné globální barvy, typografie a doporučené CSS tokeny.
- [Poznámky k child šabloně](../../glenmark-product-child/site-specific/README.md): konkrétní cesty k jejím stylům, konfiguraci a template parts.

## Rozdělení odpovědností

- **Rodičovská šablona**: společné Glenmark rozhraní, CPT, ACF pole, Elementor widgety, hlavička a zápatí, produktový detail a feed.
- **Child šablona**: vzhled a funkce konkrétního webu, vlastní CSS/SCSS, PHP hooky, konfigurace a případné přepsání template souborů.
- **WordPress a Elementor**: obsahové stránky, navigace, globální styly a data, která redakce mění bez úprav kódu.

Konfigurační klíče `modules` jsou v rodičovském `config/theme-config.php` deklarované, ale bootstrap podle nich aktuálně moduly nezapíná ani nevypíná. Dokumentace je proto neprezentuje jako funkční přepínače.
