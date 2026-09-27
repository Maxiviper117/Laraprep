<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Planning;

enum ChangeStatus: string
{
    case Planned = 'planned';
    case Applied = 'applied';
    case Skipped = 'skipped';
    case Failed = 'failed';
    case ManualReview = 'manual-review';
}
