<?php

declare(strict_types=1);

return [
    'validation' => [
        'invalid_value' => 'The :attribute must be a valid :enum value.',
        'invalid_name' => 'The :attribute must be a valid :enum case name.',
        'not_allowed' => 'The :attribute value is not in the allowed set: :values.',
        'excluded' => 'The :attribute value :value is excluded.',
        'invalid_transition' => 'Cannot transition :from to :to for :enum.',
        'invalid_enum_class' => 'The configured enum class :class is not valid.',
    ],
    'components' => [
        'select' => [
            'placeholder' => 'Select an option…',
            'empty' => 'No options available.',
        ],
        'radio' => [
            'group_label' => 'Choose one of :count options.',
        ],
        'grid' => [
            'empty' => 'No options available.',
        ],
    ],
    'aria' => [
        'badge' => 'Status: :label',
        'select' => 'Select :name',
        'radio' => 'Choose :name',
        'grid' => ':label',
    ],
    'commands' => [
        'cache' => [
            'cached' => 'Cached enumerator reflection data.',
            'cleared' => 'Cleared enumerator reflection cache.',
        ],
    ],
];
