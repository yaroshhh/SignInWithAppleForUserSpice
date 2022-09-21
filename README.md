# Sign In with Apple for UserSpice by Yaron Helfer

This plugin adds Apple login feature into login page of UserSpice version 5.4.4 and later.

## Requirements
- [PHP](https://www.php.net/downloads.php#v7.4.30 "PHP 7.4") version 7.4 or later
- [UserSpice](https://userspice.com/ "UserSpice") 5.4.4 or later
- PHP [Composer](https://getcomposer.org/ "Composer") (for vendor package installation)
- One of the following PHP Extensions: [bcmath](https://www.php.net/manual/en/book.bc.php "bcmath Extension") or [GPM](https://www.php.net/manual/en/intro.gmp.php "GPM Extension")
- [Apple Developer Program](https://developer.apple.com/ "Apple Developer Program") (Usually costs about 99 USD annually)

## Pre-Installation
 - Prepare the following credentials for the Apple sign in integration:
    - `CLIENT_ID` - represents the Identity of the App in you Apple developer account (for instance: com.example.app).
    - `TEAM_ID` - the ID associated with your Apple Developer Program
    - `KEY_ID` - Create a unique key for the *Sign in with Apple* service
    - `CERT_PATH` - Create and download a certificate for the *Sign in with Apple* service. place it on your server and specify the path to the certificate file.

    For more information and better instruction go to the [5 minutes code guide](httpshttps://arfasoftech.com/blog/How-to-Integrate-Sign-in-with-Apple-in-PHP-(5-minute-code) "How to Integrate Sign in with Apple in PHP (5 minute code)")

## Installation

1. Place the plugin folder in the following path */usersc/plugins/apple_login*
2. Run `composer install` to add all the vendor folders
3. Login to your *UserSpice* as an Admin
4. Go to the plugin configurations page in *UserSpice Dashboard* and fill in all the required settings 
5. Go to your *Apple Developer Program* and add the path to the redirect page to the allowed URLs and domain list in your *Sign in with Apple* service
6. Finally activate the plugin and test the new Sign in button

## Support and contact
Feel free to reach out and send some feedback to [Yaron Helfer](mailto:yaronhelfer@gmail.com).

## Copyrights
This plugin made by [Yaron Helfer](https://yaronhelfer.com/ "Yaron Helfer's website") and free for use.