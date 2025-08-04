<?php

namespace KammiApiClient;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use ZephirV2\Profiler\Profiler;

class LoggingMiddleware
{
    private Profiler $profiler;
    private array $apiCalls = [];

    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;

        register_shutdown_function([$this, 'onShutdown']);
    }

    public function __invoke($handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            $start = microtime(true);

            $log = [
                'timestamp' => (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM),
                'request'   => $this->requestData($request),
            ];

            $body = $request->getBody();
            if ($body->isSeekable()) $body->rewind();
            $log['request']['body'] = $body->getContents();
            $log['request']['jsonBody'] = json_encode(Query::parse($log['request']['body']), true);

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use (&$log, $start) {
                    $log['duration_ms'] = (int) round((microtime(true) - $start) * 1000);
                    $log['response']    = $this->responseData($response);
                    $this->apiCalls[] = $log;
                    return $response;
                },
                function ($reason) use (&$log, $start) {
                    $log['duration_ms'] = (int) round((microtime(true) - $start) * 1000);
                    $log['error']       = $this->errorMessage($reason);

                    if ($reason instanceof RequestException && $reason->hasResponse()) {
                        $log['response'] = $this->responseData($reason->getResponse());
                    }

                    $this->apiCalls[] = $log;
                    throw $reason;
                }
            );
        };
    }

    private function requestData(RequestInterface $req): array
    {
        return [
            'method'           => $req->getMethod(),
            'uri'              => (string) $req->getUri(),
            'headers'          => $this->normalizeHeaders($req->getHeaders()),
            'protocol_version' => $req->getProtocolVersion(),
            'body'             => null,
        ];
    }

    private function responseData(ResponseInterface $res): array
    {
        $body = $res->getBody();
        $content = $body->isReadable() ? $body->getContents() : '';
        if ($body->isSeekable()) $body->rewind();

        return [
            'status'           => $res->getStatusCode(),
            'reason'           => $res->getReasonPhrase(),
            'headers'          => $this->normalizeHeaders($res->getHeaders()),
            'protocol_version' => $res->getProtocolVersion(),
            'body'             => $content,
        ];
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn(array $v) => implode(', ', $v), $headers);
    }

    private function errorMessage($e): string
    {
        if ($e instanceof RequestException) {
            $msg = sprintf(
                '[RequestException] %s for %s %s',
                $e->getMessage(),
                $e->getRequest()->getMethod(),
                (string) $e->getRequest()->getUri()
            );

            if ($e->hasResponse()) {
                $msg .= sprintf(
                    ' – Response: %d %s',
                    $e->getResponse()->getStatusCode(),
                    $e->getResponse()->getReasonPhrase()
                );
            }

            if ($err = ($e->getHandlerContext()['error'] ?? null)) {
                $msg .= ' – ' . $err;
            }
            return $msg;
        }

        if ($e instanceof \Throwable) {
            return sprintf('[%s] %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        }

        return 'Unknown error';
    }

    private function decodeJson(string $s)
    {
        $decoded = json_decode($s);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $s;
    }

    public function onShutdown()
    {
        $this->profiler->saveCollectorData('api_calls', $this->apiCalls);
    }
}
