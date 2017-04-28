<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt in the base directory.  If
 * not, see:
 *
 * @link      <http://www.gnu.org/licenses/>.
 * @author    niel
 * @copyright 2017 nZEDb
 */
namespace app\models;

use lithium\data\Entity;

class Predb extends \lithium\data\Model
{
	public $hasMany = ['Groups'];

	public $validates = [
		'title' => [
			[
				'required' => true
			],
			[
				'notEmpty',
				'message' => 'You must supply a title for this entry.'
			]
		]
	];

	public static function isModified(Entity $preEntry)
	{
		foreach ($preEntry->modified() as $field => $value) {
			if ($value) {
				if (nZEDb_DEBUG) {
					echo "Changed: $field\n";
				}
				return true;
			}
		}

		return false;
	}
}

?>
