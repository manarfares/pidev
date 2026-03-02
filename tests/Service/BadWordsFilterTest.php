<?php

namespace App\Tests\Service;

use App\Service\BadWordsFilter;
use PHPUnit\Framework\TestCase;

class BadWordsFilterTest extends TestCase
{
    public function testHasBadWordsDetectsForbiddenWord(): void
    {
        $filter = new BadWordsFilter();

        self::assertTrue($filter->hasBadWords('Ce service est merde.'));
    }

    public function testHasBadWordsReturnsFalseForCleanText(): void
    {
        $filter = new BadWordsFilter();

        self::assertFalse($filter->hasBadWords('Service propre et professionnel.'));
    }

    public function testCensorReplacesForbiddenWords(): void
    {
        $filter = new BadWordsFilter();
        $text = 'This is shit and damn bad.';
        $censored = $filter->censor($text);

        self::assertStringNotContainsString('shit', mb_strtolower($censored));
        self::assertStringNotContainsString('damn', mb_strtolower($censored));
        self::assertMatchesRegularExpression('/\*{4}/', $censored);
    }

    public function testGetBadWordsReturnsUniqueList(): void
    {
        $filter = new BadWordsFilter();
        $found = $filter->getBadWords('idiot ... IDIOT ... merde');

        self::assertContains('idiot', $found);
        self::assertContains('merde', $found);
        self::assertCount(2, $found);
    }
}
