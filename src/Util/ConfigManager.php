<?php

/*
 * UF Config Manager
 *
 * @link https://github.com/lcharette/UF_ConfigManager
 *
 * @copyright Copyright (c) 2019 Louis Charette
 * @license https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Util;

use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Config;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;

/**
 * GastonServicesProvider class.
 * Registers services for the account sprinkle, such as currentUser, etc.
 */
class ConfigManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * __construct function.
     *
     * @param ContainerInterface $ci
     *
     * @return void
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
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
        $this->ci->config->mergeItems(null, $this->fetch());

        return $next($request, $response);
    }

    /**
     * fetch function.
     * Fetch all the config from the db and uses the
     * cache container to store most of thoses setting in the cache system.
     *
     * @return void
     */
    public function fetch()
    {
        $cache = $this->ci->cache;

        // Case n° 1 we don't have cached content. We load everything
        // Case n° 2 we have cached content, pull that and load the non chanched things to it
        if (($cached_settings = $cache->get('UF_config')) === null) {
            $settingsCollection = Config::all();
            $settings = $this->collectionToArray($settingsCollection);

            // Save in cache. The settings that are not cached are not included
            $cache->forever('UF_config', $this->collectionToArray($settingsCollection, false));
        } else {
            // We have the cached values, we need to grab the non cached ones
            $settingsCollection = Config::where('cached', 0);
            $settings = array_merge_recursive($cached_settings, $this->collectionToArray($settingsCollection));
        }

        return $settings;
    }

    /**
     * delete function.
     * Removes a configuration option.
     *
     * @param string $key The setting's name
     *
     * @return bool Success
     */
    public function delete($key)
    {

        // Get the desired key
        if (!$setting = Config::where('key', $key)->first()) {
            return false;
        }

        // Delete time
        $setting->delete();

        // Remove from current laod
        unset($this->ci->config[$key]);

        // Delete cache
        if ($setting->cached) {
            $this->ci->cache->forget('UF_config');
        }

        return true;
    }

    /**
     * set function.
     * Sets a setting's value.
     *
     * @param string $key    The setting's name
     * @param string $value  The new value
     * @param bool   $cached (default: true)      Whether this variable should be cached or if it
     *                       changes too frequently to be efficiently cached.
     *
     * @return bool True if the value was changed, false otherwise
     */
    public function set($key, $value, $cached = true)
    {
        return $this->set_atomic($key, false, $value, $cached);
    }

    /**
     * set_atomic function.
     * Sets a setting's value only if the old_value matches the
     * current value or the setting does not exist yet.
     *
     * @param string $key       The setting's name
     * @param string $old_value Current configuration value or false to ignore
     *                          the old value
     * @param string $new_value The new value
     * @param bool   $cached    (default: true)      Whether this variable should be cached or if it
     *                          changes too frequently to be efficiently cached.
     *
     * @return bool True if the value was changed, false otherwise
     */
    public function set_atomic($key, $old_value, $new_value, $cached = true)
    {

        // Get the desired key
        $setting = Config::where('key', $key)->first();

        if ($setting) {
            if ($old_value === false || $setting->value == $old_value) {
                $setting->value = $new_value;
                $setting->save();
            } else {
                return false;
            }
        } else {
            $setting = new Config([
                'key'    => $key,
                'value'  => $new_value,
                'cached' => $cached,
            ]);
            $setting->save();
        }

        if ($cached) {
            $this->ci->cache->forget('UF_config');
        }

        $this->ci->config[$key] = $new_value;

        return true;
    }

    /**
     * getAllShemas function.
     * Get all the config schemas available.
     *
     * @return void
     */
    public function getAllShemas()
    {
        $configSchemas = [];

        $loader = new YamlFileLoader([]);

        // Get all the location where we can find config schemas
        $paths = array_reverse($this->ci->locator->findResources('schema://config', true, false));

        // For every location...
        foreach ($paths as $path) {

            // Get a list of all the schemas file
            $files_with_path = glob($path.'/*.json');

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
     * collectionToArray function.
     * This function Expand the db dot notation single level array
     * to a multi-dimensional array.
     *
     * @param Collection $Collection Eloquent collection
     *
     * @return array
     */
    private function collectionToArray($Collection, $include_noncached = true)
    {
        $settings_array = [];
        foreach ($Collection as $setting) {
            if ($include_noncached || $setting->cached) {
                array_set($settings_array, $setting->key, $setting->value);
            }
        }

        return $settings_array;
    }
}
