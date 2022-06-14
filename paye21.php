<?php

require "bootstrap.php";

use Strukt\Type\Number;
use Strukt\Core\TokenQuery as TokQ;
use Strukt\Type\Str;
use Strukt\Type\Json;
use Strukt\Raise;

$income = end($_SERVER["argv"]);
if(!is_numeric($income))
	new Raise("Number is required!");

$rates = array(

	array(

		"annual"=>"288000",
		"monthly"=>"24000",
		"rate"=>"0.1",
		"token"=>"type:first"
	),
	array(

		"annual"=>"100000",
		"monthly"=>"8333",
		"rate"=>"0.25",
		"token"=>"type:next"
	),
	array(

		"annual"=>"388000",
		"monthly"=>"32332",
		"rate"=>"0.3",
		"token"=>"type:last"
	)
);

$relief_ls = array(

	array(

		"name"=>"personal-relief",
		"annual"=>"28800",
		"monthly"=>"2400"
	)
);


$tax = Number::create(0);
$inc = Number::create($income); //Income
$rem = clone $inc;

if($inc->equals(115000))
	$rem = $inc->subtract(15000)->subtract(5000); //Subtract Morgage Interest & Pension

$net = clone $rem;

$ded = Number::create(0); //Deduction
$ttded = []; //Total Tax Deductions

$relief = reset($relief_ls); //Personal Relief
$relief = Number::create($relief["monthly"]);

foreach($rates as $idx=>$rate){

	$mbracket = Number::create($rate["monthly"]);
	$mrate = Number::create($rate["rate"]);
	$q = new TokQ($rate["token"]);

	$sType = Str::create($q->get("type"));

	$ded->reset();

	$resetRem = false;
	if($rem->lte($mbracket))
		$resetRem = true;

	if($sType->equals("first") || $sType->equals("next")){

		if($rem->gt($mbracket)){

			$rem = $rem->subtract($mbracket);
			$ded = $mbracket->times($mrate); //Tax Deduction
		}
	}

	if($sType->equals("next") && $ded->equals(0))
		if($rem->lt($mbracket))
			$ded = $rem->times($mrate);

	if($sType->equals("last")){

		if($rem->gt($mbracket))
			$ded = $net->subtract($mbracket)->times($mrate);

		if($rem->lt($mbracket))
			$ded = $rem->times($mrate);
	}

	if($resetRem)
		$rem->reset();

	$idx = $sType->yield();
	$tax = $tax->add($ded);
	$ttded[$idx] = $ded->yield();
}

if($tax->lte($relief))
	$tax->reset();

if($tax->gt($relief))
	$tax = $tax->subtract($relief);

echo Json::pp(array(

	"income"=>$inc->format(),
	"tax-brackets"=>array_map(function($val){

		return Number::create($val)->format();
		
	}, $ttded),
	"relief-types"=>array(
		"personal"=>$relief->format()
	),
	"taxable-amt"=>Number::create(array_sum($ttded))->format(),
	"tax"=>$tax->format()
));