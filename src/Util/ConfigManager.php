<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Util;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * GastonServicesProvider class.
 * Registers services for the account sprinkle, such as currentUser, etc.
 */
class ConfigManager
{
    /** @var Config */
    protected $config;

    /** @var Cache */
    protected $cache;

    /** @var ResourceLocatorInterface */
    protected $locator;

    /**
     * Constructor.
     *
     * @param ResourceLocatorInterface $locator
     * @param Cache                    $cache
     * @param Config                   $config
     */
    public function __construct(ResourceLocatorInterface $locator, Cache $cache, Config $config)
    {
        $this->locator = $locator;
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * __invoke function.
     * Invoke the ConfigManager middleware, merging the db config with the file based one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
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
     */
    public function fetch()
    {
        $cache = $this->cache;

        // Case n° 1 we don't have cached content. We load everything
        // Case n° 2 we have cached content, pull that and load the non chanched things to it
        if (($cached_settings = $cache->get('UF_config')) === null) {
            $settingsCollection = Setting::all();
            $settings = $this->collectionToArray($settingsCollection);

            // Save in cache. The settings that are not cached are not included
            $cache->forever('UF_config', $this->collectionToArray($settingsCollection, false));
        } else {
            // We have the cached values, we need to grab the non cached ones
            $settingsCollection = Setting::where('cached', 0);
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

        // Remove from current laod
        unset($this->config[$key]);

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
        return $this->set_atomic($key, null, $value, $cached);
    }

    /**
     * Sets a setting's value only if the old_value matches the
     * current value or the setting does not exist yet.
     *
     * @param string      $key       The setting's name
     * @param string|null $old_value Current configuration value or false to ignore the old value
     * @param string      $new_value The new value
     * @param bool        $cached    (default: true) Whether this variable should be cached or if it
     *                               changes too frequently to be efficiently cached.
     *
     * @return bool True if the value was changed, false otherwise
     */
    public function set_atomic(string $key, ?string $old_value, string $new_value, bool $cached = true): bool
    {

        // Get the desired key
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            if (is_null($old_value) || $setting->value == $old_value) {
                $setting->value = $new_value;
                $setting->save();
            } else {
                return false;
            }
        } else {
            $setting = new Setting([
                'key'    => $key,
                'value'  => $new_value,
                'cached' => $cached,
            ]);
            $setting->save();
        }

        if ($cached) {
            $this->cache->forget('UF_config');
        }

        $this->config[$key] = $new_value;

        return true;
    }

    /**
     * Get all the config schemas available.
     *
     * @return mixed[]
     */
    public function getAllShemas(): array
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
     * @param iterable<Setting> $collection Eloquent collection
     *
     * @return array<string,string>
     */
    protected function collectionToArray(iterable $collection, bool $include_noncached = true): array
    {
        $settings_array = [];
        foreach ($collection as $setting) {
            if ($include_noncached || $setting->cached) {
                Arr::set($settings_array, $setting->key, $setting->value);
            }
        }

        return $settings_array;
    }
}
