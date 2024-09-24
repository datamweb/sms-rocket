The **AmootSMS** driver allows you to send SMS through the [amootsms](https://amootsms.com) service. This driver provides functionality for sending simple SMS, tracking delivery status, get credit balance, and sending Patterned SMS.

### Configuration

To use the `AmootSMS` driver, you need to configure your settings properly. You can set these configuration values in two ways: through the **.env** file or the **App\Config\SMSRocketConfig.php** config file.

=== ".env"

    The **.env** file is the primary way to configure **sensitive information** such as API keys and other settings. This file should be located in the root of your CodeIgniter4 project.
    
    ```env
    # https://portal.amootsms.com/client/APIToken
    AMOOTSMS_SMS_API_KEY = Your API key 
    AMOOTSMS_SMS_SENDER = public
    ```

=== "Config\SMSRocketConfig.php"

    Alternatively, you can set your configuration directly in the **SMSRocketConfig.php** file located in the app/Config directory of your CodeIgniter4 project. This method is suitable for non-sensitive configurations.

    ```php
    <?php

    declare(strict_types=1);

    namespace Config;

    use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
    use Datamweb\SMSRocket\Drivers\AmootsmsDriver;

    class SMSRocketConfig extends OriginalSMSRocketConfig
    {
        /**
         * Constructor for SMSRocketConfig to load environment variables.
         */
        public function __construct()
        {
            parent::__construct();

            $this->drivers['custom'] = [
                'class'  => AmootsmsDriver::class,
                'config' => [
                    // https://portal.amootsms.com/client/APIToken
                    'token'         => env('AMOOTSMS_SMS_API_KEY', 'Enter your Amootsms.com token here'), 
                    'defaultSender' => env('AMOOTSMS_SMS_SENDER', 'public'),
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

$amootsms = $smsService->driver('amootsms')

->setSender('public')
->setReceiver('09118840000')
->setMessage('Ticket #20 has been created.')
->send();
```

#### Tracking Delivery Status:

You can track the delivery status of a sent SMS using the `getDeliveryStatus()` method:

```php

$message_id = $amootsms->getMessageId();

$status = $smsService->getDeliveryStatus($message_id);
echo $status; // Outputs the delivery status
```

#### Sending a Patterned SMS

In modern communication, sending SMS messages with specific patterns can greatly enhance user engagement and response rates. The SMSRocket package provides a convenient method for sending patterned SMS, allowing developers to customize their messages based on predefined templates. This capability is particularly useful for notifications, alerts, and promotional messages where specific formatting is required.

To send an SMS with a specific pattern, you can utilize the sendPatterned method provided by the SMSRocket service. This method enables you to specify a pattern code that corresponds to a template defined in your SMS provider's system. Additionally, you can pass any necessary data that the template requires. Here’s how to implement it in your code:

```php
$amootsms= $smsService->driver('amootsms')
->setPattern('2218') // Set the pattern code to identify the template// Set the pattern code to identify the template
->setPatternData([ // Provide the necessary data for the pattern
    'opt_code'=> '1245',
])
->send('09118840000');

if($amootsms->isOK()){
    echo "Your SMS request has been successfully sent to the AmootSms provider. {$amootsms->getMessageId()}";    
}
```

#### Checking Your SMS Credit Balance

To get the credit balance, you simply call the `getCredit()` method on the initialized driver object.

```php
$balance = $smsService->driver('amootsms')
            ->getCredit();
echo "Remaining credit: {$balance}";
```