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

		"lbound"=>0,
		"ubound"=>12298,
		"rate"=>0.1,
		"token"=>"type:first"
	),
	array(

		"lbound"=>12299,
		"ubound"=>23885,
		"rate"=>0.15,
		"token"=>"type:next"
	),
	array(

		"lbound"=>23886,
		"ubound"=>35472,
		"rate"=>0.2,
		"token"=>"type:next"
	),
	array(

		"lbound"=>35473,
		"ubound"=>47059,
		"rate"=>0.25,
		"token"=>"type:next"
	),
	array(

		"lbound"=>47059,
		"ubound"=>1000000000,
		"rate"=>0.3,
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
$net = clone $rem;

$ttded = []; //Total Tax Deductions
$relief = reset($relief_ls); //Personal Relief
$relief = Number::create($relief["monthly"]);

foreach($rates as $idx=>$rate){

	$mrate = Number::create($rate["rate"]);
	$mlbound = Number::create($rate["lbound"]);
	$mubound = Number::create($rate["ubound"]);
	$q = new TokQ($rate["token"]);

	$sType = Str::create($q->get("type"));

	if($net->gte($mlbound)){

		if($sType->equals("last"))
			$rem = $net->subtract($mlbound);
		
		if($sType->equals("first") || $sType->equals("next"))
			$rem = $mubound->subtract($mlbound);

		$idx = sprintf("%s:%d", $sType->yield(), $idx);
		if($sType->equals("first") || $sType->equals("last"))
			$idx = $sType->yield();

		$ded = $rem->times($mrate);
		$ttded[$idx] = $ded->yield();
		$tax = $tax->add($ded);
	}
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