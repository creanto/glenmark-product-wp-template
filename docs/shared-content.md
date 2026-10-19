# Sdílený obsah Glenmark

Rodičovská šablona může číst publikované položky z pluginu Glenmark Content Hub. Nastavení je v **Vzhled → Přizpůsobit → Sdílený obsah Glenmark**.

## Nastavení cílového webu

Do pole **REST API URL** vložte základní adresu API centrálního webu, například:

```text
https://content.example.cz/wp-json/glenmark-content/v1
```

Koncové `/items` se připojí automaticky. Zástupné hodnoty konkrétního webu nastavte ve stejné sekci Customizeru:

- `{{site_name}}` – název webu, výchozí je název WordPress webu;
- `{{site_url}}` – URL webu, výchozí je jeho domovská adresa;
- `{{data_controller_name}}` – správce osobních údajů;
- `{{data_controller_address}}` – adresa správce osobních údajů.

Tyto tokeny se nahrazují pouze v HTML obsahu z centrálního zdroje. Výslednou právní stránku vždy zkontrolujte před publikováním.

## Živý obsah

Shortcode načítá aktuální publikovanou hodnotu z centrálního zdroje:

```text
[gln_shared_content key="contact.distributor" field="phone" link="1"]
[gln_shared_content key="contact.distributor" field="email_primary" link="1"]
[gln_shared_contact key="contact.distributor" format="address"]
[gln_shared_contact key="contact.distributor" format="contact"]
[gln_shared_content key="legal.privacy" format="content"]
```

`gln_shared_content` přijímá `key`, volitelné `field`, `format` a `link`. U telefonu vytvoří `link="1"` odkaz `tel:`, u e-mailu `mailto:`. `gln_shared_contact` podporuje formáty `address` a `contact` (také alias `footer`). Formát `auto` vybere výstup podle typu položky: kontakt, HTML obsah nebo odkaz.

V Elementoru jsou samostatné widgety **Glenmark → Sdílená šablona** a **Glenmark → Krátký sdílený údaj**. Šablona nabízí pouze položky s HTML obsahem a podporuje úpravu typografie, barvy, pozadí, zarovnání, odsazení a okraje. Verze, datum a schvalovací číslo z Content Hubu se zobrazí drobným písmem vpravo pod obsahem, jsou-li vyplněné. Widget krátkého údaje nabízí kontaktní položky a jejich celé zobrazení, adresu nebo jednotlivá pole. Starší widget zůstává registrovaný kvůli existujícím stránkám.

Oba widgety a shortcody zobrazí serverově vykreslený obsah jako okamžitý fallback a po načtení stránky jej obnoví přes REST endpoint cílového webu. Změny ze zdroje se tak projeví bez editace stránky, i když HTML stránky drží cache nebo CDN. Pětiminutovou cache a ETag lze vypnout konstantou `GLENMARK_SHARED_CONTENT_CACHE_ENABLED` v `inc/shared-content/api.php`; výchozí hodnota je `true`. Při `false` se Hub ověřuje při každém požadavku, ale poslední úspěšná data se stále ukládají na 7 dní jako fallback při výpadku.

Pro text lze použít `[gln_shared_content key="contact.distributor" field="phone"]`; pro odkazovaný telefon nebo e-mail přidejte `link="1"`. `[gln_shared_contact key="contact.distributor" format="address"]` vykreslí adresu a `format="contact"` celý kontakt. Shortcody jsou živé stejně jako Elementor widgety. V dynamickém výběru standardních Elementor polí textu a URL je dostupný tag **Glenmark: krátký sdílený údaj**; funguje i v bezplatném Elementoru, pokud dané pole podporuje dynamické tagy.

## Editovatelná kopie

V Elementoru lze v běžném widgetu **Textový editor** v záložce Obsah vybrat sdílenou šablonu a kliknout na **Vložit jako editovatelnou kopii**. Do standardního WYSIWYG pole se vloží HTML s nahrazenými tokeny `{{…}}`; dále se upravuje lokálně a změny v hubu jej samy nepřepíší. Vložení nahradí celý současný obsah Textového editoru a vyžaduje potvrzení.

V Gutenberg editoru otevřete postranní panel **Sdílený obsah Glenmark**, vyberte položku a zvolte **Vložit / aktualizovat kopii**. Vloží se blok s obsahem převedeným do běžných Gutenberg bloků. Uvnitř lze text i formátování dále upravovat. Jde o uloženou kopii, nikoli živý widget; změnu zdroje je potřeba vložit/aktualizovat ručně.

Blok uchovává klíč a verzi zdrojové položky. Panel ukazuje, zda je kopie aktuální. Pokud zdroj dostal novou verzi, po obnovení nabídky lze daný blok nahradit. Nahradí se pouze označený sdílený blok; jeho lokální úpravy se přepíší, proto editor výměnu výslovně potvrzuje. Obsah mimo něj zůstane nedotčený.

## Cache a dostupnost

Při zapnuté konstantě šablona cachuje seznam na 5 minut a používá ETag zdroje. Při dočasném výpadku centrálního webu ponechá poslední úspěšně načtená data až 7 dní bez ohledu na nastavení pětiminutové cache. Neznámý nebo nepublikovaný klíč vykreslí prázdný obsah. REST API se volá ze serveru cílového WordPressu, nikoli z návštěvníkova prohlížeče.

## Veřejné API a bezpečnost

Čtecí API je veřejné. Do centrálního Content Hubu proto publikujte pouze údaje určené ke zveřejnění. Frontend po načtení stránky obnovuje obsah přes veřejný REST endpoint cílového webu; spojení z cílového webu na Hub zůstává server-server. Editorové načtení aktuální nabídky používá samostatnou REST proxy cílového webu chráněnou oprávněním `edit_posts` a nonce WordPressu.