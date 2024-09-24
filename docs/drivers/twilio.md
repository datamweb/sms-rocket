The `Twilio` driver allows you to send SMS through the [twilio.com](https://www.twilio.com) service. This driver provides functionality for sending simple SMS, tracking delivery status, get credit balance.

### Configuration

To use the `Twilio` driver, you need to configure your settings properly. You can set these configuration values in two ways: through the **.env** file or the **App\Config\SMSRocketConfig.php** config file.

=== ".env"

    The `.env` file is the primary way to configure **sensitive information** such as API keys and other settings. This file should be located in the root of your CodeIgniter4 project.
    
    ```env
    # https://console.twilio.com/us1/account/keys-credentials/api-keys
    TWILIO_SMS_ACCOUNT_SID =
    TWILIO_SMS_AUTH_TOKEN =
    TWILIO_SMS_SENDER =
    TWILIO_SMS_MESSAGING_SERVICE_SID =
    ```

=== "Config\SMSRocketConfig.php"

    Alternatively, you can set your configuration directly in the **SMSRocketConfig.php** file located in the app/Config directory of your CodeIgniter4 project. This method is suitable for non-sensitive configurations.

    ```php
    <?php

    declare(strict_types=1);

    namespace Config;

    use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
    use App\SMSRocket\Drivers\CustomSMSDriver;

    class SMSRocketConfig extends OriginalSMSRocketConfig
    {
        /**
         * Constructor for SMSRocketConfig to load environment variables.
         */
        public function __construct()
        {
            parent::__construct();

            $this->drivers['twilio'] = [
                'class'  => TwilioDriver::class,
                'config' => [
                    'AccountSID'          => env('TWILIO_SMS_ACCOUNT_SID', 'ACXXXXXX'), // https://console.twilio.com/us1/account/keys-credentials/api-keys
                    'AuthToken'           => env('TWILIO_SMS_AUTH_TOKEN', '961297XXXXX'),
                    'defaultSender'       => env('TWILIO_SMS_SENDER', '+120XXXXXX'),
                    'messagingServiceSid' => env('TWILIO_SMS_MESSAGING_SERVICE_SID', 'MGXXXXX'), //required if defaultSender(From) is not passed
                    'isAvailable'         => true,
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

$twilio = $smsService->driver('twilio')
        ->setSender('+120XXXXXX')
        ->setReceiver('+9809118840000')
        ->setMessage('Ticket #20 has been created.')
        ->send();
```

#### Tracking Delivery Status:

You can track the delivery status of a sent SMS using the `getDeliveryStatus()` method:

```php
$message_id = $twilio->getMessageId();

$status = $smsService->getDeliveryStatus($message_id);
echo $status; // Outputs the delivery status
```


#### Checking Your SMS Credit Balance

To get the credit balance, you simply call the `getCredit()` method on the initialized driver object.

```php
$balance = $smsService->driver('twilio')->getCredit();

echo "Remaining credit: {$balance}";
```