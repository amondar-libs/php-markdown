<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

use Closure;

/**
 * Class Markdown
 */
class Markdown implements MarkdownContract
{
    /**
     * @var array<int, mixed>
     */
    protected array $data = [];

    /**
     * Constructor method for initializing the object with data and configurations.
     *
     * @param  string  $nl  The newline character(s) to be used.
     * @param  string  $tab  The tab character(s) to be used.
     * @param  bool  $suppressed  Determines whether the Markdown output has been suppressed without additional breaks
     *                            after each line.
     */
    public function __construct(
        private readonly string $nl = PHP_EOL,
        private readonly string $tab = '   ',
        private readonly bool $suppressed = false,
    ) {}

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
     * Creates a new instance of the class with the given settings.
     *
     * @param  string  $nl  The newline character(s) to be used.
     * @param  string  $tab  The tab character(s) to be used.
     * @param  bool  $suppressed  Determines whether the Markdown output has been suppressed without additional breaks
     *                            after each line.
     */
    public static function make(string $nl = PHP_EOL, string $tab = '   ', bool $suppressed = false): static
    {
        return new static($nl, $tab, $suppressed);
    }

    /**
     * Determines if the Markdown is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determines if the Markdown is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Starts suppressing content by adding a suppression marker to the internal data.
     */
    public function startSuppressing(): static
    {
        return static::make($this->nl, $this->tab, suppressed: true)->raw($this);
    }

    /**
     * Ends the suppressing Markdown operation and appends the corresponding data.
     */
    public function endSuppressing(): static
    {
        return static::make($this->nl, $this->tab)->raw($this);
    }

    /**
     * Executes the given callback while suppressing specific behavior or operations.
     *
     * @param  callable($this): static  $callback  The callback to be executed while suppression is active.
     *                                             It receives the current instance as its parameter.
     */
    public function suppress(callable $callback): static
    {
        return $callback($this->startSuppressing())->endSuppressing();
    }

    /**
     * Adds a Markdown heading to the object with the specified text and heading type.
     *
     * @param  string|null  $text  The text content for the heading.
     * @param  MarkdownHeading  $headingType  The heading level, such as H1, H2, etc. Defaults to H1.
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
     */
    public function quote(string|array|null $list): static
    {
        if ($list !== null) {
            $list = ! is_array($list) ? [ $list ] : $list;
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
     * @param  string|static|null  $raw  The raw Markdown string to be processed.
     * @return static Returns the current instance for method chaining.
     */
    public function raw(string|Markdown|null $raw): static
    {
        if (
            is_string($raw) || (
                $raw instanceof Markdown
                && $raw->isNotEmpty()
            )
        ) {
            $this->data[] = [
                'type' => MarkdownType::RAW,
                'raw'  => $raw,
            ];
        }

        return $this;
    }

    /**
     * Adds a break element to the data collection.
     */
    public function break(): static
    {
        $this->data[] = [
            'type' => MarkdownType::BREAK,
        ];

        return $this;
    }

    /**
     * Executes a callback if the given condition is met.
     * The condition can be a boolean or a callable that evaluates to a boolean.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param  (Closure($this): TWhenParameter)|TWhenParameter|null  $condition
     * @param  (callable($this, TWhenParameter): TWhenReturnType)  $callback
     */
    public function when($condition, callable $callback): static
    {
        if (is_callable($condition)) {
            $condition = $condition($this);
        }

        return $condition ? $callback($this, $condition) : $this;
    }

    /**
     * Converts the current object's data into its Markdown string representation.
     */
    public function toString(): string
    {
        $nl = $this->nl;
        $out = [];

        foreach ($this->data as $item) {
            switch ($item[ 'type' ]) {
                case MarkdownType::HEADER:
                    $out[] = $item[ 'prefix' ] . ' ' . $item[ 'text' ] . $nl . $this->getLineEnd();
                    break;

                case MarkdownType::PARAGRAPH:
                    $out[] = $item[ 'prefix' ] . $item[ 'text' ] . $nl . $this->getLineEnd();
                    break;

                case MarkdownType::NUMERIC_LIST:
                    $out[] = $this->renderList($item[ 'tree' ], $nl, true) . $this->getLineEnd();
                    break;

                case MarkdownType::LIST:
                    $out[] = $this->renderList($item[ 'tree' ], $nl) . $this->getLineEnd();
                    break;

                case MarkdownType::QUOTE:
                    $quoted = [];

                    foreach ($item[ 'list' ] as $line) {
                        $quoted[] = '> ' . $line;
                    }

                    $out[] = implode($nl, $quoted) . $nl . $this->getLineEnd();
                    break;

                case MarkdownType::CODE:
                    $out[] = '```' . $item[ 'lang' ] . $nl
                             . $item[ 'code' ] . $nl
                             . '```' . $nl . $this->getLineEnd();
                    break;

                case MarkdownType::LINK:
                    $out[] = '[' . ($item[ 'name' ] ?? $item[ 'url' ]) . '](' . $item[ 'url' ] . ')' . $nl .
                             $this->getLineEnd();
                    break;

                case MarkdownType::IMAGE:
                    $out[] = '![' . ($item[ 'title' ] ?? '') . '](' . $item[ 'url' ]
                             . ( ! empty($item[ 'alt' ]) ? ' "' . $item[ 'alt' ] . '"' : '') . ')' . $nl .
                             $this->getLineEnd();
                    break;

                case MarkdownType::RAW:
                    $out[] = $item[ 'raw' ] . $nl . $this->getLineEnd();
                    break;

                case MarkdownType::BREAK:
                    $out[] = $nl;
                    break;
            }
        }

        return mb_rtrim(implode('', $out), "$nl\t ");
    }

    /**
     * Retrieves the appropriate line ending based on the suppressed state.
     */
    protected function getLineEnd(): string
    {
        return $this->suppressed ? '' : $this->nl;
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
        $tab = $this->tab;
        $i = 1;
        $out = [];

        foreach ($tree as $key => $items) {
            if (is_array($items)) {
                $docs = $items[ 0 ] ?? '';
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
                        $children[] = $tab . '- ' . $items[ $j ];
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
