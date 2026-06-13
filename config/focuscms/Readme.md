# FocusCMS Config Overrides

Ez a mappa opcionális konfigurációs override fájlok számára szolgál.

A FocusCMS automatikusan beolvassa az itt található PHP config fájlokat,
és merge-eli őket az alkalmazás alap konfigurációival.

## Mire való?

Ez a mappa lehetővé teszi, hogy a CMS viselkedését projekt szinten módosítsd
anélkül, hogy a core config fájlokat módosítanád.

Ez különösen hasznos:

- projekt specifikus taxonomy módosításokhoz
- multilingual route slugokhoz
- CMS feature konfigurációhoz
- fejlesztői override-okhoz

## Hogyan működik?

Ha létezik például:

config/taxonomies.php

és létrehozod:

config/focuscms/taxonomies.php

akkor a FocusCMS a kettőt összevonja:

core config + override config

Az override értékek felülírják a core értékeket.

## Példa

Core config:

config/taxonomies.php

```php
return [

    'categories' => [

        'title' => 'Category',

        'hierarchical' => true

    ]

];