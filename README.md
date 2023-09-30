# Azure Email API Client

AKA "tiny-ace" in GitHub.

The Azure Email API Client is a single, tiny PHP class file which allows sending of emails via Azure Communication Services using an access token.

## Quick Start

1. Upload the file to your website.
1. Construct an instance of the `AzureEmailApiClient` class with various configuration options.
1. Call the `sendEmail` method to send emails via Azure.

## Example

```php
$mailer = new AzureEmailApiClient([
    'azureResourceName' => 'ace-dev-yours-01', // The name of the ACS resource in Azure.
    'azureRegion' => 'uk', // The region part of the full domain name I.E. {azureResourceName}.{azureRegion}.communication.azure.com.
    'accessKey' => 'access-key-goes-here', // This can be found by looking around the resource in Azure.
    'senderAddress' => 'someone@example.com', // This must be a defined entry for the verified domain in Azure ACS.
    'replyToAddress' => 'someone@example.com',
    'replyToDisplayName' => 'Someone'
]);

$mailer->sendEmail([
	'subject' => 'Hello World!',
	'htmlContent' => '<p>This is an email sent via ACS.</p>',
	'to' => [
		[ 'address' => 'someone_else@example.com', 'displayName' => 'Someone Else']
	]
]);
```

## Features

- Connect to ACS via access keys and send emails.
- The class handles the difficulties of sending an appropriate Authentication header to securely hash the access key.
- Supports plain text emails as well as HTML emails.

## License

The Azure Email API client was written by Nick Hill and is released under the MIT license. See LICENSE for more information.