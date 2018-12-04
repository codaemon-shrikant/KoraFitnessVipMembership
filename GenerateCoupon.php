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
    return round($creditDiscount, 2);
  }
  function CreditDiscountFor100percent()
  {
    $creditDiscount = 100;
    return round($creditDiscount, 2);
  }
  function TotalDiscount($defaultDiscountinPercentage, $creditDiscount)
  {
    $totalDiscount = $defaultDiscountinPercentage + $creditDiscount;
    return round($totalDiscount, 2);
  }

}
?>