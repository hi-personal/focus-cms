<?php

if (! function_exists('cms_locale')) {

    function cms_locale()
    {
        return app()->getLocale();
    }
}

if (! function_exists('cms_locale_full')) {

    function cms_locale_full(?string $locale = null): string
    {
        $locale = $locale ?? cms_locale();

        $locales = [
            /*
             * Európa
             */
            'hu' => 'hu_HU',
            'en' => 'en_US',
            'en-gb' => 'en_GB',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
            'es' => 'es_ES',
            'it' => 'it_IT',
            'pt' => 'pt_PT',
            'pt-br' => 'pt_BR',
            'nl' => 'nl_NL',
            'pl' => 'pl_PL',
            'cs' => 'cs_CZ',
            'sk' => 'sk_SK',
            'sl' => 'sl_SI',
            'ro' => 'ro_RO',
            'sr' => 'sr_RS',
            'hr' => 'hr_HR',
            'bs' => 'bs_BA',
            'uk' => 'uk_UA',
            'ru' => 'ru_RU',
            'bg' => 'bg_BG',
            'el' => 'el_GR',
            'da' => 'da_DK',
            'sv' => 'sv_SE',
            'no' => 'no_NO',
            'fi' => 'fi_FI',
            'et' => 'et_EE',
            'lv' => 'lv_LV',
            'lt' => 'lt_LT',

            /*
             * Ázsia
             */
            'tr' => 'tr_TR',
            'ar' => 'ar_SA',
            'he' => 'he_IL',
            'fa' => 'fa_IR',
            'hi' => 'hi_IN',
            'bn' => 'bn_BD',
            'ur' => 'ur_PK',
            'zh' => 'zh_CN',
            'zh-tw' => 'zh_TW',
            'ja' => 'ja_JP',
            'ko' => 'ko_KR',
            'th' => 'th_TH',
            'vi' => 'vi_VN',
            'id' => 'id_ID',
            'ms' => 'ms_MY',

            /*
             * fallback
             */
        ];

        return $locales[$locale]
            ?? strtolower($locale)
                .'_'
                .strtoupper($locale);
    }
}