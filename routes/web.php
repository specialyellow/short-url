<?php

use SpecialYellow\ShortURL\Facades\ShortURL;

if (! config('short-url.disable_default_route')) {
    ShortURL::routes();
}
