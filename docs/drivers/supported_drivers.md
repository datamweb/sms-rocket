The **CodeIgniter 4 SMSRocket** package supports the following SMS drivers, allowing you to choose the one that best fits your project requirements. Below is a list of the supported drivers along with their testing status.

| Driver        | Description                                         | Site                | Status         |
| ------------- | --------------------------------------------------- | ------------------- | -------------- |
| Twilio        | A popular cloud communication platform for SMS.     | www.twilio.com      | 🔄 In Progress |
| Amootsms      | A leading Iranian SMS platform with versatile APIs. | www.amootsms.com    | ✅ Tested      |
| FarazSMS      | A leading Iranian SMS platform with versatile APIs. | www.farazsms.com    | ✅ Tested      |
| Idehpardazan  | A leading Iranian SMS platform with versatile APIs. | www.sms.ir          | ✅ Tested      |
| Custom Driver | Allows for integration of custom SMS providers.     |    ---              | ✅ Tested      |


!!! note "Testing Status"

    - ✅ **Tested:** The driver has been thoroughly tested and is functioning as expected.
    - 🔄 **In Progress:** The driver has been created based on the provider’s technical documentation but has not yet been tested in a live environment.


### How to Add a New Driver

In addition to the list of supported drivers above, SMSRocket allows you to create a custom driver for any SMS provider. This flexibility lets you integrate with virtually any SMS service that meets your needs.

To add a new custom driver, please refer to this [guide](../create_custom_driver.md) for detailed instructions.

