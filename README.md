<p align="center">
  <img src="https://bsecure-dev.s3-eu-west-1.amazonaws.com/dev/react_app/assets/secure_logo.png" width="400px" position="center">
</p>



[![Latest Version on Packagist](https://img.shields.io/packagist/v/bsecure/bsecure-php.svg?style=flat-square)](https://packagist.org/packages/bsecure/bsecure-php)
[![Latest Stable Version](https://poser.pugx.org/bsecure/bsecure-php/v)](//packagist.org/packages/bsecure/bsecure-php) 
[![Total Downloads](https://img.shields.io/packagist/dt/bsecure/bsecure-php.svg?style=flat-square)](https://packagist.org/packages/bsecure/bsecure-php)
[![License](https://poser.pugx.org/bsecure/bsecure-php/license)](//packagist.org/packages/bsecure/bsecure-php)
[![Build Status](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bSecureCheckout/bsecure-php/?branch=master)

bSecure 
=========================
bSecure is a utility library for two-click checkout custom integration. bSecure library simplifies communication between builder and bSecure server and processing tasks for builder's ease.

### About bSecure Checkout ##

It gives you an option to enable *universal-login*, *two-click checkout* and accept multiple payment method for your customers, as well as run your e-commerce store hassle free.\
It is built for *desktop*, *tablet*, and *mobile devices* and is continuously tested and updated to offer a frictionless payment experience for your e-commerce store.


### Manual Installation
To use the bindings, include the init.php file.

`` require_once('/path/to/bSecure-php/init.php')``

**Prerequisites** 

The bindings require the following extensions in order to work properly:
* curl

If you want to install the package manually, you'll have to make sure that these extensions are available.

### Documentation
Visit our Site  [bSecure](https://www.bsecure.pk/) to read the documentation and get support.

### Configuration Setup

By following a few simple steps, you can set up your **bSecure Checkout** and **Single-Sign-On**. 

#### Getting Your Credentials

1. Go to [Builder's Portal](https://builder.bsecure.pk/)
2. [App Integration](https://builder.bsecure.pk/integration-sandbox) >> Sandbox / Live
3. Select Environment Type (Custom Integration)
4. Fill following fields:\
    a. *Store URL* its required in any case\
    b. *Login Redirect URL* Required for feature **Login with bSecure**\
    c. *Checkout Redirect URL* Required for feature **Pay with bSecure**\
    d. *Checkout Order Status webhook* Required for feature **Pay with bSecure**
5. Save your client credentials ('<YOUR-CLIENT-ID> and <YOUR-CLIENT-SECRET>')
6. Please make sure to keep credentials at safe place in your code.


## Client Authentication
To call below mentioned functions of bSecure, you first have to authenticate your client.
Following function will be used to authenticate your client:

```php
\bSecure\bSecure::setClientId('<YOUR-CLIENT-ID>');
\bSecure\bSecure::setClientSecret('<YOUR-CLIENT-SECRET>');
\bSecure\bSecure::setAppEnvironment('<YOUR-APP-ENVIRONMENT>');
\bSecure\bSecure::getAuthToken();
```
``
<YOUR-CLIENT-ID> and <YOUR-CLIENT-SECRET> can be obtained from Builder's Portal for your application.
``

We suggest to call this function before each of the following to keep authentication updated:
* create_order
* order_status
* get_customer_profile

  
## bSecure Checkout

#### Create Order
To create an order you should have an order_id, customer, charges and products object parameters that are to be set before creating an order.
##### Create Order Request Params:

###### Product Object:

Products object should be in below mentioned format:

```
"products": [
  {
  "id": "product-id",
  "name": "product-name",
  "sku": "product-sku",
  "quantity": 0,
  "price": 0,
  "sale_price": 0,
  "image": "product-image",
  "description": "product-description",
  "short_description": "product-short-description"
  }
]
```

###### Shipment Object

Shipment object should be in below mentioned format:

>1- If the merchant want his pre-specified shipment method then he should pass shipment method detail in below mentioned format:  
```
"shipment": {
  "charges": "numeric",
  "method_name": "string"
}
```

###### Customer Object

Customer object should be in below mentioned format:

>1- If the customer has already signed-in via bSecure into your system and you have auth-code for the customer you can
just pass that code in the customer object no need for the rest of the fields.

>2- Since all the fields in Customer object are optional, if you don’t have any information about customer just pass the
empty object, or if you have customer details then your customer object should be in below mentioned format:
```
"customer": {
  "name": "string",
  "email": "string",
  "country_code": "string",
  "phone_number": "string",
}
```
###### Charges Object

Charges object should be in below mentioned format:

```
"order_charge" : {
  "sub_total" : "float",
  "discount" : "float",
  "total" : "float",
}
```

#### Create Order
```php
\bSecure\Order::setOrderId('<YOUR-ORDER-ID>');
\bSecure\Order::setCustomer($customer);
\bSecure\Order::setShipmentDetails($shipment);
\bSecure\Order::setCartItems($products);
\bSecure\Order::setCharges($charges);
$result = \bSecure\Order::createOrder();
return $result;
```

In response createOrder(), will return order expiry, checkout_url, order_reference and merchant_order_id.
```
array (
  'expiry' => '2020-11-27 10:55:14',
  'checkout_url' => 'bSecure-checkout-url',
  'store_url' => 'store-url',
  'order_reference' => 'bsecure-reference',
  'merchant_order_id' => '<YOUR-ORDER-ID>',
) 
```
>If you are using a web-solution then simply redirect the user to checkout_url
```
if(!empty($result['checkout_url']))
return redirect($result['checkout_url']); 
```
When order is created successfully on bSecure, you will be redirected to bSecure checkout app where you will process your checkout.

>If you have Android or IOS SDK then initialize your sdk and provide order_reference,checkout_url and store_url to it
```
if(!empty($result']))
return $result; 
```


#### Callback on Order Placement
Once the order is successfully placed, bSecure will redirect the customer to the url you mentioned in “Checkout
redirect url” in your [environment settings](https://builder.bsecure.pk/) in [Builder's Portal](https://builder.bsecure.pk/), with one additional param **order_ref** in the query
string.

#### Order Updates
By using order_ref you received in the "**[Callback on Order Placement](#callback-on-order-placement)**" you can call below method to get order details.

```php
$order_ref = $order->order_ref;

$result =  \bSecure\Order::orderStatus($order_ref);
return $result;
```

#### Order Status Change Webhook
Whenever there is any change in order status or payment status, bSecure will send you an update with complete
order details (contents will be the same as response of *[Order Updates](#order-updates)* on the URL you mentioned in *Checkout Order Status webhook* in your environment settings in [Builder's Portal](https://builder.bsecure.pk/). (your webhook must be able to accept POST request).

In response of "**[Callback on Order Placement](#callback-on-order-placement)**" and "**[Order Updates](#order-updates)**" you will recieve complete details of your order in below mentioned format:

```
{
  "status": 200,
  "message": [
    "Request Successful"
  ],
  "body": {
    "merchant_order_id": "your-order-id",
    "order_ref": "bsecure-order-reference",
    "order_type": "App/Manual/Payment gateway",
    "placement_status": "6",
    "payment_status": null,
    "customer": {
      "name": "",
      "email": "",
      "country_code": "",
      "phone_number": "",
      "gender": "",
      "dob": ""
    },
    "payment_method": {
      "id": 5,
      "name": "Debit/Credit Card"
    },
    "card_details": {
      "card_type": null,
      "card_number": null,
      "card_expire": null,
      "card_name": null
    },
    "delivery_address": {
      "country": "",
      "province": "",
      "city": "",
      "area": "",
      "address": "",
      "lat": "",
      "long": ""
    },
    "shipment_method": {
      "id": 0,
      "name": "",
      "description": "",
      "cost": 0
    },
    "items": [
      {
        "product_id": "",
        "product_name": "",
        "product_sku": "",
        "product_qty": ""
      },
    ],
    "created_at": "",
    "time_zone": "",
    "summary": {
      "total_amount": "",
      "sub_total_amount": "",
      "discount_amount": "",
      "shipment_cost": "",
      "merchant_service_charges": ""
    }
  },
  "exception": null
}

```

### Managing Orders and Payments

#### Payment Status

| ID  | Value     | Description                                                         |
| :-: | :-------- | :------------------------------------------------------------------ |
|  0  | Pending   | Order received, no payment initiated.Awaiting payment (unpaid).     |
|  1  | Completed | Order fulfilled and complete. Payment also recieved.                |
|  2  | Failed    | Payment failed or was declined (unpaid) or requires authentication. |

#### Order Status

| ID  | Value                 | Description                                                                                                                       |
| :-: | :-------------------- | :-------------------------------------------------------------------------------------------------------------------------------- |
|  1  | Created               | A customer created an order but not landed on bsecure                                                                             |
|  2  | Initiated             | Order is awaiting fulfillment.                                                                                                    |
|  3  | Placed                | Order fulfilled and complete Payment received. Order is awaiting fulfillment. – requires no further action                        |
|  4  | Awaiting Confirmation | Awaiting action by the customer to authenticate the transaction.                                                                  |
|  5  | Canceled              | Canceled by an admin or the customer.                                                                                             |
|  6  | Expired               | Orders that were not fulfilled within a pre-specified timeframe. timeframe                                                        |
|  7  | Failed                | Payment failed or was declined (unpaid).Note that this status may not show immediately and instead show as Pending until verified |
|  8  | Awaiting Payment      | Order received, no payment initiated. Awaiting payment (unpaid)                                                                   |


## bSecure Single Sign On (SSO)

### Authenticate Client
You can authenticate your client by calling below mentioned function

Save the provided **state** as you will receive the same state in the successful authorization callback.
```php
$client = bSecure\SSO::clientAuthenticate($state);
return redirect($client['redirect_url']); 

```
In response clientAuthenticate(), will return a redirect_url, on which you have to redirect your customer.
```
array (
  "redirect_url": "<SSO-REDIRECT-LINK>",
)
```

### Client Authorization
On Successful Authorization,\
bSecure will redirect to <LOGIN-REDIRECT-LINK> you provided when setting up environment in Builders portal, along
with two parameters in query string: **code** and **state**
```
eg: https://my-store.com/sso-callback?code=abc&state=xyz
```
code received in above callback is customer's auth_code which will be further used to get customer profile.

#### Verify Callback
Verify the state you received in the callback by matching it to the value you stored in DB before sending the client authentication
request, you should only proceed if and only if both values match.

### Get Customer Profile
Auth_code recieved from **[Client Authorization](#client-authorization)** should be passed to method below to get customer profile. 


```php
return bSecure\SSO::customerProfile('<AUTH-CODE>');
```

In response, it will return customer name, email, phone_number, country_code, address book.
```
array (
    'name' => 'customer-name',
    'email' => 'customer-email',
    'phone_number' => 'customer-phone-number',
    'country_code' => customer-phone-code,
    'address' => 
        array (
          'country' => '',
          'state' => '',
          'city' => '',
          'area' => '',
          'address' => '',
          'postal_code' => '',
        ),
)
```
### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributions

**"bSecure – Your Universal Checkout"** is open source software.
