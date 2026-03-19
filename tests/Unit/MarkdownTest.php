<?php

declare(strict_types = 1);

use Amondar\Markdown\Escaper;
use Amondar\Markdown\Markdown;
use Amondar\Markdown\MarkdownHeading;

// Heading
it('builds headings correctly', function () {
    // h1 heading
    $result = Markdown::make()->heading('Test Heading')->toString();
    expect($result)->toBe('# Test Heading');

    // h2 heading
    $result = Markdown::make()->heading('Test Heading', MarkdownHeading::H2)->toString();
    expect($result)->toBe('## Test Heading');

    // h3 heading
    $result = Markdown::make()->heading('Test Heading', MarkdownHeading::H3)->toString();
    expect($result)->toBe('### Test Heading');

    // With bindings
    $result = Markdown::make(escaper: Escaper::makeForV2())
        ->heading(
            'Some controlled text and {{?}} random part here',
            MarkdownHeading::H3,
            ['S_o*m|e(D)y-n.a{}m[]i+?$c string']
        )->toString();
    expect($result)->toBe('### Some controlled text and S\_o\*m\|e\(D\)y\-n\.a\{\}m\[\]i\+?$c string random part here');
})->group('heading');

// Line
it('builds lines with and without prefix', function () {
    // simple line
    $result = Markdown::make()->line('Test Line')->toString();
    expect($result)->toBe('Test Line');

    // line with prefix
    $result = Markdown::make()->line('Test Line', '> ')->toString();
    expect($result)->toBe('> Test Line');

    // line with bindings
    $result = Markdown::make(escaper: Escaper::makeForV2())
        ->line(
            'Test Line with bindings: {{?}}, {{?}}, {{?}}...',
            bindings: ['A_D', 'D_A']
        )->toString();
    expect($result)->toBe('Test Line with bindings: A\_D, D\_A, \{\{?\}\}...');
})->group('line');

// Numeric list
it('builds numeric lists (simple, common, and nested)', function () {

    // simple numeric list
    $list = [
        'Item 1',
        'Item 2',
        'Item 3',
    ];

    $result = Markdown::make()->numericList($list)->toString();

    expect($result)->toContain('1. Item 1')
        ->and($result)->toContain('2. Item 2')
        ->and($result)->toContain('3. Item 3')
        ->toBe(
            <<<'MARKDOWN'
            1. Item 1
            2. Item 2
            3. Item 3
            MARKDOWN
        );

    // common numeric list
    $list = [
        '**Item 1**' => 'Description',
        'Item 2',
        'Item 3',
    ];

    $result = Markdown::make()->numericList($list)->toString();

    expect($result)->toContain('1. **Item 1** - Description')
        ->and($result)->toContain('2. Item 2')
        ->and($result)->toContain('3. Item 3')
        ->toBe(
            <<<'MARKDOWN'
            1. **Item 1** - Description
            2. Item 2
            3. Item 3
            MARKDOWN
        );

    // complex numeric list with nested items
    $complexList = [
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

    $result = Markdown::make()->numericList($complexList)->toString();

    expect($result)->toContain('1. **Category 1** - Description')
        ->and($result)->toContain('   - Sub-item 1')
        ->and($result)->toContain('2. **Category 2** - Description')
        ->toBe(
            <<<'MARKDOWN'
            1. **Category 1** - Description
               - Sub-item 1
               - Sub-item 2
            2. **Category 2** - Description
               - Sub-item 1
               - Sub-item 2
            MARKDOWN
        );

    // complex numeric list with nested bindings
    $complexList = [
        '**Category 1**' => [
            'Description for {{?}}',
            'Sub-item {{?}} and {{?}}',
            'Sub-item {{?}}',
        ],
        '**Category 2**' => [
            'Description for {{?}} and {{?}}',
            'Sub-item {{?}}',
            'Sub-item {{?}}',
        ],
    ];

    $result = Markdown::make(escaper: Escaper::makeForV2())->numericList($complexList, [
        [
            ['Me'],
            [1],
            [2],
        ],
        [
            ['Me', 'You'],
            [2, 3],
            [4],
        ],
    ])->toString();

    expect($result)->toContain('1\. **Category 1** \- Description for Me')
        ->and($result)->toContain('   - Sub-item 1')
        ->and($result)->toContain('2\. **Category 2** \- Description for Me and You')
        ->toBe(
            <<<'MARKDOWN'
            1\. **Category 1** \- Description for Me
               - Sub-item 1 and \{\{?\}\}
               - Sub-item 2
            2\. **Category 2** \- Description for Me and You
               - Sub-item 2
               - Sub-item 4
            MARKDOWN
        );
})->group('numeric-list');

// Bullet list
it('builds bullet lists (simple, common, and nested)', closure: function () {

    // simple list
    $list = [
        'Item 1.',
        'Item 2.',
        'Item 3.',
    ];

    $result = Markdown::make(escaper: Escaper::make(['.', '-']))->list($list)->toString();

    expect($result)->toContain('- Item 1\.')
        ->and($result)->toContain('- Item 2\.')
        ->and($result)->toContain('- Item 3\.')
        ->toBe(
            <<<'MARKDOWN'
            - Item 1\.
            - Item 2\.
            - Item 3\.
            MARKDOWN
        );

    // common list
    $list = [
        '**Item 1.**' => 'Description.',
        'Item 2',
        'Item 3',
    ];

    $result = Markdown::make(escaper: Escaper::make(['.', '-']))->list($list)->toString();

    expect($result)->toContain('- **Item 1\.** \- Description\.')
        ->and($result)->toContain('- Item 2')
        ->and($result)->toContain('- Item 3')
        ->toBe(
            <<<'MARKDOWN'
            - **Item 1\.** \- Description\.
            - Item 2
            - Item 3
            MARKDOWN
        );

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

    $result = Markdown::make(escaper: Escaper::make(['.', '-']))->list($complexList)->toString();

    expect($result)->toContain('- **Category 1\.** \- Description\.')
        ->and($result)->toContain('   - Sub\-item 1')
        ->and($result)->toContain('- Category 2 \- Description')
        ->toBe(
            <<<'MARKDOWN'
            - **Category 1\.** \- Description\.
               - Sub\-item 1
               - Sub\-item 2
            - Category 2 \- Description
               - Sub\-item 1
               - Sub\-item 2\.
            MARKDOWN
        );
})->group('bullet-list');
//
// Quote
it('builds quotes (single and multiple lines)', function () {

    // single quote
    $result = Markdown::make()->quote('Test Quote')->toString();
    expect($result)->toBe('> Test Quote');

    // multiple quotes
    $quotes = [
        'Quote Line 1',
        'Quote Line 2',
        'Quote Line 3',
    ];

    $result = Markdown::make()->quote($quotes)->toString();

    expect($result)->toContain('> Quote Line 1')
        ->and($result)->toContain('> Quote Line 2')
        ->and($result)->toContain('> Quote Line 3')
        ->toBe(
            <<<'MARKDOWN'
            > Quote Line 1
            > Quote Line 2
            > Quote Line 3
            MARKDOWN
        );
})->group('quote');

// Block
it('builds fenced code blocks with language', function () {

    // code block
    $code = <<<'CODE'
            function test() { 
                return true; 
            }
            CODE;
    $result = Markdown::make()->block($code, 'php')->toString();

    expect($result)->toStartWith('```php')
        ->and($result)->toContain($code)
        ->and($result)->toEndWith('```')
        ->toBe(
            <<<MARKDOWN
            ```php
            $code
            ```
            MARKDOWN
        );

    // different language
    $code = 'SELECT * FROM users;';
    $result = Markdown::make()->block($code, 'sql')->toString();

    expect($result)->toStartWith('```sql')
        ->and($result)->toContain($code)
        ->and($result)->toEndWith('```');
})->group('code-block');

// Link
it('builds links', function () {

    $result = Markdown::make()->link('https://example.com')->toString();
    expect($result)->toBe('[https://example.com](https://example.com)');

    $result = Markdown::make()->link('https://example.com', 'Example')->toString();
    expect($result)->toBe('[Example](https://example.com)');
})->group('link');

// Image
it('builds images', function () {

    $result = Markdown::make()->image('https://example.com/image.png')->toString();
    expect($result)->toBe('![](https://example.com/image.png)');

    $result = Markdown::make()->image('https://example.com/image.png', 'Title Text', 'Alt title')->toString();
    expect($result)->toBe('![Title Text](https://example.com/image.png "Alt title")');
})->group('image');

// Raw
it('adds raw content', function () {

    $result = Markdown::make()->raw('#### Raw content')->toString();

    expect($result)->toBe('#### Raw content');
})->group('raw');

// Table
it('render table content', function () {

    $result = Markdown::make()->table(
        ['Header 1', 'Header 2', 'Header 3'],
        [
            ['Value 1', 'Value 2', 'Value 3'],
            ['Value 4', 'Value 5', 'Value 6'],
        ],
    )->toString();

    expect($result)->toBe(
        <<<'MARKDOWN'
        | Header 1 | Header 2 | Header 3 |
        | --- | --- | --- |
        | Value 1 | Value 2 | Value 3 |
        | Value 4 | Value 5 | Value 6 |
        MARKDOWN
    );
})->group('table');

// To string
it('converts to string correctly', function () {

    $result = Markdown::make()->heading('Title')->line('Content')->toString();

    expect($result)->toBeString()
        ->and($result)->toStartWith('# Title' . PHP_EOL)
        ->and($result)->toEndWith('Content');
})->group('to-string');

// Chaining
it('supports method chaining', function () {
    $result = Markdown::make('<br>')
        ->heading('Title')
        ->line('Content')
        ->link('https://example.com', 'Example')
        ->toString();

    expect($result)->toContain('# Title')
        ->and($result)->toContain('Content')
        ->and($result)->toContain('[Example](https://example.com)')
        ->toBe(
            <<<'MARKDOWN'
            # Title<br><br>Content<br><br>[Example](https://example.com)
            MARKDOWN
        );

    $result = Markdown::make(suppressed: true)
        ->heading('Title')
        ->line('Content')
        ->break()
        ->link('https://example.com', 'Example')
        ->toString();

    expect($result)->toContain('# Title')
        ->and($result)->toContain('Content')
        ->and($result)->toContain('[Example](https://example.com)')
        ->toBe(
            <<<'MARKDOWN'
            # Title
            Content
            
            [Example](https://example.com)
            MARKDOWN
        );

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

    expect($result)->toContain('# Title')
        ->and($result)->toContain('Content')
        ->and($result)->toContain('[Example](https://example.com)')
        ->toBe(
            <<<'MARKDOWN'
            # Title
            Content
            
            [Example](https://example.com)
            
            # Title 2
            
            Content 2
            MARKDOWN
        );

    $result = Markdown::make()
        ->suppress(fn($m) => $m->heading('Title')
            ->line('Content')
            ->break()
            ->link('https://example.com', 'Example'))
        ->heading('Title 2')
        ->line('Content 2')
        ->toString();

    expect($result)->toContain('# Title')
        ->and($result)->toContain('Content')
        ->and($result)->toContain('[Example](https://example.com)')
        ->toBe(
            <<<'MARKDOWN'
            # Title
            Content
            
            [Example](https://example.com)
            
            # Title 2
            
            Content 2
            MARKDOWN
        );
})->group('chaining', 'test');

// Conditioning
it('supports conditioning', function ($condition, $callback, $expected) {
    $result = Markdown::make(escaper: Escaper::make(['.']))
        ->heading('Title')
        ->line('Content')
        ->link('https://example.com', 'Example')
        ->when($condition, $callback)
        ->toString();

    expect($result)->toContain('# Title')
        ->and($result)->toContain('Content')
        ->and($result)->toContain('[Example](https://example.com)')
        ->toBe($expected);

})->group('conditioning')
    ->with([
        'with `false` condition' => [
            false,
            fn($markdown) => $markdown->line('This line should not be added.'),
            <<<'MARKDOWN'
            # Title
            
            Content
            
            [Example](https://example.com)
            MARKDOWN
        ],
        'with `true` condition' => [
            true,
            fn($markdown) => $markdown->line('This line should be added.'),
            <<<'MARKDOWN'
            # Title
            
            Content
            
            [Example](https://example.com)
            
            This line should be added\.
            MARKDOWN
        ],
        'with `callable` condition with `string` return' => [
            fn() => 'This line should be added too.',
            fn(Markdown $markdown, $line) => $markdown->suppress(fn($markdown) => $markdown->break()->line('This line should not be added.')->line($line)),
            <<<'MARKDOWN'
            # Title
            
            Content
            
            [Example](https://example.com)
            
            This line should not be added\.
            This line should be added too\.
            MARKDOWN
        ],
        'with `callable` condition with `false` return' => [
            fn() => false,
            fn($markdown) => $markdown->break()->line('This line should not be added.'),
            <<<'MARKDOWN'
            # Title
            
            Content
            
            [Example](https://example.com)
            MARKDOWN
        ],
    ]);

it('should pass close to real', function () {
    $orderItems = [
        ['name' => 'USB-C Cable (2m)', 'qty' => 2, 'price' => 9.99],
        ['name' => 'Wireless Mouse [Bluetooth]', 'qty' => 1, 'price' => 24.50],
    ];
    $customerName = 'John_Doe';
    $orderId = '#ORD-2024.001';

    $md = Markdown::make(suppressed: true, escaper: Escaper::makeForV2())
        ->heading('Order Confirmation', MarkdownHeading::H3, bindings: [])
        ->break()
        ->line('**Customer:** {{?}}', bindings: [$customerName])
        ->line('**Order:** {{?}}', bindings: [$orderId])
        ->break()
        ->raw('**Items:**')
        ->list(
            array_map(fn($item) => '{{?}} × {{?}} — ${{?}}', $orderItems),
            array_map(fn($item) => [$item['name'], $item['qty'], $item['price']], $orderItems),
        )
        ->break()
        ->line('Thank you for your order!');

    expect($md->toString())->toBe(
        <<<'MARKDOWN'
        ### Order Confirmation

        **Customer:** John\_Doe
        **Order:** \#ORD\-2024\.001
        
        **Items:**
        - USB\-C Cable \(2m\) × 2 — $9\.99
        - Wireless Mouse \[Bluetooth\] × 1 — $24\.5
        
        Thank you for your order\!
        MARKDOWN
    );
})->group('close-to-real');
