<?php

class WeatherInfoAnalyzer {
    private $dayTemperatureInfoArray = array();
    private $file;

    public function __construct($file)
    {

        $this->file = $file;
    }

    public function prepareDayTemperatureInfoArray($filePath) {
        $this->file->open($filePath);
        while(($line = $this->file->readLine()) !== false) {
            $columns = preg_split("/\s+/", trim($line));
            if($this->shouldSkip($columns)) {
                continue;
            }
            $this->dayTemperatureInfoArray[] = new DayTemperatureInfo($columns[2], $columns[1], $columns[0]);
        }

        $this->file->close();
    }
    /**
     * @param $columns
     * @return bool
     */
    public function shouldSkip($columns)
    {
        return !sizeof($columns) || !is_numeric($columns[0]) || !is_numeric($columns[1]) || !is_numeric($columns[2]);
    }

    public function findDayWithLeastDiff() {
        $min = $this->dayTemperatureInfoArray[0] ;
        foreach ($this->dayTemperatureInfoArray as $dayTemperatureInfo) {
            if($dayTemperatureInfo->diff() < $min->diff()) {
                $min = $dayTemperatureInfo;
            }
        }
        return $min;
    }

}

class File {
    private $handle;
    private static $instance = null;
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function open($path = null) {
        $this->handle = fopen($path, 'r');
    }

    public function readLine() {
        return fgets($this->handle);
    }

    public function close() {
        fclose($this->handle);
    }
}

class DayTemperatureInfo {
    private $minTemp;
    private $maxTemp;
    private $day;

    public function __construct($minTemp, $maxTemp, $day)
    {
        $this->minTemp = $minTemp;
        $this->maxTemp = $maxTemp;
        $this->day = $day;
    }

    public function __toString()
    {
        return $this->day . '  -> ' . $this->diff();
    }

    public function diff()
    {
        return $this->maxTemp - $this->minTemp;
    }

}

$o = new WeatherInfoAnalyzer(File::getInstance());
$o->prepareDayTemperatureInfoArray(__DIR__ . DIRECTORY_SEPARATOR . 'weather.dat');
echo $o->findDayWithLeastDiff();
