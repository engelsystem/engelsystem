<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

/**
 * Helper class for BCP47 language tag validation and name lookup
 */
class Language
{
    /**
     * Common BCP47 regional variants used in translation contexts
     */
    public const COMMON_TAGS = [
        // Arabic
        'ar-AE' => 'Arabic (UAE)',
        'ar-EG' => 'Arabic (Egypt)',
        'ar-MA' => 'Arabic (Morocco)',
        'ar-SA' => 'Arabic (Saudi Arabia)',
        // Chinese
        'zh-CN' => 'Chinese (China, Simplified)',
        'zh-Hans' => 'Chinese (Simplified)',
        'zh-Hant' => 'Chinese (Traditional)',
        'zh-HK' => 'Chinese (Hong Kong)',
        'zh-TW' => 'Chinese (Taiwan)',
        // Dutch
        'nl-BE' => 'Dutch (Belgium)',
        'nl-NL' => 'Dutch (Netherlands)',
        // English
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'en-GB' => 'English (UK)',
        'en-IE' => 'English (Ireland)',
        'en-IN' => 'English (India)',
        'en-NZ' => 'English (New Zealand)',
        'en-US' => 'English (US)',
        'en-ZA' => 'English (South Africa)',
        // French
        'fr-BE' => 'French (Belgium)',
        'fr-CA' => 'French (Canada)',
        'fr-CH' => 'French (Switzerland)',
        'fr-FR' => 'French (France)',
        // German
        'de-AT' => 'German (Austria)',
        'de-CH' => 'German (Switzerland)',
        'de-DE' => 'German (Germany)',
        'de-LU' => 'German (Luxembourg)',
        // Portuguese
        'pt-BR' => 'Portuguese (Brazil)',
        'pt-PT' => 'Portuguese (Portugal)',
        // Russian
        'ru-BY' => 'Russian (Belarus)',
        'ru-RU' => 'Russian (Russia)',
        // Spanish
        'es-419' => 'Spanish (Latin America)',
        'es-AR' => 'Spanish (Argentina)',
        'es-ES' => 'Spanish (Spain)',
        'es-MX' => 'Spanish (Mexico)',
        'es-US' => 'Spanish (US)',
    ];

    /**
     * ISO 639-1 language codes with names
     */
    public const ISO_639_1 = [
        'aa' => 'Afar',
        'ab' => 'Abkhazian',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bh' => 'Bihari',
        'bi' => 'Bislama',
        'bn' => 'Bengali',
        'bo' => 'Tibetan',
        'br' => 'Breton',
        'bs' => 'Bosnian',
        'ca' => 'Catalan',
        'ce' => 'Chechen',
        'co' => 'Corsican',
        'cs' => 'Czech',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de' => 'German',
        'dz' => 'Dzongkha',
        'el' => 'Greek',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'eu' => 'Basque',
        'fa' => 'Persian',
        'fi' => 'Finnish',
        'fj' => 'Fijian',
        'fo' => 'Faroese',
        'fr' => 'French',
        'fy' => 'Western Frisian',
        'ga' => 'Irish',
        'gd' => 'Scottish Gaelic',
        'gl' => 'Galician',
        'gn' => 'Guarani',
        'gu' => 'Gujarati',
        'ha' => 'Hausa',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hr' => 'Croatian',
        'ht' => 'Haitian Creole',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'ia' => 'Interlingua',
        'id' => 'Indonesian',
        'ie' => 'Interlingue',
        'ig' => 'Igbo',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jv' => 'Javanese',
        'ka' => 'Georgian',
        'kk' => 'Kazakh',
        'km' => 'Khmer',
        'kn' => 'Kannada',
        'ko' => 'Korean',
        'ku' => 'Kurdish',
        'ky' => 'Kyrgyz',
        'la' => 'Latin',
        'lb' => 'Luxembourgish',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lv' => 'Latvian',
        'mg' => 'Malagasy',
        'mi' => 'Maori',
        'mk' => 'Macedonian',
        'ml' => 'Malayalam',
        'mn' => 'Mongolian',
        'mr' => 'Marathi',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'my' => 'Burmese',
        'ne' => 'Nepali',
        'nl' => 'Dutch',
        'no' => 'Norwegian',
        'ny' => 'Chichewa',
        'oc' => 'Occitan',
        'om' => 'Oromo',
        'or' => 'Odia',
        'pa' => 'Punjabi',
        'pl' => 'Polish',
        'ps' => 'Pashto',
        'pt' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'rw' => 'Kinyarwanda',
        'sa' => 'Sanskrit',
        'sd' => 'Sindhi',
        'si' => 'Sinhala',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'so' => 'Somali',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'st' => 'Southern Sotho',
        'su' => 'Sundanese',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'tg' => 'Tajik',
        'th' => 'Thai',
        'ti' => 'Tigrinya',
        'tk' => 'Turkmen',
        'tl' => 'Tagalog',
        'tr' => 'Turkish',
        'tt' => 'Tatar',
        'ug' => 'Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'zh' => 'Chinese',
        'zu' => 'Zulu',
    ];

    /**
     * Validates a BCP47 language tag
     *
     * Accepts:
     * - ISO 639-1 codes (e.g., 'en', 'de')
     * - Regional variants (e.g., 'pt-BR', 'zh-Hans')
     */
    public static function isValid(string $code): bool
    {
        $code = trim($code);

        if (empty($code)) {
            return false;
        }

        // Check common tags first (case-insensitive)
        foreach (array_keys(self::COMMON_TAGS) as $tag) {
            if (strcasecmp($tag, $code) === 0) {
                return true;
            }
        }

        // Check ISO 639-1 base codes
        $parts = explode('-', $code);
        $baseCode = strtolower($parts[0]);

        if (!isset(self::ISO_639_1[$baseCode])) {
            return false;
        }

        // If it's just a base code, it's valid
        if (count($parts) === 1) {
            return true;
        }

        // Validate region/script part (2-4 alphanumeric characters)
        $region = $parts[1];
        return (bool) preg_match('/^[A-Za-z0-9]{2,4}$/', $region);
    }

    /**
     * Get the human-readable name for a language code
     */
    public static function getName(string $code): string
    {
        $code = trim($code);

        if (empty($code)) {
            return $code;
        }

        // Check common tags first (case-insensitive match, return proper case)
        foreach (self::COMMON_TAGS as $tag => $name) {
            if (strcasecmp($tag, $code) === 0) {
                return $name;
            }
        }

        // Parse the code
        $parts = explode('-', $code);
        $baseCode = strtolower($parts[0]);

        if (!isset(self::ISO_639_1[$baseCode])) {
            return $code;
        }

        $baseName = self::ISO_639_1[$baseCode];

        // If there's a region, append it
        if (count($parts) > 1) {
            return $baseName . ' (' . strtoupper($parts[1]) . ')';
        }

        return $baseName;
    }

    /**
     * Normalize a language code to consistent casing
     *
     * @return string Normalized code (lowercase base, uppercase region)
     */
    public static function normalize(string $code): string
    {
        $code = trim($code);

        if (empty($code)) {
            return $code;
        }

        // Check common tags for exact match
        foreach (array_keys(self::COMMON_TAGS) as $tag) {
            if (strcasecmp($tag, $code) === 0) {
                return $tag;
            }
        }

        $parts = explode('-', $code);
        $normalized = strtolower($parts[0]);

        if (count($parts) > 1) {
            // Script tags are title case, region codes are uppercase
            $second = $parts[1];
            if (strlen($second) === 4) {
                // Script (e.g., Hans, Hant)
                $normalized .= '-' . ucfirst(strtolower($second));
            } else {
                // Region (e.g., BR, US)
                $normalized .= '-' . strtoupper($second);
            }
        }

        return $normalized;
    }

    /**
     * Get all available language options for autocomplete
     *
     * @return array<array{code: string, name: string}>
     */
    public static function getAllOptions(): array
    {
        $options = [];

        // Add common tags first (they're most likely to be used)
        foreach (self::COMMON_TAGS as $code => $name) {
            $options[] = ['code' => $code, 'name' => $name];
        }

        // Add ISO 639-1 languages
        foreach (self::ISO_639_1 as $code => $name) {
            $options[] = ['code' => $code, 'name' => $name];
        }

        return $options;
    }
}
