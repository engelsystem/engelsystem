<?php

declare(strict_types=1);

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

class LegacyDevelopment extends Legacy
{
    public function render(Request $request, Throwable $e): void
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

    protected function formatStackTrace(array $stackTrace): array
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
                $args[] = $this->getDisplayNameOfValue($arg);
            }

            $return[] = [
                'file'        => $path . ':' . $line,
                $functionName => $args,
            ];
        }

        return $return;
    }

    private function getDisplayNameOfValue(mixed $arg): string
    {
        return match (gettype($arg)) {
            'string', 'integer', 'double' => (string) $arg,
            'boolean'  => $arg ? 'true' : 'false',
            'object'   => get_class($arg),
            'resource' => get_resource_type($arg), // @codeCoverageIgnore
            default    => gettype($arg),
        };
    }
}
