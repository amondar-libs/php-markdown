# PHP Markdown Builder

Zero-dependency, fluent Markdown builder for PHP 8.2+.

- Tiny API, no runtime dependencies
- Fluent chaining to compose documents
- Supports headings, paragraphs, ordered and unordered lists (with simple nesting), quotes, fenced code blocks, links,
  images, and raw markdown
- Customizable newline and indentation characters
- Around 9-10ms to render 10k markdown lines

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

### Tables

```php
$result = Markdown::make()->table(
        ['Header 1', 'Header 2', 'Header 3'],
        [
            ['Value 1', 'Value 2', 'Value 3'],
            ['Value 4', 'Value 5', 'Value 6'],
        ],
    )->toString();

/*
| Header 1 | Header 2 | Header 3 |
| --- | --- | --- |
| Value 1 | Value 2 | Value 3 |
| Value 4 | Value 5 | Value 6 |
*/
```

### Raw markdown passthrough

```php
Markdown::make()->raw('#### Raw content')->toString();
// "#### Raw content"
```

> You can also put a `Markdown` class instance as "raw" content.

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

You can also suppress Markdown output to stop automatic adding additional new line after each "block":

```php
$result = Markdown::make()
        ->startSuppressing()
        ->heading('Title')
        ->line('Content')
        ->break()
        ->link('https://example.com', 'Example')
        ->endSuppressing()
        ->heading('Title 2')
        ->line('Content 2')
        ->toString();

/*
# Title
Content

[Example](https://example.com)

# Title 2

Content 2
*/

//This usage is the same as above
$result = Markdown::make()
        ->suppress(
            fn($markdown) => $markdown->heading('Title')
                                      ->line('Content')
                                      ->break()
                                      ->link('https://example.com', 'Example')
        )
        ->heading('Title 2')
        ->line('Content 2')
        ->toString();
```

### Conditioning

You can apply class conditional extension:

```php
$result = Markdown::make()
        ->heading('Title')
        ->line('Content')
        ->link('https://example.com', 'Example')
        ->when(false, fn(Markdown $markdown) => $markdown->line('This line should not be added.'))
        ->toString();

/*
# Title
            
Content

[Example](https://example.com)
*/

$result = Markdown::make()
        ->heading('Title')
        ->line('Content')
        ->link('https://example.com', 'Example')
        ->when(true, fn(Markdown $markdown) => $markdown->line('This line should be added.'))
        ->toString();

/*
# Title
            
Content

[Example](https://example.com)

This line should be added.
*/

$result = Markdown::make()
        ->heading('Title')
        ->line('Content')
        ->link('https://example.com', 'Example')
        ->when(fn() => 'This line should be added too.', fn(Markdown $markdown) => $markdown->suppress(fn($m) => $m->break()->line('This line should not be added.')->line($line)))
        ->toString();

/*
# Title
            
Content

[Example](https://example.com)


This line should be added.
This line should be added too.
*/
```

### Markdown V2 autoescape

When you are using the package in Markdown V2 mode (for example in `Telegram` messages), you can use the autoescaping
feature.
Just pass an array of characters, that should be escaped in your text during Markdown building:

```php
// complex list with nested items
$complexList = [
    '**Category 1.**' => [
        'Description.',
        'Sub-item 1',
        'Sub-item 2',
    ],
    'Category 2' => [
        'Description',
        'Sub-item 1',
        'Sub-item 2.',
    ],
];

$result = Markdown::make(shouldEscape: ['.', '-'])->list($complexList)->toString();

/*
- **Category 1\.** - Description\.
   - Sub\-item 1
   - Sub\-item 2
- Category 2 - Description
   - Sub\-item 1
   - Sub\-item 2\.
*/
```

> As you can see, it is still possible to use Markdown syntax in your text, but all characters that are in the list will
> be escaped. If you want to use _Markdown_ syntax characters as _none-Markdown_, you should escape then by yourself.

## Customization

You can customize the builder by passing custom newline and indentation characters as well as suppressing automatic
newlines:

```php
use Amondar\Markdown\Markdown;

// force LF newlines
// 4-space indentation
Markdown::make(nl: "\n", tab: "    ", suppressed: false);
```

## Behavior and notes

- Null or empty values are ignored. For example, `->line(null)` or `->heading('')` produce no output.
- `toString()` trims trailing newlines and extra spaces from the final output.
- `__toString()` proxies to `toString()` so you can echo the builder directly: `echo Markdown::make()->line('Hi');`.
- List rendering supports a simple two-level pattern: a keyed item may have a description (first element) and optional
  sub-items which are rendered as indented bullets. That can be helpful in documentation generation.

## License

MIT License. See [LICENSE.md](LICENSE.md).
