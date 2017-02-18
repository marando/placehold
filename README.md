# placehold


## Installation

```shell
composer require marando/placehold
```


## Running the Example

```php
php -S localhost:8000
```

Then go to [http://localhost:8000](http://localhost:8000)



## Usage

```php
use \Marando\Placehold\Placehold;
```

```html
<img src="<? echo new Placehold(800, 600) ?>">
```

## Options

```php
new Placehold(
    $width, 
    $height, 
    $bgColor, 
    $fgcolor, 
    $text, 
    $font
);
```