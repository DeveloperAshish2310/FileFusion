Yes — you can encrypt/decrypt model data in Laravel, and there are a few ways to do it. The **recommended** simple approach is to use Laravel’s built‑in **encrypted attribute cast** on the model. If you truly want *one place / single change* that automatically encrypts outgoing and decrypts incoming data for many models/fields, you can do that too by hooking into Eloquent events in a service provider (I’ll show an example).

I’ll show two options:

---

# 1) Best / simplest — use Laravel’s built‑in `encrypted` cast (per model, minimal change)

Laravel supports an `encrypted` attribute cast. When you declare a field as `encrypted` in the model’s `$casts`, Laravel will **encrypt on set/save** and **decrypt on get** automatically using the app key.

Example:

```php
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $casts = [
        'secret_notes' => 'encrypted',
        'meta'         => 'encrypted:array', // encrypts JSON array and returns array
    ];
}
```

Benefits:

* Very easy, secure, uses Laravel `Crypt` under the hood (APP_KEY).
* Handles nulls and JSON/array casting when you use `:array` or `:collection`.
* Automatic (no need to write manual encryption code per attribute).

Limitations:

* Must add a line to each model (small per-model change).
* Encrypted columns cannot be searched/filtered at DB level (unless you store separate search tokens).
* Indexing & DB functions don’t work on encrypted values.

---

# 2) “Single change” global approach — automatic encrypt/decrypt via Eloquent events (one place to configure)

If you want **one central change** and not edit multiple models, you can implement a global encryption system that:

* Reads a config listing model classes and attributes (or a wildcard),
* Automatically encrypts those attributes on `saving` and decrypts on `retrieved`.

**Warning & caveats** before the code:

* This approach can be fragile: you must prevent double-encryption, handle nulls, handle arrays/JSON, and accept that you cannot query these encrypted fields in SQL.
* Use APP_KEY and `Crypt` for security.
* Test thoroughly to avoid corrupting data.
* Consider key-rotation strategy before deploying.

**Step 1 — add a config file `config/field-encryption.php`:**

```php
<?php
// config/field-encryption.php
return [
    // Per-model list of attributes to encrypt. Use '*' as model key for "all models"
    // Example:
    // App\Models\User::class => ['ssn', 'credit_card'],
    // '*' => ['notes']  // apply to all models
    'models' => [
        App\Models\Category::class => ['secret_notes', 'meta'],
        // '*' => ['sensitive_field'],
    ],

    // A prefix used to detect already-encrypted values to avoid double-encrypting.
    'prefix' => 'ENC::',
];
```

**Step 2 — AppServiceProvider (or a dedicated provider) boot method:**

```php
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

public function boot()
{
    $config = config('field-encryption.models', []);
    $prefix = config('field-encryption.prefix', 'ENC::');

    // Encrypt right before saving (creating / updating)
    Model::saving(function (Model $model) use ($config, $prefix) {
        $this->processModelAttributes($model, $config, $prefix, 'encrypt');
    });

    // Decrypt right after model is retrieved from DB
    Model::retrieved(function (Model $model) use ($config, $prefix) {
        $this->processModelAttributes($model, $config, $prefix, 'decrypt');
    });

    // Also decrypt fresh() and after find() etc. if needed — retrieved covers most cases
}

/**
 * Helper used above (you can put this in the provider or in a trait/helper class).
 */
protected function processModelAttributes(Model $model, array $config, string $prefix, string $mode)
{
    $class = get_class($model);
    $attributesToHandle = [];

    // specific class config
    if (isset($config[$class]) && is_array($config[$class])) {
        $attributesToHandle = array_merge($attributesToHandle, $config[$class]);
    }

    // wildcard config for all models
    if (isset($config['*']) && is_array($config['*'])) {
        $attributesToHandle = array_merge($attributesToHandle, $config['*']);
    }

    foreach ($attributesToHandle as $attr) {
        if (! array_key_exists($attr, $model->getAttributes())) {
            // attribute not present in model attributes — skip
            continue;
        }

        $value = $model->getAttribute($attr);

        if ($mode === 'encrypt') {
            if ($value === null) {
                continue;
            }
            // Avoid double-encrypt: if already prefixed, skip
            if (is_string($value) && Str::startsWith($value, $prefix)) {
                continue;
            }

            // If value is array/object, JSON encode it first
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            $encrypted = Crypt::encryptString((string) $value);
            $model->setAttribute($attr, $prefix . $encrypted);
        } elseif ($mode === 'decrypt') {
            if (! is_string($value) || ! Str::startsWith($value, $prefix)) {
                continue; // not encrypted or null
            }

            $cipherText = substr($value, strlen($prefix));
            try {
                $decrypted = Crypt::decryptString($cipherText);
            } catch (\Exception $e) {
                // failed to decrypt — skip or log
                continue;
            }

            // try decode JSON back to array/object if JSON
            $json = json_decode($decrypted, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($json) || is_null($json))) {
                $model->setAttribute($attr, $json);
            } else {
                $model->setAttribute($attr, $decrypted);
            }
        }
    }
}
```

**Notes on the provider approach:**

* Put the helper method inside the same provider or reference a class/trait to keep code clean.
* You may also need to handle `replicating`, `newFromBuilder`, `hydrate` depending on your usage, but the `retrieved` event covers most common reads.
* You must ensure this provider runs after config is loaded (AppServiceProvider is fine).
* If you also use `Model::create()` etc., `saving` will encrypt before DB write.

---

## Which approach should you choose?

* If you control the models and there aren’t dozens, **use the `encrypted` cast** — it’s the simplest, safest, and built into Laravel.
* If you absolutely need **one central switch** (single change) and cannot edit models, use the **global events + config** approach, but be careful and test (double-encryption, JSON, nulls, querying limitations, performance).

---

## Other important considerations (short list)

* **Search & indexing**: encrypted fields cannot be searched or indexed. You might create separate searchable/hashed tokens if you need search.
* **Key management / rotation**: rotating APP_KEY is hard — consider envelope encryption or dedicated KMS if you’ll rotate keys.
* **Backups & migrations**: when altering the scheme, migration scripts must be aware data is encrypted.
* **Type handling**: cast arrays/objects to/from JSON before encryption (see examples).
* **Performance**: encrypting many fields on large models can add CPU overhead.

---

If you want, I can:

* give a ready-to-drop-in ServiceProvider file for the global approach, or
* show the exact `$casts` entries for a sample model,
* or show how to add a trait that allows `protected $encrypts = ['ssn']` per model (middle ground).

Which one would you like me to provide right now?
