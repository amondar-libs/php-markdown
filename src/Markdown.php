<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

use ArgumentCountError;
use Closure;

/**
 * Class Markdown
 */
class Markdown implements MarkdownContract
{
    /**
     * New line identifier.
     */
    public static string $NL = PHP_EOL;

    /**
     * Tabulation identifier.
     */
    public static string $TAB = '   ';

    /**
     * @var array<int, mixed>
     */
    protected array $data = [];

    /**
     * Converts the object to its string representation.
     *
     * @return string The string representation of the object.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Creates and returns a new instance of the calling class.
     *
     * @return static A new instance of the calling class.
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Adds a Markdown heading to the object with the specified text and heading type.
     *
     * @param  string|null  $text  The text content for the heading.
     * @param  MarkdownHeading  $headingType  The heading level, such as H1, H2, etc. Defaults to H1.
     * @return static The current instance with the heading added.
     */
    public function heading(?string $text, MarkdownHeading $headingType = MarkdownHeading::H1): static
    {
        if ( ! empty($text)) {
            $this->data[] = [
                'type'   => MarkdownType::HEADER,
                'prefix' => $headingType->value,
                'text'   => $text,
            ];
        }

        return $this;
    }

    /**
     * Adds a line(paragraph) of text with an optional prefix to the internal data structure.
     *
     * @note Allow entering empty lines
     *
     * @param  string|null  $text  The text content to be added.
     * @param  string  $prefix  An optional prefix to prepend to the text.
     * @return static Returns the current instance for method chaining.
     */
    public function line(?string $text, string $prefix = ''): static
    {
        if ( ! empty($text)) {
            $type = MarkdownType::PARAGRAPH;

            $this->data[] = compact('type', 'text', 'prefix');
        }

        return $this;
    }

    /**
     * Processes a numeric list based on the provided tree structure.
     *
     * @param  array|null  $tree  The tree structure representing the numeric list.
     * @return static Returns the current instance for method chaining.
     */
    public function numericList(?array $tree): static
    {
        if ( ! empty($tree)) {
            $type = MarkdownType::NUMERIC_LIST;

            $this->data[] = compact('type', 'tree');
        }

        return $this;
    }

    /**
     * Creates a bulleted list from the provided tree structure.
     *
     * @param  array|null  $tree  An array representing the structure of the list.
     * @return static Returns the current instance for method chaining.
     */
    public function list(?array $tree): static
    {
        if ( ! empty($tree)) {
            $type = MarkdownType::LIST;

            $this->data[] = compact('type', 'tree');
        }

        return $this;
    }

    /**
     * Adds a quoted text or list of quotes to the current instance.
     *
     * @param  string|array|null  $list  The text or an array of texts to be quoted.
     * @return static The current instance with the quote(s) added.
     */
    public function quote(string|array|null $list): static
    {
        if ( ! empty($list)) {
            $list = ! is_array($list) ? [$list] : $list;
            $type = MarkdownType::QUOTE;

            $this->data[] = compact('type', 'list');
        }

        return $this;
    }

    /**
     * Adds a block of code with the specified language to the current data.
     *
     * @param  string|null  $code  The string of code to be added.
     * @param  string  $lang  The programming language of the code block.
     * @return static Returns the current instance for method chaining.
     */
    public function block(?string $code, string $lang = ''): static
    {
        if ( ! empty($code)) {
            $type = MarkdownType::CODE;

            $this->data[] = compact('type', 'code', 'lang');
        }

        return $this;
    }

    /**
     * Creates a link with the specified URL and an optional name.
     *
     * @param  string|null  $url  The URL for the link.
     * @param  string|null  $name  An optional name for the link. Defaults to null if not provided.
     * @return static Returns the current instance.
     */
    public function link(?string $url, ?string $name = null): static
    {
        if ( ! empty($url)) {
            $type = MarkdownType::LINK;

            $this->data[] = compact('type', 'url', 'name');
        }

        return $this;
    }

    /**
     * Adds an image to the Markdown content with optional alt and title attributes.
     *
     * @param  string|null  $url  The URL of the image.
     * @param  string|null  $title  Optional title for the image.
     * @param  string|null  $alt  Optional alt text for the image.
     */
    public function image(?string $url, ?string $title = null, ?string $alt = null): static
    {
        if ( ! empty($url)) {
            $type = MarkdownType::IMAGE;

            $this->data[] = compact('type', 'url', 'title', 'alt');
        }

        return $this;
    }

    /**
     * Sets the raw Markdown content.
     *
     * @param  string|null  $raw  The raw Markdown string to be processed.
     * @return static Returns the current instance for method chaining.
     */
    public function raw(?string $raw): static
    {
        if ( ! empty($raw)) {
            $type = MarkdownType::RAW;

            $this->data[] = compact('type', 'raw');
        }

        return $this;
    }

    /**
     * Converts the current object's data into its Markdown string representation.
     *
     * @return string A Markdown-formatted string constructed from the object's data.
     */
    public function toString(): string
    {
        $nl = static::$NL;
        $finalMarkdown = '';

        foreach ($this->data as $item) {
            $finalMarkdown .= match ($item['type']) {
                MarkdownType::HEADER       => "{$item['prefix']} {$item['text']}$nl$nl",
                MarkdownType::PARAGRAPH    => "{$item['prefix']}{$item['text']}$nl$nl",
                MarkdownType::NUMERIC_LIST => $this->renderList($item['tree'], $nl, true),
                MarkdownType::LIST         => $this->renderList($item['tree'], $nl),
                MarkdownType::QUOTE        => implode('', array_map(fn($item) => "> $item$nl", $item['list'])) . $nl,
                MarkdownType::CODE         => "```{$item['lang']}$nl{$item['code']}$nl```$nl$nl",
                MarkdownType::LINK         => '[' . ($item['name'] ?? $item['url']) . "]({$item['url']})$nl$nl",
                MarkdownType::IMAGE        => '![' . ($item['title'] ?? '') . "]({$item['url']}" . ($item['alt'] ? " \"{$item['alt']}\"" : '') . ")$nl$nl",
                MarkdownType::RAW          => "{$item['raw']}$nl$nl",
            };
        }

        return mb_trim($finalMarkdown, "$nl\t ");
    }

    /**
     * Renders a hierarchical list as a string.
     *
     * @param  array  $tree  The hierarchical array structure representing the list.
     * @param  string  $nl  The newline character(s) to use in the rendered output.
     * @param  bool  $isOrdered  Indicates whether the list should be ordered (numerical) or unordered.
     * @return string The rendered list as a formatted string.
     */
    protected function renderList(array $tree, string $nl, bool $isOrdered = false): string
    {
        $index = 1;
        $tab = static::$TAB;

        return implode(
            '',
            $this->mapWithKeys($tree, function ($items, $key) use ($nl, &$index, $isOrdered, $tab) {
                if (is_array($items)) {
                    $docs = array_shift($items);

                    $result = sprintf(
                        "%s %s%s$nl",
                        $isOrdered ? $index . '.' : '-',
                        is_string($key) ? $key : '',
                        ! empty($docs) ? " - $docs" : '',
                    ) . implode('', array_map(fn($item) => "$tab- $item$nl", $items));
                } elseif (is_string($key)) {
                    $result = sprintf(
                        "%s %s - %s$nl",
                        $isOrdered ? $index . '.' : '-',
                        $key,
                        $items
                    );
                } else {
                    $result = sprintf(
                        "%s %s$nl",
                        $isOrdered ? $index . '.' : '-',
                        $items
                    );
                }

                $index++;

                return $result;
            })
        ) . $nl;
    }

    /**
     * Maps the given array using the provided callback and preserves the keys of the original array.
     *
     * @param  array  $array  The input array to be mapped.
     * @param  Closure  $callback  The callback function to apply to each item in the array. The callback can accept the value and optionally the key as arguments.
     * @return array The resulting array with mapped values and preserved keys.
     */
    protected function mapWithKeys(array $array, Closure $callback): array
    {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }
}
