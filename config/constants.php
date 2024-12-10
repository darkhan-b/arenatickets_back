<?php

const ORDER_TIME_LIMIT = 20; // in minutes (amount of minutes given to finish / pay order)
const REFUND_APPLICATION_LIMIT = 72; // in hours (refund is impossible if there is less than this amount of hours left before beginning of event)
const PARTNER_API_TIMESTAMP_LIMIT = 120; // in seconds (for api signature check)

const API_DVOREC_RESPUBLIKI = 'dvorec_respubliki';
const API_ALMATY_ARENA = 'almaty_arena';
const API_SEMEY_ABAY_ARENA = 'abay_arena';
const API_SHOWMARKET = 'showmarket';

const TIMETABLES_FOR_TESTING = [144, 146, 227, 277, 737, 769, 931, 932, 953, 938, 944];

const DEFAULT_SOURCE = 'arenatickets';
