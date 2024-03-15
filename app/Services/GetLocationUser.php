<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;

final class GetLocationUser
{
	public function execute(int $locationId)
	{
		$installers = [];
		$user       = Auth::user();
		$locations  = $user->locations->all();

		foreach ($locations as $location) {
			if ($location->id_location === $locationId) {
				return $location->users->all();
			}
		}

		return $installers;
	}
}
