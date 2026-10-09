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
[wr_shared_content key="contact.distributor" field="phone" link="1"]
[wr_shared_content key="contact.distributor" field="email_primary" link="1"]
[wr_shared_contact key="contact.distributor" format="address"]
[wr_shared_contact key="contact.distributor" format="contact"]
[wr_shared_content key="legal.privacy" format="content"]
```

`wr_shared_content` přijímá `key`, volitelné `field`, `format` a `link`. U telefonu vytvoří `link="1"` odkaz `tel:`, u e-mailu `mailto:`. `wr_shared_contact` podporuje formáty `address` a `contact` (také alias `footer`). Formát `auto` vybere výstup podle typu položky: kontakt, HTML obsah nebo odkaz.

V Elementoru je k dispozici widget **Glenmark → Sdílený obsah Glenmark**. Umožňuje vybrat publikovanou položku, způsob zobrazení a případné konkrétní pole. Oba způsoby jsou živé; změna v Content Hubu se po obnovení cache projeví bez editace stránky.

## Editovatelná kopie

V Gutenberg editoru otevřete postranní panel **Sdílený obsah Glenmark**, vyberte položku a zvolte **Vložit / aktualizovat kopii**. Vloží se blok s obsahem převedeným do běžných Gutenberg bloků. Uvnitř lze text i formátování dále upravovat.

Blok uchovává klíč a verzi zdrojové položky. Panel ukazuje, zda je kopie aktuální. Pokud zdroj dostal novou verzi, po obnovení nabídky lze daný blok nahradit. Nahradí se pouze označený sdílený blok; jeho lokální úpravy se přepíší, proto editor výměnu výslovně potvrzuje. Obsah mimo něj zůstane nedotčený.

## Cache a dostupnost

Šablona cachuje seznam na 5 minut a používá ETag zdroje. Při dočasném výpadku centrálního webu ponechá poslední úspěšně načtená data až 7 dní. Neznámý nebo nepublikovaný klíč vykreslí prázdný obsah. REST API se volá ze serveru cílového WordPressu, nikoli z návštěvníkova prohlížeče.

## Veřejné API a bezpečnost

Čtecí API je veřejné. Do centrálního Content Hubu proto publikujte pouze údaje určené ke zveřejnění. Editorové načtení aktuální nabídky používá REST proxy cílového webu chráněnou oprávněním `edit_posts` a nonce WordPressu.