<?php

namespace App\Providers;

use App\Contracts\WordListParser;
use App\Contracts\WordSimilarityAlgorithm;
use App\Services\PlainTextWordListParser;
use App\Services\SortedLettersAlgorithm;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WordSimilarityAlgorithm::class, SortedLettersAlgorithm::class);
        $this->app->bind(WordListParser::class, PlainTextWordListParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
