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

        echo '<pre style="background-color:#333;color:#ccc;z-index:1000;position:fixed;bottom:1em;padding:1em;width:97%;max-height: 90%;overflow-y:auto;">';
        echo sprintf('%s: (%s)' . PHP_EOL, get_class($e), $e->getCode());
        $data = [
            'string'     => $e->getMessage(),
            'file'       => $file . ':' . $e->getLine(),
            'stacktrace' => $this->formatStackTrace($e->getTrace()),
        ];
        var_dump($data);
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

            $return[] = [
                'file'        => $path . ':' . $line,
                $functionName => $trace['args'] ?? null,
            ];
        }

        return $return;
    }
}
