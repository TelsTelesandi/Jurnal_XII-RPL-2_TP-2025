<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\LoginResponse as CustomLoginResponse;
use Blaspsoft\Blasp\BlaspService;
use Blaspsoft\Blasp\Config\ConfigurationLoader;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        $extraWords = config('blasp.extra_words');

        if (is_array($extraWords) && !empty($extraWords)) {
            // Get default language
            $language = config('blasp.default_language', 'english');
            
            // Load existing profanities from language file
            $languagePath = base_path("vendor/blaspsoft/blasp/config/languages/{$language}.php");
            $existingProfanities = [];
            
            if (file_exists($languagePath)) {
                $languageConfig = require $languagePath;
                $existingProfanities = $languageConfig['profanities'] ?? [];
            } else {
                // Fallback to config profanities
                $existingProfanities = config('blasp.profanities', []);
            }
            
            // Merge extra words with existing profanities
            $allProfanities = array_unique(array_merge($existingProfanities, $extraWords));
            
            // Override binding to use merged profanities
            $this->app->bind(BlaspService::class, function ($app) use ($allProfanities) {
                return new BlaspService(
                    $allProfanities,
                    null, // false positives
                    $app->make(ConfigurationLoader::class)
                );
            });
            
            // Also override the 'blasp' alias
            $this->app->bind('blasp', function ($app) {
                return $app->make(BlaspService::class);
            });
        }
    }

}
