<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use UserFrosting\Config\Config;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\FormGenerator\Form;

/**
 * Display Page Controller.
 *
 * Controller class for /settings URL. Generate the interface required to
 * modify the site settings.
 */
class DisplayPage
{
    /**
     * Inject services.
     */
    public function __construct(
        protected ConfigManager $manager,
        protected Authenticator $authenticator,
        protected Translator $translator,
        protected Config $config,
        protected RouteParserInterface $routeParser,
        protected Twig $view,
    ) {
    }

    /**
     * Used to display a list of all schema with their form.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        // Access-controlled resource
        if (!$this->authenticator->checkAccess('update_site_config')) {
            throw new ForbiddenException();
        }

        // Get all the config schemas
        $schemas = $this->manager->getAllSchemas();

        // Parse each of them to get it's content
        foreach ($schemas as $i => $schemaData) {
            // Set the schema, the validator and the form
            $schema = new RequestSchemaRepository($schemaData['config']);
            $validator = new JqueryValidationAdapter($schema, $this->translator);

            // Create the form
            $form = new Form($schema, $this->config);

            // The field names dot syntax won't make it across the HTTP POST request.
            // Wrap them in a nice `data` array
            $form->setFormNamespace('data');

            // Twig doesn't need the raw thing
            unset($schemas[$i]['config']);

            // Add the field and validator so Twig can play with them
            $schemas[$i]['fields'] = $form->generate();
            $schemas[$i]['validators'] = $validator->rules('json', true);

            // Add the save url for that schema
            $schemas[$i]['formAction'] = $this->routeParser->urlFor('ConfigManager.save', ['schemaName' => $schemaData['filename']]);
        }

        // Time to render the page !
        return $this->view->render($response, 'pages/ConfigManager.html.twig', [
            'schemas' => $schemas,
        ]);
    }
}
