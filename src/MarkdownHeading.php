<?php

declare(strict_types = 1);

namespace Amondar\Markdown;

enum MarkdownHeading: string
{
    case H1 = '#';
    case H2 = '##';
    case H3 = '###';
    case H4 = '####';
    case H5 = '#####';
    case H6 = '######';
}
