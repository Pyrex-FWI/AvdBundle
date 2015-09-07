# AvDistrict Downloader

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/Pyrex-FWI/DigitalDjPoolBundle.svg?branch=master)](https://travis-ci.org/Pyrex-FWI/DigitalDjPoolBundle)
[![Codacy Badge](https://www.codacy.com/project/badge/96ed127edb5c409e99550057b49025f0)](https://www.codacy.com/app/yemistikris/DigitalDjPoolBundle)

INSTALL
=======

Update your composer.json

```json
"require-dev": {
    "pyrex-fwi/avdistrict-bundle": "dev-master"
}
```

Update your AppKernel.php

```php
new DeejayPoolBundle\DeejayPoolBundle()
```

Add your account information into config.yml

```yaml
av_district:
    credentials:
        login:    %avd.credentials.login%
        password: %avd.credentials.password%

    configuration:
        root_path: %avd.configuration.root_path%

```

Console usages:
```yaml
 avd
  ddp:download              Download files from av district
```

#### Run tests

- vendor/bin/phpunit -c phpunit.xml --debug --verbose
- vendor/bin/phpunit -c phpunit.xml --debug --verbose --coverage-html Tests/TestData/Coverage
- vendor/bin/phpunit -c phpunit.xml --debug --verbose --coverage-html Tests/TestData/Coverage --debug --stop-on-error -v


http://gnugat.github.io/2014/10/29/sf2-bundle-standalone.html
