# CodeIgniter4 SMSRocket

[![PHPUnit](https://github.com/datamweb/sms-rocket/actions/workflows/phpunit.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/phpunit.yml)
[![PHPCSFixer](https://github.com/datamweb/sms-rocket/actions/workflows/phpcsfixer.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/phpcsfixer.yml)
[![PHPStan](https://github.com/datamweb/sms-rocket/actions/workflows/phpstan.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/phpstan.yml)
[![Psalm](https://github.com/datamweb/sms-rocket/actions/workflows/psalm.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/psalm.yml)
[![Rector](https://github.com/datamweb/sms-rocket/actions/workflows/rector.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/rector.yml)
[![PHPCPD](https://github.com/datamweb/sms-rocket/actions/workflows/phpcpd.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/phpcpd.yml)
[![Deptrac](https://github.com/datamweb/sms-rocket/actions/workflows/deptrac.yml/badge.svg)](https://github.com/datamweb/sms-rocket/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/datamweb/sms-rocket/badge.svg?branch=develop)](https://coveralls.io/github/datamweb/sms-rocket?branch=develop)

---

[![Latest Stable Version](https://poser.pugx.org/datamweb/sms-rocket/v?style=for-the-badge)](https://packagist.org/packages/datamweb/sms-rocket) [![Total Downloads](https://poser.pugx.org/datamweb/sms-rocket/downloads?style=for-the-badge)](https://packagist.org/packages/datamweb/sms-rocket) [![Latest Unstable Version](https://poser.pugx.org/datamweb/sms-rocket/v/unstable?style=for-the-badge)](https://packagist.org/packages/datamweb/sms-rocket) [![License](https://poser.pugx.org/datamweb/sms-rocket/license?style=for-the-badge)](https://packagist.org/packages/datamweb/sms-rocket) [![PHP Version Require](https://poser.pugx.org/datamweb/sms-rocket/require/php?style=for-the-badge)](https://packagist.org/packages/datamweb/sms-rocket)
 
The **CodeIgniter 4 SMSRocket** package was developed to tackle recurring issues with SMS integration across various CodeIgniter 4 projects. Having personally encountered these challenges in multiple projects, I created this package to offer a structured and unified solution. SMSRocket includes features such as support for multiple drivers, automatic user phone detection for seamless integration with CodeIgniter Shield, caching, retry mechanisms for failed message sending attempts and multiple messaging all designed to make SMS handling in your projects smoother and more efficient.

One of the key features of this package is the ability to configure **custom drivers**. This allows developers to easily implement their own drivers if they need to work with specific SMS providers or internal solutions. This flexibility ensures that SMSRocket can adapt to any SMS provider and be tailored to the specific needs of your project.

## Features

- **Multi-driver support:** Easily switch between different SMS providers.
- **Caching:** Caches SMS responses to reduce redundant requests.
- **Logging:** Logs SMS sending operations for easy debugging and monitoring.
- **Retry Mechanism:** Automatically retries failed message sending attempts.
- **Multiple Messaging:** Supports sending SMS to multiple recipients at once.
- **User Integration:** Automatically detects the phone number field from `User` models (integration with CodeIgniter Shield).
- **Data History:** Maintains a complete history of SMS transactions in the database for future reference and analysis.
- **Sensitive Data Handling:** Provides functionality to obfuscate sensitive information before storing it in the database to enhance security and privacy.

## Customization & Flexibility

- **Custom Drivers**: If the existing drivers do not meet your needs, you can easily add new SMS drivers by implementing the `SMSDriverInterface`. This allows you to integrate any SMS provider, either through APIs or other mechanisms, offering complete control over how messages are sent.
  
- **Configurable Drivers**: Each driver can be customized individually with its own set of configuration options, allowing you to fine-tune settings like API keys, default senders, and availability of the drivers for specific environments (e.g., production vs. testing).

- **Integration with Existing Systems**: The package is designed to be highly modular and easy to integrate with other packages or libraries in CodeIgniter. Whether you need to integrate SMS functionality into a larger notification system or an e-commerce platform, SMSRocket can be extended and customized as needed.

## Documentation

For a comprehensive overview of the package, including setup, configuration, usage examples, and advanced features, please refer to the official documentation. All essential details are covered to help you get started smoothly.

Explore the documentation [here](https://smsrocket.codeigniter4.ir/).

## Installation

To install the package via Composer, run:

```console
composer require datamweb/sms-rocket
```

For more information, please refer to [installation](docs/installation.md).

## Acknowledgements

I believe that the **CodeIgniter4** framework has not received the attention it deserves from the developer community. Therefore, it is up to all of us to contribute and build a strong and useful community around this framework. One effective way to do this is by submitting **Pull Requests** to add SMS drivers from different countries, helping to improve and expand **SMSRocket**. By doing so, we can turn this package into a comprehensive and valuable tool for all users and strengthen the **CodeIgniter4** ecosystem along the way.

Every open-source project depends on its contributors to be a success. The following users have
contributed in one manner or another in making CodeIgniter4 SMSRocket:

<a href="https://github.com/datamweb/sms-rocket/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=datamweb/sms-rocket" alt="Contributors">
</a>

Made with [contrib.rocks](https://contrib.rocks).
