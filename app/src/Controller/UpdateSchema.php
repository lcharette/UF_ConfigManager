<?php

declare(strict_types=1);

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
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\ConfigManager\Exceptions\MissingDataException;
use UserFrosting\Sprinkle\ConfigManager\Exceptions\SchemaNotFoundException;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
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
     * @param string   $schemaName
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(string $schemaName, Request $request, Response $response): Response
    {
        $this->validateAccess();
        $this->handle($request, $schemaName);
        $payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param Request $request
     */
    protected function handle(Request $request, string $schemaName): void
    {
        // Request POST data
        $post = (array) $request->getParsedBody();

        // Make sure post has data
        if (!isset($post['data'])) {
            throw new MissingDataException();
        }

        // Load the request schema
        $schema = $this->getSchema($schemaName);

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($post['data']);

        // We change the dot notation of our elements to a multidimensional array
        // This is required for the fields (but not for the schema) because the validator doesn't use the
        // dot notation the same way. Sending dot notation field name to the validator will fail.
        $dataArray = [];
        foreach ($data as $key => $value) {
            Arr::set($dataArray, $key, $value);
        }

        // Validate request data
        $this->validateData($schema, $dataArray);

        // The data is now validated. Instead or switching back the array to dot notation,
        // we can use the `$data` that's still intact. The validator doesn't change the data
        // Next, update each config
        foreach ($data as $key => $value) {
            // We need to access the $schemaData to find if we need to cache this one
            // TODO : $schemaData not available !
            $cached = (isset($schemaData['config'][$key]['cached'])) ? $schemaData['config'][$key]['cached'] : true;

            // Set the config using the manager
            $this->manager->set($key, $value, $cached);
        }

        //Success message!
        $this->alerts->addMessageTranslated('success', 'SITE.CONFIG.SAVED');
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(): void
    {
        if (!$this->authenticator->checkAccess('update_site_config')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    protected function getSchema(string $schemaName): RequestSchemaInterface
    {
        // So we first get the schema data. Load file instead of in constructor as it's easier to mock/test
        $file = $this->locator->getResource('schema://config/' . $schemaName . '.json');
        if ($file === null) {
            $e = new SchemaNotFoundException();
            $e->setSchema($schemaName);

            throw $e;
        }

        $loader = new YamlFileLoader([]);
        $schemaData = $loader->loadFile((string) $file);

        // We can't pass the file directly to RequestSchema because it's a custom one
        // So we create a new empty RequestSchemaRepository and feed it the `config` part of our custom schema
        $schema = new RequestSchemaRepository($schemaData['config']);

        return $schema;
    }

    /**
     * Validate request POST data.
     *
     * @param RequestSchemaInterface $schema
     * @param mixed[]                $data
     */
    protected function validateData(RequestSchemaInterface $schema, array $data): void
    {
        $validator = new ServerSideValidator($schema, $this->translator);
        if ($validator->validate($data) === false && is_array($validator->errors())) {
            $e = new ValidationException();
            $e->addErrors($validator->errors());

            throw $e;
        }
    }
}
