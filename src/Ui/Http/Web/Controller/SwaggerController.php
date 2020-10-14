<?php

declare(strict_types=1);

namespace App\Ui\Http\Web\Controller;

use App\Ui\Http\Rest\Controller\OpenApiController;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser as YamlParser;
use Throwable;

use function file_exists;
use function file_get_contents;
use function json_encode;
use function str_replace;

class SwaggerController extends OpenApiController
{
    /**
     * @var string
     */
    private string $template;

    /**
     * SwaggerController constructor.
     * @param YamlParser $yamlParser
     * @param YamlDumper $yamlDumper
     * @param string $openApiFile
     * @param string $template
     */
    public function __construct(YamlParser $yamlParser, YamlDumper $yamlDumper, string $openApiFile, string $template)
    {
        parent::__construct($yamlParser, $yamlDumper, $openApiFile);

        $this->template = $template;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpInternalServerErrorException
     */
    public function getOpenApi(Request $request, Response $response): Response
    {
        $accept = $request->getHeaderLine('Accept');

        switch ($accept) {
            case 'application/json':
                return $this->getOpenApiJson($request, $response);
            case 'application/yaml':
                return $this->getOpenApiYaml($request, $response);
        }

        try {
            $response->getBody()->write($this->getTemplate($request));

            return $response->withStatus(StatusCodeInterface::STATUS_OK);
        } catch (Throwable $e) {
            throw new HttpInternalServerErrorException($request, null, $e);
        }
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getTemplate(Request $request): string
    {
        if (!file_exists($this->template)) {
            return '';
        }

        $spec = $this->getParsedSpec($request);

        if (empty($spec)) {
            return '';
        }

        $template = file_get_contents($this->template);
        $spec = json_encode($spec, self::JSON_OPTIONS);

        return str_replace('{{spec}}', $spec, $template);
    }
}
