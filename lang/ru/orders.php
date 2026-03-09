<?php

return [
    'cannot_cancel' => 'Этот заказ нельзя отменить',
    'cannot_reschedule' => 'Невозможно изменить время этого заказа',
    'reschedule_too_late' => 'До сеанса менее 24 часов, перенос невозможен',
    'cancelled_success' => 'Заказ отменён',
    'rescheduled_success' => 'Заказ успешно перенесён',

    // Log notes
    'log_cancelled_over_24h' => 'Отменён клиентом (24+ часов, удержано 15%)',
    'log_cancelled_under_24h' => 'Отменён клиентом (менее 24 часов)',
    'log_refund_detail' => 'Возврат: :refund сум, Удержано: :fee сум',
    'log_rescheduled_by_customer' => 'Перенесён клиентом',
];
