<?php

/**
 *
 * LeetScript interpreter
 *
 * Copyright (C) 2017-2018 by PeratX <peratx@itxtech.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 *
 */

namespace PeratX\LeetScript;

use iTXTech\SimpleFramework\Module\Module;
use phqagent\element\User;
use phqagent\message\Message;

class LeetScriptHelper extends Module{
	public function load(){
	}

	public function unload(){
	}

	public static function sendMessage(User $user, string $msg){
		new Message($user, $msg, true);
	}
}