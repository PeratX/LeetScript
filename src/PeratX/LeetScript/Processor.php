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

use iTXTech\SimpleFramework\Console\Logger;
use phqagent\element\User;
use phqagent\LeetQQListener;
use phqagent\message\Message;
use protocol\Protocol;

abstract class Processor implements LeetQQListener{

	/** @var Session[] */
	protected $sessions = [];
	/** @var LeetScript */
	protected $script;
	private $userClass;

	public function __construct(LeetScript $script, string $class){
		$this->script = $script;
		if(is_a($class, Session::class, true)){
			$this->userClass = $class;
		} else {
			Logger::error($class . " is not extended from " . Session::class);
		}
	}

	public function getScript(): LeetScript{
		return $this->script;
	}

	public function onMessageReceive(Message $message){
		if($session = $this->getSession($message->getFrom())){
			$session->processMessage($message);
		}else{
			/** @var Session $session */
			$session = new $this->userClass($this, $message->getUser());
			$this->addSession($session);
			$session->processMessage($message);
		}
	}

	public function shutdown(){
		foreach($this->sessions as $session){
			$session->close();
		}
	}

	public function doTick(int $currentTick){
		if($currentTick % 20 == 0){//1s
			foreach($this->sessions as $session){
				$session->onUpdate();
			}
			$this->checkNewFriend();
		}
	}

	protected $friendList = [];

	protected function checkNewFriend() : void{
		$list = Protocol::getFriendList();
		$uins = [];
		if(count($this->friendList) === 0){
			foreach($list as $uin => $user){
				$uins[] = $uin;
			}
			$this->friendList = $uins;
			return;
		}
		foreach($list as $uin => $user){
			if(!in_array($uin, $this->friendList)){
				//Got new Friend
				$this->onNewFriend(new User($uin));
			}
		}
		$this->friendList = $uins;
	}

	public function addSession(Session $session){
		$this->sessions[$session->getTarget()->getUin()] = $session;
	}

	public function removeSession(Session $session){
		unset($this->sessions[$session->getTarget()->getUin()]);
	}

	public function getSession($target){
		if($target instanceof User){
			if(isset($this->sessions[$target->getUin()])){
				return $this->sessions[$target->getUin()];
			}
		}
		return false;
	}

	public abstract function onNewFriend(User $user);
}