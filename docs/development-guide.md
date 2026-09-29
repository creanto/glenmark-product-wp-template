# Rozšiřování child šablony

Tento dokument je pro vývojáře agentur. Závazná pravidla názvů a umístění kódu jsou v [projektových standardech](project-standards.md); tento návod je doplňuje o konkrétní architekturu Glenmark Product.

## 1. Dědičnost a rozhodnutí, kam změna patří

| Potřeba | Doporučené místo |
| --- | --- |
| Společné chování všech webů Glenmark v ČR | Rodičovská šablona, vlastní modul pod `inc/` |
| Barvy, výška hlavičky, varianty header/footer | Parent `config/theme-config.php` jako výchozí hodnota; child `site-specific/config/theme-config.php` jako override |
| Nastavení spravované redakcí | WordPress Customizer, nastavení stránky nebo Elementor Site Settings |
| Vzhled jednoho konkrétního webu | Child SCSS/CSS v `site-specific/assets/css/` |
| Specifická logika jednoho webu | Child `functions.php`; větší soubor lze vytvořit v child `site-specific/` a explicitně jej načíst z child `functions.php` |
| Změna HTML hlavičky, zápatí či detailu produktu | Child template part nebo child template stejné relativní cesty |
| Nový obsah / produktová data | WordPress záznamy a ACF, ne pevný text v šabloně |

Rodičovská šablona se načítá jako základ. Child `functions.php` se spouští navíc; nevytvářejte v něm znovu obecné funkce rodiče. WordPress při `get_template_part()` a `locate_template()` hledá odpovídající soubor v child šabloně před rodičem. Produktový detail explicitně používá `single-wr_product.php`; child může dodat soubor stejného jména.

Soubory assetů vyhledávané přes `webrev_get_theme_file_path()` a `webrev_get_theme_file_uri()` mohou být přepsány stejnou relativní cestou v child. Tato možnost se týká jen míst, která dané helpery používají.

## 2. `theme-config.php`

Rodič načte `config/theme-config.php` a následně, pokud existuje, sloučí `site-specific/config/theme-config.php` z aktivního stylesheet (child) adresáře. Sloučení používá `array_replace_recursive()`: child hodnoty přepisují odpovídající klíče rodiče, zatímco neuvedené klíče zůstávají zachované.

K hodnotám přistupujte přes `webrev_config('cesta.k.hodnote', $default)`. Nepřistupujte přímo k souboru konfigurace z náhodného modulu. Hlavní aktuální skupiny jsou:

- `layout.container_width`;
- `layout.header_variants` a `layout.footer_variants` s popiskem a relativní cestou k template partu;
- výchozí hodnoty hlavičky v `layout.header`;
- `colors` a `typography.font_family`.

Do konfigurace patří hodnoty, ne algoritmy. WordPress Customizer hodnoty se ukládají jako `theme_mod` a jsou jiným mechanismem než `theme-config.php`.

**Pozor:** klíče `modules.acf`, `modules.cpt` a `modules.elementor` jsou nyní pouze deklarativní. `inc/bootstrap.php` je načítá bez kontroly těchto hodnot, takže jejich změna funkce modulů nevypne.

### Přidání varianty hlavičky nebo zápatí

1. Přidejte template part do child `site-specific/template-parts/header/` nebo `footer/`.
2. Přidejte variantu do odpovídajícího `layout.header_variants` nebo `layout.footer_variants` v child configu.
3. Template zapisujte jako přenositelné PHP: data escapujte přes příslušné WordPress funkce a vzhled ponechte v CSS.
4. Variantu ověřte v **Vzhled → Přizpůsobit** a také jako výchozí i transparentní režim.

Rodičovské varianty jsou v `template-parts/header/` a `template-parts/footer/`. K vykreslení se používá `webrev_get_template_part()`.

## 3. Barvy, písma a kaskáda CSS

Globální barvy a typografii nastavte v Elementor Site Settings a odpovídající Elementor Kit importujte pro daný web. JSON exporty jsou v `elementor-site-settings/`; Kit je samostatný importní artefakt, nikoli živá synchronizace s PHP konfigurací.

Při psaní CSS/SCSS dodržujte tuto prioritu:

1. Elementor globální tokeny jsou základ (`--e-global-color-*`, `--e-global-typography-*`).
2. Rodičovské SCSS přidává obecné layouty a komponenty.
3. Child CSS se načítá po stylu rodiče a může upravit vzhled konkrétního webu.
4. Vyšší CSS specificita může přebít obecné nastavení Elementoru; selektory proto držte co nejužší a `!important` používejte jen výjimečně.

Viz [referenci Elementor CSS proměnných](elementor-css-variables.md). Nevytvářejte vlastní aliasy pro Elementor barvy a písma, pokud už lze použít `--e-*` token. Hodnota fallback patří přímo do CSS vlastnosti.

Parent source SCSS je v `assets/css/`; hlavní vstup je `theme.scss`, výstup je `theme.css`. U child je aktuální source `site-specific/assets/css/site.scss` a načítaný výstup `site-specific/assets/css/site.css`; písma mají `fonts.scss`/`fonts.css`. `site.css` je načten až po `webrev-theme`. CSS neupravujte bez odpovídající úpravy SCSS. Child `functions.php` načítá CSS, nikoli SCSS; produkční web potřebuje aktuální zkompilovaný výstup.

Rodičovský `config/theme-config.php` také předává vybrané hodnoty do inline CSS a může načítat Google Font Open Sans podle `typography.font_family`. Nezaměňujte tuto konfiguraci za plnou synchronizaci s Elementor Kit.

## 4. PHP a hooky

- Každý PHP modul začíná ochranou `ABSPATH`.
- Obecné funkce používají prefix `webrev_`; child-only funkce mají jasný site prefix.
- Obecný modul patří do jediného odpovědného souboru pod `inc/` a musí být explicitně připojen v `inc/bootstrap.php`.
- Kód pro konkrétní web patří do child `functions.php` nebo do child souboru, který je z něj explicitně načten.
- Vstupy validujte, výstupy escapujte (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`) a používejte nonce/capability check u zápisových endpointů.
- Pro sdílenou odchylku přidejte pojmenovaný filtr nebo akci; v child hooku zachovejte priority a počet argumentů.
- Nepřidávejte produktové výjimky do obecných helperů nebo widgetů.

Rodičovská šablona má například filtr `wr-pharma-product/elementor/theme_style_scope`. Ne každý modul má rozšiřovací filtr. Pokud child potřebuje upravit interní ACF mapping, schema nebo výstup feedu, nejprve posuďte, zda je změna obecná; pro sdílený use case doplňte filtr do rodiče místo kopírování celého modulu.

## 5. Elementor a obsahové moduly

Widgety se registrují v `inc/elementor/register-widgets.php`; konkrétní implementace jsou v `inc/elementor/widgets/`. Při přidání widgetu:

1. vytvořte třídu podle okolního vzoru a ověřte, že Elementor je dostupný;
2. přidejte soubor i třídu do registrátoru;
3. použijte stabilní ACF/CPT data, escaping a vlastní názvy kontrol;
4. otestujte editor, front-end, prázdný stav a mobil;
5. obecný widget neváževejte na jednu stránku nebo jeden Elementor Kit.

Zvláštní logika hlavičky a expert gate rozšiřuje nastavení Elementor dokumentu. Custom element Scalable Canvas se registruje jen při dostupné podpoře Container. Widget Product Filters je v registrátoru, ale odpovídající shortcode `wr_product_filters` nyní vrací prázdný řetězec; před rozšířením nebo použitím jej považujte za nedokončený.

Soubor `inc/elementor-json-editor.php` přidává administrační editor Elementor JSON s REST endpointy chráněnými oprávněním `manage_options`. Při práci s ním zálohujte dokument; nejde o veřejné API.

## 6. Pluginy a integrace

Požadavky a doporučení udržujte v `inc/plugin-activation.php`. TGM nabízí povinné pluginy Elementor a CookieYes a volitelný Git Updater. ACF PRO je samostatná licencovaná závislost pro repeater a galerii použitou produktovým modelem; při chybějící PRO verzi se administrátorům zobrazí upozornění a ACF Free není dostačující. Plugin ani jeho ZIP/licenční klíč se do repozitáře nevkládá. Git Updater je doporučený plugin pro aktualizace rodičovské šablony z veřejného GitHub repozitáře; jeho TGM zdroj je instalační ZIP konkrétního vydání a případně je potřeba posunout jej při změně vydávané verze. Git Updater vyžaduje PHP 8.0+. Pro správu souhlasu s cookies a blokování externích skriptů je zvolen WPConsent. Kompatibilitu vlastního Elementor video placeholderu s jeho blokováním a souběh se stávající integrací CookieYes ověřte na stagingu před nasazením; automaticky ji nepředpokládejte.

### Vydání aktualizace rodičovské šablony

1. Zvyšte `Version` v hlavičce `style.css` (například z `1.0.0` na `1.0.1`).
2. Vytvořte na GitHubu release s odpovídajícím tagem, například `v1.0.1`; nevydávejte jej jako prerelease.
3. Na webu s aktivním Git Updaterem spusťte kontrolu aktualizací nebo vyčkejte na pravidelnou kontrolu. Git Updater porovnává verzi šablony s vydáním v propojeném GitHub repozitáři.
4. Novou verzi nejprve ověřte na stagingu. Aktualizace přepisuje soubory rodičovské šablony, proto vlastní úpravy patří do child šablony.

Při přidávání pluginu rozlišujte závislost od doporučení. Ověřte kompatibilitu a konflikty s vlastními hlavičkami/zápatími, Elementor, SEO, cache a cookie consent. Plugin nesmí být považován za součást šablony jen proto, že je nainstalovaný v místním workspace.

## 7. Produkty a feed

CPT, ACF pole, rodičovský single template, JSON-LD i veřejný feed jsou popsány v [návodu k produktům a feedu](product-content-and-feed.md). Změna názvu ACF pole může ovlivnit více vrstev najednou; před refaktorem vyhledejte všechna jeho použití.

Feed je veřejný a obsahuje všechny publikované produkty. Expert gate není přístupová kontrola. Feed nemá cenu ani dostupnost a není Merchant Center XML exportem.

## 8. Kontrola změny

Před předáním ověřte:

- aktivní child theme, žádné úpravy ztracené v rodiči;
- PHP syntaxi, lint a testy dostupné v daném projektu;
- mobil/desktop, editor Elementor i front-end;
- Customizer globální hodnoty a page-specific override;
- produkt s vyplněnými i prázdnými ACF poli;
- JSON-LD stránky a REST feed, veřejnou dostupnost obrázků a dokumentů;
- consent video, SEO duplicity a cache;
- že dokumentace odpovídá skutečné cestě zdroje a generovaného souboru.
