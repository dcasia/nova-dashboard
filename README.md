# Nova Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![Total Downloads](https://img.shields.io/packagist/dt/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![License](https://img.shields.io/packagist/l/digital-creative/nova-dashboard)](https://github.com/dcasia/nova-dashboard/blob/master/LICENSE)

![Laravel Nova Dashboard In Action](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/screenshots/demo.gif)

The missing dashboard for Laravel Nova!

# Installation

You can install the package via composer:

```
composer require digital-creative/nova-dashboard
```

## Usage

The dashboard itself is simply a standard Laravel Nova card, so you can use it either as a card on any resource 
or within the default Nova dashboard functionality.

```php
use DigitalCreative\NovaDashboard\Card\NovaDashboard;
use DigitalCreative\NovaDashboard\Card\View;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    public function cards(): array
    {
        return [
            NovaDashboard::make()
                ->addView('Website Performance', function (View $view) {
                    return $view
                        ->icon('window')
                        ->addWidgets([
                            BounceRate::make(),
                            ConversionRate::make(),
                            WebsiteTraffic::make(),
                            SessionDuration::make(),
                        ])
                        ->addFilters([
                            LocationFilter::make(),
                            UserTypeFilter::make(),
                            DateRangeFilter::make(),
                        ]);
                }),
        ];
    }
}
```

## Widgets

The widgets are responsible for displaying your data on your views; they are essentially standard Nova cards.
However, they respond to dashboard events and reload their data whenever the filters change.

Once you have a widget, they are usually configured like this:

```php
class MyCustomWidget extends ValueWidget
{
    /**
     * Here you can configure your widget by calling whatever options are available for each widget
     */
    public function configure(NovaRequest $request): void
    {
        $this->icon('<svg>...</svg>');
        $this->title('Session Duration');
        $this->textColor('#f95738');
        $this->backgroundColor('#f957384f');
    }

    /**
     * This function is responsible for returning the actual data that will be shown on the widget,
     * each widget expects its own format, so please refer to the widget documentation 
     */
    public function value(Filters $filters): mixed
    {
        /**
         * $filters contain all the set values from the filters that were shown on the frontend. 
         * You can retrieve them and implement any custom logic you may have.
         */
        $filterValue = $filters->getFilterValue(LikesFilter::class);
        
        return 'example';
    }
}
```

### List of current available widgets:

- Value Widget: [https://github.com/dcasia/value-widget](https://github.com/dcasia/value-widget)
- [Add your widget here.](https://github.com/dcasia/nova-dashboard/edit/main/README.md)

## Filters

![Filters Preview](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/screenshots/widget-3.png)

These are standard nova filter classes with 1 simple difference, the method `->apply()` does not get called by default. Why?

```php
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class ExampleFilter extends BooleanFilter
{
    public function apply(Request $request, $query, $value)
    {
        // this function is required however it is not used by the nova-dashboard
    }
}
```

Usually your widget `value()` function will receive an instance of `\DigitalCreative\NovaDashboard\Filters` this class 
contains a method for retrieving the value of any given filter, for example:

```php
class SessionDuration extends ValueWidget
{
    public function value(Filters $filters): mixed
    {
        $filterA = $filters->getFilterValue(YourFilterClass::class);
        $filterB = $filters->getFilterValue(YourSecondFilterClass::class);
        
        return new ValueResult(...);
    }
}
```

However, if you want to reuse the logic that you have previously set on your filters or share existing filters with
the dashboard you can call the method `->applyToQueryBuilder()` to get the same behavior:

```php
class SessionDuration extends ValueWidget
{
    public function value(Filters $filters): mixed
    {
        $result = $filters->applyToQueryBuilder(User::query())->get();    
    }
}
```

`applyToQueryBuilder` will run every filter through the default filter logic of nova.

## ⭐️ Show Your Support
Please give a ⭐️ if this project helped you!

## License

The MIT License (MIT). Please see [License File](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/LICENSE) for more information.
