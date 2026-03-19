<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

/**
 * Class Escaper
 *
 * @author Amondar-SO
 */
readonly class Escaper
{
    protected array $normalizedChars;

    public function __construct(public array $characters)
    {
        $map = [];

        foreach ($this->characters as $char) {
            $map[ $char ] = '\\' . $char;
        }

        $this->normalizedChars = $map;
    }

    public static function make(array $characters): Escaper
    {
        return new Escaper($characters);
    }

    public static function makeForV2(array $extends = []): Escaper
    {
        $defaultCharacters = [
            '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!', '\\',
        ];

        return new Escaper(array_merge($defaultCharacters, $extends));
    }

    public function applyTo(string|array $line, array $bindings = []): string|array
    {
        if ($bindings === []) {
            return $this->escape($line);
        }

        $normalizedBindings = $this->escape($bindings);
        $default = $this->applyTo('{{?}}');

        return $this->replaceBindingsRecursive($line, $normalizedBindings, $default);
    }

    protected function replaceBindingsRecursive(string|array $subject, array $bindings, string $default): string|array
    {
        $index = 0;

        if (is_string($subject)) {
            return preg_replace_callback(
                '/{{\?}}/',
                static function () use ($bindings, &$index, $default): string {
                    return (string) ($bindings[$index++] ?? $default);
                },
                $subject
            );
        }


        return array_map(function ($item) use ($bindings, &$index, $default): string|array {
            return $this->replaceBindingsRecursive($item, $bindings[ $index++ ], $default);
        }, $subject);
    }

    protected function escape(array|string $line): string|array
    {
        if (is_string($line)) {
            return strtr($line, $this->normalizedChars);
        }

        $result = [];

        foreach ($line as $key => $item) {
            $item = is_string($item) || is_array($item) ? $item : (string) $item;

            if (is_string($key)) {
                $result[ $this->escape($key) ] = $this->escape($item);
            } else {
                $result[] = $this->escape($item);
            }
        }

        return $result;
    }
}
