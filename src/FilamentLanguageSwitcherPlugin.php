<?php

namespace CraftForge\FilamentLanguageSwitcher;

use Closure;
use CraftForge\FilamentLanguageSwitcher\Http\Middleware\SetLocale;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class FilamentLanguageSwitcherPlugin implements Plugin
{
    protected static ?int $rememberLocaleDays = null;

    protected array|Closure $locales = [];
    protected bool $showFlags = true;
    protected bool $showOnAuthPages = false;
    protected string $renderHook = PanelsRenderHook::USER_MENU_BEFORE;

    public function getId(): string
    {
        return 'filament-language-switcher';
    }

    public static function make(): static
    {
        return new static();
    }

    public function locales(array|Closure $locales): static
    {
        $this->locales = $locales;
        return $this;
    }

    public function showFlags(bool $show = true): static
    {
        $this->showFlags = $show;
        return $this;
    }

    public function showOnAuthPages(bool $show = true): static
    {
        $this->showOnAuthPages = $show;
        return $this;
    }

    /**
     * Remember the selected locale in a cookie.
     * Call without arguments to store forever, or specify the number of days.
     */
    public function rememberLocale(?int $days = null): static
    {
        static::$rememberLocaleDays = $days ?? 0;
        return $this;
    }

    public function renderHook(string $hook): static
    {
        $this->renderHook = $hook;
        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            name: $this->renderHook,
            hook: fn (): View => $this->renderLanguageSwitcher(),
        );

        if ($this->showOnAuthPages) {
            $authHooks = [
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER,
            ];

            foreach ($authHooks as $authHook) {
                $panel->renderHook(
                    name: $authHook,
                    hook: fn (): View => $this->renderLanguageSwitcher(floating: true),
                );
            }
        }

        $panel->middleware([SetLocale::class], isPersistent: true);
    }

    public static function getRememberLocaleDays(): ?int
    {
        return static::$rememberLocaleDays;
    }

    protected function renderLanguageSwitcher(bool $floating = false): View
    {
        $locales = $this->getLocales();
        $currentLocale = app()->getLocale();
        $currentLanguage = collect($locales)->firstWhere('code', $currentLocale);
        $otherLanguages = $locales;
        $showFlags = $this->showFlags;

        return view('filament-language-switcher::language-switcher', compact(
            'otherLanguages',
            'currentLanguage',
            'showFlags',
            'floating',
        ));
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function getLocales(): array
    {
        $locales = $this->locales instanceof Closure
            ? call_user_func($this->locales)
            : $this->locales;

        if (!empty($locales)) {
            return array_map(function ($locale) {
                if (is_string($locale)) {
                    $locale = ['code' => $locale];
                }

                if (!isset($locale['name'])) {
                    $locale['name'] = $this->getLanguageName($locale['code']);
                }

                if (!isset($locale['flag'])) {
                    $locale['flag'] = $this->getCountryCode($locale['code']);
                }

                return $locale;
            }, $locales);
        }

        return $this->getFilamentLocales();
    }

    protected function getFilamentLocales(): array
    {
        $filamentLangPath = base_path('vendor/filament/filament/resources/lang');
        $locales = [];

        if (!File::isDirectory($filamentLangPath)) {
            return $locales;
        }

        $directories = File::directories($filamentLangPath);

        foreach ($directories as $directory) {
            $localeCode = basename($directory);

            // Skip vendor directory if exists
            if ($localeCode === 'vendor') {
                continue;
            }

            $locales[] = [
                'code' => $localeCode,
                'name' => $this->getLanguageName($localeCode),
                'flag' => $this->getCountryCode($localeCode),
            ];
        }

        return $locales;
    }

    protected function getLanguageName(string $localeCode): string
    {
        $languageNames = [
            'af' => 'Afrikaans',
            'am' => 'አማርኛ',
            'ar' => 'العربية',
            'as' => 'অসমীয়া',
            'az' => 'Azərbaycan',
            'be' => 'Беларуская',
            'bg' => 'Български',
            'bn' => 'বাংলা',
            'bo' => 'བོད་ཡིག',
            'bs' => 'Bosanski',
            'ca' => 'Català',
            'ckb' => 'کوردی',
            'cs' => 'Čeština',
            'cy' => 'Cymraeg',
            'da' => 'Dansk',
            'de' => 'Deutsch',
            'dv' => 'ދިވެހި',
            'el' => 'Ελληνικά',
            'en' => 'English',
            'eo' => 'Esperanto',
            'es' => 'Español',
            'et' => 'Eesti',
            'eu' => 'Euskera',
            'fa' => 'فارسی',
            'fi' => 'Suomi',
            'fo' => 'Føroyskt',
            'fr' => 'Français',
            'fy' => 'Frysk',
            'ga' => 'Gaeilge',
            'gd' => 'Gàidhlig',
            'gl' => 'Galego',
            'gu' => 'ગુજરાતી',
            'ha' => 'Hausa',
            'he' => 'עברית',
            'hi' => 'हिन्दी',
            'hr' => 'Hrvatski',
            'hu' => 'Magyar',
            'hy' => 'Հայերեն',
            'id' => 'Indonesia',
            'ig' => 'Igbo',
            'is' => 'Íslenska',
            'it' => 'Italiano',
            'ja' => '日本語',
            'jv' => 'Basa Jawa',
            'ka' => 'ქართული',
            'kk' => 'Қазақ',
            'km' => 'ខ្មែរ',
            'kn' => 'ಕನ್ನಡ',
            'ko' => '한국어',
            'ku' => 'کوردی',
            'ky' => 'Кыргызча',
            'la' => 'Latina',
            'lb' => 'Lëtzebuergesch',
            'lo' => 'ລາວ',
            'lt' => 'Lietuvių',
            'lv' => 'Latviešu',
            'mg' => 'Malagasy',
            'mk' => 'Македонски',
            'ml' => 'മലയാളം',
            'mn' => 'Монгол',
            'mr' => 'मराठी',
            'ms' => 'Bahasa Malaysia',
            'mt' => 'Malti',
            'my' => 'မြန်မာ',
            'nb' => 'Norsk (Bokmål)',
            'nd' => 'isiNdebele',
            'ne' => 'नेपाली',
            'nl' => 'Nederlands',
            'nn' => 'Norsk (Nynorsk)',
            'no' => 'Norsk',
            'ny' => 'Chichewa',
            'or' => 'ଓଡ଼ିଆ',
            'pa' => 'ਪੰਜਾਬੀ',
            'pl' => 'Polski',
            'ps' => 'پښتو',
            'pt' => 'Português',
            'qu' => 'Runa Simi',
            'ro' => 'Română',
            'ru' => 'Русский',
            'rw' => 'Kinyarwanda',
            'sa' => 'संस्कृतम्',
            'sd' => 'سنڌي',
            'se' => 'Davvisámegiella',
            'si' => 'සිංහල',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'sm' => 'Gagana Samoa',
            'sn' => 'ChiShona',
            'so' => 'Soomaali',
            'sq' => 'Shqip',
            'sr' => 'Српски',
            'st' => 'Sesotho',
            'su' => 'Basa Sunda',
            'sv' => 'Svenska',
            'sw' => 'Kiswahili',
            'ta' => 'தமிழ்',
            'te' => 'తెలుగు',
            'tg' => 'Тоҷикӣ',
            'th' => 'ไทย',
            'tk' => 'Türkmen',
            'tl' => 'Filipino',
            'tn' => 'Setswana',
            'to' => 'Lea Faka-Tonga',
            'tr' => 'Türkçe',
            'ts' => 'Xitsonga',
            'tt' => 'Татар',
            'tw' => 'Twi',
            'ty' => 'Reo Tahiti',
            'ug' => 'ئۇيغۇر',
            'uk' => 'Українська',
            'ur' => 'اردو',
            'uz' => 'O\'zbek',
            've' => 'Tshivenḓa',
            'vi' => 'Tiếng Việt',
            'wo' => 'Wolof',
            'xh' => 'isiXhosa',
            'yi' => 'ייִדיש',
            'yo' => 'Yorùbá',
            'zh' => '中文',
            'zu' => 'isiZulu',

            // Regional variants
            'zh_CN' => '简体中文',
            'zh_TW' => '繁體中文',
            'zh_HK' => '繁體中文 (香港)',
            'zh_SG' => '简体中文 (新加坡)',
            'zh_MO' => '繁體中文 (澳門)',
            'en_US' => 'English (United States)',
            'en_GB' => 'English (United Kingdom)',
            'en_AU' => 'English (Australia)',
            'en_CA' => 'English (Canada)',
            'en_IE' => 'English (Ireland)',
            'en_NZ' => 'English (New Zealand)',
            'en_ZA' => 'English (South Africa)',
            'en_IN' => 'English (India)',
            'fr_CA' => 'Français (Canada)',
            'fr_CH' => 'Français (Suisse)',
            'fr_BE' => 'Français (Belgique)',
            'es_MX' => 'Español (México)',
            'es_AR' => 'Español (Argentina)',
            'es_CO' => 'Español (Colombia)',
            'es_CL' => 'Español (Chile)',
            'es_PE' => 'Español (Perú)',
            'es_VE' => 'Español (Venezuela)',
            'es_UY' => 'Español (Uruguay)',
            'es_PY' => 'Español (Paraguay)',
            'es_BO' => 'Español (Bolivia)',
            'es_EC' => 'Español (Ecuador)',
            'es_GT' => 'Español (Guatemala)',
            'es_HN' => 'Español (Honduras)',
            'es_SV' => 'Español (El Salvador)',
            'es_NI' => 'Español (Nicaragua)',
            'es_CR' => 'Español (Costa Rica)',
            'es_PA' => 'Español (Panamá)',
            'es_DO' => 'Español (República Dominicana)',
            'es_PR' => 'Español (Puerto Rico)',
            'es_CU' => 'Español (Cuba)',
            'pt_BR' => 'Português (Brasil)',
            'pt_PT' => 'Português (Portugal)',
            'pt_AO' => 'Português (Angola)',
            'pt_MZ' => 'Português (Moçambique)',
            'de_AT' => 'Deutsch (Österreich)',
            'de_CH' => 'Deutsch (Schweiz)',
            'de_LU' => 'Deutsch (Luxemburg)',
            'de_LI' => 'Deutsch (Liechtenstein)',
            'it_CH' => 'Italiano (Svizzera)',
            'it_SM' => 'Italiano (San Marino)',
            'it_VA' => 'Italiano (Vaticano)',
            'nl_BE' => 'Nederlands (België)',
            'nl_SR' => 'Nederlands (Suriname)',
            'ar_EG' => 'العربية (مصر)',
            'ar_SA' => 'العربية (السعودية)',
            'ar_AE' => 'العربية (الإمارات)',
            'ar_JO' => 'العربية (الأردن)',
            'ar_LB' => 'العربية (لبنان)',
            'ar_SY' => 'العربية (سوريا)',
            'ar_IQ' => 'العربية (العراق)',
            'ar_KW' => 'العربية (الكويت)',
            'ar_QA' => 'العربية (قطر)',
            'ar_BH' => 'العربية (البحرين)',
            'ar_OM' => 'العربية (عمان)',
            'ar_YE' => 'العربية (اليمن)',
            'ar_MA' => 'العربية (المغرب)',
            'ar_TN' => 'العربية (تونس)',
            'ar_DZ' => 'العربية (الجزائر)',
            'ar_LY' => 'العربية (ليبيا)',
            'ar_SD' => 'العربية (السودان)',
            'ru_RU' => 'Русский (Россия)',
            'ru_BY' => 'Русский (Беларусь)',
            'ru_KZ' => 'Русский (Казахстан)',
            'ru_KG' => 'Русский (Кыргызстан)',
            'ru_UA' => 'Русский (Украина)',
            'hi_IN' => 'हिन्दी (भारत)',
            'bn_BD' => 'বাংলা (বাংলাদেশ)',
            'bn_IN' => 'বাংলা (ভারত)',
            'ta_IN' => 'தமிழ் (இந்தியா)',
            'ta_LK' => 'தமிழ் (இலங்கை)',
            'te_IN' => 'తెలుగు (భారతదేశం)',
            'ml_IN' => 'മലയാളം (ഇന്ത്യ)',
            'kn_IN' => 'ಕನ್ನಡ (ಭಾರತ)',
            'gu_IN' => 'ગુજરાતી (ભારત)',
            'pa_IN' => 'ਪੰਜਾਬੀ (ਭਾਰਤ)',
            'or_IN' => 'ଓଡ଼ିଆ (ଭାରତ)',
            'as_IN' => 'অসমীয়া (ভাৰত)',
            'mr_IN' => 'मराठी (भारत)',
            'ur_PK' => 'اردو (پاکستان)',
            'ur_IN' => 'اردو (بھارت)',
            'fa_IR' => 'فارسی (ایران)',
            'fa_AF' => 'فارسی (افغانستان)',
            'ps_AF' => 'پښتو (افغانستان)',
            'ps_PK' => 'پښتو (پاکستان)',
            'sw_KE' => 'Kiswahili (Kenya)',
            'sw_TZ' => 'Kiswahili (Tanzania)',
            'am_ET' => 'አማርኛ (ኢትዮጵያ)',
            'ha_NG' => 'Hausa (Nigeria)',
            'yo_NG' => 'Yorùbá (Nigeria)',
            'ig_NG' => 'Igbo (Nigeria)',
            'zu_ZA' => 'isiZulu (South Africa)',
            'xh_ZA' => 'isiXhosa (South Africa)',
            'af_ZA' => 'Afrikaans (Suid-Afrika)',
            'st_ZA' => 'Sesotho (Afrika Borwa)',
            'tn_ZA' => 'Setswana (Afrika Borwa)',
            'ts_ZA' => 'Xitsonga (Afrika Dzonga)',
            've_ZA' => 'Tshivenḓa (Afurika Tshipembe)',
            'nd_ZA' => 'isiNdebele (iSewula Afrika)',
            'ss_ZA' => 'siSwati (iNingizimu Afrika)',
            'nr_ZA' => 'isiNdebele (iSewula Afrika)',
        ];

        return $languageNames[$localeCode] ?? ucfirst($localeCode);
    }

    protected function getCountryCode(string $localeCode): string
    {
        $countryMappings = [
            'af' => 'za', 'am' => 'et', 'ar' => 'sa', 'as' => 'in', 'az' => 'az',
            'be' => 'by', 'bg' => 'bg', 'bn' => 'bd', 'bo' => 'cn', 'bs' => 'ba',
            'ca' => 'es', 'ckb' => 'iq', 'cs' => 'cz', 'cy' => 'gb', 'da' => 'dk',
            'de' => 'de', 'dv' => 'mv', 'el' => 'gr', 'en' => 'gb', 'eo' => 'uy',
            'es' => 'es', 'et' => 'ee', 'eu' => 'es', 'fa' => 'ir', 'fi' => 'fi',
            'fo' => 'fo', 'fr' => 'fr', 'fy' => 'nl', 'ga' => 'ie', 'gd' => 'gb',
            'gl' => 'es', 'gu' => 'in', 'ha' => 'ng', 'he' => 'il', 'hi' => 'in',
            'hr' => 'hr', 'hu' => 'hu', 'hy' => 'am', 'id' => 'id', 'ig' => 'ng',
            'is' => 'is', 'it' => 'it', 'ja' => 'jp', 'jv' => 'id', 'ka' => 'ge',
            'kk' => 'kz', 'km' => 'kh', 'kn' => 'in', 'ko' => 'kr', 'ku' => 'iq',
            'ky' => 'kg', 'la' => 'va', 'lb' => 'lu', 'lo' => 'la', 'lt' => 'lt',
            'lv' => 'lv', 'mg' => 'mg', 'mk' => 'mk', 'ml' => 'in', 'mn' => 'mn',
            'mr' => 'in', 'ms' => 'my', 'mt' => 'mt', 'my' => 'mm', 'nb' => 'no',
            'nd' => 'za', 'ne' => 'np', 'nl' => 'nl', 'nn' => 'no', 'no' => 'no',
            'ny' => 'mw', 'or' => 'in', 'pa' => 'in', 'pl' => 'pl', 'ps' => 'af',
            'pt' => 'pt', 'qu' => 'pe', 'ro' => 'ro', 'ru' => 'ru', 'rw' => 'rw',
            'sa' => 'in', 'sd' => 'pk', 'se' => 'no', 'si' => 'lk', 'sk' => 'sk',
            'sl' => 'si', 'sm' => 'ws', 'sn' => 'zw', 'so' => 'so', 'sq' => 'al',
            'sr' => 'rs', 'st' => 'za', 'su' => 'id', 'sv' => 'se', 'sw' => 'ke',
            'ta' => 'in', 'te' => 'in', 'tg' => 'tj', 'th' => 'th', 'tk' => 'tm',
            'tl' => 'ph', 'tn' => 'za', 'to' => 'to', 'tr' => 'tr', 'ts' => 'za',
            'tt' => 'ru', 'tw' => 'gh', 'ty' => 'pf', 'ug' => 'cn', 'uk' => 'ua',
            'ur' => 'pk', 'uz' => 'uz', 've' => 'za', 'vi' => 'vn', 'wo' => 'sn',
            'xh' => 'za', 'yi' => 'il', 'yo' => 'ng', 'zh' => 'cn', 'zu' => 'za',

            // Regional variants
            'zh_CN' => 'cn', 'zh_TW' => 'tw', 'zh_HK' => 'hk', 'zh_SG' => 'sg', 'zh_MO' => 'mo',
            'en_US' => 'us', 'en_GB' => 'gb', 'en_AU' => 'au', 'en_CA' => 'ca', 'en_IE' => 'ie',
            'en_NZ' => 'nz', 'en_ZA' => 'za', 'en_IN' => 'in', 'fr_CA' => 'ca', 'fr_CH' => 'ch',
            'fr_BE' => 'be', 'es_MX' => 'mx', 'es_AR' => 'ar', 'es_CO' => 'co', 'es_CL' => 'cl',
            'es_PE' => 'pe', 'es_VE' => 've', 'es_UY' => 'uy', 'es_PY' => 'py', 'es_BO' => 'bo',
            'es_EC' => 'ec', 'es_GT' => 'gt', 'es_HN' => 'hn', 'es_SV' => 'sv', 'es_NI' => 'ni',
            'es_CR' => 'cr', 'es_PA' => 'pa', 'es_DO' => 'do', 'es_PR' => 'pr', 'es_CU' => 'cu',
            'pt_BR' => 'br', 'pt_PT' => 'pt', 'pt_AO' => 'ao', 'pt_MZ' => 'mz', 'de_AT' => 'at',
            'de_CH' => 'ch', 'de_LU' => 'lu', 'de_LI' => 'li', 'it_CH' => 'ch', 'it_SM' => 'sm',
            'it_VA' => 'va', 'nl_BE' => 'be', 'nl_SR' => 'sr', 'ar_EG' => 'eg', 'ar_SA' => 'sa',
            'ar_AE' => 'ae', 'ar_JO' => 'jo', 'ar_LB' => 'lb', 'ar_SY' => 'sy', 'ar_IQ' => 'iq',
            'ar_KW' => 'kw', 'ar_QA' => 'qa', 'ar_BH' => 'bh', 'ar_OM' => 'om', 'ar_YE' => 'ye',
            'ar_MA' => 'ma', 'ar_TN' => 'tn', 'ar_DZ' => 'dz', 'ar_LY' => 'ly', 'ar_SD' => 'sd',
            'ru_RU' => 'ru', 'ru_BY' => 'by', 'ru_KZ' => 'kz', 'ru_KG' => 'kg', 'ru_UA' => 'ua',
            'hi_IN' => 'in', 'bn_BD' => 'bd', 'bn_IN' => 'in', 'ta_IN' => 'in', 'ta_LK' => 'lk',
            'te_IN' => 'in', 'ml_IN' => 'in', 'kn_IN' => 'in', 'gu_IN' => 'in', 'pa_IN' => 'in',
            'or_IN' => 'in', 'as_IN' => 'in', 'mr_IN' => 'in', 'ur_PK' => 'pk', 'ur_IN' => 'in',
            'fa_IR' => 'ir', 'fa_AF' => 'af', 'ps_AF' => 'af', 'ps_PK' => 'pk', 'sw_KE' => 'ke',
            'sw_TZ' => 'tz', 'am_ET' => 'et', 'ha_NG' => 'ng', 'yo_NG' => 'ng', 'ig_NG' => 'ng',
            'zu_ZA' => 'za', 'xh_ZA' => 'za', 'af_ZA' => 'za', 'st_ZA' => 'za', 'tn_ZA' => 'za',
            'ts_ZA' => 'za', 've_ZA' => 'za', 'nd_ZA' => 'za', 'ss_ZA' => 'za', 'nr_ZA' => 'za',
        ];

        return $countryMappings[$localeCode] ?? strtolower(substr($localeCode, 0, 2));
    }
}
