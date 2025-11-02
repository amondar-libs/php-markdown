<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

enum MarkdownType: string
{
    case HEADER = 'header';
    case PARAGRAPH = 'paragraph';
    case QUOTE = 'quote';
    case LINK = 'link';
    case IMAGE = 'image';
    case CODE = 'code';
    case LIST = 'list';
    case NUMERIC_LIST = 'numeric_list';
    case RAW = 'raw';

}
