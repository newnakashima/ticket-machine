<?php

require_once __DIR__ . '/../src/machine.php';

// 金種受け取りテスト
function testInputMoney() {
    $machine = new TicketMachine();
    $input = [
        'm100' => 2,
    ];
    $machine->input($input);
    $expected = [
        'm100' => 2,
    ];
    foreach ($expected as $type => $number) {
        assert($machine->inputMoney[$type] == $number);
    }
}

// 券購入処理テスト
function testBuyTicket($input, $expected) {
    $machine = new TicketMachine();
    $machine->input($input);
    $machine->buyTicket();
    foreach ($expected as $type => $number) {
        assert($machine->returnMoney[$type] == $number);
    }
}

$buyTicketTestCases = [
    '100円2枚の場合' => [
        'input' => [
            'm100' => 2,
        ],
        'expected' => [
            'm50' => 1,
            'm10' => 2,
        ],
    ],
    '10000円札1枚の場合' => [
        'input' => [
            'm10000' => 1,
        ],
        'expected' => [
            'm5000' => 1,
            'm1000' => 4,
            'm500' => 1,
            'm100' => 3,
            'm50' => 1,
            'm10' => 2,
        ]
    ],
    '50円玉3枚の場合' => [
        'input' => [
            'm50' => 3,
        ],
        'expected' => [
            'm10' => 2,
        ]
    ],
    '5000円札1枚の場合' => [
        'input' => [
            'm5000' => 1,
        ],
        'expected' => [
            'm1000' => 4,
            'm500' => 1,
            'm100' => 3,
            'm50' => 1,
            'm10' => 2,
        ],
    ],
    '電子マネーの場合' => [
        'input' => [
            'e_money' => 1000,
        ],
        'expected' => [
            'e_money' => 876,
        ]
    ],
    '物理マネーで金額不足' => [
        'input' => [
            'm100' => 1,
        ],
        'expected' => [
            'message' => 'お金が足りません',
        ]
    ],
    '電子マネーで残高不足' => [
        'input' => [
            'e_money' => 100,
        ],
        'expected' => [
            'message' => '電子マネーの残高が足りません',
        ]
    ,]
];

echo "Tests Started.\n";

testInputMoney();
foreach ($buyTicketTestCases as $case) {
    testBuyTicket($case['input'], $case['expected']);
}

echo "Tests Ended.\n";
