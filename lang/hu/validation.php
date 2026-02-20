<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validációs nyelvi sorok
    |--------------------------------------------------------------------------
    |
    | Az alábbi nyelvi sorok tartalmazzák az alapértelmezett hibaüzeneteket,
    | amelyeket a validátor osztály használ. Néhány szabálynak több verziója is
    | van, például a méret szabályok. Nyugodtan módosítsd ezeket az üzeneteket.
    |
    */

    'accepted' => 'A(z) :attribute mezőt el kell fogadni.',
    'accepted_if' => 'A(z) :attribute mezőt el kell fogadni, ha :other :value.',
    'active_url' => 'A(z) :attribute nem érvényes URL.',
    'after' => 'A(z) :attribute mezőnek egy :date utáni dátumnak kell lennie.',
    'after_or_equal' => 'A(z) :attribute mezőnek egy :date utáni vagy azzal egyenlő dátumnak kell lennie.',
    'alpha' => 'A(z) :attribute mező csak betűket tartalmazhat.',
    'alpha_dash' => 'A(z) :attribute mező csak betűket, számokat, kötőjeleket és aláhúzásokat tartalmazhat.',
    'alpha_num' => 'A(z) :attribute mező csak betűket és számokat tartalmazhat.',
    'array' => 'A(z) :attribute mezőnek tömbnek kell lennie.',
    'ascii' => 'A(z) :attribute mező csak egyszerű ASCII karaktereket tartalmazhat.',
    'before' => 'A(z) :attribute mezőnek egy :date előtti dátumnak kell lennie.',
    'before_or_equal' => 'A(z) :attribute mezőnek egy :date előtti vagy azzal egyenlő dátumnak kell lennie.',
    'between' => [
        'array' => 'A(z) :attribute mező :min és :max elem között kell legyen.',
        'file' => 'A(z) :attribute mező :min és :max kilobájt között kell legyen.',
        'numeric' => 'A(z) :attribute mező :min és :max között kell legyen.',
        'string' => 'A(z) :attribute mező :min és :max karakter között kell legyen.',
    ],
    'boolean' => 'A(z) :attribute mező csak igaz vagy hamis lehet.',
    'can' => 'A(z) :attribute mező nem engedélyezett értéket tartalmaz.',
    'confirmed' => 'A(z) :attribute megerősítése nem egyezik.',
    'contains' => 'A(z) :attribute mezőből hiányzik egy szükséges érték.',
    'current_password' => 'A jelszó helytelen.',
    'date' => 'A(z) :attribute mező nem érvényes dátum.',
    'date_equals' => 'A(z) :attribute mezőnek egy :date dátummal egyenlőnek kell lennie.',
    'date_format' => 'A(z) :attribute mező nem egyezik a(z) :format formátummal.',
    'decimal' => 'A(z) :attribute mezőnek :decimal tizedesjegyűnek kell lennie.',
    'declined' => 'A(z) :attribute mezőt el kell utasítani.',
    'declined_if' => 'A(z) :attribute mezőt el kell utasítani, ha :other :value.',
    'different' => 'A(z) :attribute és :other mezőknek különbözőnek kell lenniük.',
    'digits' => 'A(z) :attribute mezőnek :digits számjegyűnek kell lennie.',
    'digits_between' => 'A(z) :attribute mezőnek :min és :max számjegy között kell lennie.',
    'dimensions' => 'A(z) :attribute mező érvénytelen képméreteket tartalmaz.',
    'distinct' => 'A(z) :attribute mezőben ismétlődő érték van.',
    'doesnt_end_with' => 'A(z) :attribute mező nem végződhet a következőkkel: :values.',
    'doesnt_start_with' => 'A(z) :attribute mező nem kezdődhet a következőkkel: :values.',
    'email' => 'A(z) :attribute mezőnek érvényes email-címnek kell lennie.',
    'ends_with' => 'A(z) :attribute mezőnek a következőkkel kell végződnie: :values.',
    'enum' => 'A kiválasztott :attribute érvénytelen.',
    'exists' => 'A kiválasztott :attribute érvénytelen.',
    'extensions' => 'A(z) :attribute mezőnek az alábbi kiterjesztések egyikének kell lennie: :values.',
    'file' => 'A(z) :attribute mezőnek fájlnak kell lennie.',
    'filled' => 'A(z) :attribute mező nem lehet üres.',
    'gt' => [
        'array' => 'A(z) :attribute mező több mint :value elemet kell tartalmazzon.',
        'file' => 'A(z) :attribute mezőnek nagyobbnak kell lennie, mint :value kilobájt.',
        'numeric' => 'A(z) :attribute mezőnek nagyobbnak kell lennie, mint :value.',
        'string' => 'A(z) :attribute mezőnek hosszabbnak kell lennie, mint :value karakter.',
    ],
    'gte' => [
        'array' => 'A(z) :attribute mező legalább :value elemet kell tartalmazzon.',
        'file' => 'A(z) :attribute mezőnek legalább :value kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek legalább :value-nek kell lennie.',
        'string' => 'A(z) :attribute mezőnek legalább :value karakter hosszúnak kell lennie.',
    ],
    'hex_color' => 'A(z) :attribute mezőnek érvényes hexadecimális színnek kell lennie.',
    'image' => 'A(z) :attribute mezőnek képfájlnak kell lennie.',
    'in' => 'A kiválasztott :attribute érvénytelen.',
    'in_array' => 'A(z) :attribute mező nem található meg a :other mezőben.',
    'integer' => 'A(z) :attribute mezőnek egész számnak kell lennie.',
    'ip' => 'A(z) :attribute mezőnek érvényes IP-címnek kell lennie.',
    'ipv4' => 'A(z) :attribute mezőnek érvényes IPv4-címnek kell lennie.',
    'ipv6' => 'A(z) :attribute mezőnek érvényes IPv6-címnek kell lennie.',
    'json' => 'A(z) :attribute mezőnek érvényes JSON szövegnek kell lennie.',
    'list' => 'A(z) :attribute mezőnek listának kell lennie.',
    'lowercase' => 'A(z) :attribute mezőnek kisbetűsnek kell lennie.',
    'lt' => [
        'array' => 'A(z) :attribute mező kevesebb, mint :value elemet tartalmazhat.',
        'file' => 'A(z) :attribute mezőnek kisebbnek kell lennie, mint :value kilobájt.',
        'numeric' => 'A(z) :attribute mezőnek kisebbnek kell lennie, mint :value.',
        'string' => 'A(z) :attribute mezőnek rövidebbnek kell lennie, mint :value karakter.',
    ],
    'lte' => [
        'array' => 'A(z) :attribute mező nem tartalmazhat több mint :value elemet.',
        'file' => 'A(z) :attribute mezőnek legfeljebb :value kilobájt lehet.',
        'numeric' => 'A(z) :attribute mezőnek legfeljebb :value lehet.',
        'string' => 'A(z) :attribute mezőnek legfeljebb :value karakter hosszú lehet.',
    ],
    'mac_address' => 'A(z) :attribute mezőnek érvényes MAC-címnek kell lennie.',
    'max' => [
        'array' => 'A(z) :attribute mező nem tartalmazhat több mint :max elemet.',
        'file' => 'A(z) :attribute mező nem lehet nagyobb, mint :max kilobájt.',
        'numeric' => 'A(z) :attribute mező nem lehet nagyobb, mint :max.',
        'string' => 'A(z) :attribute mező nem lehet hosszabb, mint :max karakter.',
    ],
    'max_digits' => 'A(z) :attribute mező nem tartalmazhat több mint :max számjegyet.',
    'mimes' => 'A(z) :attribute mezőnek az alábbi fájltípusok egyikének kell lennie: :values.',
    'mimetypes' => 'A(z) :attribute mezőnek az alábbi fájltípusok egyikének kell lennie: :values.',
    'min' => [
        'array' => 'A(z) :attribute mezőnek legalább :min elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek legalább :min kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek legalább :min-nek kell lennie.',
        'string' => 'A(z) :attribute mezőnek legalább :min karakter hosszúnak kell lennie.',
    ],
    'min_digits' => 'A(z) :attribute mezőnek legalább :min számjegyet kell tartalmaznia.',
    'missing' => 'A(z) :attribute mezőnek hiányoznia kell.',
    'missing_if' => 'A(z) :attribute mezőnek hiányoznia kell, ha :other :value.',
    'missing_unless' => 'A(z) :attribute mezőnek hiányoznia kell, kivéve, ha :other :value.',
    'missing_with' => 'A(z) :attribute mezőnek hiányoznia kell, ha :values jelen van.',
    'missing_with_all' => 'A(z) :attribute mezőnek hiányoznia kell, ha :values jelen vannak.',
    'multiple_of' => 'A(z) :attribute mezőnek :value többszörösének kell lennie.',
    'not_in' => 'A kiválasztott :attribute érvénytelen.',
    'not_regex' => 'A(z) :attribute mező formátuma érvénytelen.',
    'numeric' => 'A(z) :attribute mezőnek számnak kell lennie.',
    'password' => [
        'letters' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy betűt.',
        'mixed' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy nagybetűt és egy kisbetűt.',
        'numbers' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy számot.',
        'symbols' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy szimbólumot.',
        'uncompromised' => 'A megadott :attribute egy adatszivárgás során érintett. Kérjük, válasszon egy másik :attribute-t.',
    ],
    'present' => 'A(z) :attribute mezőnek jelen kell lennie.',
    'present_if' => 'A(z) :attribute mezőnek jelen kell lennie, ha :other :value.',
    'present_unless' => 'A(z) :attribute mezőnek jelen kell lennie, kivéve, ha :other :value.',
    'present_with' => 'A(z) :attribute mezőnek jelen kell lennie, ha :values jelen van.',
    'present_with_all' => 'A(z) :attribute mezőnek jelen kell lennie, ha :values jelen vannak.',
    'prohibited' => 'A(z) :attribute mező tiltott.',
    'prohibited_if' => 'A(z) :attribute mező tiltott, ha :other :value.',
    'prohibited_unless' => 'A(z) :attribute mező tiltott, kivéve, ha :other benne van a(z) :values értékekben.',
    'prohibits' => 'A(z) :attribute mező kizárja :other jelenlétét.',
    'regex' => 'A(z) :attribute mező formátuma érvénytelen.',
    'required' => 'A(z) :attribute mező kitöltése kötelező.',
    'required_array_keys' => 'A(z) :attribute mezőnek tartalmaznia kell a következő kulcsokat: :values.',
    'required_if' => 'A(z) :attribute mező kitöltése kötelező, ha :other :value.',
    'required_if_accepted' => 'A(z) :attribute mező kitöltése kötelező, ha :other el van fogadva.',
    'required_if_declined' => 'A(z) :attribute mező kitöltése kötelező, ha :other el van utasítva.',
    'required_unless' => 'A(z) :attribute mező kitöltése kötelező, kivéve, ha :other szerepel a(z) :values között.',
    'required_with' => 'A(z) :attribute mező kitöltése kötelező, ha :values jelen van.',
    'required_with_all' => 'A(z) :attribute mező kitöltése kötelező, ha :values jelen van.',
    'required_without' => 'A(z) :attribute mező kitöltése kötelező, ha :values nincs jelen.',
    'required_without_all' => 'A(z) :attribute mező kitöltése kötelező, ha egyik :values sincs jelen.',
    'same' => 'A(z) :attribute mezőnek egyeznie kell :other mezővel.',
    'size' => [
        'array' => 'A(z) :attribute mezőnek :size elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek :size kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek :size-nek kell lennie.',
        'string' => 'A(z) :attribute mezőnek :size karakter hosszúnak kell lennie.',
    ],
    'starts_with' => 'A(z) :attribute mezőnek a következőkkel kell kezdődnie: :values.',
    'string' => 'A(z) :attribute mezőnek szövegnek kell lennie.',
    'timezone' => 'A(z) :attribute mezőnek érvényes időzónának kell lennie.',
    'unique' => 'A(z) :attribute mezőt már használják.',
    'uploaded' => 'A(z) :attribute feltöltése sikertelen volt.',
    'uppercase' => 'A(z) :attribute mezőnek nagybetűsnek kell lennie.',
    'url' => 'A(z) :attribute mezőnek érvényes URL-nek kell lennie.',
    'ulid' => 'A(z) :attribute mezőnek érvényes ULID-nek kell lennie.',
    'uuid' => 'A(z) :attribute mezőnek érvényes UUID-nek kell lennie.',

    /*
    |--------------------------------------------------------------------------
    | Egyedi validációs nyelvi sorok
    |--------------------------------------------------------------------------
    |
    | Itt adhatsz meg egyedi validációs üzeneteket attribútumokhoz azzal a
    | konvencióval, hogy "attribute.rule" legyen a sor neve. Ez lehetővé
    | teszi, hogy gyorsan megadd az egyedi üzenetet egy adott szabályhoz.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'egyedi üzenet',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Egyedi validációs attribútumok
    |--------------------------------------------------------------------------
    |
    | Az alábbi nyelvi sorokat arra használjuk, hogy az attribútum helyőrzőit
    | barátságosabbá tegyük, például "E-mail cím" helyett "email".
    | Ez segít kifejezőbbé tenni az üzeneteinket.
    |
    */

    'attributes' => [],

];

