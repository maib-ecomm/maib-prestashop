[![N|Solid](https://www.maib.md/images/logo.svg)](https://www.maib.md)

# Maib Payment Gateway Module for Prestashop v. 8.x
Accept Visa / Mastercard / Apple Pay / Google Pay on your store with the Maib Payment Gateway Module for Prestashop v. 8.x

## Description
You can familiarize yourself with the integration steps and website requirements [here](https://docs.maibmerchants.md/en/integration-steps-and-requirements).

To test the integration, you will need access to a Test Project (Project ID / Project Secret / Signature Key). For this, please submit a request to the email address: ecom@maib.md.

To process real payments, you must have the e-commerce contract signed and complete at least one successful transaction using the Test Project data and test card details.

After signing the contract, you will receive access to the maibmerchants platform and be able to activate the Production Project.

## Functional
**Online payments**: Visa / Mastercard / Apple Pay / Google Pay.

**Three currencies**: MDL / USD / EUR (depending on your Project settings).

**Payment refund**: To refund the payment it is necessary to update the order status (see _refund.png_) to the selected status for _Refunded payment_ in **Maib Payment Gateway Module** extension settings (see _settings.png_). The payment amount will be returned to the customer's card.

## Requirements
- Registration on the maibmerchants.md
- Prestashop v. 8.x
- _curl_ and _json_ extensions enabled

## Installation
1. Download the extension file from Github or Prestashop repository.
2. In the Prestashop Admin Panel/Admin UI, go to _Modules > Module Manager_.
3. Click the _Upload a module_ button and select the extension file. Once the upload is complete, Prestashop will begin the installation process.
4. Under the _Payment_ section you will see a new added module **Maib Payment Gateway Module**.
5. Click the _Install_ button.
6. Click the _Configure_ button for extension settings.

## Settings
1. Project ID - Project ID from maibmerchants.md
2. Project Secret - Project Secret from maibmerchants.md. It is available after project activation.
3. Signature Key - Signature Key for validating notifications on Callback URL. It is available after project activation.
4. Ok URL / Fail URL / Callback URL - add links in the respective fields of the Project settings in maibmerchants.
5. Order status settings: Pending payment - Order status when payment is in pending.
6. Order status settings: Completed payment - Order status when payment is successfully completed.
7. Order status settings: Failed payment - Order status when payment failed.
8. Order status settings: Refunded payment - Order status when payment is refunded. For payment refund, update the order status to the this selected status (see _refund.png_).

## Troubleshooting
If you require further assistance, please don't hesitate to contact the **Maib Payment Gateway Module** ecommerce support team by sending an email to ecom@maib.md. 

In your email, make sure to include the following information:
- Merchant name
- Project ID
- Date and time of the transaction with errors
- Errors from log file