<?php

namespace HandlerCore\models;

enum PaginationMode: string {
    case SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';
    case APPROXIMATE = 'APPROXIMATE';
}