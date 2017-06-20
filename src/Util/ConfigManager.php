<?php
/**
 * Gaston (http://gaston.bbqsoftwares.com)
 *
 * @link      https://github.com/lcharette/GASTON
 * @copyright Copyright (c) 2016 Louis Charette
 * @license
 */
namespace UserFrosting\Sprinkle\ConfigManager\Util;

use UserFrosting\Sprinkle\ConfigManager\Database\Models\Config;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Exception\JsonException;
use Interop\Container\ContainerInterface;

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
     * @access public
     * @param ContainerInterface $ci
     * @return void
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * __invoke function.
     * Invoke the ConfigManager middleware, merging the db config with the file based one
     *
     * @access public
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
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
     * cache container to store most of thoses setting in the cache system
     *
     * @access public
     * @return void
     */
    public function fetch() {

        $cache = $this->ci->cache;

        // Case n° 1 we don't have cached content. We load everything
        // Case n° 2 we have cached content, pull that and load the non chanched things to it
        if (($cached_settings = $cache->get('UF_config')) === null)
		{
			$settingsCollection = Config::all();
			$settings = $this->collectionToArray($settingsCollection);

			// Save in cache. The settings that are not cached are not included
			$cache->forever('UF_config', $this->collectionToArray($settingsCollection, false));
		}
		else
		{
			// We have the cached values, we need to grab the non cached ones
			$settingsCollection = Config::where('cached', 0);
			$settings = array_merge_recursive($cached_settings, $this->collectionToArray($settingsCollection));
		}

		return $settings;
    }

    /**
     * delete function.
     * Removes a configuration option
     *
     * @access public
     * @param string $key       The setting's name
     * @return bool             Success
     */
    public function delete($key) {

        // Get the desired key
        if (!$setting = Config::where('key', $key)->first()) {
            return false;
        }

        // Delete time
        $setting->delete();

        // Remove from current laod
		unset($this->ci->config[$key]);

        // Delete cache
		if ($setting->cached)
		{
			$this->ci->cache->forget('UF_config');
		}

		return true;
    }

    /**
     * set function.
     * Sets a setting's value
     *
     * @access public
     * @param string $key                       The setting's name
     * @param string $value                     The new value
     * @param bool $cached (default: true)      Whether this variable should be cached or if it
	 *                                          changes too frequently to be efficiently cached.
     * @return bool                             True if the value was changed, false otherwise
     */
    public function set($key, $value, $cached = true) {
		return $this->set_atomic($key, false, $value, $cached);
	}

	/**
	 * set_atomic function.
	 * Sets a setting's value only if the old_value matches the
	 * current value or the setting does not exist yet.
	 *
	 * @access public
     * @param string $key                       The setting's name
	 * @param string $old_value                 Current configuration value or false to ignore
	 *                                          the old value
	 * @param string $new_value                 The new value
	 * @param bool $cached (default: true)      Whether this variable should be cached or if it
	 *                                          changes too frequently to be efficiently cached.
	 * @return bool                             True if the value was changed, false otherwise
	 */
	public function set_atomic($key, $old_value, $new_value, $cached = true) {

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
                'key' => $key,
                'value' => $new_value,
                'cached' => $cached
            ]);
            $setting->save();
        }

		if ($cached)
		{
			$this->ci->cache->forget('UF_config');
		}

		$this->ci->config[$key] = $new_value;
		return true;
	}

    /**
     * getAllShemas function.
     * Get all the config schemas available
     *
     * @access public
     * @return void
     */
    public function getAllShemas() {

        $configSchemas = array();

        // Get all the location where we can find config schemas
        $paths = array_reverse($this->ci->locator->findResources('schema://config', true, false));

        // For every location...
        foreach ($paths as $path) {

            // Get a list of all the schemas file
            $files_with_path = glob($path . "/*.json");

            // Load every found files
            foreach ($files_with_path as $file) {

                 // Load the file content
                 $schema = $this->loadSchema($file);

                 // Get file name
                 $filename = basename($file, ".json");

                 //inject file name
                 $schema['filename'] = $filename;

                 // Add to list
                 $configSchemas[$filename] = $schema;
            }
        }

        return $configSchemas;

    }

    /**
     * loadSchema function.
     * Load the specified file content and return it as an array
     *
     * @access public
     * @param mixed $file   The full path of the schema we want
     * @return array        The schema content
     */
    public function loadSchema($file)
    {
        $doc = file_get_contents($file);
        if ($doc === false)
            throw new FileNotFoundException("The schema '$file' could not be found.");

        $schema = json_decode($doc, true);
        if ($schema === null) {
            throw new JsonException("The schema '$file' does not contain a valid JSON document.  JSON error: " . json_last_error());
        }

        return $schema;
    }


    /**
     * collectionToArray function.
     * This function Expand the db dot notation single level array
     * to a multi-dimensional array
     *
     * @access private
     * @param Collection $Collection    Eloquent collection
     * @return array
     */
    private function collectionToArray($Collection, $include_noncached = true) {
        $settings_array = array();
        foreach ($Collection as $setting) {
            if ($include_noncached || $setting->cached) {
                array_set($settings_array, $setting->key, $setting->value);
            }
        }
        return $settings_array;
    }
}