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

use phqagent\element\User;
use phqagent\message\Message;

abstract class Session{
	const STATE_STARTING = 0;

	/** @var User */
	protected $user;
	/** @var Processor*/
	protected $processor;
	protected $timeout = 5 * 60;
	protected $lastUpdate;
	protected $menu;
	protected $firstTime;

	public function __construct(Processor $processor, User $user){
		$this->user = $user;
		$this->processor = $processor;
		$this->lastUpdate = time();
		$this->firstTime = true;
	}

	public function onUpdate(){
		if((time() - $this->lastUpdate) > $this->timeout){
			$this->close();
			$this->processor->removeSession($this);
		}
	}

	public function processMessage(Message $message){
		var_dump($message->getContent(), $this->user->getName());
		$this->lastUpdate = time();
		if($this->firstTime){
			$this->firstTime = false;
			$this->menu = $this->processor->getScript()->getEntry();
			$this->sendMessage($this->generateMessage($this->menu));
			return;
		}
		$menu = $this->processor->getScript()->getMenu($this->menu);
		$opt = $menu["opt"][$message->getContent()] ?? [];
		if($opt !== []){
			if($opt["action"] === LeetScript::ACTION_MSG){
				$this->sendMessage($opt[LeetScript::ACTION_MSG]);
			} elseif($opt["action"] === LeetScript::ACTION_GOTO){
				$this->menu = $opt[LeetScript::ACTION_GOTO];
				$this->sendMessage($this->generateMessage($this->menu));
			}
		} else {
			$this->sendMessage($this->generateMessage($this->menu,
				$this->processor->getScript()->getStaticString(LeetScript::STRING_OPT_NOT_FOUND) . PHP_EOL));
		}
	}

	private function generateMessage(string $menuName, string $extra = ""): string {
		$menu = $this->processor->getScript()->getMenu($menuName);
		if($menu === []){
			return $this->processor->getScript()->getStaticString(LeetScript::STRING_ERROR_OCCURRED);
		}
		$msg = $extra . $menu["title"] . PHP_EOL;
		foreach($menu["opt"] as $key => $value){
			$msg .= $key . ". " . $value["title"] . "\n";
		}
		return substr($msg, 0, strlen($msg) - 1);
	}

	protected function sendMessage(string $message){
		LeetScriptHelper::sendMessage($this->user, $message);
	}

	public function getTarget(){
		return $this->user;
	}

	public abstract function close();
}