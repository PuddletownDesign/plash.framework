<?php

/** 
 * Benchmark Class
 * 
 * start() - starts the benchmarking app
 * stop() - stops the benchmarking app
 * display - displays the benchmarking result
 */
namespace Plash;

class Benchmark {
	private $elapsedTime, $memory, $enabled;
	
	// start timer
	public function __construct($label='Unlabeled') 
	{
		$this->label = $label . ' Benchmark';
	}
	
	/** 
	 * Start the benchmark
	 * @param [String]
	 * @return Null
	 */
	public function start($enabled=true) {
		$this->enabled = $enabled;
		if (!$this->enabled){ return false; }
		
		if(!$this->elapsedTime=$this->getMicrotime()){
			throw new Exception('Error obtaining start time!');
		}
	}
	
	/** 
	 * Stop the bench mark
	 * 
	 */
	public function stop(){
		if (!$this->enabled){ return false; }
		
		if(!$this->elapsedTime=round($this->getMicrotime()-$this->elapsedTime,5)){
			throw new Exception('Error obtaining stop time!');
		}
		$this->memory = $this->memUsage();
		$this->peak_memory = $this->peakMemUsage();
		$this->elapsedTime;
	}
	
	/** 
	 * Display benchmark data
	 * @param 
	 * @return string
	 */
	public function display()
	{
		if (!$this->enabled) { return false;}
		
		$string = "<hr><strong>{$this->label}</strong><br>";
		$string .= "<small>{$this->elapsedTime} seconds</small> <b>/</b> ";
		$string .= "<small>{$this->memory} MB RAM</small>" . ' <b>/</b> ';
		$string .= "<small>{$this->peak_memory} MB (peak)</small>";
		echo $string . '<br>';
	}
	
	//define private 'getMicrotime()' method
	private function getMicrotime(){
		list($useg,$seg)=explode(' ',microtime());
		return ((float)$useg+(float)$seg);
	}
	
	//mem usage
	private function memUsage()
	{
	    return (memory_get_usage(true)/1000)/1000;
	}
	private function peakMemUsage()
	{
		return (memory_get_peak_usage(true)/1000)/1000;
	}
}

?>