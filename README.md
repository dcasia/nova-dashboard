# Nova Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![Total Downloads](https://img.shields.io/packagist/dt/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![License](https://img.shields.io/packagist/l/digital-creative/nova-dashboard)](https://github.com/dcasia/nova-dashboard/blob/master/LICENSE)

![Laravel Nova Dashboard In Action](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/demo.gif)

# Installation

You can install the package via composer:

```
composer require digital-creative/nova-dashboard
```

## Usage

Add this to `NovaServiceProvider.php`

```php
use DigitalCreative\NovaDashboard\Examples\ExampleDashboard;
use DigitalCreative\NovaDashboard\NovaDashboard;

public function tools()
{
    return [
        new NovaDashboard([
            ExampleDashboard::class, // Example as on the gif above
        ])
    ];
}
```

> To to visualize the demo above you also will need to install these extra packages `composer require digital-creative/value-widget digital-creative/nova-range-input-filter digital-creative/nova-pill-filter`

## Documentation

WIP, meanwhile have a look at the examples folder!

## License

The MIT License (MIT). Please see [License File](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/LICENSE) for more information.
