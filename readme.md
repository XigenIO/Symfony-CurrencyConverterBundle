# Currency Converter
Currency Converter bundle for Symfony

Provides an easy way to convert an amount of GBP to common currencies within a Symfony project using the [fixer.io][fixer-io] API. Rates are stored in the symfony cache for a maximum of 4 hours before being updated automaticly.

## Installation
You can install this bundle via composer. You will need to manually add the repository by placing this in your projects composer.json file:
```json
{

"repositories": {
    "bundle-CurrencyConverter": {
        "type": "vcs",
        "url": "https://git.xigen.co.uk/Symfony-Bundles/CurrencyConverter.git"
    }
}

}
```

and then require the bundle as normal.

```bash
composer require xigen/currency-converter
```

Finally register the bundle in `app/AppKernel.php`:
```php
$bundles = [
    [...]
    new Xigen\Bundle\CurrencyConverterBundle\CurrencyConverterBundle(),
];
```

## Usage

```php
// Fetch the convert service
$convert = $this->getContainer()->get('currency_converter.convert');

// Convert 10 euros into pounds
$pounds = $convert->from('EUR', 10);

// Convert 10 pounds into euros
$euros = $convert->to('EUR', 10);

dump([
    'pounds' => $pounds,
    'euros' => $euros,
]);
```

[fixer-io]: http://fixer.io
