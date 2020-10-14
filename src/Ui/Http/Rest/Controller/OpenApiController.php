<?php

declare(strict_types=1);

namespace App\Ui\Http\Rest\Controller;

use ArrayObject;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser as YamlParser;

use Throwable;

use function dirname;
use function file_exists;
use function file_get_contents;
use function json_encode;

class OpenApiController
{
    protected const JSON_OPTIONS = JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT;

    /**
     * @var YamlParser
     */
    protected YamlParser $yamlParser;

    /**
     * @var YamlDumper
     */
    protected YamlDumper $yamlDumper;

    /**
     * @var string
     */
    protected string $openApiFile;

    /**
     * OpenApiController constructor.
     * @param YamlParser $yamlParser
     * @param YamlDumper $yamlDumper
     * @param string $openApiFile
     */
    public function __construct(YamlParser $yamlParser, YamlDumper $yamlDumper, string $openApiFile)
    {
        $this->yamlParser = $yamlParser;
        $this->yamlDumper = $yamlDumper;
        $this->openApiFile = $openApiFile;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     */
    public function getOpenApiJson(Request $request, Response $response): Response
    {
        try {
            $content = $this->getParsedSpec($request);

            if (empty($content)) {
                throw new Exception('Spec is not valid.');
            }

            return $this->json($response, $content);
        } catch (Throwable $e) {
            throw new HttpNotFoundException($request, null, $e);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     */
    public function getOpenApiYaml(Request $request, Response $response): Response
    {
        try {
            $content = $this->getParsedSpec($request);

            if (empty($content)) {
                throw new Exception('Spec is not valid.');
            }

            $response->getBody()->write($this->yamlDumper->dump($content));

            return $response
                ->withStatus(StatusCodeInterface::STATUS_OK)
                ->withHeader('Content-Type', 'application/yaml');
        } catch (Throwable $e) {
            throw new HttpNotFoundException($request, null, $e);
        }
    }

    /**
     * @return string
     */
    protected function getSpec(): string
    {
        if (!file_exists($this->openApiFile)) {
            return '';
        }

        return file_get_contents($this->openApiFile);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getParsedSpec(Request $request): array
    {
        $content = $this->yamlParser->parse($this->getSpec());

        if (empty($content)) {
            return [];
        }

        $content['servers'][0]['url'] = dirname((string)$request->getUri());

        return $content;
    }

    /**
     * @param Response $response
     * @param null $data
     * @param int $status
     * @param bool $json
     * @return Response
     * @throws JsonException
     */
    protected function json(Response $response, $data = null, int $status = 200, bool $json = false): Response
    {
        if (null === $data) {
            $data = new ArrayObject();
            $json = false;
        }

        if (false === $json) {
            $data = json_encode($data, self::JSON_OPTIONS);
        }

        $response->getBody()->write($data);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
