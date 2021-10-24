<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\FlashcardGame\QuizFlashcardGame\QuizFlashcardGame;
use App\FlashcardGame\FlashcardGameInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            FlashcardGameInterface::class,
            QuizFlashcardGame::class
        );
    }
}
