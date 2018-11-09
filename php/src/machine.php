<?php

// 券売機クラス
class TicketMachine {

    const PRICE_PHYSICAL_MONEY = 130;
    const PRICE_ELECTORIC_MONEY = 124;

    // 金種リスト
    static public $moneyTypes = [
        10,
        50,
        100,
        500,
        1000,
        5000,
        10000,
    ];

    // 投入された金種と枚数
    public $inputMoney = [];

    // 投入された金額の合計
    public $inputSum = 0;

    // お釣りの金種と枚数
    public $returnMoney = [];

    // 金種受取り
    function input($input) {
        foreach($input as $key => $value) {
            if (preg_match('/m[1-9][0-9]{1,4}/', $key)) {
                $this->inputMoney[$key] = $value;
                continue;
            }
            if ($key === 'e_money') {
                $this->inputMoney[$key] = $value;
            }
        }
    }

    // 券購入処理
    public function buyTicket() {
        $this->calcInput();
        if (isset($this->inputMoney['e_money'])) {
            $response = $this->requestToEmoneyServer($this->inputMoney['e_money']);
            $data = json_decode($response, JSON_OBJECT_AS_ARRAY);
            if ($data['result'] === 'success') {
                $this->returnMoney['e_money'] = (int)$data['left'];
            } else {
                $this->returnMoney['message'] = $data['reason'];
            }
            return;
        }
        $this->inputSum -= self::PRICE_PHYSICAL_MONEY;
        if ($this->inputSum < 0) {
            $this->returnMoney['message'] = 'お金が足りません';
            return;
        }
        $this->breakMoney();
    }

    // JSON形式で出力
    public function renderJson() {
        $json = json_encode($this->returnMoney, JSON_UNESCAPED_UNICODE);
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo $json;
    }

    // 電子マネー処理サーバとの通信スタブ
    private function requestToEmoneyServer($input) {
        $price = self::PRICE_ELECTORIC_MONEY;
        $left = $input - $price;
        if ($left < 0) {
            return <<<JSON
{
    "result": "failed",
    "reason": "電子マネーの残高が足りません"
}
JSON;
        }
        return <<<JSON
{
    "result": "success",
    "left": $left,
    "subbed": $price
}
JSON;
    }

    // 金種から金額を集計
    private function calcInput() {
        foreach (self::$moneyTypes as $type) {
            if (is_numeric($this->inputMoney["m$type"])) {
                $this->inputSum += $type * $this->inputMoney["m$type"];
            }
        }
    }

    // お釣りを金種に分解
    private function breakMoney() {
        for ($i = count(self::$moneyTypes) - 1; $i >= 0; $i--) {
            if ($this->inputSum >= self::$moneyTypes[$i]) {
                $this->returnMoney['m' . self::$moneyTypes[$i]] = (int)($this->inputSum / self::$moneyTypes[$i]);
                $this->inputSum %= self::$moneyTypes[$i];
            }
        }
    }
}
