# Práce se šablonou Glenmark Product

Návod popisuje běžné založení a správu webu agenturou. Rodič obsahuje obecné funkce, komponenty a univerzální výchozí nastavení. Branding, fonty, ikony a seznam lékáren patří do child šablony; referenční a vývojová šablona je Cetalgen.

## 1. Co šablona poskytuje

Základem je WordPress, bezplatný Elementor a rodičovská šablona Glenmark Product. Rodič přidává vlastní Elementor widgety, typy obsahu, produktový detail, konfiguraci hlavičky a zápatí a integrace popsané níže. Elementor Pro není závislostí šablony; lze jej doplnit, pokud projekt potřebuje jeho funkce.

Šablona nabízí tyto vlastní Elementor widgety:

- Product Grid a Product Carousel: seznam a karusel produktů.
- Product Box: samostatné produktové pole.
- Pharmacy Grid: přehled lékáren.
- Post Grid: výpis článků.
- Benefits, CTA, Rich Heading a Breadcrumbs.
- Sdílená šablona: živé HTML šablony z Content Hubu včetně stylování a metadat dokumentu.
- Krátký sdílený údaj: živé kontakty, adresy nebo jednotlivá pole; použít lze také shortcode a dynamický tag.
- Product Filters: widget je zaregistrovaný, ale napojený shortcode nyní vrací prázdný obsah. Pro produkční filtr jej nepoužívejte, dokud nebude jeho funkce doplněna.

Samostatný prvek Hero/Scalable Canvas vyžaduje Elementor s podporou Container. Dostupnost této funkce se řídí verzí Elementoru; ověřte ji na cílovém webu.

## 2. Požadavky a pluginy

Šablona nabízí pluginy přes TGM Plugin Activation. Seznam je definovaný v `inc/plugin-activation.php`; Elementor a WPConsent jsou povinné. TGM nezobrazuje upozornění na neaktivní doporučené pluginy ani na dostupné aktualizace.

| Plugin | Úloha | Stav podle šablony |
| --- | --- | --- |
| Elementor | Tvorba a editace obsahu; šablona registruje vlastní widgety. | Povinný |
| WPConsent | Správa souhlasu s cookies a blokování sledovacích skriptů. | Povinný |
| ACF PRO | Správa produktových polí včetně repeaterů a galerie. | Nutný pro kompletní sadu polí; instaluje se samostatně z licencovaného zdroje |
| All in One SEO | SEO nástroje a metadata. | Doporučený, volitelný |
| Classic Editor | Klasický editor příspěvků. | Doporučený, volitelný |
| WP Super Cache | Cachování. | Doporučený, volitelný |
| Wordfence Security | Bezpečnostní kontrola a ochrana. | Doporučený, volitelný |
| UpdraftPlus | Zálohy. | Doporučený, volitelný |
| Regenerate Thumbnails | Přegenerování náhledů po změně rozměrů. | Doporučený, volitelný |
| WP Statistics | Statistiky návštěvnosti. | Doporučený, volitelný |
| User Switching | Přepnutí uživatelského účtu při podpoře. | Doporučený, volitelný |
| Git Updater | Aktualizace rodičovské šablony z GitHubu. | Doporučený, volitelný; vyžaduje PHP 8.0+ |

TGM nabízí povinné pluginy Elementor a WPConsent a volitelný Git Updater z GitHubu. WPConsent se instaluje z WordPress.org; licence Pro a její aktivace se spravují samostatně. Pokud není aktivní ACF PRO, administrátorům se zobrazí samostatné upozornění. ACF Free není dostačující náhrada; ACF PRO je nutné nainstalovat z licencovaného zdroje. Šablona plugin ani licenci ACF PRO nedodává. Ověřte na stagingu blokování vložených videí a skriptů pomocí nastavení WPConsent.

WPConsent Pro se instaluje a licencuje podle správy licencí projektu. Licenční klíč se nesmí uložit do Gitu ani dokumentace. Nastavte banner a blokování podle schválených právních požadavků a na stagingu ověřte vložená videa.

Git Updater je potřeba aktivovat, aby WordPress mohl nabídnout aktualizace rodičovské šablony z GitHubu. Nabídku aktualizace najdete na stránce **Nástěnka → Aktualizace** nebo **Vzhled → Šablony**. Úpravy konkrétního webu ponechte v child šabloně; aktualizace rodiče přepisuje jeho soubory.

Další bezplatné nebo placené pluginy lze přidat podle zadání webu. Před nasazením ověřte kompatibilitu, duplicitu funkcí (zejména SEO, cache a cookie consent), licenci, aktualizace a chování na stagingu. Elementor Pro je volitelný, nikoli podmínka pro vlastní widgety šablony.

### Provozní požadavky a doporučení

Repozitář zatím nedefinuje ani netestuje minimální verzi WordPressu nebo PHP pro samotnou rodičovskou šablonu. Nepovažujte proto konkrétní starší verzi za garantovanou; při nasazení použijte aktuálně podporovaný WordPress a verze PHP vyžadované všemi aktivními pluginy. Požadavky hostingu WordPressu se průběžně mění, proto je ověřte v [oficiálních požadavcích WordPressu](https://wordpress.org/about/requirements/). Pokud používáte Git Updater, počítejte nejméně s PHP 8.0; jeho novější vydání mohou požadavky zvýšit.

Pro spolehlivý provoz dále platí:

- Používejte HTTPS a udržovaný hosting s databází podporovanou aktuální verzí WordPressu.
- Před aktualizací šablony, WordPressu nebo pluginů udělejte zálohu souborů i databáze a změny nejprve ověřte na stagingu. Automatické aktualizace rodičovské šablony zapínejte jen tehdy, pokud máte ověřenou zálohu a postup obnovy.
- WordPress musí mít možnost zapisovat do `wp-content/themes`, jinak aktualizace jedním kliknutím nemusí proběhnout. Pokud hosting zápis blokuje, použijte jeho doporučené nastavení přihlašovacích údajů k souborovému systému.
- Git Updater vyžaduje dostupnost GitHubu z hostingu a aktivní plugin. Aktualizace nejsou okamžité oznámení při vydání; kontrola probíhá při běžném mechanismu aktualizací WordPressu. Soukromý repozitář navíc vyžaduje přístupové údaje Git Updateru.
- Po aktualizaci vymažte cache WordPressu, serveru a CDN. Ověřte front-end i Elementor editor, produktové stránky, ACF data, souhlas s cookies, REST feed a šablonové přepisy v child šabloně.
- V produkci nezobrazujte návštěvníkům PHP chyby; logování chyb ponechte dostupné správci nebo hostingu.

## 3. Doporučený postup založení webu

1. Nainstalujte a aktivujte rodičovskou Glenmark Product a její child šablonu; jako aktivní téma používejte child.
2. Nainstalujte Elementor, WPConsent a ACF PRO. Aktivujte licenci WPConsent Pro podle správy licencí projektu a ACF PRO instalujte z licencovaného zdroje. Aktivujte další pluginy podle schváleného rozsahu projektu.
3. Importujte správný Elementor Site Settings Kit. V repozitáři jsou JSON nastavení v `elementor-site-settings/`; samostatně může být připraven i balíček pro child web. Import proveďte přes Elementor Import/Export Kit a zkontrolujte globální barvy, písma a nastavení webu.
4. Vytvořte nebo importujte obsah a ACF záznamy. Produktová data zadávejte do CPT Produkty, ne pouze do volného textu stránky.
5. Nastavte loga, navigaci, hlavičku, zápatí a sociální odkazy v administraci.
6. Dolaďte konkrétní web v child šabloně a otestujte zobrazení na mobilu, souhlasy cookies, produktový detail a feed.

Import Elementor Kit může přepsat globální nastavení Elementoru. Před importem exportujte nebo jinak zálohujte současný Kit. Rodičovský `config/theme-config.php` a Elementor Kit jsou samostatné zdroje hodnot; jejich barvy se automaticky nesynchronizují.

## 4. Hlavička, branding a zápatí

V administraci otevřete **Vzhled → Přizpůsobit**. Dostupné sekce zahrnují:

- **Branding**: primární logo, logo pro transparentní hlavičku, favicon a odkazy na Facebook, Instagram a YouTube.
- **Header**: varianta hlavičky, globální režim Normal/Transparent/Glassy/Glassy dark, sticky chování, zmenšení při scrollu, výška, maximální šířka loga, pozadí/obrázek a barva navigace.
- **Footer**: výběr stránky pro obsah zápatí; lze použít Elementor stránku. Pokud není vybraná stránka s obsahem, vykreslí se standardní varianta zápatí.
- **Site controls**: tlačítko zpět nahoru.
- **Notice**: volitelný obsahový pruh v patičce.
- **Expert only notice**: text, popisky tlačítek a URL po odmítnutí.

Menu spravujte ve **Vzhled → Menu** a přiřaďte je do umístění Primary Menu a Footer Menu. Položku menu lze označit jako Hero.

### Nastavení hlavičky pro jednu stránku

Globální nastavení hlavičky lze pro konkrétní stránku nebo podporovaný obsah přepsat v nastavení dokumentu Elementor. Dostupné volby zahrnují režim hlavičky, sticky, shrink, skrývání při scrollu, zobrazení loga po scrollu a výběr navigačního menu. V klasickém editoru je k dispozici také metabox nastavení hlavičky. Hodnota **Use global setting** dědí globální volbu; ostatní volby ji přepíší pouze na dané stránce.

Transparentní režim vyžaduje odpovídající úvodní vizuál a kontrastní logo/navigaci. Pokud je zapnutý, použije se samostatné transparentní logo, pokud je nastavené.

## 5. Stránky a Elementor

Běžné landing pages tvořte v Elementoru z jeho bezplatných funkcí a vlastních widgetů šablony. Globální styly spravujte v **Elementor → Nastavení webu / Site Settings**. Rodičovská šablona nevyžaduje Elementor Pro pro registraci vlastních widgetů.

Widgety Product Grid, Product Carousel, Product Box a Pharmacy Grid čerpají z CPT/ACF dat. Nastavení widgetu určují výběr nebo zobrazení položek; vlastní obsah produktů se upravuje v jejich záznamu.

Nové příspěvky mají automaticky dostat šablonu článku `template-article.php`. Při ruční změně ověřte výsledek na front-endu.

## 6. Produkty a obsah

Produkty vytvářejte v administraci pod **Produkty**. Vyplňte název, podklady, obrázky, popisy a odkazy podle [návodu k produktům a feedu](product-content-and-feed.md). Výchozí detail renderuje rodičovská šablona. Obsah hlavního dlouhého popisu může být tvořen ACF polem, nebo Elementor obsahem produktu.

Lékárny jsou samostatný CPT. Každý záznam má logo a cílovou URL. Odkazy konkrétních lékáren na produktu se nastavují v polích produktu, případně u jednotlivých variant balení.

## 7. Souhlas s cookies a video

Správa banneru souhlasu a blokování skriptů probíhá přes WPConsent. Nastavení kategorií a pravidel blokování musí odpovídat schváleným právním požadavkům webu; funkčnost vložených videí ověřte na stagingu.

V anonymním okně ověřte Elementor videa YouTube/Vimeo: před souhlasem se nesmí načíst jejich externí obsah a po udělení příslušného souhlasu musí jít video spustit. Vlastní video placeholder šablony vyžaduje samostatný test kompatibility s aktivním consent pluginem; automatické propojení nepředpokládejte.

## 8. Před předáním webu

- Zkontrolujte desktop i mobil, navigaci, obě varianty loga a všechny stavy hlavičky.
- Ověřte, že produktová stránka zobrazuje publikovaná ACF data a že URL feedu vrací validní JSON.
- Prohlédněte si stránku bez souhlasu s cookies a po jeho udělení.
- Ověřte, že SEO plugin a šablona nevypisují konfliktní nebo duplicitní strukturovaná data.
- Zkontrolujte zálohování, cache, formuláře/linky, licenci placených pluginů a právní schválení zdravotnického obsahu.
