# Glenmark Product: informace pro spolupracující agenturu

Glenmark Product je společný základ pro produktové weby Glenmark v České republice. Rodičovská šablona obsahuje sdílené rozvržení, produktové funkce, Elementor widgety, ACF pole a české napojení na lékárny. Konkrétní web se staví a dolaďuje v child šabloně, nikoli úpravami společného základu.

## Jak začít

Zdrojové soubory rodičovské i child šablony jsou dostupné v Git repozitáři projektu. Pro práci na webu nainstalujte obě šablony a aktivujte child šablonu. Přístup do repozitáře a příslušné přihlašovací údaje poskytne vlastník projektu.

Základem pro tvorbu stránek je bezplatná verze Elementoru. Rodičovská šablona do něj přidává widgety pro výpis a prezentaci produktů, lékáren, článků, benefitů, CTA a další obsah. Elementor Pro ani jiné placené rozšíření nejsou podmínkou; další pluginy lze přidat podle potřeb projektu po ověření kompatibility.

Pro kompletní produktová data je potřeba ACF PRO, protože datový model využívá mimo jiné opakovatelná pole a galerii. TGM nabízí k instalaci Elementor a CookieYes a volitelně Git Updater; ACF PRO se instaluje odděleně z licencovaného zdroje a jeho ZIP ani licenční klíč nepatří do Git repozitáře. Šablona integruje souhlas s cookies a blokování videí přes CookieYes. Pokud projekt používá licencovaný WPConsent Pro, licenci na webu aktivuje Creanto; klíč se klientovi ani provozovateli nepředává a nepatří do Git repozitáře. Nepoužívejte oba consent systémy současně bez ověření jejich integrace. Před nasazením ověřte také chování vložených videí YouTube/Vimeo.

## Co lze nastavit v administraci

- **Elementor → Nastavení webu**: globální barvy a typografii. Pro web je připraven Elementor Site Settings Kit v repozitáři; importuje se přes Elementor. Importovaný Kit a PHP konfigurace šablony se automaticky nesynchronizují.
- **Vzhled → Přizpůsobit**: loga včetně varianty pro transparentní hlavičku, favicon, sociální odkazy, rozvržení a chování hlavičky, zápatí a další nastavení webu.
- **Nastavení konkrétní stránky v Elementoru**: lokální režim hlavičky, například transparentní či skleněný, sticky chování, zmenšení při scrollu nebo výběr menu. Stránka může dědit globální nastavení nebo je přepsat.
- **Produkty**: záznamy produktů a jejich ACF údaje. Z těchto dat vychází výchozí detail produktu, produktové widgety i strukturovaná data.

## Co upravovat v child šabloně

Child šablona je místo pro vzhled a funkce konkrétního webu: pokročilé CSS/SCSS, vlastní PHP hooky, konfiguraci, nové varianty hlavičky/zápatí nebo přepsání produktové šablony. Drobné úpravy dělejte přednostně v child stylech; společné funkce měňte v rodiči jen tehdy, když mají být dostupné i ostatním Glenmark webům.

Barvy a písma nastavujte nejprve globálně v Elementoru. Vlastní CSS může vzhled dále specifikovat; obecné hodnoty Elementor Kit zdědí komponenty, které používají jeho globální proměnné. Child styly se načítají po rodičovských, a proto slouží k lokálním úpravám bez zásahu do společného základu.

## Produkty a feed

Výchozí produktový detail zajišťuje rodičovská šablona a lze jej upravit nebo přepsat v child. Veřejný JSON-LD katalog produktů je dostupný na `/wp-json/gln-pharma-product/v1/product-feed`. Feed vychází z publikovaných produktů a jejich ACF polí. Není to cenový Merchant Center feed a neobsahuje cenu ani skladovou dostupnost.

Pozor: feed zahrnuje všechny publikované produkty, i když je na jejich stránce zapnuté potvrzení pro odborníky. Toto potvrzení není zabezpečení ani omezení přístupu k feedu.

## Podrobné návody

- [Práce se šablonou v administraci](agency-guide.md)
- [Produkty, ACF a produktový feed](product-content-and-feed.md)
- [Rozšiřování child šablony](development-guide.md)
- [Projektové standardy](project-standards.md)
- [Elementor CSS proměnné](elementor-css-variables.md)
