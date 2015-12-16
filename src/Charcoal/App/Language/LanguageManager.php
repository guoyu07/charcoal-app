<?php

namespace Charcoal\App\Language;

use \InvalidArgumentException;
use \RuntimeException;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\AbstractManager;

// Intra-module (`charcoal-config`) dependency
use \Charcoal\Config\GenericConfig;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Translation\Catalog;
use \Charcoal\Translation\CatalogInterface;
use \Charcoal\Translation\ConfigurableTranslationTrait;
use \Charcoal\Translation\MultilingualAwareInterface;
use \Charcoal\Translation\TranslationConfig;
use \Charcoal\Translation\TranslationString;
use \Charcoal\Translation\TranslationStringInterface;

// Local namespace dependencies
use \Charcoal\App\Language\Language;
use \Charcoal\App\Language\LanguageInterface;

/**
 * Manage a collection of LanguageInterface objects and a unique TranslationConfig object.
 *
 * Not implementing ConfigurableInterface because the AbstractManager shouldn't permit
 * it's properties to be modified from outside the manager.
 */
class LanguageManager extends AbstractManager implements
    MultilingualAwareInterface
{
    use ConfigurableTranslationTrait;

    /**
     * A static cache of language information, raw data
     * used to create fill-up Language objects.
     *
     * @var GenericConfig
     */
    private static $language_index;

    /**
     * Set up the available languages, defaults, and active
     *
     * @return self
     */
    public function setup()
    {
        $config = $this->config();

        if (!($config instanceof TranslationConfig)) {
            $this->setup_languages($config);
            $this->setup_translations($config);
        }

        return $this;
    }

    /**
     * Set up the available languages, defaults, and active
     *
     * Settings:
     * - languages
     * - default_language
     * - current_language
     *
     * @param  array $config The raw configuration array provided upon instantiation.
     * @return void
     * @throws RuntimeException If the languages passed to the manager isn't an associative array.
     * @todo   Implement cache get/set of JSON data based on languages.
     */
    public function setup_languages(array $config)
    {
        $langs = [];

        if (isset($config['default_language'])) {
            $langs['default_language'] = $config['default_language'];
        }

        /** Not recommended; allow the current language to be determined by the client. */
        if (isset($config['current_language'])) {
            $langs['current_language'] = $config['current_language'];
        }

        /**
         * Build the array of Language objects from the `TranslationConfig`-filtered list
         * to prevent any bad apples from slipping through.
         */
        if (isset($config['languages'])) {
            if (is_array($config['languages'])) {
                $config['languages'] = array_filter($config['languages'], function ($config) {
                    return (!isset($config['active']) || $config['active']);
                });

                if (count($config['languages'])) {
                    $index = self::language_index(array_keys($config['languages']));

                    $langs['languages'] = [];
                    foreach ($config['languages'] as $ident => $data) {
                        $lang = new Language();

                        if (!is_array($data)) {
                            $data = [];
                        }

                        if (isset($index[$ident])) {
                            $data = array_merge($index[$ident], $data);
                        }

                        if (!isset($data['ident'])) {
                            $lang->set_ident($ident);
                        }

                        $lang->set_data($data);

                        $langs['languages'][$lang->ident()] = $lang;
                    }
                }
            } else {
                throw new RuntimeException('Languages must be an associative array (e.g., `$langCode => $langInfo`).');
            }
        }

        $translator = new TranslationConfig($langs);
        $this->set_config($translator);
    }

    /**
     * Set up global string translations
     *
     * Settings:
     * - translations
     *
     * @param  array $config The raw configuration array provided upon instantiation.
     * @return void
     * @throws RuntimeException If the translations passed to the manager isn't an associative array.
     */
    private function setup_translations(array $config)
    {
        $trans = [];

        /**
         * Build the array of Language objects from the `TranslationConfig`-filtered list
         * to prevent any bad apples from slipping through.
         */
        if (isset($config['translations'])) {
            if (is_array($config['translations'])) {
                $trans = $config['translations'];
            } else {
                throw new RuntimeException(
                    'Global translations must be an associative array (e.g., `$ident => $translations`).'
                );
            }
        }

        $catalog = new Catalog($trans);
        $this->set_catalog($catalog);
    }

    /**
     * Get the manager's translation catalog
     *
     * @param  CatalogInterface $catalog A catalog to hold translations for the manager.
     * @return self
     */
    protected function set_catalog(CatalogInterface $catalog)
    {
        $this->catalog = $catalog;
        return $this;
    }

    /**
     * Get the manager's translation catalog
     *
     * @return CatalogInterface The manager's catalog of translations.
     */
    public function catalog()
    {
        return $this->catalog;
    }

    /**
     * Alias of `ConfigurableInterface::config()`
     *
     * @return TranslationConfig The manager's translation configuration object.
     * @throws RuntimeException If the manager hasn't been set up.
     */
    public function translation()
    {
        $config = $this->config();

        if (!($config instanceof TranslationConfig)) {
            throw new RuntimeException('Manager hasn’t been set up.');
        }

        return $config;
    }

    /**
     * Get a list of all existing languages, their names and translations,
     * codes in various standards, and directionality.
     *
     * @param  string $cache_key If provided, returns a subset of language information.
     *     Defaults to returning all language data.
     * @return GenericConfig
     *
     * @todo Implement cache get/set of JSON data based on languages.
     */
    public static function language_index($cache_key = null)
    {
        if (!isset(self::$language_index)) {
            if ($cache_key || !$cache_key) {
                self::$language_index = new GenericConfig(__DIR__.'/../../../../config/languages.json');
            }
        }

        return self::$language_index;
    }
}