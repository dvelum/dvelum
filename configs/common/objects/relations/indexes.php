<?php
return [
    'source_id' => [
            'columns' => [
                    'source_id',
                    'target_id'
            ],
            'fulltext' => false,
            'unique' => true,
    ],
    'order_no' => [
            'columns' =>[
                'order_no',
            ],
            'fulltext' => false,
            'unique' => false,
    ]
];