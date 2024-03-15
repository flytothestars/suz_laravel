<?php

if(($handle = fopen("to_contract.csv", "r")) !== FALSE)
{
	while(($data = fgetcsv($handle, 1000, ";")) !== FALSE)
	{
		$serial = $data[0];
		$contract = $data[1];
		// echo "update kit set `owner_id` = $contract WHERE `v_serial` = '$serial';<br>";
		echo "update kit set `stock_id` = NULL WHERE `v_serial` = '$serial' AND `owner_id` = '$contract';<br>";
	}
	fclose($handle);
}