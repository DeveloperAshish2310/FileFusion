<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Encryptor;







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
        //
        Schema::defaultStringLength(191);
        // 🔐 Encrypt before save or update
        // Model::saving(function ($model) {
        //     foreach ($model->getAttributes() as $key => $value) {
        //         if (in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'])) continue;

        //         if (!empty($value) && !self::isEncrypted($value)) {
        //             $model->{$key} = Encryptor::encrypt($value);
        //         }
        //     }
        // });

        // // 🔓 Decrypt automatically when model is retrieved
        // Model::retrieved(function ($model) {
        //     foreach ($model->getAttributes() as $key => $value) {
        //         if (in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'])) continue;

        //         if (!empty($value)) {
        //             $value = Encryptor::decrypt($value);
        //             $model->{$key} = Encryptor::decrypt($value);
        //         }
        //     }
        // });
    }


    protected static function isEncrypted($value)
    {
        // quick check: base64 pattern & decrypt success
        return is_string($value)
            && preg_match('/^[A-Za-z0-9+\/=]+$/', $value)
            && (bool) openssl_decrypt(base64_decode($value), 'AES-256-CBC', 'dummy', 0, str_repeat('0', 16));
    }
}
