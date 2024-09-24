### IDApardaz Driver

The **Idehpardazan** driver allows you to send SMS through the [amootsms](https://sms.ir) service. This driver provides functionality for sending simple SMS, tracking delivery status, getting credit balance, and sending Patterned SMS.

### Configuration

To use the **Idehpardazan** driver, you need to configure your settings properly. You can set these configuration values in two ways: through the **.env** file or the **App\Config\SMSRocketConfig.php** config file.

=== ".env"

    The `.env` file is the primary way to configure **sensitive information** such as API keys and other settings. This file should be located in the root of your CodeIgniter4 project.

    ```env
    # https://panel.idapardaz.com/client/APIToken
    SMSIR_SMS_API_KEY = Your API key
    SMSIR_SMS_SENDER = 
    ```

=== "Config\SMSRocketConfig.php"

    Alternatively, you can set your configuration directly in the **SMSRocketConfig.php** file located in the app/Config directory of your CodeIgniter4 project. This method is suitable for non-sensitive configurations.

    ```php
    <?php

    declare(strict_types=1);

    namespace Config;

    use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
    use Datamweb\SMSRocket\Drivers\IdehpardazanDriver;

    class SMSRocketConfig extends OriginalSMSRocketConfig
    {
        /**
         * Constructor for SMSRocketConfig to load environment variables.
         */
        public function __construct()
        {
            parent::__construct();

            $this->drivers['smsir'] = [
                'class'  => IdehpardazanDriver::class,
                'config' => [
                    'api_key'       => env('SMSIR_SMS_API_KEY', 'Enter your sms.ir api key here'),
                    'defaultSender' => env('SMSIR_SMS_SENDER', '3000XXXXX'),
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

$smsir = $smsService->driver('smsir')
    ->setSender('public')
    ->setReceiver('09123456789')
    ->setMessage('Your appointment is confirmed.')
    ->send();
```

#### Tracking Delivery Status:

You can track the delivery status of a sent SMS using the `getDeliveryStatus()` method:

```php
$message_id = $smsir->getMessageId();

$status = $smsService->getDeliveryStatus($message_id);
echo $status; // Outputs the delivery status
```

#### Sending a Patterned SMS

To send an SMS with a specific pattern, you can use the `sendPatterned()` method provided by the SMSRocket service. This allows you to send predefined template-based SMS with custom data.

```php
$smsir = $smsService->driver('smsir')
    ->setPattern('5023') // Set the pattern code
    ->setPatternData([    // Provide the necessary data for the pattern
        'verification_code' => '5678',
    ])
    ->send('09123456789');

if($smsir->isOK()){
    echo "Your patterned SMS was successfully sent. Message ID: {$idapardaz->getMessageId()}";
}
```

#### Checking Your SMS Credit Balance

To get the credit balance, call the `getCredit()` method on the initialized driver object:

```php
$balance = $smsService->driver('smsir')->getCredit();
echo "Remaining credit: {$balance}";
```