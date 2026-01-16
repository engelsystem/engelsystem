<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Services;

use Engelsystem\Services\ShiftValidationResult;
use Engelsystem\Test\Unit\TestCase;

class ShiftValidationResultTest extends TestCase
{
    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::__construct
     */
    public function testConstruct(): void
    {
        $result = new ShiftValidationResult(
            isValid: true,
            errors: [],
            warnings: ['Some warning']
        );

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertEquals(['Some warning'], $result->warnings);
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::success
     */
    public function testSuccess(): void
    {
        $result = ShiftValidationResult::success();

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertEmpty($result->warnings);

        $resultWithWarnings = ShiftValidationResult::success(['Warning 1', 'Warning 2']);
        $this->assertTrue($resultWithWarnings->isValid);
        $this->assertEmpty($resultWithWarnings->errors);
        $this->assertCount(2, $resultWithWarnings->warnings);
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::failure
     */
    public function testFailure(): void
    {
        $result = ShiftValidationResult::failure(['Error 1', 'Error 2']);

        $this->assertFalse($result->isValid);
        $this->assertCount(2, $result->errors);
        $this->assertEmpty($result->warnings);

        $resultWithWarnings = ShiftValidationResult::failure(['Error'], ['Warning']);
        $this->assertFalse($resultWithWarnings->isValid);
        $this->assertCount(1, $resultWithWarnings->errors);
        $this->assertCount(1, $resultWithWarnings->warnings);
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::error
     */
    public function testError(): void
    {
        $result = ShiftValidationResult::error('Single error message');

        $this->assertFalse($result->isValid);
        $this->assertCount(1, $result->errors);
        $this->assertEquals('Single error message', $result->errors[0]);
        $this->assertEmpty($result->warnings);
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::hasErrors
     */
    public function testHasErrors(): void
    {
        $successResult = ShiftValidationResult::success();
        $this->assertFalse($successResult->hasErrors());

        $errorResult = ShiftValidationResult::error('Error');
        $this->assertTrue($errorResult->hasErrors());
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::hasWarnings
     */
    public function testHasWarnings(): void
    {
        $noWarnings = ShiftValidationResult::success();
        $this->assertFalse($noWarnings->hasWarnings());

        $withWarnings = ShiftValidationResult::success(['Warning']);
        $this->assertTrue($withWarnings->hasWarnings());
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::getErrorsAsString
     */
    public function testGetErrorsAsString(): void
    {
        $result = ShiftValidationResult::failure(['Error 1', 'Error 2', 'Error 3']);

        $this->assertEquals('Error 1, Error 2, Error 3', $result->getErrorsAsString());
        $this->assertEquals('Error 1 | Error 2 | Error 3', $result->getErrorsAsString(' | '));

        $emptyResult = ShiftValidationResult::success();
        $this->assertEquals('', $emptyResult->getErrorsAsString());
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::getWarningsAsString
     */
    public function testGetWarningsAsString(): void
    {
        $result = ShiftValidationResult::success(['Warning 1', 'Warning 2']);

        $this->assertEquals('Warning 1, Warning 2', $result->getWarningsAsString());
        $this->assertEquals('Warning 1; Warning 2', $result->getWarningsAsString('; '));

        $emptyResult = ShiftValidationResult::success();
        $this->assertEquals('', $emptyResult->getWarningsAsString());
    }

    /**
     * @covers \Engelsystem\Services\ShiftValidationResult::merge
     */
    public function testMerge(): void
    {
        // Success + Success = Success
        $success1 = ShiftValidationResult::success(['Warning 1']);
        $success2 = ShiftValidationResult::success(['Warning 2']);
        $merged = $success1->merge($success2);

        $this->assertTrue($merged->isValid);
        $this->assertEmpty($merged->errors);
        $this->assertCount(2, $merged->warnings);

        // Success + Failure = Failure
        $failure = ShiftValidationResult::failure(['Error 1']);
        $merged2 = $success1->merge($failure);

        $this->assertFalse($merged2->isValid);
        $this->assertCount(1, $merged2->errors);
        $this->assertCount(1, $merged2->warnings);

        // Failure + Failure = Failure (combined errors)
        $failure1 = ShiftValidationResult::failure(['Error 1']);
        $failure2 = ShiftValidationResult::failure(['Error 2'], ['Warning']);
        $merged3 = $failure1->merge($failure2);

        $this->assertFalse($merged3->isValid);
        $this->assertCount(2, $merged3->errors);
        $this->assertCount(1, $merged3->warnings);
    }
}
