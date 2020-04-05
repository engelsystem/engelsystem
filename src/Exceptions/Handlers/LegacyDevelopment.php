<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

class LegacyDevelopment extends Legacy
{
    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e)
    {
        $file = $this->stripBasePath($e->getFile());

        echo sprintf(
            '<pre style="%s">',
            'background-color:#333;color:#ccc;z-index:1000;position:fixed;'
            . 'bottom:1em;padding:1em;width:97%;max-height:90%;overflow-y:auto;'
        );
        echo sprintf('%s: (%s)' . PHP_EOL, get_class($e), $e->getCode());
        $data = [
            'string'     => $e->getMessage(),
            'file'       => $file . ':' . $e->getLine(),
            'stacktrace' => $this->formatStackTrace($e->getTrace()),
        ];

        ob_start(function (string $buffer) {
            return htmlspecialchars($buffer);
        });
        var_dump($data);
        ob_end_flush();

        echo '</pre>';
    }

    /**
     * @param array $stackTrace
     * @return array
     */
    protected function formatStackTrace($stackTrace)
    {
        $return = [];
        $stackTrace = array_reverse($stackTrace);

        foreach ($stackTrace as $trace) {
            $path = '';
            $line = '';

            if (isset($trace['file']) && isset($trace['line'])) {
                $path = $this->stripBasePath($trace['file']);
                $line = $trace['line'];
            }

            $functionName = $trace['function'];

            $args = [];
            foreach (($trace['args'] ?? []) as $arg) {
                // @codeCoverageIgnoreStart
                switch (gettype($arg)) {
                    case 'string':
                    case 'integer':
                    case 'double':
                        $args[] = $arg;
                        break;
                    case 'boolean':
                        $args[] = $arg ? 'true' : 'false';
                        break;
                    case 'object':
                        $args[] = get_class($arg);
                        break;
                    case 'resource':
                        $args[] = get_resource_type($arg);
                        break;
                    default:
                        $args[] = gettype($arg);
                    // @codeCoverageIgnoreEnd
                }
            }

            $return[] = [
                'file'        => $path . ':' . $line,
                $functionName => $args ?? null,
            ];
        }

        return $return;
    }
}
