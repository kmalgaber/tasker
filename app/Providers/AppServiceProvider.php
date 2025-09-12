<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\SecuritySchemes\OAuthFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading();
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::oauth2()
                        ->flow('authorizationCode', function (OAuthFlow $flow) {
                            $flow
                                ->authorizationUrl(config('app.url') . '/auth/realms/sso/protocol/openid-connect/auth')
                                ->tokenUrl(config('app.url') . '/auth/realms/sso/protocol/openid-connect/token')
                                ->addScope('*', 'all');
                        })
                );
            });
    }
}
