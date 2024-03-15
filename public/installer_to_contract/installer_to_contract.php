<?php

if(($handle = fopen("to_contract.csv", "r")) !== FALSE)
{
	while(($data = fgetcsv($handle, 1000, ";")) !== FALSE)
	{
		$id = $data[0];
		$contract = $data[6];
		echo "update kit set owner_id = '$contract' WHERE id = $id;<br>";
	}
	fclose($handle);
}