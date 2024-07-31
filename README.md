# eml-builder
A pure php library for building EML files


### Getting Started

Install package from packagist by composer
```
composer require mailmug/eml-builder
```

### Sample code
```php
<?php 
namespace Mailmug\EmlBuilder;

require 'vendor/autoload.php';


$data = '{
  "from": "no-reply@bar.com",
  "to": {
    "name": "Foo Bar",
    "email": "foo@bar.com"
  },
  "subject": "Winter promotions",
  "text": "Lorem ipsum..."
}';

$builder = new EmlBuilder;
$data = json_decode( $data );
echo $builder->build( $data );
```

### Output

```eml
Subject: Winter promotions
From: no-reply@bar.com
To: "Foo Bar" <foo@bar.com>
Content-Type: multipart/mixed;
boundary="----=W1yqQ2FfqKrjhD4fKXphx6pfu6Z5nWkmYmTm5BvGk"

------=W1yqQ2FfqKrjhD4fKXphx6pfu6Z5nWkmYmTm5BvGk
Content-Type: text/plain; charset=utf-8

Lorem ipsum...
```
