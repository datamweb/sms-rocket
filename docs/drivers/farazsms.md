### FarazSMS Driver

The `FarazSMS` driver allows you to send SMS through the **farazsms.com** service. This driver provides functionality for sending simple SMS, tracking delivery status, getting credit balance, and sending Patterned SMS.

### Configuration

To use the **farazsms** driver, you need to configure your settings properly. You can set these configuration values in two ways: through the **.env** file or the **Config\SMSRocketConfig.php** config file.

=== ".env"

    The `.env` file is the primary way to configure **sensitive information** such as API keys and other settings. This file should be located in the root of your CodeIgniter4 project.

    ```env
    FARAZSMS_DRIVER_API_KEY = Your API key from https://panel.farazsms.com/client/APIToken
    ```

=== "Config\SMSRocketConfig.php"

    Alternatively, you can set your configuration directly in the **SMSRocketConfig.php** file located in the app/Config directory of your CodeIgniter4 project. This method is suitable for non-sensitive configurations.

    ```php
    <?php

    declare(strict_types=1);

    namespace Config;

    use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
    use Datamweb\SMSRocket\Drivers\FarazSMSDriver;

    class SMSRocketConfig extends OriginalSMSRocketConfig
    {
        /**
         * Constructor for SMSRocketConfig to load environment variables.
         */
        public function __construct()
        {
            parent::__construct();

            $this->drivers['farazsms'] = [
                'class' => FarazSMSDriver::class,
                'config' => [
                    'api_key'       => env('FARAZSMS_SMS_API_KEY', 'your-api-key'),
                    'defaultSender' => env('FARAZSMS_SMS_SENDER', '5000XXXX'),
                    'isAvailable'   => true,
                ],
            ];
            // ... other drivers ...
        }
    }
    ```

!!! note annotate "Choosing Between **.env** and **SMSRocketConfig.php**"

    It is recommended to use the **.env** file for sensitive information to keep it secure and separate from the codebase.
    Use the **SMSRocketConfig.php** file for general configurations that are not sensitive and can be hardcoded into the application.

### Usage Example

#### Sending a Simple SMS:

To send a simple SMS using the send method:

```php
/** @var SMSRocketService $smsService */
$smsService = service('smsRocket');

$farazsms = $smsService->driver('farazsms')
    ->setSender('500041XXXXX')
    ->setReceiver('09123450000')
    ->setMessage('Your order has been confirmed.')
    ->send();
```

#### Tracking Delivery Status:

You can track the delivery status of a sent SMS using the `getDeliveryStatus()` method:

```php
$message_id = $farazsms->getMessageId();

$status = $smsService->getDeliveryStatus($message_id);
echo $status; // Outputs the delivery status
```

#### Sending a Patterned SMS

To send an SMS with a specific pattern, you can use the `sendPatterned()` method provided by the SMSRocket service. This allows you to send predefined template-based SMS with custom data.

```php
$farazsms = $smsService->driver('farazsms')
    ->setPattern('3021') // Set the pattern code
    ->setPatternData([    // Provide the necessary data for the pattern
        'order_code' => '1234',
    ])
    ->send('09123456789');

if($farazsms->isOK()){
    echo "Your patterned SMS was successfully sent. Message ID: {$farazsms->getMessageId()}";
}
```

#### Checking Your SMS Credit Balance

To get the credit balance, call the `getCredit()` method on the initialized driver object:

```php
$balance = $smsService->driver('farazsms')->getCredit();
echo "Remaining credit: {$balance}";
```