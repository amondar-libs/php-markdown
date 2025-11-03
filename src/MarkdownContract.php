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
     * Creates and returns a new instance of the calling class.
     *
     * @return static A new instance of the calling class.
     */
    public static function make(): static;

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
     * Executes a callback if the given condition is met.
     * The condition can be a boolean or a callable that evaluates to a boolean.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param  (Closure($this): TWhenParameter)|TWhenParameter|null  $condition
     * @param  (callable($this, TWhenParameter): TWhenReturnType)  $callback
     */
    public function when($condition, callable $callback): static;

    /**
     * Converts the object to its string representation.
     *
     * @return string A string representation of the object.
     */
    public function toString(): string;
}
