<?php

abstract class Prove_Util
{
  public static function isList($array)
  {
    if (!is_array($array))
      return false;
    // TODO: this isn't actually correct in general, but it's correct given Prove's responses
    foreach (array_keys($array) as $k) {
      if (!is_numeric($k))
        return false;
    }
    return true;
  }

  public static function convertProveObjectToArray($values)
  {
    $results = array();
    foreach ($values as $k => $v) {
      // FIXME: this is an encapsulation violation
      if (Prove_Object::$_permanentAttributes->includes($k)) {
        continue;
      }
      if ($v instanceof Prove_Object) {
        $results[$k] = $v->__toArray(true);
      }
      else if (is_array($v)) {
        $results[$k] = self::convertProveObjectToArray($v);
      }
      else {
        $results[$k] = $v;
      }
    }
    return $results;
  }

  public static function convertToProveObject($resp, $apiKey)
  {
    $types = array(
      'charge' => 'Prove_Charge',
		  'customer' => 'Prove_Customer',
      'list' => 'Prove_List',
		  'invoice' => 'Prove_Invoice',
		  'invoiceitem' => 'Prove_InvoiceItem',
      'event' => 'Prove_Event',
		  'transfer' => 'Prove_Transfer',
      'plan' => 'Prove_Plan',
      'recipient' => 'Prove_Recipient'
    );
    if (self::isList($resp)) {
      $mapped = array();
      foreach ($resp as $i)
        array_push($mapped, self::convertToProveObject($i, $apiKey));
      return $mapped;
    } else if (is_array($resp)) {
      if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']]))
        $class = $types[$resp['object']];
      else
        $class = 'Prove_Object';
      return Prove_Object::scopedConstructFrom($class, $resp, $apiKey);
    } else {
      return $resp;
    }
  }
}
