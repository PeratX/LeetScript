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

use iTXTech\SimpleFramework\Util\Config;

class LeetScript{
	public const ACTION_MSG = "msg";
	public const ACTION_GOTO = "goto";

	public const STRING_ERROR_OCCURRED = "error_occurred";
	public const STRING_OPT_NOT_FOUND = "opt_not_found";

	private $staticStringCacheKeys;
	private $staticStringCacheValues;

	/** @var array */
	private $rawData;
	/** @var string[] */
	private $string;
	/** @var string */
	private $entry;
	/** @var string[][] */
	private $menu;

	public function __construct(string $file){
		$this->rawData = (new Config($file, Config::YAML))->getAll();
		$this->string = $this->rawData["string"] ?? [];
		$this->entry = $this->rawData["entry"];
		$this->menu = $this->rawData["menu"];
		$this->generateStaticStrings();
	}

	public function getEntry(){
		return $this->entry;
	}

	public function getMenu(string $menu): array{
		return $this->menu[$menu] ?? [];
	}

	private function buildStaticStringCache(){
		$this->staticStringCacheKeys = [];
		$this->staticStringCacheValues = [];
		foreach($this->string as $key => $value){
			$this->staticStringCacheKeys[] = "{" . $key . "}";
			$this->staticStringCacheValues[] = $value;
		}
	}

	private function processStaticString(string $str){
		return str_replace($this->staticStringCacheKeys, $this->staticStringCacheValues, $str);
	}

	private function generateStaticStrings(){
		$this->buildStaticStringCache();
		foreach($this->string as $key => $value){
			$this->string[$key] = $this->processStaticString($value);
		}
		$this->buildStaticStringCache();
		foreach($this->menu as $mk => $menu){
			$menu["title"] = $this->processStaticString($menu["title"]);
			foreach($menu["opt"] as $ok => $opt){
				$opt["title"] = $this->processStaticString($opt["title"]);
				if($opt["action"] === self::ACTION_MSG){
					$opt[self::ACTION_MSG] = $this->processStaticString($opt[self::ACTION_MSG]);
				}
				$menu["opt"][$ok] = $opt;
			}
			$this->menu[$mk] = $menu;
		}
	}

	public function getStaticString(string $key){
		return $this->string[$key] ?? "null";
	}
}