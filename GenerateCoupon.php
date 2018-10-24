<?php
class GenerateCoupon {
	
  function calculateDiscount($defaultDiscountinPercentage, $cartTotal)
  {
    $percentAmount = ($defaultDiscountinPercentage / 100) * $cartTotal;
    $amountAfterDiscount = $cartTotal - $percentAmount;
    return $amountAfterDiscount;
  }
  function CreditDiscount($creditAmount, $cartTotal)
  {
    $creditDiscount = ($creditAmount/$cartTotal) * 100;
    return $creditDiscount;
  }
  function CreditDiscountFor100percent()
  {
    $creditDiscount = 100;
    return $creditDiscount;
  }
  function TotalDiscount($defaultDiscountinPercentage, $creditDiscount)
  {
    $totalDiscount = $defaultDiscountinPercentage + $creditDiscount;
    return $totalDiscount;
  }

}
?>