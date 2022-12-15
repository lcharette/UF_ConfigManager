<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Controller;

use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Admin\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\ConfigManager\Middlewares\ConfigManager;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Update Schema Controller.
 *
 * Controller class post /settings/{schema} URL. Handle post request to save
 * the site settings.
 */
class UpdateSchema
{
    /**
     * Inject services.
     */
    public function __construct(
        protected ConfigManager $manager,
        protected Authenticator $authenticator,
        protected AlertStream $alerts,
        protected ResourceLocatorInterface $locator,
        protected Translator $translator,
    ) {
    }

    /**
     * Processes the request to save the settings to the db.
     *
     * @param string   $schema
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(string $schema, Request $request, Response $response): Response
    {
        if (!$this->authenticator->checkAccess('update_site_config')) {
            throw new ForbiddenException();
        }

        // Request POST data
        $post = $request->getParsedBody();

        // Make sure args has schema
        if (isset($args['schema'])) {
            $schemaName = $args['schema'];
        } else {
            throw new NotFoundException('Schema not defined.');
        }

        // Make sure post has data
        if (isset($post['data'])) {
            $data = $post['data'];
        } else {
            throw new NotFoundException('Data not found.');
        }

        // So we first get the schema data. Load file instead of in constructor as it's easier to mock/test
        if (!$file = $this->locator->getResource('schema://config/' . $schemaName . '.json')) {
            throw new NotFoundException("Schema $schemaName not found.");
        }
        $loader = new YamlFileLoader([]);
        $schemaData = $loader->loadFile($file);

        // We can't pass the file directly to RequestSchema because it's a custom one
        // So we create a new empty RequestSchemaRepository and feed it the `config` part of our custom schema
        $schema = new RequestSchemaRepository($schemaData['config']);

        // Transform the data
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($data);

        // We change the dot notation of our elements to a multidimensional array
        // This is required for the fields (but not for the schema) because the validator doesn't use the
        // dot notation the same way. Sending dot notation field name to the validator will fail.
        $dataArray = [];
        foreach ($data as $key => $value) {
            Arr::set($dataArray, $key, $value);
        }

        // We validate the data array against the schema
        $validator = new ServerSideValidator($schema, $this->translator);
        if (!$validator->validate($dataArray)) {
            $ms->addValidationErrors($validator);

            // TODO : Throw Error
            return $response->withStatus(400);
        }

        // The data is now validated. Instead or switching back the array to dot notation,
        // we can use the `$data` that's still intact. The validator doesn't change the data
        // Next, update each config
        foreach ($data as $key => $value) {
            // We need to access the $schemaData to find if we need to cache this one
            $cached = (isset($schemaData['config'][$key]['cached'])) ? $schemaData['config'][$key]['cached'] : true;

            // Set the config using the manager
            $this->manager->set($key, $value, $cached);
        }

        //Success message!
        $this->alerts->addMessageTranslated('success', 'SITE.CONFIG.SAVED');

        $payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
