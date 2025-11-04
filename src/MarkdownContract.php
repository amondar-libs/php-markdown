<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

use Stringable;

/**
 * Interface MarkdownContract
 *
 * Defines the contract for markdown generation functionality.
 * Provides methods for creating various markdown elements like headings, lists, quotes, etc.
 */
interface MarkdownContract extends Stringable
{
    /**
     * Creates a new instance of the class with the given settings.
     *
     * @param  string  $nl  The newline character(s) to be used.
     * @param  string  $tab  The tab character(s) to be used.
     * @param  bool  $suppressed  Determines whether the Markdown output has been suppressed without additional breaks
     *                            after each line.
     * @param  array|null  $shouldEscape  Define an array of characters that should be escaped automatically in headers and paragraphs.
     * @return Markdown
     */
    public static function make(string $nl = PHP_EOL, string $tab = '   ', bool $suppressed = false, ?array $shouldEscape = null): static;

    /**
     * Determines if the Markdown is empty.
     */
    public function isEmpty(): bool;

    /**
     * Determines if the Markdown is not empty.
     */
    public function isNotEmpty(): bool;

    /**
     * Starts suppressing content by adding a suppression marker to the internal data.
     */
    public function startSuppressing(): static;

    /**
     * Ends the suppressing Markdown operation and appends the corresponding data.
     */
    public function endSuppressing(): static;

    /**
     * Executes the given callback while suppressing specific behavior or operations.
     *
     * @param  callable($this): static  $callback  The callback to be executed while suppression is active.
     *                                             It receives the current instance as its parameter.
     */
    public function suppress(callable $callback): static;

    /**
     * Adds a Markdown heading to the object with the specified text and heading type.
     *
     * @param  string|null  $text  The text content for the heading.
     * @param  MarkdownHeading  $headingType  The heading level, such as H1, H2, etc. Defaults to H1.
     */
    public function heading(?string $text, MarkdownHeading $headingType = MarkdownHeading::H1): static;

    /**
     * Adds a line(paragraph) of text with an optional prefix to the internal data structure.
     *
     * @note Allow entering empty lines
     *
     * @param  string|null  $text  The text content to be added.
     * @param  string  $prefix  An optional prefix to prepend to the text.
     */
    public function line(?string $text, string $prefix = ''): static;

    /**
     * Appends a paragraph to the current instance, optionally with a prefix.
     *
     * @param  string|null  $text  The text of the paragraph. Pass null for no text.
     * @param  string  $prefix  An optional prefix to prepend to the paragraph.
     */
    public function paragraph(?string $text, string $prefix = ''): static;

    /**
     * Processes a numeric list based on the provided tree structure.
     *
     * @param  array|null  $tree  The tree structure representing the numeric list.
     */
    public function numericList(?array $tree): static;

    /**
     * Creates a bulleted list from the provided tree structure.
     *
     * @param  array|null  $tree  An array representing the structure of the list.
     */
    public function list(?array $tree): static;

    /**
     * Adds a quoted text or list of quotes to the current instance.
     *
     * @param  string|array|null  $list  The text or an array of texts to be quoted.
     */
    public function quote(string|array|null $list): static;

    /**
     * Adds a block of code with the specified language to the current data.
     *
     * @param  string|null  $code  The string of code to be added.
     * @param  string  $lang  The programming language of the code block.
     */
    public function block(?string $code, string $lang = ''): static;

    /**
     * Creates a link with the specified URL and an optional name.
     *
     * @param  string|null  $url  The URL for the link.
     * @param  string|null  $name  An optional name for the link. Defaults to null if not provided.
     */
    public function link(?string $url, ?string $name = null): static;

    /**
     * Adds an image to the Markdown content with optional alt and title attributes.
     *
     * @param  string|null  $url  The URL of the image.
     * @param  string|null  $title  Optional title for the image.
     * @param  string|null  $alt  Optional alt text for the image.
     */
    public function image(?string $url, ?string $title = null, ?string $alt = null): static;

    /**
     * Sets the raw Markdown content.
     *
     * @param  string|null  $raw  The raw Markdown string to be processed.
     */
    public function raw(?string $raw): static;

    /**
     * Adds a break element to the data collection.
     */
    public function break(): static;

    /**
     * Adds a table structure to the data collection.
     *
     * @param  array  $headers  An array representing the table's header row.
     * @param  array  $rows  A multidimensional array containing the table's rows.
     */
    public function table(array $headers, array $rows): static;

    /**
     * Executes a callback if the given condition is met.
     * The condition can be a boolean or a callable that evaluates to a boolean.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param  (Closure($this): TWhenParameter)|TWhenParameter|null  $condition
     * @param  (callable($this, TWhenParameter): TWhenReturnType)|null  $callback
     */
    public function when($condition = null, ?callable $callback = null): static;

    /**
     * Converts the object to its string representation.
     *
     * @return string A string representation of the object.
     */
    public function toString(): string;
}
