# Code Generator for Laravel

## Installation

You can install the package via composer:

``` bash
composer require jeylabs/laravel-code-generator
```

## Usage

```php

<?php

namespace App;

use Jeylabs\CodeGenerator\Traits\Code;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Code;

    static $codePrefix = 'PRO';
}
```
Example : PRO0000001

#### If you want to customize your code column
```php
static $codeColumn = 'code';
```

#### If you want to set the prefix base on conditions
```php
static $codePrefix = [
        "RM" => [
            'column' => 'type',
            'value' => 'Raw Materials'
        ],
        "FG" => [
            'column' => 'type',
            'value' => 'Finished Goods'
        ]
    ];
```