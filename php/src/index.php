<?php

require_once __DIR__ . '/machine.php';

$machine = new TicketMachine();
$machine->input($_GET);
$machine->buyTicket();
$machine->renderJson();

