<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

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
        if ($text !== null) {
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
        if ($text !== null) {
            $this->data[] = [
                'type'   => MarkdownType::PARAGRAPH,
                'text'   => $text,
                'prefix' => $prefix,
            ];
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
        if ($tree !== null) {
            $this->data[] = [
                'type' => MarkdownType::NUMERIC_LIST,
                'tree' => $tree,
            ];
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
        if ($tree !== null) {
            $this->data[] = [
                'type' => MarkdownType::LIST,
                'tree' => $tree,
            ];
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
        if ($list !== null) {
            $list = ! is_array($list) ? [$list] : $list;
            $this->data[] = [
                'type' => MarkdownType::QUOTE,
                'list' => $list,
            ];
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
        if ($code !== null) {
            $this->data[] = [
                'type' => MarkdownType::CODE,
                'code' => $code,
                'lang' => $lang,
            ];
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
        if ($url !== null) {
            $this->data[] = [
                'type' => MarkdownType::LINK,
                'url'  => $url,
                'name' => $name,
            ];
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
        if ($url !== null) {
            $this->data[] = [
                'type'  => MarkdownType::IMAGE,
                'url'   => $url,
                'title' => $title,
                'alt'   => $alt,
            ];
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
        if ($raw !== null) {
            $this->data[] = [
                'type' => MarkdownType::RAW,
                'raw'  => $raw,
            ];
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
        $out = [];

        foreach ($this->data as $item) {
            switch ($item['type']) {
                case MarkdownType::HEADER:
                    $out[] = $item['prefix'] . ' ' . $item['text'] . $nl;
                    break;

                case MarkdownType::PARAGRAPH:
                    $out[] = $item['prefix'] . $item['text'] . $nl;
                    break;

                case MarkdownType::NUMERIC_LIST:
                    $out[] = $this->renderList($item['tree'], $nl, true);
                    break;

                case MarkdownType::LIST:
                    $out[] = $this->renderList($item['tree'], $nl);
                    break;

                case MarkdownType::QUOTE:
                    $quoted = [];

                    foreach ($item['list'] as $line) {
                        $quoted[] = '> ' . $line;
                    }

                    $out[] = implode($nl, $quoted) . $nl;
                    break;

                case MarkdownType::CODE:
                    $out[] = '```' . $item['lang'] . $nl
                             . $item['code'] . $nl
                             . '```' . $nl;
                    break;

                case MarkdownType::LINK:
                    $out[] = '[' . ($item['name'] ?? $item['url']) . '](' . $item['url'] . ')' . $nl;
                    break;

                case MarkdownType::IMAGE:
                    $out[] = '![' . ($item['title'] ?? '') . '](' . $item['url']
                             . ( ! empty($item['alt']) ? ' "' . $item['alt'] . '"' : '') . ')' . $nl;
                    break;

                case MarkdownType::RAW:
                    $out[] = $item['raw'] . $nl;
                    break;
            }
        }

        return mb_rtrim(implode($nl, $out), "$nl\t ");
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
        $tab = static::$TAB;
        $i = 1;
        $out = [];

        foreach ($tree as $key => $items) {
            if (is_array($items)) {
                $docs = $items[0] ?? '';
                $line = ($isOrdered ? ($i . '.') : '-')
                        . (is_string($key) ? (' ' . $key) : '')
                        . ($docs !== '' ? (' - ' . $docs) : '')
                        . $nl;

                // Check that there are more than one item in the array.
                // Use count cache and an array of children to avoid string reallocations.
                $n = count($items);
                if ($n > 1) {
                    $children = [];

                    for ($j = 1; $j < $n; $j++) {
                        $children[] = $tab . '- ' . $items[$j];
                    }

                    $line .= implode($nl, $children) . $nl;
                }

                $out[] = $line;
            } elseif (is_string($key)) {
                $out[] = ($isOrdered ? ($i . '.') : '-') . ' ' . $key . ' - ' . $items . $nl;
            } else {
                $out[] = ($isOrdered ? ($i . '.') : '-') . ' ' . $items . $nl;
            }
            $i++;
        }

        return implode('', $out);
    }
}
