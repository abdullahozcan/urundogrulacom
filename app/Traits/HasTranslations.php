<?php

namespace App\Traits;

trait HasTranslations
{
    /**
     * Get a translated attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getTranslation(string $key, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->getAttributeValue($key);

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        if (!is_array($translations)) {
            return $translations;
        }

        return $translations[$locale] ?? $translations[config('languages.default', 'tr')] ?? null;
    }

    /**
     * Magic method to intercept attribute access for translatable fields.
     */
    public function getAttribute($key)
    {
        if (property_exists($this, 'translatable') && in_array($key, $this->translatable)) {
            return $this->getTranslation($key);
        }

        return parent::getAttribute($key);
    }
}
