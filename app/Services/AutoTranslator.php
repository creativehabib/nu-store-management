<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AutoTranslator
{
    public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): ?string
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        if ($targetLocale === $sourceLocale) {
            return $text;
        }

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get('https://api.mymemory.translated.net/get', [
                    'q' => $text,
                    'langpair' => $sourceLocale . '|' . $targetLocale,
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $translatedText = $response->json('responseData.translatedText');

        if (! is_string($translatedText)) {
            return null;
        }

        $translatedText = trim(html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $translatedText !== '' ? $translatedText : null;
    }
}
