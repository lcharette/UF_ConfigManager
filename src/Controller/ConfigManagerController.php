<?php

/*
 * UF Config Manager
 *
 * @link https://github.com/lcharette/UF_ConfigManager
 *
 * @copyright Copyright (c) 2019 Louis Charette
 * @license https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Controller;

use Interop\Container\ContainerInterface;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\FormGenerator\Form;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;

/**
 * ConfigManagerController Class.
 *
 * Controller class for /settings/* URLs.  Generate the interface required to modify the sites settings and saving the changes
 */
class ConfigManagerController
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var ConfigManager Hold the ConfigManager class that handle setting the config and getting the config schema
     *                    Note that we don't interact with the `Config` db model directly since it can't handle the cache
     */
    protected $manager;

    /**
     * __construct function.
     * Create a new ConfigManagerController object.
     *
     * @param ContainerInterface $ci
     *
     * @return void
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->manager = new ConfigManager($ci);
    }

    /**
     * mainList function.
     * Used to display a list of all schema with their form.
     *
     * @param mixed $request
     * @param mixed $response
     * @param mixed $args
     *
     * @return void
     */
    public function displayMain($request, $response, $args)
    {

        // Access-controlled resource
        if (!$this->ci->authorizer->checkAccess($this->ci->currentUser, 'update_site_config')) {
            throw new ForbiddenException();
        }

        // Get all the config schemas
        $schemas = $this->manager->getAllShemas();

        // Parse each of them to get it's content
        foreach ($schemas as $i => $schemaData) {

            // Set the schemam, the validator and the form
            $schema = new RequestSchemaRepository($schemaData['config']);
            $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

            // Create the form
            $config = $this->ci->config;
            $form = new Form($schema, $config);

            // The field names dot syntaxt won't make it across the HTTP POST request.
            // Wrap them in a nice `data` array
            $form->setFormNamespace('data');

            // Twig doesn't need the raw thing
            unset($schemas[$i]['config']);

            // Add the field and validator so Twig can play with them
            $schemas[$i]['fields'] = $form->generate();
            $schemas[$i]['validators'] = $validator->rules('json', true);

            // Add the save url for that schema
            $schemas[$i]['formAction'] = $this->ci->router->pathFor('ConfigManager.save', ['schema' => $schemaData['filename']]);
        }

        // Time to render the page !
        $this->ci->view->render($response, 'pages/ConfigManager.html.twig', [
            'schemas' => $schemas,
        ]);
    }

    /**
     * update function.
     * Processes the request to save the settings to the db.
     *
     * @param mixed $request
     * @param mixed $response
     * @param mixed $args
     *
     * @return void
     */
    public function update($request, $response, $args)
    {

        // Get the alert message stream
        $ms = $this->ci->alerts;

        // Access-controlled resource
        if (!$this->ci->authorizer->checkAccess($this->ci->currentUser, 'update_site_config')) {
            throw new ForbiddenException();
        }

        // Request POST data
        $post = $request->getParsedBody();

        // So we first get the shcema data
        $loader = new YamlFileLoader('schema://config/'.$args['schema'].'.json');
        $schemaData = $loader->load();

        // We can't pass the file directly to RequestSchema because it's a custom one
        // So we create a new empty RequestSchemaRepository and feed it the `config` part of our custom schema
        $schema = new RequestSchemaRepository($schemaData['config']);

        // Transform the data
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($post['data']);

        // We change the dot notation of our elements to a multidimensionnal array
        // This is required for the fields (but not for the schema) because the validator doesn't use the
        // dot notation the same way. Sending dot notation field name to the validator will fail.
        $dataArray = [];
        foreach ($data as $key => $value) {
            array_set($dataArray, $key, $value);
        }

        // We validate the data array against the schema
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($dataArray)) {
            $ms->addValidationErrors($validator);

            return $response->withStatus(400);
        }

        // The data is now validaded. Instead or switching back the array to dot notation,
        // we can use the `$data` that's still intact. The validator doesn't change the data
        // Next, update each config
        foreach ($data as $key => $value) {

            // We need to access the $schemaData to find if we need to cache this one
            $cached = (isset($schemaData['config'][$key]['cached'])) ? $schemaData['config'][$key]['cached'] : true;

            // Set the config using the manager
            $this->manager->set($key, $value, $cached);
        }

        //Success message!
        $ms->addMessageTranslated('success', 'SITE.CONFIG.SAVED');

        return $response->withJson([], 200, JSON_PRETTY_PRINT);
    }
}
