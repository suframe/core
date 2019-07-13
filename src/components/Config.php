<?php
/**
 * User: qian
 * Date: 2019/6/6 15:45
 */

namespace suframe\core\components;

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\ZendConfigProvider;

class Config extends \Zend\Config\Config {
	private static $instance;

	/**
	 * @param mixed ...$args
	 * @return static
	 */
	static function getInstance(...$args) {
		if (!isset(self::$instance)) {
			self::$instance = new static($args[0] ?? [], true);
		}
		return self::$instance;
	}
	/**
	 * 获取所有配置
	 * @return array
	 */
	public function getAll(){
		return $this->data;
	}

	/**
	 * Retrieve a value and return $default if there is no element set.
	 *
	 * @param  string $name
	 * @param  mixed  $default
	 * @return mixed|Config
	 */
	public function get($name, $default = null)
	{
		$names = explode('.', $name);
		/** @var Config $value */
		$value = '';
		foreach ($names as $item) {
			if($value){
				if(!is_object($value)){
					return null;
				}
				$value = $value->get($item);
			} else {
				if (array_key_exists($item, $this->data)) {
					$value = $this->data[$item];
				} else {
					return $default;
				}
			}
		}
		return $value ?: $default;
	}

	/**
	 * 加载文件
	 * @param $file
	 * @return \Zend\Config\Config
	 */
	public function loadFile($file, $allowModifications = true){
		$data = $this->aggregator($file);
		$config = new \Zend\Config\Config($data, $allowModifications);
		return $this->merge($config);
	}

	/**
	 * 加载文件并设置名称
	 * @param $file
	 * @param null $name
	 * @return $this|\Zend\Config\Config
	 */
	public function loadFileByName($file, $name = null, $allowModifications = true) {
		if (!is_file($file)) {
			return $this;
		}
		if (!$name) {
			$name = substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1, strrpos($file, '.') - strlen($file));
		}
		$data = $this->aggregator($file);
		$config = new \Zend\Config\Config([$name => $data], $allowModifications);
		return $this->merge($config);
	}

	/**
	 * 批量获取文件
	 * @param $files
	 * @return $this
	 */
	public function loadFilesByName($files, $allowModifications = true) {
		if (is_string($files)) {
			$files = [$files];
		}
		foreach ($files as $file) {
			$this->loadFileByName($file, $allowModifications);
		}
		return $this;
	}

	/**
	 * 读取文件
	 * @param $file
	 * @return array
	 */
	protected function aggregator($file) {
		$aggregator = new ConfigAggregator([
			new ZendConfigProvider($file)
		]);
		return $aggregator->getMergedConfig();
	}

}