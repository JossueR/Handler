<?php

namespace HandlerCore\models;

enum FiltersCheckMode:string
{
    case PRELOAD_FIELDS = 'PRELOAD_FIELDS';
    case CHECK_IN_QUERY = 'CHECK_IN_QUERY';

    case DISABLED = 'DISABLED';
}