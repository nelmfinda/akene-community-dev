<?php

namespace Akeneo\Tool\Component\StorageUtils\Saver;

use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;

/**
 * Saver interface, provides a minimal contract to save a single business object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SaverInterface
{
    /**
     * Save a single object
     *
     * @param mixed $object  The object to save
     * @param array $options The saving options
     *
     * @throws \InvalidArgumentException
     * @throws DuplicateObjectException
     */
    public function save($object, array $options = []);
}
