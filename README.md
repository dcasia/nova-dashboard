# Nova Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![Total Downloads](https://img.shields.io/packagist/dt/digital-creative/nova-dashboard)](https://packagist.org/packages/digital-creative/nova-dashboard)
[![License](https://img.shields.io/packagist/l/digital-creative/nova-dashboard)](https://github.com/dcasia/nova-dashboard/blob/master/LICENSE)

![Laravel Nova Dashboard In Action](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/screenshots/demo.gif)

The missing dashboard for Laravel Nova! Easy to manage with [Nova Dashboard Manager](https://novapackages.com/packages/nova-bi/nova-dashboard-manager).

# Installation

You can install the package via composer:

```
composer require digital-creative/nova-dashboard
```

**Recommended:** Publish Configuration File for adjusting model and table names

    php artisan vendor:publish --provider="DigitalCreative\NovaDashboard\ToolServiceProvider" --tag="config"

Publish Migrations

    php artisan vendor:publish --provider="DigitalCreative\NovaDashboard\ToolServiceProvider" --tag="migration"

Run Migrations

    php artisan migrate

## Usage


### Option 1: Code your Dashboards

Register the `NovaDashboard` tool within your `NovaServiceProvider.php`

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

> To visualize the demo above you also will need to install these extra packages: 
> - digital-creative/value-widget 
> - digital-creative/nova-range-input-filter 
> - digital-creative/nova-pill-filter


### Option 2: Manage your Dashboards

In alternative you can use the [Nova Dashboard Manager](https://novapackages.com/packages/nova-bi/nova-dashboard-manager) which provides a ready to use User Interface to adminstrate dashboards. The packages has ready-to-use examples and guides how to implement your own metric-Widgets.

# Dashboard

```php
use DigitalCreative\NovaDashboard\Dashboard;

class ExampleDashboard extends Dashboard
{

    public static string $title = 'Example Dashboard';

    public function views(): array
    {
        return [
            ExampleView1::make()->authorizedToSee(fn() => true),
            ExampleView2::make()->editable()->private(),
            ExampleView3::make()->editable()
        ];
    }

    public function options(): array
    {
        return [
            'expandFilterByDefault' => true,
            'grid' => [
                'compact' => true,
                'numberOfCols' => 6
            ]
        ];
    }

}
```

Dashboard groups view, from this file you can customize some basic settings and define your views.

#### views()

Required an array of views.

#### options()

| key                   | type  | description                                                                                         |
|-----------------------|-------|-----------------------------------------------------------------------------------------------------|
| expandFilterByDefault | bool  | whether the filters should default to be expanded or collapsed by default                           |
| grid                  | array | See a full list of all available options here: https://vue-responsive-dash.netlify.app/api/#props-2 |


#### Hide dashboard from side menu

You can hide a dashboard, without losing functionlity (t's still accessible through apis or if you open it directly if you know the URL) using

```php
    NovaDashboard::make([...])->withoutNavigationMenu()
```

> Note: If you use CollapsibleResourceManager, you can link direcly to a dashboard by calling:

```php
use DigitalCreative\NovaDashboard\WidgetResource

    WidgetResource::make(MainDashboard::class)->label('Main Dashboard')),
```

# Views

There are 3 components that can be set within views (Widgets, Actions, Filters)

Example of a typical view class

```php
use DigitalCreative\NovaDashboard\View;

class MainView extends View
{

    public function filters(): array
    {
        return [
            new ExampleFilter()
        ];
    }

    public function actions(): array
    {
        return [
            new ExampleAction(),
        ];
    }

    public function widgets(): array
    {
        return [
            new ExampleWidget(),
        ];
    }

}
```

When creating a instance of a view there are several methods you can call to:

```php
public function views(): array
{
    return [
        ExampleView1::make()->authorizedToSee(fn() => true),
        ExampleView2::make()->editable()->private(),
        ExampleView3::make()->editable()
    ];
}
```

#### authorizedToSee

As every other resource (dashboard, view, widget, filter, action) there is an authorizedToSee method which determines if the 
current logged in user can see / execute the respective resource.

#### editable

Enabling this in conjunction with `Dynamic Widget mode` will allow the widgets on the view to be editable,

you can also pass a callback to determine if the current logged in user has access to this functionality, for example:

```php
ExampleView::make()->editable(fn() => false);
```

#### private

By default, every view is `Public` this means that every logged user can see all the widgets in it (unless if fails on the authorizeToSee Check),
however setting the view to private, every user will have their own set of widgets.
 
Combine this option with `editable` method to allow each user of your dashboard to freely be able to create widgets on
their own without one seen stuff from each other.

## Widgets

Widgets are the core functionality of this package and there are several ways for defining them:

##### Static mode

![Widgets Static Mode Preview](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/screenshots/widget-1.png)

```php
public function widgets(): array
{
    return [
        new ExampleWidget(0, 0, 2, 1, [ 'title' => 'Total Sales', 'source' => 'source1' ]),
        new ExampleWidget(2, 0, 2, 1, [ 'title' => 'Products in Stock', 'source' => 'source2' ]),
        new SomeOtherWidget(4, 0, 2, 1, [ 'title' => 'Conversion Rate', 'source' => 'source3' ]),
    ];
}
```

By passing `4` or `5` arguments to a widget it will be constructed in `Static Mode`, this means this widget cannot be
modified *dragged* or *resized* from within the dashboard itself, use this mode if you don't want to allow the user to modify
the default settings.

The arguments are:

```
int $x, int $y, int $width, int $height, array $options = []
```

##### Dynamic mode

![Widgets Static Mode Preview](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/screenshots/widget-2.png)

```php
public function widgets(): array
{
    return [
        new ExampleWidget(),
    ];
}
```

Simply pass no arguments to it, a new button will appear on the dashboard which will allow you to create as many widgets as you want.

> Note: While possible to mix Static and Dynamic Widgets the static ones gets shifted around due to the lack of a setting to lock an item on the grid,
> this will likely be fixed once the original author of the underlying grid library accepts the suggestion of developing such feature: https://github.com/bensladden/vue-responsive-dash/issues/196

## List of current available widgets:

- ChartJS Widget: [https://github.com/dcasia/chartjs-widget](https://github.com/dcasia/chartjs-widget)
- Value Widget: [https://github.com/dcasia/value-widget](https://github.com/dcasia/value-widget)
- [Add your widget here.](https://github.com/dcasia/nova-dashboard/edit/master/README.md)

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
    
    public function options(Request $request)
    {
        return [
            'Option 1' => 0,
            'Option 2' => 1,
            'Option 3' => 2,
        ];
    }

}
```

Usually your widget `resolveValue()` function will receive an instance of `\DigitalCreative\NovaDashboard\Filters` this class 
contains a method for retrieving the value of any given filter, for example:

```php
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\NovaDashboard\ValueResult;
use Illuminate\Support\Collection;

/**
 * On any widget class
 */
public function resolveValue(Collection $options, Filters $filters): ValueResult
{
    
    /**
     * On any widget class
     */
    $filterA = $filters->getFilterValue(YourFilterClass::class);
    $filterB = $filters->getFilterValue(YourSecondFilterClass::class);

    if($filterA === 'expected') {

        return new ValueResult(...);

    }

     return new ValueResult(...);

}
```

However, if you want to reuse the logic that you have previously set on your filters or share existing filters with
the dashboard you can call the method `applyToQueryBuilder` to get the same behavior:

```php
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\NovaDashboard\ValueResult;
use Illuminate\Support\Collection;

public function resolveValue(Collection $options, Filters $filters): ValueResult
{
    $result = $filters->applyToQueryBuilder(AnyEloquentModel::query())->get();    
}
```

`applyToQueryBuilder` will run every filter through the default filter logic of nova.

## Actions

Every action needs to extend the class: `DigitalCreative\NovaDashboard\Action` which has only 1 required method `->execute()`

```php
use DigitalCreative\NovaDashboard\Action;
use DigitalCreative\NovaDashboard\Filters;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class ExampleAction extends Action
{

    public function execute(ActionFields $fields, Filters $filters): ?array
    {
        return Action::message('You are awesome!');
    }

    public function fields(): array
    {
        return [
            Text::make('Some field'),
        ];
    }

}
```

The only difference between this action, and the default nova action are the method that will be called once executed,
in fact if you define a `->handle()` method this action can be used on every other nova resource as well.

## ⭐️ Show Your Support
Please give a ⭐️ if this project helped you!

## License

The MIT License (MIT). Please see [License File](https://raw.githubusercontent.com/dcasia/nova-dashboard/master/LICENSE) for more information.
