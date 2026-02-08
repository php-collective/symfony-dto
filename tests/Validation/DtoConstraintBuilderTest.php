<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test\Validation;

use PhpCollective\SymfonyDto\Test\Fixtures\ValidationTestDto;
use PhpCollective\SymfonyDto\Validation\DtoConstraintBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class DtoConstraintBuilderTest extends TestCase
{
    public function testFromDtoReturnsCollectionConstraint(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $this->assertInstanceOf(Assert\Collection::class, $constraint);
    }

    public function testRequiredFieldMapsToNotBlankAndRequired(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $this->assertArrayHasKey('name', $constraint->fields);
        $this->assertInstanceOf(Assert\Required::class, $constraint->fields['name']);

        $innerConstraints = $constraint->fields['name']->constraints;
        $types = array_map(fn ($c) => $c::class, $innerConstraints);

        $this->assertContains(Assert\NotBlank::class, $types);
        $this->assertContains(Assert\Length::class, $types);
    }

    public function testOptionalFieldMapsToOptional(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $this->assertArrayHasKey('email', $constraint->fields);
        $this->assertInstanceOf(Assert\Optional::class, $constraint->fields['email']);
    }

    public function testLengthConstraints(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $lengthConstraint = null;
        foreach ($constraint->fields['name']->constraints as $c) {
            if ($c instanceof Assert\Length) {
                $lengthConstraint = $c;

                break;
            }
        }

        $this->assertNotNull($lengthConstraint);
        $this->assertSame(2, $lengthConstraint->min);
        $this->assertSame(50, $lengthConstraint->max);
    }

    public function testRangeConstraints(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $this->assertArrayHasKey('age', $constraint->fields);

        $rangeConstraint = null;
        foreach ($constraint->fields['age']->constraints as $c) {
            if ($c instanceof Assert\Range) {
                $rangeConstraint = $c;

                break;
            }
        }

        $this->assertNotNull($rangeConstraint);
        $this->assertSame(0, $rangeConstraint->min);
        $this->assertSame(150, $rangeConstraint->max);
    }

    public function testPatternConstraint(): void
    {
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $regexConstraint = null;
        foreach ($constraint->fields['email']->constraints as $c) {
            if ($c instanceof Assert\Regex) {
                $regexConstraint = $c;

                break;
            }
        }

        $this->assertNotNull($regexConstraint);
        $this->assertSame('/^[^@]+@[^@]+\.[^@]+$/', $regexConstraint->pattern);
    }

    public function testValidationIntegration(): void
    {
        $validator = Validation::createValidator();
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        // Valid data
        $violations = $validator->validate(['name' => 'John', 'email' => 'john@example.com', 'age' => 30], $constraint);
        $this->assertCount(0, $violations);

        // Missing required field
        $violations = $validator->validate(['email' => 'test@example.com'], $constraint);
        $this->assertGreaterThan(0, count($violations));

        // Name too short
        $violations = $validator->validate(['name' => 'A'], $constraint);
        $this->assertGreaterThan(0, count($violations));

        // Age out of range
        $violations = $validator->validate(['name' => 'John', 'age' => 200], $constraint);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testAllowExtraFields(): void
    {
        $validator = Validation::createValidator();
        $dto = new ValidationTestDto();
        $constraint = DtoConstraintBuilder::fromDto($dto);

        $violations = $validator->validate(['name' => 'John', 'extra' => 'field'], $constraint);
        $this->assertCount(0, $violations);
    }
}
