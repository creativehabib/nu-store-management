<?php

use App\Services\AutoTranslator;
use Illuminate\Support\Facades\Http;

it('returns the original text when source and target locales match', function () {
    Http::preventStrayRequests();

    expect(app(AutoTranslator::class)->translate('Dashboard', 'en'))->toBe('Dashboard');
});

it('translates text through the configured auto translation endpoint', function () {
    Http::fake([
        'api.mymemory.translated.net/*' => Http::response([
            'responseData' => [
                'translatedText' => 'ড্যাশবোর্ড',
            ],
        ]),
    ]);

    $translatedText = app(AutoTranslator::class)->translate('Dashboard', 'bn');

    expect($translatedText)->toBe('ড্যাশবোর্ড');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.mymemory.translated.net/get'
        && $request['q'] === 'Dashboard'
        && $request['langpair'] === 'en|bn');
});

it('returns null when the auto translation endpoint fails', function () {
    Http::fake([
        'api.mymemory.translated.net/*' => Http::response([], 500),
    ]);

    expect(app(AutoTranslator::class)->translate('Dashboard', 'bn'))->toBeNull();
});
