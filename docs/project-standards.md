# Standardy pro praci se sablonou

Tato sablona je zaklad pro vice farmaceutickych produktu. Kazda zmena musi proto zachovat dve vlastnosti:

- obecne funkce musi zustat znovu pouzitelne v dalsich instalacich;
- konkretni produkt musi byt mozno stylovat a konfigurovat bez uprav obecneho jadra.

Dokument je vychozi dohoda pro dalsi rozvoj sablony. Pri pridani noveho opakovane pouzitelneho reseni se ma tato pravidla rozsirit, ne obchazet.

## 1. Zakladni pravidla

### 1.1 Nejd drive urci rozsah zmeny

Pred implementaci zarad zmenu do jedne z techto kategorii:

- **jadro sablony**: chovani pouzitelne na vsech produktech;
- **modul**: samostatna funkcni cast, kterou lze zapnout, vypnout nebo znovu pouzit;
- **produktova konfigurace**: hodnoty konkretniho webu, napriklad barvy, rozlozeni nebo varianty headeru;
- **stranka nebo komponenta**: vzhled a chovani konkretniho typu stranky;
- **instalacni obsah**: Elementor, ACF data, obrazky a dalsi obsah konkretniho webu.

Pokud zmena patri do vice kategorii, rozdeli ji na mensi casti. Mechanismus patri do obecneho souboru, jeho nastaveni nebo vzhled patri do produktove casti.

### 1.2 Jedna odpovednost na soubor

Do existujiciho souboru pridavej kod jen tehdy, pokud patri ke stejne odpovednosti. Nova funkcni oblast patri do vlastniho souboru a tento soubor se nasledne explicitne nacte v `inc/bootstrap.php` nebo zaregistruje na odpovidajicim WordPress hooku.

Nedavej produktove vyjimky do univerzalnych helperu, obecnych widgetu ani do zakladnich komponent jen proto, ze je to rychlejsi.

### 1.3 Neznama budoucnost je duvod pro modularitu

Kod, ktery se muze objevit na dalsim produktu, pis obecne. Produktove hodnoty predavej jako konfiguraci, argumenty, filtry nebo data. Nekopiruj celou obecnou funkci kvuli jedne odlisnosti.

## 2. Dodrzovana adresarova struktura

### PHP a WordPress

- `inc/` - funkcni jadro sablony a registrace modulu.
- `inc/acf/` - ACF field groups podle domeny, napriklad produkty a lekarny.
- `inc/post-types/` - registrace custom post types.
- `inc/shortcodes/` - samostatne shortcode moduly.
- `inc/elementor/` - Elementor widgety a upravy jeho chovani.
- `template-parts/` - znovu pouzitelne casti sablony, napriklad varianty headeru a footeru.
- `config/theme-config.php` - konfigurace konkretni instalace nebo produktu.
- korenove template soubory (`front-page.php`, `single-*.php`, `archive.php`) - pouze orchestrace a vyber dat; slozite vykreslovani patri do `template-parts/` nebo widgetu.

### SCSS

- `assets/css/abstracts/` - promenne, barvy a mixiny bez vystupu CSS.
- `assets/css/base/` - reset a zakladni pravidla dokumentu.
- `assets/css/components/` - obecne komponenty pouzitelne napric webem, napr. header, tlacitka, cookies a animace.
- `assets/css/pages/` - styly konkretniho typu stranky, napr. home, product a article.
- `assets/css/fonts.scss` - nacitani fontu.
- `assets/css/theme.scss` - hlavni SCSS vstup a poradi importu.
- `assets/css/theme.css` - kompilovany vystup; rucne jej neupravuj.

Nova globalni komponenta patri do `components/`. Styl konkretni stranky patri do `pages/`. Pokud je styl pouzitelny na obou mistech, dej zaklad do komponenty a produktovou odchylku do prislusne stranky.

### JavaScript a dalsi assety

- `assets/js/` - obecne skripty sablony; vetsi funkcni celky maji vlastni soubor.
- `assets/slick/` a `assets/magnific/` - knihovny a jejich lokalni konfigurace.
- `img/` - obrazove podklady produktu; obecne assety nesmi byt svazane s jednim webem.

## 3. PHP standardy

### Obecne funkce

- Kazdy PHP soubor zacina kontrolou `ABSPATH`.
- Nazvy funkci, hooku, scriptu a stylu maji prefix `webrev_` nebo `webrev-` podle typu identifikatoru.
- Funkce maji jednu jasnou odpovednost a maji validovat vstupy.
- Opakovane hodnoty a vychozi nastaveni nenechavej roztrousene v kodu; patri do konfigurace nebo do pojmenovane konstanty.
- Univerzalni pristup ke konfiguraci pouzivej pres `webrev_config()` a `webrev_get_setting()`, ne pres prime cteni konkretniho souboru z libovolneho mista.
- Pri vystupu pouzivej odpovidajici WordPress escaping (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Nove moduly se musi nacitat z jednoho predvidatelneho mista, typicky `inc/bootstrap.php`.

### Konfigurace

`inc/theme-config.php` obsahuje obecne funkce pro nacteni a cteni konfigurace. Hodnoty konkretni instalace patri do `config/theme-config.php`.

Do konfigurace patri zejmena:

- barvy, rozmery a breakpointy konkretniho produktu;
- varianty headeru a footeru;
- zapnuti nebo vypnuti volitelnych modulu;
- cesty k produktovym sablonam nebo dalsi volby webu.

Do konfigurace nepatri algoritmy. Ty patri do obecneho modulu, ktery konfiguraci cte.

### Sablony, widgety a data

- Template soubory maji ridit tok vykresleni, ne obsahovat rozsahlou business logiku.
- Opakovane HTML presun do `template-parts/` nebo vlastniho Elementor widgetu.
- Data priprav v PHP a do sablony predavej v jasne pojmenovanych promennych.
- Produktove CSS tridy, texty a nastaveni nesmi byt zadratovane v obecnem widgetu, pokud je lze predat pres data nebo konfiguraci.
- ACF pole organizuj podle domeny v `inc/acf/`; jejich nazvy a klice musi byt stabilni napric instalacemi.

## 4. SCSS standardy

### Zdrojovy kod

- Vsechny nove styly se pisi do `.scss` souboru. Kompilovane `.css` soubory se neupravuji rucne.
- Po zmene SCSS se kompiluje pouze lokalne podle workflow projektu; sablona nema kompilaci provadet za behu WordPressu.
- `theme.scss` zustava vstupnim souborem. Poradi importu respektuje: `abstracts` -> fonty -> `base` -> `components` -> `pages`.
- Promenne, mapy, mixiny a funkce patri do `abstracts/`. Nevytvarej je znovu v jednotlivych strankach.
- Pouzivej nesting, dedicnost a mixiny jen tam, kde zlepsuji strukturu. Nadmerny nesting a `!important` jsou posledni moznost.
- Produktove hodnoty drz v promennych a konfiguraci; opakovane barvy, mezery, radiusy a stiny nepiste jako nahodne literaly.
- Pro Elementor globalni barvy a typografii pouzivej jeho CSS custom properties podle `docs/elementor-css-variables.md`.
- Globalni pravidlo patri do `components/` nebo `base/`; selektor vazany na jednu stranku patri do `pages/`.

Priklad rozdeleni:

```scss
// components/_product-card.scss
.product-card {
  border-radius: $wr-radius;
  color: $wr-color-text;
}

// pages/_product.scss
.single-wr_product .product-card {
  // Odchylka platna pouze pro produktovou stranku.
}
```

### Kompilace

SCSS je zdroj pravdy. Do repozitare patri i CSS vystup, pokud jej pouziva WordPress, ale po kazde zmene musi byt vytvoren automatizovanym VS Code workflow. Nikdy neupravuj jen `theme.css` bez odpovidajici zmeny ve SCSS.

## 5. JavaScript a assety

- Obecne chovani patri do samostatneho souboru v `assets/js/`.
- Skript se nacita jen tehdy, kdyz jej stranka nebo modul potrebuje; zavislosti registruj explicitne.
- Produktove interakce nepatri do obecneho `theme.js`, pokud nemaji obecne pouziti.
- Enqueue, verze a zavislosti spravuj v `inc/assets.php` nebo v samostatnem asset modulu, pokud rozsah naroste.
- Knihovny tretich stran neupravuj primo; lokalni nastaveni drz oddelene od knihovnich souboru.

## 6. Jak pridavat novou funkci

1. Urc rozsah: jadro, modul, konfigurace, stranka nebo obsah.
2. Najdi nejmensi existujici vlastni soubor se stejnou odpovednosti.
3. Pokud soubor neexistuje, vytvor novy v odpovidajici podslozce `inc/`, `assets/` nebo `template-parts/`.
4. Obecnou cast napis bez konkretniho produktu a produktove hodnoty pridej do `config/theme-config.php`, dat nebo samostatne stranky.
5. Novy PHP modul pripoj v `inc/bootstrap.php`; novy SCSS soubor importuj v `assets/css/theme.scss`.
6. Zkontroluj escaping, prefixy, zavislosti a zda zmena nezasahuje jiny typ stranky.
7. SCSS zkompiluj automatizovanym workflow a over vzhled desktopu i mobilu.
8. Aktualizuj tento dokument nebo specializovanou dokumentaci, pokud pribylo nove pravidlo nebo modul.

## 7. Kontrolni seznam pred predanim

- [ ] Obecne jadro neobsahuje hodnoty konkretniho produktu.
- [ ] Produktova konfigurace je v `config/theme-config.php` nebo v jasne oddelenem souboru.
- [ ] Nova funkcni oblast ma vlastni soubor a odpovidajici umisteni.
- [ ] PHP obsahuje `ABSPATH` ochranu, prefix a WordPress escaping.
- [ ] SCSS zmena je ve zdrojovem `.scss`, ne pouze v `.css`.
- [ ] Globalni styly nejsou smichane se styly konkretni stranky.
- [ ] Zmena je nacitana explicitne a nema zbytecne globalni zavislosti.
- [ ] Overen je dopad na dalsi sablony a responsivni chovani.