<?php

/**
 * config/init.php
 */

return [

    /**
     * A CMS inicializálásakor az alábbi konfigurációs kulcsok alapján
     * opciók kerülnek létrehozásra az `options` táblában, ha még nem léteznek.
     *
     * A tömb elemei konfigurációs útvonalak (config path).
     *
     * Feldolgozási szabályok:
     *
     * - Ha a config path `validation_rules.options.*` formátumú,
     *   akkor a konfigurációban található kulcsok kerülnek létrehozásra
     *   az `options` táblában.
     *
     * - A létrehozott opciók alapértelmezett értékeit a
     *   `validation_rules.options.default_values` konfiguráció határozza meg.
     *
     * Példa:
     *
     * 'initialized_options' => [
     *     'validation_rules.options.website_settings',
     * ]
     *
     * Az itt felsorolt konfigurációk alapján létrehozott opciók
     * a CMS működéséhez szükséges alapbeállításokat biztosítják.
     */
    'initialized_options' => [
        'validation_rules.options.website_settings',
    ]
];