<?php

namespace Akeneo\Channel\Infrastructure\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if a locale is activated.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActivatedLocale extends Constraint
{
    /** @var string */
    public $message = 'The locale "%locale%" exists but has to be activated.';

    /** @var string */
    public $propertyPath = '';
}
