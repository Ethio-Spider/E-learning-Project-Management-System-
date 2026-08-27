<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CourseValidationTest extends TestCase
{
    public function testValidCoursePassesValidation(): void
    {
        $result = Validator::validateCourse([
            'title' => 'Project Planning',
            'description' => 'A practical course description.',
            'category' => 'Management',
            'instructor' => 'Alex Instructor',
            'duration' => '6 weeks',
            'level' => 'Beginner',
            'status' => 'Active',
        ]);

        self::assertFalse($result['validator']->hasErrors());
    }

    public function testInvalidCourseReturnsValidationErrors(): void
    {
        $result = Validator::validateCourse(['title' => 'x', 'description' => 'short']);

        self::assertTrue($result['validator']->hasErrors());
        self::assertArrayHasKey('title', $result['validator']->getErrors());
    }

    public function testSqlInjectionInputIsRejectedAsAnId(): void
    {
        self::assertFalse(Validator::positiveInt("1' OR '1'='1"));
    }

    public function testExpiredDeadlineIsRejected(): void
    {
        self::assertFalse(Validator::validDeadline('2020-01-01 00:00:00', 1700000000));
        self::assertTrue(Validator::validDeadline('2030-01-01 00:00:00', 1700000000));
    }
}
