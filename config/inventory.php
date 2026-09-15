<?php

return [
    'statuses' => ['active', 'inactive'],

    'quotation_statuses' => ['Draft', 'Submitted', 'Approved', 'Rejected', 'Expired', 'Converted'],

    'approval_decisions' => ['Approved', 'Rejected'],

    'purchase_order_statuses' => ['Draft', 'Sent', 'Partially Received', 'Received', 'Closed', 'Cancelled'],

    'goods_receipt_statuses' => ['Draft', 'Completed'],

    'stock_movement_directions' => ['In', 'Out'],

    'stock_adjustment_statuses' => ['Pending', 'Approved', 'Rejected'],

    'adjustment_types' => ['Increase', 'Decrease'],

    'adjustment_reasons' => [
        'Opening Stock',
        'Physical Count Correction',
        'Damaged / Expired',
        'Lost / Theft',
        'Internal Use',
        'Other',
    ],
];
