<?php


namespace Pars\Core\Logging;


use Laminas\Log\Formatter\Simple;

class LogFormatter extends Simple
{
    protected function normalize($value)
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }

        // better readable JSON
        static $jsonFlags;
        if ($jsonFlags === null) {
            $jsonFlags = 0;
            $jsonFlags |= defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0;
            $jsonFlags |= defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
            $jsonFlags |= defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
        }

        // Error suppression is used in several of these cases as a fix for each of
        // #5383 and #4616. Without it, #4616 fails whenever recursion occurs during
        // json_encode() operations; usage of a dedicated error handler callback
        // causes #5383 to fail when the Logger is being used as an error handler.
        // The only viable solution here is error suppression, ugly as it may be.
        if ($value instanceof \DateTime) {
            $value = $value->format($this->getDateTimeFormat());
        } elseif ($value instanceof \Traversable) {
            $value = @json_encode(iterator_to_array($value), $jsonFlags);
        } elseif (is_array($value)) {
            $value = @json_encode($value, $jsonFlags);
        } elseif (is_object($value) && ! method_exists($value, '__toString')) {
            $value = sprintf('object(%s) %s', get_class($value), @json_encode($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        } elseif (! is_object($value)) {
            $value = gettype($value);
        }

        return (string) $value;
    }


}
