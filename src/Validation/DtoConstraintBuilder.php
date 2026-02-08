<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Validation;

use PhpCollective\Dto\Dto\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class DtoConstraintBuilder
{
    /**
     * Build a Symfony Assert\Collection constraint from DTO validation rules.
     *
     * @param \PhpCollective\Dto\Dto\Dto $dto
     *
     * @return \Symfony\Component\Validator\Constraints\Collection
     */
    public static function fromDto(Dto $dto): Assert\Collection
    {
        $fields = [];
        foreach ($dto->validationRules() as $field => $rules) {
            $constraints = [];
            if (!empty($rules['required'])) {
                $constraints[] = new Assert\NotBlank();
            }
            if (isset($rules['minLength']) || isset($rules['maxLength'])) {
                $lengthOptions = [];
                if (isset($rules['minLength'])) {
                    $lengthOptions['min'] = $rules['minLength'];
                }
                if (isset($rules['maxLength'])) {
                    $lengthOptions['max'] = $rules['maxLength'];
                }
                $constraints[] = new Assert\Length($lengthOptions);
            }
            if (isset($rules['min']) || isset($rules['max'])) {
                $rangeOptions = [];
                if (isset($rules['min'])) {
                    $rangeOptions['min'] = $rules['min'];
                }
                if (isset($rules['max'])) {
                    $rangeOptions['max'] = $rules['max'];
                }
                $constraints[] = new Assert\Range($rangeOptions);
            }
            if (isset($rules['pattern'])) {
                $constraints[] = new Assert\Regex(['pattern' => $rules['pattern']]);
            }

            if ($constraints) {
                if (!empty($rules['required'])) {
                    $fields[$field] = new Assert\Required($constraints);
                } else {
                    $fields[$field] = new Assert\Optional($constraints);
                }
            }
        }

        return new Assert\Collection(['fields' => $fields, 'allowExtraFields' => true]);
    }
}
