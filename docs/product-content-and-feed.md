# Produkty, ACF a produktový feed

Rodičovská šablona registruje vlastní typ obsahu `gln_product` (v administraci **Produkty**) a taxonomii kategorií produktů. Stránka produktu čerpá obsah z ACF. Pro kompletní pole včetně repeaterů a galerie je potřeba ACF PRO.

## 1. Jak připravit produkt

1. Vytvořte nový záznam v **Produkty → Přidat produkt**.
2. Použijte titulek záznamu jako bezpečnou zálohu pro zobrazovaný název.
3. Vyplňte ACF skupiny podle jejich účelu. Pole používejte konzistentně; stejné hodnoty se propisují do detailu, widgetů i strukturovaných dat.
4. Nastavte náhledový obrázek nebo produktový packshot. Galerie a varianty balení jsou samostatná pole.
5. Před zveřejněním zkontrolujte detail produktu i mobilní zobrazení.

### Hlavní skupiny polí

| Skupina dat | Příklady polí | Použití |
| --- | --- | --- |
| Základní informace | Celý název, název, podtitul, kategorie, indikace, claim | Nadpisy, štítky a kategorizace |
| Texty | Krátký popis, popis, hlavní benefity, účinné/obsažené látky, složení, dávkování, upozornění | Detail produktu a kontext pro vyhledávače |
| Prodej a dokumenty | V prodeji, ukončení prodeje, VPOIS, SPC, domovská stránka | Informace a odkazy na detailu a ve feedu |
| Product schema | Brand, výrobce, SKU, GTIN-13, velikost balení, léková forma | Schema.org Product |
| Obrázky | Náhledový obrázek/packshot, galerie, obrázek varianty | Detail, widgety a feed |
| Lékárny | URL Benu, Dr. Max, lekarna.cz a dalších podporovaných sítí | Odkazy „kde koupit“ |
| Varianty balení | Název, počet, obrázek, odkazy na lékárny | Varianty na detailu i ve strukturovaných datech |
| Registrovaná balení | Síla, množství, kód SÚKL, SPC, PIL | Přehled registrovaných balení a dokumentace |
| Ikony | Obrázek, text, popis | Vizuální vlastnosti produktu |

Přesné názvy polí a klíče jsou definované v `inc/acf/product-fields.php`. Při jejich změně zkontrolujte také `single-gln_product.php`, widgety a výstup schema/feedu. Pole ACF jsou stabilní datové rozhraní; nepřejmenovávejte je jen kvůli textovému labelu.

## 2. Výchozí detail a jeho přepsání

Rodičovská šablona vykresluje detail přes `single-gln_product.php`. Obsahuje hlavní produktový vizuál, popisy, benefity, galerii, odkazy na lékárny a varianty balení. Pokud je dlouhý obsah produktu sestaven v Elementoru, šablona vykreslí i Elementor obsah produktu.

Child může dodat vlastní `single-gln_product.php`; WordPress/filtr `template_include` vybere child verzi před parent verzí. Doporučený postup je nejprve prozkoumat původní template, přepsat jej pouze pro skutečně site-specific markup a zachovat napojení na ACF. Pro menší změny preferujte child CSS/SCSS nebo hooky před kopírováním celé šablony.

## 3. Strukturovaná data na stránce

Rodič vypisuje JSON-LD `Product` na detailu produktu. Na homepage může vypisovat vybraný hlavní produkt označený polem `product_main_product`. Schema sestává z názvu, URL, popisu, obrázku, kategorie a dostupných brand/manufacturer/SKU/GTIN údajů. Rozšířený výstup přidává ACF specifika jako balení, lékovou formu, látky, indikace, dokumenty, lékárny a varianty.

Vyplňujte identifikátory jen ověřenými hodnotami. Nevyplňujte smyšlené SKU ani GTIN a nevytvářejte Offer, cenu nebo skladovou dostupnost, pokud pro ně neexistují odpovídající aktuální data.

## 4. Veřejný produktový feed

REST endpoint je veřejný a dostupný na:

```text
/wp-json/gln-pharma-product/v1/product-feed
```

Vrací JSON-LD objekt s `@context` a `@graph`, v němž je každý publikovaný `gln_product` záznam jako Schema.org `Product`. Odpověď má typ `application/ld+json` a veřejné cachování na jednu hodinu. HTML stránek obsahuje také `link rel="alternate"`, který feed zpřístupní crawlerům.

Feed znovu používá společnou funkci sestavení Product schema a přidává hodnoty z ACF: rozšířené popisy a upozornění, stav prodeje/VPOIS, SPC/PIL, web produktu, více obrázků, lékárenské odkazy, registrovaná balení, ikony a varianty balení.

Feed není Google Merchant Center XML feed: neobsahuje cenu, měnu ani skladovou dostupnost, protože tyto údaje nemají v současném ACF modelu zdroj. Pokud je některá integrace vyžaduje, je potřeba připravit zvláštní export a datový model, ne do JSON-LD doplnit odhad.

### Důležité: obsah určený odborníkům

Produktový feed aktuálně zahrnuje všechny publikované produkty, včetně těch, které mají na webu zapnutý expertní gate. Gate je prezentační potvrzovací vrstva, nikoli autentizace ani ochrana REST feedu. Pokud produktový obsah nesmí být dostupný veřejně, nezveřejňujte jej jako veřejný `gln_product` bez změny přístupového modelu a právního posouzení. Stejně tak je nutné ověřit, zda zobrazení citlivých údajů ve zdrojovém kódu stránky splňuje pravidla projektu.

## 5. Kontrola feedu

- Otevřete URL endpointu a ověřte stav HTTP 200 a validní JSON.
- Zkontrolujte, že feed obsahuje jen publikované produkty a že URL/image hodnoty vedou na veřejně dostupné zdroje.
- U jednoho produktu porovnejte názvy, texty, varianty, lékárny a dokumenty s ACF záznamem.
- Ověřte JSON-LD také na detailu stránky a zkontrolujte duplicity generované případným SEO pluginem.
- Po změně polí otestujte i widgety Product Grid/Carousel a výchozí detail.
