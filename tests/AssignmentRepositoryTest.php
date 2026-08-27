<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AssignmentRepositoryTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('SQLite PDO driver is not available.');
        }

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            assignment_id INTEGER NOT NULL,
            enrollment_id INTEGER NOT NULL,
            student_email TEXT NOT NULL,
            submission_text TEXT,
            file_url TEXT
        )');
    }

    public function testDuplicateSubmissionIsRejected(): void
    {
        $repository = new AssignmentRepository($this->pdo);
        $repository->submitAssignment(4, 9, 'student@example.com', 'First submission');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Already submitted this assignment.');
        $repository->submitAssignment(4, 9, 'student@example.com', 'Second submission');
    }
}
