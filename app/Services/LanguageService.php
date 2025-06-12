<?php

declare(strict_types=1);

namespace App\Services;

class LanguageService
{
    /**
     * Map of language locales to their full names with native names
     * A comprehensive list of languages with their native names
     */
    protected array $languageNames = [
        'aa' => 'Afar (Afaraf)',
        'ab' => 'Abkhazian (Аҧсуа)',
        'ae' => 'Avestan (Avesta)',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic (አማርኛ)',
        'an' => 'Aragonese (Aragonés)',
        'ar' => 'Arabic (العربية)',
        'as' => 'Assamese (অসমীয়া)',
        'av' => 'Avaric (Авар мацӀ)',
        'ay' => 'Aymara (Aymar aru)',
        'az' => 'Azerbaijani (Azərbaycan dili)',
        'ba' => 'Bashkir (Башҡорт теле)',
        'be' => 'Belarusian (Беларуская)',
        'bg' => 'Bulgarian (Български)',
        'bh' => 'Bihari languages (भोजपुरी)',
        'bi' => 'Bislama',
        'bm' => 'Bambara (Bamanankan)',
        'bn' => 'Bengali (বাংলা)',
        'bo' => 'Tibetan (བོད་ཡིག)',
        'br' => 'Breton (Brezhoneg)',
        'bs' => 'Bosnian (Bosanski)',
        'ca' => 'Catalan (Català)',
        'ce' => 'Chechen (Нохчийн мотт)',
        'ch' => 'Chamorro (Chamoru)',
        'co' => 'Corsican (Corsu)',
        'cr' => 'Cree (ᓀᐦᐃᔭᐍᐏᐣ)',
        'cs' => 'Czech (Čeština)',
        'cu' => 'Old Church Slavonic (Ѩзыкъ словѣньскъ)',
        'cv' => 'Chuvash (Чӑваш чӗлхи)',
        'cy' => 'Welsh (Cymraeg)',
        'da' => 'Danish (Dansk)',
        'de' => 'German (Deutsch)',
        'dv' => 'Divehi (ދިވެހި)',
        'dz' => 'Dzongkha (རྫོང་ཁ)',
        'ee' => 'Ewe (Eʋegbe)',
        'el' => 'Greek (Ελληνικά)',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Spanish (Español)',
        'et' => 'Estonian (Eesti)',
        'eu' => 'Basque (Euskara)',
        'fa' => 'Persian (فارسی)',
        'ff' => 'Fulah (Fulfulde)',
        'fi' => 'Finnish (Suomi)',
        'fj' => 'Fijian (Vosa Vakaviti)',
        'fo' => 'Faroese (Føroyskt)',
        'fr' => 'French (Français)',
        'fy' => 'Western Frisian (Frysk)',
        'ga' => 'Irish (Gaeilge)',
        'gd' => 'Gaelic (Gàidhlig)',
        'gl' => 'Galician (Galego)',
        'gn' => 'Guarani (Avañe\'ẽ)',
        'gu' => 'Gujarati (ગુજરાતી)',
        'gv' => 'Manx (Gaelg)',
        'ha' => 'Hausa (هَوُسَ)',
        'he' => 'Hebrew (עברית)',
        'hi' => 'Hindi (हिन्दी)',
        'ho' => 'Hiri Motu',
        'hr' => 'Croatian (Hrvatski)',
        'ht' => 'Haitian (Kreyòl ayisyen)',
        'hu' => 'Hungarian (Magyar)',
        'hy' => 'Armenian (Հայերեն)',
        'hz' => 'Herero (Otjiherero)',
        'ia' => 'Interlingua',
        'id' => 'Indonesian (Bahasa Indonesia)',
        'ie' => 'Interlingue (Interlingue)',
        'ig' => 'Igbo (Asụsụ Igbo)',
        'ii' => 'Sichuan Yi (ꆈꌠ꒿)',
        'ik' => 'Inupiaq (Iñupiaq)',
        'io' => 'Ido',
        'is' => 'Icelandic (Íslenska)',
        'it' => 'Italian (Italiano)',
        'iu' => 'Inuktitut (ᐃᓄᒃᑎᑐᑦ)',
        'ja' => 'Japanese (日本語)',
        'jv' => 'Javanese (Basa Jawa)',
        'ka' => 'Georgian (ქართული)',
        'kg' => 'Kongo (KiKongo)',
        'ki' => 'Kikuyu (Gĩkũyũ)',
        'kj' => 'Kuanyama (Kuanyama)',
        'kk' => 'Kazakh (Қазақ тілі)',
        'kl' => 'Kalaallisut (Kalaallisut)',
        'km' => 'Central Khmer (ភាសាខ្មែរ)',
        'kn' => 'Kannada (ಕನ್ನಡ)',
        'ko' => 'Korean (한국어)',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri (कश्मीरी)',
        'ku' => 'Kurdish (Kurdî)',
        'kv' => 'Komi (Коми)',
        'kw' => 'Cornish (Kernewek)',
        'ky' => 'Kirghiz (Кыргызча)',
        'la' => 'Latin (Latina)',
        'lb' => 'Luxembourgish (Lëtzebuergesch)',
        'lg' => 'Ganda (Luganda)',
        'li' => 'Limburgan (Limburgs)',
        'ln' => 'Lingala (Lingála)',
        'lo' => 'Lao (ພາສາລາວ)',
        'lt' => 'Lithuanian (Lietuvių)',
        'lu' => 'Luba-Katanga',
        'lv' => 'Latvian (Latviešu)',
        'mg' => 'Malagasy',
        'mh' => 'Marshallese (Kajin M̧ajeļ)',
        'mi' => 'Maori (Te Reo Māori)',
        'mk' => 'Macedonian (Македонски)',
        'ml' => 'Malayalam (മലയാളം)',
        'mn' => 'Mongolian (Монгол)',
        'mr' => 'Marathi (मराठी)',
        'ms' => 'Malay (Bahasa Melayu)',
        'mt' => 'Maltese (Malti)',
        'my' => 'Burmese (မြန်မာစာ)',
        'na' => 'Nauru (Dorerin Naoero)',
        'nb' => 'Norwegian Bokmål (Norsk bokmål)',
        'nd' => 'North Ndebele (isiNdebele)',
        'ne' => 'Nepali (नेपाली)',
        'ng' => 'Ndonga (Owambo)',
        'nl' => 'Dutch (Nederlands)',
        'nn' => 'Norwegian Nynorsk (Norsk nynorsk)',
        'no' => 'Norwegian (Norsk)',
        'nr' => 'South Ndebele (isiNdebele)',
        'nv' => 'Navajo (Diné bizaad)',
        'ny' => 'Chichewa (ChiCheŵa)',
        'oc' => 'Occitan (Occitan)',
        'oj' => 'Ojibwa (ᐊᓂᔑᓈᐯᒧᐎᓐ)',
        'om' => 'Oromo (Afaan Oromoo)',
        'or' => 'Oriya (ଓଡ଼ିଆ)',
        'os' => 'Ossetian (Ирон)',
        'pa' => 'Punjabi (ਪੰਜਾਬੀ)',
        'pi' => 'Pali (पाऴि)',
        'pl' => 'Polish (Polski)',
        'ps' => 'Pashto (پښتو)',
        'pt' => 'Portuguese (Português)',
        'qu' => 'Quechua (Runa Simi)',
        'rm' => 'Romansh (Rumantsch)',
        'rn' => 'Rundi (Kirundi)',
        'ro' => 'Romanian (Română)',
        'ru' => 'Russian (Русский)',
        'rw' => 'Kinyarwanda (Kinyarwanda)',
        'sa' => 'Sanskrit (संस्कृतम्)',
        'sc' => 'Sardinian (Sardu)',
        'sd' => 'Sindhi (سنڌي)',
        'se' => 'Northern Sami (Davvisámegiella)',
        'sg' => 'Sango (Yângâ tî sängö)',
        'si' => 'Sinhala (සිංහල)',
        'sk' => 'Slovak (Slovenčina)',
        'sl' => 'Slovenian (Slovenščina)',
        'sm' => 'Samoan (Gagana fa\'a Samoa)',
        'sn' => 'Shona (chiShona)',
        'so' => 'Somali (Soomaaliga)',
        'sq' => 'Albanian (Shqip)',
        'sr' => 'Serbian (Српски)',
        'ss' => 'Swati (SiSwati)',
        'st' => 'Southern Sotho (Sesotho)',
        'su' => 'Sundanese (Basa Sunda)',
        'sv' => 'Swedish (Svenska)',
        'sw' => 'Swahili (Kiswahili)',
        'ta' => 'Tamil (தமிழ்)',
        'te' => 'Telugu (తెలుగు)',
        'tg' => 'Tajik (Тоҷикӣ)',
        'th' => 'Thai (ไทย)',
        'ti' => 'Tigrinya (ትግርኛ)',
        'tk' => 'Turkmen (Türkmençe)',
        'tl' => 'Tagalog (Wikang Tagalog)',
        'tn' => 'Tswana (Setswana)',
        'to' => 'Tonga (Lea Faka-Tonga)',
        'tr' => 'Turkish (Türkçe)',
        'ts' => 'Tsonga (Xitsonga)',
        'tt' => 'Tatar (Татарча)',
        'tw' => 'Twi (Twi)',
        'ty' => 'Tahitian (Reo Tahiti)',
        'ug' => 'Uighur (ئۇيغۇرچە)',
        'uk' => 'Ukrainian (Українська)',
        'ur' => 'Urdu (اردو)',
        'uz' => 'Uzbek (Oʻzbek)',
        've' => 'Venda (Tshivenḓa)',
        'vi' => 'Vietnamese (Tiếng Việt)',
        'vo' => 'Volapük',
        'wa' => 'Walloon (Walon)',
        'wo' => 'Wolof (Wollof)',
        'xh' => 'Xhosa (isiXhosa)',
        'yi' => 'Yiddish (ייִדיש)',
        'yo' => 'Yoruba (Yorùbá)',
        'za' => 'Zhuang (Saɯ cueŋƅ)',
        'zh' => 'Chinese (中文)',
        'zu' => 'Zulu (isiZulu)',
    ];

    /**
     * Get all active languages
     */
    public function getLanguages(): array
    {
        return $this->languageNames;
    }

    /**
     * Get all active languages with detailed information
     */
    public function getActiveLanguages(): array
    {
        // Get all the languages inside /resources/lang folder as key.
        // The key is the language code (e.g., 'en', 'bn').
        $languages = array_diff(scandir(resource_path('lang')), ['..', '.', '.DS_Store']);

        // Process languages to get unique keys
        $uniqueLanguages = [];
        foreach ($languages as $language) {
            // Remove .json extension if present
            $langKey = preg_replace('/\.json$/', '', $language);
            // Add to unique languages if not already added
            $uniqueLanguages[$langKey] = [
                'name' => $this->getLanguageNameByLocale($langKey),
            ];
        }

        $languages = ld_apply_filters('languages', $uniqueLanguages);

        foreach ($languages as $code => &$language) {
            $language['code'] = strtoupper($code);
            $language['icon'] = "/images/flags/language-{$code}.svg";
        }

        return $languages;
    }

    /**
     * Get all available language names
     */
    public function getLanguageNames(): array
    {
        return $this->languageNames;
    }

    /**
     * Get language name by locale code
     */
    public function getLanguageNameByLocale(string $locale): string
    {
        // Normalize the locale (remove any region part for matching)
        $normalizedLocale = strtolower(explode('-', $locale)[0]);

        // Return the full name if available, otherwise return capitalized locale
        return $this->languageNames[$normalizedLocale] ?? ucfirst($locale);
    }
}
