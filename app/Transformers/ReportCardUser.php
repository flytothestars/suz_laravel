<?php
namespace App\Transformers;

use App\Models\User;
use League\Fractal;

class ReportCardUser extends Fractal\TransformerAbstract
{
	public function transform(User $user)
	{
		return [
			'id'   => $user->getKey(),
			'name' => $user->name,
		];
	}
}
