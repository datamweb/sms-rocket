# Getting Started with SMSRocket

Welcome to **CodeIgniter4 SMSRocket**! This guide will help you start using the **SMSRocket** package in your CodeIgniter 4 project.

## Prerequisites

Before you begin, make sure you have the following:

- A CodeIgniter 4 project set up and running.
- PHP version **8.1** and above

## Configuration  

If you're using the **.env** file for setting up your SMS drivers, you can skip this step. However, if you'd prefer to manage your SMS drivers manually in a configuration file, you should create the following file at **app/Config/SMSRocketConfig.php** with the structure below:

```php
<?php

declare(strict_types=1);

namespace Config;

use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
use App\SMSRocket\Drivers\CustomSMSDriver;

class SMSRocketConfig extends OriginalSMSRocketConfig
{
    // ... other ...
    public bool $enableDBLogging = true;

    /**
     * Constructor for SMSRocketConfig to load environment variables.
     */
    public function __construct()
    {
        parent::__construct();

        // Register the custom SMS driver
        $this->drivers['custom'] = [
            'class' => CustomSMSDriver::class,
            'config' => [
                'api_key'       => env('CUSTOM_SMS_DRIVER_API_KEY', 'your-api-key'),
                'defaultSender' => env('CUSTOM_SMS_DRIVER_SMS_SENDER', '3000XXXX'),
                'isAvailable'   => true,
            ],
        ];

        // ... other drivers ...
    }
}
```

This configuration allows you to define custom drivers and load environment variables. If you're using the **.env** file, it's not necessary to create this file unless you need custom logic for drivers.

## Database Migration  

Since **SMSRocket** stores SMS history in the database, you need to run the migration to create the required table. 

Execute the following command to run the migration:  

```console  
php spark migrate -n Datamweb\SMSRocket
```  

## Usage

### Sending SMS

To send an SMS, use the `smsRocket` Service:

```php
use Datamweb\SMSRocket\Services\SMSRocketService;


/** @var SMSRocketService $smsService */
$smsService = service('smsRocket');

// Set driver
$response = $smsService->driver('twilio')

// Set sender and message
->setSender('YourSenderID')->setMessage('Hello, World!')

// Send SMS to a single recipient
->send('+1234567890');
// $response = $smsService->setReceiver('+1234567890')->send();

// Check response
if ($response->isOK()) {
     echo "Your SMS request has been successfully sent to the provider. {$response->getMessageId()}"; 
} else {
    echo "Failed to send SMS: " . $response->getMessage();
}
```

!!! note annotate "Note on Default Driver Configuration"

    To simplify SMS sending, you can set a **default driver** in the configuration file. Open **Config/SMSRocketConfig.php** and set:  

    ```php  
    public string $defaultDriver = 'twilio';  
    ```  

    With this setting, you can send SMS without calling `driver()` explicitly:  

    ```php  
    $response = $smsService  
        ->setSender('YourSenderID')  
        ->setMessage('Hello, Default World!')  
        ->send('1234567890');  
    ```  

    If needed, you can still override the default driver by using:  

    ```php  
    $response = $smsService->driver('custom')->send('1234567890');  
    ```  

    This setup improves code clarity while maintaining flexibility for different providers.


### Sending to Multiple Recipients

You can also send SMS to multiple recipients:

```php
$responses = $smsService->send(['1234567890', '0987654321'], 'Hello, World!');

// Process responses
foreach ($responses as $number => $response) {
    echo "Response for {$number}: " . $response->getMessage() . PHP_EOL;
}
```

!!! note annotate "NOT Intended for Bulk SMS"

    This method is not intended for bulk SMS sending. In fact, it sends a separate request to the provider for each recipient number. Therefore, we do not recommend using this approach for sending a large volume of messages, as it may lead to hitting rate limits imposed by the provider.