<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Twig\Environment;

class TwigTemplateProperty extends AbstractProperty
{
    const CONTEXT_KEY = 'context';
    const TEMPLATE_KEY = 'template';

    /** @var array */
    protected $excludeParams = [self::CONTEXT_KEY, self::TEMPLATE_KEY];

    /** @var Environment */
    protected $environment;

    /**  @var array */
    protected $reservedKeys = ['record', 'value'];

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $checkInvalidArgument = array_intersect(array_keys($this->getOr(self::CONTEXT_KEY, [])), $this->reservedKeys);
        if (count($checkInvalidArgument)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Context of template "%s" includes reserved key(s) - (%s)',
                    $this->get(self::TEMPLATE_KEY),
                    implode(', ', array_values($checkInvalidArgument))
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        $context = array_merge(
            $this->getOr(self::CONTEXT_KEY, []),
            [
                'record' => $record,
                'value'  => $record->getValue($this->getOr(self::DATA_NAME_KEY, $this->get(self::NAME_KEY))),
            ]
        );

        return $this->getTemplate()->render($context);
    }

    /**
     * Load twig template
     */
    protected function getTemplate()
    {
        $templateName = $this->get(self::TEMPLATE_KEY);

        return $this->environment->loadTemplate(
            $this->environment->getTemplateClass($templateName),
            $templateName,
        );
    }
}
