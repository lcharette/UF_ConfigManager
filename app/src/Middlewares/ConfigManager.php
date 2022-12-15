<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Middlewares;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Middleware to merge database config with file config on every request.
 */
class ConfigManager
{
    /**
     * Inject services.
     *
     * @param ResourceLocatorInterface $locator
     * @param Cache                    $cache
     * @param Config                   $config
     */
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected Cache $cache,
        protected Config $config
    ) {
    }

    /**
     * Invoke the ConfigManager middleware, merging the db config with the file based one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable|\Closure                        $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $this->config->mergeItems(null, $this->fetch());

        return $next($request, $response);
    }

    /**
     * Fetch all the config from the db.
     * Uses the cache container to store most of thoses setting in the cache system.
     *
     * @return array<string,string>
     */
    public function fetch(): array
    {
        // Case n° 1 we don't have cached content. We load everything
        // Case n° 2 we have cached content, get and merge non cached setting into it
        if (($cached_settings = $this->cache->get('UF_config')) === null) {
            $settingsCollection = Setting::all();
            $settings = $this->collectionToArray($settingsCollection);

            // Get an array of the settings that are not marked as cached and save them in cache.
            $cachedSettings = $this->collectionToArray($settingsCollection, false);
            $this->cache->forever('UF_config', $cachedSettings);
        } else {
            // We have the cached values, we need to grab the non cached ones
            $settingsCollection = Setting::where('cached', false)->get();
            $settings = array_merge_recursive($cached_settings, $this->collectionToArray($settingsCollection));
        }

        return $settings;
    }

    /**
     * Removes a configuration option.
     *
     * @param string $key The setting's name
     *
     * @return bool Success
     */
    public function delete(string $key): bool
    {
        // Get the desired key
        if (!$setting = Setting::where('key', $key)->first()) {
            return false;
        }

        // Delete time
        $setting->delete();

        // Remove from current load
        $this->config->offsetUnset($key);

        // Delete cache
        if ($setting->cached) {
            $this->cache->forget('UF_config');
        }

        return true;
    }

    /**
     * Sets a setting's value.
     *
     * @param string $key    The setting's name
     * @param string $value  The new value
     * @param bool   $cached (default: true)      Whether this variable should be cached or if it
     *                       changes too frequently to be efficiently cached.
     *
     * @return bool True if the value was changed, false otherwise
     */
    public function set(string $key, string $value, bool $cached = true): bool
    {
        // Get the desired key
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            if ($setting->value !== $value) {
                $setting->value = $value;
                $setting->cached = $cached;
                $setting->save();
            } else {
                return false;
            }
        } else {
            $setting = new Setting([
                'key'    => $key,
                'value'  => $value,
                'cached' => $cached,
            ]);
            $setting->save();
        }

        if ($cached) {
            $this->cache->forget('UF_config');
        }

        $this->config->set($key, $value);

        return true;
    }

    /**
     * Get all the config schemas available.
     *
     * @return mixed[]
     */
    public function getAllSchemas(): array
    {
        $configSchemas = [];

        $loader = new YamlFileLoader([]);

        // Get all the location where we can find config schemas
        $paths = array_reverse($this->locator->findResources('schema://config', true, false));

        // For every location...
        foreach ($paths as $path) {
            // Get a list of all the schemas file
            $files_with_path = glob($path . '/*.json');

            // Load every found files
            foreach ($files_with_path as $file) {
                // Load the file content
                $schema = $loader->loadFile($file);

                // Get file name
                $filename = basename($file, '.json');

                //inject file name
                $schema['filename'] = $filename;

                // Add to list
                $configSchemas[$filename] = $schema;
            }
        }

        return $configSchemas;
    }

    /**
     * This function Expand the db dot notation single level array to a multi-dimensional array.
     *
     * @param iterable<Setting> $collection       Eloquent collection
     * @param bool              $includeNonCached If false, only settings marked as cached will be returned. True, all settings are returned.
     *
     * @return array<string,string>
     */
    protected function collectionToArray(iterable $collection, bool $includeNonCached = true): array
    {
        $settings_array = [];
        foreach ($collection as $setting) {
            if ($includeNonCached || $setting->cached) {
                Arr::set($settings_array, $setting->key, $setting->value);
            }
        }

        return $settings_array;
    }
}
