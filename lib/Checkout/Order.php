<?php

namespace bSecure;
use bSecure\Helpers\Constant;

/**
 * Class bSecure.
 */
class Order
{
    /** @var string The merchant order id to be used for Create Order requests. */
    private static $orderId = null;
    private static $orderIdDefinition = false;

    /** @var array The customer object to be used for Create Order requests. */
    private static $customer = [];
    private static $customerDefinition = false;

    /** @var array The products object to be used for Create Order requests. */
    private static $products = [];
    private static $productsDefinition = false;

    /** @var string The order charges to be used for Create Order requests. */
    private static $sub_total_amount = null;
    private static $discount_amount = null;
    private static $total_amount = null;

    private static $chargesDefinition = false;

    /** @var array The shipment object to be used for Create Order requests. */
    private static $shipment = [
      "charges" => '',
      "method_name" => '',
    ];

    /* @var string $orderPayload this variable is used for, setting payload for create order API call to bSecure server */
    private static $orderPayload = [
      'order_id' => null,
      'customer' => null,
      'products' => null,
      'shipment_charges' => null,
      'shipment_method_name' => null,
      'sub_total_amount' => null,
      'discount_amount' => null,
      'total_amount' => null
    ];

    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    public static function setOrderId($orderId)
    {
        self::$orderIdDefinition =true;
        self::$orderId = $orderId;
    }
    /**
     * Sets the clientSecret to be used for Connect requests.
     *
     * @param string $clientSecret
     */
    public static function setCustomer($customerData)
    {
        self::$customerDefinition =true;
        $customer = self::_setCustomer($customerData);
        self::$customer = $customer;
    }

    /**
     * Sets the clientSecret to be used for Connect requests.
     *
     * @param string $clientSecret
     */
    public static function setShipmentDetails($shipmentData)
    {
        $shipmentObject = [];
        $shipment = self::_setShipmentDetails($shipmentData);
        $shipmentObject['charges'] = $shipment['charges'];
        $shipmentObject['method_name'] = $shipment['method_name'];
        self::$shipment = $shipmentObject;
    }

    /**
     * Sets the clientSecret to be used for Connect requests.
     *
     * @param string $clientSecret
     */
    public static function setCartItems($products)
    {
        $orderItems = [];

        if(!empty($products))
        {
            foreach ($products as $key => $product) {
                //Product Price
                $price = array_key_exists('price',$product) ? $product['price'] : 0;
                $sale_price = array_key_exists('sale_price',$product) ? $product['sale_price'] : 0;
                $quantity = array_key_exists('quantity',$product) ? $product['quantity'] : 1;

                //Product options
                $product_options = self::_setProductOptionsDataStructure($product);

                $options_price = $product_options['price'];
                $options = $product_options['options'];


                #Product charges
                $discount = ( $price - $sale_price ) * $quantity;
                $product_price = ( $price + $options_price ) * $quantity;
                $product_sub_total = ( $price + $options_price ) * $quantity;

                $orderItems[] = [
                  "id" => array_key_exists('id',$product) ? $product['id'] : null,
                  "name" => array_key_exists('name',$product) ? $product['name'] : null,
                  "sku" => array_key_exists('sku',$product) ? $product['sku'] : null,
                  "quantity" => $quantity,
                  "price" => $product_price,
                  "sale_price" => $sale_price,
                  "discount" => $discount,
                  "sub_total" => $product_sub_total,
                  "image" => array_key_exists('image',$product) ? $product['image'] : null,
                  "short_description" => array_key_exists('short_description',$product) ? $product['short_description'] : null,
                  "description" => array_key_exists('description',$product) ? $product['description'] : null,
                  "product_options" => $options
                ];
            }

        }
        self::$products = $orderItems;
        self::$productsDefinition =true;
    }

    /**
     * Sets the clientSecret to be used for Connect requests.
     *
     * @param string $clientSecret
     */
    public static function setCharges($orderCharges)
    {
        self::$sub_total_amount = array_key_exists('sub_total', $orderCharges) ? $orderCharges['sub_total'] : 0;
        self::$discount_amount = array_key_exists('discount', $orderCharges) ? $orderCharges['discount'] : 0;
        self::$total_amount = array_key_exists('total', $orderCharges) ? $orderCharges['total'] : 0;
        self::$chargesDefinition =true;
    }

    private static function _setCustomer($customerData)
    {
        $customer = [];
        if(!empty($customerData))
        {
            $auth_code = array_key_exists('auth_code',$customerData) ? $customerData['auth_code'] : '' ;

            if( !empty( $auth_code ) )
            {
                $customer = [
                  "auth_code" => $auth_code,
                ];;
            }
            else{
                $customer = [
                  "country_code" => array_key_exists('country_code',$customerData) ? $customerData['country_code'] : '',
                  "phone_number" => array_key_exists('phone_number',$customerData) ? $customerData['phone_number'] : '',
                  "name" => array_key_exists('name',$customerData) ? $customerData['name'] : '',
                  "email" => array_key_exists('email',$customerData) ? $customerData['email'] : '',
                ];
            }
        }

        return $customer;
    }

    private static function _setShipmentDetails($shipmentData)
    {
        $shipmentDetail = [
          "charges" => '',
          "method_name" => '',
        ];
        if(!empty($shipmentData))
        {
            $shipmentDetail['charges'] = array_key_exists('charges',$shipmentData) ? $shipmentData['charges'] : '';
            $shipmentDetail['method_name'] = array_key_exists('method_name',$shipmentData) ? $shipmentData['method_name'] : '';
        }
        return  $shipmentDetail;
    }

    private static function _setProductOptionsDataStructure($product)
    {
        $product_options = array_key_exists('product_options',$product) ? $product['product_options'] : [];

        $price = 0;
        if( isset($product_options) && !empty($product_options) )
        {
            foreach( $product_options as $productOption )
            {
                $productValue = array_key_exists('value',$productOption) ? $productOption['value'] : [];
                foreach( $productValue as $key => $optionValue )
                {
                    $optionPrice = array_key_exists('price',$optionValue) ? $optionValue['price'] : [];
                    if(!empty($optionPrice))
                    {
                        #Price ++
                        $price += $optionPrice;
                    }
                }
            }
        }

        return [
          'price' => $price,
          'options'  => $product_options
        ];
    }


    private static function _setOrderPayload()
    {
        if(!self::$chargesDefinition)
        {
            $msg = 'No charges provided.  (HINT: set your sub_total, discount and total amount using '
              . '"bSecure::setCharges(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }else if(!self::$productsDefinition)
        {
            $msg = 'No cart_items provided.  (HINT: set your cart_items using '
              . '"bSecure::setCartItems(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }else if(!self::$customerDefinition)
        {
            $msg = 'No customer_details provided.  (HINT: set your customer_details using '
              . '"bSecure::setCustomer(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }else{
            self::$orderPayload = [
              'order_id' => self::$orderId,
              'customer' => self::$customer,
              'products' => self::$products,
              'shipment_charges' => self::$shipment['charges'],
              'shipment_method_name' => self::$shipment['method_name'],
              'sub_total_amount' => self::$sub_total_amount,
              'discount_amount' => self::$discount_amount,
              'total_amount' => self::$total_amount
            ];
            return self::$orderPayload;
        }

    }
    /**
     * @return string the Auth Token used for requests
     */
    public static function createOrder()
    {
        self::_setOrderPayload();

        $msg = 'No auth_token provided.  (HINT: set your auth_token using '
          . '"bSecure::setAuthToken()".  See '
          . Constant::DOCUMENTATION_LINK.', for details, '
          . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
        $access_token = bSecure::getAuthToken();
        if($access_token == null)
            throw new Exception\AuthenticationException($msg);
        else{
            return SSOController::createOrder(self::$orderPayload);
        }
    }


    /**
     * @return string the Auth Token used for requests
     */
    public static function orderStatus($order_ref)
    {
        if($order_ref == null || $order_ref == "")
        {
            $msg = 'No order_ref provided. See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }

        return SSOController::orderStatus($order_ref);
    }
}