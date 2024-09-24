You can install the **SMSRocket** package in your CodeIgniter 4 project using either Composer or manual installation. Using Composer is the recommended way to install **SMSRocket** as it simplifies dependency management and package updates. However, if you have any issues with Composer, the manual installation method allows you to use the package as well.

=== "With Composer"

    Using Composer is the easiest and fastest way to install the package. Follow these steps:

    1. **Open Terminal or Command Prompt:**
      Open your terminal or Command Prompt and navigate to your CodeIgniter 4 project directory.

    2. **Run the Installation Command:**
      Execute the following command to install the *SMSRocket* package:

        ```console
        composer require datamweb/sms-rocket
        ```

        Composer will download the package and add it to your project.

    3. **Update Configuration (Optional):**
         After installation, the package files will be placed in the `vendor/` directory and will be automatically loaded by Composer. You may need to update your project configuration files as necessary.
    

=== "Manual Installation"

    If you cannot use Composer or prefer to install the package manually, follow these steps:

    1. Download the Package
      First, download the **SMSRocket** package manually.
      
          - Visit the package’s GitHub repository and download the [latest version](https://github.com/datamweb/sms-rocket/releases).
          - Alternatively, if you have the ZIP file of the package, copy it to your project directory.

    2. Extract Files
      Extract the ZIP file and place the contents in an appropriate directory. For example, you can copy it to `APPPATH . 'ThirdParty\sms-rocket\src'`.

    3. Update Autoloader
      To ensure the package is automatically loaded in your project, update the Autoloader configuration. Go to `app/Config/Autoload.php` and add the package namespace and path:

        ```php
        $psr4 = [
            'App'         => APPPATH,
            'Datamweb\\SMSRocket' =>  APPPATH . 'ThirdParty\sms-rocket\src'
        ];
        ```

    4. Configure the Package
      After adding the package, make sure to configure the necessary settings for SMSRocket (like setting up API Key and ...).
