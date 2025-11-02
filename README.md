# PHP Markdown Builder

Zero-dependency, fluent Markdown builder for PHP 8.2+.

- Tiny API, no runtime dependencies
- Fluent chaining to compose documents
- Supports headings, paragraphs, ordered and unordered lists (with simple nesting), quotes, fenced code blocks, links, images, and raw markdown
- Customizable newline and indentation characters

## Installation

Install via Composer:

```bash
composer require amondar-libs/php-markdown
```

Requirements:
- PHP 8.2+

## Quick start

```php
use Amondar\Markdown\Markdown;
use Amondar\Markdown\MarkdownHeading;

$md = Markdown::make()
    ->heading('Hello World')
    ->line('This is a paragraph.')
    ->link('https://example.com', 'Example')
    ->toString();

// Result:
// # Hello World
//
// This is a paragraph.
//
// [Example](https://example.com)
```

## API and examples

Below, most examples mirror the test suite to ensure accuracy.

### Create an instance

```php
$md = Amondar\Markdown\Markdown::make();
```

You can convert to a string using either `toString()` or implicit casting via `__toString()`.

```php
(string) $md;      // calls __toString()
$md->toString();   // explicit
```

### Headings

```php
use Amondar\Markdown\Markdown;
use Amondar\Markdown\MarkdownHeading;

Markdown::make()->heading('Test Heading')->toString();
// "# Test Heading"

Markdown::make()->heading('Test Heading', MarkdownHeading::H2)->toString();
// "## Test Heading"

Markdown::make()->heading('Test Heading', MarkdownHeading::H3)->toString();
// "### Test Heading"
```

### Paragraph line (with optional prefix)

```php
Markdown::make()->line('Test Line')->toString();
// "Test Line"

Markdown::make()->line('Test Line', '> ')->toString();
// "> Test Line"
```

Notes:
- Empty strings and nulls are ignored by the builder (no output for that call).

### Ordered (numeric) lists

Simple list:

```php
$list = [
    'Item 1',
    'Item 2',
    'Item 3',
];

Markdown::make()->numericList($list)->toString();
/*
1. Item 1
2. Item 2
3. Item 3
*/
```

Keyed + value description (common pattern):

```php
$list = [
    '**Item 1**' => 'Description',
    'Item 2',
    'Item 3',
];

Markdown::make()->numericList($list)->toString();
/*
1. **Item 1** - Description
2. Item 2
3. Item 3
*/
```

Nested style (category with description + sub-items):

```php
$complex = [
    '**Category 1**' => [
        'Description',
        'Sub-item 1',
        'Sub-item 2',
    ],
    '**Category 2**' => [
        'Description',
        'Sub-item 1',
        'Sub-item 2',
    ],
];

Markdown::make()->numericList($complex)->toString();
/*
1. **Category 1** - Description
   - Sub-item 1
   - Sub-item 2
2. **Category 2** - Description
   - Sub-item 1
   - Sub-item 2
*/
```
> You can use `Description` as empty string or NULL to omit the description.

### Unordered (bullet) lists

Simple list:

```php
$list = [
    'Item 1',
    'Item 2',
    'Item 3',
];

Markdown::make()->list($list)->toString();
/*
- Item 1
- Item 2
- Item 3
*/
```

Keyed + value description:

```php
$list = [
    '**Item 1**' => 'Description',
    'Item 2',
    'Item 3',
];

Markdown::make()->list($list)->toString();
/*
- **Item 1** - Description
- Item 2
- Item 3
*/
```

Nested style:

```php
$complex = [
    '**Category 1**' => [
        'Description',
        'Sub-item 1',
        'Sub-item 2',
    ],
    'Category 2' => [
        'Description',
        'Sub-item 1',
        'Sub-item 2',
    ],
];

Markdown::make()->list($complex)->toString();
/*
- **Category 1** - Description
   - Sub-item 1
   - Sub-item 2
- Category 2 - Description
   - Sub-item 1
   - Sub-item 2
*/
```

> You can use `Description` as empty string or NULL to omit the description.

### Quotes

```php
Markdown::make()->quote('Test Quote')->toString();
// "> Test Quote"

Markdown::make()->quote([
    'Quote Line 1',
    'Quote Line 2',
    'Quote Line 3',
])->toString();
/*
> Quote Line 1
> Quote Line 2
> Quote Line 3
*/
```

### Fenced code blocks

```php
$code = <<<'CODE'
function test() {
    return true;
}
CODE;

Markdown::make()->block($code, 'php')->toString();

/*
    ```php
    function test() {
        return true;
    }
    ```
*/

Markdown::make()->block('SELECT * FROM users;', 'sql')->toString();

/*
    ```sql
    SELECT * FROM users;
    ```
*/
```

### Links

```php
Markdown::make()->link('https://example.com')->toString();
// "[https://example.com](https://example.com)"

Markdown::make()->link('https://example.com', 'Example')->toString();
// "[Example](https://example.com)"
```

### Images

```php
Markdown::make()->image('https://example.com/image.png')->toString();
// "![](https://example.com/image.png)"

Markdown::make()->image(
    'https://example.com/image.png',
    'Title Text',
    'Alt title'
)->toString();
// "![Title Text](https://example.com/image.png \"Alt title\")"
```

### Raw markdown passthrough

```php
Markdown::make()->raw('#### Raw content')->toString();
// "#### Raw content"
```

### Method chaining

```php
$result = Markdown::make()
    ->heading('Title')
    ->line('Content')
    ->link('https://example.com', 'Example')
    ->toString();

/*
# Title

Content

[Example](https://example.com)
*/
```

## Customization

The builder uses two public static properties to define formatting characters:

- `Markdown::$NL` — newline sequence used when composing multi-line output; defaults to `PHP_EOL`.
- `Markdown::$TAB` — indentation used for nested list items; defaults to three spaces `'   '`. 

You can override them globally before composing:

```php
use Amondar\Markdown\Markdown;

Markdown::$NL = "\n";       // force LF newlines
Markdown::$TAB = "    ";     // 4-space indentation
```

## Behavior and notes

- Null or empty values are ignored. For example, `->line(null)` or `->heading('')` produce no output.
- `toString()` trims trailing newlines and extra spaces from the final output.
- `__toString()` proxies to `toString()` so you can echo the builder directly: `echo Markdown::make()->line('Hi');`.
- List rendering supports a simple two-level pattern: a keyed item may have a description (first element) and optional sub-items which are rendered as indented bullets. That can be helpful in documentation generation.

## License

MIT License. See [LICENSE.md](LICENSE.md).
